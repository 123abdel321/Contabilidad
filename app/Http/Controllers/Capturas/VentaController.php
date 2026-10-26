<?php

namespace App\Http\Controllers\Capturas;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
// SERVICES
use App\Http\Services\VentaServices;
// JOBS
use App\Jobs\SendSingleEmail;
use App\Jobs\ProcessConsultarFE;
// HELPERS
use App\Helpers\Printers\VentasPdf;
use App\Helpers\Printers\VentasPos;
use App\Helpers\Eco\SendEcoWhatsApp;
use App\Helpers\Printers\VentasInformeZ;
use App\Helpers\FacturaElectronica\VentaElectronicaSender;
use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;
// TRAITS
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;
use App\Http\Controllers\Traits\BegFacturacionElectronica;
// MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacVentas;
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacFormasPago;
use App\Models\Sistema\FacResoluciones;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Empresas\UsuarioPermisos;
use App\Models\Sistema\FacVentaDetalles;

class VentaController extends Controller
{
    use BegDocumentHelpersTrait;
    use BegFacturacionElectronica;

    protected VentaServices $ventaServices;

    protected $messages = [
        'required' => 'El campo :attribute es requerido.',
        'exists'   => 'El :attribute es inválido.',
        'numeric'  => 'El campo :attribute debe ser un valor numérico.',
        'string'   => 'El campo :attribute debe ser texto',
        'array'    => 'El campo :attribute debe ser un arreglo.',
        'date'     => 'El campo :attribute debe ser una fecha válida.',
    ];

    protected $ventaData = [];

    public function __construct(
        Request $request,
        VentaServices $ventaServices,
    ) {
        $this->ventaServices = $ventaServices;
    }

    // ============================================================
    // INDEX
    // ============================================================

    public function index(Request $request)
    {
        $usuarioPermisos = UsuarioPermisos::where('id_user', request()->user()->id)
            ->where('id_empresa', request()->user()->id_empresa)
            ->first();

        $valorUVT = VariablesEntorno::where('nombre', 'valor_uvt')->first();
        $ivaIncluido = VariablesEntorno::where('nombre', 'iva_incluido')->first();
        $vendedorVentas = VariablesEntorno::where('nombre', 'vendedores_ventas')->first();
        $idClientePorDefecto = VariablesEntorno::where('nombre', 'id_cliente_venta_defecto')->first();
        $idClientePorDefecto = $idClientePorDefecto && $idClientePorDefecto->valor ? $idClientePorDefecto->valor : null;
        $cuentaPropina = VariablesEntorno::where('nombre', 'cuenta_propina')->first();
        $cuentaPropina = $cuentaPropina && $cuentaPropina->valor ? $cuentaPropina->valor : null;
        $porcentajePropina = VariablesEntorno::where('nombre', 'porcentaje_propina')->first();
        $porcentajePropina = $porcentajePropina && $porcentajePropina->valor ? $porcentajePropina->valor : null;
        $multiplesPrecios = VariablesEntorno::where('nombre', 'precios_multiples_venta')->first();
        $multiplesPrecios = $multiplesPrecios && $multiplesPrecios->valor ? $multiplesPrecios->valor : null;

        if ($cuentaPropina) {
            $cuentaPropina = PlanCuentas::where('cuenta', $cuentaPropina)->first();
        }

        $clientePorDefecto = null;
        if ($idClientePorDefecto) $clientePorDefecto = Nits::with('vendedor.nit')->where('id', $idClientePorDefecto)->first();
        else $clientePorDefecto = Nits::with('vendedor.nit')->where('numero_documento', 'LIKE', '22222%')->first();

        $bodegas = explode(",", $usuarioPermisos->ids_bodegas_responsable);
        $resolucionesId = explode(",", $usuarioPermisos->ids_resolucion_responsable);
        $resolucionesData = FacResoluciones::whereIn('id', $resolucionesId)
            ->whereIn('tipo_resolucion', [FacResoluciones::TIPO_POS, FacResoluciones::TIPO_FACTURA_ELECTRONICA])
            ->get();

        $data = [
            'cliente'            => $clientePorDefecto,
            'resolucion'         => $resolucionesData,
            'cuentaPropina'      => $cuentaPropina,
            'multiplesPrecios'   => $multiplesPrecios,
            'porcentajePropina'  => $porcentajePropina,
            'valor_uvt'          => $valorUVT ? $valorUVT->valor : 0,
            'bodegas'            => FacBodegas::whereIn('id', $bodegas)->get(),
            'iva_incluido'       => $ivaIncluido ? $ivaIncluido->valor : '',
            'vendedores_ventas'  => $vendedorVentas ? $vendedorVentas->valor : ''
        ];

        return view('pages.capturas.venta.venta-view', $data);
    }

    public function indexInforme()
    {
        return view('pages.contabilidad.ventas.ventas-view');
    }

    public function indexInformeGeneral()
    {
        return view('pages.contabilidad.ventas.ventas-view');
    }

    // ============================================================
    // CREATE — wrapper del servicio
    // ============================================================

    public function create(Request $request)
    {
        $rules = [
            'id_cliente'           => 'required|exists:sam.nits,id',
            'id_bodega'            => 'required|exists:sam.fac_bodegas,id',
            'id_resolucion'        => 'required|exists:sam.fac_resoluciones,id',
            'fecha_manual'         => 'required|date',
            'documento_referencia' => 'required|string',
            'propina'              => 'nullable',
            'productos'            => 'array|required',
            'productos.*.id_producto' => [
                'required',
                'exists:sam.fac_productos,id',
                function ($attribute, $value, $fail) {
                    $producto = FacProductos::whereId($value)
                        ->with('familia')
                        ->first();

                    if (!$producto->id_familia) {
                        $fail("El producto (".$producto->codigo." - ".$producto->nombre.") no tiene familia configurada");
                    } else if (!$producto->familia->id_cuenta_venta) {
                        $fail("La familia (".$producto->familia->codigo." - ".$producto->familia->nombre.") no tiene cuenta venta configurada");
                    }
                }
            ],
            'productos.*.cantidad'             => 'required|numeric|gt:0',
            'productos.*.costo'                => 'required|min:0',
            'productos.*.descuento_porcentaje' => 'required|numeric|min:0|max:100',
            'productos.*.descuento_valor'      => 'required|numeric|min:0',
            'productos.*.iva_porcentaje'       => 'required|numeric|min:0|max:100',
            'productos.*.iva_valor'            => 'required|numeric|min:0',
            'productos.*.total'                => 'required|numeric|min:0',
            'productos.*.concepto'             => 'nullable',
            'pagos'                            => 'array|required',
            'pagos.*.id'                       => 'required|exists:sam.fac_formas_pagos,id',
            'pagos.*.valor'                    => 'required|numeric|min:1',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'data'    => [],
                "message" => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $user = $request->user();

            $contexto = [
                'id_usuario'           => $user->id,
                'id_empresa'           => $user->id_empresa,
                'token_db'             => $user->has_empresa,
                'id_cliente'           => $request->get('id_cliente'),
                'id_bodega'            => $request->get('id_bodega'),
                'id_resolucion'        => $request->get('id_resolucion'),
                'id_vendedor'          => $request->get('id_vendedor'),
                'id_familia'           => null,
                'productos'            => $request->get('productos'),
                'pagos'                => $request->get('pagos'),
                'propina'              => $request->get('propina') ?? 0,
                'calcula_retencion'    => $request->get('calcula_retencion') ?? false,
                'fecha_manual'         => $request->get('fecha_manual'),
                'documento_referencia' => $request->get('documento_referencia'),
                'observacion'          => $request->get('observacion'),
                'auto_crear_nit'       => false,
                'auto_crear_producto'  => false,
                'fe_async'             => false,
            ];

            $venta = $this->ventaServices->create($contexto);

            return response()->json([
                'success'   => true,
                'data'      => [],
                'impresion' => ($venta->resolucion
                    && $venta->resolucion->comprobante
                    && $venta->resolucion->comprobante->imprimir_en_capturas)
                    ? $venta->id
                    : '',
                'message'   => 'Venta creada con exito!'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                "success" => false,
                'data'    => [],
                "message" => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    // ============================================================
    // GENERATE — listado de ventas (intacto)
    // ============================================================

    public function generate(Request $request)
    {
        $draw       = $request->get('draw');
        $start      = (int) $request->get("start", 0);
        $rowperpage = (int) $request->get("length", 20);
        $rowperpage = $rowperpage > 0 ? min($rowperpage, 500) : 20;
        $detallar   = $request->get('detallar_venta') === 'si';

        $baseQuery = $this->buildVentasQuery($request);

        $totalRecords = (clone $baseQuery)->count();

        $with = [
            'bodega:id,codigo,nombre',
            'cliente:id,razon_social,primer_nombre,otros_nombres,primer_apellido,segundo_apellido,email,telefono_1,telefono_2,notificacion_whatsapp',
            'vendedor.nit:id,razon_social,primer_nombre,otros_nombres,primer_apellido,segundo_apellido',
            'resolucion:id,tipo_resolucion,nombre,prefijo,consecutivo_desde,consecutivo_hasta',
            'factura:id,id_resolucion,fe_codigo_identificador',
        ];

        if ($detallar) {
            $with['detalles'] = function ($q) {
                $q->select(
                    'id', 'id_venta', 'id_producto', 'cantidad', 'costo',
                    'subtotal', 'iva_porcentaje', 'iva_valor',
                    'descuento_porcentaje', 'descuento_valor', 'total'
                )->with('producto:id,codigo,nombre,precio_inicial');
            };
        }

        $dataVentas = (clone $baseQuery)
            ->with($with)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();

        [$totalesGeneral, $totalesNotas] = $this->getTotalesVentas($request);

        $this->ventaData = [];
        $this->generarVentaDetallesOptimizado($dataVentas, $detallar);

        return response()->json([
            'success'              => true,
            'draw'                 => $draw,
            'totalesVenta'         => [$totalesGeneral],
            'totalesNotas'         => [$totalesNotas],
            'iTotalRecords'        => $totalRecords,
            'iTotalDisplayRecords' => $totalRecords,
            'data'                 => $this->ventaData,
            'perPage'              => $rowperpage,
            'message'              => 'Ventas cargadas con éxito!',
        ]);
    }

    // ============================================================
    // READ — facturas para POS (intacto)
    // ============================================================

    public function read(Request $request)
    {
        $draw       = $request->get('draw');
        $start      = $request->get("start");
        $rowperpage = $request->get("length");

        $facturas = FacVentas::orderBy('id', 'DESC')
            ->with('bodega', 'cliente', 'comprobante', 'centro_costo')
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'created_by',
                'updated_by'
            );

        if ($request->get('consecutivo'))  $facturas->where('consecutivo', $request->get('consecutivo'));
        if ($request->get('id_cliente'))   $facturas->where('id_cliente', $request->get('id_cliente'));
        if ($request->get('id_bodega'))    $facturas->where('id_bodega', $request->get('id_bodega'));
        if ($request->get('id_resolucion')) $facturas->where('id_resolucion', $request->get('id_resolucion'));

        $facturas->where('codigo_tipo_documento_dian', CodigoDocumentoDianTypes::VENTA_NACIONAL);

        $facturasTotals   = $facturas->get();
        $facturasPaginate = $facturas->skip($start)->take(10);

        return response()->json([
            'success'              => true,
            'draw'                 => $draw,
            'iTotalRecords'        => $facturasTotals->count(),
            'iTotalDisplayRecords' => $facturasTotals->count(),
            'data'                 => $facturasPaginate->get(),
            'perPage'              => 10,
            'message'              => 'Facturas generados con exito!'
        ]);
    }

    // ============================================================
    // PDFs (intactos)
    // ============================================================

    public function showPdf(Request $request, $id)
    {
        $factura = FacVentas::whereId($id)
            ->with('resolucion', 'comprobante')
            ->first();

        if (!$factura) {
            return response()->json([
                'success' => false,
                'data'    => [],
                'message' => 'La factura no existe'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

        if ($factura->resolucion?->tipo_impresion == 0) {
            $data = (new VentasPos($empresa, $factura, null))->buildPdf()->getData();
            return view('pdf.facturacion.ventas-pos', $data);
        }

        $claveUrl = $this->generarClavePDF(
            $empresa->id,
            $factura->comprobante->id,
            $factura->consecutivo,
            $factura->fecha_manual
        );

        return (new VentasPdf($empresa, $factura, $claveUrl))
            ->buildPdf()
            ->showPdf();
    }

    public function showPdfZ(Request $request)
    {
        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        $data    = (new VentasInformeZ($empresa, $request->all()))->buildPdf()->getData();

        return view('pdf.facturacion.ventas-informez-pos', $data);
    }

    // ============================================================
    // FACTURACIÓN ELECTRÓNICA — reintento manual (intacto)
    // ============================================================

    public function facturacionElectronica(Request $request)
    {
        $rules = ['id_venta' => "required|exists:sam.fac_ventas,id"];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'data'    => [],
                "message" => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $venta   = FacVentas::where('id', $request->id_venta)->first();
        $empresa = Empresa::where('id', $request->user()->id_empresa)->first();

        if ($venta->fe_codigo_identificador) {
            return response()->json([
                "success" => false,
                'data'    => [],
                "message" => ['factura_electronica' => ['mensaje' => "La factura $venta->consecutivo ya fue emitida."]]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::connection('sam')->beginTransaction();

            if ($venta->fe_zip_key) {
                $consulta = (new VentaElectronicaSender($venta))->consultarZip($venta->fe_zip_key);

                if ($consulta['success'] && $consulta['is_valid']) {
                    $venta = $this->SetFeFields($venta, $consulta['cufe'], $empresa->nit);
                    $venta->save();

                    DB::connection('sam')->commit();

                    return response()->json([
                        'success' => true,
                        'data'    => [],
                        'message' => 'Factura electrónica enviada!',
                    ], 200);
                }

                if (!$consulta['success']) {
                    \Log::warning('[ventas-fe] consultarZip falló', $consulta);
                }
            }

            $ventaElectronica = (new VentaElectronicaSender($venta))->send();

            if ($ventaElectronica["status"] >= 400) {
                if ($ventaElectronica["zip_key"]) {
                    $venta->fe_zip_key = $ventaElectronica["zip_key"];
                    $venta->save();

                    if ($ventaElectronica["message_object"] == 'Batch en proceso de validación.') {
                        ProcessConsultarFE::dispatch($venta->id, $ventaElectronica["zip_key"], $request->user()->id, $empresa->id);

                        DB::connection('sam')->commit();

                        return response()->json([
                            "success" => false,
                            'data'    => [],
                            "message" => 'Batch en proceso de validación, el sistema le notificará una vez haya consultado la información'
                        ], 300);
                    }
                }

                DB::connection('sam')->commit();

                return response()->json([
                    "success" => false,
                    'data'    => [],
                    "message" => $ventaElectronica["error_message"]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($ventaElectronica["status"] == 200) {
                $feSended = $ventaElectronica['status'] == 200;
                $hasCufe  = (isset($ventaElectronica['cufe']) && $ventaElectronica['cufe']);

                if ($feSended || $hasCufe) {
                    $venta = $this->SetFeFields($venta, $ventaElectronica['cufe'], $empresa->nit);
                    $venta->fe_zip_key  = $ventaElectronica['zip_key'];
                    $venta->fe_xml_file = $ventaElectronica['xml_url'];
                    $venta->save();
                }
            }

            DB::connection('sam')->commit();

            return response()->json([
                'success' => true,
                'data'    => [],
                'message' => 'Factura electrónica enviada!'
            ], 200);

        } catch (\Throwable $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success" => false,
                'data'    => [],
                "message" => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    // ============================================================
    // EMAIL / NOTIFICACIÓN (intactos)
    // ============================================================

    public function sendEmail(Request $request)
    {
        $rules = [
            'email'    => 'required|email',
            'id_venta' => "required|exists:sam.fac_ventas,id",
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'data'    => [],
                "message" => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $empresa = Empresa::where('id', $request->user()->id_empresa)->first();

            $ecoToken = VariablesEntorno::where('nombre', 'eco_login')->first();
            $ecoToken = $ecoToken?->valor ?? null;

            if (!$ecoToken) {
                return response()->json([
                    "success" => false,
                    'data'    => [],
                    "message" => ['token Eco' => ['No se ha configurado el token de ECO en las variables de entorno.']]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $venta = FacVentas::with('cliente')->where('id', $request->id_venta)->first();

            $claveUrl = $this->generarClavePDF(
                $empresa->id,
                $venta->comprobante->id,
                $venta->consecutivo,
                $venta->fecha_manual
            );

            $filterData = [
                'id_nit'           => $venta->cliente->id_nit,
                'nombre_completo'  => $venta->cliente->nombre_completo,
                'numero_documento' => $venta->cliente->numero_documento,
                'email'            => $venta->cliente->email,
            ];

            $facturaPdf = (new VentasPdf($empresa, $venta, $claveUrl))->buildPdf()->saveStorage();
            $emailData  = [
                'cliente'  => $venta->cliente,
                'factura'  => $venta,
                'empresa'  => $empresa
            ];

            if ($facturaPdf) {
                $path    = stripslashes($facturaPdf);
                $baseUrl = "https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/";

                if (!str_contains($path, $baseUrl)) {
                    $facturaPdf = $baseUrl . $path;
                }
            }

            SendSingleEmail::dispatch(
                $empresa,
                $request->get('email'),
                $emailData,
                $filterData,
                $facturaPdf,
                $ecoToken,
                'emails.capturas.factura',
            );

            return response()->json([
                'success' => true,
                'data'    => [],
                'message' => 'Factura enviada con exito!'
            ], 200);

        } catch (\Throwable $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success" => false,
                'data'    => [],
                "message" => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function sendNotification(Request $request)
    {
        $rules = ['id_venta' => "required|exists:sam.fac_ventas,id"];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'data'    => [],
                "message" => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $venta = FacVentas::with('cliente')->where('id', $request->id_venta)->first();

            if ($this->isFe($venta) && !$venta->cufe) {
                return response()->json([
                    "success" => false,
                    'data'    => [],
                    "message" => "La factura electrónica $venta->documento_referencia_fe no tiene cufe generado.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

            $claveUrl = $this->generarClavePDF(
                $empresa->id,
                $venta->comprobante->id,
                $venta->consecutivo,
                $venta->fecha_manual
            );

            $pdf   = (new VentasPdf($empresa, $venta, $claveUrl))->buildPdf()->saveStorage();
            $email = $request->get('email') ?? $venta->cliente->email;

            $this->sendEmailFactura(
                $request->user()['has_empresa'],
                $email,
                $venta,
                $pdf
            );

            return response()->json([
                'success' => true,
                'data'    => [],
                'message' => 'Factura enviada con exito!'
            ], 200);

        } catch (\Throwable $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success" => false,
                'data'    => [],
                "message" => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function sendWhatsapp(Request $request)
    {
        $rules = [
            'id_venta' => 'required|exists:sam.fac_ventas,id',
            'telefono' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $empresa = Empresa::where('id', $request->user()->id_empresa)->first();

            if (!$empresa) {
                throw new \Exception('Empresa no encontrada.');
            }

            // Token ECO
            $ecoToken = VariablesEntorno::where('nombre', 'eco_login')->first();
            $ecoToken = $ecoToken?->valor ?? null;

            if (!$ecoToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se ha configurado el token de ECO en las variables de entorno.',
                ], 422);
            }

            // Venta
            $venta = FacVentas::with('cliente', 'comprobante', 'resolucion')
                ->where('id', $request->id_venta)
                ->first();

            if (!$venta) {
                throw new \Exception('Venta no encontrada.');
            }

            // ─── Datos de la plantilla ───
            $nombreCliente = $venta->cliente
                ? $venta->cliente->nombre_completo
                : 'Cliente';

            // Tipo de documento
            $tipoDocumento = 'Factura';

            // Número del documento
            $numeroDocumento = $venta->documento_referencia ?: $venta->consecutivo;

            // Valor
            $valorDocumento = number_format((float) $venta->total_factura, 2, ',', '.');

            // Fecha del documento
            $fechaDocumento = $venta->fecha_manual
                ? Carbon::parse($venta->fecha_manual)->format('Y-m-d')
                : Carbon::now()->format('Y-m-d');

            // Estado del documento (Pagada / Completada)
            $estadoDocumento = 'Pagada';

            // Nombre de la empresa emisora
            $nombreEmpresa = $empresa->razon_social ?: 'PORTAFOLIO ERP SAS';

            // ─── Clave del PDF ───
            $claveUrl = $this->generarClavePDF(
                $empresa->id,
                $venta->comprobante->id,
                $venta->consecutivo,
                $venta->fecha_manual
            );

            // La URL del PDF va SIN el dominio (el botón ya lo tiene)
            $linkFactura = 'documentos-generales-pdf?code=' . $claveUrl;

            // ─── Parámetros de la plantilla ───
            $whatsappData = [
                "1" => $nombreCliente,
                "2" => $tipoDocumento,
                "3" => $numeroDocumento,
                "4" => $valorDocumento,
                "5" => $fechaDocumento,
                "6" => $estadoDocumento,
                "7" => $nombreEmpresa,
                "8" => $linkFactura,
            ];

            // ─── Teléfono ───
            $telefono = preg_replace('/\D/', '', (string) $request->get('telefono'));

            if (strlen($telefono) < 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'El número debe tener al menos 10 dígitos.',
                ], 422);
            }

            if (strlen($telefono) === 10) {
                $telefono = '57' . $telefono;
            }

            // ─── Enviar WhatsApp por ECO ───
            $sender = new SendEcoWhatsApp(
                $telefono,
                $whatsappData,
                [
                    'id_nit'           => $venta->cliente->id_nit,
                    'nombre_completo'  => $nombreCliente,
                    'numero_documento' => $venta->cliente->numero_documento,
                ],
                'general_documento_portafolioerp',
                'general_documento_portafolioerp'
            );

            $sender->setToken($ecoToken);
            $response = $sender->send();

            \Log::info('WhatsApp factura enviada', [
                'id_venta' => $venta->id,
                'telefono' => $telefono,
                'link'     => $linkFactura,
                'response' => $response,
            ]);

            if (!$response || !isset($response->status)) {
                throw new \Exception('Sin respuesta de ECO.');
            }

            if ($response->status >= 400) {
                $msg = $response->response->message ?? 'Error al enviar WhatsApp por ECO';

                return response()->json([
                    'success' => false,
                    'message' => $msg,
                    'eco'     => $response->response,
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Factura enviada por WhatsApp',
                'eco'     => $response->response,
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('Error enviando WhatsApp factura', [
                'id_venta' => $request->id_venta ?? null,
                'error'    => $e->getMessage(),
                'line'     => $e->getLine(),
                'file'     => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ============================================================
    // DELETE (intacto)
    // ============================================================

    public function delete(Request $request)
    {
        $rules = ['id_venta' => 'required|exists:sam.fac_ventas,id'];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'data'    => [],
                "message" => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::connection('sam')->beginTransaction();

            $venta = FacVentas::with('bodegas')->where("id", $request->get("id_venta"))->first();

            foreach ($venta->bodegas as $bodega) {
                if ($bodega->cantidad) {
                    \App\Models\Sistema\FacProductosBodegas::where('id_producto', $bodega->id_producto)
                        ->increment('cantidad', $bodega->cantidad);
                }
            }

            $venta->documentos()->delete();
            $venta->detalles()->delete();
            $venta->bodegas()->delete();
            $venta->pagos()->delete();
            $venta->delete();

            DB::connection('sam')->commit();

            return response()->json([
                'success' => true,
                'data'    => [],
                'message' => 'Venta eliminada con exito!'
            ], 200);

        } catch (\Throwable $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success" => false,
                'data'    => [],
                "message" => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    // ============================================================
    // HELPERS PRIVADOS — solo los que siguen usándose
    // ============================================================

    private function buildVentasQuery(Request $request)
    {
        return FacVentas::query()
            ->select([
                'id', 'id_cliente', 'id_bodega', 'id_resolucion', 'id_vendedor',
                'id_factura', 'documento_referencia', 'fecha_manual',
                'consecutivo',
                'subtotal', 'total_iva', 'total_descuento', 'total_rete_fuente',
                'total_factura', 'fe_codigo_identificador',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ])
            ->addSelect([
                'total_productos_cantidad' => FacVentaDetalles::query()
                    ->selectRaw('COALESCE(SUM(cantidad), 0)')
                    ->whereColumn('fac_venta_detalles.id_venta', 'fac_ventas.id'),
            ])
            ->where('fecha_manual', '>=', $request->get('fecha_desde'))
            ->where('fecha_manual', '<=', $request->get('fecha_hasta'))
            ->when($request->filled('id_cliente'),    fn ($q) => $q->where('id_cliente', $request->get('id_cliente')))
            ->when($request->filled('factura'),       fn ($q) => $q->where('documento_referencia', 'LIKE', '%' . $request->get('factura') . '%'))
            ->when($request->filled('id_resolucion'), fn ($q) => $q->where('id_resolucion', $request->get('id_resolucion')))
            ->when($request->filled('id_bodega'),     fn ($q) => $q->where('id_bodega', $request->get('id_bodega')))
            ->when($request->filled('id_usuario'),    fn ($q) => $q->where('created_by', $request->get('id_usuario')))
            ->when($request->filled('id_producto'),   fn ($q) => $q->whereHas('detalles',
                fn ($d) => $d->where('id_producto', $request->get('id_producto'))))
            ->when($request->filled('id_forma_pago'), fn ($q) => $q->whereHas('pagos',
                fn ($p) => $p->where('id_forma_pago', $request->get('id_forma_pago'))));
    }

    private function getTotalesVentas(Request $request): array
    {
        $apply = function ($q) use ($request) {
            return $q
                ->when($request->filled('id_cliente'),    fn ($q) => $q->where('FV.id_cliente', $request->get('id_cliente')))
                ->when($request->filled('id_resolucion'), fn ($q) => $q->where('FV.id_resolucion', $request->get('id_resolucion')))
                ->when($request->filled('id_bodega'),     fn ($q) => $q->where('FV.id_bodega', $request->get('id_bodega')))
                ->when($request->filled('factura'),       fn ($q) => $q->where('FV.documento_referencia', 'LIKE', '%' . $request->get('factura') . '%'))
                ->when($request->filled('id_usuario'),    fn ($q) => $q->where('FV.created_by', $request->get('id_usuario')))
                ->when($request->filled('id_producto'),   fn ($q) => $q->whereIn('FV.id', function ($sub) use ($request) {
                    $sub->select('id_venta')->from('fac_venta_detalles')
                        ->where('id_producto', $request->get('id_producto'));
                }))
                ->when($request->filled('id_forma_pago'), fn ($q) => $q->whereIn('FV.id', function ($sub) use ($request) {
                    $sub->select('id_venta')->from('fac_venta_pagos')
                        ->where('id_forma_pago', $request->get('id_forma_pago'));
                }))
                ->where('FV.fecha_manual', '>=', $request->get('fecha_desde'))
                ->where('FV.fecha_manual', '<=', $request->get('fecha_hasta'));
        };

        $general = $apply(DB::connection('sam')->table('fac_ventas as FV'))
            ->whereNull('FV.id_factura')
            ->where('FV.codigo_tipo_documento_dian', CodigoDocumentoDianTypes::VENTA_NACIONAL)
            ->selectRaw('
                COALESCE(SUM(FV.total_factura), 0)     AS total_venta,
                COALESCE(SUM(FV.total_rete_fuente), 0) AS total_rete
            ')->first();

        $detalle = $apply(DB::connection('sam')->table('fac_ventas as FV'))
            ->join('fac_venta_detalles as FVD', 'FV.id', '=', 'FVD.id_venta')
            ->leftJoin('fac_productos as FP', 'FVD.id_producto', '=', 'FP.id')
            ->whereNull('FV.id_factura')
            ->where('FV.codigo_tipo_documento_dian', CodigoDocumentoDianTypes::VENTA_NACIONAL)
            ->selectRaw('
                COALESCE(SUM(FVD.cantidad), 0)                     AS total_productos_cantidad,
                COALESCE(SUM(FP.precio_inicial * FVD.cantidad), 0) AS total_costo
            ')->first();

        $totalesGeneral = [
            'total_productos_cantidad' => (float) $detalle->total_productos_cantidad,
            'total_costo'              => (float) $detalle->total_costo,
            'total_venta'              => (float) $general->total_venta,
        ];

        $notas = $apply(DB::connection('sam')->table('fac_ventas as FV'))
            ->join('fac_venta_detalles as FVD', 'FV.id', '=', 'FVD.id_venta')
            ->leftJoin('fac_productos as FP', 'FVD.id_producto', '=', 'FP.id')
            ->whereNotNull('FV.id_factura')
            ->selectRaw('
                COALESCE(SUM(FVD.cantidad), 0)                     AS total_productos_cantidad,
                COALESCE(SUM(FP.precio_inicial * FVD.cantidad), 0) AS total_costo,
                COALESCE(SUM(FVD.total), 0)                        AS total_venta
            ')->first();

        $totalesNotas = [
            'total_productos_cantidad' => (float) $notas->total_productos_cantidad,
            'total_costo'              => (float) $notas->total_costo,
            'total_venta'              => (float) $notas->total_venta,
        ];

        return [$totalesGeneral, $totalesNotas];
    }

    private function generarVentaDetallesOptimizado($dataVentas, bool $detallar): void
    {

        foreach ($dataVentas as $value) {
            $cantidadTotal = $detallar
                ? $value->detalles->sum('cantidad')
                : ($value->total_productos_cantidad ?? 0);

            $resolucionFe = ($value->resolucion
                && $value->resolucion->tipo_resolucion == FacResoluciones::TIPO_FACTURA_ELECTRONICA)
                ? $value->resolucion
                : null;

            $this->ventaData[] = [
                'id'                      => $value->id,
                'descripcion'             => '',
                'cantidad'                => $cantidadTotal,
                'costo'                   => '',
                'valor'                   => '',
                'nombre_bodega'           => $value->bodega ? "{$value->bodega->codigo} - {$value->bodega->nombre}" : '',
                'nombre_completo'         => $value->cliente?->nombre_completo ?? $value->id_cliente,
                'email_cliente'           => $value->cliente?->email,
                'documento_referencia'    => $value->documento_referencia,
                'fecha_manual'            => $value->fecha_manual,
                'subtotal'                => $value->subtotal,
                'iva_porcentaje'          => '',
                'nombre_vendedor'         => $value->vendedor?->nit?->nombre_completo ?? '',
                'total_iva'               => $value->total_iva,
                'descuento_porcentaje'    => '',
                'total_descuento'         => $value->total_descuento,
                'rete_fuente_porcentaje'  => '',
                'total_rete_fuente'       => $value->total_rete_fuente,
                'total_factura'           => $value->total_factura,
                'anulado'                 => null,
                'fecha_creacion'          => $value->created_at?->format('Y-m-d H:i:s'),
                'fecha_edicion'           => $value->updated_at?->format('Y-m-d H:i:s'),
                'telefono_1'              => $value->cliente->telefono_1,
                'telefono_2'              => $value->cliente->telefono_2,
                'created_by'              => $value->created_by,
                'updated_by'              => $value->updated_by,
                'detalle'                 => false,
                'resolucion'              => $resolucionFe,
                'factura'                 => $value->factura ?? null,
                'fe_codigo_identificador' => $value->fe_codigo_identificador,
            ];

            if ($detallar) {
                foreach ($value->detalles as $d) {
                    $this->ventaData[] = [
                        'id'                      => '',
                        'descripcion'             => $d->producto
                            ? "{$d->producto->codigo} - {$d->producto->nombre}"
                            : 'SIN PRODUCTO RELACIONADO',
                        'cantidad'                => $d->cantidad,
                        'valor'                   => $d->costo,
                        'costo'                   => $d->producto?->precio_inicial ?? '0',
                        'subtotal'                => $d->subtotal,
                        'nombre_bodega'           => '',
                        'nombre_completo'         => '',
                        'documento_referencia'    => '',
                        'fecha_manual'            => '',
                        'iva_porcentaje'          => $d->iva_porcentaje,
                        'nombre_vendedor'         => '',
                        'total_iva'               => $d->iva_valor,
                        'descuento_porcentaje'    => $d->descuento_porcentaje,
                        'total_descuento'         => $d->descuento_valor,
                        'rete_fuente_porcentaje'  => '',
                        'total_rete_fuente'       => '',
                        'total_factura'           => $d->total,
                        'anulado'                 => '',
                        'fecha_creacion'          => '',
                        'fecha_edicion'           => '',
                        'created_by'              => '',
                        'updated_by'              => '',
                        'detalle'                 => true,
                        'resolucion'              => null,
                        'fe_codigo_identificador' => null,
                    ];
                }
            }
        }
    }
}
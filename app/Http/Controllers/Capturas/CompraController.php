<?php

namespace App\Http\Controllers\Capturas;

use DB;
use DateTimeImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//HELPERS
use App\Helpers\Documento;
use App\Helpers\Printers\ComprasPdf;
use App\Helpers\FacturaElectronica\DocumentoSoporteSender;
use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;
//TRAITS
use App\Http\Controllers\Traits\BegConsecutiveTrait;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;
use App\Http\Controllers\Traits\BegFacturacionElectronica;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\FacCompras;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacFormasPago;
use App\Models\Sistema\FacCompraPagos;
use App\Models\Sistema\FacResoluciones;
use App\Models\Sistema\PlanCuentasTipo;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Empresas\UsuarioPermisos;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\FacCompraDetalles;
use App\Models\Sistema\FacProductosBodegas;
use App\Models\Sistema\FacProductosBodegasMovimiento;

class CompraController extends Controller
{
    use BegConsecutiveTrait;
    use BegDocumentHelpersTrait;
    use BegFacturacionElectronica;

    protected $tipoRetencion = 'cuenta_compra_retencion';
    protected $nit = null;
    protected $bodega = null;
	protected $messages = null;
    protected $compraData = [];
    protected $retenciones = [];
    protected $totalesPagos = [
        'total_efectivo' => 0,
        'total_otrospagos' => 0,
    ];
    protected $totalesFactura = [
        'tope_retencion' => 0,
        'porcentaje_rete_fuente' => 0,
        'id_cuenta_rete_fuente' => null,
        'subtotal' => 0,
        'total_iva' => 0,
        'total_rete_fuente' => 0,
        'total_descuento' => 0,
        'total_factura' => 0,
    ];
    protected $cuentasContables = [
        "cuenta_compra" => ["valor" => "subtotal"],
		"cuenta_compra_descuento" => ["valor" => "descuento_valor"],
        "cuenta_compra_iva" => ["valor" => "iva_valor"],
    ];

    public function __construct(Request $request)
	{
		$this->messages = [
            'required' => 'El campo :attribute es requerido.',
            'exists' => 'El :attribute es inválido.',
            'numeric' => 'El campo :attribute debe ser un valor numérico.',
            'string' => 'El campo :attribute debe ser texto',
            'array' => 'El campo :attribute debe ser un arreglo.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
        ];
	}

    public function index ()
    {
        $usuarioPermisos = UsuarioPermisos::where('id_user', request()->user()->id)
            ->where('id_empresa', request()->user()->id_empresa)
            ->first();

        $valorUVT = VariablesEntorno::where('nombre', 'valor_uvt')->first();
        $ivaIncluido = VariablesEntorno::where('nombre', 'iva_incluido')->first();

        $bodegas = explode(",", $usuarioPermisos->ids_bodegas_responsable);

        $data = [
            'cliente' => Nits::with('vendedor.nit')->where('numero_documento', 'LIKE', '22222222%')->first(),
            'bodegas' => FacBodegas::whereIn('id', $bodegas)->get(),
            'comprobante' => Comprobantes::where('tipo_comprobante', 2)->first(),
            'iva_incluido' => false,
            'valor_uvt' => $valorUVT ? $valorUVT->valor : 0
        ];
        
        return view('pages.capturas.compra.compra-view', $data);
    }

    public function indexInforme ()
    {
        $data = [
        ];
        return view('pages.contabilidad.compras.compras-view', $data);
    }

    public function create (Request $request)
    {
        $modo = $request->input('modo', 'compra');

        $rules = [
            'id_proveedor' => 'required|exists:sam.nits,id',
            'id_bodega' => 'required|exists:sam.fac_bodegas,id',
            'fecha_manual' => 'required|date',
            'documento_referencia' => 'required|string',
            'productos' => 'array|required',
            'productos.*.id_producto' => [
                'required',
                'exists:sam.fac_productos,id',
                function ($attribute, $value, $fail) {
                    $producto = FacProductos::whereId($value)->with('familia')->first();
                    if (!$producto->id_familia) {
                        $fail("El producto (" . $producto->codigo . " - " . $producto->nombre . ") no tiene familia configurada");
                    } else if (!$producto->familia->id_cuenta_compra) {
                        $fail("La familia (" . $producto->familia->codigo . " - " . $producto->familia->nombre . ") no tiene cuenta compra configurada");
                    }
                }
            ],
            'productos.*.cantidad' => 'required|numeric|gt:0',
            'productos.*.costo' => 'required|min:0',
            'productos.*.descuento_porcentaje' => 'required|min:0|max:99',
            'productos.*.descuento_valor' => 'required|min:0',
            'productos.*.iva_porcentaje' => 'required|min:0|max:99',
            'productos.*.iva_valor' => 'required|min:0',
            'productos.*.total' => 'required|min:0',
            'pagos' => 'array|required',
            'pagos.*.id' => 'required|exists:sam.fac_formas_pagos,id',
            'pagos.*.valor' => 'required|numeric|min:1',
        ];

        // Reglas condicionales según el modo
        if ($modo === 'documento_soporte') {
            $rules['modo'] = 'required|in:compra,documento_soporte';
            $rules['id_resolucion'] = 'required|exists:sam.fac_resoluciones,id';
            $rules['id_comprobante'] = 'nullable';
        } else {
            $rules['modo'] = 'nullable|in:compra,documento_soporte';
            $rules['id_comprobante'] = 'required|exists:sam.comprobantes,id';
            $rules['id_resolucion'] = 'nullable';
        }

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($modo === 'documento_soporte') {
            $this->resolucion = FacResoluciones::whereId($request->get('id_resolucion'))
                ->with('comprobante')
                ->first();
        }
        
        $resolucionCompras = null;
        $comprobanteCompras = null;

        if ($modo === 'documento_soporte') {

            $resolucionCompras = FacResoluciones::whereId($request->get('id_resolucion'))
                ->with('comprobante')
                ->first();

            if (!$resolucionCompras->isValid) {
                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => ["Resolución" => ["La resolución {$resolucionCompras->nombre_completo} está agotada"]]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (!$resolucionCompras->comprobante) {
                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => ["Comprobante" => ["La resolución {$resolucionCompras->nombre_completo} no tiene un comprobante asociado o está agotada."]]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $comprobanteCompras = $resolucionCompras->comprobante;

            $consecutivo = $this->getNextConsecutive($comprobanteCompras->id, $request->get('fecha_manual'));
            $request->request->add([
                'consecutivo' => $consecutivo,
                'id_comprobante' => $comprobanteCompras->id,
                'id_resolucion' => $resolucionCompras->id
            ]);

        } else {
            $comprobanteCompras = Comprobantes::where('id', $request->get('id_comprobante'))->first();

            if(!$comprobanteCompras) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=> ['Comprobante compras' => ['El Comprobante de compras es incorrecto!']]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $consecutivo = $this->getNextConsecutive($request->get('id_comprobante'), $request->get('fecha_manual'));
            $request->request->add([
                'consecutivo' => $consecutivo,
                'id_resolucion' => null
            ]);
        }

        $existeDocumento = DocumentosGeneral::where('documento_referencia', $request->get('documento_referencia'))
            ->where('id_comprobante', $request->get('id_comprobante'));

        if ($existeDocumento->count()) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> ['Documento referencia' => ["El Documento referencia {$request->get('documento_referencia')} ya existe!"]]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $hoy = new DateTimeImmutable('today');
        $fechaManual = DateTimeImmutable::createFromFormat('Y-m-d', $request->get('fecha_manual'));

        // Verificar si se encuentra en una fecha cerrada
        $isFechaCierreLimit = $this->isFechaCierreLimit($request->get('fecha_manual'));
        if ($isFechaCierreLimit) {
			return response()->json([
                "success" => false,
                'data' => [],
                "message" => ['fecha_manual' => ['mensaje' => 'No permite digitar documentos con fecha inferior al '. $request->get('fecha_manual')]]
            ], 200);
		}

        if ($fechaManual > $hoy) {
            return response()->json([
                "success" => false,
                "data" => [],
                "message" => [
                    'fecha_manual' => ['mensaje' => 'La fecha no puede ser mayor al día de hoy']
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {

            DB::connection('sam')->beginTransaction();

            $this->proveedor = $this->findProveedor($request->get('id_proveedor'));
            $responsabilidades = $this->getResponsabilidades($this->proveedor->id_responsabilidades);

            if (!in_array('5', $responsabilidades)) {
                $this->tipoRetencion = 'cuenta_compra_retencion_nodeclarante';
            }

            $compra = $this->createFacturaCompra($request);
            $enviarFacturaElectronica = false;
            
            //GUARDAR DETALLE & MOVIMIENTO CONTABLE COMPRAS
            $documentoGeneral = new Documento(
                $request->get('id_comprobante'),
                $compra,
                $request->get('fecha_manual'),
                $request->get('consecutivo'),
                false,
                true
            );
            
            //AGREGAR DETALLE DE PRODUCTOS
            foreach ($request->get('productos') as $producto) {
                $producto = (object)$producto;
                $productoDb = $this->findProducto($producto->id_producto);

                $subTotal = (float)$producto->costo * $producto->cantidad;
                
                //CREAR COMPRA DETALLE
                FacCompraDetalles::create([
                    'id_compra' => $compra->id,
                    'id_cuenta_compra' => $productoDb->familia->id_cuenta_compra,
                    'id_cuenta_compra_retencion' => $this->totalesFactura['id_cuenta_rete_fuente'],
                    'id_cuenta_compra_iva' => $productoDb->familia->id_cuenta_compra_iva,
                    'id_cuenta_compra_descuento' => $productoDb->familia->id_cuenta_compra_descuento,
                    'id_producto' => $producto->id_producto,
                    'descripcion' => $productoDb->codigo.' - '.$productoDb->nombre,
                    'cantidad' => $producto->cantidad,
                    'costo' => $producto->costo,
                    'subtotal' => $subTotal,
                    'descuento_porcentaje' => $producto->descuento_porcentaje,
                    'descuento_valor' => $producto->descuento_valor,
                    'iva_porcentaje' => $producto->iva_porcentaje,
                    'iva_valor' => $producto->iva_valor,
                    'total' => $producto->total,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                //PROMEDIAR PRECIO
                $promediarPrecio = VariablesEntorno::where('nombre', 'precio_ponderado')->first();
			    $promediarPrecio = $promediarPrecio ? $promediarPrecio->valor : false;
                if ($promediarPrecio && $productoDb->precio_inicial != $producto->costo) {
                    $existenciasBodega = FacProductosBodegas::where('id_producto', $producto->id_producto)
                        ->where('id_bodega', $this->bodega->id)
                        ->sum('cantidad');
                    if (intval($existenciasBodega) > 0) {
                        $costoAnterior = $productoDb->precio_inicial * $existenciasBodega;
                        $costoNuevo = $producto->costo * $producto->cantidad;
                        $costoPromedio = ($costoAnterior + $costoNuevo) / (intval($existenciasBodega) + intval($producto->cantidad));
                        $productoDb->precio_inicial = round($costoPromedio);
                    } else {
                        $productoDb->precio_inicial = $producto->costo;
                    }
                    if ($productoDb->porcentaje_utilidad != 100 && $productoDb->porcentaje_utilidad > 0) {
                        $productoDb->precio = ($producto->costo * ($productoDb->porcentaje_utilidad / 100)) + $producto->costo;
                    }
                    $productoDb->save();
                }

                //AGREGAR MOVIMIENTO CONTABLE
                foreach ($this->cuentasContables as $cuentaKey => $cuenta) {
                    $cuentaRecord = $productoDb->familia->{$cuentaKey};
                    
                    if ($cuentaRecord) {
                        $keyTotalItem = $cuenta["valor"];
                        $doc = new DocumentosGeneral([
                            "id_cuenta" => $cuentaRecord->id,
                            "id_nit" => $cuentaRecord->exige_nit ? $compra->id_proveedor : null,
                            "id_centro_costos" => $cuentaRecord->exige_centro_costos ? $compra->id_centro_costos : null,
                            "concepto" => 'COMPRA: '.$cuentaRecord->exige_concepto ? $this->proveedor->nombre_nit.' - '.$compra->documento_referencia : null,
                            "documento_referencia" => $cuentaRecord->exige_documento_referencia ? $compra->documento_referencia : null,
                            "debito" => $cuentaRecord->naturaleza_compras == PlanCuentas::DEBITO ? $producto->{$keyTotalItem} : 0,
                            "credito" => $cuentaRecord->naturaleza_compras == PlanCuentas::CREDITO ? $producto->{$keyTotalItem} : 0,
                            "created_by" => request()->user()->id,
                            "updated_by" => request()->user()->id
                        ]);
                        $documentoGeneral->addRow($doc, $cuentaRecord->naturaleza_compras);
                    }
                }

                //AGREGAR MOVIMIENTO BODEGA
                $bodegaProducto = FacProductosBodegas::where('id_bodega', $this->bodega->id)
                    ->where('id_producto', $producto->id_producto)
                    ->first();
                
                if (!$bodegaProducto) {
                    $bodegaProducto = FacProductosBodegas::create([
                        'id_producto' => $producto->id_producto,
                        'id_bodega' => $compra->id_bodega,
                        'cantidad' => 0,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);
                }
    
                $movimiento = new FacProductosBodegasMovimiento([
                    'id_producto' => $producto->id_producto,
                    'id_bodega' => $compra->id_bodega,
                    'cantidad_anterior' => $bodegaProducto->cantidad,
                    'cantidad' => $producto->cantidad,
                    'tipo_tranferencia' => 1,
                    'inventario' => $productoDb->familia->inventario ? 1 : 0,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                if ($bodegaProducto && $productoDb->familia->inventario) {
                    $bodegaProducto->updated_by = request()->user()->id;
                    $bodegaProducto->cantidad+= $producto->cantidad;
                    $bodegaProducto->save();
                }
    
                $movimiento->relation()->associate($compra);
                $compra->bodegas()->save($movimiento);
            }
            
            //AGREGAR RETEFUENTE
            if ($this->totalesFactura['total_rete_fuente']) {
                $cuentaRetencion = PlanCuentas::whereId($this->totalesFactura['id_cuenta_rete_fuente'])->first();

                if ($cuentaRetencion->naturaleza_compras == PlanCuentas::DEBITO || $cuentaRetencion->naturaleza_compras == PlanCuentas::CREDITO) {
                    $doc = new DocumentosGeneral([
                        "id_cuenta" => $cuentaRetencion->id,
                        "id_nit" => $cuentaRetencion->exige_nit ? $compra->id_proveedor : null,
                        "id_centro_costos" => $cuentaRetencion->exige_centro_costos ? $compra->id_centro_costos : null,
                        "concepto" => 'TOTAL: '.$cuentaRetencion->exige_concepto ? $this->proveedor->nombre_nit.' - '.$compra->documento_referencia : null,
                        "documento_referencia" => $cuentaRetencion->exige_documento_referencia ? $compra->documento_referencia : null,
                        "debito" => $cuentaRetencion->naturaleza_compras == PlanCuentas::DEBITO ? $this->totalesFactura['total_rete_fuente'] : 0,
                        "credito" => $cuentaRetencion->naturaleza_compras == PlanCuentas::CREDITO ? $this->totalesFactura['total_rete_fuente'] : 0,
                        "created_by" => request()->user()->id,
                        "updated_by" => request()->user()->id
                    ]);
                    $documentoGeneral->addRow($doc, $cuentaRetencion->naturaleza_compras);
                } else {
                    DB::connection('sam')->rollback();
                    return response()->json([
                        "success" => false,
                        'data' => [],
                        "message" => ['Cuenta retención' => ['La cuenta ' . $cuentaRetencion->cuenta . ' - ' . $cuentaRetencion->nombre . ' no tiene naturaleza en compras']]
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

            }

            $saldoPendiente = $this->totalesFactura['total_factura'];

            //AGREGAR FORMAS DE PAGO
            foreach ($request->get('pagos') as $pagoItem) {

                $pagoItem = (object)$pagoItem;
                $formaPago = $this->findFormaPago($pagoItem->id);
                
                $pagoValor = $pagoItem->id == 1 ? $pagoItem->valor - $this->totalesPagos['total_cambio'] : $pagoItem->valor;
                $saldoPendiente-= $pagoValor;

                $documentoReferenciaAnticipos = $this->isAnticiposDocumentoRefe($formaPago, $compra->id_nit);
                //CRUZAR ANTICIPOS
                if (count($documentoReferenciaAnticipos)) {

                    $pagoAnticipos = $pagoItem->valor;

                    foreach ($documentoReferenciaAnticipos as $anticipos) {

                        if (!$pagoAnticipos) {
                            break;
                        }

                        $anticipoUsado = 0;
                        $anticipoDisponible = floatval($anticipos->saldo);

                        if ($anticipoDisponible >= $pagoAnticipos) {
                            $anticipoUsado = $pagoAnticipos;
                        } else {
                            $anticipoUsado = $anticipoDisponible;
                        }

                        $pagoAnticipos -= $anticipoUsado;

                        $doc = $this->addFormaPago(
                            $anticipos->documento_referencia,
                            $formaPago,
                            $pagoItem,
                            $compra,
                            $anticipoUsado,
                            $saldoPendiente
                        );
                        $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_compras);
                    }

                } else {
                    $doc = $this->addFormaPago(
                        $compra->documento_referencia,
                        $formaPago,
                        $pagoItem,
                        $compra,
                        $pagoValor,
                        $saldoPendiente
                    );
                    $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_compras);
                }
            }

            if (!$documentoGeneral->save()) {
				DB::connection('sam')->rollback();
				return response()->json([
					'success'=>	false,
					'data' => [],
					'message'=> $documentoGeneral->getErrors()
				], Response::HTTP_UNPROCESSABLE_ENTITY);
			}

            $this->updateConsecutivo($request->get('id_comprobante'), $request->get('consecutivo'));

            DB::connection('sam')->commit();

            return response()->json([
				'success'=>	true,
				'data' => $documentoGeneral->getRows(),
				'impresion' => $comprobanteCompras->imprimir_en_capturas ? $compra->id : '',
				'message'=> 'Compra creada con exito!'
			], 200);

        } catch (Exception $e) {
			DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function generate(Request $request)
    {
        $draw       = $request->get('draw');
        $start      = (int) $request->get("start", 0);
        $rowperpage = (int) $request->get("length", 20);
        $rowperpage = $rowperpage > 0 ? min($rowperpage, 500) : 20;
        $detallar   = $request->get('detallar_compra') === 'si';

        // 1) Query base
        $baseQuery = $this->buildComprasQuery($request);

        // 2) Conteo real sin hidratar modelos
        $totalRecords = (clone $baseQuery)->count();

        // 3) Relaciones necesarias por página
        $with = [
            'bodega:id,codigo,nombre',
            'proveedor:id,razon_social,primer_nombre,otros_nombres,primer_apellido,segundo_apellido,email',
            'comprobante:id,nombre',
            'usuario:id,username',
        ];

        if ($detallar) {
            $with['detalles'] = function ($q) {
                $q->select(
                    'id', 'id_compra', 'id_producto', 'cantidad', 'costo',
                    'subtotal', 'iva_porcentaje', 'iva_valor',
                    'descuento_porcentaje', 'descuento_valor', 'total',
                    'descripcion'
                )->with('producto:id,codigo,nombre,precio_inicial');
            };
        }

        $dataCompras = (clone $baseQuery)
            ->with($with)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();

        // 4) Totales (4 KPIs)
        $totalesGeneral = $this->getTotalesCompras($request);

        // 5) Construir filas
        $this->compraData = [];
        $this->generarCompraDetallesOptimizado($dataCompras, $detallar);

        return response()->json([
            'success'              => true,
            'draw'                 => $draw,
            'totalesCompra'        => [ $totalesGeneral ],
            'iTotalRecords'        => $totalRecords,
            'iTotalDisplayRecords' => $totalRecords,
            'data'                 => $this->compraData,
            'perPage'              => $rowperpage,
            'message'              => 'Compras cargadas con éxito!',
        ]);
    }

    public function calcularFormasPago($pagos)
    {
        $totalCambio = 0;
        $totalPagos = 0;
        foreach ($pagos as $pago) {
            $pago = (object)$pago;
            $totalPagos+= $pago->valor;
            if ($pago->id == 1) $this->totalesPagos['total_efectivo']+= $pago->valor;
            else $this->totalesPagos['total_otrospagos']+= $pago->valor;
        }
        if ($this->totalesFactura['total_factura'] < $totalPagos) {
            $totalCambio = $totalPagos - $this->totalesFactura['total_factura'];
        }
        
        $this->totalesPagos['total_cambio'] = $totalCambio;
    }

    public function showPdf(Request $request, $id)
    {
        $factura = FacCompras::whereId($id)->first();

        if(!$factura) {
            return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'La factura no existe'
            ]);
        }

        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

        // $data = (new ComprasPdf($empresa, $factura))->buildPdf()->getData();
        // return view('pdf.facturacion.compras', $data);

        $claveUrl = $this->generarClavePDF(
            $empresa->id,
            $factura->comprobante->id,
            $factura->consecutivo,
            $factura->fecha_manual
        );
 
        return (new ComprasPdf($empresa, $factura, $claveUrl))
            ->buildPdf()
            ->showPdf();
    }

    private function isAnticiposDocumentoRefe($formaPago, $idNit)
    {
        $tiposCuenta = $formaPago->cuenta->tipos_cuenta;
        foreach ($tiposCuenta as $tipoCuenta) {
            if ($tipoCuenta->id_tipo_cuenta == PlanCuentasTipo::TIPO_CUENTA_ANTICIPO_PROVEEDORES_XC) {
                $anticipoCuenta = (new Extracto(
                    $idNit,
                    null,
                    null,
                    Carbon::now()->format('Y-m-d H:i:s'),
                    $formaPago->cuenta->id
                ))->anticiposDiscriminados()->get();

                return $anticipoCuenta;
            }
        }

        return [];
    }

    private function generarCompraDetallesOptimizado($dataCompras, bool $detallar): void
    {
        foreach ($dataCompras as $value) {
            $cantidadTotal = $detallar
                ? $value->detalles->sum('cantidad')
                : ($value->total_productos_cantidad ?? 0);

            // Documento soporte: se emite cuando existe resolución asociada o código DIAN DOCUMENTO_SOPORTE
            $esDocumentoSoporte = (
                !empty($value->id_resolucion)
                || $value->codigo_tipo_documento_dian == CodigoDocumentoDianTypes::DOCUMENTO_SOPORTE
            );

            $this->compraData[] = [
                'id'                      => $value->id,
                'descripcion'             => '',
                'cantidad'                => $cantidadTotal,
                'costo'                   => '',
                'nombre_bodega'           => $value->bodega ? "{$value->bodega->codigo} - {$value->bodega->nombre}" : '',
                'nombre_completo'         => $value->proveedor?->nombre_completo ?? '',
                'nombre_usuario'          => $value->usuario?->username ?? '',
                'documento_referencia'    => $value->documento_referencia,
                'fecha_manual'            => $value->fecha_manual,
                'subtotal'                => $value->subtotal,
                'iva_porcentaje'          => '',
                'total_iva'               => $value->total_iva,
                'descuento_porcentaje'    => '',
                'total_descuento'         => $value->total_descuento,
                'rete_fuente_porcentaje'  => '',
                'total_rete_fuente'       => $value->total_rete_fuente,
                'total_factura'           => $value->total_factura,
                'anulado'                 => null, // ⚠️ S2: no existe en fac_compras
                'fecha_creacion'          => $value->created_at?->format('Y-m-d H:i:s'),
                'fecha_edicion'           => $value->updated_at?->format('Y-m-d H:i:s'),
                'created_by'              => $value->created_by,
                'updated_by'              => $value->updated_by,
                'detalle'                 => false,
                'es_documento_soporte'    => $esDocumentoSoporte,
                'fe_codigo_identificador' => $value->fe_codigo_identificador,
                'fe_zip_key'              => $value->fe_zip_key,
            ];

            if ($detallar) {
                foreach ($value->detalles as $d) {
                    $this->compraData[] = [
                        'id'                      => '',
                        'descripcion'             => $d->descripcion
                            ?: ($d->producto
                                ? "{$d->producto->codigo} - {$d->producto->nombre}"
                                : 'SIN PRODUCTO RELACIONADO'),
                        'cantidad'                => $d->cantidad,
                        'valor'                   => $d->costo,   // se conserva por compat
                        'costo'                   => $d->costo,
                        'subtotal'                => $d->subtotal,
                        'nombre_bodega'           => '',
                        'nombre_completo'         => '',
                        'nombre_usuario'          => '',
                        'documento_referencia'    => '',
                        'fecha_manual'            => '',
                        'iva_porcentaje'          => $d->iva_porcentaje,
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
                        'es_documento_soporte'    => false,
                        'fe_codigo_identificador' => null,
                        'fe_zip_key'              => null,
                    ];
                }
            }
        }
    }

    private function getResponsabilidades($id_responsabilidades)
    {
        if ($id_responsabilidades) {
            return explode(",", $id_responsabilidades);
        }
        return [];
    }

    private function addFormaPago($documentoReferencia, $formaPago, $pagoItem, $compra, $valor, $saldo)
    {
        FacCompraPagos::create([
            'id_compra' => $compra->id,
            'id_forma_pago' => $formaPago->id,
            'valor' => $valor,
            'saldo' => $saldo,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);

        $doc = new DocumentosGeneral([
            'id_cuenta' => $formaPago->cuenta->id,
            'id_nit' => $formaPago->cuenta->exige_nit ? $compra->id_proveedor : null,
            'id_centro_costos' => $formaPago->cuenta->exige_centro_costos ? $compra->id_centro_costos : null,
            'concepto' => 'TOTAL: '.$formaPago->cuenta->exige_concepto ? $this->proveedor->nombre_nit.' - '.$compra->documento_referencia : null,
            'documento_referencia' => $formaPago->cuenta->exige_documento_referencia ? $compra->documento_referencia : null,
            'debito' => $valor,
            'credito' => $valor,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);

        return $doc;
    }

    private function createFacturaCompra ($request)
    {
        $this->calcularTotales($request->get('productos'));
        $this->calcularFormasPago($request->get('pagos'));

        $this->bodega = FacBodegas::whereId($request->get('id_bodega'))->first();

        $compra = FacCompras::create([
            'id_proveedor' => $request->get('id_proveedor'),
            'id_comprobante' => $request->get('id_comprobante'),
            'id_resolucion' => $request->get('id_resolucion'),
            'id_bodega' => $request->get('id_bodega'),
            'id_centro_costos' => $this->bodega->id_centro_costos,
            'fecha_manual' => $request->get('fecha_manual'),
            'consecutivo' => $request->get('consecutivo'),
            'documento_referencia' => $request->get('documento_referencia'),
            'subtotal' => $this->totalesFactura['subtotal'],
            'total_iva' => $this->totalesFactura['total_iva'],
            'total_descuento' => $this->totalesFactura['total_descuento'],
            'total_rete_fuente' => $this->totalesFactura['total_rete_fuente'],
            'porcentaje_rete_fuente' => $this->totalesFactura['porcentaje_rete_fuente'],
            'codigo_tipo_documento_dian' => $request->get('id_resolucion') ? CodigoDocumentoDianTypes::DOCUMENTO_SOPORTE : null,
            'total_factura' => $this->totalesFactura['total_factura'],
            'observacion' => '',
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id,
        ]);

        return $compra;
    }

    private function calcularTotales($productos)
    {
        // Una sola carga para todos los productos (antes: 1 query por línea)
        $productosDb = $this->cargarProductosDb($productos);

        // PASO 1: elegir la retención de mayor porcentaje
        $this->seleccionarRetencionAplicableProductos($productos, $productosDb);
        
        // PASO 2: calcular y acumular por producto
        foreach ($productos as $producto) {
            $this->acumularTotalesProducto($this->calcularProducto((object)$producto));
        }

        // PASO 3: aplicar retención final
        $this->aplicarRetencionProductos();
    }

    private function cargarProductosDb($productos)
    {
        $ids = [];
        foreach ($productos as $p) {
            $ids[] = ((object)$p)->id_producto;
        }

        return FacProductos::with([
            'familia.cuenta_compra',
            'familia.cuenta_compra_retencion.impuesto',
            'familia.cuenta_compra_retencion_nodeclarante.impuesto',
            'familia.cuenta_compra_iva.impuesto',
            'familia.cuenta_compra_descuento',
        ])->whereIn('id', array_unique($ids))->get()->keyBy('id');
    }

    private function seleccionarRetencionAplicableProductos($productos, $productosDb): void
    {
        foreach ($productos as $producto) {
            $productoDb = $productosDb[((object)$producto)->id_producto] ?? null;
            if (!$productoDb) continue;

            $cuentaRetencion = $productoDb->familia?->{$this->tipoRetencion};
            if (!$cuentaRetencion || !$cuentaRetencion->impuesto) continue;

            $impuesto = $cuentaRetencion->impuesto;
            if (floatval($impuesto->porcentaje) > $this->totalesFactura['porcentaje_rete_fuente']) {
                $this->totalesFactura['porcentaje_rete_fuente'] = floatval($impuesto->porcentaje);
                $this->totalesFactura['tope_retencion']         = floatval($impuesto->base);
                $this->totalesFactura['id_cuenta_rete_fuente']  = $cuentaRetencion->id;
            }
        }
    }

    private function calcularProducto(object $producto): object
    {
        $subtotal = $producto->cantidad * $producto->costo;

        return (object)[
            'subtotal'    => $subtotal,
            'iva'         => $producto->iva_valor,
            'descuento'   => $producto->descuento_valor,
            'total_linea' => $subtotal - $producto->descuento_valor + $producto->iva_valor,
        ];
    }

    private function acumularTotalesProducto(object $calculo): void
    {
        $this->totalesFactura['subtotal']        += $calculo->subtotal;
        $this->totalesFactura['total_iva']       += $calculo->iva;
        $this->totalesFactura['total_descuento'] += $calculo->descuento;
        $this->totalesFactura['total_factura']   += $calculo->total_linea;
    }

    private function aplicarRetencionProductos(): void
    {
        $aplica = $this->totalesFactura['total_factura'] > $this->totalesFactura['tope_retencion']
            && $this->totalesFactura['porcentaje_rete_fuente'] > 0;

        if ($aplica) {
            $rete = ($this->totalesFactura['subtotal'] * $this->totalesFactura['porcentaje_rete_fuente']) / 100;
            $this->totalesFactura['total_rete_fuente'] = $rete;
            $this->totalesFactura['total_factura']    -= $rete;
        } else {
            $this->totalesFactura['id_cuenta_rete_fuente'] = null;
        }
    }

    private function findProveedor ($id_proveedor)
    {
        return Nits::whereId($id_proveedor)
            ->select(
                '*',
                DB::raw("CASE
                    WHEN id IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)
                    ELSE NULL
                END AS nombre_nit")
            )
            ->first();
    }

    private function findProducto ($id_producto)
    {
        $producto = FacProductos::where('id', $id_producto)
            ->with(
                'familia.cuenta_compra',
                'familia.cuenta_compra_retencion.impuesto',
                'familia.cuenta_compra_iva.impuesto',
                'familia.cuenta_compra_descuento'
            )
            ->first();

        if ($producto->utilizado_captura == 0) {
            $producto->utilizado_captura = 1;
            $producto->save();
        }

        return $producto;
    }

    private function findFormaPago ($id_forma_pago)
    {
        return FacFormasPago::where('id', $id_forma_pago)
            ->with(
                'cuenta'
            )
            ->first();
    }

    private function buildComprasQuery(Request $request)
    {
        return FacCompras::query()
            ->select([
                'id', 'id_proveedor', 'id_bodega', 'id_comprobante', 'id_resolucion',
                'documento_referencia', 'fecha_manual', 'consecutivo',
                'subtotal', 'total_iva', 'total_descuento', 'total_rete_fuente',
                'total_factura',
                'codigo_tipo_documento_dian',
                'fe_codigo_identificador', 'fe_zip_key', 'fe_xml_file',
                'created_by', 'updated_by', 'created_at', 'updated_at',
            ])
            ->addSelect([
                'total_productos_cantidad' => FacCompraDetalles::query()
                    ->selectRaw('COALESCE(SUM(cantidad), 0)')
                    ->whereColumn('fac_compra_detalles.id_compra', 'fac_compras.id'),
            ])
            ->where('fecha_manual', '>=', $request->get('fecha_desde'))
            ->where('fecha_manual', '<=', $request->get('fecha_hasta'))
            ->when($request->filled('id_proveedor'), fn ($q) => $q->where('id_proveedor', $request->get('id_proveedor')))
            ->when($request->filled('factura'),      fn ($q) => $q->where('documento_referencia', 'LIKE', '%' . $request->get('factura') . '%'))
            ->when($request->filled('consecutivo'),  fn ($q) => $q->where('consecutivo', $request->get('consecutivo')))
            ->when($request->filled('id_comprobante'),fn ($q) => $q->where('id_comprobante', $request->get('id_comprobante')))
            ->when($request->filled('id_bodega'),    fn ($q) => $q->where('id_bodega', $request->get('id_bodega')))
            ->when($request->filled('id_usuario'),   fn ($q) => $q->where('created_by', $request->get('id_usuario')))
            ->when($request->filled('id_producto'),  fn ($q) => $q->whereHas('detalles',
                fn ($d) => $d->where('id_producto', $request->get('id_producto'))))
            ->when($request->filled('id_forma_pago'), fn ($q) => $q->whereHas('pagos',
                fn ($p) => $p->where('id_forma_pago', $request->get('id_forma_pago'))));
    }

    private function getTotalesCompras(Request $request): array
    {
        $apply = function ($q) use ($request) {
            return $q
                ->when($request->filled('id_proveedor'),  fn ($q) => $q->where('FC.id_proveedor', $request->get('id_proveedor')))
                ->when($request->filled('id_comprobante'),fn ($q) => $q->where('FC.id_comprobante', $request->get('id_comprobante')))
                ->when($request->filled('id_bodega'),     fn ($q) => $q->where('FC.id_bodega', $request->get('id_bodega')))
                ->when($request->filled('factura'),       fn ($q) => $q->where('FC.documento_referencia', 'LIKE', '%' . $request->get('factura') . '%'))
                ->when($request->filled('consecutivo'),   fn ($q) => $q->where('FC.consecutivo', $request->get('consecutivo')))
                ->when($request->filled('id_usuario'),    fn ($q) => $q->where('FC.created_by', $request->get('id_usuario')))
                ->when($request->filled('id_producto'),   fn ($q) => $q->whereIn('FC.id', function ($sub) use ($request) {
                    $sub->select('id_compra')->from('fac_compra_detalles')
                        ->where('id_producto', $request->get('id_producto'));
                }))
                ->when($request->filled('id_forma_pago'), fn ($q) => $q->whereIn('FC.id', function ($sub) use ($request) {
                    $sub->select('id_compra')->from('fac_compra_pagos')
                        ->where('id_forma_pago', $request->get('id_forma_pago'));
                }))
                ->where('FC.fecha_manual', '>=', $request->get('fecha_desde'))
                ->where('FC.fecha_manual', '<=', $request->get('fecha_hasta'));
        };

        // Totales de cabecera (total_factura)
        $general = $apply(DB::connection('sam')->table('fac_compras as FC'))
            ->selectRaw('COALESCE(SUM(FC.total_factura), 0) AS total_venta')
            ->first();

        // Totales de detalle (cantidad + costo + iva)
        $detalle = $apply(DB::connection('sam')->table('fac_compras as FC'))
            ->join('fac_compra_detalles as FCD', 'FC.id', '=', 'FCD.id_compra')
            ->selectRaw('
                COALESCE(SUM(FCD.cantidad), 0)     AS total_productos_cantidad,
                COALESCE(SUM(FCD.subtotal), 0)     AS total_costo,
                COALESCE(SUM(FCD.iva_valor), 0)    AS total_iva
            ')->first();

        return [
            'total_productos_cantidad' => (float) $detalle->total_productos_cantidad,
            'total_costo'              => (float) $detalle->total_costo,
            'total_iva'                => (float) $detalle->total_iva,
            'total_venta'              => (float) $general->total_venta,
        ];
    }

    public function facturacionElectronica(Request $request)
    {
        $rules = [
            'id_compra' => 'required|exists:sam.fac_compras,id',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data'    => [],
                'message' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $compra  = FacCompras::with(['proveedor', 'detalles', 'pagos', 'resolucion'])->find($request->id_compra);
        $empresa = Empresa::where('id', $request->user()->id_empresa)->first();

        if (!$compra) {
            return response()->json([
                'success' => false,
                'data'    => [],
                'message' => ['compra' => ['La compra no existe.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$compra->id_resolucion) {
            return response()->json([
                'success' => false,
                'data'    => [],
                'message' => ['documento_soporte' => ['Esta compra no fue creada como documento soporte.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Si ya fue emitido, sólo permitir consultar estado si hay zip_key (reenvío = volver a enviar)
        $esReenvio = (bool) $request->boolean('reenviar');

        try {
            DB::connection('sam')->beginTransaction();

            // 1) Si existe zip_key pendiente, consultar estado primero
            if ($compra->fe_zip_key) {
                $consulta = (new DocumentoSoporteSender($compra))
                    ->consultarZip($compra->fe_zip_key);

                if ($consulta['success'] && $consulta['is_valid']) {
                    $compra->fe_codigo_identificador = $consulta['cufe'];
                    $compra->fe_zip_key = null;
                    $compra->save();

                    DB::connection('sam')->commit();

                    return response()->json([
                        'success' => true,
                        'data'    => [],
                        'message' => 'Documento soporte validado correctamente.',
                    ], 200);
                }

                // Si no está válido, sigue al paso de envío (o devuelve el error si prefieres)
                if (!$consulta['success']) {
                    \Log::warning('[compras-fe] consultarZip falló', $consulta);
                }
            }

            // 2) Enviar (o reenviar)
            $sender = (new DocumentoSoporteSender($compra))->send();

            if (($sender['status'] ?? 0) >= 400) {
                if (!empty($sender['zip_key'])) {
                    $compra->fe_zip_key = $sender['zip_key'];
                    $compra->save();
                }
                DB::connection('sam')->commit();
                return response()->json([
                    'success' => false,
                    'data'    => [],
                    'message' => $sender['error_message'] ?? 'Error al enviar documento soporte.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (($sender['status'] ?? 0) === 200) {
                $compra->fe_codigo_identificador = $sender['cufe'] ?? null;
                $compra->fe_zip_key              = $sender['zip_key'] ?? null;
                $compra->fe_xml_file             = $sender['xml_url'] ?? null;
                $compra->save();
            }

            DB::connection('sam')->commit();

            return response()->json([
                'success' => true,
                'data'    => [],
                'message' => 'Documento soporte enviado correctamente.',
            ], 200);

        } catch (\Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                'success' => false,
                'data'    => [],
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

}

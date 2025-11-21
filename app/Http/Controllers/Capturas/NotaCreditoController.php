<?php

namespace App\Http\Controllers\Capturas;

use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Jobs\ProcessConsultarFE;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//HELPERS
use App\Helpers\Documento;
use App\Helpers\Printers\VentasPdf;
use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;
use App\Helpers\FacturaElectronica\NotaCreditoElectronicaSender;
//TRAITS
use App\Http\Controllers\Traits\BegConsecutiveTrait;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;
use App\Http\Controllers\Traits\BegFacturacionElectronica;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Empresas\UsuarioPermisos;
use App\Models\Sistema\Nits;
use App\Models\Sistema\FacVentas;
use App\Models\Sistema\FacFactura;
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacVentaPagos;
use App\Models\Sistema\FacFormasPago;
use App\Models\Sistema\FacResoluciones;
use App\Models\Sistema\FacVentaDetalles;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\FacFacturaDetalle;
use App\Models\Sistema\FacProductosBodegas;
use App\Models\Sistema\FacProductosBodegasMovimiento;

class NotaCreditoController extends Controller
{
    use BegConsecutiveTrait;
    use BegDocumentHelpersTrait;
    use BegFacturacionElectronica;

    protected $bodega = null;
    protected $facturaVentas = null;
    protected $resolucion = null;
	protected $messages = null;
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
        "cuenta_venta_devolucion" => ["valor" => "subtotal", "opuesto" => false],
		"cuenta_venta_descuento" => ["valor" => "descuento_valor", "opuesto" => true],
        "cuenta_venta_devolucion_iva" => ["valor" => "iva_valor", "opuesto" => false],
        "cuenta_inventario" => ["valor" => "subtotal", "opuesto" => true],
        "cuenta_costos" => ["valor" => "subtotal", "opuesto" => true],
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

    public function index (Request $request)
    {
        $usuarioPermisos = UsuarioPermisos::where('id_user', request()->user()->id)
            ->where('id_empresa', request()->user()->id_empresa)
            ->first();

        $ivaIncluido = VariablesEntorno::where('nombre', 'iva_incluido')->first();
        $resolucionesId = explode(",", $usuarioPermisos->ids_resolucion_responsable);
        $resolucionesData = FacResoluciones::whereIn('id', $resolucionesId)
            ->where('tipo_resolucion', FacResoluciones::TIPO_NOTA_CREDITO)
            ->get();

        $data = [
            'resolucion' => $resolucionesData,
            'iva_incluido' => $ivaIncluido ? $ivaIncluido->valor : '',
        ];

        return view('pages.capturas.nota_credito.nota_credito-view', $data);
    }

    // Método principal para crear la nota de crédito
    public function create(Request $request)
    {
        $rules = $this->getValidationRules();
        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->facturaVentas = FacFactura::with('cliente')
            ->where('id', $request->get('id_factura'))
            ->first();
            
        $this->resolucion = FacResoluciones::whereId($request->get('id_resolucion'))
            ->with('comprobante')
            ->first();

        // Validar resolución
        $resolucionValidation = $this->validateResolucion();
        if ($resolucionValidation) {
            return $resolucionValidation;
        }

        $consecutivo = $this->getNextConsecutive($this->resolucion->comprobante->id, $request->get('fecha_manual'));
        
        $request->request->add([
            'id_comprobante' => $this->resolucion->comprobante->id,
            'consecutivo' => $consecutivo
        ]);

        try {
            DB::connection('sam')->beginTransaction();

            $enviarFacturaElectronica = false;
            $empresa = Empresa::where('id', $request->user()->id_empresa)->first();
            
            // Crear nota crédito
            $notaCredito = $this->createNotaCredito($request);

            // Generar documento contable (LÓGICA REUTILIZADA)
            $documentoGeneral = $this->generarMovimientoContable(
                $request,
                $notaCredito,
                true // indica que se debe guardar en BD
            );
            
            if (!$documentoGeneral['success']) {
                DB::connection('sam')->rollback();
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => $documentoGeneral['message']
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $this->updateConsecutivo($request->get('id_comprobante'), $request->get('consecutivo'));
            
            if (!$documentoGeneral['documento']->save()) {
                DB::connection('sam')->rollback();
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => $documentoGeneral['documento']->getErrors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Facturación electrónica
            $feResponse = $this->procesarFacturaElectronica($notaCredito, $empresa, $request);
            if ($feResponse) {
                return $feResponse;
            }
            
            DB::connection('sam')->commit();

            return response()->json([
                'success' => true,
                'data' => $documentoGeneral['documento']->getRows(),
                'impresion' => $this->resolucion->comprobante->imprimir_en_capturas ? $notaCredito->id : '',
                'message' => 'Nota credito creado con exito!'
            ], 200);

        } catch (Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    // Método para consultar el movimiento contable sin guardar
    public function movimientoContable(Request $request)
    {
        if (!$request->get('id_factura')) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Movimiento contable generado con exito!'
            ], Response::HTTP_OK);
        }
        
        $rules = $this->getValidationRulesMovimiento();
        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $this->facturaVentas = FacFactura::find($request->get('id_factura'));

            // Generar documento contable (LÓGICA REUTILIZADA)
            $resultado = $this->generarMovimientoContable(
                $request,
                null,
                false // NO guardar en BD
            );
            
            if (!$resultado['success']) {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => $resultado['message']
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $movimientoGeneral = $resultado['documento']->getRows();
            $totales = $resultado['documento']->getTotals();

            $movimientoGeneral[] = [
                'id_cuenta' => 'TOTALES',
                'id_nit' => null,
                'id_centro_costos' => null,
                'concepto' => $totales->diferencia,
                'documento_referencia' => null,
                'debito' => $totales->debito,
                'credito' => $totales->credito
            ];

            return response()->json([
                'success' => true,
                'data' => $movimientoGeneral,
                'message' => 'Movimiento contable generado con exito!'
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    // ============================================================
    // MÉTODO CENTRAL: Genera el movimiento contable (REUTILIZABLE)
    // ============================================================
    private function generarMovimientoContable(Request $request, $notaCredito = null, $guardarEnBD = false)
    {
        $documentoGeneral = $guardarEnBD && $notaCredito
            ? new Documento(
                $this->resolucion->comprobante->id,
                $notaCredito,
                $request->get('fecha_manual'),
                $request->get('consecutivo'),
                false
            )
            : new Documento();

        $this->calcularTotales($request->get('productos'));

        foreach ($request->get('productos') as $producto) {
            $producto = (object)$producto;
            $clienteId = $guardarEnBD ? $notaCredito->id_cliente : $request->get('id_cliente');
            $nit = $this->findCliente($clienteId);
            $productoDb = $this->findProducto($producto->id_producto);
            $detalleProducto = FacFacturaDetalle::find($producto->id_factura_detalle);
            $totalesProducto = $this->calcularTotalesProducto($producto);
            // dd($guardarEnBD, $notaCredito);
            // Procesar movimiento de bodega (solo si se guarda)
            if (true) {
                $bodegaResult = $this->procesarMovimientoBodega($producto, $productoDb, $notaCredito);
                if ($bodegaResult) {
                    $resultInventario = $this->agregarMovimientoInventario(
                        $documentoGeneral,
                        $productoDb,
                        $producto,
                        $nit,
                        $notaCredito
                    );

                    if (is_array($resultInventario) && isset($resultInventario['error'])) {
                        return ['success' => false, 'message' => $resultInventario['error']];
                    }
                    $documentoGeneral = $resultInventario;
                }
            }

            // Guardar detalle (solo si se guarda)
            if ($guardarEnBD && $notaCredito) {
                $this->crearNotaCreditoDetalle($notaCredito, $productoDb, $detalleProducto, $producto, $totalesProducto);
            }
            
            // Agregar movimientos contables
            $resultMovimientos = $this->agregarMovimientosProducto(
                $documentoGeneral,
                $productoDb,
                $producto,
                $totalesProducto,
                $nit,
                $clienteId,
                $request->get('documento_referencia') ?? ($notaCredito ? $notaCredito->documento_referencia : null)
            );
            
            if (is_array($resultMovimientos) && isset($resultMovimientos['error'])) {
                return ['success' => false, 'message' => $resultMovimientos['error']];
            }
            $documentoGeneral = $resultMovimientos;

            // Validar cuenta IVA
            $validacionIva = $this->validarCuentaIva($productoDb, $totalesProducto);
            if ($validacionIva) {
                return ['success' => false, 'message' => $validacionIva];
            }

            // Agregar retención fuente
            $resultReteFuente = $this->agregarReteFuente(
                $documentoGeneral,
                $nit,
                $clienteId,
                $request->get('documento_referencia') ?? ($notaCredito ? $notaCredito->documento_referencia : null)
            );
            
            if (is_array($resultReteFuente) && isset($resultReteFuente['error'])) {
                return ['success' => false, 'message' => $resultReteFuente['error']];
            }
            $documentoGeneral = $resultReteFuente;

        }

        // Agregar formas de pago
        if ($request->has('pagos') && $request->get('pagos')) {
            $this->calcularFormasPago($request->get('pagos'));
            $resultFormasPago = $this->agregarFormasPago(
                $documentoGeneral,
                $request,
                $nit,
                $clienteId,
                $notaCredito
            );
            
            if (is_array($resultFormasPago) && isset($resultFormasPago['error'])) {
                return ['success' => false, 'message' => $resultFormasPago['error']];
            }
            $documentoGeneral = $resultFormasPago;
        }

        return ['success' => true, 'documento' => $documentoGeneral];
    }

    // ============================================================
    // MÉTODOS AUXILIARES
    // ============================================================

    private function getValidationRules()
    {
        return [
            'id_factura' => 'required|exists:sam.fac_ventas,id',
            'id_resolucion' => 'required|exists:sam.fac_resoluciones,id',
            'fecha_manual' => 'required|date',
            'documento_referencia' => 'required|string',
            'productos' => 'array|required',
            'productos.*.id_factura_detalle' => 'required|exists:sam.fac_venta_detalles,id',
            'productos.*.id_producto' => [
                'required',
                'exists:sam.fac_productos,id',
                function ($attribute, $value, $fail) {
                    $producto = FacProductos::whereId($value)->with('familia')->first();
                    if (!$producto) {
                        $fail("El producto id: $value, No existe");
                    } else if (!$producto->familia->id_cuenta_venta_devolucion) {
                        $fail("La familia ({$producto->familia->codigo} - {$producto->familia->nombre}) no tiene cuenta de venta devolución configurada");
                    }
                }
            ],
            'productos.*.cantidad' => 'required|numeric|min:0',
            'productos.*.total_devolucion' => 'required|numeric|min:1',
            'pagos' => 'array|required',
            'pagos.*.id' => 'required|exists:sam.fac_formas_pagos,id',
            'pagos.*.valor' => 'required|numeric|min:1',
        ];
    }

    private function getValidationRulesMovimiento()
    {
        $rules = $this->getValidationRules();
        $rules['pagos'] = 'array|nullable';
        $rules['pagos.*.id'] = 'nullable|exists:sam.fac_formas_pagos,id';
        $rules['pagos.*.valor'] = 'nullable|numeric|min:1';
        unset($rules['id_resolucion']);
        unset($rules['fecha_manual']);
        unset($rules['documento_referencia']);
        return $rules;
    }

    private function validateResolucion()
    {
        if (!$this->resolucion->isValid) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => ["Resolución" => ["La resolución {$this->resolucion->nombre_completo} está agotada"]]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$this->resolucion->isActive) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => ["Resolución" => ["La resolución {$this->resolucion->nombre_completo} está vencida"]]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return null;
    }

    private function procesarMovimientoBodega($producto, $productoDb, $notaCredito)
    {
        if (!$producto->cantidad || !$productoDb->familia->inventario) {
            return null;
        }

        if (!$this->bodega) {
            return true;
        }

        $bodegaProducto = FacProductosBodegas::where('id_bodega', $this->bodega->id)
            ->where('id_producto', $producto->id_producto)
            ->first();

        if (!$bodegaProducto) {
            $bodegaProducto = FacProductosBodegas::create([
                'id_producto' => $producto->id_producto,
                'id_bodega' => $notaCredito->id_bodega,
                'cantidad' => 0,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);
        }

        $movimiento = new FacProductosBodegasMovimiento([
            'id_producto' => $producto->id_producto,
            'id_bodega' => $notaCredito->id_bodega,
            'cantidad_anterior' => $bodegaProducto->cantidad,
            'cantidad' => $producto->cantidad,
            'tipo_tranferencia' => 1,
            'inventario' => $productoDb->familia->inventario ? 1 : 0,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);

        $bodegaProducto->updated_by = request()->user()->id;
        $bodegaProducto->cantidad += $producto->cantidad;
        $bodegaProducto->save();
        
        $movimiento->relation()->associate($notaCredito);
        $notaCredito->bodegas()->save($movimiento);

        return $bodegaProducto;
    }

    private function agregarMovimientoInventario($documentoGeneral, $productoDb, $producto, $nit, $notaCredito)
    {
        if ($productoDb->precio_inicial && $productoDb->familia->cuenta_costos) {
            $cuentaCosto = $productoDb->familia->cuenta_costos;
            
            // Validar naturaleza_ventas
            if (is_null($cuentaCosto->naturaleza_ventas)) {
                return [
                    'error' => [
                        "Cuenta costos" => [
                            "La cuenta de costos ({$cuentaCosto->cuenta} - {$cuentaCosto->nombre}) " .
                            "no tiene configurada la naturaleza de ventas. " .
                            "Familia: {$productoDb->familia->codigo} - {$productoDb->familia->nombre}"
                        ]
                    ]
                ];
            }

            $docRefCosto = $notaCredito ? $notaCredito->documento_referencia : null;
            $docCosto = new DocumentosGeneral([
                "id_cuenta" => $cuentaCosto->id,
                "id_nit" => $cuentaCosto->exige_nit ? $this->facturaVentas->id_cliente : null,
                "id_centro_costos" => $cuentaCosto->exige_centro_costos ? $this->facturaVentas->id_centro_costos : null,
                "concepto" => $cuentaCosto->exige_concepto ? 'NOTA CREDITO: ' . $nit->nombre_nit . ' - ' . $docRefCosto : null,
                "documento_referencia" => $cuentaCosto->exige_documento_referencia ? $docRefCosto : null,
                "debito" => $productoDb->precio_inicial * $producto->cantidad,
                "credito" => $productoDb->precio_inicial * $producto->cantidad,
                "created_by" => request()->user()->id,
                "updated_by" => request()->user()->id
            ]);
            $documentoGeneral->addRow($docCosto, $cuentaCosto->naturaleza_ventas);
        }

        if ($productoDb->precio_inicial && $productoDb->familia->cuenta_inventario) {
            $cuentaInventario = $productoDb->familia->cuenta_inventario;
            
            // Validar naturaleza_ventas
            if (is_null($cuentaInventario->naturaleza_ventas)) {
                return [
                    'error' => [
                        "Cuenta costos" => [
                            "La cuenta de inventario ({$cuentaInventario->cuenta} - {$cuentaInventario->nombre}) " .
                            "no tiene configurada la naturaleza de ventas. " .
                            "Familia: {$productoDb->familia->codigo} - {$productoDb->familia->nombre}"
                        ]
                    ]
                ];
            }

            $docRefCosto = $notaCredito ? $notaCredito->documento_referencia : null;
            $docCosto = new DocumentosGeneral([
                "id_cuenta" => $cuentaInventario->id,
                "id_nit" => $cuentaInventario->exige_nit ? $this->facturaVentas->id_cliente : null,
                "id_centro_costos" => $cuentaInventario->exige_centro_costos ? $this->facturaVentas->id_centro_costos : null,
                "concepto" => $cuentaInventario->exige_concepto ? 'NOTA CREDITO: ' . $nit->nombre_nit . ' - ' . $docRefCosto : null,
                "documento_referencia" => $cuentaInventario->exige_documento_referencia ? $docRefCosto : null,
                "debito" => $productoDb->precio_inicial * $producto->cantidad,
                "credito" => $productoDb->precio_inicial * $producto->cantidad,
                "created_by" => request()->user()->id,
                "updated_by" => request()->user()->id
            ]);
            $documentoGeneral->addRow($docCosto, $cuentaInventario->naturaleza_ventas);
        }

        return $documentoGeneral;
    }

    private function crearNotaCreditoDetalle($notaCredito, $productoDb, $detalleProducto, $producto, $totalesProducto)
    {
        FacVentaDetalles::create([
            'id_venta' => $notaCredito->id,
            'id_venta_detalle' => $detalleProducto->id,
            'id_producto' => $productoDb->id,
            'id_cuenta_venta' => $productoDb->familia->id_cuenta_venta_devolucion,
            'id_cuenta_venta_retencion' => $productoDb->familia->id_cuenta_venta_retencion,
            'id_cuenta_venta_iva' => $productoDb->familia->id_cuenta_venta_devolucion_iva,
            'id_cuenta_venta_descuento' => $productoDb->familia->id_cuenta_venta_descuento,
            'descripcion' => $productoDb->codigo . ' - ' . $productoDb->nombre,
            'cantidad' => $producto->cantidad,
            'costo' => $detalleProducto->costo,
            'subtotal' => $totalesProducto->subtotal,
            'descuento_porcentaje' => $detalleProducto->descuento_porcentaje,
            'descuento_valor' => $totalesProducto->descuento,
            'iva_porcentaje' => $detalleProducto->iva_porcentaje,
            'iva_valor' => $totalesProducto->iva,
            'total' => $producto->total_devolucion,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);
    }

    private function agregarMovimientosProducto($documentoGeneral, $productoDb, $producto, $totalesProducto, $nit, $clienteId, $docReferencia)
    {
        // Devolución
        $cuentaDevolucion = $productoDb->familia->cuenta_venta_devolucion;
        // Validar naturaleza_ventas
        if (is_null($cuentaDevolucion->naturaleza_ventas)) {
            return [
                'error' => [
                    "Cuenta venta devolución" => [
                        "La cuenta de venta devolución ({$cuentaDevolucion->cuenta} - {$cuentaDevolucion->nombre}) " .
                        "no tiene configurada la naturaleza de ventas. " .
                        "Familia: {$productoDb->familia->codigo} - {$productoDb->familia->nombre}"
                    ]
                ]
            ];
        }
        
        $docDevolucion = new DocumentosGeneral([
            "id_cuenta" => $cuentaDevolucion->id,
            "id_nit" => $cuentaDevolucion->exige_nit ? $this->facturaVentas->id_cliente : null,
            "id_centro_costos" => $cuentaDevolucion->exige_nit ? $this->facturaVentas->id_centro_costos : null,
            "concepto" => $cuentaDevolucion->exige_concepto ? 'NOTA: CREDITO ' . $this->facturaVentas->cliente->nombre_completo . ' - ' . $docReferencia : null,
            "documento_referencia" => $cuentaDevolucion->exige_documento_referencia ? $docReferencia : null,
            "debito" => $totalesProducto->subtotal,
            "credito" => $totalesProducto->subtotal,
            "created_by" => request()->user()->id,
            "updated_by" => request()->user()->id
        ]);
        $documentoGeneral->addRow($docDevolucion, $cuentaDevolucion->naturaleza_ventas);

        // Costo
        if ($totalesProducto->subtotal && $productoDb->familia->cuenta_compra_devolucion) {
            $cuentaCosto = $productoDb->familia->cuenta_compra_devolucion;
            
            // Validar que tenga naturaleza configurada
            if (is_null($cuentaCosto->naturaleza_ventas)) {
                return [
                    'error' => [
                        "Cuenta compra devolución" => [
                            "La cuenta de compra devolución ({$cuentaCosto->cuenta} - {$cuentaCosto->nombre}) " .
                            "no tiene configurada la naturaleza de ventas. " .
                            "Familia: {$productoDb->familia->codigo} - {$productoDb->familia->nombre}"
                        ]
                    ]
                ];
            }
            
            $docCosto = new DocumentosGeneral([
                "id_cuenta" => $cuentaCosto->id,
                "id_nit" => $cuentaCosto->exige_nit ? $this->facturaVentas->id_cliente : null,
                "id_centro_costos" => $cuentaCosto->exige_centro_costos ? $this->facturaVentas->id_centro_costos : null,
                "concepto" => $cuentaCosto->exige_concepto ? 'NOTA: CREDITO ' . $nit->nombre_nit . ' - ' . $docReferencia : null,
                "documento_referencia" => $cuentaCosto->exige_documento_referencia ? $docReferencia : null,
                "debito" => $productoDb->precio_inicial * $producto->cantidad,
                "credito" => $productoDb->precio_inicial * $producto->cantidad,
                "created_by" => request()->user()->id,
                "updated_by" => request()->user()->id
            ]);
            $documentoGeneral->addRow($docCosto, $cuentaCosto->cuenta_compra_devolucion);
        }

        // Descuento
        if ($totalesProducto->descuento && $productoDb->familia->cuenta_venta_descuento) {
            $cuentaDescuento = $productoDb->familia->cuenta_venta_descuento;
            
            // Validar naturaleza_ventas
            if (is_null($cuentaDescuento->naturaleza_ventas)) {
                return [
                    'error' => [
                        "Cuenta venta descuento" => [
                            "La cuenta de venta descuento ({$cuentaDescuento->cuenta} - {$cuentaDescuento->nombre}) " .
                            "no tiene configurada la naturaleza de ventas. " .
                            "Familia: {$productoDb->familia->codigo} - {$productoDb->familia->nombre}"
                        ]
                    ]
                ];
            }
            
            $cuentaOpuestoDescuento = PlanCuentas::CREDITO == $cuentaDescuento->naturaleza_ventas ? PlanCuentas::DEBITO : PlanCuentas::CREDITO;
            $docDescuento = new DocumentosGeneral([
                "id_cuenta" => $cuentaDescuento->id,
                "id_nit" => $cuentaDescuento->exige_nit ? $this->facturaVentas->id_cliente : null,
                "id_centro_costos" => $cuentaDescuento->exige_centro_costos ? $this->facturaVentas->id_centro_costos : null,
                "concepto" => $cuentaDescuento->exige_concepto ? 'NOTA: CREDITO ' . $nit->nombre_nit . ' - ' . $docReferencia : null,
                "documento_referencia" => $cuentaDescuento->exige_documento_referencia ? $docReferencia : null,
                "debito" => $totalesProducto->descuento,
                "credito" => $totalesProducto->descuento,
                "created_by" => request()->user()->id,
                "updated_by" => request()->user()->id
            ]);
            $documentoGeneral->addRow($docDescuento, $cuentaOpuestoDescuento);
        }

        // IVA
        $cuentaIva = $productoDb->familia->cuenta_venta_devolucion_iva;
        if ($totalesProducto->iva && $cuentaIva) {
            
            // Validar que tenga cuenta_venta_devolucion_iva configurada
            if (is_null($cuentaIva->naturaleza_ventas)) {
                return [
                    'error' => [
                        "Cuenta venta devolución IVA" => [
                            "La cuenta de venta devolución IVA ({$cuentaIva->cuenta} - {$cuentaIva->nombre}) " .
                            "no tiene configurada la naturaleza de ventas. " .
                            "Familia: {$productoDb->familia->codigo} - {$productoDb->familia->nombre}"
                        ]
                    ]
                ];
            }
            
            // $cuentaOpuestoIva = PlanCuentas::CREDITO == $cuentaIva->naturaleza_ventas ? PlanCuentas::DEBITO : PlanCuentas::CREDITO;
            $docIva = new DocumentosGeneral([
                "id_cuenta" => $cuentaIva->id,
                "id_nit" => $cuentaIva->exige_nit ? $this->facturaVentas->id_cliente : null,
                "id_centro_costos" => $cuentaIva->exige_centro_costos ? $this->facturaVentas->id_centro_costos : null,
                "concepto" => $cuentaIva->exige_concepto ? 'NOTA: CREDITO ' . $nit->nombre_nit . ' - ' . $docReferencia : null,
                "documento_referencia" => $cuentaIva->exige_documento_referencia ? $docReferencia : null,
                "debito" => $totalesProducto->iva,
                "credito" => $totalesProducto->iva,
                "created_by" => request()->user()->id,
                "updated_by" => request()->user()->id
            ]);
            $documentoGeneral->addRow($docIva, $cuentaIva->naturaleza_ventas);
        }

        return $documentoGeneral;
    }

    private function validarCuentaIva($productoDb, $totalesProducto)
    {
        $cuentaVentaDevolucion = $productoDb->familia->cuenta_venta_devolucion_iva;
        if ($totalesProducto->iva && !$cuentaVentaDevolucion) {
            return ["Tablas <b style='color: #000'>→</b> Familias" => 
                ['mensaje' => "La familia <b style='color:#481897'>{$productoDb->familia->codigo}</b> - <b style='color:#481897'>{$productoDb->familia->nombre}</b> no tiene configurada la cuenta devolución iva en ventas."]
            ];
        }
        return null;
    }

    private function agregarReteFuente($documentoGeneral, $nit, $clienteId, $docReferencia)
    {
        if ($this->totalesFactura['total_rete_fuente'] && $this->totalesFactura['id_cuenta_rete_fuente']) {
            $cuentaRetencion = PlanCuentas::whereId($this->totalesFactura['id_cuenta_rete_fuente'])->first();
            if ($cuentaRetencion) {
                // Validar naturaleza_ventas
                if (is_null($cuentaRetencion->naturaleza_ventas)) {
                    return [
                        'error' => [
                            "Cuenta retención fuente" => [
                                "La cuenta de retención en la fuente ({$cuentaRetencion->cuenta} - {$cuentaRetencion->nombre}) " .
                                "no tiene configurada la naturaleza de ventas."
                            ]
                        ]
                    ];
                }
                
                $cuentaOpuestoRetencion = PlanCuentas::CREDITO == $cuentaRetencion->naturaleza_ventas ? PlanCuentas::DEBITO : PlanCuentas::CREDITO;
                $doc = new DocumentosGeneral([
                    "id_cuenta" => $cuentaRetencion->id,
                    "id_nit" => $cuentaRetencion->exige_nit ? $this->facturaVentas->id_cliente : null,
                    "id_centro_costos" => $cuentaRetencion->exige_centro_costos ? $this->facturaVentas->id_centro_costos : null,
                    "concepto" => $cuentaRetencion->exige_concepto ? 'TOTAL: ' . $nit->nombre_nit . ' - ' . $docReferencia : null,
                    "documento_referencia" => $cuentaRetencion->exige_documento_referencia ? $docReferencia : null,
                    "debito" => $this->totalesFactura['total_rete_fuente'],
                    "credito" => $this->totalesFactura['total_rete_fuente'],
                    "created_by" => request()->user()->id,
                    "updated_by" => request()->user()->id
                ]);
                $documentoGeneral->addRow($doc, $cuentaOpuestoRetencion);
            }
        }
        return $documentoGeneral;
    }

    private function agregarFormasPago($documentoGeneral, $request, $nit, $clienteId, $notaCredito = null)
    {
        $totalProductos = $this->totalesFactura['total_factura'];
        
        foreach ($request->get('pagos') as $pago) {
            $pago = (object)$pago;
            
            $formaPago = $this->findFormaPago($pago->id);
            // Validar naturaleza_ventas
            if (is_null($formaPago->cuenta->naturaleza_ventas)) {
                return [
                    'error' => [
                        "Cuenta forma de pago" => [
                            "La cuenta de la forma de pago ({$formaPago->cuenta->cuenta} - {$formaPago->cuenta->nombre}) " .
                            "no tiene configurada la naturaleza de ventas. " .
                            "Forma de pago: {$formaPago->nombre}"
                        ]
                    ]
                ];
            }
            
            $pagoValor = $pago->id == 1 ? $pago->valor - $this->totalesPagos['total_cambio'] : $pago->valor;
            $totalProductos -= $pagoValor;

            if ($notaCredito) {
                FacVentaPagos::create([
                    'id_venta' => $notaCredito->id,
                    'id_forma_pago' => $formaPago->id,
                    'valor' => $pagoValor,
                    'saldo' => $totalProductos,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);
            }

            $cuentaFormaPago = $formaPago->cuenta;
            $docReferencia = $notaCredito ? $notaCredito->documento_referencia : $request->get('documento_referencia');
            $cuentaOpuestoPago = PlanCuentas::CREDITO == $cuentaFormaPago->naturaleza_ventas ? PlanCuentas::DEBITO : PlanCuentas::CREDITO;

            $doc = new DocumentosGeneral([
                'id_cuenta' => $cuentaFormaPago->id,
                'id_nit' => $cuentaFormaPago->exige_nit ? $this->facturaVentas->id_cliente : null,
                'id_centro_costos' => $cuentaFormaPago->exige_centro_costos ? $this->facturaVentas->id_centro_costos : null,
                'concepto' => $cuentaFormaPago->exige_concepto ? 'TOTAL: ' . $nit->nombre_nit . ' - ' . $docReferencia : null,
                'documento_referencia' => $cuentaFormaPago->exige_documento_referencia ? $docReferencia : null,
                'debito' => $pagoValor,
                'credito' => $pagoValor,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);
            $documentoGeneral->addRow($doc, $cuentaOpuestoPago);
        }

        return $documentoGeneral;
    }

    private function procesarFacturaElectronica($notaCredito, $empresa, $request)
    {
        if (!$this->facturaVentas->fe_codigo_identificador) {
            return null;
        }

        $notaCreditoElectronica = (new NotaCreditoElectronicaSender($notaCredito))->send();

        if ($notaCreditoElectronica["status"] >= 400) {
            if ($notaCreditoElectronica["zip_key"]) {
                $notaCredito->fe_zip_key = $notaCreditoElectronica["zip_key"];
                $notaCredito->save();

                if ($notaCreditoElectronica["message_object"] == 'Batch en proceso de validación.') {
                    ProcessConsultarFE::dispatch($notaCredito->id, $notaCreditoElectronica["zip_key"], $request->user()->id, $empresa->id);
                    DB::connection('sam')->commit();
                    return response()->json([
                        "success" => false,
                        'data' => [],
                        "message" => 'Batch en proceso de validación, el sistema le notificará una vez haya consultado la información'
                    ], 300);
                }
            }

            if (!$notaCreditoElectronica["zip_key"] && $notaCreditoElectronica["status"] == 500) {
                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => $notaCreditoElectronica["error_message"]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            DB::connection('sam')->commit();
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $notaCreditoElectronica["error_message"]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($notaCreditoElectronica["status"] == 200) {
            $feSended = $notaCreditoElectronica['status'] == 200;
            $hasCufe = (isset($notaCreditoElectronica['cufe']) && $notaCreditoElectronica['cufe']);

            if ($feSended || $hasCufe) {
                $notaCredito = $this->SetFeFields($notaCredito, $notaCreditoElectronica['cufe'], $empresa->nit);
                $notaCredito->fe_zip_key = $notaCreditoElectronica['zip_key'];
                $notaCredito->fe_xml_file = $notaCreditoElectronica['xml_url'];
                $notaCredito->save();
            }
        }

        return null;
    }

    public function facturacionElectronica(Request $request)
    {
        $rules = [
			'id_nota_credito' => "required|exists:sam.fac_ventas,id",
		];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $empresa = Empresa::where('id', $request->user()->id_empresa)->first();
        $notaCredito = FacVentas::with('factura')
            ->where('id', $request->id_nota_credito)
            ->first();

        if ($notaCredito->fe_codigo_identificador) {
			return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>['factura_electronica' => ['mensaje' => "La factura $notaCredito->consecutivo ya fue emitida."]]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
		}

        if (!$notaCredito->factura->fe_codigo_identificador) {
			return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>['Factura electronica' => ['mensaje' => "La factura asociada a la nota débito no tiene CUFE generado"]]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
		}

        try {
            DB::connection('sam')->beginTransaction();

            $notaCreditoElectronica = (new NotaCreditoElectronicaSender($notaCredito))->send();

            if ($notaCreditoElectronica["status"] >= 400) {
                if ($notaCreditoElectronica["zip_key"]) {
                    $notaCredito->fe_zip_key = $notaCreditoElectronica["zip_key"];
                    $notaCredito->save();

                    if ($notaCreditoElectronica["message_object"] == 'Batch en proceso de validación.') {
                        //JOB CONSULTAR FACTURA EN 1MN
                        ProcessConsultarFE::dispatch($notaCredito->id, $notaCreditoElectronica["zip_key"], $request->user()->id, $empresa->id);

                        DB::connection('sam')->commit();

                        return response()->json([
                            "success" => false,
                            'data' => [],
                            "message" => 'Batch en proceso de validación, el sistema le notificará una vez haya consultado la información'
                        ], 300);
                    }
                }

                DB::connection('sam')->commit();

                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => $notaCreditoElectronica["error_message"]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($notaCreditoElectronica["status"] == 200) {
                $feSended = $notaCreditoElectronica['status'] == 200;
                $hasCufe = (isset($notaCreditoElectronica['cufe']) && $notaCreditoElectronica['cufe']);

                if($feSended || $hasCufe){
                    $notaCreditoElectronica['status'] = 200;
                    $notaCredito = $this->SetFeFields($notaCredito, $notaCreditoElectronica['cufe'], $empresa->nit);
                    $notaCredito->fe_zip_key = $notaCreditoElectronica['zip_key'];
                    $notaCredito->fe_xml_file = $notaCreditoElectronica['xml_url'];
                    $notaCredito->save();
                }
            }

            DB::connection('sam')->commit();

            return response()->json([
				'success'=>	true,
				'data' => [],
				'message'=> 'Factura electrónica enviada!'
			], 200);


            DB::connection('sam')->commit();

            return response()->json([
				'success'=>	true,
				'data' => [],
				'message'=> 'Factura electrónica enviada!'
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

    public function detalleFactura (Request $request)
    {
        try {
            $facturaDetalles = FacFacturaDetalle::where('fac_venta_detalles.id_venta', $request->get('id'))
                ->with('producto')
                ->get();

            foreach ($facturaDetalles as $detalle) {
                $detalle->cantidad_devuelta = 0;
                $detalle->total_devuelto = 0;
                $devoluciones = FacFacturaDetalle::where('id_venta_detalle', $detalle->id);
                if ($devoluciones->count()) {
                    $devoluciones = $devoluciones->get();
                    foreach ($devoluciones as $devolucion) {
                        $detalle->cantidad_devuelta+= $devolucion->cantidad;
                        $detalle->total_devuelto+= $devolucion->total;
                    }
                }
            }

            return response()->json([
                "success" => true,
                "data" => $facturaDetalles,
                "message" => "Factura detalle consultada con exito!"
            ]);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    private function createNotaCredito ($request)
    {
        $this->calcularTotales($request->get('productos'));
        $this->calcularFormasPago($request->get('pagos'));
        
        $this->bodega = FacBodegas::find($this->facturaVentas->id_bodega);

        return FacVentas::create([
            'id_cliente' => $this->facturaVentas->id_cliente,
            'id_factura' => $this->facturaVentas->id,
            'id_resolucion' => $request->get('id_resolucion'),
            'id_comprobante' => $this->resolucion->comprobante->id,
            'id_bodega' => $this->facturaVentas->id_bodega,
            'id_centro_costos' => $this->bodega->id_centro_costos,
            'fecha_manual' => $request->get('fecha_manual'),
            'consecutivo' => $request->get('documento_referencia'),
            'documento_referencia' => $request->get('documento_referencia'),
            'subtotal' => $this->totalesFactura['subtotal'],
            'total_iva' => $this->totalesFactura['total_iva'],
            'total_descuento' => $this->totalesFactura['total_descuento'],
            'total_rete_fuente' => $this->totalesFactura['total_rete_fuente'],
            'total_cambio' => $this->totalesPagos['total_cambio'],
            'porcentaje_rete_fuente' => $this->totalesFactura['porcentaje_rete_fuente'],
            'codigo_tipo_documento_dian' => CodigoDocumentoDianTypes::NOTA_CREDITO,
            'total_factura' => $this->totalesFactura['total_factura'],
            'observacion' => $request->get('observacion'),
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id,
        ]);
    }

    private function calcularTotalesProducto ($producto)
    {
        $subtotal = 0;
        $totalIva = 0;
        $totalDescuento = 0;
        $totalRetefuente = 0;

        $detalleProducto = FacFacturaDetalle::find($producto->id_factura_detalle);
        $ivaIncluido = VariablesEntorno::where('nombre', 'iva_incluido')->first();

        $subtotal = $producto->total_devolucion;
        $proporcion = $producto->total_devolucion / floatval($detalleProducto->total);

        if ($detalleProducto->iva_valor) {
            $totalIva = $detalleProducto->iva_valor * $proporcion;
            $subtotal-= $totalIva;
            // if ($ivaIncluido) {
            // }
        }

        if ($detalleProducto->descuento_valor) {
            $totalDescuento = $detalleProducto->descuento_valor * $proporcion;
            $subtotal-= $totalDescuento;
        }

        if ($this->facturaVentas->total_rete_fuente) {
            $proporcionReteFuente = $this->totalesFactura['total_factura'] / floatval($this->facturaVentas->total_factura);
            $totalRetefuente = $this->facturaVentas->total_rete_fuente * $proporcionReteFuente;
        }

        return (object)[
            "subtotal" => $subtotal,
            "iva" => $totalIva,
            "descuento" => $totalDescuento,
            "retefuente" => $totalRetefuente
        ];
    }

    private function calcularTotales ($productos)
    {
        foreach ($productos as $producto) {
            $producto = (object)$producto;
            $detalleProducto = FacFacturaDetalle::find($producto->id_factura_detalle);

            $productoDb = FacProductos::where('id', $producto->id_producto)
                ->with(
                    'familia.cuenta_venta_devolucion',
                    'familia.cuenta_venta_retencion.impuesto',
                    'familia.cuenta_venta_devolucion_iva.impuesto',
                    'familia.cuenta_venta_descuento'
                )
                ->first();

            $cuentaRetencion = $productoDb->familia->cuenta_venta_retencion;
            if ($cuentaRetencion && $cuentaRetencion->impuesto) {
                $impuesto = $cuentaRetencion->impuesto;
                if (floatval($impuesto->porcentaje) > $this->totalesFactura['porcentaje_rete_fuente']) {
                    $this->totalesFactura['porcentaje_rete_fuente'] = floatval($impuesto->porcentaje);
                    $this->totalesFactura['tope_retencion'] = floatval($impuesto->base);
                    $this->totalesFactura['id_cuenta_rete_fuente'] = $cuentaRetencion->id;
                }
            }

            $proporcion = $producto->total_devolucion / floatval($detalleProducto->total);

            $this->totalesFactura['subtotal']+= $producto->total_devolucion;
            if ($detalleProducto->iva_valor) {
                $this->totalesFactura['total_iva']+= $detalleProducto->iva_valor * $proporcion;
                $this->totalesFactura['subtotal']-= $detalleProducto->iva_valor * $proporcion;
            }
            if ($detalleProducto->descuento_valor) {
                $this->totalesFactura['total_descuento']+= $detalleProducto->descuento_valor * $proporcion;
                $this->totalesFactura['subtotal']-= $detalleProducto->descuento_valor * $proporcion;
            }
            $this->totalesFactura['total_factura']+= $producto->total_devolucion;
        }
        if ($this->facturaVentas->total_rete_fuente) {
            $proporcionReteFuente = $this->totalesFactura['total_factura'] / floatval($this->facturaVentas->total_factura);
            $this->totalesFactura['total_rete_fuente'] = $this->facturaVentas->total_rete_fuente * $proporcionReteFuente;
        } else {
            $this->totalesFactura['id_cuenta_rete_fuente'] = null;
        }
    }

    private function calcularFormasPago($pagos)
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

    private function findCliente ($id_cliente)
    {
        return Nits::whereId($id_cliente)
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
                'familia.cuenta_venta',
                'familia.cuenta_venta_retencion.impuesto',
                'familia.cuenta_venta_iva.impuesto',
                'familia.cuenta_venta_descuento',
                'familia.cuenta_inventario',
                'familia.cuenta_costos',
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

}
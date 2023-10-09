<?php

namespace App\Http\Controllers\Capturas;

use DB;
use App\Helpers\Documento;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacVentas;
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacFormasPago;
use App\Models\Sistema\FacVentaPagos;
use App\Models\Sistema\FacResoluciones;
use App\Models\Sistema\FacVentaDetalles;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\FacProductosBodegas;
use App\Models\Sistema\FacProductosBodegasMovimiento;

class VentaController extends Controller
{
    use BegConsecutiveTrait;

    protected $bodega = null;
    protected $resolucion = null;
	protected $messages = null;
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
        "cuenta_venta" => ["valor" => "subtotal"],
		"cuenta_venta_descuento" => ["valor" => "descuento_valor"],
        "cuenta_venta_iva" => ["valor" => "iva_valor"],
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
        $data = [
            'bodegas' => FacBodegas::first(),
            'resolucion' => FacResoluciones::first(),
        ];
        return view('pages.capturas.venta.venta-view', $data);
    }

    public function create (Request $request)
    {
        $rules = [
            'id_cliente' => 'required|exists:sam.nits,id',
            'id_bodega' => 'required|exists:sam.fac_bodegas,id',
            'fecha_manual' => 'required|date',
            'documento_referencia' => 'required|string',
            'productos' => 'array|required',
            'productos.*.id_producto' => [
                'required',
                'exists:sam.fac_productos,id',
                function ($attribute, $value, $fail) {
					$producto = FacProductos::whereId($value)
                        ->with('familia')
                        ->first();

                    if (!$producto->familia->id_cuenta_venta) {
                        $fail("La familia (".$producto->familia->codigo." - ".$producto->familia->nombre.") no tiene cuenta venta configurada");
                    }
				}
            ],
            'productos.*.cantidad' => 'required|numeric|min:1',
            'productos.*.costo' => 'required|min:0',
            'productos.*.descuento_porcentaje' => 'required|numeric|min:0|max:99',
            'productos.*.descuento_valor' => 'required|numeric|min:0',
            'productos.*.iva_porcentaje' => 'required|numeric|min:0|max:99',
            'productos.*.iva_valor' => 'required|numeric|min:0',
            'productos.*.total' => 'required|numeric|min:0',
            'pagos' => 'array|required',
            'pagos.*.id' => 'required|exists:sam.fac_formas_pagos,id',
            'pagos.*.valor' => 'required|numeric|min:1',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->messages()
            ], 422);
        }

        $this->resolucion = FacResoluciones::whereId($request->get('id_resolucion'))
            ->with('comprobante')
            ->first();

        $consecutivo = $this->getNextConsecutive($this->resolucion->comprobante->id, $request->get('fecha_manual'));
        $request->request->add([
            'id_comprobante' => $this->resolucion->comprobante->id,
            'consecutivo' => $consecutivo
        ]);

        try {
            DB::connection('sam')->beginTransaction();
            //CREAR FACTURA VENTA
            $venta = $this->createFacturaVenta($request); 

            //GUARDAR DETALLE & MOVIMIENTO CONTABLE VENTAS
            $documentoGeneral = new Documento(
                $this->resolucion->comprobante->id,
                $venta,
                $request->get('fecha_manual'),
                $request->get('consecutivo')
            );

            foreach ($request->get('productos') as $producto) {
                $producto = (object)$producto;

                $nit = $this->findCliente($venta->id_cliente);
                $productoDb = $this->findProducto($producto->id_producto);

                //AGREGAR MOVIMIENTO BODEGA
                $bodegaProducto = FacProductosBodegas::where('id_bodega', $this->bodega->id)
                    ->where('id_producto', $producto->id_producto)
                    ->first();

                if ($bodegaProducto && $producto->cantidad > $bodegaProducto->cantidad) {

                    DB::connection('sam')->rollback();
                    return response()->json([
                        "success"=>false,
                        'data' => [],
                        "message"=> 'La cantidad del producto'.$productoDb->codigo. ' - ' .$productoDb->nombre. ' supera la cantidad en bodega'
                    ], 422);
                }

                //CREAR COMPRA DETALLE
                FacVentaDetalles::create([
                    'id_venta' => $venta->id,
                    'id_cuenta_venta' => $productoDb->familia->id_cuenta_venta,
                    'id_cuenta_venta_retencion' => $productoDb->familia->id_cuenta_venta_retencion,
                    'id_cuenta_venta_iva' => $productoDb->familia->id_cuenta_venta_iva,
                    'id_cuenta_venta_descuento' => $productoDb->familia->id_cuenta_venta_descuento,
                    'descripcion' => $productoDb->codigo.' - '.$productoDb->nombre,
                    'cantidad' => $producto->cantidad,
                    'costo' => $producto->costo,
                    'subtotal' => $producto->costo * $producto->cantidad,
                    'descuento_porcentaje' => $producto->descuento_porcentaje,
                    'descuento_valor' => $producto->descuento_valor,
                    'iva_porcentaje' => $producto->iva_porcentaje,
                    'iva_valor' => $producto->iva_valor,
                    'total' => $producto->total,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                //AGREGAR MOVIMIENTO CONTABLE
                foreach ($this->cuentasContables as $cuentaKey => $cuenta) {
                    $cuentaRecord = $productoDb->familia->{$cuentaKey};
    
                    $keyTotalItem = $cuenta["valor"];
    
                    $doc = new DocumentosGeneral([
                        "id_cuenta" => $cuentaRecord->id,
                        "id_nit" => $cuentaRecord->exige_nit ? $venta->id_cliente : null,
                        "id_centro_costos" => $cuentaRecord->exige_centro_costos ? $venta->id_centro_costos : null,
                        "concepto" => 'COMPRA: '.$cuentaRecord->exige_concepto ? $nit->nombre_nit.' - '.$venta->documento_referencia : null,
                        "documento_referencia" => $cuentaRecord->exige_documento_referencia ? $venta->documento_referencia : null,
                        "debito" => $cuentaRecord->naturaleza_cuenta == PlanCuentas::DEBITO ? $producto->{$keyTotalItem} : 0,
                        "credito" => $cuentaRecord->naturaleza_cuenta == PlanCuentas::CREDITO ? $producto->{$keyTotalItem} : 0,
                        "created_by" => request()->user()->id,
                        "updated_by" => request()->user()->id
                    ]);
                    $documentoGeneral->addRow($doc, $cuentaRecord->naturaleza_cuenta);
                }

                //AGREGAR MOVIMIENTO BODEGA
                $bodegaProducto = FacProductosBodegas::where('id_bodega', $this->bodega->id)
                    ->where('id_producto', $producto->id_producto)
                    ->first();

                if ($bodegaProducto && $productoDb->familia->inventario) {

                    $movimiento = new FacProductosBodegasMovimiento([
                        'id_producto' => $producto->id_producto,
                        'id_bodega' => $venta->id_bodega,
                        'cantidad_anterior' => $bodegaProducto->cantidad,
                        'cantidad' => $producto->cantidad,
                        'tipo_tranferencia' => 2,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);

                    $bodegaProducto->updated_by = request()->user()->id;
                    $bodegaProducto->cantidad-= $producto->cantidad;
                    $bodegaProducto->save();

                    $movimiento->relation()->associate($venta);
                    $venta->bodegas()->save($movimiento);
                }
            }

            //AGREGAR RETE FUENTE
            if ($this->totalesFactura['total_rete_fuente']) {
                $cuentaRetencion = PlanCuentas::whereId($this->totalesFactura['id_cuenta_rete_fuente'])->first();

                $doc = new DocumentosGeneral([
                    "id_cuenta" => $cuentaRetencion->id,
                    "id_nit" => $cuentaRetencion->exige_nit ? $venta->id_cliente : null,
                    "id_centro_costos" => $cuentaRetencion->exige_centro_costos ? $venta->id_centro_costos : null,
                    "concepto" => 'TOTAL: '.$cuentaRetencion->exige_concepto ? $nit->nombre_nit.' - '.$venta->documento_referencia : null,
                    "documento_referencia" => $cuentaRetencion->exige_documento_referencia ? $venta->documento_referencia : null,
                    "debito" => $cuentaRetencion->naturaleza_cuenta == PlanCuentas::DEBITO ? $this->totalesFactura['total_rete_fuente'] : 0,
                    "credito" => $cuentaRetencion->naturaleza_cuenta == PlanCuentas::CREDITO ? $this->totalesFactura['total_rete_fuente'] : 0,
                    "created_by" => request()->user()->id,
                    "updated_by" => request()->user()->id
                ]);
                $documentoGeneral->addRow($doc, $cuentaRetencion->naturaleza_cuenta);
            }

            $totalProductos = $this->totalesFactura['total_factura'];

            //AGREGAR FORMAS DE PAGO
            foreach ($request->get('pagos') as $pago) {
                $pago = (object)$pago;

                $formaPago = $this->findFormaPago($pago->id);
                $totalProductos-= $pago->valor;

                FacVentaPagos::create([
                    'id_venta' => $venta->id,
                    'id_forma_pago' => $formaPago->id,
                    'valor' => $pago->valor,
                    'saldo' => $totalProductos,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                $doc = new DocumentosGeneral([
                    'id_cuenta' => $formaPago->cuenta->id,
                    'id_nit' => $formaPago->cuenta->exige_nit ? $venta->id_cliente : null,
                    'id_centro_costos' => $formaPago->cuenta->exige_centro_costos ? $venta->id_centro_costos : null,
                    'concepto' => 'TOTAL: '.$formaPago->cuenta->exige_concepto ? $nit->nombre_nit.' - '.$venta->documento_referencia : null,
                    'documento_referencia' => $formaPago->cuenta->exige_documento_referencia ? $venta->documento_referencia : null,
                    'debito' => $formaPago->cuenta->naturaleza_cuenta == PlanCuentas::DEBITO ? $pago->valor : 0,
                    'credito' => $formaPago->cuenta->naturaleza_cuenta == PlanCuentas::CREDITO ? $pago->valor : 0,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);
                $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_cuenta);
            }

            $this->updateConsecutivo($request->get('id_comprobante'), $request->get('consecutivo'));

            if (!$documentoGeneral->save()) {

				DB::connection('sam')->rollback();
				return response()->json([
					'success'=>	false,
					'data' => [],
					'message'=> $documentoGeneral->getErrors()
				], 422);
			}

            DB::connection('sam')->commit();

            return response()->json([
				'success'=>	true,
				'data' => $documentoGeneral->getRows(),
				'impresion' => '',
				'message'=> 'Venta creada con exito!'
			], 200);

        } catch (Exception $e) {

			DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    private function createFacturaVenta ($request)
    {
        $this->calcularTotales($request->get('productos'));

        $this->bodega = FacBodegas::whereId($request->get('id_bodega'))->first();

        $venta = FacVentas::create([
            'id_cliente' => $request->get('id_cliente'),
            'id_resolucion' => $request->get('id_resolucion'),
            'id_comprobante' => $this->resolucion->comprobante->id,
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
            'total_factura' => $this->totalesFactura['total_factura'],
            'observacion' => $request->get('observacion'),
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id,
        ]);

        return $venta;
    }

    private function calcularTotales ($productos)
    {
        foreach ($productos as $producto) {
            $producto = (object)$producto;

            $productoDb = FacProductos::where('id', $producto->id_producto)
                ->with(
                    'familia.cuenta_venta',
                    'familia.cuenta_venta_retencion.impuesto',
                    'familia.cuenta_venta_iva.impuesto',
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
            $subtotal = $producto->cantidad * $producto->costo;
            $this->totalesFactura['subtotal']+= $subtotal;
            $this->totalesFactura['total_iva']+= $producto->iva_valor;
            $this->totalesFactura['total_descuento']+= $producto->descuento_valor;
            $this->totalesFactura['total_factura']+= $subtotal - $producto->descuento_valor + $producto->iva_valor;
        }

        if ($this->totalesFactura['total_factura'] > $this->totalesFactura['tope_retencion'] && $this->totalesFactura['porcentaje_rete_fuente'] > 0) {
            $total_rete_fuente = ($this->totalesFactura['subtotal'] * $this->totalesFactura['porcentaje_rete_fuente']) / 100;
            $this->totalesFactura['total_rete_fuente'] = $total_rete_fuente;
            $this->totalesFactura['total_factura']-= $total_rete_fuente;
        } else {
            $this->totalesFactura['id_cuenta_rete_fuente'] = null;
        }
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
        return FacProductos::where('id', $id_producto)
            ->with(
                'familia.cuenta_venta',
                'familia.cuenta_venta_retencion.impuesto',
                'familia.cuenta_venta_iva.impuesto',
                'familia.cuenta_venta_descuento'
            )
            ->first();
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
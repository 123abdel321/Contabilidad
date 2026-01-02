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
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\FacCargueDescargue;
use App\Models\Sistema\FacProductosBodegas;
use App\Models\Sistema\FacMovimientoInventarios;
use App\Models\Sistema\FacProductosBodegasMovimiento;
use App\Models\Sistema\FacMovimientoInventarioDetalles;

class MovimientoInventarioController extends Controller
{
    use BegConsecutiveTrait;

    protected $messages = null;

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
        return view('pages.capturas.movimiento_inventario.movimiento_inventario-view');
    }

    public function create (Request $request)
    {
        
        $rules = [
            'tipo' => 'required|min:1',
            'fecha_manual' => 'required|min:1',
            'id_nit' => 'nullable|exists:sam.nits,id',
            'id_bodega_origen' => 'required|exists:sam.fac_bodegas,id',
            'id_cargue_descargue' => 'required|exists:sam.fac_cargue_descargues,id',
            'id_bodega_destino' => 'nullable|required_if:tipo,=,2|exists:sam.fac_bodegas,id',
            'productos' => 'array|required',
            'productos.*.id_producto' => 'required|exists:sam.fac_productos,id',
            'productos.*.cantidad' => 'required|numeric|gt:0',
            'productos.*.costo' => 'required|min:0',
            'productos.*.total' => 'required|numeric|min:0'
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        if ($request->get('id_bodega_origen') == $request->get('id_bodega_destino')) {
			return response()->json(["success" => false, "message" => "La bodega de origen no puede ser igual a la bodega destino."], 422);
		}
        
        try {
            DB::connection('sam')->beginTransaction();

            $bodega = FacBodegas::where('id', $request->get('id_bodega_origen'))->first();
            $cargueDescargue = FacCargueDescargue::where('id', $request->get('id_cargue_descargue'))->first();
            $consecutivo = $this->getNextConsecutive($cargueDescargue->id_comprobante, $request->get('fecha_manual'));

            $request->request->add([
                'id_comprobante' => $cargueDescargue->id_comprobante,
                'id_centro_costos' => $bodega->id_centro_costos,
                'consecutivo' => $consecutivo
            ]);

            //CREAR MOVIMIENTO INVENTARIO
            $movimiento = $this->createMovimientoInventario($request);
            
            //INIT DOCUMENTOS TARRO
            $documentoGeneral = new Documento(
                $cargueDescargue->id_comprobante,
                $movimiento,
                $request->get('fecha_manual'),
                $request->get('consecutivo')
            );

            $nit = $this->findNit($movimiento->id_nit);

            //RECORRER PRODUCTOS
            foreach ($request->get('productos') as $producto) {
                $producto = (object)$producto;

                //CREAR COMPRA DETALLE
                FacMovimientoInventarioDetalles::create([
                    'id_movimiento_inventario' => $movimiento->id,
                    'id_producto' => $producto->id_producto,
                    'cantidad' => $producto->cantidad,
                    'costo' => $producto->costo,
                    'total' => $producto->cantidad * $producto->costo,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);
                
                //AGREGAR MOVIMIENTO CONTABLE DEBITO
                $cuentaDebito = PlanCuentas::where('id', $cargueDescargue->id_cuenta_debito)
                    ->first();

                if (!$cuentaDebito) {
                    DB::connection('sam')->rollback();
                    return response()->json([
                        "success"=>false,
                        'data' => [],
                        "message"=> ['Cuenta contable' => ['La cuenta debito del Cargue / Descargue no fue encontrada!']]
                    ], 422);
                }
                    
                $doc = new DocumentosGeneral([
                    "id_cuenta" => $cuentaDebito->id,
                    "id_nit" => $cuentaDebito->exige_nit ? $movimiento->id_nit : null,
                    "id_centro_costos" => $cuentaDebito->exige_centro_costos ? $movimiento->id_centro_costos : null,
                    "concepto" => $this->nombreTipo($request->get('tipo')). ' ' .$nit->nombre_completo,
                    "documento_referencia" => $cuentaDebito->exige_documento_referencia ? $movimiento->consecutivo : null,
                    "debito" => $producto->cantidad * $producto->costo,
                    "credito" => $producto->cantidad * $producto->costo,
                    "created_by" => request()->user()->id,
                    "updated_by" => request()->user()->id
                ]);

                $documentoGeneral->addRow($doc, PlanCuentas::DEBITO);

                //AGREGAR MOVIMIENTO CONTABLE CREDITO
                $cuentaCredito = PlanCuentas::where('id', $cargueDescargue->id_cuenta_credito)
                    ->first();

                if (!$cuentaDebito) {
                    DB::connection('sam')->rollback();
                    return response()->json([
                        "success"=>false,
                        'data' => [],
                        "message"=> ['Cuenta contable' => ['La cuenta credito del Cargue / Descargue no fue encontrada!']]
                    ], 422);
                }
                    
                $doc = new DocumentosGeneral([
                    "id_cuenta" => $cuentaCredito->id,
                    "id_nit" => $cuentaCredito->exige_nit ? $movimiento->id_nit : null,
                    "id_centro_costos" => $cuentaCredito->exige_centro_costos ? $movimiento->id_centro_costos : null,
                    "concepto" => $this->nombreTipo($request->get('tipo')). ' ' .$nit->nombre_completo,
                    "documento_referencia" => $cuentaCredito->exige_documento_referencia ? $movimiento->consecutivo : null,
                    "debito" => $producto->cantidad * $producto->costo,
                    "credito" => $producto->cantidad * $producto->costo,
                    "created_by" => request()->user()->id,
                    "updated_by" => request()->user()->id
                ]);

                $documentoGeneral->addRow($doc, PlanCuentas::CREDITO);

                //AGREGAR MOVIMIENTO BODEGA
                $bodegaProducto = FacProductosBodegas::where('id_bodega', $movimiento->id_bodega_origen)
                    ->where('id_producto', $producto->id_producto)
                    ->first();

                if ($request->get('tipo') == '1') { //CARGUE
                    if (!$bodegaProducto) {
                        $bodegaProducto = FacProductosBodegas::create([
                            'id_producto' => $producto->id_producto,
                            'id_bodega' => $movimiento->id_bodega_origen,
                            'cantidad' => 0,
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);
                    }

                    $movimientoBodega = new FacProductosBodegasMovimiento([
                        'id_producto' => $producto->id_producto,
                        'id_bodega' => $movimiento->id_bodega_origen,
                        'cantidad_anterior' => $bodegaProducto->cantidad,
                        'cantidad' => $producto->cantidad,
                        'tipo_tranferencia' => 1,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);

                    $bodegaProducto->cantidad+= $producto->cantidad;
                    $bodegaProducto->save();

                    $movimientoBodega->relation()->associate($movimiento);
                    $movimiento->bodegas()->save($movimientoBodega);

                } else if ($request->get('tipo') == '2') { //TRASLADO

                    //BODEGA ORIDEN
                    $bodegaProducto = FacProductosBodegas::where('id_bodega', $movimiento->id_bodega_origen)
                        ->where('id_producto', $producto->id_producto)
                        ->first();

                    //BODEGA DESTINO
                    $bodegaProductoDestino = FacProductosBodegas::where('id_bodega', $movimiento->id_bodega_destino)
                        ->where('id_producto', $producto->id_producto)
                        ->first();

                    if (!$bodegaProducto) {

                        DB::connection('sam')->rollback();
                        return response()->json([
                            "success"=>false,
                            'data' => [],
                            "message"=> ['Cantidad bodega' => ['La cantidad del producto supera la cantidad en bodega']]
                        ], 422);
                    }

                    if (!$bodegaProductoDestino) {
                        $bodegaProductoDestino = FacProductosBodegas::create([
                            'id_producto' => $producto->id_producto,
                            'id_bodega' => $movimiento->id_bodega_destino,
                            'cantidad' => 0,
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);
                    }

                    //CARGAR CANTIDAD EN BODEGA DESTINO
                    $movimientoBodegaDestino = new FacProductosBodegasMovimiento([
                        'id_producto' => $producto->id_producto,
                        'id_bodega' => $movimiento->id_bodega_destino,
                        'cantidad_anterior' => $bodegaProductoDestino->cantidad,
                        'cantidad' => $producto->cantidad,
                        'tipo_tranferencia' => 3,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);

                    $bodegaProductoDestino->cantidad+= $producto->cantidad;
                    $bodegaProductoDestino->save();

                    $movimientoBodegaDestino->relation()->associate($movimiento);
                    $movimiento->bodegas()->save($movimientoBodegaDestino);

                    //DESCARGAR CANTIDAD EN BODEGA ORIGEN
                    $movimientoBodegaOrigen = new FacProductosBodegasMovimiento([
                        'id_producto' => $producto->id_producto,
                        'id_bodega' => $movimiento->id_bodega_origen,
                        'cantidad_anterior' => $bodegaProducto->cantidad,
                        'cantidad' => $producto->cantidad,
                        'tipo_tranferencia' => 3,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);

                    $bodegaProducto->cantidad-= $producto->cantidad;
                    $bodegaProducto->save();

                    $movimientoBodegaOrigen->relation()->associate($movimiento);
                    $movimiento->bodegas()->save($movimientoBodegaOrigen);
                } else { //DESCARGUE
                    if ($producto->cantidad > $bodegaProducto->cantidad) {
                        DB::connection('sam')->rollback();
                        return response()->json([
                            "success"=>false,
                            'data' => [],
                            "message"=> ['Cantidad bodega' => ['La cantidad del producto supera la cantidad en bodega']]
                        ], 422);
                    }

                    $movimientoBodega = new FacProductosBodegasMovimiento([
                        'id_producto' => $producto->id_producto,
                        'id_bodega' => $movimiento->id_bodega_origen,
                        'cantidad_anterior' => $bodegaProducto->cantidad,
                        'cantidad' => $producto->cantidad,
                        'tipo_tranferencia' => 2,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);

                    $bodegaProducto->updated_by = request()->user()->id;
                    $bodegaProducto->cantidad-= $producto->cantidad;
                    $bodegaProducto->save();

                    $movimientoBodega->relation()->associate($movimiento);
                    $movimiento->bodegas()->save($movimientoBodega);
                }
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
                'message'=> 'Movimiento inventario creado con exito!'
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

    private function createMovimientoInventario ($request)
    {
        $dataTotal = $this->calcularTotales($request->get('productos'));

        $movimiento = FacMovimientoInventarios::create([
            'id_nit' => $request->get('id_nit'),
            'id_cargue_descargues' => $request->get('id_cargue_descargue'),
            'id_comprobante' => $request->get('id_comprobante'),
            'id_centro_costos' => $request->get('id_centro_costos'),
            'id_cuenta_debito' => $request->get('id_cuenta_debito'),
            'id_cuenta_credito' => $request->get('id_cuenta_credito'),
            'id_bodega_origen' => $request->get('id_bodega_origen'),
            'id_bodega_destino' => $request->get('id_bodega_destino'),
            'tipo' => $request->get('tipo'),
            'fecha_manual' => $request->get('fecha_manual'),
            'cantidad' => $dataTotal['cantidad'],
            'total_movimiento' => $dataTotal['total_movimiento'],
            'consecutivo' => $request->get('consecutivo'),
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id,
        ]);

        return $movimiento;
    }

    private function findNit ($id_nit)
    {
        return Nits::whereId($id_nit)
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

    private function nombreTipo ($tipo)
    {
        if ($tipo == '1') return 'CARGUE';
        if ($tipo == '2') return 'TRASLADO';
        return 'DESCARGUE';
    }

    private function calcularTotales ($productos)
    {
        $dataTotal = [
            'cantidad' => 0,
            'total_movimiento' => 0,
        ];

        foreach ($productos as $producto) {
            $producto = (object)$producto;

            $dataTotal['cantidad']+= $producto->cantidad;
            $dataTotal['total_movimiento']+= ($producto->cantidad * $producto->costo);
        }

        return $dataTotal;
    }

}

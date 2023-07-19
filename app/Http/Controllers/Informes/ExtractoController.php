<?php

namespace App\Http\Controllers\Informes;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Nits;

class ExtractoController extends Controller
{
    public $messages;
    public $carteraCollection = [];

    public function __construct()
    {
        $this->messages = [
            'id_nit.exists' => 'El id debe existir en la tabla de nits.',
            'required' => 'El campo :attribute es requerido.',
            'exists' => 'El :attribute: :input seleccionado es inválido.',
            'numeric' => 'El :attribute debe ser un número.',
			'string' => 'El :attribute debe ser texto.',
			'min' => 'El campo :attribute debe ser mayor que cero.',
			'max' => 'El campo :attribute debe tener menos de :max caracteres.',
			'date' => 'El campo :attribute debe ser una fecha válida.',
			'array' => 'El campo :attribute debe ser un arreglo.',
			'formas_pago.required' => 'Seleccione las formas de pago.'
        ];
    }

    public function extracto(Request $request)
    {
        $rules = [
			"id_nit" => "nullable|exists:sam.nits,id",
			"numero_documento" => "nullable|exists:sam.nits,numero_documento",
			// "id_cuenta" => "required_without_all:cuenta,id_tipo_cuenta",
			// "cuenta" => "required_without:id_tipo_cuenta|sometimes|nullable|exists:sam.con_plan_cuentas,cuenta",
			"id_tipo_cuenta" => "nullable|exists:sam.tipo_cuentas,id",
		];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->messages()
            ], 422);
        }

        $wheres = '';
        $fecha = Carbon::now();

        if($request->has('id_tipo_cuenta') && $request->get('id_tipo_cuenta')) {
            $wheres.= ' AND PC.id_tipo_cuenta = '.$request->get('id_tipo_cuenta');
        }

        if($request->has('id_nit') && $request->get('id_nit')){
            $wheres.= ' AND N.id = '.$request->get('id_nit');
        } else if ($request->has('numero_documento')) {
            $nit = Nits::whereNumeroDocumento($request->get("numero_documento"))->first();
            if($nit) {
                $wheres.= ' AND N.id = '.$nit->id;
            }
        }

        if($request->has('documento_referencia') && $request->get('documento_referencia')) {
            $wheres.= ' AND DG.documento_referencia = '.$request->get('documento_referencia');
        }

        if($request->has('fecha') && $request->get('fecha')) {
            $wheres.= " AND DG.fecha_manual <= '{$request->get('fecha')}'";
            $fecha = $request->get('fecha');
        }

        $query = "SELECT
                N.id AS id_nit,
                N.numero_documento,
                CONCAT(N.otros_nombres, ' ', N.primer_apellido) AS nombre_nit,
                N.razon_social,
                N.telefono_1,
                N.telefono_2,
                N.email,
                N.direccion,
                PC.id AS id_cuenta,
                PC.cuenta,
                PC.nombre AS nombre_cuenta,
                DG.documento_referencia,
                DG.id_centro_costos,
                CC.codigo AS codigo_cecos,
                CC.nombre AS nombre_cecos,
                DG.id_comprobante AS id_comprobante,
                '' AS codigo_comprobante,
                '' AS nombre_comprobante,
                CO.tipo_comprobante,
                DG.consecutivo,
                DG.concepto,
                DG.fecha_manual,
                DG.created_at,
                PC.naturaleza_ingresos,
                PC.naturaleza_egresos,
                PC.naturaleza_compras,
                PC.naturaleza_ventas,
                PC.naturaleza_cuenta,
                SUM(DG.debito) AS debito,
                SUM(DG.credito) AS credito,
                DATEDIFF('$fecha', DG.fecha_manual) AS dias_cumplidos,
                'si' AS detalle,
                CASE
                    WHEN CO.tipo_comprobante = 0 THEN IF(PC.naturaleza_ingresos = 0, SUM(debito), SUM(credito))
                    WHEN CO.tipo_comprobante = 1 THEN IF(PC.naturaleza_egresos = 0, SUM(debito), SUM(credito))
                    WHEN CO.tipo_comprobante = 2 THEN IF(PC.naturaleza_compras = 0, SUM(debito), SUM(credito))
                    WHEN CO.tipo_comprobante = 3 THEN IF(PC.naturaleza_ventas = 0, SUM(debito), SUM(credito))
                    WHEN CO.tipo_comprobante > 3 THEN IF(PC.naturaleza_cuenta = 1, SUM(debito), SUM(credito))
                END AS total_abono,
                CASE
                    WHEN CO.tipo_comprobante = 0 THEN IF(PC.naturaleza_ingresos = 0, SUM(credito), SUM(debito))
                    WHEN CO.tipo_comprobante = 1 THEN IF(PC.naturaleza_egresos = 0, SUM(credito), SUM(debito))
                    WHEN CO.tipo_comprobante = 2 THEN IF(PC.naturaleza_compras = 0, SUM(credito), SUM(debito))
                    WHEN CO.tipo_comprobante = 3 THEN IF(PC.naturaleza_ventas = 0, SUM(credito), SUM(debito))
                    WHEN CO.tipo_comprobante > 3 THEN IF(PC.naturaleza_cuenta = 0, SUM(debito), SUM(credito))
                END AS total_facturas,
                CASE
                    WHEN CO.tipo_comprobante = 0 THEN IF(PC.naturaleza_ingresos = 1, SUM(DG.debito) - SUM(DG.credito), SUM(DG.credito) - SUM(DG.debito))
                    WHEN CO.tipo_comprobante = 1 THEN IF(PC.naturaleza_egresos = 1, SUM(DG.debito) - SUM(DG.credito), SUM(DG.credito) - SUM(DG.debito))
                    WHEN CO.tipo_comprobante = 2 THEN IF(PC.naturaleza_compras = 1, SUM(DG.debito) - SUM(DG.credito), SUM(DG.credito) - SUM(DG.debito))
                    WHEN CO.tipo_comprobante = 3 THEN IF(PC.naturaleza_ventas = 1, SUM(DG.debito) - SUM(DG.credito), SUM(DG.credito) - SUM(DG.debito))
                    WHEN CO.tipo_comprobante > 3 THEN IF(PC.naturaleza_cuenta = 0, SUM(DG.debito) - SUM(DG.credito), SUM(DG.credito) - SUM(DG.debito))
                END AS saldo

            FROM
                documentos_generals DG
                    
            LEFT JOIN nits N ON DG.id_nit = N.id
            LEFT JOIN plan_cuentas PC ON DG.id_cuenta = PC.id
            LEFT JOIN centro_costos CC ON DG.id_centro_costos = CC.id
            LEFT JOIN comprobantes CO ON DG.id_comprobante = CO.id
                    
            WHERE DG.documento_referencia IS NOT NULL
                $wheres
                    
            GROUP BY DG.id_cuenta, DG.id_nit, DG.documento_referencia

            HAVING (CASE
                WHEN CO.tipo_comprobante = 0 THEN IF(PC.naturaleza_ingresos = 1, SUM(DG.debito) - SUM(DG.credito), SUM(DG.credito) - SUM(DG.debito))
                WHEN CO.tipo_comprobante = 1 THEN IF(PC.naturaleza_egresos = 1, SUM(DG.debito) - SUM(DG.credito), SUM(DG.credito) - SUM(DG.debito))
                WHEN CO.tipo_comprobante = 2 THEN IF(PC.naturaleza_compras = 1, SUM(DG.debito) - SUM(DG.credito), SUM(DG.credito) - SUM(DG.debito))
                WHEN CO.tipo_comprobante = 3 THEN IF(PC.naturaleza_ventas = 1, SUM(DG.debito) - SUM(DG.credito), SUM(DG.credito) - SUM(DG.debito))
                WHEN CO.tipo_comprobante > 3 THEN IF(PC.naturaleza_cuenta = 0, SUM(DG.debito) - SUM(DG.credito), SUM(DG.credito) - SUM(DG.debito))
            END) != 0  
        ";
        $extracto = DB::connection('sam')->select($query);

        if($request->has('detallar_cartera') && $request->get('detallar_cartera')) {
            $queryDetalle = "SELECT
                N.id AS id_nit,
                N.numero_documento,
                CONCAT(N.otros_nombres, ' ', N.primer_apellido) AS nombre_nit,
                N.razon_social,
                N.telefono_1,
                N.telefono_2,
                N.email,
                N.direccion,
                PC.id AS id_cuenta,
                PC.cuenta,
                PC.nombre AS nombre_cuenta,
                DG.documento_referencia,
                DG.id_centro_costos,
                CC.codigo AS codigo_cecos,
                CC.nombre AS nombre_cecos,
                DG.id_comprobante,
                CO.codigo AS codigo_comprobante,
                CO.nombre AS nombre_comprobante,
                CO.tipo_comprobante,
                DG.consecutivo,
                DG.concepto,
                DG.fecha_manual,
                DG.created_at,
                PC.naturaleza_ingresos,
                PC.naturaleza_egresos,
                PC.naturaleza_compras,
                PC.naturaleza_ventas,
                PC.naturaleza_cuenta,
                DG.debito,
                DG.credito,
                'detalle' AS detalle,
                CASE
                    WHEN CO.tipo_comprobante = 0 THEN IF(PC.naturaleza_ingresos = 0, debito, credito)
                    WHEN CO.tipo_comprobante = 1 THEN IF(PC.naturaleza_egresos = 0, debito, credito)
                    WHEN CO.tipo_comprobante = 2 THEN IF(PC.naturaleza_compras = 0, debito, credito)
                    WHEN CO.tipo_comprobante = 3 THEN IF(PC.naturaleza_ventas = 0, debito, credito)
                    WHEN CO.tipo_comprobante > 3 THEN IF(PC.naturaleza_cuenta = 1, debito, credito)
                END AS total_abono,
                CASE
                    WHEN CO.tipo_comprobante = 0 THEN IF(PC.naturaleza_ingresos = 0, credito, debito)
                    WHEN CO.tipo_comprobante = 1 THEN IF(PC.naturaleza_egresos = 0, credito, debito)
                    WHEN CO.tipo_comprobante = 2 THEN IF(PC.naturaleza_compras = 0, credito, debito)
                    WHEN CO.tipo_comprobante = 3 THEN IF(PC.naturaleza_ventas = 0, credito, debito)
                    WHEN CO.tipo_comprobante > 3 THEN IF(PC.naturaleza_cuenta = 0, debito, credito)
                END AS total_facturas
            FROM
                documentos_generals DG
                
            LEFT JOIN nits N ON DG.id_nit = N.id
            LEFT JOIN plan_cuentas PC ON DG.id_cuenta = PC.id
            LEFT JOIN centro_costos CC ON DG.id_centro_costos = CC.id
            LEFT JOIN comprobantes CO ON DG.id_comprobante = CO.id
                
            WHERE DG.documento_referencia IS NOT NULL
                $wheres
            
            ORDER BY cuenta, id_nit, documento_referencia, created_at
            ";

            $extractoDetalle = DB::connection('sam')->select($queryDetalle);

            foreach ($extracto as $extrac) {
                $this->carteraCollection[$extrac->id_nit.'-'.$extrac->id_cuenta.'-'.$extrac->documento_referencia][] = (object)[
                    "id_nit" => $extrac->id_nit,
                    "numero_documento" => $extrac->numero_documento,
                    "nombre_nit" => $extrac->nombre_nit,
                    "razon_social" => $extrac->razon_social,
                    "telefono_1" => $extrac->telefono_1,
                    "telefono_2" => $extrac->telefono_2,
                    "email" => $extrac->email,
                    "direccion" => $extrac->direccion,
                    "id_cuenta" => $extrac->id_cuenta,
                    "cuenta" => $extrac->cuenta,
                    "nombre_cuenta" => $extrac->nombre_cuenta,
                    "documento_referencia" => $extrac->documento_referencia,
                    "id_centro_costos" => $extrac->id_centro_costos,
                    "codigo_cecos" => $extrac->codigo_cecos,
                    "nombre_cecos" => $extrac->nombre_cecos,
                    "id_comprobante" => $extrac->id_comprobante,
                    "codigo_comprobante" => $extrac->codigo_comprobante,
                    "nombre_comprobante" => $extrac->nombre_comprobante,
                    "tipo_comprobante" => $extrac->tipo_comprobante,
                    "consecutivo" => $extrac->consecutivo,
                    "concepto" => $extrac->concepto,
                    "fecha_manual" => $extrac->fecha_manual,
                    "created_at" => $extrac->created_at,
                    "naturaleza_ingresos" => $extrac->naturaleza_ingresos,
                    "naturaleza_egresos" => $extrac->naturaleza_egresos,
                    "naturaleza_compras" => $extrac->naturaleza_compras,
                    "naturaleza_ventas" => $extrac->naturaleza_ventas,
                    "naturaleza_cuenta" => $extrac->naturaleza_cuenta,
                    "debito" => $extrac->debito,
                    "credito" => $extrac->credito,
                    "dias_cumplidos" => $extrac->dias_cumplidos,
                    "total_abono" => $extrac->total_abono,
                    "total_facturas" => $extrac->total_facturas,
                    "saldo" => $extrac->saldo,
                    "detalle" => 'total'
                ];
            }

            foreach ($extractoDetalle as $detalle) {
                $this->carteraCollection[$detalle->id_nit.'-'.$detalle->id_cuenta.'-'.$detalle->documento_referencia][] = (object)[
                    "id_nit" => $detalle->id_nit,
                    "numero_documento" => '',
                    "nombre_nit" => '',
                    "razon_social" => $detalle->razon_social,
                    "telefono_1" => $extrac->telefono_1,
                    "telefono_2" => $extrac->telefono_2,
                    "email" => $extrac->email,
                    "direccion" => $extrac->direccion,
                    "id_cuenta" => $detalle->id_cuenta,
                    "cuenta" => $detalle->cuenta,
                    "nombre_cuenta" => $detalle->nombre_cuenta,
                    "documento_referencia" => '',
                    "id_centro_costos" => $detalle->id_centro_costos,
                    "codigo_cecos" => $detalle->codigo_cecos,
                    "nombre_cecos" => $detalle->nombre_cecos,
                    "id_comprobante" => $detalle->id_comprobante,
                    "codigo_comprobante" => $detalle->codigo_comprobante,
                    "nombre_comprobante" => $detalle->nombre_comprobante,
                    "tipo_comprobante" => $detalle->tipo_comprobante,
                    "consecutivo" => $detalle->consecutivo,
                    "concepto" => $detalle->concepto,
                    "fecha_manual" => $detalle->fecha_manual,
                    "created_at" => $detalle->created_at,
                    "naturaleza_ingresos" => '',
                    "naturaleza_egresos" => '',
                    "naturaleza_compras" => '',
                    "naturaleza_ventas" => '',
                    "naturaleza_cuenta" => '',
                    "debito" => $detalle->debito,
                    "credito" => $detalle->credito,
                    "dias_cumplidos" => '',
                    "total_abono" => $detalle->total_abono,
                    "total_facturas" => $detalle->total_facturas,
                    "saldo" => '0',
                    "detalle" => ''
                ];
            }
            // dd($this->carteraCollection);
            $extractoDetallado = [];
            foreach ($this->carteraCollection as $carteraDatos) {
                foreach ($carteraDatos as $cartera) {
                    $extractoDetallado[] = $cartera;
                }
            }
            
            return response()->json([
                'success'=>	true,
                'data' => $extractoDetallado,
                'message'=> 'Extracto detallado generado con exito!'
            ]);
        }

        return response()->json([
            'success'=>	true,
            'data' => $extracto,
            'message'=> 'Extracto generado con exito!'
        ]);
    }
}

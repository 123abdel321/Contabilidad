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
                PC.id AS id_cuenta,
                PC.cuenta,
                PC.nombre AS nombre_cuenta,
                DG.documento_referencia,
                DG.id_centro_costos,
                CC.codigo AS codigo_cecos,
                CC.nombre AS nombre_cecos,
                DG.id_comprobante AS id_comprobante,
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
                SUM(DG.debito) AS debito,
                SUM(DG.credito) AS credito,
                DATEDIFF('$fecha', DG.fecha_manual) AS dias_cumplidos,
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
                    
            WHERE DG.id IS NOT NULL
                $wheres
                    
            GROUP BY DG.id_cuenta, DG.id_nit, DG.documento_referencia

            HAVING (CASE
                WHEN CO.tipo_comprobante = 0 THEN IF(PC.naturaleza_ingresos = 1, SUM(DG.debito) - SUM(DG.credito), SUM(DG.credito) - SUM(DG.debito))
                WHEN CO.tipo_comprobante = 1 THEN IF(PC.naturaleza_egresos = 1, SUM(DG.debito) - SUM(DG.credito), SUM(DG.credito) - SUM(DG.debito))
                WHEN CO.tipo_comprobante = 2 THEN IF(PC.naturaleza_compras = 1, SUM(DG.debito) - SUM(DG.credito), SUM(DG.credito) - SUM(DG.debito))
                WHEN CO.tipo_comprobante = 3 THEN IF(PC.naturaleza_ventas = 1, SUM(DG.debito) - SUM(DG.credito), SUM(DG.credito) - SUM(DG.debito))
                WHEN CO.tipo_comprobante > 3 THEN IF(PC.naturaleza_cuenta = 0, SUM(DG.debito) - SUM(DG.credito), SUM(DG.credito) - SUM(DG.debito))
            END) > 0  
        ";
        $extracto = DB::connection('sam')->select($query);

        return response()->json([
            'success'=>	true,
            'data' => $extracto,
            'message'=> 'Extracto generado con exito!'
        ]);
    }
}

<?php

namespace App\Jobs;

use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\PrivateMessageEvent;
use Carbon\Carbon;
//MODELS
use App\Models\User;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfCartera;

class ProcessInformeCartera implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $request;
    public $id_usuario;
	public $id_empresa;
    public $id_cartera = 1;
    public $carteras = [];
    public $carteraCollection = [];

    public function __construct($request, $id_usuario, $id_empresa)
    {
        $this->request = $request;
		$this->id_usuario = $id_usuario;
		$this->id_empresa = $id_empresa;
    }

    public function handle()
    {
		$empresa = Empresa::find($this->id_empresa);
        
        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

        DB::connection('informes')->beginTransaction();
        
        try {
            $cartera = InfCartera::create([
				'id_empresa' => $this->id_empresa,
				'id_cuenta' => $this->request['id_cuenta'],
				'id_nit' => $this->request['id_nit'],
				'fecha_hasta' => $this->request['fecha_cartera'],
				'detallar_cartera' => $this->request['detallar_cartera'],
			]);

            $this->id_cartera = $cartera->id;

            $this->documentosCartera();
            $this->documentosTotal();
            if($this->request['detallar_cartera'] == '1') {
                $this->documentosCarteraDetalle();
            }

            ksort($this->carteraCollection, SORT_STRING | SORT_FLAG_CASE);

            foreach (array_chunk($this->carteraCollection,233) as $carteraCollection){
                DB::connection('informes')
                    ->table('inf_cartera_detalles')
                    ->insert(array_values($carteraCollection));
            }

            DB::connection('informes')->commit();

            event(new PrivateMessageEvent('informe-cartera-'.$empresa->token_db.'_'.$this->id_usuario, [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con exito!',
                'titulo' => 'Cartera generada',
                'id_cartera' => $this->id_cartera,
                'autoclose' => false
            ]));

        } catch (Exception $exception) {
            DB::connection('informes')->rollback();

			throw $exception;
        }
    }

    private function documentosCartera()
    {
        $fecha = Carbon::now();

        DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                "N.id AS id_nit",
                "TD.nombre AS tipo_documento",
                "N.numero_documento",
                "N.id_ciudad",
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "N.razon_social",
                "N.telefono_1",
                "N.telefono_2",
                "N.email",
                "N.direccion",
                "N.plazo",
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.nombre AS nombre_cuenta",
                "DG.documento_referencia",
                "DG.id_centro_costos",
                "CC.codigo AS codigo_cecos",
                "CC.nombre AS nombre_cecos",
                "DG.id_comprobante AS id_comprobante",
                "CO.codigo AS codigo_comprobante",
                "CO.nombre AS nombre_comprobante",
                "CO.tipo_comprobante",
                "DG.consecutivo",
                "DG.concepto",
                "DG.fecha_manual",
                "DG.created_at",
                "PC.naturaleza_ingresos",
                "PC.naturaleza_egresos",
                "PC.naturaleza_compras",
                "PC.naturaleza_ventas",
                "PC.naturaleza_cuenta",
                DB::raw("SUM(DG.debito) AS debito"),
                DB::raw("SUM(DG.credito) AS credito"),
                DB::raw("DATEDIFF('$fecha', DG.fecha_manual) AS dias_cumplidos"),
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                "DG.created_by",
                "DG.updated_by",
                DB::raw("IF(PC.naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono"),
                DB::raw("IF(PC.naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas"),
                DB::raw("IF(
                    PC.naturaleza_cuenta = 0,
                    SUM(DG.debito - DG.credito),
                    SUM(DG.credito - DG.debito)
                ) AS saldo")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->leftJoin('tipos_documentos AS TD', 'N.id_tipo_documento', 'TD.id')
            ->where('anulado', 0)
            ->when($this->request['id_nit'] ? $this->request['id_nit'] : false, function ($query) {
				$query->where('N.id', $this->request['id_nit']);
			})
            ->when($this->request['id_cuenta'] ? $this->request['id_cuenta'] : false, function ($query) {
				$query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
			})
            ->when($this->request['fecha_cartera'] ? $this->request['fecha_cartera'] : false, function ($query) {
				$query->where('DG.fecha_manual', '<=', $this->request['fecha_cartera']);
			})
            ->havingRaw("IF(PC.naturaleza_cuenta=0, SUM(DG.debito - DG.credito), SUM(DG.credito - DG.debito)) != 0")
            ->groupByRaw('DG.id_cuenta, DG.id_nit, DG.documento_referencia')
            ->orderByRaw('DG.fecha_manual')
            ->chunk(233, function ($documentos) {
                // dd($documentos);
                foreach ($documentos as $documento) {
                    //AGREGAR DETALLE DE CUENTAS PADRE
                    if($this->request['detallar_cartera'] == '1') {
                        $this->addTotalsPadresData($documento);
                    }
                    //AGREGAR DOCUMENTOS POR NIT
                    $this->addTotalNitsData($documento);
                }
            });
    }

    private function documentosCarteraDetalle()
    {
        $fecha = Carbon::now();

        DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                "N.id AS id_nit",
                "TD.nombre AS tipo_documento",
                "N.numero_documento",
                "N.id_ciudad",
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "N.razon_social",
                "N.telefono_1",
                "N.telefono_2",
                "N.email",
                "N.direccion",
                "N.plazo",
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.nombre AS nombre_cuenta",
                "DG.documento_referencia",
                "DG.id_centro_costos",
                "CC.codigo AS codigo_cecos",
                "CC.nombre AS nombre_cecos",
                "DG.id_comprobante AS id_comprobante",
                "CO.codigo AS codigo_comprobante",
                "CO.nombre AS nombre_comprobante",
                "CO.tipo_comprobante",
                "DG.consecutivo",
                "DG.concepto",
                "DG.fecha_manual",
                "DG.created_at",
                "PC.naturaleza_ingresos",
                "PC.naturaleza_egresos",
                "PC.naturaleza_compras",
                "PC.naturaleza_ventas",
                "PC.naturaleza_cuenta",
                "debito",
                "credito",
                DB::raw("DATEDIFF('$fecha', DG.fecha_manual) AS dias_cumplidos"),
                // "'detalle' AS detalle",
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                "DG.created_by",
                "DG.updated_by",
                DB::raw("IF(PC.naturaleza_cuenta = 0, credito, debito) AS total_abono"),
                DB::raw("IF(PC.naturaleza_cuenta = 0, debito, credito) AS total_facturas"),
                DB::raw("IF(
                    PC.naturaleza_cuenta = 0,
                    DG.debito - DG.credito,
                    DG.credito - DG.debito
                ) AS saldo")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->leftJoin('tipos_documentos AS TD', 'N.id_tipo_documento', 'TD.id')
            ->where('anulado', 0)
            ->when($this->request['id_nit'] ? $this->request['id_nit'] : false, function ($query) {
				$query->where('N.id', $this->request['id_nit']);
			})
            ->when($this->request['id_cuenta'] ? $this->request['id_cuenta'] : false, function ($query) {
				$query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
			})
            ->when($this->request['fecha_cartera'] ? $this->request['fecha_cartera'] : false, function ($query) {
				$query->where('DG.fecha_manual', '<=', $this->request['fecha_cartera']);
			})
            ->orderByRaw('DG.fecha_manual')
            ->chunk(233, function ($documentosDetalle) {
                foreach ($documentosDetalle as $documentoDetalle) {
                    $cuentaNumero = 1;
                    $cuentaNueva = $this->nuevaCuentaDetalle($documentoDetalle, $cuentaNumero);
                    while ($this->hasCuentaData($cuentaNueva)) {
                        $cuentaNumero++;
                        $cuentaNueva = $this->nuevaCuentaDetalle($documentoDetalle, $cuentaNumero);
                    }
                    //DETALLE DOCUMENTOS
                    $this->carteraCollection[$cuentaNueva] = [
                        'id_cartera' => $this->id_cartera,
                        'id_nit' => $documentoDetalle->id_nit,
                        'numero_documento' => '',
                        'nombre_nit' => '',
                        'razon_social' => '',
                        'plazo' => $documentoDetalle->plazo,
                        'id_cuenta' => $documentoDetalle->id_cuenta,
                        'cuenta' => '',
                        'nombre_cuenta' => '',
                        'documento_referencia' => '',
                        'codigo_comprobante' => $documentoDetalle->codigo_comprobante,
                        'nombre_comprobante' => $documentoDetalle->nombre_comprobante,
                        'concepto' => $documentoDetalle->concepto,
                        'fecha_manual' => $documentoDetalle->fecha_manual,
                        'dias_cumplidos' => $documentoDetalle->dias_cumplidos,
                        'fecha_creacion' => $documentoDetalle->fecha_creacion,
                        'fecha_edicion' => $documentoDetalle->fecha_edicion,
                        'created_by' => $documentoDetalle->created_by,
                        'updated_by' => $documentoDetalle->updated_by,
                        'total_abono' => $documentoDetalle->total_abono,
                        'total_facturas' => $documentoDetalle->total_facturas,
                        'saldo' => $documentoDetalle->saldo,
                        'detalle' => false,
                        'detalle_group' => false,
                    ];
                }
            });
    }

    private function documentosTotal()
    {
        $fecha = Carbon::now();

        $totalDocumentos = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                "N.id AS id_nit",
                "TD.nombre AS tipo_documento",
                "N.numero_documento",
                "N.id_ciudad",
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "N.razon_social",
                "N.telefono_1",
                "N.telefono_2",
                "N.email",
                "N.direccion",
                "N.plazo",
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.nombre AS nombre_cuenta",
                "DG.documento_referencia",
                "DG.id_centro_costos",
                "CC.codigo AS codigo_cecos",
                "CC.nombre AS nombre_cecos",
                "DG.id_comprobante AS id_comprobante",
                "CO.codigo AS codigo_comprobante",
                "CO.nombre AS nombre_comprobante",
                "CO.tipo_comprobante",
                "DG.consecutivo",
                "DG.concepto",
                "DG.fecha_manual",
                "DG.created_at",
                DB::raw("SUM(DG.debito) AS debito"),
                DB::raw("SUM(DG.credito) AS credito"),
                DB::raw("DATEDIFF('$fecha', DG.fecha_manual) AS dias_cumplidos"),
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                "DG.created_by",
                "DG.updated_by",
                DB::raw("IF(PC.naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono"),
                DB::raw("IF(PC.naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas"),
                DB::raw("IF(
                    PC.naturaleza_cuenta = 0,
                    SUM(DG.debito - DG.credito),
                    SUM(DG.credito - DG.debito)
                ) AS saldo")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->leftJoin('tipos_documentos AS TD', 'N.id_tipo_documento', 'TD.id')
            ->where('anulado', 0)
            ->when($this->request['id_nit'] ? $this->request['id_nit'] : false, function ($query) {
				$query->where('N.id', $this->request['id_nit']);
			})
            ->when($this->request['id_cuenta'] ? $this->request['id_cuenta'] : false, function ($query) {
				$query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
			})
            ->when($this->request['fecha_cartera'] ? $this->request['fecha_cartera'] : false, function ($query) {
				$query->where('DG.fecha_manual', '<=', $this->request['fecha_cartera']);
			})
            ->groupByRaw('DG.id')
            ->orderByRaw('DG.fecha_manual')
            ->get();
        //TOTAL DOCUMENTOS
        $this->carteraCollection['9999'] = [
            'id_cartera' => $this->id_cartera,
            'id_nit' => '',
            'numero_documento' => '',
            'nombre_nit' => '',
            'razon_social' => '',
            'plazo' => '',
            'id_cuenta' => '',
            'cuenta' => 'TOTALES',
            'nombre_cuenta' => '',
            'documento_referencia' => '',
            'codigo_comprobante' => '',
            'nombre_comprobante' => '',
            'concepto' => '',
            'fecha_manual' => '',
            'dias_cumplidos' => '',
            'fecha_creacion' => NULL,
            'fecha_edicion' => NULL,
            'created_by' => NULL,
            'updated_by' => NULL,
            'total_abono' => $totalDocumentos->sum('total_abono'),
            'total_facturas' => $totalDocumentos->sum('total_facturas'),
            'saldo' => $totalDocumentos->sum('saldo'),
            'detalle' => 'si',
            'detalle_group' => false,
        ];
    }

    private function addTotalsPadresData($documento)
    {
        $cuentasAsociadas = $this->getCuentas($documento->cuenta); //return ARRAY PADRES CUENTA
        foreach ($cuentasAsociadas as $cuenta) {
            if ($this->hasCuentaData($cuenta)) $this->sumCuentaData($cuenta, $documento);
            else $this->newCuentaData($cuenta, $documento, $cuentasAsociadas);
        }
    }

    private function addTotalNitsData($documento)
    {
        $cuentaNumero = 1;
        $cuentaNueva = $this->nuevaCuenta($documento, $cuentaNumero);
        while ($this->hasCuentaData($cuentaNueva)) {
            $cuentaNumero++;
            $cuentaNueva = $this->nuevaCuenta($documento, $cuentaNumero);
        }
        //TOTAL POR NITS
        $this->carteraCollection[$cuentaNueva] = [
            'id_cartera' => $this->id_cartera,
            'id_nit' => $documento->id_nit,
            'numero_documento' => $documento->numero_documento,
            'nombre_nit' => $documento->nombre_nit,
            'razon_social' => $documento->razon_social,
            'plazo' => $documento->plazo,
            'id_cuenta' => $documento->id_cuenta,
            'cuenta' => $documento->cuenta,
            'nombre_cuenta' => $documento->nombre_cuenta,
            'documento_referencia' => $documento->documento_referencia,
            'codigo_comprobante' => $documento->codigo_comprobante,
            'nombre_comprobante' => $documento->nombre_comprobante,
            'concepto' => $documento->concepto,
            'fecha_manual' => $documento->fecha_manual,
            'dias_cumplidos' => $documento->dias_cumplidos,
            'fecha_creacion' => $documento->fecha_creacion,
            'fecha_edicion' => $documento->fecha_edicion,
            'created_by' => $documento->created_by,
            'updated_by' => $documento->updated_by,
            'total_abono' => $documento->total_abono,
            'total_facturas' => $documento->total_facturas,
            'saldo' => $documento->saldo,
            'detalle' => false,
            'detalle_group' => 'nits',
        ];
    }

    private function newCuentaData($cuenta, $documento, $cuentasAsociadas)
    {
        $detalle = false;
        $detalleGroup = false;

        if(strlen($cuenta) >= strlen($cuentasAsociadas[count($cuentasAsociadas)-1])){
            $detalle = true;
        }

        if(strlen($cuenta) >= strlen($cuentasAsociadas[count($cuentasAsociadas)-2])){
            $detalleGroup = true;
        }
        
        $cuentaData = PlanCuentas::whereCuenta($cuenta)->first();
        if(!$cuentaData){
            return;
        }

        $this->carteraCollection[$cuenta] = [
            'id_cartera' => $this->id_cartera,
            'id_nit' => '',
            'numero_documento' => '',
            'nombre_nit' => '',
            'razon_social' => '',
            'plazo' => '',
            'id_cuenta' => $cuentaData->id,
            'cuenta' => $cuentaData->cuenta,
            'nombre_cuenta' => $cuentaData->nombre,
            'documento_referencia' => '',
            'codigo_comprobante' => '',
            'nombre_comprobante' => '',
            'concepto' => '',
            'fecha_manual' => '',
            'dias_cumplidos' => '',
            'fecha_creacion' => NULL,
            'fecha_edicion' => NULL,
            'created_by' => NULL,
            'updated_by' => NULL,
            'total_abono' => number_format((float)$documento->total_abono, 2, '.', ''),
            'total_facturas' => number_format((float)$documento->total_facturas, 2, '.', ''),
            'saldo' => number_format((float)$documento->saldo, 2, '.', ''),
            'detalle' => $detalle,
            'detalle_group' => $detalleGroup,
        ];
    }

    private function getCuentas($cuenta)
    {
        $dataCuentas = NULL;

        if(strlen($cuenta) > 6){
            $dataCuentas =[
                mb_substr($cuenta, 0, 1),
                mb_substr($cuenta, 0, 2),
                mb_substr($cuenta, 0, 4),
                mb_substr($cuenta, 0, 6),
                $cuenta,
            ];
        } else if (strlen($cuenta) > 4) {
            $dataCuentas =[
                mb_substr($cuenta, 0, 1),
                mb_substr($cuenta, 0, 2),
                mb_substr($cuenta, 0, 4),
                $cuenta,
            ];
        } else if (strlen($cuenta) > 2) {
            $dataCuentas =[
                mb_substr($cuenta, 0, 1),
                mb_substr($cuenta, 0, 2),
                $cuenta,
            ];
        } else if (strlen($cuenta) > 1) {
            $dataCuentas =[
                mb_substr($cuenta, 0, 1),
                $cuenta,
            ];
        } else {
            $dataCuentas =[
                $cuenta,
            ];
        }

        return $dataCuentas;
    }

    private function nuevaCuenta($documento, $cuentaNumero)
    {
        return $documento->cuenta.'-'.
            $documento->id_nit.'B'.
            $documento->documento_referencia.'A'.
            $cuentaNumero.'B';
    }

    private function nuevaCuentaDetalle($documento, $cuentaNumero)
    {
        return $documento->cuenta.'-'.
            $documento->id_nit.'B'.
            $documento->documento_referencia.'B'.
            $cuentaNumero.'B';
    }

    private function sumCuentaData($cuenta, $extracto)
    {
        $this->carteraCollection[$cuenta]['total_abono']+= number_format((float)$extracto->total_abono, 2, '.', '');
        $this->carteraCollection[$cuenta]['total_facturas']+= number_format((float)$extracto->total_facturas, 2, '.', '');
        $this->carteraCollection[$cuenta]['saldo']+= number_format((float)$extracto->saldo, 2, '.', '');
    }

    private function hasCuentaData($cuenta)
	{
		return isset($this->carteraCollection[$cuenta]);
	}


}

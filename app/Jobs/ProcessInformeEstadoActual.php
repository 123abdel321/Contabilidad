<?php

namespace App\Jobs;

use DB;
use Exception;
use Illuminate\Bus\Queueable;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Informes\InfEstadoActual;

class ProcessInformeEstadoActual implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $request;
    public $errores;
    public $id_usuario;
	public $id_empresa;
    public $id_estado_actual;
    public $estadoActualCollection = [];
    public $meses = ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];

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

            $estadoActual = InfEstadoActual::create([
                'id_empresa' => $this->id_empresa,
                'year' => $this->request['year'],
                'id_comprobante' => $this->request['id_comprobante']
            ]);

            $this->id_estado_actual = $estadoActual->id;

            $this->documentosEstadoActual();

            $collectionEstadoActual = $this->ordenarData();

            DB::connection('informes')->table('inf_estado_actual_detalles')->insert($collectionEstadoActual);

            DB::connection('informes')->commit();

            event(new PrivateMessageEvent('informe-estado-actual-'.$empresa->token_db.'_'.$this->id_usuario, [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con exito!',
                'titulo' => 'Estado actual generado',
                'id_estado_actual' => $this->id_estado_actual,
                'autoclose' => false
            ]));

        } catch (Exception $exception) {
            DB::connection('informes')->rollback();

			throw $exception;
        }
    }

    private function documentosEstadoActual()
    {
        DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'fecha_manual',
                DB::raw("DATE_FORMAT(fecha_manual, '%m') mes"),
                DB::raw("DATE_FORMAT(fecha_manual, '%Y') year"),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(debito) - SUM(credito) AS diferencia'),
                DB::raw("COUNT(id) registros")
            )
            ->where('anulado', 0)
            ->when($this->request['year'] ? $this->request['year'] : false, function ($query) {
                $query->whereYear('fecha_manual', '=', $this->request['year']);
            })
            ->when($this->request['id_comprobante'] ? $this->request['id_comprobante'] : false, function ($query) {
                $query->where('id_comprobante', '=', $this->request['id_comprobante']);
            })
            ->orderBy('fecha_manual', 'ASC')
            ->groupby(
                DB::raw("DATE_FORMAT(fecha_manual, '%m')"),
                DB::raw("DATE_FORMAT(fecha_manual, '%Y')")
            )
            ->chunk(233, function ($documentos) {
                foreach ($documentos as $documento) {

                    $inicioMes = date('Y-m-01', strtotime($documento->fecha_manual));
			        $finMes = date("Y-m-t", strtotime($documento->fecha_manual));

                    $documento->mes = $this->meses[intval($documento->mes)-1];
                    $documento->errores = $this->getErrores($inicioMes, $finMes);
                    $documento->documentos = $this->getDocumentos($inicioMes, $finMes);
                    $documento->comprobantes = $this->getComprobantes($inicioMes, $finMes);

                    $this->estadoActualCollection[] = $documento;
                }
            });
    }

    private function getErrores($inicioMes, $finMes)
    {
        $this->errores = 0;

        DB::connection('sam')->table('documentos_generals AS DG')
            ->join('plan_cuentas', 'DG.id_cuenta', 'plan_cuentas.id')
                ->where(function ($query) use ($inicioMes, $finMes) {
                    $query->where('DG.fecha_manual', '>=', $inicioMes)
                        ->where('DG.fecha_manual', '<=', $finMes);
            })
            ->where('anulado', 0)
            ->when($this->request['year'] ? $this->request['year'] : false, function ($query) {
                $query->whereYear('DG.fecha_manual', '=', $this->request['year']);
            })
            ->when($this->request['id_comprobante'] ? $this->request['id_comprobante'] : false, function ($query) {
                $query->where('id_comprobante', '=', $this->request['id_comprobante']);
            })
            ->orderBy('DG.id')
            ->chunk(233, function ($documentos) {

                foreach ($documentos as $documento) {
                    if($documento->exige_nit && !$documento->id_nit){
                        $this->errores++;
                    }
        
                    if($documento->exige_documento_referencia && !$documento->documento_referencia){
                        $this->errores++;
                    }
        
                    if($documento->exige_concepto && !$documento->concepto){
                        $this->errores++;
                    }
        
                    if($documento->exige_centro_costos && !$documento->id_centro_costos){
                        $this->errores++;
                    }
                }
            });

        return $this->errores;
    }

    private function getDocumentos($inicioMes, $finMes)
    {
        $query = DB::connection('sam')->table('documentos_generals AS DG')
            ->join('plan_cuentas', 'DG.id_cuenta', 'plan_cuentas.id')
                ->where(function ($query) use ($inicioMes, $finMes) {
                    $query->where('DG.fecha_manual', '>=', $inicioMes)
                        ->where('DG.fecha_manual', '<=', $finMes);
            })
            ->where('anulado', 0)
            ->when($this->request['year'] ? $this->request['year'] : false, function ($query) {
                $query->whereYear('fecha_manual', '=', $this->request['year']);
            })
            ->when($this->request['id_comprobante'] ? $this->request['id_comprobante'] : false, function ($query) {
                $query->where('id_comprobante', '=', $this->request['id_comprobante']);
            })
            ->orderBy('DG.id')
            ->groupBy('DG.documento_referencia');

        return $query->count();
    }

    private function getComprobantes($inicioMes, $finMes)
    {
        $query = DB::connection('sam')->table('documentos_generals AS DG')
            ->join('plan_cuentas', 'DG.id_cuenta', 'plan_cuentas.id')
                ->where(function ($query) use ($inicioMes, $finMes) {
                    $query->where('DG.fecha_manual', '>=', $inicioMes)
                        ->where('DG.fecha_manual', '<=', $finMes);
            })
            ->where('anulado', 0)
            ->when($this->request['year'] ? $this->request['year'] : false, function ($query) {
                $query->whereYear('fecha_manual', '=', $this->request['year']);
            })
            ->when($this->request['id_comprobante'] ? $this->request['id_comprobante'] : false, function ($query) {
                $query->where('id_comprobante', '=', $this->request['id_comprobante']);
            })
            ->orderBy('DG.id')
            ->groupBy('id_comprobante');

        return $query->count();
    }

    private function ordenarData()
    {
        $agrupado = [];
		$ordenado = [];
        $totals2 = [
			"id_estado_actual" => $this->id_estado_actual,
			"mes" => 'TOTALES',
			"year" => '',
			"debito" => 0,
			"credito" => 0,
			"diferencia" => 0,
			"registros" => 0,
			"errores" => 0,
			"documentos" => 0,
			"comprobantes" => 0,
			"total" => 2,
		];

        foreach($this->estadoActualCollection as $estadoActual){
			$agrupado[$estadoActual->year][] = $estadoActual;
		}

        ksort($agrupado);

        foreach ($agrupado as $year) {

			$totals = [
				"id_estado_actual" => $this->id_estado_actual,
				"mes" => 'TOTALES '. $year[0]->year,
				"year" => '',
				"debito" => 0,
				"credito" => 0,
				"diferencia" => 0,
				"registros" => 0,
				"errores" => 0,
				"documentos" => 0,
				"comprobantes" => 0,
				"total" => 1,
			];

			foreach ($year as $data) {
				$totals['debito']+= $data->debito;
				$totals['credito']+= $data->credito;
				$totals['diferencia']+= $data->diferencia;
				$totals['registros']+= $data->registros;
				$totals['errores']+= $data->errores;
				$totals['documentos']+= $data->documentos;
				$totals['comprobantes']+= $data->comprobantes;

				$totals2['debito']+= $data->debito;
				$totals2['credito']+= $data->credito;
				$totals2['diferencia']+= $data->diferencia;
				$totals2['registros']+= $data->registros;
				$totals2['errores']+= $data->errores;
				$totals2['documentos']+= $data->documentos;
				$totals2['comprobantes']+= $data->comprobantes;
				array_push($ordenado, $this->dataEstadoActual($data));
			}
			if(!$this->request['year']){
				array_push($ordenado, $totals);
			}
		}

		array_push($ordenado, $totals2);

		return $ordenado;
    }

    private function dataEstadoActual($data){
		return [
			'id_estado_actual' => $this->id_estado_actual,
			'mes' => $data->mes,
			'year' => $data->year,
			'debito' => $data->debito,
			'credito' => $data->credito,
			'diferencia' => $data->diferencia,
			'registros' => $data->registros,
			'errores' => $data->errores,
			'documentos' => $data->documentos,
			'comprobantes' => $data->comprobantes,
			'total' => 0
		];
	}

}
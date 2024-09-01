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
use App\Models\Informes\InfEstadoComprobante;

class ProcessInformeEstadoComprobante implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $request;
    public $errores;
    public $id_usuario;
	public $id_empresa;
    public $id_estado_comprobante;
    public $estadoComprobanteCollection = [];
    public $tipoComprobante = ['Ingresos', 'Egresos', 'Compras', 'Ventas', 'Otros', 'Cierre'];

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

            $estadoComprobante = InfEstadoComprobante::create([
                'id_empresa' => $this->id_empresa,
                'year' => $this->request['year'],
                'month' => $this->request['month']
            ]);

            $this->id_estado_comprobante = $estadoComprobante->id;

            $this->documentosEstadoComprobantes();

            $collectionEstadoComprobante = $this->ordenarData();

            DB::connection('informes')->table('inf_estado_comprobante_detalles')->insert($collectionEstadoComprobante);

            DB::connection('informes')->commit();

            event(new PrivateMessageEvent('informe-estado-comprobante-'.$empresa->token_db.'_'.$this->id_usuario, [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con exito!',
                'titulo' => 'Estado comprobante generado',
                'id_estado_comprobante' => $this->id_estado_comprobante,
                'autoclose' => false
            ]));

        } catch (Exception $exception) {
            DB::connection('informes')->rollback();

			throw $exception;
        }
    }

    private function documentosEstadoComprobantes()
    {
        DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'DG.id_comprobante',
                'DG.fecha_manual',
                'comprobantes.codigo AS codigo_comprobante',
                'comprobantes.nombre AS nombre_comprobante',
                'comprobantes.tipo_comprobante AS nombre_tipo_comprobante',
                DB::raw("DATE_FORMAT(DG.fecha_manual, '%Y') year"),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(debito) - SUM(credito) AS diferencia'),
                DB::raw("COUNT(DG.id) registros")
            )
            ->leftJoin('comprobantes', 'DG.id_comprobante', 'comprobantes.id')
            ->when($this->request['year'] ? $this->request['year'] : false, function ($query) {
                $query->whereYear('DG.fecha_manual', '=', $this->request['year']);
            })
            ->when($this->request['month'] ? $this->request['month'] : false, function ($query) {
                $query->whereMonth('DG.fecha_manual', '=', $this->request['month']);
            })
            ->orderBy('fecha_manual', 'ASC')
            ->groupby(
                'DG.id_comprobante'
            )
            ->chunk(233, function ($documentos) {
                foreach ($documentos as $documento) {

                    $documento->errores = $this->getErrores($documento->id_comprobante);
                    $documento->documentos = $this->getDocumentos($documento->id_comprobante);

                    $this->estadoComprobanteCollection[] = $documento;
                }
            });
    }

    private function getErrores($id_comprobante)
    {
        $this->errores = 0;

        DB::connection('sam')->table('documentos_generals AS DG')
            ->join('plan_cuentas', 'DG.id_cuenta', 'plan_cuentas.id')
            ->where('anulado', 0)
            ->when($this->request['year'] ? $this->request['year'] : false, function ($query) {
                $query->whereYear('DG.fecha_manual', '=', $this->request['year']);
            })
            ->when($this->request['month'] ? $this->request['month'] : false, function ($query) {
                $query->whereMonth('DG.fecha_manual', '=', $this->request['month']);
            })
            ->where('id_comprobante', '=', $id_comprobante)
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

    private function getDocumentos($id_comprobante)
    {
        $query = DB::connection('sam')->table('documentos_generals AS DG')
            ->join('plan_cuentas', 'DG.id_cuenta', 'plan_cuentas.id')
            ->where('anulado', 0)
            ->when($this->request['year'] ? $this->request['year'] : false, function ($query) {
                $query->whereYear('DG.fecha_manual', '=', $this->request['year']);
            })
            ->when($this->request['month'] ? $this->request['month'] : false, function ($query) {
                $query->whereMonth('DG.fecha_manual', '=', $this->request['month']);
            })
            ->where('id_comprobante', '=', $id_comprobante)
            ->groupBy('DG.documento_referencia')
            ->get();

        return count($query);
    }

    private function ordenarData()
    {
        $ordenado = [];
		$totals = [
			"id_estado_comprobante" => $this->id_estado_comprobante,
			"codigo_comprobante" => "TOTALES",
			"nombre_comprobante" => "",
			"year" => "",
			"documentos" => 0,
			"registros" => 0,
			"debito" => 0,
			"credito" => 0,
			"diferencia" => 0,
			"nombre_tipo_comprobante" => "",
			"errores" => 0,
			"total" => 2
		];

		foreach($this->estadoComprobanteCollection as $estadoComprobantes){

			$totals['debito']+= $estadoComprobantes->debito;
			$totals['credito']+= $estadoComprobantes->credito;
			$totals['diferencia']+= $estadoComprobantes->diferencia;
			$totals['registros']+= $estadoComprobantes->registros;
			$totals['errores']+= $estadoComprobantes->errores;
			$totals['documentos']+= $estadoComprobantes->documentos;

			array_push($ordenado, $this->dataEstadoComprobantes($estadoComprobantes));
		}

		array_push($ordenado, $totals);

		return $ordenado;
    }

    private function dataEstadoComprobantes($data){
		return [
			'id_estado_comprobante' => $this->id_estado_comprobante,
			'codigo_comprobante' => $data->codigo_comprobante,
			'nombre_comprobante' => $data->nombre_comprobante,
			'year' => $data->year,
			'documentos' => $data->documentos,
			'registros' => $data->registros,
			'debito' => $data->debito,
			'credito' => $data->credito,
			'diferencia' => $data->diferencia,
			'nombre_tipo_comprobante' => $this->tipoComprobante[$data->nombre_tipo_comprobante],
			'errores' => $data->errores,
			'total' => 0
		];
	}

}
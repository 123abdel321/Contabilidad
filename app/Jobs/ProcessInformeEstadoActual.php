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
    public $faltantes;
    public $consecutivoActual;
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
                'month' => $this->request['month'],
                'detalle' => $this->request['detalle'],
                'id_comprobante' => $this->request['id_comprobante']
            ]);

            $this->id_estado_actual = $estadoActual->id;

            $this->documentosEstadoActual();
            $this->totalMesesEstadoActual();
            $estadoActualCollection = $this->ordenarData();            
            
            foreach (array_chunk($estadoActualCollection,233) as $data){
                DB::connection('informes')
                    ->table('inf_estado_actual_detalles')
                    ->insert($data);
            }

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
                'DG.fecha_manual',
                'DG.id_comprobante',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                DB::raw("0 AS documentos"),
                DB::raw("CONCAT(CO.codigo, ' - ', CO.nombre) AS comprobantes"),
                DB::raw("DATE_FORMAT(fecha_manual, '%m') AS mes"),
                DB::raw("DATE_FORMAT(fecha_manual, '%Y') AS year"),
                DB::raw("DATE_FORMAT(fecha_manual, '%m') AS meses"),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(debito) - SUM(credito) AS diferencia'),
                'DG.concepto',
                DB::raw("COUNT(DG.id) registros")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('anulado', 0)
            ->when($this->request['year'] ? $this->request['year'] : false, function ($query) {
                $query->whereYear('fecha_manual', '=', $this->request['year']);
            })
            ->when($this->request['month'] ? $this->request['month'] : false, function ($query) {
                $query->whereMonth('DG.fecha_manual', '=', $this->request['month']);
            })
            ->when($this->request['id_comprobante'] ? $this->request['id_comprobante'] : false, function ($query) {
                $query->where('DG.id_comprobante', '=', $this->request['id_comprobante']);
            })
            ->orderByRaw('DG.fecha_manual, CO.codigo, DG.consecutivo ASC')
            ->groupby(
                DB::raw("DATE_FORMAT(fecha_manual, '%m')"),
                DB::raw("DATE_FORMAT(fecha_manual, '%Y')"),
                'id_comprobante'
            )
            ->chunk(987, function ($documentos) {
                
                foreach ($documentos as $documento) {

                    $inicioMes = date('Y-m', strtotime($documento->fecha_manual));
			        $finMes = date("Y-m-t", strtotime($documento->fecha_manual));

                    $documento->mes = $this->meses[intval($documento->mes)-1];
                    [$erroresCount, $errores] = $this->getErrores($inicioMes.'-01', $finMes, $documento->id_comprobante);
                    $documento->errores = $erroresCount;
                    $documento->total = 2;

                    $this->estadoActualCollection[$inicioMes.'-A_'.$documento->id_comprobante] = $documento;
                    if ($this->request['detalle']) $this->documentosDetalle($documento->id_comprobante, $inicioMes, $finMes);
                }
            });
    }

    private function totalMesesEstadoActual()
    {
        DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'DG.fecha_manual',
                'DG.id_comprobante',
                DB::raw("0 AS documentos"),
                DB::raw("'' AS comprobantes"),
                DB::raw("DATE_FORMAT(fecha_manual, '%m') AS mes"),
                DB::raw("DATE_FORMAT(fecha_manual, '%Y') AS year"),
                DB::raw("DATE_FORMAT(fecha_manual, '%m') AS meses"),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(debito) - SUM(credito) AS diferencia'),
                'DG.concepto',
                DB::raw("COUNT(DG.id) registros")
            )
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('anulado', 0)
            ->when($this->request['year'] ? $this->request['year'] : false, function ($query) {
                $query->whereYear('fecha_manual', '=', $this->request['year']);
            })
            ->when($this->request['month'] ? $this->request['month'] : false, function ($query) {
                $query->whereMonth('DG.fecha_manual', '=', $this->request['month']);
            })
            ->when($this->request['id_comprobante'] ? $this->request['id_comprobante'] : false, function ($query) {
                $query->where('DG.id_comprobante', '=', $this->request['id_comprobante']);
            })
            ->orderByRaw('DG.fecha_manual, CO.codigo, DG.consecutivo ASC')
            ->groupby(
                DB::raw("DATE_FORMAT(fecha_manual, '%m')"),
                DB::raw("DATE_FORMAT(fecha_manual, '%Y')")
            )
            ->chunk(987, function ($documentos) {
                foreach ($documentos as $documento) {
                    $inicioMes = date('Y-m', strtotime($documento->fecha_manual));
			        $finMes = date("Y-m-t", strtotime($documento->fecha_manual));

                    $documento->mes = $this->meses[intval($documento->mes)-1];
                    [$erroresCount, $errores] = $this->getErrores($inicioMes.'-01', $finMes);
                    $documento->errores = $erroresCount;
                    $documento->total = 4;
                    $this->estadoActualCollection[$inicioMes.'-A99999999_99999999'] = $documento;
                }
            });
    }

    private function totalYearsEstadoActual()
    {
        $total = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                DB::raw("0 AS documentos"),
                DB::raw("'' AS comprobantes"),
                DB::raw("'' AS mes"),
                DB::raw("DATE_FORMAT(fecha_manual, '%Y') AS year"),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(debito) - SUM(credito) AS diferencia'),
                DB::raw("COUNT(DG.id) registros")
            )
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('anulado', 0)
            ->when($this->request['year'] ? $this->request['year'] : false, function ($query) {
                $query->whereYear('fecha_manual', '=', $this->request['year']);
            })
            ->when($this->request['month'] ? $this->request['month'] : false, function ($query) {
                $query->whereMonth('DG.fecha_manual', '=', $this->request['month']);
            })
            ->when($this->request['id_comprobante'] ? $this->request['id_comprobante'] : false, function ($query) {
                $query->where('DG.id_comprobante', '=', $this->request['id_comprobante']);
            })
            ->orderByRaw('DG.fecha_manual, CO.codigo, DG.consecutivo ASC')
            ->groupby(
                DB::raw("DATE_FORMAT(fecha_manual, '%Y')")
            )
            ->first();

        if ($total) {
            $total->mes = 'TOTALES '. $total->year;
            [$erroresCount, $errores] = $this->getErrores(null, null, null, null, $total->year);
            $total->errores = $erroresCount;
            $total->total = 1;
            return $total;
        } else {
            return null;
        }
    }

    private function getErrores($inicioMes = null, $finMes = null, $idComprobante = null, $consecutivo = null, $year = null)
    {
        $this->errores = '';
        $this->erroresCount = 0;

        DB::connection('sam')->table('documentos_generals AS DG')
            ->join('plan_cuentas', 'DG.id_cuenta', 'plan_cuentas.id')
            ->where(function ($query) use ($inicioMes, $finMes, $year) {
                if ($inicioMes && $finMes) {
                    $query->where('DG.fecha_manual', '>=', $inicioMes)
                        ->where('DG.fecha_manual', '<=', $finMes);
                }
                if ($year) {
                    $query->whereYear('DG.fecha_manual', $year);
                }
            })
            ->where('anulado', 0)
            ->when($idComprobante ? $idComprobante : false, function ($query) use ($idComprobante) {
                $query->where('DG.id_comprobante', '=', $idComprobante);
            })
            ->when($consecutivo ? $consecutivo : false, function ($query) use ($consecutivo) {
                $query->where('DG.consecutivo', '=', $consecutivo);
            })
            ->orderBy('DG.id')
            ->chunk(233, function ($documentos) {

                foreach ($documentos as $documento) {
                    if($documento->exige_nit && !$documento->id_nit){
                        $this->errores.=" La cuenta {$documento->cuenta} exige nit <br/>";
                        $this->erroresCount++;
                    }
        
                    if($documento->exige_documento_referencia && !$documento->documento_referencia){
                        $this->errores.=" La cuenta {$documento->cuenta} exige documento referencia <br/>";
                        $this->erroresCount++;
                    }
        
                    if($documento->exige_concepto && !$documento->concepto){
                        $this->errores.=" La cuenta {$documento->cuenta} exige concepto <br/>";
                        $this->erroresCount++;
                    }
        
                    if($documento->exige_centro_costos && !$documento->id_centro_costos){
                        $this->errores.=" La cuenta {$documento->cuenta} exige centro costos <br/>";
                        $this->erroresCount++;
                    }
                }
            });

        return [$this->erroresCount, $this->errores];
    }

    private function getDocumentos($idComprobante, $inicioMes, $finMes)
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
            ->where('id_comprobante', '=', $idComprobante)
            ->orderBy('DG.id')
            ->groupBy('DG.documento_referencia', 'DG.id_comprobante')
            ->get();

        return count($query);
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
            ->groupBy('id_comprobante')
            ->get();

        return count($query);
    }

    private function ordenarData()
    {
		$ordenado = [];

        uksort($this->estadoActualCollection, function($a, $b) {
            // Extraer las partes de cada clave
            $partsA = explode('_', $a);
            $partsB = explode('_', $b);
            
            // Comparar la parte inicial (año-mes)
            $cmpDate = strcmp($partsA[0], $partsB[0]);
            if ($cmpDate !== 0) {
                return $cmpDate;
            }
            
            // Manejar el caso especial de 99999999 (debe ir al final de su grupo)
            $isSpecialA = strpos($a, '99999999') !== false;
            $isSpecialB = strpos($b, '99999999') !== false;
            
            if ($isSpecialA && !$isSpecialB) return 1;  // A es especial, va después
            if (!$isSpecialA && $isSpecialB) return -1; // B es especial, va después
            
            // Comparar el número principal del documento (segunda parte)
            $numA = isset($partsA[1]) ? (int)$partsA[1] : 0;
            $numB = isset($partsB[1]) ? (int)$partsB[1] : 0;
            
            if ($numA !== $numB) {
                return $numA - $numB;
            }
            
            // Si tienen el mismo número principal, comparar los subdocumentos
            $subA = isset($partsA[2]) ? (int)$partsA[2] : 0;
            $subB = isset($partsB[2]) ? (int)$partsB[2] : 0;
            
            return $subA - $subB;
        });

        foreach($this->estadoActualCollection as $estadoActual){
			$ordenado[] = [
                'id_estado_actual' => $this->id_estado_actual,
                'fecha_manual' => !$estadoActual->total ? $estadoActual->fecha_manual : null,
                'numero_documento' => !$estadoActual->total ? $estadoActual->numero_documento : null,
                'nombre_nit' => !$estadoActual->total ? $estadoActual->nombre_nit : null,
                'documentos' => $estadoActual->documentos,
                'comprobantes' => $estadoActual->comprobantes,
                'mes' => $estadoActual->total == 2 || $estadoActual->total == 4 ? $estadoActual->mes : '',
                'year' => $estadoActual->year,
                'debito' => $estadoActual->debito,
                'credito' => $estadoActual->credito,
                'diferencia' => $estadoActual->diferencia,
                'registros' => $estadoActual->registros,
                'concepto' => !$estadoActual->total ? $estadoActual->concepto : null,
                'errores' => $estadoActual->errores,
                'total' => $estadoActual->total,
            ];
		}

        $estadoActual = $this->totalYearsEstadoActual();


        $ordenado[] = [
            'id_estado_actual' => $this->id_estado_actual,
            'fecha_manual' => '',
            'numero_documento' => '',
            'nombre_nit' => '',
            'documentos' => isset($estadoActual) ? $estadoActual->documentos : '',
            'comprobantes' => isset($estadoActual) ? $estadoActual->comprobantes : '',
            'mes' => isset($estadoActual) ? $estadoActual->total == 2 || $estadoActual->total == 4 ? $estadoActual->mes : '' : '',
            'year' => isset($estadoActual) ? $estadoActual->year : '',
            'debito' => isset($estadoActual) ? $estadoActual->debito : '',
            'credito' => isset($estadoActual) ? $estadoActual->credito : '',
            'diferencia' => isset($estadoActual) ? $estadoActual->diferencia : '',
            'registros' => isset($estadoActual) ? $estadoActual->registros : '',
            'concepto' => '',
            'errores' => isset($estadoActual) ? $estadoActual->errores : '',
            'total' => isset($estadoActual) ? $estadoActual->total : '',
        ];

		return $ordenado;
    }

    private function dataEstadoActual($data)
    {
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

    private function documentosDetalle($idComprobante, $inicioMes, $finMes)
    {
        $this->faltantes = 0;
        $this->consecutivoActual = 0;
        DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'DG.fecha_manual',
                'DG.id_comprobante',
                'DG.consecutivo AS documentos',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                DB::raw("'' AS comprobantes"),
                DB::raw("'' AS mes"),
                DB::raw("DATE_FORMAT(fecha_manual, '%Y') AS year"),
                DB::raw("'' AS meses"),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(debito) - SUM(credito) AS diferencia'),
                'DG.concepto',
                DB::raw("COUNT(DG.id) registros")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->where(function ($query) use ($inicioMes, $finMes) {
                $query->where('DG.fecha_manual', '>=', $inicioMes.'-01')
                    ->where('DG.fecha_manual', '<=', $finMes);
            })
            ->where('anulado', 0)
            ->where('id_comprobante', '=', $idComprobante)
            ->orderByRaw('CAST(DG.consecutivo AS UNSIGNED)')
            ->groupby(
                'DG.consecutivo',
                'DG.id_comprobante'
            )
            ->chunk(233, function ($documentos) use ($inicioMes) {

                foreach ($documentos as $documento) {
                    if ($this->consecutivoActual && $this->consecutivoActual + 1 != intval($documento->documentos)){
                        $diferencia = intval($documento->documentos) - intval($this->consecutivoActual + 1);
                        $diferencia = $diferencia < 0 ? $diferencia * -1 : $diferencia;
                        $this->estadoActualCollection[$inicioMes.'-A_'.$documento->id_comprobante.'_'.$this->consecutivoActual + 1] = (object)[
                            'id_comprobante' => '',
                            'comprobantes' => '',
                            'fecha_manual' => '',
                            'numero_documento' => '',
                            'nombre_nit' => '',
                            'mes' => '',
                            'year' => $documento->year,
                            'meses' => '',
                            'debito' => '',
                            'credito' => '',
                            'diferencia' => '',
                            'concepto' => '',
                            'registros' => '',
                            'total' => '3',
                            'mes' => '',
                            'errores' => 0,
                            'documentos' => 'Faltantes ('.$diferencia.')',
                        ];
                    }

                    $this->consecutivoActual = intval($documento->documentos);
                    [$erroresCount, $errores] = $this->getErrores(null, null, $documento->id_comprobante, $documento->documentos);
                    $documento->errores = $errores;
                    $documento->total = 0;

                    $this->estadoActualCollection[$inicioMes.'-A_'.$documento->id_comprobante.'_'.$documento->documentos] = $documento;
                }
            });
    }

    private function totalDocumentosFecha($year)
    {
        $data = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'DG.id_comprobante',
                DB::raw("0 AS documentos"),
                DB::raw("'' AS comprobantes"),
                DB::raw("'' AS mes"),
                DB::raw("'' AS year"),
                DB::raw("'' AS meses"),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(debito) - SUM(credito) AS diferencia'),
                DB::raw("COUNT(DG.id) registros")
            )
            ->where(function ($query) use ($year) {
                $query->whereYear('DG.fecha_manual', $year);
            })
            ->where('anulado', 0)
            ->orderByRaw('DG.fecha_manual ASC')
            ->groupby(
                DB::raw("DATE_FORMAT(fecha_manual, '%Y')")
            )
            ->get();

        $data[0]->mes = 'TOTALES '. $year;
        [$erroresCount, $errores] = $this->getErrores(null, null, null, null, $data[0]->year);
        $data[0]->errores = $erroresCount;
        $data[0]->total = 1;
        return $data[0];
    }

}
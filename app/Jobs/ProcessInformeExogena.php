<?php

namespace App\Jobs;

use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\PrivateMessageEvent;
use Carbon\Carbon;
//MODELS
use App\Models\User;
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfExogena;
use App\Models\Sistema\ExogenaFormato;
use App\Models\Sistema\ExogenaFormatoConcepto;

class ProcessInformeExogena implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $request;
    public $empresa;
    public $id_usuario;
	public $id_empresa;
    public $id_exogena;
    public $token_db;
    public $timeout = 300;
    public $exogenaCollection = [];

    public function __construct($request, $id_usuario, $id_empresa, $id_exogena)
    {
        $this->request = $request;
		$this->id_usuario = $id_usuario;
		$this->id_empresa = $id_empresa;
        $this->id_exogena = $id_exogena;
        $this->token_db = $this->empresa ? $this->empresa->token_db : 'unknown';
    }

    public function handle()
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

		$this->empresa = Empresa::find($this->id_empresa);
        
        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $this->empresa->token_db);

        try {
            DB::connection('informes')->beginTransaction();

            $this->documentosExogana();

            foreach (array_chunk($this->exogenaCollection, 233) as $exogenaCollection) {
                DB::connection('informes')
                    ->table('inf_exogena_detalles')
                    ->insert(array_values($exogenaCollection));
            }

            InfExogena::where('id', $this->id_exogena)->update([
                'estado' => 2
            ]);

            DB::connection('informes')->commit();

            event(new PrivateMessageEvent("informe-exogena-{$this->empresa->token_db}_{$this->id_usuario}", [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con exito!',
                'titulo' => 'Impuestos generada',
                'id_exogena' => $this->id_exogena,
                'autoclose' => false
            ]));

            $endTime = microtime(true);
            $endMemory = memory_get_usage();

            $executionTime = $endTime - $startTime;
            $memoryUsage = $endMemory - $startMemory;

            Log::info("Informe exogena ejecutado en {$executionTime} segundos, usando {$memoryUsage} bytes de memoria. Usuario id: {$this->id_usuario}");

        } catch (Exception $exception) {
            DB::connection('informes')->rollback();
			throw $exception;
        }
    }

    private function documentosExogana()
    {
        $query = $this->exogenaDocumentosQuery();
        $query->unionAll($this->exogenaAnteriorQuery());

        [$formato, $columnasFormato, $cuentaColumnas] = $this->getConfiguracion();

        DB::connection('sam')
			->table(DB::raw("({$query->toSql()}) as exogena"))
			->mergeBindings($query)
			->select([
				'id',
				'id_nit',
				'id_cuenta',
				DB::raw('SUM(debito) AS debito'),
				DB::raw('SUM(credito) AS credito'),
				DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
				DB::raw('SUM(debito) - SUM(credito) AS saldo'),
			])
            ->groupByRaw('id_nit, id_cuenta')
            ->orderBy('id')
            ->chunk(233, function ($documentos) use ($formato, $cuentaColumnas, $columnasFormato) {

                $nits = Nits::with(['pais', 'departamento', 'ciudad', 'tipo_documento'])
                    ->whereIn('id', $documentos->pluck('id_nit')->toArray())
                    ->get()
                    ->keyBy('id');

                $cuentas = PlanCuentas::with(['exogena_formato' => function ($query) {
                        $query->where('id', $this->request['id_formato']);
                    },
                    'exogena_concepto' => function ($query) {
                        if ($this->request['id_concepto']) {
                            $query->where('id', $this->request['id_concepto']);
                        }
                    },
                    'impuesto'
                    ])
                    ->whereIn('id', $documentos->pluck('id_cuenta')->toArray())
                    ->get()
					->keyBy('id');

                foreach ($documentos as $documento) {
                    $nit = $nits->get($documento->id_nit);
					$cuenta = $cuentas->get($documento->id_cuenta);

                    if (!$nit || !$cuenta->exogena_formato) continue;

                    $dataColumnasFormato = [
						'tipo_documento' => $formato->tipo_documento ? $nit->tipo_documento->nombre : null,
						'numero_documento' => $formato->numero_documento ? $nit->numero_documento : null,
						'digito_verificacion' => $formato->digito_verificacion ? $nit->digito_verificacion : null,
						'primer_apellido' => $formato->primer_apellido ? $nit->primer_apellido : null,
						'segundo_apellido' => $formato->segundo_apellido ? $nit->segundo_apellido : null,
						'primer_nombre' => $formato->primer_nombre ? $nit->primer_nombre : null,
						'otros_nombres' => $formato->otros_nombres ? $nit->otros_nombres : null,
						'razon_social' => $formato->razon_social ? $nit->razon_social : null,
						'direccion' => $formato->direccion ? $nit->direccion : null,
						'departamento' => $formato->departamento ? $nit->departamento?->nombre : null,
						'municipio' => $formato->municipio ? $nit->ciudad?->nombre : null,
						'pais' => $formato->pais ? $nit->pais?->nombre : null,
					];

                    $newRowExogena = array_intersect_key($dataColumnasFormato, $columnasFormato);
                    
                    $cuentaColumnasBase = $cuentaColumnas->mapWithKeys(function ($columna) {
						return [$columna->columna => 0];
					});

                    $newRowExogena = $cuentaColumnasBase->merge($newRowExogena);
                    $newRowExogena['id_exogena'] = $this->id_exogena;
                    $newRowExogena['id_exogena_formato'] = $formato->id;
                    $newRowExogena['id_exogena_formato_concepto'] = $cuenta->id_exogena_formato_concepto;
                    $newRowExogena['id_nit'] = $documento->id_nit;
                    $newRowExogena['cuenta'] = $cuenta->cuenta;

                    // Obtener el concepto
                    $concepto = ExogenaFormatoConcepto::find($cuenta->id_exogena_formato_concepto);
                    $newRowExogena['concepto'] = $concepto ? $concepto->concepto : null;

                    $this->dataEmpleados();

                    // Obtener la columna relacionada a la cuenta
                    $columna = $cuentaColumnas->get($cuenta->id_exogena_formato_columna);

                    if (!$columna) {
                        InfExogena::where('id', $this->id_exogena)->update([
                            'estado' => 0
                        ]);

                        event(new PrivateMessageEvent(
                            'informe-exogena-'.$this->token_db.'_'.$this->id_usuario, 
                            [
                                'tipo' => 'error',
                                'mensaje' => "La columna relacionada al formato {$formato->formato} de la cuenta {$cuenta->cuenta} no existe.",
                                'titulo' => 'Error en proceso',
                                'autoclose' => false
                            ]
                        ));

                        DB::connection('informes')->rollback();
                        return;
                    }

                    $naturaleza = PlanCuentas::DEBITO ? 'debito' : 'credito';
                    $columnaValue = $columna->saldo ? $documento->saldo : $documento->{$naturaleza};

                    if ($columna->acumulado) {
                        $columnaValue += $documento->saldo_anterior;
                    }

                    $isSaldoYCredito = $columna->saldo && $columna->naturaleza && $cuenta->naturaleza_cuenta;
                    $columnaValue = $isSaldoYCredito ? $columnaValue * (-1) : $columnaValue;

                    $ordenExogena = "$documento->id_nit-{$cuenta->id_exogena_formato_concepto}";

                    if ($columnaValue == 0) continue;

                    if (isset($this->exogenaCollection[$ordenExogena])) {
                        $storedExogena = $this->exogenaCollection[$ordenExogena];
                        $storedExogena[$columna->columna] += $columnaValue;
                        $this->exogenaCollection[$ordenExogena] = $storedExogena;
                    } else {
                        $newRowExogena[$columna->columna] = $columnaValue;
                        $this->exogenaCollection[$ordenExogena] = $newRowExogena->toArray();
                    }

                    // Agrega valor a columnas con id_columna_porcentaje_base
                    $columnasPorcentaje = $cuentaColumnas->filter(function ($cuentaColumna) use ($columna) {
                        return $cuentaColumna->id_columna_porcentaje_base == $columna->id;
                    });

                    foreach ($columnasPorcentaje as $columnaPorcentaje) {
                        if (!$cuenta->tipo_impuesto) {

                            InfExogena::where('id', $this->id_exogena)->update([
                                'estado' => 0
                            ]);

                            event(new PrivateMessageEvent(
                                'informe-exogena-'.$this->token_db.'_'.$this->id_usuario, 
                                [
                                    'tipo' => 'error',
                                    'mensaje' => 'El impuesto relacionado a la cuenta {$cuenta->cuenta} no existe.',
                                    'titulo' => 'Error en proceso',
                                    'autoclose' => false
                                ]
                            ));

                            DB::connection('informes')->rollback();
                            return;
                        }

                        $storedExogena = $this->exogenaCollection[$ordenExogena];

                        $porcentajeBase = $cuenta->tipo_impuesto->porcentaje;
                        $valorColumnaPorcentaje = $storedExogena[$columna->columna];
                        $base = round($valorColumnaPorcentaje / ($porcentajeBase / 100), 2);
                        $storedExogena[$columnaPorcentaje->columna] += $base;

                        $this->exogenaCollection[$ordenExogena] = $storedExogena;
                    }
                }
            }
        );
    }

    private function dataEmpleados()
    {
        // if ($nit->empleado) {
        //     // Consulta y suma periodos de pago para columnas con id_tipo_concepto_nomina
        //     $tiposConceptos = $cuentaColumnas
        //         ->where('id_tipo_concepto_nomina', '!=', null)
        //         ->pluck('id_tipo_concepto_nomina');

        //     $conceptos = DB::connection('dynamic')
        //         ->table('nom_conceptos')
        //         ->select('id')
        //         ->whereIn('tipo_concepto', $tiposConceptos)
        //         ->pluck('id');

        //     foreach ($cuentaColumnas as $columna) {
        //         if (!$columna->id_tipo_concepto_nomina) continue;

        //         $periodosPago = DB::connection('dynamic')
        //             ->table('nom_periodo_pagos')
        //             ->select('id')
        //             ->where('id_empleado', $documento->id_nit)
        //             ->whereYear('fecha_inicio_periodo', $this->filtros['year'])
        //             ->whereYear('fecha_fin_periodo', $this->filtros['year'])
        //             ->pluck('id');

        //         $sumDetalles = DB::connection('dynamic')
        //             ->table('nom_periodo_pago_detalles')
        //             ->select('id')
        //             ->whereIn('id_periodo_pago', $periodosPago)
        //             ->whereIn('id_concepto', $conceptos)
        //             ->sum('valor');

        //         $newRowExogena[$columna->columna] += $sumDetalles;
        //     }
        // }
    }

    private function exogenaDocumentosQuery()
    {
		return DB::connection('sam')->table('documentos_generals AS DG')
			->select([
				'id',
				'id_nit',
				'id_cuenta',
				DB::raw('SUM(debito) AS debito'),
				DB::raw('SUM(credito) AS credito'),
				DB::raw('SUM(0) AS saldo_anterior'),
				DB::raw('SUM(0) + SUM(debito) - SUM(credito) AS saldo'),
			])
			->whereYear('fecha_manual', $this->request['year'])
			->when($this->request['id_nit'], function ($query) {
				$query->where('id_nit', $this->request['id_nit']);
			})
			->groupByRaw('id_nit, id_cuenta');
    }

    private function getConfiguracion()
    {
        $formato = ExogenaFormato::with('columnas')->findOrFail($this->request['id_formato']);

        $skipColumns = [
            'id',
            'created_at',
            'created_by',
            'updated_by',
            'updated_at',
            'formato'
        ];

        $columnasFormato = collect($formato->toArray())
            ->reject(fn ($value, $key) => in_array($key, $skipColumns) || empty($value))
            ->all();

        $cuentaColumnas = $formato->columnas->keyBy('id'); // Usa la relación ya cargada

        return [$formato, $columnasFormato, $cuentaColumnas];
    }

    private function exogenaAnteriorQuery()
	{
		return DB::connection('sam')->table('documentos_generals AS DG')
			->select([
				'id',
				'id_nit',
				'id_cuenta',
				DB::raw('0 AS debito'),
				DB::raw('0 AS credito'),
				DB::raw('SUM(debito) - SUM(credito) AS saldo_anterior'),
				DB::raw('0 AS saldo'),
			])
			->whereYear('fecha_manual', '<', $this->request['year'])
			->when($this->request['id_nit'], function ($query) {
				$query->where('id_nit', $this->request['id_nit']);
			})
			->groupByRaw('id_nit, id_cuenta');
	}

    public function failed($exception)
    {
        DB::connection('informes')->rollBack();

        // Si no tenemos la empresa, intentamos obtenerla
        if (!$this->empresa && $this->id_empresa) {
            $this->empresa = Empresa::find($this->id_empresa);
        }

        $token_db = $this->empresa ? $this->empresa->token_db : 'unknown';

        InfExogena::where('id', $this->id_exogena)->update([
            'estado' => 0
        ]);

        event(new PrivateMessageEvent(
            'informe-exogena-'.$token_db.'_'.$this->id_usuario, 
            [
                'tipo' => 'error',
                'mensaje' => 'Error al generar el informe: '.$exception->getMessage(),
                'titulo' => 'Error en proceso',
                'autoclose' => false
            ]
        ));

        // Registrar el error en los logs
        logger()->error("Error en ProcessInformeExogena: ".$exception->getMessage(), [
            'exception' => $exception,
            'request' => $this->request,
            'user_id' => $this->id_usuario,
            'empresa_id' => $this->id_empresa
        ]);
    }
}
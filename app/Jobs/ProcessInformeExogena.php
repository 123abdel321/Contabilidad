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
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfExogena;
use App\Models\Sistema\ExogenaFormato;

class ProcessInformeExogena implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $request;
    public $id_usuario;
	public $id_empresa;
    public $id_exogena;

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
        
        try {
            DB::connection('informes')->beginTransaction();

            $exogena = InfExogena::create([
				'year' => $this->request['year'],
				'id_empresa' => $this->id_empresa,
				'id_nit' => $this->request['id_nit'],
				'id_exogena_formato' => $this->request['id_formato'],
				'id_exogena_formato_concepto' => $this->request['id_concepto'],
			]);

            $this->id_exogena = $exogena->id;

            $this->documentosExogana();

            // ksort($this->impuestosCollection, SORT_STRING | SORT_FLAG_CASE);
            // foreach (array_chunk($this->impuestosCollection,233) as $impuestosCollection){
            //     DB::connection('informes')
            //         ->table('inf_impuestos_detalles')
            //         ->insert(array_values($impuestosCollection));
            // }

            DB::connection('informes')->commit();

            $urlEventoNotificacion = $empresa->token_db.'_'.$this->id_usuario;
            if ($this->id_notificacion) {
                $urlEventoNotificacion = $this->id_notificacion;
            }

            event(new PrivateMessageEvent('informe-impuestos-'.$urlEventoNotificacion, [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con exito!',
                'titulo' => 'Impuestos generada',
                'id_impuestos' => $this->id_impuestos,
                'autoclose' => false
            ]));

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
                
                $exogenaCollection = [];

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

                    // dd($cuenta->exogena_concepto);

                    $formatosCuentaExogenaByConcepto = $cuenta->exogena_concepto
						->groupBy('id')
                        ->get();

                    foreach ($formatosCuentaExogenaByConcepto as $idConceptoExogena => $formatosCuentaExogena) {
                        $newRowExogena = $cuentaColumnasBase->merge($newRowExogena);
                        $newRowExogena['id_exogena'] = $this->id_exogena;
						$newRowExogena['id_exogena_formato'] = $formato->id;
						$newRowExogena['id_exogena_formato_concepto'] = $idConceptoExogena;
						$newRowExogena['id_nit'] = $documento->id_nit;
						$newRowExogena['cuenta'] = $cuenta->cuenta;

                        dd($formatosCuentaExogena);

                        // if ($nit->empleado) {
                        // }
                        // dd($formatosCuentaExogena);
                        foreach ($formatosCuentaExogena as $formatoCuentaExogena) {
                            dd($formatoCuentaExogena);
                            $newRowExogena['concepto'] = $formatoCuentaExogena->concepto;
                            dd($newRowExogena);

                        }

                        dd($newRowExogena);
                    }

                    dd($formatosCuentaExogenaByConcepto);

                    dd($nit, $cuenta);
                }
            });

        dd('hee aca');
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
}
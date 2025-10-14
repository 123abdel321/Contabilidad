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
use App\Models\Sistema\PlanCuentasTipo;

class ProcessInformeCartera implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $request;
    public $id_usuario;
	public $id_empresa;
    public $contador = 10000;
    public $carteras = [];
    public $id_cartera = 0;
    public $carteraCollection = [];
    public $id_notificacion = null;
    public $cobrar = [3,7];
    public $pagar = [4,8];

    public function __construct($request, $id_usuario, $id_empresa)
    {
        $this->request = $request;
		$this->id_usuario = $id_usuario;
		$this->id_empresa = $id_empresa;
        if ($this->request['id_cuenta']) {
            $cuenta = PlanCuentas::find($this->request['id_cuenta']);
            $this->request['cuenta'] = $cuenta->cuenta;
        }
        if (array_key_exists('notificacion', $this->request)) {
            $this->id_notificacion = $this->request['notificacion'];
        }
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
				'id_nit' => $this->request['id_nit'],
				'id_cuenta' => $this->request['id_cuenta'],
				'fecha_desde' => $this->request['fecha_desde'],
				'fecha_hasta' => $this->request['fecha_hasta'],
				'agrupar_cartera' => $this->request['agrupar_cartera'],
				'nivel' => $this->request['nivel'],
			]);
            $this->id_cartera = $cartera->id;

            if ($this->request['tipo_informe'] == 'por_edades') {
                $this->carteraEdades();
            } else {
                $this->nivelUnoCartera();//NIVEL 1: GRUPOS
                if (!$this->request['tipo_informe']) $this->nivelCeroCartera();//INFORME AMBOS
                if ($this->request['nivel'] != '1') $this->nivelDosCartera();//NIVEL 2: SUB-GRUPOS 
                if ($this->request['nivel'] == '3') $this->nivelTresCartera();//NIVEL 3: DETALLE 
                $this->totalesCartera();//TOTALES
            }

            ksort($this->carteraCollection, SORT_STRING | SORT_FLAG_CASE);
            foreach (array_chunk($this->carteraCollection,233) as $carteraCollection){
                DB::connection('informes')
                    ->table('inf_cartera_detalles')
                    ->insert(array_values($carteraCollection));
            }

            DB::connection('informes')->commit();

            $urlEventoNotificacion = $empresa->token_db.'_'.$this->id_usuario;
            if ($this->id_notificacion) {
                $urlEventoNotificacion = $this->id_notificacion;
            }

            event(new PrivateMessageEvent('informe-cartera-'.$urlEventoNotificacion, [
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

    private function nivelCeroCartera()
    {
        $query = $this->carteraDocumentosQuery();
        $query->unionAll($this->carteraAnteriorQuery());

        return DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS cartera"))
            ->mergeBindings($query)
            ->select(
                'id_nit',
                'numero_documento',
                'nombre_nit',
                'razon_social',
                'apartamentos',
                'id_cuenta',
                'cuenta',
                'id_tipo_cuenta',
                'naturaleza_cuenta',
                'auxiliar',
                'nombre_cuenta',
                'documento_referencia',
                'id_centro_costos',
                'codigo_cecos',
                'nombre_cecos',
                'id_comprobante',
                'codigo_comprobante',
                'nombre_comprobante',
                'consecutivo',
                'concepto',
                'fecha_manual',
                'created_at',
                'fecha_creacion',
                'fecha_edicion',
                'created_by',
                'updated_by',
                'anulado',
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                DB::raw("IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono"),
                // $this->calcularTotalFacturas(),
                DB::raw("IF(naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas"),
                DB::raw('SUM(total_columnas) AS total_columnas'),
                DB::raw("(CASE
					WHEN naturaleza_cuenta = 0 AND SUM(debito) < 0 THEN 1
					WHEN naturaleza_cuenta = 1 AND SUM(credito) < 0 THEN 1
					ELSE 0
				END) AS error")
            )
            ->groupByRaw($this->request['agrupar_cartera'].', id_tipo_cuenta')
            ->orderByRaw('cuenta, id_nit, documento_referencia, created_at')
            ->havingRaw('saldo_anterior != 0 OR total_abono != 0 OR total_facturas != 0 OR saldo_final != 0')
            ->chunk(233, function ($documentos) {
                $documentos->each(function ($documento) {
                    $key = '';

                    if ($this->request['agrupar_cartera'] == 'id_nit') {
                        $key = $documento->numero_documento;
                    }
                    if ($this->request['agrupar_cartera'] == 'id_cuenta') {
                        $key = $documento->cuenta;
                    }

                    if ($this->hasCuentaData($key)) {
                        $this->sumCuentaData($key, $documento);
                    } else {
                        $this->carteraCollection[$key] = [
                            'id_cartera' => $this->id_cartera,
                            'id_nit' => $documento->id_nit,
                            'numero_documento' => $documento->numero_documento,
                            'nombre_nit' => $documento->nombre_nit,
                            'razon_social' => $documento->razon_social,
                            'apartamento_nit' => $documento->apartamentos,
                            'id_cuenta' => $documento->id_cuenta,
                            'cuenta' => $documento->numero_documento,
                            'naturaleza_cuenta' => $documento->naturaleza_cuenta,
                            'nombre_cuenta' => $documento->nombre_nit,
                            'documento_referencia' => '',
                            'id_centro_costos' => $documento->id_centro_costos,
                            'id_comprobante' => $documento->id_comprobante,
                            'codigo_comprobante' => $documento->codigo_comprobante,
                            'nombre_comprobante' => $documento->nombre_comprobante,
                            'codigo_cecos' => $documento->codigo_cecos,
                            'nombre_cecos' => $documento->nombre_cecos,
                            'consecutivo' => $documento->consecutivo,
                            'concepto' => '',
                            'fecha_manual' => '',
                            'fecha_creacion' => $documento->fecha_creacion,
                            'fecha_edicion' => $documento->fecha_edicion,
                            'created_by' => $documento->created_by,
                            'updated_by' => $documento->updated_by,
                            'dias_cumplidos' => '',
                            'mora' => '',
                            'saldo_anterior' => $documento->saldo_anterior,
                            'total_abono' => null,
                            'total_facturas' => null,
                            'saldo' => $documento->saldo_final,
                            'nivel' => 9,
                            'errores' => $documento->error
                        ];
                    }
                });
            });
    }

    private function nivelUnoCartera()
    {
        $query = $this->carteraDocumentosQuery();
        $query->unionAll($this->carteraAnteriorQuery());

        return DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS cartera"))
            ->mergeBindings($query)
            ->select(
                'id_nit',
                'numero_documento',
                'nombre_nit',
                'razon_social',
                'apartamentos',
                'id_cuenta',
                'cuenta',
                'id_tipo_cuenta',
                'naturaleza_cuenta',
                'auxiliar',
                'nombre_cuenta',
                'documento_referencia',
                'id_centro_costos',
                'codigo_cecos',
                'nombre_cecos',
                'id_comprobante',
                'codigo_comprobante',
                'nombre_comprobante',
                'consecutivo',
                'concepto',
                'fecha_manual',
                'created_at',
                'fecha_creacion',
                'fecha_edicion',
                'created_by',
                'updated_by',
                'anulado',
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                DB::raw("IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono"),
                // $this->calcularTotalFacturas(),
                DB::raw("IF(naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas"),
                DB::raw('SUM(total_columnas) AS total_columnas'),
                DB::raw("(CASE
					WHEN naturaleza_cuenta = 0 AND SUM(debito) < 0 THEN 1
					WHEN naturaleza_cuenta = 1 AND SUM(credito) < 0 THEN 1
					ELSE 0
				END) AS error")
            )
            ->groupByRaw($this->request['agrupar_cartera'].', id_tipo_cuenta')
            ->orderByRaw('cuenta, id_nit, documento_referencia, created_at')
            ->havingRaw('saldo_anterior != 0 OR total_abono != 0 OR total_facturas != 0 OR saldo_final != 0')
            ->chunk(233, function ($documentos) {
                $documentos->each(function ($documento) {
                    $key = '';
                    $ambos = false;
                    $nombreTipoCuenta = NULL;
                    if ($this->request['agrupar_cartera'] == 'id_nit') {
                        $key = $documento->numero_documento;
                    }
                    if ($this->request['agrupar_cartera'] == 'id_cuenta') {
                        $key = $documento->cuenta;
                    }

                    if (!$this->request['tipo_informe']) {
                        $ambos = true;
                        $keyHeaderY = $key.'-AA';
                        $keyHeaderZ = $key.'-BA';
                        if (in_array($documento->id_tipo_cuenta, $this->cobrar)) {
                            $key.='-AC';
                            $nombreTipoCuenta = 'TOTALES POR COBRAR';
                        }
                        else {
                            $key.='-BC';
                            $nombreTipoCuenta = 'TOTALES POR PAGAR';
                        }
                        if (in_array($documento->id_tipo_cuenta, $this->cobrar)) {
                            if (!$this->hasCuentaData($keyHeaderY)) $this->agregarCabezaAmbos($keyHeaderY, true);
                        } else {
                            if (!$this->hasCuentaData($keyHeaderZ)) $this->agregarCabezaAmbos($keyHeaderZ, false);
                        }
                    }

                    if ($this->hasCuentaData($key)) {
                        $total_abono = $documento ? $documento->total_abono : 0;
                        $total_facturas = $documento ? $documento->total_facturas : 0;
                        
                        if (!$this->request['tipo_informe']) {
                            $total_abono = 0;
                            $total_facturas = 0;
                        }

                        $this->carteraCollection[$key]['saldo_anterior']+= number_format((float)$documento->saldo_anterior, 2, '.', '');
                        $this->carteraCollection[$key]['total_abono']+= number_format((float)$total_abono, 2, '.', '');
                        $this->carteraCollection[$key]['total_facturas']+= number_format((float)$total_facturas, 2, '.', '');
                        $this->carteraCollection[$key]['saldo']+= number_format((float)$documento->saldo_final, 2, '.', '');
                    } else {
                        $numeroDocumento = $documento->numero_documento;
                        if (!$this->request['tipo_informe']) $numeroDocumento = $documento->cuenta;

                        $nombreNit = $documento->nombre_nit;
                        if (!$this->request['tipo_informe']) $nombreNit = $documento->nombre_cuenta;

                        $ubicacion = $documento->apartamentos;
                        if (!$this->request['tipo_informe']) $ubicacion = '';

                        $this->carteraCollection[$key] = [
                            'id_cartera' => $this->id_cartera,
                            'id_nit' => $documento->id_nit,
                            'numero_documento' => $ambos ? $nombreTipoCuenta : $numeroDocumento,
                            'nombre_nit' => $ambos ? null : $nombreNit,
                            'razon_social' => $documento->razon_social,
                            'apartamento_nit' => $ubicacion,
                            'id_cuenta' => $documento->id_cuenta,
                            'cuenta' => $ambos ? $nombreTipoCuenta : $documento->cuenta,
                            'naturaleza_cuenta' => $documento->naturaleza_cuenta,
                            'nombre_cuenta' => $ambos ? $nombreTipoCuenta : $documento->nombre_cuenta,
                            'documento_referencia' => '',
                            'id_centro_costos' => $documento->id_centro_costos,
                            'id_comprobante' => $documento->id_comprobante,
                            'codigo_comprobante' => $documento->codigo_comprobante,
                            'nombre_comprobante' => $documento->nombre_comprobante,
                            'codigo_cecos' => $documento->codigo_cecos,
                            'nombre_cecos' => $documento->nombre_cecos,
                            'consecutivo' => $documento->consecutivo,
                            'concepto' => '',
                            'fecha_manual' => '',
                            'fecha_creacion' => $documento->fecha_creacion,
                            'fecha_edicion' => $documento->fecha_edicion,
                            'created_by' => $documento->created_by,
                            'updated_by' => $documento->updated_by,
                            'dias_cumplidos' => '',
                            'mora' => '',
                            'saldo_anterior' => $documento->saldo_anterior,
                            'total_abono' => $documento->total_abono,
                            'total_facturas' => $documento->total_facturas,
                            'saldo' => $documento->saldo_final,
                            'nivel' => 1,
                            'errores' => $documento->error
                        ];
                    }
                });
            });
    }

    private function nivelDosCartera()
    {
        $query = $this->carteraDocumentosQuery();
        $query->unionAll($this->carteraAnteriorQuery());
        
        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS cartera"))
            ->mergeBindings($query)
            ->select(
                'id_nit',
                'numero_documento',
                'nombre_nit',
                'razon_social',
                'apartamentos',
                'id_cuenta',
                'cuenta',
                'naturaleza_cuenta',
                'auxiliar',
                'nombre_cuenta',
                'id_tipo_cuenta',
                'documento_referencia',
                'id_centro_costos',
                'codigo_cecos',
                'nombre_cecos',
                'id_comprobante',
                'codigo_comprobante',
                'nombre_comprobante',
                'consecutivo',
                'concepto',
                'fecha_manual',
                'created_at',
                'fecha_creacion',
                'fecha_edicion',
                'created_by',
                'updated_by',
                'anulado',
                'plazo',
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                DB::raw("IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono"),
                DB::raw("IF(naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas"),
                DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos'),
                DB::raw('SUM(total_columnas) AS total_columnas'),
                DB::raw("(CASE
					WHEN naturaleza_cuenta = 0 AND SUM(debito) < 0 THEN 1
					WHEN naturaleza_cuenta = 1 AND SUM(credito) < 0 THEN 1
					ELSE 0
				END) AS error")
            )
            ->groupByRaw($this->groupString(2).', id_tipo_cuenta')
            ->orderByRaw('cuenta, id_nit, documento_referencia, created_at')
            ->havingRaw('saldo_anterior != 0 OR total_abono != 0 OR total_facturas != 0 OR saldo_final != 0')
            ->chunk(233, function ($documentos) {
                $documentos->each(function ($documento) {
                    $key = '';
                    if (!$this->request['tipo_informe']) {
                        $prefijo = '';

                        if (in_array($documento->id_tipo_cuenta, $this->cobrar)) $prefijo = '-AB';
                        else $prefijo = '-BB';

                        if ($this->request['agrupar_cartera'] == 'id_nit') {
                            $key = $documento->numero_documento.$prefijo.'-A-'.$documento->cuenta;
                        }
                        if ($this->request['agrupar_cartera'] == 'id_cuenta') {
                            $nombreKey = str_replace(' ', '', $documento->nombre_nit);
                            $key = $documento->cuenta.$prefijo.'-A-'.$nombreKey;
                        }
                    } else {
                        if ($this->request['agrupar_cartera'] == 'id_nit') {
                            $key = $documento->numero_documento.'-A-'.$documento->cuenta;
                        }
                        if ($this->request['agrupar_cartera'] == 'id_cuenta') {
                            $nombreKey = str_replace(' ', '', $documento->nombre_nit);
                            $key = $documento->cuenta.'-A-'.$nombreKey;
                        }
                    }
                    
                    $mora = $documento->dias_cumplidos - $documento->plazo;
                    $this->carteraCollection[$key] = [
                        'id_cartera' => $this->id_cartera,
                        'id_nit' => $documento->id_nit,
                        'numero_documento' => $documento->numero_documento,
                        'nombre_nit' => $documento->nombre_nit,
                        'razon_social' => $documento->razon_social,
                        'apartamento_nit' => $documento->apartamentos,
                        'id_cuenta' => $documento->id_cuenta,
                        'cuenta' => $documento->cuenta,
                        'naturaleza_cuenta' => $documento->naturaleza_cuenta,
                        'nombre_cuenta' => $documento->nombre_cuenta,
                        'documento_referencia' => '',
                        'id_centro_costos' => $documento->id_centro_costos,
                        'id_comprobante' => $documento->id_comprobante,
                        'codigo_comprobante' => $documento->codigo_comprobante,
                        'nombre_comprobante' => $documento->nombre_comprobante,
                        'codigo_cecos' => $documento->codigo_cecos,
                        'nombre_cecos' => $documento->nombre_cecos,
                        'consecutivo' => $documento->consecutivo,
                        'concepto' => '',
                        'fecha_manual' => '',
                        'fecha_creacion' => $documento->fecha_creacion,
                        'fecha_edicion' => $documento->fecha_edicion,
                        'created_by' => $documento->created_by,
                        'updated_by' => $documento->updated_by,
                        'dias_cumplidos' => '',
                        'mora' => '',
                        'saldo_anterior' => $documento->saldo_anterior,
                        'total_abono' => $documento->total_abono,
                        'total_facturas' => $documento->total_facturas,
                        'saldo' => $documento->saldo_final,
                        'nivel' => 2,
                        'errores' => $documento->error
                    ];
                });
            });
    }

    private function nivelTresCartera()
    {
        $query = $this->carteraDocumentosQuery();
        $query->unionAll($this->carteraAnteriorQuery());

        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS cartera"))
            ->mergeBindings($query)
            ->select(
                'id_nit',
                'numero_documento',
                'nombre_nit',
                'razon_social',
                'apartamentos',
                'id_cuenta',
                'cuenta',
                'naturaleza_cuenta',
                'id_tipo_cuenta',
                'auxiliar',
                'nombre_cuenta',
                'documento_referencia',
                'id_centro_costos',
                'codigo_cecos',
                'nombre_cecos',
                'id_comprobante',
                'codigo_comprobante',
                'nombre_comprobante',
                'consecutivo',
                'concepto',
                'fecha_manual',
                'created_at',
                'fecha_creacion',
                'fecha_edicion',
                'created_by',
                'updated_by',
                'anulado',
                'plazo',
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                DB::raw("IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono"),
                DB::raw("IF(naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas"),
                DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos'),
                DB::raw('SUM(total_columnas) AS total_columnas'),
                DB::raw("(CASE
					WHEN naturaleza_cuenta = 0 AND SUM(debito) < 0 THEN 1
					WHEN naturaleza_cuenta = 1 AND SUM(credito) < 0 THEN 1
					ELSE 0
				END) AS error")
            )
            ->groupByRaw($this->groupString(3))
            ->orderByRaw('cuenta, id_nit, documento_referencia, created_at')
            ->havingRaw('saldo_anterior != 0 OR total_abono != 0 OR total_facturas != 0 OR saldo_final != 0')
            ->chunk(233, function ($documentos) {
                $documentos->each(function ($documento) {
                    $this->contador++;
                    $key = '';

                    if (!$this->request['tipo_informe']) {
                        $prefijo = '';

                        if (in_array($documento->id_tipo_cuenta, $this->cobrar)) $prefijo = '-AB';
                        else $prefijo = '-BB';
                        if ($this->request['agrupar_cartera'] == 'id_nit') {
                            $key = $documento->numero_documento.$prefijo.'-A-'.$documento->cuenta.$this->contador;
                        }
                        if ($this->request['agrupar_cartera'] == 'id_cuenta') {
                            $nombreKey = str_replace(' ', '', $documento->nombre_nit);
                            $key = $documento->cuenta.$prefijo.'-A-'.$nombreKey.'-B-'.$this->contador;
                        }
                    } else {
                        if ($this->request['agrupar_cartera'] == 'id_nit') {
                            $key = $documento->numero_documento.'-A-'.$documento->cuenta.'-B-'.$this->contador;
                        }
                        if ($this->request['agrupar_cartera'] == 'id_cuenta') {
                            $nombreKey = str_replace(' ', '', $documento->nombre_nit);
                            $key = $documento->cuenta.'-A-'.$nombreKey.'-B-'.$this->contador;
                        }
                    }

                    $mora = $documento->dias_cumplidos - $documento->plazo;
                    $this->carteraCollection[$key] = [
                        'id_cartera' => $this->id_cartera,
                        'id_nit' => $documento->id_nit,
                        'numero_documento' => $documento->numero_documento,
                        'nombre_nit' => $documento->nombre_nit,
                        'razon_social' => $documento->razon_social,
                        'apartamento_nit' => $documento->apartamentos,
                        'id_cuenta' => $documento->id_cuenta,
                        'cuenta' => $documento->cuenta,
                        'naturaleza_cuenta' => $documento->naturaleza_cuenta,
                        'nombre_cuenta' => $documento->nombre_cuenta,
                        'documento_referencia' => $documento->documento_referencia,
                        'id_centro_costos' => $documento->id_centro_costos,
                        'id_comprobante' => $documento->id_comprobante,
                        'codigo_comprobante' => $documento->codigo_comprobante,
                        'nombre_comprobante' => $documento->nombre_comprobante,
                        'codigo_cecos' => $documento->codigo_cecos,
                        'nombre_cecos' => $documento->nombre_cecos,
                        'consecutivo' => $documento->consecutivo,
                        'concepto' => $documento->concepto,
                        'fecha_manual' => $documento->fecha_manual,
                        'fecha_creacion' => $documento->fecha_creacion,
                        'fecha_edicion' => $documento->fecha_edicion,
                        'created_by' => $documento->created_by,
                        'updated_by' => $documento->updated_by,
                        'dias_cumplidos' => $documento->dias_cumplidos,
                        'mora' => $mora < 0 ? 0 : $mora,
                        'saldo_anterior' => $documento->saldo_anterior,
                        'total_abono' => $documento->total_abono,
                        'total_facturas' => $documento->total_facturas,
                        'saldo' => $documento->saldo_final,
                        'nivel' => 3,
                        'errores' => $documento->error
                    ];
                });
            });
    }

    private function totalesCartera()
    {
        $query = $this->carteraDocumentosQuery();
        $query->unionAll($this->carteraAnteriorQuery());

        $total = DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS cartera"))
            ->mergeBindings($query)
            ->select(
                'id_nit',
                'numero_documento',
                'nombre_nit',
                'razon_social',
                'id_cuenta',
                'cuenta',
                'naturaleza_cuenta',
                'auxiliar',
                'nombre_cuenta',
                'documento_referencia',
                'id_centro_costos',
                'codigo_cecos',
                'nombre_cecos',
                'id_comprobante',
                'codigo_comprobante',
                'nombre_comprobante',
                'consecutivo',
                'concepto',
                'fecha_manual',
                'created_at',
                'fecha_creacion',
                'fecha_edicion',
                'created_by',
                'updated_by',
                'anulado',
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                DB::raw("IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono"),
                // $this->calcularTotalFacturas(),
                DB::raw("IF(naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas"),
                DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos'),
                DB::raw('SUM(total_columnas) AS total_columnas')
            )
            ->groupByRaw('id_tipo_cuenta')
            ->orderByRaw('created_at')
            ->havingRaw('saldo_anterior != 0 OR total_abono != 0 OR total_facturas != 0 OR saldo_final != 0')   
            ->chunk(233, function ($documentos) {
                $documentos->each(function ($documento) {
                    $key = '99999999999';
                    if ($this->hasCuentaData($key)) {
                        $this->sumCuentaData($key, $documento);
                    } else {
                        $this->newCuentaTotales($key, $documento);
                    }
                });
            });
    }

    private function carteraEdades()
    {
        $query = $this->carteraDocumentosQuery();
        $query->unionAll($this->carteraAnteriorQuery());
        
        $datos = DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS cartera"))
            ->mergeBindings($query)
            ->select(
                'id_nit',
                'numero_documento',
                'nombre_nit',
                'razon_social',
                'id_cuenta',
                'cuenta',
                'naturaleza_cuenta',
                'apartamentos',
                'auxiliar',
                'nombre_cuenta',
                'documento_referencia',
                'id_centro_costos',
                'codigo_cecos',
                'nombre_cecos',
                'id_comprobante',
                'codigo_comprobante',
                'nombre_comprobante',
                'consecutivo',
                'concepto',
                'fecha_manual',
                'created_at',
                'fecha_creacion',
                'fecha_edicion',
                'created_by',
                'updated_by',
                'anulado',
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                DB::raw("IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono"),
                DB::raw("IF(naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas"),
                DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos'),
                DB::raw('SUM(total_columnas) AS total_columnas'),
                DB::raw("SUM(CASE WHEN DATEDIFF(now(), fecha_manual) BETWEEN 0 AND 30 THEN (debito - credito) ELSE 0 END) AS saldo_0_30"),
                DB::raw("SUM(CASE WHEN DATEDIFF(now(), fecha_manual) BETWEEN 31 AND 60 THEN (debito - credito) ELSE 0 END) AS saldo_30_60"),
                DB::raw("SUM(CASE WHEN DATEDIFF(now(), fecha_manual) BETWEEN 61 AND 90 THEN (debito - credito) ELSE 0 END) AS saldo_60_90"),
                DB::raw("SUM(CASE WHEN DATEDIFF(now(), fecha_manual) > 90 THEN (debito - credito) ELSE 0 END) AS saldo_mas_90"),
                DB::raw("(CASE
                    WHEN naturaleza_cuenta = 0 AND SUM(debito) < 0 THEN 1
                    WHEN naturaleza_cuenta = 1 AND SUM(credito) < 0 THEN 1
                    ELSE 0
                END) AS error")
            )
            ->groupBy([
                'id_nit',
                'id_cuenta'
            ])
            ->orderByRaw('id_nit')
            ->havingRaw('saldo_anterior != 0 OR total_abono != 0 OR total_facturas != 0 OR saldo_final != 0')
            ->chunk(233, function ($documentos) {
                $documentos->each(function ($documento) {
                    
                    $key = '';

                    if ($this->request['agrupar_cartera'] == 'id_nit') {
                        $key = $documento->numero_documento;
                    }
                    if ($this->request['agrupar_cartera'] == 'id_cuenta') {
                        $key = $documento->cuenta;
                    }

                    $this->carteraCollection[$key] = [
                        'id_cartera' => $this->id_cartera,
                        'id_nit' => $documento->id_nit,
                        'numero_documento' => $documento->numero_documento,
                        'nombre_nit' => $documento->nombre_nit,
                        'razon_social' => $documento->razon_social,
                        'apartamento_nit' => $documento->apartamentos,
                        'id_cuenta' => $documento->id_cuenta,
                        'cuenta' => $documento->numero_documento,
                        'naturaleza_cuenta' => $documento->naturaleza_cuenta,
                        'nombre_cuenta' => $documento->nombre_cuenta,
                        'documento_referencia' => '',
                        'id_centro_costos' => $documento->id_centro_costos,
                        'id_comprobante' => $documento->id_comprobante,
                        'codigo_comprobante' => $documento->codigo_comprobante,
                        'nombre_comprobante' => $documento->nombre_comprobante,
                        'codigo_cecos' => $documento->codigo_cecos,
                        'nombre_cecos' => $documento->nombre_cecos,
                        'consecutivo' => $documento->consecutivo,
                        'concepto' => '',
                        'fecha_manual' => '',
                        'fecha_creacion' => $documento->fecha_creacion,
                        'fecha_edicion' => $documento->fecha_edicion,
                        'created_by' => $documento->created_by,
                        'updated_by' => $documento->updated_by,
                        'dias_cumplidos' => '',
                        'mora' => $documento->saldo_0_30,
                        'saldo_anterior' => $documento->saldo_30_60,
                        'total_abono' => $documento->saldo_60_90,
                        'total_facturas' => $documento->saldo_mas_90,
                        'saldo' => $documento->saldo_final,
                        'nivel' => 0,
                        'errores' => $documento->error
                    ];
                });
            });
    }

    private function carteraDocumentosQuery($documento_referencia = NULL, $id_nit = NULL, $id_cuenta = NULL)
    {
        $documentosQuery = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "N.razon_social",
                "N.plazo",
                "N.apartamentos",
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.naturaleza_cuenta",
                "PC.auxiliar",
                "PC.nombre AS nombre_cuenta",
                "PCT.id_tipo_cuenta",
                "DG.documento_referencia",
                "DG.id_centro_costos",
                "CC.codigo AS codigo_cecos",
                "CC.nombre AS nombre_cecos",
                "CO.id AS id_comprobante",
                "CO.codigo AS codigo_comprobante",
                "CO.nombre AS nombre_comprobante",
                "DG.consecutivo",
                "DG.concepto",
                "DG.fecha_manual",
                DB::raw("DATE_FORMAT(DG.fecha_manual, '%Y-%m') AS fecha_mes"),
                "DG.created_at",
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                "DG.created_by",
                "DG.updated_by",
                "DG.anulado",
                DB::raw("0 AS saldo_anterior"),
                DB::raw("DG.debito AS debito"),
                DB::raw("DG.credito AS credito"),
                DB::raw("DG.debito - DG.credito AS saldo_final"),
                DB::raw("1 AS total_columnas")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'PC.id', 'PCT.id_cuenta')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('anulado', 0)
            ->whereIn('PCT.id_tipo_cuenta', $this->tipoCuentas())
            ->when($this->request['fecha_desde'] ? true : false, function ($query) {
				$query->where('DG.fecha_manual', '>=', $this->request['fecha_desde']);
			}) 
            ->when($this->request['fecha_hasta'] ? true : false, function ($query) {
				$query->where('DG.fecha_manual', '<=', $this->request['fecha_hasta']);
			})
            ->when($this->request['id_nit'] ? true : false, function ($query) {
				$query->where('DG.id_nit', $this->request['id_nit']);
			})
			->when($this->request['id_cuenta'] ? true : false, function ($query) {
				$query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
			});

        return $documentosQuery;
    }

    private function carteraAnteriorQuery($documento_referencia = NULL, $id_nit = NULL, $id_cuenta = NULL)
    {
        $anterioresQuery = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "N.razon_social",
                "N.plazo",
                "N.apartamentos",
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.naturaleza_cuenta",
                "PC.auxiliar",
                "PC.nombre AS nombre_cuenta",
                "PCT.id_tipo_cuenta",
                "DG.documento_referencia",
                "DG.id_centro_costos",
                "CC.codigo AS codigo_cecos",
                "CC.nombre AS nombre_cecos",
                "CO.id AS id_comprobante",
                "CO.codigo AS codigo_comprobante",
                "CO.nombre AS nombre_comprobante",
                "DG.consecutivo",
                "DG.concepto",
                "DG.fecha_manual",
                DB::raw("DATE_FORMAT(DG.fecha_manual, '%Y-%m') AS fecha_mes"),
                "DG.created_at",
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                "DG.created_by",
                "DG.updated_by",
                "DG.anulado",
                DB::raw("debito - credito AS saldo_anterior"),
                DB::raw("0 AS debito"),
                DB::raw("0 AS credito"),
                DB::raw("0 AS saldo_final"),
                DB::raw("1 AS total_columnas")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'PC.id', 'PCT.id_cuenta')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('anulado', 0)
            ->whereIn('PCT.id_tipo_cuenta', $this->tipoCuentas())
            ->when($this->request['fecha_desde'] ? true : false, function ($query) {
				$query->where('DG.fecha_manual', '<', $this->request['fecha_desde']);
			})
            ->when($this->request['id_nit'] ? true : false, function ($query) {
				$query->where('DG.id_nit', $this->request['id_nit']);
			})
			->when($this->request['id_cuenta'] ? true : false, function ($query) {
				$query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
			});

        return $anterioresQuery;
    }
    
    private function groupString($nivel)
    {
        $groupBy = '';
        if ($nivel == 2) {
            if ($this->request['agrupar_cartera'] == 'id_nit') {
                $groupBy = 'id_cuenta, id_nit';
            }
            if ($this->request['agrupar_cartera'] == 'id_cuenta') {
                $groupBy = 'id_nit, id_cuenta';
            }
        }

        if ($nivel == 3) {
            if ($this->request['agrupar_cartera'] == 'id_nit') {
                $groupBy = 'id_cuenta, id_nit, documento_referencia';
            }
            if ($this->request['agrupar_cartera'] == 'id_cuenta') {
                $groupBy = 'id_nit, id_cuenta, documento_referencia';
            }
        }

        return $groupBy;
    }

    private function tipoCuentas ()
    {
        if ($this->request['tipo_informe'] == 'por_cobrar') {
            return [
                PlanCuentasTipo::TIPO_CUENTA_CXC,
                PlanCuentasTipo::TIPO_CUENTA_ANTICIPO_PROVEEDORES_XC
            ];
        }
        if ($this->request['tipo_informe'] == 'por_pagar') {
            return [
                PlanCuentasTipo::TIPO_CUENTA_CXP,
                PlanCuentasTipo::TIPO_CUENTA_ANTICIPO_CLIENTES_XP
            ];
        }
        return [
            PlanCuentasTipo::TIPO_CUENTA_CXC,
            PlanCuentasTipo::TIPO_CUENTA_CXP,
            PlanCuentasTipo::TIPO_CUENTA_ANTICIPO_CLIENTES_XP,
            PlanCuentasTipo::TIPO_CUENTA_ANTICIPO_PROVEEDORES_XC
        ];
    }

    private function calcularTotalAbono ()
    {
        if ($this->request['tipo_informe'] == 'por_cobrar' || $this->request['tipo_informe'] == 'por_pagar') {
            return DB::raw("IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono");
        }
        return DB::raw("(CASE
            WHEN id_tipo_cuenta = 4 OR id_tipo_cuenta = 8 OR id_tipo_cuenta = 3 OR id_tipo_cuenta = 7 THEN IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito))
            ELSE 0
        END) AS total_abono");
    }

    private function calcularTotalFacturas ()
    {
        if ($this->request['tipo_informe'] == 'por_cobrar' || $this->request['tipo_informe'] == 'por_pagar') {
            return DB::raw("IF(naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas");
        }
        return DB::raw("(CASE
            WHEN id_tipo_cuenta = 3 OR id_tipo_cuenta = 7 THEN IF(naturaleza_cuenta = 0, SUM(debito), SUM(credito))
            ELSE 0
        END) AS total_facturas");
    }

    private function hasCuentaData($key)
	{
		return isset($this->carteraCollection[$key]);
	}

    private function sumCuentaData($key, $documento, $noTotalizar = true)
    {
        $total_abono = $documento ? $documento->total_abono : 0;
        $total_facturas = $documento ? $documento->total_facturas : 0;
        
        if (!$this->request['tipo_informe'] && $noTotalizar) {
            $total_abono = 0;
            $total_facturas = 0;
        }

        $this->carteraCollection[$key]['saldo_anterior']+= number_format((float)$documento->saldo_anterior, 2, '.', '');
        $this->carteraCollection[$key]['total_abono']+= number_format((float)$total_abono, 2, '.', '');
        $this->carteraCollection[$key]['total_facturas']+= number_format((float)$total_facturas, 2, '.', '');
        $this->carteraCollection[$key]['saldo']+= number_format((float)$documento->saldo_final, 2, '.', '');
    }

    private function newCuentaData($key, $documento)
    {
        $numeroDocumento = $documento->numero_documento;
        if (!$this->request['tipo_informe']) $numeroDocumento = $documento->cuenta;

        $nombreNit = $documento->nombre_nit;
        if (!$this->request['tipo_informe']) $nombreNit = $documento->nombre_cuenta;

        $this->carteraCollection[$key] = [
            'id_cartera' => $this->id_cartera,
            'id_nit' => $documento->id_nit,
            'numero_documento' => $numeroDocumento,
            'nombre_nit' => $nombreNit,
            'razon_social' => $documento->razon_social,
            'apartamento_nit' => $documento->apartamentos,
            'id_cuenta' => $documento->id_cuenta,
            'cuenta' => $documento->cuenta,
            'naturaleza_cuenta' => $documento->naturaleza_cuenta,
            'nombre_cuenta' => $documento->nombre_cuenta,
            'documento_referencia' => '',
            'id_centro_costos' => $documento->id_centro_costos,
            'id_comprobante' => $documento->id_comprobante,
            'codigo_comprobante' => $documento->codigo_comprobante,
            'nombre_comprobante' => $documento->nombre_comprobante,
            'codigo_cecos' => $documento->codigo_cecos,
            'nombre_cecos' => $documento->nombre_cecos,
            'consecutivo' => $documento->consecutivo,
            'concepto' => '',
            'fecha_manual' => '',
            'fecha_creacion' => $documento->fecha_creacion,
            'fecha_edicion' => $documento->fecha_edicion,
            'created_by' => $documento->created_by,
            'updated_by' => $documento->updated_by,
            'dias_cumplidos' => '',
            'mora' => '',
            'saldo_anterior' => $documento->saldo_anterior,
            'total_abono' => $documento->total_abono,
            'total_facturas' => $documento->total_facturas,
            'saldo' => $documento->saldo_final,
            'nivel' => 1,
            'errores' => $documento->error
        ];
    }

    private function agregarCabezaAmbos($key, $tipo = false)
    {
        return;
        $this->carteraCollection[$key] = [
            'id_cartera' => $this->id_cartera,
            'id_nit' => '',
            'numero_documento' => $tipo ? 'CUENTAS POR COBRAR' : 'CUENTAS POR PAGAR',
            'nombre_nit' => '',
            'razon_social' => '',
            'apartamento_nit' => '',
            'id_cuenta' => '',
            'cuenta' => '',
            'naturaleza_cuenta' => '',
            'nombre_cuenta' => $tipo ? 'CUENTAS POR COBRAR' : 'CUENTAS POR PAGAR',
            'documento_referencia' => '',
            'id_centro_costos' => '',
            'id_comprobante' => '',
            'codigo_comprobante' => '',
            'nombre_comprobante' => '',
            'codigo_cecos' => '',
            'nombre_cecos' => '',
            'consecutivo' => '',
            'concepto' => '',
            'fecha_manual' => '',
            'fecha_creacion' => '',
            'fecha_edicion' => '',
            'created_by' => '',
            'updated_by' => '',
            'dias_cumplidos' => '',
            'mora' => '',
            'saldo_anterior' => '',
            'total_facturas' => '',
            'total_abono' => '',
            'saldo' => '',
            'nivel' => 1,
            'errores' => '',
        ];
    }

    private function newCuentaTotales($key, $documento)
    {
        $total_abono = 0;
        $total_facturas = 0;
        if ($this->request['tipo_informe']) {
            $total_abono = $documento ? $documento->total_abono : 0;
            $total_facturas = $documento ? $documento->total_facturas : 0;
        }

        $this->carteraCollection['99999999999'] = [
            'id_cartera' => $this->id_cartera,
            'id_nit' => '',
            'numero_documento' => '',
            'nombre_nit' => '',
            'razon_social' => '',
            'apartamento_nit' => '',
            'id_cuenta' => '',
            'cuenta' => 'TOTALES',
            'naturaleza_cuenta' => '',
            'nombre_cuenta' => '',
            'documento_referencia' => '',
            'id_centro_costos' => '',
            'id_comprobante' => '',
            'codigo_comprobante' => '',
            'nombre_comprobante' => '',
            'codigo_cecos' => '',
            'nombre_cecos' => '',
            'consecutivo' => '',
            'concepto' => '',
            'fecha_manual' => '',
            'fecha_creacion' => '',
            'fecha_edicion' => '',
            'created_by' => '',
            'updated_by' => '',
            'dias_cumplidos' => '',
            'mora' => '',
            'saldo_anterior' => $documento ? $documento->saldo_anterior : 0,
            'total_abono' => $total_abono,
            'total_facturas' => $total_facturas,
            'saldo' => $documento ? $documento->saldo_final : 0,
            'nivel' => 0,
            'errores' => 0,
        ];
    }
}
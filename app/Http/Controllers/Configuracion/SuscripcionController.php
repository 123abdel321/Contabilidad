<?php

namespace App\Http\Controllers\Configuracion;

use DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
// MODELS
use App\Models\User;
use App\Models\Empresas\PagosSuscripcion;
use App\Models\Empresas\EmpresaSuscripcion;
use App\Models\Empresas\ComponentesSuscripcion;
use App\Models\Empresas\EmpresaComponentesSuscripcion;


class SuscripcionController extends Controller
{
    protected $messages = [
        'required' => 'El campo :attribute es requerido.',
        'exists' => 'El :attribute es inválido.',
        'numeric' => 'El campo :attribute debe ser un valor numérico.',
        'string' => 'El campo :attribute debe ser texto',
        'array' => 'El campo :attribute debe ser un arreglo.',
        'date' => 'El campo :attribute debe ser una fecha válida.',
    ];

    public function __construct()
    {
    }

    // ============================================================
    // COMPONENTES SUSCRIPCION
    // ============================================================

    public function index(Request $request)
    {
        $user = $request->user();
        $esDios = $user->rol_portafolio;

        $data = [
            'esDios' => $esDios
        ];

        return view('pages.configuracion.suscripcion.suscripcion-view', $data);
    }

    public function componentesGet(Request $request)
    {
        $draw = $request->get('draw');
        $user = $request->user();
        $hoy  = Carbon::now()->startOfDay();

        $suscripcionActiva = EmpresaSuscripcion::where('id_empresa', $user->id_empresa)
            ->where('estado', '1')
            ->first();

        $query = EmpresaComponentesSuscripcion::with('componente')
            ->where('id_empresa', $user->id_empresa)
            ->has('componente')
            ->orderBy('id', 'desc');

        if ($suscripcionActiva) {
            $query->where('id_empresa_suscripcion', $suscripcionActiva->id);
        }

        $totalEmpresaSuscripcion = $query->count();
        $items = $query->get();

        $sumaMensual     = 0;
        $sumaTotalCiclo  = 0;
        $totalAlDia      = 0;
        $detalleConsumo  = [];

        foreach ($items as $comp) {
            $calc = $this->calcularProrrateoComponente($comp, $hoy);

            $comp->cantidad_calc        = $calc['cantidad'];
            $comp->precio_mensual       = $calc['precio_mensual'];
            $comp->meses_ciclo          = $calc['meses_ciclo'];
            $comp->precio_total_ciclo   = $calc['precio_total_ciclo'];
            $comp->dias_totales         = $calc['dias_totales'];
            $comp->dias_transcurridos   = $calc['dias_transcurridos'];
            $comp->porcentaje_consumido = $calc['porcentaje_consumido'];
            $comp->valor_al_dia         = $calc['valor_al_dia'];
            $comp->descuento_unitario   = $calc['descuento_unitario'];

            $sumaMensual    += $calc['precio_mensual'];
            $sumaTotalCiclo += $calc['precio_total_ciclo'];
            $totalAlDia     += $calc['valor_al_dia'];

            $detalleConsumo[] = [
                'id'                       => $comp->id,
                'id_componente'            => $comp->id_componente,
                'nombre'                   => $comp->componente->nombre ?? 'N/A',
                'cantidad'                 => $calc['cantidad'],
                'precio_unitario'          => $calc['precio_unitario'],
                'precio_mensual'           => $calc['precio_mensual'],
                'meses_ciclo'              => $calc['meses_ciclo'],
                'precio_total_ciclo'       => $calc['precio_total_ciclo'],
                'fecha_inicio_suscripcion' => $comp->fecha_inicio_suscripcion,
                'fecha_siguiente_cobro'    => $comp->fecha_siguiente_cobro,
                'descuento_unitario'       => $calc['descuento_unitario'],
                'dias_totales'             => $calc['dias_totales'],
                'dias_transcurridos'       => $calc['dias_transcurridos'],
                'porcentaje_consumido'     => $calc['porcentaje_consumido'],
                'valor_al_dia'             => $calc['valor_al_dia'],
            ];
        }

        // Fila TOTAL
        $itemsArray = $items->all();
        $itemsArray[] = (object)[
            'id'                    => 'TOTAL',
            'id_empresa'            => $user->id_empresa,
            'id_empresa_suscripcion'=> null,
            'id_componente'         => null,
            'componente'            => null,
            'cantidad'              => $totalEmpresaSuscripcion,
            'cantidad_calc'         => $totalEmpresaSuscripcion,
            'precio'                => $sumaMensual,
            'precio_mensual'        => $sumaMensual,
            'precio_total_ciclo'    => $sumaTotalCiclo,
            'meses_ciclo'           => null,
            'dias_totales'          => null,
            'dias_transcurridos'    => null,
            'porcentaje_consumido'  => null,
            'valor_al_dia'          => $totalAlDia,
            'fecha_inicio_suscripcion' => null,
            'fecha_siguiente_cobro' => null,
        ];

        return response()->json([
            'success'              => true,
            'draw'                 => $draw,
            'iTotalRecords'        => $totalEmpresaSuscripcion,
            'iTotalDisplayRecords' => $totalEmpresaSuscripcion,
            'data'                 => $itemsArray,
            'perPage'              => 20,
            'total_componentes'    => round($sumaMensual, 2),
            'total_ciclo'          => round($sumaTotalCiclo, 2),
            'total_al_dia'         => round($totalAlDia, 2),
            'total_pendiente'      => round($sumaTotalCiclo - $totalAlDia, 2),
            'hoy'                  => $hoy->format('Y-m-d'),
            'id_suscripcion'       => $suscripcionActiva->id ?? null,
            'consumo_detalle'      => $detalleConsumo,
            'message'              => 'Suscripciones de empresa cargados con exito!'
        ]);
    }

    public function combosGet(Request $request)
    {
        $componentesSuscripcion = ComponentesSuscripcion::select(
            DB::raw('*'),
            DB::raw("CONCAT(nombre, ' - ', FORMAT(precio, 2)) as text")
        );

        if ($request->get("q")) {
            $componentesSuscripcion->where('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $componentesSuscripcion->paginate(40);
    }

    public function componentesPost(Request $request)
    {
        $rules = [
            "id_componente" => "required|exists:clientes.componentes_suscripcions,id",
            "precio"        => "required|numeric|min:0",
            "cantidad"      => "required|integer|min:1",
            "fecha_inicio"  => "required|date",
            "descuento"     => "required|numeric|min:0",
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $validator->errors()
            ], 422);
        }

        try {
            DB::connection('clientes')->beginTransaction();

            $user = $request->user();
            $esDios = $user->rol_portafolio;
            $idEmpresa = $user->id_empresa;

            $componente = ComponentesSuscripcion::find($request->get('id_componente'));

            if (!$componente) {
                throw new Exception('Componente no encontrado.');
            }

            $suscripcion = EmpresaSuscripcion::where('id_empresa', $idEmpresa)
                ->where('estado', '1')
                ->first();

            if (!$suscripcion) {
                throw new Exception('No se encontró una suscripción activa para esta empresa.');
            }

            $cantidad = 1;
            if ((int) $componente->tipo === 1) {
                $cantidad = (int) $request->get('cantidad');
                if ($cantidad < 1) $cantidad = 1;
            }

            $precioUnitario = $esDios
                ? (float) $request->get('precio')
                : (float) $componente->precio;

            $descuentoUnitario = $esDios
                ? (float) $request->get('descuento')
                : 0;

            if ($descuentoUnitario < 0) $descuentoUnitario = 0;
            if ($descuentoUnitario > $precioUnitario) $descuentoUnitario = $precioUnitario;

            $fechaInicioSuscripcion = $esDios
                ? $request->get('fecha_inicio')
                : Carbon::now()->format('Y-m-d');

            $fechaInicio         = Carbon::parse($fechaInicioSuscripcion)->startOfDay();
            $fechaSiguienteCobro = $fechaInicio->copy()->endOfMonth()->format('Y-m-d');

            $idUp = $request->get('id');

            if ($idUp) {
                $empresaComponente = EmpresaComponentesSuscripcion::where('id', $idUp)
                    ->where('id_empresa', $idEmpresa)
                    ->first();

                if (!$empresaComponente) {
                    throw new Exception('Componente de suscripción no encontrado.');
                }

                $empresaComponente->update([
                    'id_componente'            => $componente->id,
                    'cantidad'                 => $cantidad,
                    'precio'                   => $precioUnitario,
                    'descuento'                => $descuentoUnitario,
                    'fecha_inicio_suscripcion' => $fechaInicioSuscripcion,
                    'fecha_siguiente_cobro'    => $fechaSiguienteCobro,
                    'updated_by'               => $user->id,
                ]);

                $empresaComponentesSuscripcion = $empresaComponente;
                $mensaje = "Componente actualizado correctamente.";
            } else {
                $empresaComponentesSuscripcion = EmpresaComponentesSuscripcion::create([
                    'id_empresa'               => $idEmpresa,
                    'id_empresa_suscripcion'   => $suscripcion->id,
                    'id_componente'            => $componente->id,
                    'cantidad'                 => $cantidad,
                    'precio'                   => $precioUnitario,
                    'descuento'                => $descuentoUnitario,
                    'fecha_inicio_suscripcion' => $fechaInicioSuscripcion,
                    'fecha_siguiente_cobro'    => $fechaSiguienteCobro,
                    'created_by'               => $user->id,
                    'updated_by'               => $user->id,
                ]);

                $mensaje = "Componente agregado y precio actualizado.";
            }

            $totalComponentes = 0;
            foreach ($suscripcion->componentes()->get() as $c) {
                $cant = max(1, (int) ($c->cantidad ?? 1));
                $desc = (float) ($c->descuento ?? 0);
                $totalComponentes += ((float) $c->precio - $desc) * $cant;
            }
            $suscripcion->precio = $totalComponentes;
            $suscripcion->save();

            DB::connection('clientes')->commit();

            return response()->json([
                "success" => true,
                'data'    => $empresaComponentesSuscripcion->load('componente'),
                "message" => $mensaje
            ]);

        } catch (Exception $e) {
            DB::connection('clientes')->rollback();
            return response()->json([
                "success" => false,
                'data'    => [],
                "message" => $e->getMessage()
            ], 422);
        }
    }

    // ============================================================
    // PAGOS SUSCRIPCION
    // ============================================================

    public function pagosGet(Request $request)
    {
        $draw = $request->get('draw');
        $user = $request->user();

        $empresaSuscripcion = PagosSuscripcion::select(
                'id',
                'id_empresa',
                'id_usuario',
                'detalle_json',
                'valor',
                'estado',
                'created_at',
                'updated_at'
            )
            ->with('usuario')
            ->where('id_empresa', $user->id_empresa)
            ->orderBy('id', 'desc');

        $totalEmpresaSuscripcion = $empresaSuscripcion->count();

        return response()->json([
            'success'               => true,
            'draw'                  => $draw,
            'iTotalRecords'         => $totalEmpresaSuscripcion,
            'iTotalDisplayRecords'  => $totalEmpresaSuscripcion,
            'data'                  => $empresaSuscripcion->get(),
            'perPage'               => 20,
            'message'               => 'Pagos cargados con éxito!'
        ]);
    }

    public function pagosResumenGet(Request $request)
    {
        try {
            $user = $request->user();
            $idEmpresa = $user->id_empresa;
            $hoy = Carbon::now()->startOfDay();

            $suscripcion = EmpresaSuscripcion::where('id_empresa', $idEmpresa)
                ->where('estado', '1')
                ->first();

            $estadoSuscripcion = 'sin_suscripcion';
            $fechaSiguientePago = null;
            $montoSiguientePago = 0;

            if ($suscripcion) {
                $proximaFecha = EmpresaComponentesSuscripcion::where('id_empresa', $idEmpresa)
                    ->where('id_empresa_suscripcion', $suscripcion->id)
                    ->min('fecha_siguiente_cobro');

                $fechaSiguientePago = $proximaFecha;

                $montoSiguientePago = 0;
                $componentes = EmpresaComponentesSuscripcion::where('id_empresa', $idEmpresa)
                    ->where('id_empresa_suscripcion', $suscripcion->id)
                    ->has('componente')
                    ->get();

                foreach ($componentes as $c) {
                    $cant = max(1, (int) ($c->cantidad ?? 1));
                    $desc = (float) ($c->descuento ?? 0);
                    $montoSiguientePago += ((float) $c->precio - $desc) * $cant;
                }

                if ($proximaFecha) {
                    $fechaCobro = Carbon::parse($proximaFecha)->startOfDay();
                    $diasRestantes = $hoy->diffInDays($fechaCobro, false);

                    if ($diasRestantes < 0) {
                        $estadoSuscripcion = 'vencida';
                    } elseif ($diasRestantes <= 5) {
                        $estadoSuscripcion = 'por_vencer';
                    } else {
                        $estadoSuscripcion = 'activa';
                    }
                } else {
                    $estadoSuscripcion = 'activa';
                }
            }

            $ultimoPago = PagosSuscripcion::where('id_empresa', $idEmpresa)
                ->where('estado', 1)
                ->orderBy('id', 'desc')
                ->first();

            $ultimoPagoData = null;
            if ($ultimoPago) {
                $metodo = null;
                try {
                    $detalle = json_decode($ultimoPago->detalle_json, true);
                    $metodo = $detalle['metodo_pago'] ?? null;
                } catch (Exception $e) {
                    $metodo = null;
                }

                $ultimoPagoData = [
                    'fecha'  => $ultimoPago->created_at ? $ultimoPago->created_at->format('d/m/Y') : null,
                    'valor'  => (float) $ultimoPago->valor,
                    'metodo' => $metodo,
                ];
            }

            $totalPagadoAnio = PagosSuscripcion::where('id_empresa', $idEmpresa)
                ->where('estado', 1)
                ->whereYear('created_at', $hoy->year)
                ->sum('valor');

            return response()->json([
                'success' => true,
                'data'    => [
                    'estado_suscripcion'   => $estadoSuscripcion,
                    'fecha_siguiente_pago' => $fechaSiguientePago
                        ? Carbon::parse($fechaSiguientePago)->format('d/m/Y')
                        : null,
                    'monto_siguiente_pago' => round($montoSiguientePago, 2),
                    'ultimo_pago'          => $ultimoPagoData,
                    'total_pagado_anio'    => round($totalPagadoAnio, 2),
                ],
                'message' => 'Resumen de pagos cargado con éxito!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'data'    => [],
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function consumoActualGet(Request $request)
    {
        try {
            $user = $request->user();
            $consumo = $this->calcularConsumoActual($user->id_empresa);

            return response()->json([
                'success' => true,
                'data'    => $consumo,
                'message' => 'Consumo actual calculado con éxito!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'data'    => [],
                'message' => $e->getMessage()
            ], 422);
        }
    }

    // ============================================================
    // WOMPI
    // ============================================================

    public function wompiAcceptanceToken(Request $request)
    {
        try {
            $token = (new \App\Helpers\Wompi())->acceptanceToken();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo obtener el acceptance token',
                ], 422);
            }

            return response()->json([
                'success'          => true,
                'acceptance_token' => $token,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function wompiFirmaIntegridad(Request $request)
    {
        $rules = [
            'reference'       => 'required|string',
            'amount_in_cents' => 'required|integer|min:1',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $reference     = $request->get('reference');
            $amountInCents = (int) $request->get('amount_in_cents');
            $currency      = 'COP';
            $secret        = config('services.wompi.integrity_secret');

            if (!$secret) {
                throw new Exception('Integrity secret no configurado en el .env');
            }

            $rawString = $reference . $amountInCents . $currency . $secret;
            $signature = hash('sha256', $rawString);

            return response()->json([
                'success'   => true,
                'signature' => $signature,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * PASO 1: Crear pago PENDIENTE antes de abrir el widget.
     * El backend genera el reference y lo guarda en token_db.
     */
    public function wompiCrearPagoPendiente(Request $request)
    {
        $rules = [
            'meses' => 'required|integer|min:1|max:24',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            DB::connection('clientes')->beginTransaction();

            $user = $request->user();
            $idEmpresa = $user->id_empresa;
            $meses = (int) $request->get('meses');

            $suscripcion = EmpresaSuscripcion::where('id_empresa', $idEmpresa)
                ->where('estado', '1')
                ->first();

            if (!$suscripcion) {
                throw new Exception('No hay suscripción activa.');
            }

            $desglose = $this->calcularDesglosePago($idEmpresa, $suscripcion->id, $meses);
            $monto = (float) $desglose['total'];

            if ($monto <= 0) {
                throw new Exception('El monto a pagar es 0.');
            }

            // 🔑 El BACKEND genera la referencia única
            $reference = 'SUSC_' . $idEmpresa . '_' . time() . '_' . rand(1000, 9999);

            $componentes = EmpresaComponentesSuscripcion::with('componente')
                ->where('id_empresa', $idEmpresa)
                ->where('id_empresa_suscripcion', $suscripcion->id)
                ->has('componente')
                ->get();

            $detalleComponentes = [];
            foreach ($componentes as $c) {
                $cant = max(1, (int) ($c->cantidad ?? 1));
                $desc = (float) ($c->descuento ?? 0);
                $detalleComponentes[] = [
                    'nombre'    => $c->componente->nombre ?? 'N/A',
                    'precio'    => (float) $c->precio,
                    'cantidad'  => $cant,
                    'descuento' => $desc,
                ];
            }

            $detalleJson = json_encode([
                'componentes' => $detalleComponentes,
                'meses'       => $meses,
                'desglose'    => $desglose,
                'reference'   => $reference,
            ]);

            $pago = PagosSuscripcion::create([
                'id_empresa'   => $idEmpresa,
                'id_usuario'   => $user->id,
                'token_db'     => $reference,
                'detalle_json' => $detalleJson,
                'valor'        => $monto,
                'estado'       => 2,
            ]);

            DB::connection('clientes')->commit();

            return response()->json([
                'success'   => true,
                'pago_id'   => $pago->id,
                'reference' => $reference,
                'monto'     => $monto,
                'message'   => 'Pago pendiente creado.',
            ]);

        } catch (Exception $e) {
            DB::connection('clientes')->rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * PASO 2: Reconciliar el pago (consultar Wompi y actualizar).
     */
    public function wompiReconciliarPago(Request $request)
    {
        $rules = [
            'pago_id'           => 'required|integer',
            'transaction_id'    => 'nullable|string',
            'reference'         => 'nullable|string',
            'payment_source_id' => 'nullable',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            DB::connection('clientes')->beginTransaction();

            $user = $request->user();
            $idEmpresa = $user->id_empresa;

            $pago = PagosSuscripcion::where('id', $request->get('pago_id'))
                ->where('id_empresa', $idEmpresa)
                ->first();

            if (!$pago) {
                throw new Exception('Pago no encontrado.');
            }

            $estadoAnterior = (int) $pago->estado;

            $wompi = new \App\Helpers\Wompi();

            if ($request->get('transaction_id')) {
                $transaccion = $wompi->obtenerTransaccion($request->get('transaction_id'));
            } else {
                $transaccion = $wompi->buscarTransaccionPorReference($pago->token_db);
            }

            if (!$transaccion->success) {
                throw new Exception('No se pudo consultar la transacción en Wompi: ' . $transaccion->message);
            }

            $data = $transaccion->data;

            if (isset($data[0])) {
                $data = $data[0];
            }

            $estadoWompi = $data['status'] ?? 'UNKNOWN';

            $estadoPago = 2;
            if ($estadoWompi === 'APPROVED') $estadoPago = 1;
            elseif (in_array($estadoWompi, ['DECLINED', 'VOIDED', 'ERROR'])) $estadoPago = 0;

            $detalle = json_decode($pago->detalle_json, true) ?: [];

            $detalle['wompi_transaction_id'] = $data['id'] ?? null;
            $detalle['wompi_status'] = $estadoWompi;
            $detalle['metodo_pago'] = $data['payment_method']['type'] ?? 'Tarjeta';

            $pago->detalle_json = json_encode($detalle);
            $pago->estado = $estadoPago;

            if (!empty($data['id'])) {
                $pago->token_db = $data['id'];
            }

            $pago->save();

            if ($estadoPago === 1 && $estadoAnterior !== 1) {
                $suscripcion = EmpresaSuscripcion::where('id_empresa', $idEmpresa)
                    ->where('estado', '1')
                    ->first();

                $paymentSourceId = $request->get('payment_source_id')
                    ?? ($data['payment_source_id'] ?? null);

                if ($suscripcion && $paymentSourceId) {
                    $suscripcion->id_forma_pago = $paymentSourceId;
                    $suscripcion->save();
                }

                $meses = (int) ($detalle['meses'] ?? 1);
                if ($meses < 1) $meses = 1;

                $componentes = EmpresaComponentesSuscripcion::where('id_empresa', $idEmpresa)
                    ->where('id_empresa_suscripcion', $suscripcion->id ?? 0)
                    ->has('componente')
                    ->get();

                $hoy = Carbon::now()->startOfDay();

                foreach ($componentes as $c) {
                    $fechaVencimiento = $c->fecha_siguiente_cobro
                        ? Carbon::parse($c->fecha_siguiente_cobro)->startOfDay()
                        : null;

                    $esVencido           = $fechaVencimiento && $fechaVencimiento->lt($hoy);
                    $esPorVencerMismoMes = $fechaVencimiento && !$esVencido && $fechaVencimiento->isSameMonth($hoy);
                    $esNuevo             = !$fechaVencimiento;
                    $esActivo            = $fechaVencimiento && !$esVencido && !$esPorVencerMismoMes;

                    if ($esActivo) {
                        // ═══════════════════════════════════════════════════
                        // CASO 2: ACTIVO
                        // fecha_inicio_suscripcion NO se modifica
                        // fecha_siguiente_cobro avanza N meses
                        // ═══════════════════════════════════════════════════
                        $nuevaFechaCobro = $fechaVencimiento->copy()->addMonthsNoOverflow($meses);
                        $c->fecha_siguiente_cobro = $nuevaFechaCobro->format('Y-m-d');
                    } elseif ($esVencido) {
                        // ═══════════════════════════════════════════════════
                        // CASO 1: VENCIDO
                        // fecha_inicio_suscripcion = día 1 del mes de fecha_siguiente_cobro
                        // fecha_siguiente_cobro = día 1 + N meses
                        // ═══════════════════════════════════════════════════
                        $fechaInicioCiclo = $fechaVencimiento->copy()->startOfMonth();
                        $nuevaFechaCobro  = $fechaInicioCiclo->copy()->addMonthsNoOverflow($meses);

                        $c->fecha_inicio_suscripcion = $fechaInicioCiclo->format('Y-m-d');
                        $c->fecha_siguiente_cobro    = $nuevaFechaCobro->format('Y-m-d');
                    } elseif ($esPorVencerMismoMes) {
                        // ═══════════════════════════════════════════════════
                        // CASO 4: POR VENCER EN EL MISMO MES
                        // fecha_inicio_suscripcion = hoy
                        // fecha_siguiente_cobro = día 1 del mes siguiente + (N-1) meses
                        // ═══════════════════════════════════════════════════
                        $c->fecha_inicio_suscripcion = $hoy->format('Y-m-d');

                        $primerDiaMesSiguiente = $hoy->copy()->startOfMonth()->addMonth();
                        $nuevaFechaCobro = $primerDiaMesSiguiente->copy()->addMonthsNoOverflow($meses - 1);

                        $c->fecha_siguiente_cobro = $nuevaFechaCobro->format('Y-m-d');
                    } else {
                        // ═══════════════════════════════════════════════════
                        // CASO 3: NUEVO
                        // fecha_inicio_suscripcion = hoy
                        // fecha_siguiente_cobro = día 1 del mes siguiente + (N-1) meses
                        // ═══════════════════════════════════════════════════
                        $c->fecha_inicio_suscripcion = $hoy->format('Y-m-d');

                        $primerDiaMesSiguiente = $hoy->copy()->startOfMonth()->addMonth();
                        $nuevaFechaCobro = $primerDiaMesSiguiente->copy()->addMonthsNoOverflow($meses - 1);

                        $c->fecha_siguiente_cobro = $nuevaFechaCobro->format('Y-m-d');
                    }

                    $c->save();
                }

                // Recalcular precio total de la suscripción
                $totalComponentes = 0;
                foreach ($componentes as $c) {
                    $cant = max(1, (int) ($c->cantidad ?? 1));
                    $desc = (float) ($c->descuento ?? 0);
                    $totalComponentes += ((float) $c->precio - $desc) * $cant;
                }
                if ($suscripcion) {
                    $suscripcion->precio = $totalComponentes;
                    $suscripcion->save();
                }
            }

            DB::connection('clientes')->commit();

            return response()->json([
                'success'      => true,
                'estado'       => $estadoPago,
                'wompi_status' => $estadoWompi,
                'message'      => 'Pago reconciliado correctamente.',
            ]);

        } catch (Exception $e) {
            DB::connection('clientes')->rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function wompiDescuentosGet(Request $request)
    {
        try {
            $descuentos = \App\Models\Empresas\DescuentoSuscripcion::where('activo', 1)
                ->orderBy('meses', 'asc')
                ->get(['meses', 'descuento_porcentaje']);

            return response()->json([
                'success' => true,
                'data'    => $descuentos,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function wompiPreviewPago(Request $request)
    {
        $rules = [
            'meses' => 'required|integer|min:1|max:24',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $user = $request->user();
            $idEmpresa = $user->id_empresa;

            $suscripcion = EmpresaSuscripcion::where('id_empresa', $idEmpresa)
                ->where('estado', '1')
                ->first();

            if (!$suscripcion) {
                throw new Exception('No hay suscripción activa.');
            }

            $desglose = $this->calcularDesglosePago($idEmpresa, $suscripcion->id, $request->get('meses'));

            return response()->json([
                'success' => true,
                'data'    => $desglose,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ============================================================
    // HELPERS PRIVADOS
    // ============================================================

    private function calcularConsumoActual($idEmpresa)
    {
        $hoy = Carbon::now()->startOfDay();

        $suscripcionActiva = EmpresaSuscripcion::where('id_empresa', $idEmpresa)
            ->where('estado', '1')
            ->first();

        if (!$suscripcionActiva) {
            return [
                'hoy'             => $hoy->format('Y-m-d'),
                'id_suscripcion'  => null,
                'total_mensual'   => 0,
                'total_al_dia'    => 0,
                'total_pendiente' => 0,
                'detalle'         => [],
            ];
        }

        $componentes = EmpresaComponentesSuscripcion::with('componente')
            ->where('id_empresa', $idEmpresa)
            ->where('id_empresa_suscripcion', $suscripcionActiva->id)
            ->has('componente')
            ->get();

        $detalle = [];
        $totalAlDia = 0;
        $totalMensual = 0;

        foreach ($componentes as $comp) {
            if (!$comp->fecha_inicio_suscripcion || !$comp->fecha_siguiente_cobro) {
                continue;
            }

            $fechaInicio = Carbon::parse($comp->fecha_inicio_suscripcion)->startOfDay();
            $fechaFin    = Carbon::parse($comp->fecha_siguiente_cobro)->startOfDay();

            $diasTotales = (int) $fechaInicio->diffInDays($fechaFin);
            if ($diasTotales <= 0) $diasTotales = 30;

            $diasTranscurridos = (int) $fechaInicio->diffInDays($hoy, false);
            if ($diasTranscurridos < 0) $diasTranscurridos = 0;
            elseif ($diasTranscurridos > $diasTotales) $diasTranscurridos = $diasTotales;

            $cantidad = (int) ($comp->cantidad ?? 1);
            if ($cantidad <= 0) $cantidad = 1;

            $precioUnitario = (float) $comp->precio;
            $precioMensual  = $precioUnitario * $cantidad;
            $valorAlDia     = round($precioMensual * ($diasTranscurridos / $diasTotales), 2);

            $totalMensual += $precioMensual;
            $totalAlDia   += $valorAlDia;

            $detalle[] = [
                'id'                       => $comp->id,
                'id_componente'            => $comp->id_componente,
                'nombre'                   => $comp->componente->nombre ?? 'N/A',
                'cantidad'                 => $cantidad,
                'precio_unitario'          => $precioUnitario,
                'precio_mensual'           => $precioMensual,
                'fecha_inicio_suscripcion' => $comp->fecha_inicio_suscripcion,
                'fecha_siguiente_cobro'    => $comp->fecha_siguiente_cobro,
                'dias_totales'             => $diasTotales,
                'dias_transcurridos'       => $diasTranscurridos,
                'valor_al_dia'             => $valorAlDia,
                'porcentaje_consumido'     => round(($diasTranscurridos / $diasTotales) * 100, 2),
            ];
        }

        return [
            'hoy'             => $hoy->format('Y-m-d'),
            'id_suscripcion'  => $suscripcionActiva->id,
            'total_mensual'   => round($totalMensual, 2),
            'total_al_dia'    => round($totalAlDia, 2),
            'total_pendiente' => round($totalMensual - $totalAlDia, 2),
            'detalle'         => $detalle,
        ];
    }

    private function calcularProrrateoComponente($comp, Carbon $hoy = null)
    {
        $hoy = $hoy ?: Carbon::now()->startOfDay();

        $cantidad       = max(1, (int) ($comp->cantidad ?? 1));
        $precioUnitario = (float) $comp->precio;
        $descuentoUnit  = (float) ($comp->descuento ?? 0);

        $precioUnitarioNeto = max(0, $precioUnitario - $descuentoUnit);
        $precioMensual = $precioUnitarioNeto * $cantidad;

        if (!$comp->fecha_inicio_suscripcion || !$comp->fecha_siguiente_cobro) {
            return [
                'cantidad'             => $cantidad,
                'precio_unitario'      => $precioUnitario,
                'descuento_unitario'   => $descuentoUnit,
                'precio_mensual'       => $precioMensual,
                'meses_ciclo'          => 1,
                'precio_total_ciclo'   => round($precioMensual, 2),
                'dias_totales'         => 30,
                'dias_transcurridos'   => 30,
                'porcentaje_consumido' => 100,
                'valor_al_dia'         => round($precioMensual, 2),
                'valor_pendiente'      => 0,
            ];
        }

        $fechaInicio = Carbon::parse($comp->fecha_inicio_suscripcion)->startOfDay();
        $fechaFin    = Carbon::parse($comp->fecha_siguiente_cobro)->startOfDay();

        $diasTotales = (int) $fechaInicio->diffInDays($fechaFin);
        if ($diasTotales <= 0) $diasTotales = 30;

        $mesesCiclo = round($diasTotales / 30, 4);
        if ($mesesCiclo <= 0) $mesesCiclo = 1;

        $precioTotalCiclo = round($precioMensual * $mesesCiclo, 2);

        $diasTranscurridos = (int) $fechaInicio->diffInDays($hoy, false);
        if ($diasTranscurridos < 0) $diasTranscurridos = 0;
        elseif ($diasTranscurridos > $diasTotales) $diasTranscurridos = $diasTotales;

        $porcentaje = round(($diasTranscurridos / $diasTotales) * 100, 2);
        $valorAlDia = round($precioTotalCiclo * ($diasTranscurridos / $diasTotales), 2);

        return [
            'cantidad'             => $cantidad,
            'precio_unitario'      => $precioUnitario,
            'descuento_unitario'   => $descuentoUnit,
            'precio_mensual'       => $precioMensual,
            'meses_ciclo'          => $mesesCiclo,
            'precio_total_ciclo'   => $precioTotalCiclo,
            'dias_totales'         => $diasTotales,
            'dias_transcurridos'   => $diasTranscurridos,
            'porcentaje_consumido' => $porcentaje,
            'valor_al_dia'         => $valorAlDia,
            'valor_pendiente'      => round($precioTotalCiclo - $valorAlDia, 2),
        ];
    }

    private function calcularDesglosePago($idEmpresa, $idEmpresaSuscripcion, $meses)
    {
        $meses = max(1, (int) $meses);
        $hoy   = Carbon::now()->startOfDay();

        $componentes = EmpresaComponentesSuscripcion::with('componente')
            ->where('id_empresa', $idEmpresa)
            ->where('id_empresa_suscripcion', $idEmpresaSuscripcion)
            ->has('componente')
            ->get();

        if ($componentes->isEmpty()) {
            return [
                'monto_mes_actual'  => 0,
                'monto_meses_extra' => 0,
                'subtotal'          => 0,
                'descuento'         => 0,
                'porcentaje'        => 0,
                'total'             => 0,
                'meses'             => $meses,
                'detalle_meses'     => [],
            ];
        }

        $precioMensualTotal = 0;

        foreach ($componentes as $c) {
            $cant = max(1, (int) ($c->cantidad ?? 1));
            $desc = (float) ($c->descuento ?? 0);
            $precioMensualTotal += ((float) $c->precio - $desc) * $cant;
        }

        $suscripcion = EmpresaSuscripcion::where('id_empresa', $idEmpresa)
            ->where('estado', '1')
            ->first();

        $fechaProximoCobro = null;
        if ($suscripcion) {
            $fechaProximoCobro = EmpresaComponentesSuscripcion::where('id_empresa', $idEmpresa)
                ->where('id_empresa_suscripcion', $suscripcion->id)
                ->has('componente')
                ->min('fecha_siguiente_cobro');
        }

        // 🔑 Determinar el caso
        $esVencido          = false;
        $esPorVencerMismoMes = false; // vence en el mismo mes actual
        $esActivo           = false;  // vence en mes futuro
        $esNuevo            = true;

        if ($fechaProximoCobro) {
            $fechaVencimiento = Carbon::parse($fechaProximoCobro)->startOfDay();

            if ($fechaVencimiento->lt($hoy)) {
                // VENCIDO (ya pasó)
                $esVencido = true;
                $esNuevo   = false;
            } elseif ($fechaVencimiento->isSameMonth($hoy)) {
                // POR VENCER en el mismo mes (ej. hoy 11, vence 12)
                $esPorVencerMismoMes = true;
                $esNuevo             = false;
            } else {
                // ACTIVO (vence en mes futuro)
                $esActivo = true;
                $esNuevo  = false;
            }
        }

        if ($esActivo) {
            // ═══════════════════════════════════════════════════
            // CASO 2: ACTIVO (vence en mes futuro)
            // Paga meses completos
            // ═══════════════════════════════════════════════════
            $montoMesActual  = round($precioMensualTotal, 2);
            $montoMesesExtra = round($precioMensualTotal * ($meses - 1), 2);

            $diasDelMes    = 30;
            $diasRestantes = 30;

            $labelMesActual = 'Mes completo';
        } elseif ($esVencido) {
            // ═══════════════════════════════════════════════════
            // CASO 1: VENCIDO
            // Cobra desde fecha_siguiente_cobro hasta fin de ese mes
            // ═══════════════════════════════════════════════════
            $fechaInicioCiclo = Carbon::parse($fechaProximoCobro)->startOfDay();
            $fechaFinCiclo    = $fechaInicioCiclo->copy()->endOfMonth();

            $primerDiaDelMes = $fechaInicioCiclo->copy()->startOfMonth();
            $ultimoDiaDelMes = $fechaInicioCiclo->copy()->endOfMonth();
            $diasDelMes      = $primerDiaDelMes->diffInDays($ultimoDiaDelMes) + 1;

            $diasRestantes = $fechaInicioCiclo->diffInDays($fechaFinCiclo) + 1;
            if ($diasRestantes < 1) $diasRestantes = 1;
            if ($diasRestantes > $diasDelMes) $diasRestantes = $diasDelMes;

            $factorMesActual = $diasRestantes / $diasDelMes;
            $montoMesActual  = round($precioMensualTotal * $factorMesActual, 2);

            $mesesExtra      = $meses - 1;
            $montoMesesExtra = round($precioMensualTotal * $mesesExtra, 2);

            $labelMesActual = 'Mes vencido (' . $diasRestantes . '/' . $diasDelMes . ' días)';
        } elseif ($esPorVencerMismoMes) {
            // ═══════════════════════════════════════════════════
            // CASO NUEVO: POR VENCER EN EL MISMO MES
            // Cobra desde hoy hasta fin de mes (proporcional)
            // ═══════════════════════════════════════════════════
            $fechaInicioCiclo = $hoy->copy();
            $fechaFinCiclo    = $hoy->copy()->endOfMonth();

            $primerDiaDelMes = $hoy->copy()->startOfMonth();
            $ultimoDiaDelMes = $hoy->copy()->endOfMonth();
            $diasDelMes      = $primerDiaDelMes->diffInDays($ultimoDiaDelMes) + 1;

            $diasRestantes = $fechaInicioCiclo->diffInDays($fechaFinCiclo) + 1;
            if ($diasRestantes < 1) $diasRestantes = 1;
            if ($diasRestantes > $diasDelMes) $diasRestantes = $diasDelMes;

            $factorMesActual = $diasRestantes / $diasDelMes;
            $montoMesActual  = round($precioMensualTotal * $factorMesActual, 2);

            $mesesExtra      = $meses - 1;
            $montoMesesExtra = round($precioMensualTotal * $mesesExtra, 2);

            $labelMesActual = 'Mes actual (' . $diasRestantes . '/' . $diasDelMes . ' días)';
        } else {
            // ═══════════════════════════════════════════════════
            // CASO 3: NUEVO (sin fecha_siguiente_cobro)
            // Cobra desde hoy hasta día 1 del mes siguiente
            // ═══════════════════════════════════════════════════
            $fechaInicioCiclo = $hoy->copy();
            $fechaFinCiclo    = $hoy->copy()->startOfMonth()->addMonth();

            $primerDiaDelMes = $hoy->copy()->startOfMonth();
            $ultimoDiaDelMes = $hoy->copy()->endOfMonth();
            $diasDelMes      = $primerDiaDelMes->diffInDays($ultimoDiaDelMes) + 1;

            $diasRestantes = $fechaInicioCiclo->diffInDays($fechaFinCiclo);
            if ($diasRestantes < 1) $diasRestantes = 1;

            $factorMesActual = $diasRestantes / $diasDelMes;
            $montoMesActual  = round($precioMensualTotal * $factorMesActual, 2);

            $mesesExtra      = $meses - 1;
            $montoMesesExtra = round($precioMensualTotal * $mesesExtra, 2);

            $labelMesActual = 'Mes actual (' . $diasRestantes . '/' . $diasDelMes . ' días)';
        }

        $subtotal = round($montoMesActual + $montoMesesExtra, 2);

        $descuentoRow = \App\Models\Empresas\DescuentoSuscripcion::where('meses', $meses)
            ->where('activo', 1)
            ->first();

        $porcentaje = $descuentoRow ? (float) $descuentoRow->descuento_porcentaje : 0;
        $descuento  = round($subtotal * ($porcentaje / 100), 2);

        $total = round($subtotal - $descuento, 2);

        return [
            'monto_mes_actual'   => $montoMesActual,
            'monto_meses_extra'  => $montoMesesExtra,
            'subtotal'           => $subtotal,
            'descuento'          => $descuento,
            'porcentaje'         => $porcentaje,
            'total'              => $total,
            'meses'              => $meses,
            'precio_mensual'     => round($precioMensualTotal, 2),
            'dias_del_mes'       => $diasDelMes,
            'dias_restantes'     => $diasRestantes,
            'detalle_meses'      => [
                [
                    'label' => $labelMesActual,
                    'monto' => $montoMesActual,
                ],
                [
                    'label' => ($meses - 1) . ' mes(es) completo(s)',
                    'monto' => $montoMesesExtra,
                ],
            ],
        ];
    }
    
}
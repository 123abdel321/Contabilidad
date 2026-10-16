<?php

namespace App\Http\Controllers\Configuracion;

use DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessProvisionedDatabase;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;
// MODELS
use App\Models\User;
use App\Models\Empresas\Empresa;
use App\Models\Empresas\UsuarioEmpresa;
use App\Models\Empresas\PagosSuscripcion;
use App\Models\Empresas\EmpresaSuscripcion;
use App\Models\Empresas\ComponentesSuscripcion;
use App\Models\Empresas\EmpresaComponentesSuscripcion;


class SuscripcionController extends Controller
{
    use BegDocumentHelpersTrait;
    
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
        $q = $request->get("q");

        // 🔑 Traer componentes tipo 0 (simples) y tipo 1 (con hijos)
        $query = ComponentesSuscripcion::query();

        if ($q) {
            $query->where('nombre', 'LIKE', '%' . $q . '%');
        }

        $componentes = $query->orderBy('id', 'asc')->get();

        // 🔑 Separar padres (tipo 1) y simples (tipo 0)
        $padres = $componentes->where('tipo', 1);
        $hijos  = $componentes->where('id_padre', '!=', null);

        $resultado = [];

        foreach ($componentes as $c) {
            // Solo listar los que NO tienen padre (raíz)
            if ($c->id_padre) continue;

            $item = [
                'id'           => $c->id,
                'text'         => $c->nombre . ' - ' . number_format($c->precio, 2),
                'nombre'       => $c->nombre,
                'tipo'         => (int) $c->tipo,
                'precio'       => (int) $c->precio,
                'rango_desde'  => $c->rango_desde,
                'rango_hasta'  => $c->rango_hasta,
                'id_padre'     => $c->id_padre,
                'hijos'        => [],
            ];

            // 🔑 Si es tipo 1, agregar sus hijos
            if ((int) $c->tipo === 1) {
                $hijosDelPadre = $componentes->where('id_padre', $c->id);

                foreach ($hijosDelPadre as $hijo) {
                    $item['hijos'][] = [
                        'id'          => $hijo->id,
                        'text'        => $hijo->nombre,
                        'nombre'      => $hijo->nombre,
                        'precio'      => (int) $hijo->precio,
                        'rango_desde' => $hijo->rango_desde,
                        'rango_hasta' => $hijo->rango_hasta,
                    ];
                }
            }

            $resultado[] = $item;
        }

        return response()->json([
            'data' => $resultado,
        ]);
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

            // 🔑 Obtener la empresa: primero intentar por user->id_empresa, sino por owner en estado 5
            $idEmpresa = $user->id_empresa;
            $empresa = null;
            if (!$idEmpresa) {
                $empresa = Empresa::where('id_usuario_owner', $user->id)
                    ->where('estado', 5)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($empresa) {
                    $idEmpresa = $empresa->id;
                }
            } else {
                $empresa = Empresa::where('id', $user->id_empresa)->first();
            }

            if (!$idEmpresa) {
                throw new Exception('No hay empresa asociada al usuario.');
            }

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
            if ($estadoPago === 1 && $empresa->estado != 1) {
            // if ($estadoPago === 1 && $estadoAnterior !== 1) {
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
                        // Cobra 1 mes completo (o N meses completos)
                        // ═══════════════════════════════════════════════════
                        $montoMesActual  = round($precioMensualTotal, 2);
                        $montoMesesExtra = round($precioMensualTotal * ($meses - 1), 2);

                        $diasDelMes    = 30;
                        $diasRestantes = 30;

                        $labelMesActual = 'Mes vencido (completo)';
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

                // 🔑 Activar la empresa: 5 → 1 y asociar al usuario
                $empresa = Empresa::where('id', $idEmpresa)->first();

                if ($empresa && $empresa->estado == 5) {
                    // Asociar la empresa al usuario (para el middleware)
                    $user->id_empresa  = $empresa->id;
                    $user->has_empresa = $empresa->token_db;
                    $user->save();

                    DB::connection('clientes')->commit();

                    ProcessProvisionedDatabase::dispatch(
                        $empresa->id,
                        'empresas_generales',
                        $user->remember_token
                    );

                    \Log::info('Provisión de BD despachada para empresa ' . $empresa->id);

                    return response()->json([
                        'success'      => true,
                        'estado'       => $estadoPago,
                        'wompi_status' => $estadoWompi,
                        'message'      => 'Pago reconciliado correctamente.',
                    ]);
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
                'line' => $e->getLine(),
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
    // REGISTRO
    // ============================================================

    public function registerIndex(Request $request)
    {
        // 🔑 Si NO está logueado, mandarlo al login
        if (!Auth::check()) {
            return redirect('/login');
        }

        return view('auth.login', [
            'mode' => 'register',
            'step' => (int) $request->get('step', 1),
        ]);
    }

    /**
     * POST /api/register-crear-cuenta — público
     */
    public function registerCrearCuenta(Request $request)
    {
        $rules = [
            'email'    => 'required|email|unique:clientes.users,email',
            'password' => 'required|string|min:6',
            'nombre'   => 'required|string|max:100',
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

            $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            $user = User::create([
                'firstname'           => $request->get('nombre'),
                'email'               => $request->get('email'),
                'password'            => $request->get('password'),
                'codigo_verificacion' => $codigo,
            ]);

            // Enviar correo con el código
            $this->sendGenericEmail(
                $request->get('email'),
                'Verifica tu cuenta - Portafolio ERP',
                'emails.registro.verificacion',
                [
                    'nombre_completo' => $request->get('nombre'),
                    'codigo'          => $codigo,
                ]
            );

            // Crear token Sanctum (mismo patrón que tu login)
            $token = $user->createToken('register')->plainTextToken;
            $user->remember_token = $token;
            $user->save();

            DB::connection('clientes')->commit();

            return response()->json([
                'success'      => true,
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user_id'      => $user->id,
                'message'      => 'Cuenta creada. Revisa tu correo.',
            ]);

        } catch (\Exception $e) {
            DB::connection('clientes')->rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/register-verificar-codigo — autenticado
     */
    public function registerVerificarCodigo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|size:6',
        ], $this->messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado.');
            }

            if ($user->codigo_verificacion !== $request->get('codigo')) {
                throw new \Exception('Código incorrecto.');
            }

            $user->email_verified_at   = Carbon::now();
            $user->codigo_verificacion = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Cuenta verificada.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/register-reenviar-codigo — autenticado
     */
    public function registerReenviarCodigo(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado.');
            }

            if ($user->email_verified_at) {
                throw new \Exception('Esta cuenta ya está verificada.');
            }

            $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->codigo_verificacion = $codigo;
            $user->save();
            
            $this->sendGenericEmail(
                $user->email,
                'Verifica tu cuenta - Portafolio ERP',
                'emails.registro.verificacion',
                [
                    'nombre_completo' => $user->firstname,
                    'codigo'          => $codigo,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Código reenviado.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }



    /**
     * Lee un RUT oficial de la DIAN (PDF) y devuelve los datos para pre-llenar el registro.
     *
     * POR QUÉ FALLABA LA VERSIÓN ANTERIOR:
     * Smalot devuelve el texto del RUT con TODAS las etiquetas juntas ("35. Razón social",
     * "41. Dirección principal"...) y los VALORES aparte, más abajo. Por eso un regex del tipo
     * "35. Razón social (valor)" nunca encuentra nada. Además los números salen casillero por
     * casillero y las responsabilidades se escriben "48 - Impuesto..." (con espacio).
     *
     * SOLUCIÓN:
     * El PDF de la DIAN es una plantilla fija: cada campo se dibuja en una coordenada (x, y)
     * concreta. Se leen esas coordenadas del contenido de la página 1 y se asigna cada valor
     * a su campo por posición. Si el PDF no tiene esa estructura, se usa un respaldo por texto.
     *
     * Requiere: smalot/pdfparser (ya lo tienes) y las clases Request / Validator ya importadas.
     */
    public function registerCargarRut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rut' => 'required|mimes:pdf|max:10240',
        ], $this->messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $parser = new \Smalot\PdfParser\Parser();

            try {
                $pdf = $parser->parseFile($request->file('rut')->getRealPath());
            } catch (\Throwable $e) {
                throw new \Exception('No se pudo leer el PDF. Verifica que no esté protegido con contraseña ni dañado.');
            }

            $datos = $this->rutExtraerDatos($pdf);

            if ($datos['nit'] === '') {
                throw new \Exception('No se pudo extraer el NIT del RUT. Verifica que sea un RUT oficial de la DIAN.');
            }

            return response()->json([
                'success' => true,
                'datos'   => (object) $datos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Extrae los datos de la hoja 1 del RUT (formulario 001) leyendo por posición.
     */
    private function rutExtraerDatos(\Smalot\PdfParser\Document $pdf): array
    {
        $d = [
            'nit' => '', 'dv' => '',
            'tipo_contribuyente' => '', // 1 = persona jurídica, 2 = persona natural
            'tipo_documento' => '',
            'numero_identificacion' => '',
            'primer_apellido' => '', 'segundo_apellido' => '',
            'primer_nombre' => '', 'otros_nombres' => '',
            'razon_social' => '', 'nombre_comercial' => '', 'sigla' => '',
            'pais' => 'COLOMBIA', 'pais_codigo' => '',
            'departamento' => '', 'departamento_codigo' => '',
            'ciudad' => '', 'ciudad_codigo' => '',
            'direccion' => '', 'email' => '', 'codigo_postal' => '',
            'telefono_1' => '', 'telefono_2' => '',
            'actividad_economica' => '', 'fecha_inicio_actividad' => '',
            'actividad_secundaria' => '', 'otras_actividades' => [],
            'direccion_seccional' => '', 'numero_formulario' => '',
            'responsabilidades' => [], 'responsabilidades_detalle' => [],
        ];

        $pages = $pdf->getPages();
        if (empty($pages)) {
            return $d;
        }

        $items = $this->rutLeerItems($pages[0]);

        if ($items) {
            // ── NIT + DV (casilleros 5 y 6, fila superior; el último dígito es el DV)
            $digitos = preg_replace('/\D/', '', $this->rutCajas($items, 603.5, 2.5, 0, 200));
            if (strlen($digitos) >= 6) {
                $d['nit'] = substr($digitos, 0, -1);
                $d['dv']  = substr($digitos, -1);
            }

            $d['direccion_seccional'] = $this->rutTexto($items, 607.5, 2, 200, 450);
            $d['numero_formulario']   = preg_replace('/\D/', '', $this->rutTexto($items, 704.5, 2, 400, 700));

            // ── Tipo de contribuyente (24) y documento (25, 26)
            $tipo = preg_replace('/\D/', '', $this->rutCajas($items, 570, 2.5, 165, 185));
            if ($tipo === '') {
                $texto = mb_strtolower($this->rutTexto($items, 569.5, 2, 20, 160));
                $tipo = strpos($texto, 'natural') !== false ? '2' : (strpos($texto, 'jur') !== false ? '1' : '');
            }
            $d['tipo_contribuyente'] = $tipo;

            $d['tipo_documento']        = preg_replace('/\D/', '', $this->rutCajas($items, 570, 2.5, 280, 310));
            $d['numero_identificacion'] = preg_replace('/\D/', '', $this->rutCajas($items, 570, 2.5, 330, 500));

            // ── Nombres (31-34), razón social (35), comercial (36), sigla (37)
            $d['primer_apellido']   = $this->rutTexto($items, 521.5, 2, 20, 150);
            $d['segundo_apellido']  = $this->rutTexto($items, 521.5, 2, 150, 277);
            $d['primer_nombre']     = $this->rutTexto($items, 521.5, 2, 277, 402);
            $d['otros_nombres']     = $this->rutTexto($items, 521.5, 2, 402, 700);
            $d['razon_social']      = $this->rutTexto($items, 497.5, 2, 20, 700);
            $d['nombre_comercial']  = $this->rutTexto($items, 473.5, 2, 20, 340);
            $d['sigla']             = $this->rutTexto($items, 473.5, 2, 340, 700);

            // ── Ubicación (38-40): nombre = texto largo, código = casilleros de 1 carácter
            $fila = array_filter($items, fn ($i) => abs($i['y'] - 437.5) <= 1.5);
            $nombre = function (float $xMin, float $xMax) use ($fila) {
                $r = array_filter($fila, fn ($i) => $i['x'] >= $xMin && $i['x'] < $xMax && mb_strlen($i['t']) > 1);
                usort($r, fn ($a, $b) => $a['x'] <=> $b['x']);
                return trim(implode(' ', array_column($r, 't')));
            };
            $codigo = function (float $xMin, float $xMax) use ($fila) {
                $r = array_filter($fila, fn ($i) => $i['x'] >= $xMin && $i['x'] < $xMax && mb_strlen($i['t']) === 1);
                usort($r, fn ($a, $b) => $a['x'] <=> $b['x']);
                return preg_replace('/\D/', '', implode('', array_column($r, 't')));
            };
            $d['pais']                = $nombre(20, 190) ?: 'COLOMBIA';
            $d['pais_codigo']         = $codigo(150, 200);
            $d['departamento']        = $nombre(190, 385);
            $d['departamento_codigo'] = $codigo(350, 410);
            $d['ciudad']              = $nombre(385, 700);
            $d['ciudad_codigo']       = $codigo(540, 610);

            // ── Dirección (41), correo (42), postal (43), teléfonos (44, 45)
            $d['direccion']     = $this->rutTexto($items, 412.5, 2, 20, 700);
            $d['email']         = strtolower($this->rutTexto($items, 401.5, 2, 90, 700));
            $d['codigo_postal'] = preg_replace('/\D/', '', $this->rutCajas($items, 387.5, 2, 70, 200));
            $d['telefono_1']    = preg_replace('/\D/', '', $this->rutCajas($items, 387.5, 2, 240, 440));
            $d['telefono_2']    = preg_replace('/\D/', '', $this->rutCajas($items, 387.5, 2, 440, 700));

            // ── Actividades económicas (46-50)
            $y = 329.5;
            $d['actividad_economica']     = preg_replace('/\D/', '', $this->rutCajas($items, $y, 2, 25, 72));
            $d['fecha_inicio_actividad']  = $this->rutFecha($this->rutCajas($items, $y, 2, 75, 158));
            $d['actividad_secundaria']    = preg_replace('/\D/', '', $this->rutCajas($items, $y, 2, 160, 207));
            foreach ([[330, 376], [376, 425]] as [$a, $b]) {
                $cod = preg_replace('/\D/', '', $this->rutCajas($items, $y, 2, $a, $b));
                if ($cod !== '') {
                    $d['otras_actividades'][] = $cod;
                }
            }

            // ── Responsabilidades (53): líneas "05- Impto. renta...", "48 - Impuesto sobre las ventas..."
            $lineas = array_filter($items, fn ($i) => $i['x'] < 40 && $i['y'] < 292 && $i['y'] > 120);
            usort($lineas, fn ($a, $b) => $b['y'] <=> $a['y']);
            foreach ($lineas as $l) {
                if (preg_match('/^(\d{1,2})\s*-\s*(.+)$/u', $l['t'], $m)) {
                    $cod = str_pad($m[1], 2, '0', STR_PAD_LEFT);
                    if (!in_array($cod, $d['responsabilidades'], true)) {
                        $d['responsabilidades'][]         = $cod;
                        $d['responsabilidades_detalle'][] = ['codigo' => $cod, 'descripcion' => trim($m[2])];
                    }
                }
            }
        }

        // ── Respaldo por texto si la lectura por posición no encontró el NIT
        if ($d['nit'] === '') {
            $this->rutRespaldoTexto($pages[0], $d);
        }

        // ── Normalización final
        if ($d['tipo_contribuyente'] === '') {
            $d['tipo_contribuyente'] = ($d['primer_apellido'] !== '' || $d['primer_nombre'] !== '') ? '2' : '1';
        }
        if ($d['tipo_documento'] === '') {
            $d['tipo_documento'] = $d['tipo_contribuyente'] === '1' ? '31' : '13'; // 31 = NIT, 13 = cédula
        }
        if ($d['tipo_contribuyente'] === '2') {
            $d['numero_identificacion'] = $d['numero_identificacion'] ?: $d['nit'];
            if ($d['razon_social'] === '') {
                $d['razon_social'] = trim(preg_replace('/\s+/', ' ',
                    "{$d['primer_nombre']} {$d['otros_nombres']} {$d['primer_apellido']} {$d['segundo_apellido']}"));
            }
        }
        if ($d['dv'] === '' && $d['nit'] !== '') {
            $d['dv'] = $this->rutCalcularDv($d['nit']);
        }

        return $d;
    }

    /**
     * Devuelve [['x'=>, 'y'=>, 't'=>texto], ...] con cada texto de la página y su posición real.
     * (getDataTm de Smalot no sirve aquí: desfasa las posiciones cuando hay campos vacíos.)
     */
    private function rutLeerItems(\Smalot\PdfParser\Page $page): array
    {
        $contents = $page->get('Contents');
        $stream   = '';

        if ($contents instanceof \Smalot\PdfParser\Element\ElementArray) {
            foreach ($contents->getContent() as $c) {
                $stream .= $c->getContent() . "\n";
            }
        } elseif ($contents instanceof \Smalot\PdfParser\Element\ElementMissing
            || $contents instanceof \Smalot\PdfParser\Element\ElementNull) {
            return [];
        } elseif ($contents instanceof \Smalot\PdfParser\PDFObject) {
            $elements = $contents->getHeader()->getElements();
            if (is_numeric(key($elements))) {
                foreach ($elements as $el) {
                    $stream .= ($el instanceof \Smalot\PdfParser\Element\ElementXRef
                        ? $el->getObject()->getContent()
                        : $el->getContent()) . "\n";
                }
            } else {
                $stream = $contents->getContent();
            }
        }

        if ($stream === '') {
            return [];
        }

        $num = '(-?[\d.]+)';
        preg_match_all(
            "/{$num}\s+{$num}\s+{$num}\s+{$num}\s+{$num}\s+{$num}\s+Tm\s*\(((?:\\\\.|[^\\\\)])*)\)\s*Tj/s",
            $stream,
            $matches,
            PREG_SET_ORDER
        );

        $items = [];
        foreach ($matches as $m) {
            $t = trim($this->rutDecodificar($m[7]));
            if ($t === '') {
                continue;
            }
            $items[] = ['x' => (float) $m[5], 'y' => (float) $m[6], 't' => $t];
        }

        return $items;
    }

    /** Convierte una cadena literal de PDF (escapes y octales, WinAnsi) a UTF-8. */
    private function rutDecodificar(string $raw): string
    {
        $s = preg_replace_callback('/\\\\([0-7]{1,3}|.)/s', function ($m) {
            if (preg_match('/^[0-7]{1,3}$/', $m[1])) {
                return chr(octdec($m[1]) & 0xFF);
            }
            return ['n' => "\n", 'r' => "\r", 't' => "\t", 'b' => "\x08", 'f' => "\f"][$m[1]] ?? $m[1];
        }, $raw);

        return mb_convert_encoding($s, 'UTF-8', 'Windows-1252');
    }

    /** Items de una fila (y ± tolerancia) y rango horizontal, ordenados de izquierda a derecha. */
    private function rutZona(array $items, float $y, float $tolY, float $xMin, float $xMax): array
    {
        $r = array_filter($items, fn ($i) => abs($i['y'] - $y) <= $tolY && $i['x'] >= $xMin && $i['x'] < $xMax);
        usort($r, fn ($a, $b) => $a['x'] <=> $b['x']);
        return $r;
    }

    /** Une casilleros (un carácter por caja) sin separador. */
    private function rutCajas(array $items, float $y, float $tolY, float $xMin, float $xMax): string
    {
        return implode('', array_column($this->rutZona($items, $y, $tolY, $xMin, $xMax), 't'));
    }

    /** Une textos de una zona con espacio simple. */
    private function rutTexto(array $items, float $y, float $tolY, float $xMin, float $xMax): string
    {
        return trim(preg_replace('/\s+/u', ' ', implode(' ', array_column($this->rutZona($items, $y, $tolY, $xMin, $xMax), 't'))));
    }

    /** "20250701" → "2025-07-01" */
    private function rutFecha(string $crudo): string
    {
        $n = preg_replace('/\D/', '', $crudo);
        return strlen($n) === 8 ? substr($n, 0, 4) . '-' . substr($n, 4, 2) . '-' . substr($n, 6, 2) : '';
    }

    /** Dígito de verificación oficial de la DIAN (módulo 11). */
    private function rutCalcularDv(string $nit): string
    {
        $pesos = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
        $n = strrev(preg_replace('/\D/', '', $nit));
        $suma = 0;
        for ($i = 0, $l = min(strlen($n), 15); $i < $l; $i++) {
            $suma += (int) $n[$i] * $pesos[$i];
        }
        $r = $suma % 11;
        return (string) ($r > 1 ? 11 - $r : $r);
    }

    /** Respaldo: lectura por texto plano para PDFs que no tengan la estructura habitual. */
    private function rutRespaldoTexto(\Smalot\PdfParser\Page $page, array &$d): void
    {
        $t = preg_replace('/\s+/u', ' ', $page->getText());

        // NIT + DV pegados justo antes de "Impuestos de <ciudad>"
        if (preg_match('/(\d{7,11})\s*Impuestos\s+de\b/iu', $t, $m)) {
            $d['nit'] = substr($m[1], 0, -1);
            $d['dv']  = substr($m[1], -1);
        }
        if (preg_match('/Persona\s+natural/iu', $t)) {
            $d['tipo_contribuyente'] = '2';
        } elseif (preg_match('/Persona\s+jur[ií]dica/iu', $t)) {
            $d['tipo_contribuyente'] = '1';
        }
        if (preg_match('/Persona\s+jur[ií]dica\s*\d?\s+(.+?)\s+COLOMBIA\b/iu', $t, $m)) {
            $d['razon_social'] = trim($m[1]);
        }
        if (preg_match('/COLOMBIA\s*\d{0,3}\s*([A-Za-zÁÉÍÓÚÑáéíóúñ ]+?)\s*\d{1,2}\s*([A-Za-zÁÉÍÓÚÑáéíóúñ ]+?)\s*\d{1,3}\b/u', $t, $m)) {
            $d['departamento'] = trim($m[1]);
            $d['ciudad']       = trim($m[2]);
        }
        if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $t, $m)) {
            $d['email'] = strtolower($m[0]);
        }
        if (preg_match_all('/(?<!\d)(\d{2})\s*-\s+[A-ZÁÉÍÓÚÑ][a-záéíóúñ]/u', $t, $m)) {
            $d['responsabilidades'] = array_values(array_unique($m[1]));
        }
    }

    /**
     * POST /api/register-crear-empresa — autenticado
     * Crea la empresa con estado = 5 (instalando). La provisión de BD se hace
     * cuando se aprueba el pago (ProcessProvisionedDatabase).
     */
    public function registerCrearEmpresa(Request $request)
    {
        $rules = [
            'nit'                 => 'required|max:200',
            'dv'                  => 'required|numeric',
            'tipo_contribuyente'  => 'required|in:1,2',
            'razon_social'        => 'nullable|string|max:120',
            'primer_apellido'     => 'nullable|string|max:60',
            'segundo_apellido'    => 'nullable|string|max:60',
            'primer_nombre'       => 'nullable|string|max:60',
            'otros_nombres'       => 'nullable|string|max:60',
            'direccion'           => 'nullable|string|max:200',
            'email'               => 'nullable|email|max:200',
            'telefono'            => 'nullable|string|max:50',
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

            // 🔑 Validar NIT existente
            $empresaExistente = Empresa::where('nit', $request->get('nit'))->first();

            if ($empresaExistente) {
                if ($empresaExistente->estado == 5 && $empresaExistente->id_usuario_owner == $user->id) {
                    // Mismo usuario, misma empresa en instalación → actualizar
                    $empresa = $empresaExistente;
                } elseif ($empresaExistente->estado == 5) {
                    throw new Exception('Ya existe una empresa en proceso de instalación con ese NIT.');
                } else {
                    throw new Exception('La empresa con NIT ' . $request->get('nit') . ' ya está registrada.');
                }
            } else {
                $empresa = new Empresa();
            }

            $nombreEmpresa = $request->get('razon_social')
                ?: ($request->get('primer_nombre') . ' ' . $request->get('primer_apellido'));

            // 🔑 Datos
            $empresa->servidor           = 'sam';
            $empresa->nombre             = $nombreEmpresa;
            $empresa->primer_apellido    = $request->get('primer_apellido');
            $empresa->segundo_apellido   = $request->get('segundo_apellido');
            $empresa->primer_nombre      = $request->get('primer_nombre');
            $empresa->otros_nombres      = $request->get('otros_nombres');
            $empresa->tipo_contribuyente = $request->get('tipo_contribuyente');
            $empresa->razon_social       = $request->get('razon_social');
            $empresa->nit                = $request->get('nit');
            $empresa->dv                 = $request->get('dv');
            $empresa->direccion          = $request->get('direccion');
            $empresa->email              = $request->get('email');
            $empresa->telefono           = $request->get('telefono');
            $empresa->id_usuario_owner   = $user->id;
            $empresa->estado             = 5;

            if (!$empresa->exists) {
                // 🔑 Primera vez: guardar para obtener el ID, luego asignar token_db + hash
                $empresa->save();

                $empresa->token_db = $this->generateUniqueNameDb($empresa);
                $empresa->hash     = \Hash::make($empresa->id);
                $empresa->save();
            } else {
                // Ya existía en estado 5 → solo actualizar
                $empresa->save();
            }

            // 🔑 Asociar el usuario a la empresa
            $this->associateUserToCompanyRegister($user, $empresa);

            DB::connection('clientes')->commit();

            return response()->json([
                'success'    => true,
                'empresa_id' => $empresa->id,
                'message'    => 'Empresa creada en estado de instalación.',
                'empresa'    => [
                    'id'                 => $empresa->id,
                    'razon_social'       => $empresa->razon_social ?: $empresa->nombre,
                    'nit'                => $empresa->nit,
                    'dv'                 => $empresa->dv,
                    'logo'               => $empresa->logo,
                    'notificacion_code'  => $empresa->notificacion_code ?? null,
                    'fondo_sistema'      => $empresa->fondo_sistema ?? null,
                ],
            ]);

        } catch (\Exception $e) {
            DB::connection('clientes')->rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/register-mi-progreso — autenticado
     */
    public function registerMiProgreso(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado.',
                ], 401);
            }

            $empresa = Empresa::where('id_usuario_owner', $user->id)
                ->where('estado', 5)
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'email_verificado' => (bool) $user->email_verified_at,
                    'empresa' => $empresa ? [
                        'id'                 => $empresa->id,
                        'razon_social'       => $empresa->razon_social,
                        'nit'                => $empresa->nit,
                        'dv'                 => $empresa->dv,
                        'tipo_contribuyente' => $empresa->tipo_contribuyente,
                        'primer_nombre'      => $empresa->primer_nombre,
                        'primer_apellido'    => $empresa->primer_apellido,
                        'segundo_apellido'   => $empresa->segundo_apellido,
                        'otros_nombres'      => $empresa->otros_nombres,
                        'direccion'          => $empresa->direccion,
                        'email'              => $empresa->email,
                        'telefono'           => $empresa->telefono,
                    ] : null,
                ],
            ]);

        } catch (\Exception $e) {
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

        $fechaInicio   = Carbon::parse($comp->fecha_inicio_suscripcion)->startOfDay();
        $fechaFinCiclo = Carbon::parse($comp->fecha_siguiente_cobro)->startOfDay()->addDay();

        $diasTotales = (int) $fechaInicio->diffInDays($fechaFinCiclo);
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

    public function calcularDesglosePago($idEmpresa, $idEmpresaSuscripcion, $meses)
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
        $esVencido           = false;
        $esPorVencerMismoMes = false;
        $esActivo            = false;
        $esNuevo             = true;

        if ($fechaProximoCobro) {
            $fechaVencimiento = Carbon::parse($fechaProximoCobro)->startOfDay();

            if ($fechaVencimiento->lt($hoy)) {
                $esVencido = true;
                $esNuevo   = false;
            } elseif ($fechaVencimiento->isSameMonth($hoy)) {
                $esPorVencerMismoMes = true;
                $esNuevo             = false;
            } else {
                $esActivo = true;
                $esNuevo  = false;
            }
        }

        if ($esActivo) {
            // ═══════════════════════════════════════════════════
            // CASO 2: ACTIVO (vence en mes futuro) → paga meses completos
            // ═══════════════════════════════════════════════════
            $montoMesActual  = round($precioMensualTotal, 2);
            $montoMesesExtra = round($precioMensualTotal * ($meses - 1), 2);

            $diasDelMes    = 30;
            $diasRestantes = 30;

            $labelMesActual = 'Mes completo';
        } elseif ($esVencido) {
            // ═══════════════════════════════════════════════════
            // CASO 1: VENCIDO
            // El ciclo viejo ya cerró → cobramos proporcional
            // del MES SIGUIENTE al vencimiento hasta hoy
            // ═══════════════════════════════════════════════════
            $fechaVencimiento = Carbon::parse($fechaProximoCobro)->startOfDay();

            // 🔑 El nuevo ciclo arranca el día 1 del mes siguiente al vencimiento
            $inicioNuevoCiclo = $fechaVencimiento->copy()->startOfMonth()->addMonth();

            // 🔑 Días del mes natural del nuevo ciclo
            $primerDiaDelMes = $inicioNuevoCiclo->copy()->startOfMonth();
            $ultimoDiaDelMes = $inicioNuevoCiclo->copy()->endOfMonth();
            $diasDelMes      = $primerDiaDelMes->diffInDays($ultimoDiaDelMes) + 1;

            // 🔑 VENCIDO → siempre cobra el mes completo (no proporcional)
            $diasTranscurridos = $diasDelMes;

            $factorMesActual = $diasDelMes > 0 ? ($diasTranscurridos / $diasDelMes) : 1;
            $montoMesActual  = round($precioMensualTotal * $factorMesActual, 2);

            $mesesExtra      = $meses - 1;
            $montoMesesExtra = round($precioMensualTotal * $mesesExtra, 2);

            $diasRestantes  = $diasTranscurridos;
            $labelMesActual = 'Mes actual (' . $diasTranscurridos . '/' . $diasDelMes . ' días)';
        } elseif ($esPorVencerMismoMes) {
            // ═══════════════════════════════════════════════════
            // CASO 4: POR VENCER EN EL MISMO MES
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

    // ============================================================
    // REGISTRO — PASO 4: COMPONENTES + PAGO
    // ============================================================

    /**
     * GET /api/suscripcion/componentes-arbol
     * Devuelve los componentes padre con sus hijos.
     */
    public function componentesArbolGet(Request $request)
    {
        try {
            $componentes = ComponentesSuscripcion::orderBy('id', 'asc')->get();

            $resultado = [];

            foreach ($componentes as $c) {
                if ($c->id_padre) continue;

                $item = [
                    'id'          => $c->id,
                    'nombre'      => $c->nombre,
                    'tipo'        => (int) $c->tipo,
                    'precio'      => (int) $c->precio,
                    'rango_desde' => $c->rango_desde,
                    'rango_hasta' => $c->rango_hasta,
                    'hijos'       => [],
                ];

                if ((int) $c->tipo === 1) {
                    foreach ($componentes->where('id_padre', $c->id) as $hijo) {
                        $item['hijos'][] = [
                            'id'          => $hijo->id,
                            'nombre'      => $hijo->nombre,
                            'precio'      => (int) $hijo->precio,
                            'rango_desde' => $hijo->rango_desde,
                            'rango_hasta' => $hijo->rango_hasta,
                        ];
                    }
                }

                $resultado[] = $item;
            }

            return response()->json([
                'success' => true,
                'data'    => $resultado,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/suscripcion/registro/confirmar
     * Guarda los componentes seleccionados, crea la suscripción, el pago pendiente
     * y devuelve todo lo necesario para abrir el widget de Wompi.
     */
    public function registroConfirmar(Request $request)
    {
        $rules = [
            'meses'                => 'required|integer|min:1|max:24',
            'componentes'          => 'required|array|min:1',
            'componentes.*.id'     => 'required|integer',
            'componentes.*.precio' => 'required|numeric|min:0',
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
            $meses = (int) $request->get('meses');

            // 🔑 Obtener la empresa del usuario (en estado 5 = instalación)
            // NO usamos $user->id_empresa porque puede ser null (el owner no está vinculado aún)
            $empresa = Empresa::where('id_usuario_owner', $user->id)
                ->where('estado', 5)
                ->orderBy('id', 'desc')
                ->first();

            if (!$empresa) {
                throw new Exception('No hay empresa en proceso de instalación.');
            }

            $idEmpresa = $empresa->id;

            // 🔑 Eliminar cualquier componente previo (por si reintenta)
            EmpresaComponentesSuscripcion::where('id_empresa', $idEmpresa)->delete();
            EmpresaSuscripcion::where('id_empresa', $idEmpresa)->delete();

            // 🔑 Crear la suscripción
            $suscripcion = EmpresaSuscripcion::create([
                'id_empresa'               => $idEmpresa,
                'id_suscripcion'           => 1,
                'id_forma_pago'            => 1,
                'dias_para_pagar'          => 0,
                'dias_de_gracia'           => 0,
                'fecha_inicio_suscripcion' => Carbon::now()->format('Y-m-d'),
                'fecha_inicio_facturacion' => Carbon::now()->format('Y-m-d'),
                'estado'                   => 1,
                'duracion'                 => $meses,
                'precio'                   => 0,
                'descuento'                => 0,
            ]);

            // 🔑 Guardar los componentes seleccionados
            $totalMensual = 0;
            $componentesDetalle = [];

            foreach ($request->get('componentes') as $c) {
                $componente = ComponentesSuscripcion::find($c['id']);

                if (!$componente) continue;

                $precio = (float) $c['precio'];
                $totalMensual += $precio;

                EmpresaComponentesSuscripcion::create([
                    'id_empresa'               => $idEmpresa,
                    'id_empresa_suscripcion'   => $suscripcion->id,
                    'id_componente'            => $componente->id,
                    'cantidad'                 => 1,
                    'precio'                   => $precio,
                    'descuento'                => 0,
                    'fecha_inicio_suscripcion' => null,
                    'fecha_siguiente_cobro'    => null,
                    'created_by'               => $user->id,
                    'updated_by'               => $user->id,
                ]);

                $componentesDetalle[] = [
                    'nombre'   => $componente->nombre,
                    'precio'   => $precio,
                    'cantidad' => 1,
                ];
            }

            // 🔑 Calcular desglose
            $desglose = $this->calcularDesglosePago($idEmpresa, $suscripcion->id, $meses);
            $monto = (float) $desglose['total'];

            if ($monto <= 0) {
                throw new Exception('El monto a pagar es 0.');
            }

            $suscripcion->precio = $totalMensual;
            $suscripcion->save();

            // 🔑 Crear pago pendiente
            $reference = 'SUSC_' . $idEmpresa . '_' . time() . '_' . rand(1000, 9999);

            $detalleJson = json_encode([
                'componentes' => $componentesDetalle,
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
     * POST /api/suscripcion/registro/preview-pago
     * Calcula el desglose del pago durante el registro (empresa aún no creada).
     */
    public function registroPreviewPago(Request $request)
    {
        $rules = [
            'meses'                => 'required|integer|min:1|max:24',
            'componentes'          => 'required|array|min:1',
            'componentes.*.id'     => 'required|integer',
            'componentes.*.precio' => 'required|numeric|min:0',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $meses = (int) $request->get('meses');
            $hoy   = Carbon::now()->startOfDay();

            // 🔑 Calcular el precio mensual total (suma de componentes)
            $precioMensualTotal = 0;
            foreach ($request->get('componentes') as $c) {
                $precioMensualTotal += (float) $c['precio'];
            }

            // 🔑 Como es NUEVO (empresa no existe), siempre aplica CASO 3: proporcional desde hoy
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

            $subtotal = round($montoMesActual + $montoMesesExtra, 2);

            // 🔑 Buscar descuento
            $descuentoRow = \App\Models\Empresas\DescuentoSuscripcion::where('meses', $meses)
                ->where('activo', 1)
                ->first();

            $porcentaje = $descuentoRow ? (float) $descuentoRow->descuento_porcentaje : 0;
            $descuento  = round($subtotal * ($porcentaje / 100), 2);

            $total = round($subtotal - $descuento, 2);

            return response()->json([
                'success' => true,
                'data'    => [
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
                            'label' => 'Mes actual (' . $diasRestantes . '/' . $diasDelMes . ' días)',
                            'monto' => $montoMesActual,
                        ],
                        [
                            'label' => $mesesExtra . ' mes(es) completo(s)',
                            'monto' => $montoMesesExtra,
                        ],
                    ],
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Genera un nombre único para token_db basado en razon_social + nit.
     * NO crea la BD física, solo devuelve el string.
     */
    private function generateUniqueNameDb($empresa)
    {
        $razonSocial = str_replace(" ", "_", strtolower($empresa->razon_social ?: $empresa->nombre));
        return 'portafolio_' . $razonSocial . '_' . $empresa->nit;
    }

    /**
     * Asocia el usuario a la empresa en usuarios_empresas.
     * NO toca permisos ni roles todavía (eso se hace cuando se activa la empresa).
     */
    private function associateUserToCompanyRegister($user, $empresa)
    {
        $usuarioEmpresa = UsuarioEmpresa::where('id_usuario', $user->id)
            ->where('id_empresa', $empresa->id)
            ->first();

        if (!$usuarioEmpresa) {
            UsuarioEmpresa::create([
                'id_usuario' => $user->id,
                'id_empresa' => $empresa->id,
                'id_rol'     => 2,
                'estado'     => 1,
            ]);
        }

        // 🔑 Crear permisos del owner (todos los permisos de los componentes_menus
        //    + los contenedores tipo_menu = 0)
        $idsComponentesMenu = \DB::connection('clientes')->table('componentes_menus')
            ->pluck('id')
            ->toArray();

        if (!empty($idsComponentesMenu)) {
            $idsPermisos = \DB::connection('clientes')->table('permissions')
                ->whereIn('id_componente_menu', $idsComponentesMenu)
                ->orderBy('id')
                ->pluck('id')
                ->toArray();

            if (!empty($idsPermisos)) {
                \App\Models\Empresas\UsuarioPermisos::updateOrCreate(
                    [
                        'id_user'    => $user->id,
                        'id_empresa' => $empresa->id,
                    ],
                    [
                        'ids_permission'             => implode(',', $idsPermisos),
                        'ids_bodegas_responsable'    => null,
                        'ids_resolucion_responsable' => null,
                    ]
                );
            }
        }
    }

        // ============================================================
    // PAGO PÚBLICO — LINK /pagar/{token}
    // ============================================================

    public function pagoPublicoIndex(Request $request)
    {
        $token = $request->get('token');
        return view('pages.pago-publico.pago-publico', [
            'token' => $token,
        ]);
    }

    /**
     * GET /api/pago-publico/info/{token}
     * Info de la empresa + sus componentes actuales + suscripción.
     */
    public function pagoPublicoInfo(Request $request, $token)
    {
        try {
            
            $link = app(\App\Http\Services\SuscripcionPagoLinkService::class)
                ->buscarPorToken($token);

            if (!$link) {
                return response()->json([
                    'success' => false,
                    'message' => 'Link inválido o expirado.',
                ], 404);
            }

            $empresa = $link->empresa;
            if (!$empresa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empresa no encontrada.',
                ], 404);
            }

            $suscripcion = EmpresaSuscripcion::where('id_empresa', $empresa->id)
                ->where('estado', '1')
                ->first();

            $componentesActuales = [];
            if ($suscripcion) {
                $componentesActuales = EmpresaComponentesSuscripcion::with('componente')
                    ->where('id_empresa', $empresa->id)
                    ->where('id_empresa_suscripcion', $suscripcion->id)
                    ->has('componente')
                    ->get()
                    ->map(function ($c) {
                        $cant = max(1, (int) ($c->cantidad ?? 1));
                        $desc = (float) ($c->descuento ?? 0);
                        return [
                            'id'              => $c->id,
                            'id_componente'   => $c->id_componente,
                            'nombre'          => $c->componente->nombre ?? 'N/A',
                            'cantidad'        => $cant,
                            'precio'          => (float) $c->precio,
                            'descuento'       => $desc,
                            'precio_mensual'  => (($c->precio - $desc) * $cant),
                            'fecha_siguiente_cobro' => $c->fecha_siguiente_cobro,
                        ];
                    });
            }

            // 🔑 IDs de componentes que ya tiene (para excluirlos del árbol)
            $idsContratados = $componentesActuales->pluck('id_componente')->toArray();

            $nombreCliente = $empresa->razon_social
                ?: trim("{$empresa->primer_nombre} {$empresa->otros_nombres} {$empresa->primer_apellido} {$empresa->segundo_apellido}");

            return response()->json([
                'success' => true,
                'data'    => [
                    'empresa' => [
                        'razon_social' => $nombreCliente ?: 'Cliente',
                        'nit'          => $empresa->nit,
                        'logo'         => $empresa->logo,
                    ],
                    'tiene_suscripcion'    => (bool) $suscripcion,
                    'id_suscripcion'       => $suscripcion->id ?? null,
                    'componentes_actuales' => $componentesActuales,
                    'ids_contratados'      => $idsContratados,   // ← NUEVO
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/pago-publico/preview
     * Body: { token, meses, componentes_extra: [{id, precio}] }
     *
     * Usa calcularDesglosePago para base + extras encima.
     */
    public function pagoPublicoPreview(Request $request)
    {
        $rules = [
            'token'                     => 'required|string',
            'meses'                     => 'required|integer|min:1|max:24',
            'componentes_extra'         => 'nullable|array',
            'componentes_extra.*.id'    => 'required_with:componentes_extra|integer',
            'componentes_extra.*.precio'=> 'required_with:componentes_extra|numeric|min:0',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $link = app(\App\Http\Services\SuscripcionPagoLinkService::class)
                ->buscarPorToken($request->get('token'));

            if (!$link) {
                throw new Exception('Link inválido o expirado.');
            }

            $empresa     = $link->empresa;
            $meses       = (int) $request->get('meses');
            $suscripcion = EmpresaSuscripcion::where('id_empresa', $empresa->id)
                ->where('estado', '1')
                ->first();

            // 🔑 Desglose base: componentes actuales (o 0 si no hay suscripción)
            $desglose = null;

            if ($suscripcion) {
                $desglose = $this->calcularDesglosePago($empresa->id, $suscripcion->id, $meses);
            } else {
                // Sin suscripción → trata como NUEVO con precio 0
                $desglose = [
                    'monto_mes_actual'  => 0,
                    'monto_meses_extra' => 0,
                    'subtotal'          => 0,
                    'descuento'         => 0,
                    'porcentaje'        => 0,
                    'total'             => 0,
                    'meses'             => $meses,
                    'precio_mensual'    => 0,
                    'dias_del_mes'      => 0,
                    'dias_restantes'    => 0,
                    'detalle_meses'     => [],
                ];
            }

            // 🔑 Componentes extra (nuevos que quiere agregar el cliente)
            $extra = $request->get('componentes_extra', []);
            $precioExtraMensual = 0;
            foreach ($extra as $c) {
                $precioExtraMensual += (float) $c['precio'];
            }

            // Proporcional de los extras (mismo criterio que el desglose base)
            $diasDelMes    = $desglose['dias_del_mes'] ?: 30;
            $diasRestantes = $desglose['dias_restantes'] ?: 30;
            $factor = $diasDelMes > 0 ? ($diasRestantes / $diasDelMes) : 1;

            $extraMesActual  = round($precioExtraMensual * $factor, 2);
            $extraMesesExtra = round($precioExtraMensual * ($meses - 1), 2);

            // 🔑 Recombinar
            $montoMesActual   = round($desglose['monto_mes_actual'] + $extraMesActual, 2);
            $montoMesesExtra  = round($desglose['monto_meses_extra'] + $extraMesesExtra, 2);
            $subtotal         = round($montoMesActual + $montoMesesExtra, 2);

            // Descuento se recalcula sobre el subtotal final
            $descuentoRow = \App\Models\Empresas\DescuentoSuscripcion::where('meses', $meses)
                ->where('activo', 1)
                ->first();
            $porcentaje = $descuentoRow ? (float) $descuentoRow->descuento_porcentaje : 0;
            $descuento  = round($subtotal * ($porcentaje / 100), 2);
            $total      = round($subtotal - $descuento, 2);

            return response()->json([
                'success' => true,
                'data'    => [
                    'monto_mes_actual'   => $montoMesActual,
                    'monto_meses_extra'  => $montoMesesExtra,
                    'subtotal'           => $subtotal,
                    'descuento'          => $descuento,
                    'porcentaje'         => $porcentaje,
                    'total'              => $total,
                    'meses'              => $meses,
                    'precio_mensual'     => round($desglose['precio_mensual'] + $precioExtraMensual, 2),
                    'dias_del_mes'       => $diasDelMes,
                    'dias_restantes'     => $diasRestantes,
                    'detalle_meses'      => [
                        ['label' => 'Mes actual', 'monto' => $montoMesActual],
                        ['label' => ($meses - 1) . ' mes(es) extra', 'monto' => $montoMesesExtra],
                    ],
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/pago-publico/crear-pago
     * Body: { token, meses, componentes_extra }
     */
    public function pagoPublicoCrearPago(Request $request)
    {
        $rules = [
            'token'                     => 'required|string',
            'meses'                     => 'required|integer|min:1|max:24',
            'componentes_extra'         => 'nullable|array',
            'componentes_extra.*.id'    => 'required_with:componentes_extra|integer',
            'componentes_extra.*.precio'=> 'required_with:componentes_extra|numeric|min:0',
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

            $link = app(\App\Http\Services\SuscripcionPagoLinkService::class)
                ->buscarPorToken($request->get('token'));

            if (!$link) {
                throw new Exception('Link inválido o expirado.');
            }

            $empresa     = $link->empresa;
            $idEmpresa   = $empresa->id;
            $meses       = (int) $request->get('meses');
            $suscripcion = EmpresaSuscripcion::where('id_empresa', $idEmpresa)
                ->where('estado', '1')
                ->first();

            // 🔑 Recalcular con la misma función
            $desglose = null;
            if ($suscripcion) {
                $desglose = $this->calcularDesglosePago($idEmpresa, $suscripcion->id, $meses);
            } else {
                $desglose = [
                    'monto_mes_actual'  => 0,
                    'monto_meses_extra' => 0,
                    'subtotal'          => 0,
                    'descuento'         => 0,
                    'porcentaje'        => 0,
                    'total'             => 0,
                    'precio_mensual'    => 0,
                    'dias_del_mes'      => 30,
                    'dias_restantes'    => 30,
                    'meses'             => $meses,
                    'detalle_meses'     => [],
                ];
            }

            $extra = $request->get('componentes_extra', []);
            $precioExtraMensual = 0;
            foreach ($extra as $c) {
                $precioExtraMensual += (float) $c['precio'];
            }

            $factor = $desglose['dias_del_mes'] > 0
                ? $desglose['dias_restantes'] / $desglose['dias_del_mes']
                : 1;

            $extraMesActual  = round($precioExtraMensual * $factor, 2);
            $extraMesesExtra = round($precioExtraMensual * ($meses - 1), 2);

            $montoMesActual  = round($desglose['monto_mes_actual'] + $extraMesActual, 2);
            $montoMesesExtra = round($desglose['monto_meses_extra'] + $extraMesesExtra, 2);
            $subtotal        = round($montoMesActual + $montoMesesExtra, 2);

            $descuentoRow = \App\Models\Empresas\DescuentoSuscripcion::where('meses', $meses)
                ->where('activo', 1)
                ->first();
            $porcentaje = $descuentoRow ? (float) $descuentoRow->descuento_porcentaje : 0;
            $descuento  = round($subtotal * ($porcentaje / 100), 2);
            $monto      = round($subtotal - $descuento, 2);

            if ($monto <= 0) {
                throw new Exception('El monto a pagar es 0.');
            }

            // Armar detalle de componentes (los actuales + los extra)
            $detalleComponentes = [];

            if ($suscripcion) {
                $actuales = EmpresaComponentesSuscripcion::with('componente')
                    ->where('id_empresa', $idEmpresa)
                    ->where('id_empresa_suscripcion', $suscripcion->id)
                    ->has('componente')
                    ->get();

                foreach ($actuales as $c) {
                    $detalleComponentes[] = [
                        'id_componente' => $c->id_componente,
                        'nombre'        => $c->componente->nombre ?? 'N/A',
                        'precio'        => (float) $c->precio,
                        'cantidad'      => max(1, (int) ($c->cantidad ?? 1)),
                        'descuento'     => (float) ($c->descuento ?? 0),
                        'es_nuevo'      => false,
                    ];
                }
            }

            foreach ($extra as $c) {
                $comp = ComponentesSuscripcion::find($c['id']);
                if (!$comp) continue;

                $detalleComponentes[] = [
                    'id_componente' => $comp->id,
                    'nombre'        => $comp->nombre,
                    'precio'        => (float) $c['precio'],
                    'cantidad'      => 1,
                    'descuento'     => 0,
                    'es_nuevo'      => true,
                ];
            }

            // Referencia única para Wompi
            $reference = 'PUB_' . $idEmpresa . '_' . time() . '_' . rand(1000, 9999);

            $detalleJson = json_encode([
                'componentes' => $detalleComponentes,
                'meses'       => $meses,
                'desglose'    => [
                    'monto_mes_actual'  => $montoMesActual,
                    'monto_meses_extra' => $montoMesesExtra,
                    'subtotal'          => $subtotal,
                    'descuento'         => $descuento,
                    'porcentaje'        => $porcentaje,
                    'total'             => $monto,
                ],
                'reference'   => $reference,
                'token_link'  => $request->get('token'),
            ]);

            $pago = PagosSuscripcion::create([
                'id_empresa'   => $idEmpresa,
                'id_usuario'   => $empresa->id_usuario_owner ?: 0,
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
     * POST /api/pago-publico/firma-integridad
     * Body: { reference, amount_in_cents }
     */
    public function pagoPublicoFirmaIntegridad(Request $request)
    {
        return $this->wompiFirmaIntegridad($request);
    }

    /**
     * POST /api/pago-publico/reconciliar
     * Body: { pago_id, transaction_id, reference, payment_source_id }
     *
     * Misma lógica que wompiReconciliarPago pero sin depender del user.
     */
    public function pagoPublicoReconciliar(Request $request)
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

            $pago = PagosSuscripcion::where('id', $request->get('pago_id'))->first();

            if (!$pago) {
                throw new Exception('Pago no encontrado.');
            }

            $idEmpresa = $pago->id_empresa;
            $empresa   = Empresa::find($idEmpresa);

            if (!$empresa) {
                throw new Exception('Empresa no encontrada.');
            }

            // Consultar Wompi
            $wompi = new \App\Helpers\Wompi();

            if ($request->get('transaction_id')) {
                $transaccion = $wompi->obtenerTransaccion($request->get('transaction_id'));
            } else {
                $transaccion = $wompi->buscarTransaccionPorReference($pago->token_db);
            }

            if (!$transaccion->success) {
                throw new Exception('No se pudo consultar la transacción: ' . $transaccion->message);
            }

            $data = $transaccion->data;
            if (isset($data[0])) $data = $data[0];

            $estadoWompi = $data['status'] ?? 'UNKNOWN';
            $estadoPago  = 2;
            if ($estadoWompi === 'APPROVED') $estadoPago = 1;
            elseif (in_array($estadoWompi, ['DECLINED', 'VOIDED', 'ERROR'])) $estadoPago = 0;

            $detalle = json_decode($pago->detalle_json, true) ?: [];
            $detalle['wompi_transaction_id'] = $data['id'] ?? null;
            $detalle['wompi_status']         = $estadoWompi;
            $detalle['metodo_pago']          = $data['payment_method']['type'] ?? 'Tarjeta';

            $pago->detalle_json = json_encode($detalle);
            $pago->estado       = $estadoPago;
            if (!empty($data['id'])) $pago->token_db = $data['id'];
            $pago->save();

            // Si fue aprobado → avanzar fechas + crear componentes nuevos
            if ($estadoPago === 1) {
                $suscripcion = EmpresaSuscripcion::where('id_empresa', $idEmpresa)
                    ->where('estado', '1')
                    ->first();

                if (!$suscripcion) {
                    throw new Exception('No hay suscripción activa para extender.');
                }

                $meses = (int) ($detalle['meses'] ?? 1);
                if ($meses < 1) $meses = 1;

                $hoy = Carbon::now()->startOfDay();

                // Componentes actuales → avanzar fecha_siguiente_cobro
                $actuales = EmpresaComponentesSuscripcion::where('id_empresa', $idEmpresa)
                    ->where('id_empresa_suscripcion', $suscripcion->id)
                    ->has('componente')
                    ->get();

                foreach ($actuales as $c) {
                    $fechaVencimiento = $c->fecha_siguiente_cobro
                        ? Carbon::parse($c->fecha_siguiente_cobro)->startOfDay()
                        : null;

                    // ---- CASO 0: sin fecha → nuevo ciclo desde hoy ----
                    if (!$fechaVencimiento) {
                        $c->fecha_inicio_suscripcion = $hoy->copy()->startOfMonth()->format('Y-m-d');
                        $c->fecha_siguiente_cobro    = $hoy->copy()
                            ->startOfMonth()
                            ->addMonthsNoOverflow($meses)
                            ->subDay()
                            ->format('Y-m-d');
                    }
                    // ---- CASO 1: vencido (< hoy) → reiniciar ciclo al mes actual ----
                    elseif ($fechaVencimiento->lt($hoy)) {
                        $inicioCiclo = $hoy->copy()->startOfMonth();
                        $c->fecha_inicio_suscripcion = $inicioCiclo->format('Y-m-d');
                        $c->fecha_siguiente_cobro    = $inicioCiclo
                            ->copy()
                            ->addMonthsNoOverflow($meses)
                            ->subDay()
                            ->format('Y-m-d');
                    }
                    // ---- CASO 2: aún vigente (>= hoy) → solo avanzar ----
                    else {
                        $c->fecha_siguiente_cobro = $fechaVencimiento
                            ->copy()
                            ->addMonthsNoOverflow($meses)
                            ->format('Y-m-d');
                    }

                    $c->save();
                }

                // Componentes NUEVOS → crearlos
                foreach (($detalle['componentes'] ?? []) as $c) {
                    if (empty($c['es_nuevo'])) continue;

                    $inicioCiclo = $hoy->copy()->startOfMonth();

                    EmpresaComponentesSuscripcion::create([
                        'id_empresa'               => $idEmpresa,
                        'id_empresa_suscripcion'   => $suscripcion->id,
                        'id_componente'            => $c['id_componente'],
                        'cantidad'                 => 1,
                        'precio'                   => (float) $c['precio'],
                        'descuento'                => 0,
                        'fecha_inicio_suscripcion' => $inicioCiclo->format('Y-m-d'),
                        'fecha_siguiente_cobro'    => $inicioCiclo
                            ->copy()
                            ->addMonthsNoOverflow($meses)
                            ->subDay()
                            ->format('Y-m-d'),
                        'created_by'               => $empresa->id_usuario_owner ?: 0,
                        'updated_by'               => $empresa->id_usuario_owner ?: 0,
                    ]);
                }

                // Recalcular precio total de la suscripción
                $totalComponentes = 0;
                foreach (EmpresaComponentesSuscripcion::where('id_empresa', $idEmpresa)
                            ->where('id_empresa_suscripcion', $suscripcion->id)
                            ->has('componente')->get() as $c) {
                    $cant = max(1, (int) ($c->cantidad ?? 1));
                    $desc = (float) ($c->descuento ?? 0);
                    $totalComponentes += ((float) $c->precio - $desc) * $cant;
                }
                $suscripcion->precio = $totalComponentes;
                $suscripcion->save();

                // Marcar el link como usado
                $tokenLink = $detalle['token_link'] ?? null;
                if ($tokenLink) {
                    $link = \App\Models\Empresas\SuscripcionPagoLink::where('token', $tokenLink)->first();
                    if ($link) {
                        $link->marcarComoUsado();
                    }
                }
            }

            DB::connection('clientes')->commit();

            return response()->json([
                'success'      => true,
                'estado'       => $estadoPago,
                'wompi_status' => $estadoWompi,
                'message'      => 'Pago reconciliado.',
            ]);

        } catch (Exception $e) {
            DB::connection('clientes')->rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
            ], 422);
        }
    }
    
}
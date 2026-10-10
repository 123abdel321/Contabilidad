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

    //COMPONENTES SUSCRIPCION

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
        $start = $request->get("start");
        $rowperpage = 20;

        $order_arr = $request->get('order');
        $searchValue = $request->get('search');
        $searchValue = isset($searchValue) ? $searchValue["value"] : null;

        $user = $request->user();
        $hoy  = Carbon::now()->startOfDay();

        // Solo componentes de la suscripción activa
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

        $sumaMensual     = 0; // suma de precio_mensual (1 mes)
        $sumaTotalCiclo  = 0; // suma de precio_total_ciclo (meses * mensual)
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
            'cantidad'              => $totalEmpresaSuscripcion,
            'cantidad_calc'         => $totalEmpresaSuscripcion,
            'precio'                => $sumaMensual,
            'precio_mensual'        => $sumaMensual,
            'dias_totales'          => null,
            'dias_transcurridos'    => null,
            'porcentaje_consumido'  => null,
            'valor_al_dia'          => $totalAlDia,
            'fecha_siguiente_cobro' => null,
        ];

        return response()->json([
            'success'              => true,
            'draw'                 => $draw,
            'iTotalRecords'        => $totalEmpresaSuscripcion,
            'iTotalDisplayRecords' => $totalEmpresaSuscripcion,
            'data'                 => $itemsArray,
            'perPage'              => $rowperpage,
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

            // Obtener el componente base
            $componente = ComponentesSuscripcion::find($request->get('id_componente'));

            if (!$componente) {
                throw new Exception('Componente no encontrado.');
            }

            // Determinar la suscripción activa
            $suscripcion = EmpresaSuscripcion::where('id_empresa', $idEmpresa)
                ->where('estado', '1')
                ->first();

            if (!$suscripcion) {
                throw new Exception('No se encontró una suscripción activa para esta empresa.');
            }

            // Cantidad: si NO es tipo cantidad, forzar 1
            $cantidad = 1;
            if ((int) $componente->tipo === 1) {
                $cantidad = (int) $request->get('cantidad');
                if ($cantidad < 1) {
                    $cantidad = 1;
                }
            }

            // Precio y descuento SIN combinar (Opción B)
            $precioUnitario = $esDios
                ? (float) $request->get('precio')
                : (float) $componente->precio;

            $descuentoUnitario = $esDios
                ? (float) $request->get('descuento')
                : 0;

            if ($descuentoUnitario < 0) {
                $descuentoUnitario = 0;
            }
            if ($descuentoUnitario > $precioUnitario) {
                $descuentoUnitario = $precioUnitario;
            }

            // Fechas
            $fechaInicioSuscripcion = $esDios
                ? $request->get('fecha_inicio')
                : Carbon::now()->format('Y-m-d');

            $fechaInicio         = Carbon::parse($fechaInicioSuscripcion)->startOfDay();
            $fechaSiguienteCobro = $fechaInicio->copy()->endOfMonth()->format('Y-m-d');

            // Si viene id => actualizar, si no => crear
            $idUp = $request->get('id');

            if ($idUp) {
                // EDITAR
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
                // CREAR
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

            // Recalcular precio total de la suscripción (precio - descuento) * cantidad
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

    //PAGOS SUSCRIPCION

    public function pagosGet(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = 20;

        $order_arr = $request->get('order');
        $searchValue = $request->get('search');
        $searchValue = isset($searchValue) ? $searchValue["value"] : null;

        $user = $request->user();

        $empresaSuscripcion = PagosSuscripcion::select(
                '*'
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
            'perPage'               => $rowperpage,
            'message'               => 'Suscripciones de empresa cargados con exito!'
        ]);
    }

    /**
     * Endpoint dedicado: devuelve el consumo al día de hoy de los
     * componentes de la suscripción activa de la empresa autenticada.
     */
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

    /**
     * Calcula el valor consumido al día de hoy para los componentes
     * de la suscripción activa de una empresa.
     */
    private function calcularConsumoActual($idEmpresa)
    {
        $hoy = Carbon::now()->startOfDay();

        // FIX: solo componentes de la suscripción activa
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

            // Días totales del ciclo de facturación
            $diasTotales = (int) $fechaInicio->diffInDays($fechaFin);
            if ($diasTotales <= 0) {
                $diasTotales = 30; // fallback
            }

            // FIX: diffInDays con flag false para manejar el signo directamente
            $diasTranscurridos = (int) $fechaInicio->diffInDays($hoy, false);
            if ($diasTranscurridos < 0) {
                $diasTranscurridos = 0;
            } elseif ($diasTranscurridos > $diasTotales) {
                $diasTranscurridos = $diasTotales;
            }

            // FIX: considerar cantidad
            $cantidad = (int) ($comp->cantidad ?? 1);
            if ($cantidad <= 0) {
                $cantidad = 1;
            }

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

    /**
     * Calcula el prorrateo de un componente según la fecha actual.
     * Devuelve un arreglo con cantidad, precio_mensual, días, % y valor al día.
     */
    private function calcularProrrateoComponente($comp, Carbon $hoy = null)
    {
        $hoy = $hoy ?: Carbon::now()->startOfDay();

        $cantidad       = max(1, (int) ($comp->cantidad ?? 1));
        $precioUnitario = (float) $comp->precio;
        $descuentoUnit  = (float) ($comp->descuento ?? 0);

        // Precio unitario efectivo (precio - descuento)
        $precioUnitarioNeto = max(0, $precioUnitario - $descuentoUnit);

        // Precio mensual = (precio - descuento) * cantidad
        $precioMensual = $precioUnitarioNeto * $cantidad;

        // Sin fechas: 1 mes al 100%
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
        if ($diasTotales <= 0) {
            $diasTotales = 30;
        }

        $mesesCiclo = round($diasTotales / 30, 4);
        if ($mesesCiclo <= 0) {
            $mesesCiclo = 1;
        }

        $precioTotalCiclo = round($precioMensual * $mesesCiclo, 2);

        $diasTranscurridos = (int) $fechaInicio->diffInDays($hoy, false);
        if ($diasTranscurridos < 0) {
            $diasTranscurridos = 0;
        } elseif ($diasTranscurridos > $diasTotales) {
            $diasTranscurridos = $diasTotales;
        }

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
}
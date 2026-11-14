<?php

namespace App\Http\Services;

use App\DTOs\RespuestaDTO;
use App\Models\Empresa;
use App\Models\EmpresaComponenteSuscripcion;
use App\Models\SuscripcionComponente;
use App\Traits\DataTableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuscripcionService
{
    use DataTableTrait;

    private const COLUMNS = ['id', 'componente_id', 'empresa_id', 'cantidad', 'precio', 'created_at'];

    // ============================================================
    // COMBO DE EMPRESAS (para el select del filtro)
    // ============================================================
    public function getComboEmpresas(int $sesion_user_id): RespuestaDTO
    {
        try {
            $permisoService = new PermisoService;
            $permisoService->validarPermisoGeneral(sesion_user_id: $sesion_user_id, rol_requerido: 'admin');

            $empresas = Empresa::query()
                ->select('id', 'numero_documento', 'razon_social')
                ->orderBy('razon_social')
                ->get()
                ->map(fn($e) => [
                    'id'               => $e->id,
                    'numero_documento' => $e->numero_documento,
                    'razon_social'     => $e->razon_social,
                    'texto'            => "{$e->numero_documento} - {$e->razon_social}",
                ]);

            return RespuestaDTO::exito()
                ->datos(['empresas' => $empresas])
                ->mensaje('Combo de empresas recuperado.')
                ->codigoSlug('suscripciones_combo_empresas_exito')
                ->get();

        } catch (\Exception $e) {
            Log::error($e);
            return RespuestaDTO::error($e)
                ->mensaje("Error al obtener combo de empresas: " . $e->getMessage())
                ->codigoSlug('suscripciones_combo_empresas_error')
                ->get();
        }
    }

    // ============================================================
    // COMBO DE COMPONENTES (para el modal de asignar múltiple)
    // Devuelve todos los componentes + los IDs ya asignados a la empresa
    // ============================================================
    public function getComboComponentes(int $sesion_user_id, ?int $empresaId = null): RespuestaDTO
    {
        try {
            $permisoService = new PermisoService;
            $permisoService->validarPermisoGeneral(sesion_user_id: $sesion_user_id, rol_requerido: 'admin');

            $componentes = SuscripcionComponente::query()
                ->activos()
                ->orderBy('tipo')
                ->orderBy('nombre')
                ->get()
                ->map(fn($c) => [
                    'id'               => $c->id,
                    'nombre'           => $c->nombre,
                    'tipo'             => $c->tipo,
                    'padre_id'         => $c->padre_id,
                    'precio'           => $c->precio,
                    'rango_desde'      => $c->rango_desde,
                    'rango_hasta'      => $c->rango_hasta,
                    'cantidad_default' => $c->cantidad_default ?? 1,
                    'texto'            => "{$c->nombre} ({$c->tipo})",
                ]);

            $asignados = $empresaId
                ? EmpresaComponenteSuscripcion::where('empresa_id', $empresaId)
                    ->where('activo', true)
                    ->pluck('componente_id')
                    ->values()
                : [];

            return RespuestaDTO::exito()
                ->datos(['componentes' => $componentes, 'asignados' => $asignados])
                ->mensaje('Combo de componentes recuperado.')
                ->codigoSlug('suscripciones_combo_componentes_exito')
                ->get();

        } catch (\Exception $e) {
            Log::error($e);
            return RespuestaDTO::error($e)
                ->mensaje("Error al obtener combo de componentes: " . $e->getMessage())
                ->codigoSlug('suscripciones_combo_componentes_error')
                ->get();
        }
    }

    // ============================================================
    // LISTADO POR EMPRESA (DataTable)
    // ============================================================
    public function getLista(Request $request, int $sesion_user_id): RespuestaDTO
    {
        try {
            $empresaId = $request->input('empresa_id');

            $permisoService = new PermisoService;
            $permisoService->validarPermisoGeneral(sesion_user_id: $sesion_user_id, rol_requerido: 'admin');

            if (!$empresaId) {
                return RespuestaDTO::exito()
                    ->datos(['data' => [
                        'draw'            => (int) $request->input('draw'),
                        'recordsTotal'    => 0,
                        'recordsFiltered' => 0,
                        'data'            => [],
                    ]])
                    ->mensaje('Seleccione una empresa.')
                    ->codigoSlug('suscripciones_lista_sin_empresa')
                    ->get();
            }

            $query = EmpresaComponenteSuscripcion::query()
                ->with(['componente', 'componente.padre'])
                ->where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderByDesc('id');

            return $this->getDataTableListNoOrder(
                $request,
                $query,
                function ($item) {
                    return [
                        'id'                => $item->id,
                        'componente_id'     => $item->componente_id,
                        'componente_nombre' => $item->componente->nombre ?? '-',
                        'componente_tipo'   => $item->componente->tipo ?? '-',
                        'componente_padre'  => $item->componente->padre->nombre ?? null,
                        'cantidad'          => $item->cantidad,
                        'precio'            => $item->precio,
                        'descuento'         => $item->descuento,
                        'fecha_inicio'      => optional($item->fecha_inicio)->format('Y-m-d'),
                        'fecha_fin'         => optional($item->fecha_fin)->format('Y-m-d'),
                        'activo'            => $item->activo,
                    ];
                }
            );

        } catch (\Exception $e) {
            Log::error($e);
            return RespuestaDTO::error($e)
                ->mensaje("Error al obtener la lista de suscripciones: " . $e->getMessage())
                ->codigoSlug('suscripciones_lista_error')
                ->get();
        }
    }

    // ============================================================
    // GUARDAR (asignar un componente a empresa - legacy)
    // ============================================================
    public function guardar(array $datos, int $sesion_user_id): RespuestaDTO
    {
        try {
            DB::beginTransaction();

            $permisoService = new PermisoService;
            $permisoService->validarPermisoGeneral(sesion_user_id: $sesion_user_id, rol_requerido: 'admin');

            if (empty($datos['empresa_id'])) {
                throw new \Exception('Debe seleccionar una empresa.');
            }
            if (empty($datos['componente_id'])) {
                throw new \Exception('Debe seleccionar un componente.');
            }

            $componente = SuscripcionComponente::findOrFail($datos['componente_id']);

            $cantidad = (int) ($datos['cantidad'] ?? 1);
            $precio   = (float) ($datos['precio'] ?? $componente->precio);
            $descuento = (float) ($datos['descuento'] ?? 0);

            if ($componente->tipo === 'valor') {
                $cantidad = 1;
                $precio   = (float) $componente->precio;
            }

            $existe = EmpresaComponenteSuscripcion::query()
                ->where('empresa_id', $datos['empresa_id'])
                ->where('componente_id', $datos['componente_id'])
                ->where('activo', true)
                ->exists();

            if ($existe) {
                throw new \Exception('Esta empresa ya tiene asignado ese componente.');
            }

            $registro = EmpresaComponenteSuscripcion::create([
                'empresa_id'    => $datos['empresa_id'],
                'componente_id' => $datos['componente_id'],
                'cantidad'      => $cantidad,
                'precio'        => $precio,
                'descuento'     => $descuento,
                'fecha_inicio'  => $datos['fecha_inicio'] ?? now()->toDateString(),
                'fecha_fin'     => $datos['fecha_fin'] ?? null,
                'activo'        => true,
            ]);

            DB::commit();

            return RespuestaDTO::exito()
                ->datos(['registro' => $registro])
                ->mensaje('Componente asignado a la empresa con éxito.')
                ->codigoSlug('suscripcion_guardar_exito')
                ->get();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return RespuestaDTO::error($e)
                ->mensaje("Error al guardar: " . $e->getMessage())
                ->codigoSlug('suscripcion_guardar_error')
                ->get();
        }
    }

    // ============================================================
    // GUARDAR MÚLTIPLE (asignar varios componentes a una empresa)
    // ============================================================
    public function guardarMultiple(array $datos, int $sesion_user_id): RespuestaDTO
    {
        try {
            DB::beginTransaction();

            $permisoService = new PermisoService;
            $permisoService->validarPermisoGeneral(sesion_user_id: $sesion_user_id, rol_requerido: 'admin');

            $empresaId = $datos['empresa_id'] ?? null;
            $items     = collect($datos['items'] ?? []);

            if (!$empresaId) throw new \Exception('Debe seleccionar una empresa.');
            if ($items->isEmpty()) throw new \Exception('Debe seleccionar al menos un componente.');

            Empresa::findOrFail($empresaId);

            $ids = $items->pluck('componente_id');
            if ($ids->count() !== $ids->unique()->count()) {
                throw new \Exception('Hay componentes repetidos en la solicitud.');
            }

            $componentes = SuscripcionComponente::whereIn('id', $ids)->get()->keyBy('id');

            $existentes = EmpresaComponenteSuscripcion::with('componente')
                ->where('empresa_id', $empresaId)
                ->where('activo', true)
                ->get();

            $yaAsignados  = $existentes->pluck('componente_id')->all();
            $padresUsados = $existentes->pluck('componente.padre_id')->filter()->all();

            $creados = [];

            foreach ($items as $item) {
                $c = $componentes->get($item['componente_id'] ?? null);
                if (!$c) throw new \Exception('Componente no encontrado.');

                if ($c->tipo === 'selector') {
                    throw new \Exception("«{$c->nombre}» es un selector; debe elegir uno de sus rangos.");
                }
                if (in_array($c->id, $yaAsignados)) {
                    throw new \Exception("La empresa ya tiene asignado «{$c->nombre}».");
                }
                if ($c->padre_id) {
                    if (in_array($c->padre_id, $padresUsados)) {
                        throw new \Exception("Solo se permite un rango por selector («{$c->nombre}»).");
                    }
                    $padresUsados[] = $c->padre_id;
                }

                $cantidad  = (int) ($item['cantidad'] ?? 1);
                $precio    = (float) ($item['precio'] ?? $c->precio);
                $descuento = (float) ($item['descuento'] ?? 0);

                if ($c->tipo === 'valor' || $c->padre_id) {
                    $cantidad = 1;
                }

                if ($cantidad < 1) throw new \Exception("Cantidad inválida en «{$c->nombre}».");
                if ($precio < 0)   throw new \Exception("Precio inválido en «{$c->nombre}».");
                if ($descuento < 0 || $descuento > $cantidad * $precio) {
                    throw new \Exception("Descuento inválido en «{$c->nombre}».");
                }

                $inicio = !empty($item['fecha_inicio']) ? $item['fecha_inicio'] : now()->toDateString();
                $fin    = !empty($item['fecha_fin'])    ? $item['fecha_fin']    : null;

                if ($fin && $fin < $inicio) {
                    throw new \Exception("«{$c->nombre}»: la fecha fin no puede ser menor a la fecha inicio.");
                }

                $creados[] = EmpresaComponenteSuscripcion::create([
                    'empresa_id'    => $empresaId,
                    'componente_id' => $c->id,
                    'cantidad'      => $cantidad,
                    'precio'        => $precio,
                    'descuento'     => $descuento,
                    'fecha_inicio'  => $inicio,
                    'fecha_fin'     => $fin,
                    'activo'        => true,
                ]);
            }

            DB::commit();

            return RespuestaDTO::exito()
                ->datos(['registros' => $creados])
                ->mensaje(count($creados) . ' componente(s) asignado(s) con éxito.')
                ->codigoSlug('suscripcion_guardar_multiple_exito')
                ->get();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return RespuestaDTO::error($e)
                ->mensaje("Error al guardar: " . $e->getMessage())
                ->codigoSlug('suscripcion_guardar_multiple_error')
                ->get();
        }
    }

    // ============================================================
    // ACTUALIZAR (cantidad, precio, descuento, fechas)
    // ============================================================
    public function actualizar(array $datos, int $sesion_user_id): RespuestaDTO
    {
        try {
            DB::beginTransaction();

            $permisoService = new PermisoService;
            $permisoService->validarPermisoGeneral(sesion_user_id: $sesion_user_id, rol_requerido: 'admin');

            $registro = EmpresaComponenteSuscripcion::with('componente')->findOrFail($datos['id']);

            if ($registro->componente->tipo === 'valor') {
                $registro->cantidad = 1;
                $registro->precio   = (float) $registro->componente->precio;
            } else {
                if (array_key_exists('cantidad', $datos))  $registro->cantidad  = (int) $datos['cantidad'];
                if (array_key_exists('precio', $datos))    $registro->precio    = (float) $datos['precio'];
            }

            if (array_key_exists('descuento', $datos))    $registro->descuento    = (float) $datos['descuento'];
            if (array_key_exists('fecha_inicio', $datos)) $registro->fecha_inicio = $datos['fecha_inicio'] ?: null;
            if (array_key_exists('fecha_fin', $datos))    $registro->fecha_fin    = $datos['fecha_fin'] ?: null;
            if (array_key_exists('activo', $datos))       $registro->activo       = (bool) $datos['activo'];

            $registro->save();

            DB::commit();

            return RespuestaDTO::exito()
                ->datos(['registro' => $registro])
                ->mensaje('Suscripción actualizada con éxito.')
                ->codigoSlug('suscripcion_actualizar_exito')
                ->get();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return RespuestaDTO::error($e)
                ->mensaje("Error al actualizar: " . $e->getMessage())
                ->codigoSlug('suscripcion_actualizar_error')
                ->get();
        }
    }

    // ============================================================
    // ELIMINAR
    // ============================================================
    public function eliminar(int $id, int $sesion_user_id): RespuestaDTO
    {
        try {
            DB::beginTransaction();

            $permisoService = new PermisoService;
            $permisoService->validarPermisoGeneral(sesion_user_id: $sesion_user_id, rol_requerido: 'admin');

            $registro = EmpresaComponenteSuscripcion::findOrFail($id);
            $registro->delete();

            DB::commit();

            return RespuestaDTO::exito()
                ->mensaje('Suscripción eliminada con éxito.')
                ->codigoSlug('suscripcion_eliminar_exito')
                ->get();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return RespuestaDTO::error($e)
                ->mensaje("Error al eliminar: " . $e->getMessage())
                ->codigoSlug('suscripcion_eliminar_error')
                ->get();
        }
    }

    // ============================================================
    // DATA (para editar)
    // ============================================================
    public function getData(int $id, int $sesion_user_id): RespuestaDTO
    {
        try {
            $permisoService = new PermisoService;
            $permisoService->validarPermisoGeneral(sesion_user_id: $sesion_user_id, rol_requerido: 'admin');

            $registro = EmpresaComponenteSuscripcion::with(['componente', 'componente.padre'])->findOrFail($id);

            return RespuestaDTO::exito()
                ->datos([
                    'registro' => [
                        'id'                => $registro->id,
                        'empresa_id'        => $registro->empresa_id,
                        'componente_id'     => $registro->componente_id,
                        'componente_nombre' => $registro->componente->nombre ?? null,
                        'componente_tipo'   => $registro->componente->tipo ?? null,
                        'cantidad'          => $registro->cantidad,
                        'precio'            => $registro->precio,
                        'descuento'         => $registro->descuento,
                        'fecha_inicio'      => optional($registro->fecha_inicio)->format('Y-m-d'),
                        'fecha_fin'         => optional($registro->fecha_fin)->format('Y-m-d'),
                        'activo'            => $registro->activo,
                    ],
                ])
                ->mensaje('Registro recuperado.')
                ->codigoSlug('suscripcion_data_exito')
                ->get();

        } catch (\Exception $e) {
            Log::error($e);
            return RespuestaDTO::error($e)
                ->mensaje("Error al obtener el registro: " . $e->getMessage())
                ->codigoSlug('suscripcion_data_error')
                ->get();
        }
    }
}
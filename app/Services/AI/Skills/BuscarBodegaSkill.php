<?php

namespace App\Services\AI\Skills;

use App\Models\Sistema\FacBodegas;
use App\Models\Empresas\UsuarioPermisos;

class BuscarBodegaSkill extends Skill
{
    public function name(): string
    {
        return 'buscar_bodega';
    }

    public function description(): string
    {
        return 'Busca bodegas asignadas al usuario logueado o por nombre/código. Úsala antes de crear una venta o movimiento de inventario.';
    }

    public function parameters(): array
    {
        return [
            'busqueda' => [
                'type' => 'string',
                'description' => 'Nombre o código de la bodega a buscar. Opcional si se usan las asignadas al usuario.',
                'required' => false,
            ],
        ];
    }

    public function run(array $args, array &$state): array
    {
        $busqueda = trim($args['busqueda'] ?? '');

        // 1) Por permisos del usuario logueado
        $idsPermitidos = $this->idsBodegasDelUsuario($state);
        $lista         = [];
        $origen        = 'permisos_usuario';

        if (!empty($idsPermitidos)) {
            $lista = $this->queryBodegas($idsPermitidos, $busqueda);
        }

        // 2) Fallback: búsqueda global por nombre/código
        if (empty($lista) && $busqueda !== '') {
            $origen = 'busqueda_global';
            $lista  = $this->queryBodegas(null, $busqueda);
        }

        // 3) Si no hay búsqueda y sí permisos: devolver todas las permitidas
        if (empty($lista) && $busqueda === '' && !empty($idsPermitidos)) {
            $lista = $this->queryBodegas($idsPermitidos, '');
        }

        if (empty($lista)) {
            return [
                'success' => false,
                'message' => 'No se encontraron bodegas disponibles.',
                'bodegas' => [],
            ];
        }

        // Guardar en el estado
        if (count($lista) === 1) {
            $state['id_bodega'] = $lista[0]['id'];
            $state['bodega']    = $lista[0];
        } else {
            $state['bodegas_candidatas'] = $lista;
        }

        return [
            'success' => true,
            'origen'  => $origen,
            'bodegas' => $lista,
        ];
    }

    private function queryBodegas(?array $ids, string $busqueda): array
    {
        $query = FacBodegas::query();

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        if ($busqueda !== '') {
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('codigo', 'like', "%{$busqueda}%");
            });
        }

        return $query->limit(10)
            ->get()
            ->map(fn ($b) => $this->formatear($b))
            ->values()
            ->toArray();
    }

    /**
     * IDs de bodegas asignadas al usuario logueado.
     * Lee usuario_permisos.ids_bodegas_responsable (ej: "1,2,3").
     */
    private function idsBodegasDelUsuario(array $state): array
    {
        $idUser    = $state['id_user']    ?? auth()->id();
        $idEmpresa = $state['id_empresa'] ?? null;

        if (!$idUser) {
            return [];
        }

        $query = UsuarioPermisos::query()->where('id_user', $idUser);

        if ($idEmpresa) {
            $query->where('id_empresa', $idEmpresa);
        }

        return $query->pluck('ids_bodegas_responsable')
            ->filter()
            ->flatMap(function ($valor) {
                if (is_array($valor)) {
                    return $valor;
                }
                return array_map('trim', explode(',', $valor));
            })
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();
    }

    private function formatear(FacBodegas $b): array
    {
        return [
            'id'               => $b->id,
            'codigo'           => $b->codigo,
            'nombre'           => $b->nombre,
            'id_centro_costos' => $b->id_centro_costos,
        ];
    }
}
<?php

namespace App\Services\AI\Skills;

use App\Models\Sistema\FacBodegas;
use App\Models\Empresas\UsuarioPermisos;
use App\Services\AI\Estado;

class BuscarBodegaSkill extends Skill
{
    public function name(): string
    {
        return 'buscar_bodega';
    }

    public function description(): string
    {
        return 'Lista las bodegas permitidas al usuario o busca una por nombre/código. En un flujo de venta, llama esta skill SIN "busqueda" para obtener las bodegas permitidas.';
    }

    public function parameters(): array
    {
        return [
            'busqueda' => [
                'type' => 'string',
                'description' => 'OPCIONAL. Solo si el usuario quiere localizar una bodega específica.',
                'required' => false,
            ],
            'auto' => [
                'type' => 'boolean',
                'description' => 'Si es true, la skill elige la primera bodega automáticamente y no pregunta. Úsalo al inicio del flujo de venta.',
                'required' => false,
            ],
        ];
    }

    public function run(array $args, Estado $state): array
    {
        $busqueda = trim($args['busqueda'] ?? '');
        $auto     = (bool) ($args['auto'] ?? false);

        $idsPermitidos = $this->idsBodegasDelUsuario($state);
        $lista         = [];

        if (!empty($idsPermitidos)) {
            $lista = $this->queryBodegas($idsPermitidos, $busqueda);
        }

        if (empty($lista) && $busqueda !== '') {
            $lista = $this->queryBodegas(null, $busqueda);
        }

        if (empty($lista)) {
            return [
                'success' => false,
                'message' => 'No se encontraron bodegas disponibles.',
                'bodegas' => [],
            ];
        }

        // Auto-selección: tomamos la primera
        if ($auto || count($lista) === 1) {
            $state->setBorrador('id_bodega', $lista[0]['id']);
            $state->setBorrador('bodega', $lista[0]);
            $state->limpiarCandidatos('bodega');

            return [
                'success' => true,
                'bodega'  => $lista[0],
                'bodegas' => $lista,
                'message' => "Bodega '{$lista[0]['nombre']}' fijada en el borrador.",
            ];
        }

        $state->setCandidatos('bodega', $lista);

        return [
            'success' => true,
            'bodegas' => $lista,
            'message' => 'Se encontraron varias bodegas. Pide al usuario que elija una.',
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

    private function idsBodegasDelUsuario(Estado $state): array
    {
        $idUser    = $state->idUser();
        $idEmpresa = $state->idEmpresa();

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
                if (is_array($valor)) return $valor;
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
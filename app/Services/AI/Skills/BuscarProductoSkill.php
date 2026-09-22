<?php

namespace App\Services\AI\Skills;

use App\Models\Sistema\FacProductos;

class BuscarProductoSkill extends Skill
{
    public function name(): string
    {
        return 'buscar_producto';
    }

    public function description(): string
    {
        return 'Busca productos del ERP por código o nombre para agregarlos a una venta.';
    }

    public function parameters(): array
    {
        return [
            'busqueda' => [
                'type' => 'string',
                'description' => 'Código o nombre del producto.',
                'required' => true,
            ],
        ];
    }

    public function run(array $args, array &$state): array
    {
        $busqueda = trim($args['busqueda'] ?? '');

        if ($busqueda === '') {
            return ['success' => false, 'message' => 'Debe indicar código o nombre.', 'productos' => []];
        }

        $productos = FacProductos::query()
            ->where(function ($q) use ($busqueda) {
                $q->where('codigo', 'like', "%{$busqueda}%")
                  ->orWhere('nombre', 'like', "%{$busqueda}%");
            })
            ->limit(10)
            ->get(['id', 'codigo', 'nombre', 'precio_inicial', 'tipo_producto']);

        $lista = $productos->map(fn ($p) => [
            'id' => $p->id,
            'codigo' => $p->codigo,
            'nombre' => $p->nombre,
            'precio' => $p->precio_inicial,
            'es_combo' => (int) $p->tipo_producto === 2,
        ])->values()->toArray();

        if (count($lista) === 1) {
            $state['productos_candidatos'] = $lista; // no lo fijamos aún: puede ser 1 de varios
        } elseif (count($lista) > 1) {
            $state['productos_candidatos'] = $lista;
        }

        return ['success' => true, 'productos' => $lista];
    }
}
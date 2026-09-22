<?php

namespace App\Services\AI\Skills;

use App\Models\Sistema\Nits;

class BuscarClienteSkill extends Skill
{
    public function name(): string
    {
        return 'buscar_cliente';
    }

    public function description(): string
    {
        return 'Busca clientes en el ERP por nombre, razón social o número de documento.';
    }

    public function parameters(): array
    {
        return [
            'busqueda' => [
                'type' => 'string',
                'description' => 'Texto a buscar: nombre, razón social o número de documento.',
                'required' => true,
            ],
        ];
    }

    public function run(array $args, array &$state): array
    {
        $busqueda = trim($args['busqueda'] ?? '');

        if ($busqueda === '') {
            return ['success' => false, 'message' => 'Debe indicar nombre o documento.', 'clientes' => []];
        }

        $clientes = Nits::query()
            ->where(function ($q) use ($busqueda) {
                $q->where('numero_documento', 'like', "%{$busqueda}%")
                  ->orWhere('razon_social', 'like', "%{$busqueda}%")
                  ->orWhere('nombre_comercial', 'like', "%{$busqueda}%")
                  ->orWhere('primer_nombre', 'like', "%{$busqueda}%")
                  ->orWhere('otros_nombres', 'like', "%{$busqueda}%")
                  ->orWhere('primer_apellido', 'like', "%{$busqueda}%")
                  ->orWhere('segundo_apellido', 'like', "%{$busqueda}%");
            })
            ->limit(10)
            ->get([
                'id', 'numero_documento', 'digito_verificacion',
                'primer_nombre', 'otros_nombres',
                'primer_apellido', 'segundo_apellido',
                'razon_social', 'nombre_comercial',
            ]);

        $lista = $clientes->map(fn ($c) => [
            'id' => $c->id,
            'documento' => $c->numero_documento,
            'nombre' => $this->nombreCliente($c),
        ])->values()->toArray();

        // Guardamos pistas en el borrador
        if (count($lista) === 1) {
            $state['id_cliente'] = $lista[0]['id'];
            $state['cliente'] = $lista[0];
        } elseif (count($lista) > 1) {
            $state['clientes_candidatos'] = $lista;
        }

        return ['success' => true, 'clientes' => $lista];
    }

    private function nombreCliente(Nits $c): string
    {
        if (!empty($c->razon_social)) return $c->razon_social;

        return trim(implode(' ', array_filter([
            $c->primer_nombre, $c->otros_nombres,
            $c->primer_apellido, $c->segundo_apellido,
        ])));
    }
}
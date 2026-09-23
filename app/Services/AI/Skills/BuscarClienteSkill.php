<?php

namespace App\Services\AI\Skills;

use App\Models\Sistema\Nits;
use App\Services\AI\Estado;
use Illuminate\Support\Facades\DB;

class BuscarClienteSkill extends Skill
{
    public function name(): string
    {
        return 'buscar_cliente';
    }

    public function description(): string
    {
        return 'Busca clientes por nombre, razón social o número de documento. Soporta nombre y apellido en cualquier orden.';
    }

    public function parameters(): array
    {
        return [
            'busqueda' => [
                'type' => 'string',
                'description' => 'Texto a buscar: nombre(s), apellido(s), razón social o documento. Puede ir en cualquier orden.',
                'required' => true,
            ],
        ];
    }

    public function run(array $args, Estado $state): array
    {
        $busqueda = trim($args['busqueda'] ?? '');

        if ($busqueda === '') {
            return [
                'success'  => false,
                'message'  => 'Debe indicar nombre, apellido o documento.',
                'clientes' => [],
            ];
        }

        // ---------------------------------------------------------
        // 1. Dividir la búsqueda en tokens (palabras)
        // ---------------------------------------------------------
        $tokens = array_values(array_filter(preg_split('/\s+/', $busqueda)));

        // ---------------------------------------------------------
        // 2. Columna virtual concatenada: "todos los campos" como texto
        // ---------------------------------------------------------
        $concat = "CONCAT_WS(' ',
            numero_documento,
            razon_social,
            nombre_comercial,
            primer_nombre,
            otros_nombres,
            primer_apellido,
            segundo_apellido
        )";

        // ---------------------------------------------------------
        // 3. Construir la consulta
        // ---------------------------------------------------------
        $query = Nits::query();

        if (count($tokens) > 1) {
            // MULTI-TOKEN: cada palabra debe aparecer en algún campo.
            // El orden no importa porque todo se busca en la concatenación.
            $query->where(function ($q) use ($tokens, $concat) {
                foreach ($tokens as $token) {
                    $q->whereRaw("{$concat} LIKE ?", ["%{$token}%"]);
                }
            });
        } else {
            // SINGLE-TOKEN: búsqueda normal campo por campo + concatenación
            $query->where(function ($q) use ($busqueda, $concat) {
                $q->where('numero_documento', 'like', "%{$busqueda}%")
                  ->orWhere('razon_social', 'like', "%{$busqueda}%")
                  ->orWhere('nombre_comercial', 'like', "%{$busqueda}%")
                  ->orWhere('primer_nombre', 'like', "%{$busqueda}%")
                  ->orWhere('otros_nombres', 'like', "%{$busqueda}%")
                  ->orWhere('primer_apellido', 'like', "%{$busqueda}%")
                  ->orWhere('segundo_apellido', 'like', "%{$busqueda}%")
                  ->orWhereRaw("{$concat} LIKE ?", ["%{$busqueda}%"]);
            });
        }

        // ---------------------------------------------------------
        // 4. Ordenar por relevancia
        //    - Documento exacto primero
        //    - Razón social exacta
        //    - Concatenación exacta
        //    - Resto
        // ---------------------------------------------------------
        $query->orderByRaw(
            "CASE
                WHEN numero_documento = ? THEN 0
                WHEN razon_social = ? THEN 1
                WHEN {$concat} = ? THEN 2
                WHEN {$concat} LIKE ? THEN 3
                ELSE 4
            END",
            [$busqueda, $busqueda, $busqueda, $busqueda . '%']
        );

        // ---------------------------------------------------------
        // 5. Ejecutar
        // ---------------------------------------------------------
        $clientes = $query->limit(10)->get([
            'id', 'numero_documento', 'digito_verificacion',
            'primer_nombre', 'otros_nombres',
            'primer_apellido', 'segundo_apellido',
            'razon_social', 'nombre_comercial',
        ]);

        $lista = $clientes->map(fn ($c) => [
            'id'        => $c->id,
            'documento' => $c->numero_documento,
            'nombre'    => $this->nombreCliente($c),
        ])->values()->toArray();

        if (empty($lista)) {
            return [
                'success'  => false,
                'message'  => "No se encontraron clientes con '{$busqueda}'.",
                'clientes' => [],
            ];
        }

        if (count($lista) === 1) {
            $state->setBorrador('id_cliente', $lista[0]['id']);
            $state->setBorrador('cliente', $lista[0]);
            $state->limpiarCandidatos('cliente');

            return [
                'success'  => true,
                'cliente'  => $lista[0],
                'clientes' => $lista,
                'message'  => "Cliente '{$lista[0]['nombre']}' fijado en el borrador.",
            ];
        }

        $state->setCandidatos('cliente', $lista);

        return [
            'success'  => true,
            'clientes' => $lista,
            'message'  => 'Se encontraron varios clientes. Pide al usuario que elija uno.',
        ];
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
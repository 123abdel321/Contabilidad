<?php

namespace App\Services\AI\Skills;

use App\Models\Sistema\FacFormasPago;
use App\Services\AI\Estado;

class BuscarFormaPagoSkill extends Skill
{
    public function name(): string
    {
        return 'buscar_forma_pago';
    }

    public function description(): string
    {
        return 'Busca formas de pago según el contexto (ventas, compras, ingresos, egresos, gastos). Si el usuario indica un valor a pagar, pásalo en "valor" para que se acumule en el borrador.';
    }

    public function parameters(): array
    {
        return [
            'contexto' => [
                'type' => 'string',
                'description' => 'Contexto: "ventas", "compras", "ingresos", "egresos" o "gastos".',
                'required' => true,
            ],
            'busqueda' => [
                'type' => 'string',
                'description' => 'Texto a buscar por nombre de la forma de pago. Opcional.',
                'required' => false,
            ],
            'valor' => [
                'type' => 'number',
                'description' => 'Valor a pagar con esta forma de pago. Opcional. Si se indica, se acumula en el borrador.',
                'required' => false,
            ],
        ];
    }

    public function run(array $args, Estado $state): array
    {
        $busqueda = trim($args['busqueda'] ?? '');
        $contexto = strtolower(trim($args['contexto'] ?? ''));
        $valor    = isset($args['valor']) ? (float) $args['valor'] : null;

        if ($contexto === '') {
            return [
                'success' => false,
                'message' => 'Debe indicar el contexto de la operación.',
                'formas_pago' => [],
            ];
        }

        $tiposPermitidos   = $this->tiposPorContexto()[$contexto] ?? [];
        $columnaNaturaleza = $this->columnaNaturaleza($contexto);

        if (empty($tiposPermitidos) || $columnaNaturaleza === null) {
            return [
                'success' => false,
                'message' => "Contexto no reconocido: {$contexto}.",
                'formas_pago' => [],
            ];
        }

        $query = FacFormasPago::query()
            ->with('cuenta.tipos_cuenta')
            ->whereHas('cuenta', function ($q) use ($columnaNaturaleza) {
                $q->whereNotNull('naturaleza_' . $columnaNaturaleza);
            })
            ->whereHas('cuenta.tipos_cuenta', function ($q) use ($tiposPermitidos) {
                $q->whereIn('id_tipo_cuenta', $tiposPermitidos);
            });

        if ($busqueda !== '') {
            $query->where('nombre', 'LIKE', "%{$busqueda}%");
        }

        $formas = $query->limit(40)->get();

        $lista = $formas->map(fn ($f) => [
            'id'            => $f->id,
            'nombre'        => $f->nombre,
            'id_cuenta'     => $f->id_cuenta,
            'nombre_cuenta' => $f->cuenta->nombre ?? null,
            'id_tipo_forma' => $f->id_tipo_formas_pago,
            'tipo_forma'    => $f->tipoFormaPago->nombre ?? null,
        ])->values()->toArray();

        if (empty($lista)) {
            return [
                'success' => false,
                'message' => 'No se encontraron formas de pago para este contexto.',
                'contexto' => $contexto,
                'formas_pago' => [],
            ];
        }

        // Si hay un solo resultado y el modelo pasó "valor", acumulamos en borrador.pagos
        if (count($lista) === 1 && $valor !== null && $valor > 0) {
            $this->agregarPago($state, $lista[0], $valor);

            return [
                'success'     => true,
                'contexto'    => $contexto,
                'forma_pago'  => $lista[0],
                'formas_pago' => $lista,
                'message'     => "Pago de {$valor} con '{$lista[0]['nombre']}' agregado al borrador.",
            ];
        }

        // Un solo resultado pero sin valor → lo fijamos como candidato único
        if (count($lista) === 1) {
            $state->setBorrador("forma_pago_{$contexto}", $lista[0]);
            $state->limpiarCandidatos("forma_pago_{$contexto}");

            return [
                'success'     => true,
                'contexto'    => $contexto,
                'forma_pago'  => $lista[0],
                'formas_pago' => $lista,
                'message'     => "Forma de pago '{$lista[0]['nombre']}' fijada en el borrador.",
            ];
        }

        // Varias → candidatos
        $state->setCandidatos("forma_pago_{$contexto}", $lista);

        return [
            'success'     => true,
            'contexto'    => $contexto,
            'formas_pago' => $lista,
            'message'     => 'Se encontraron varias formas de pago. Pide al usuario que elija una.',
        ];
    }

    /**
     * Acumula el pago en borrador.pagos.
     * Si ya existe la misma forma de pago, suma el valor.
     */
    private function agregarPago(Estado $state, array $formaPago, float $valor): void
    {
        $pagos = $state->borrador('pagos', []);
        if (!is_array($pagos)) $pagos = [];

        $encontrado = false;

        foreach ($pagos as &$item) {
            if ((int) $item['id'] === (int) $formaPago['id']) {
                $item['valor'] += $valor;
                $encontrado = true;
                break;
            }
        }
        unset($item);

        if (!$encontrado) {
            $pagos[] = [
                'id'     => $formaPago['id'],
                'nombre' => $formaPago['nombre'],
                'valor'  => $valor,
            ];
        }

        $state->setBorrador('pagos', $pagos);
    }

    private function tiposPorContexto(): array
    {
        return [
            'gasto'   => [FacFormasPago::TIPO_CUENTA_CAJA_BANCOS, FacFormasPago::TIPO_CUENTA_CXP, FacFormasPago::TIPO_CUENTA_ANTICIPO_PROVEEDORES_XC],
            'gastos'  => [FacFormasPago::TIPO_CUENTA_CAJA_BANCOS, FacFormasPago::TIPO_CUENTA_CXP, FacFormasPago::TIPO_CUENTA_ANTICIPO_PROVEEDORES_XC],
            'compras' => [FacFormasPago::TIPO_CUENTA_CAJA_BANCOS, FacFormasPago::TIPO_CUENTA_CXP, FacFormasPago::TIPO_CUENTA_ANTICIPO_PROVEEDORES_XC],
            'egresos' => [FacFormasPago::TIPO_CUENTA_CAJA_BANCOS, FacFormasPago::TIPO_CUENTA_CXC, FacFormasPago::TIPO_CUENTA_ANTICIPO_PROVEEDORES_XC],
            'ingresos'=> [FacFormasPago::TIPO_CUENTA_CAJA_BANCOS, FacFormasPago::TIPO_CUENTA_ANTICIPO_CLIENTES_XP],
            'ventas'  => [FacFormasPago::TIPO_CUENTA_CAJA_BANCOS, FacFormasPago::TIPO_CUENTA_CXC, FacFormasPago::TIPO_CUENTA_ANTICIPO_CLIENTES_XP],
        ];
    }

    private function columnaNaturaleza(string $contexto): ?string
    {
        $mapa = [
            'gasto'    => 'compras',
            'gastos'   => 'compras',
            'compras'  => 'compras',
            'egresos'  => 'egresos',
            'ingresos' => 'ingresos',
            'ventas'   => 'ventas',
        ];

        return $mapa[$contexto] ?? null;
    }
}
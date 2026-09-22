<?php

namespace App\Services\AI\Skills;

use App\Models\Sistema\FacFormasPago;

class BuscarFormaPagoSkill extends Skill
{
    public function name(): string
    {
        return 'buscar_forma_pago';
    }

    public function description(): string
    {
        return 'Busca formas de pago disponibles según el contexto de la operación (ventas, compras, ingresos, egresos, gastos). Úsala antes de registrar un pago o una venta.';
    }

    public function parameters(): array
    {
        return [
            'contexto' => [
                'type' => 'string',
                'description' => 'Contexto de la operación: "ventas", "compras", "ingresos", "egresos" o "gastos".',
                'required' => true,
            ],
            'busqueda' => [
                'type' => 'string',
                'description' => 'Texto a buscar por nombre de la forma de pago. Opcional.',
                'required' => false,
            ],
        ];
    }

    /**
     * Mapa de contexto → tipos de cuenta permitidos.
     */
    private function tiposPorContexto(): array
    {
        return [
            'gasto' => [
                FacFormasPago::TIPO_CUENTA_CAJA_BANCOS,
                FacFormasPago::TIPO_CUENTA_CXP,
                FacFormasPago::TIPO_CUENTA_ANTICIPO_PROVEEDORES_XC,
            ],
            'gastos' => [
                FacFormasPago::TIPO_CUENTA_CAJA_BANCOS,
                FacFormasPago::TIPO_CUENTA_CXP,
                FacFormasPago::TIPO_CUENTA_ANTICIPO_PROVEEDORES_XC,
            ],
            'compras' => [
                FacFormasPago::TIPO_CUENTA_CAJA_BANCOS,
                FacFormasPago::TIPO_CUENTA_CXP,
                FacFormasPago::TIPO_CUENTA_ANTICIPO_PROVEEDORES_XC,
            ],
            'egresos' => [
                FacFormasPago::TIPO_CUENTA_CAJA_BANCOS,
                FacFormasPago::TIPO_CUENTA_CXC,
                FacFormasPago::TIPO_CUENTA_ANTICIPO_PROVEEDORES_XC,
            ],
            'ingresos' => [
                FacFormasPago::TIPO_CUENTA_CAJA_BANCOS,
                FacFormasPago::TIPO_CUENTA_ANTICIPO_CLIENTES_XP,
            ],
            'ventas' => [
                FacFormasPago::TIPO_CUENTA_CAJA_BANCOS,
                FacFormasPago::TIPO_CUENTA_CXC,
                FacFormasPago::TIPO_CUENTA_ANTICIPO_CLIENTES_XP,
            ],
        ];
    }

    /**
     * Mapa de contexto → columna de naturaleza en PlanCuentas.
     * Para "gastos" el combo usa la columna "compras".
     */
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

    public function run(array $args, array &$state): array
    {
        $busqueda = trim($args['busqueda'] ?? '');
        $contexto = strtolower(trim($args['contexto'] ?? ''));

        if ($contexto === '') {
            return [
                'success' => false,
                'message' => 'Debe indicar el contexto de la operación (ventas, compras, ingresos, egresos, gastos).',
                'formas_pago' => [],
            ];
        }

        $tiposPermitidos  = $this->tiposPorContexto()[$contexto] ?? [];
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
            'id'             => $f->id,
            'nombre'         => $f->nombre,
            'id_cuenta'      => $f->id_cuenta,
            'nombre_cuenta'  => $f->cuenta->nombre ?? null,
            'id_tipo_forma'  => $f->id_tipo_formas_pago,
            'tipo_forma'     => $f->tipoFormaPago->nombre ?? null,
        ])->values()->toArray();

        if (empty($lista)) {
            return [
                'success' => false,
                'message' => 'No se encontraron formas de pago para este contexto.',
                'contexto' => $contexto,
                'formas_pago' => [],
            ];
        }

        // Guardar en el estado por contexto
        $claveId  = "id_forma_pago_{$contexto}";
        $claveObj = "forma_pago_{$contexto}";

        if (count($lista) === 1) {
            $state[$claveId]  = $lista[0]['id'];
            $state[$claveObj] = $lista[0];
        } else {
            $state["formas_pago_candidatas_{$contexto}"] = $lista;
        }

        return [
            'success'     => true,
            'contexto'    => $contexto,
            'formas_pago' => $lista,
        ];
    }
}
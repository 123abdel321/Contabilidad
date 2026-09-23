<?php

namespace App\Services\AI\Skills;

use App\Services\AI\Estado;
use App\Services\Ventas\VentaCreatorService;

class CrearVentaSkill extends Skill
{
    public function name(): string
    {
        return 'crear_venta';
    }

    public function description(): string
    {
        return 'Crea la venta en el ERP con los datos ya recolectados en el borrador. Requiere cliente, resolución, bodega, productos y pagos. Úsala solo cuando el usuario confirme.';
    }

    public function parameters(): array
    {
        return [
            'propina' => [
                'type' => 'number',
                'description' => 'Valor de propina opcional.',
                'required' => false,
            ],
            'observacion' => [
                'type' => 'string',
                'description' => 'Observación opcional de la venta.',
                'required' => false,
            ],
            'confirmado' => [
                'type' => 'boolean',
                'description' => 'Debe ser true para confirmar que el usuario autorizó crear la venta.',
                'required' => true,
            ],
        ];
    }

    public function run(array $args, Estado $state): array
    {
        if (empty($args['confirmado'])) {
            return ['success' => false, 'message' => 'Falta confirmación del usuario para crear la venta.'];
        }

        $idUser    = $state->idUser();
        $idEmpresa = $state->idEmpresa();

        if (!$idUser || !$idEmpresa) {
            return ['success' => false, 'message' => 'Falta contexto de usuario/empresa.'];
        }

        // ---------- 1. Leer borrador ----------
        $borrador  = $state->borrador();
        $productos = $borrador['productos'] ?? [];
        $pagos     = $borrador['pagos']     ?? [];
        $propina   = (float) ($args['propina'] ?? $borrador['propina'] ?? 0);

        // ---------- 2. Normalizar pagos ----------
        // Si el usuario dijo "paga en efectivo" pero no dio monto, aquí
        // calculamos el total y lo asignamos a la forma de pago seleccionada.
        $totalVenta = 0.0;
        foreach ($productos as $p) {
            $totalVenta += (float) ($p['total'] ?? 0);
        }
        $totalVenta += $propina;

        if (empty($pagos)) {
            // No hay pagos explícitos: usar la forma de pago elegida
            $formaPago = $borrador['forma_pago_ventas'] ?? null;
            if ($formaPago) {
                $pagos = [[
                    'id'    => $formaPago['id'],
                    'valor' => $totalVenta,
                ]];
            }
        } else {
            // Hay pagos pero pueden venir sin valor o incompletos
            if (count($pagos) === 1 && empty($pagos[0]['valor'])) {
                $pagos[0]['valor'] = $totalVenta;
            }

            $totalPagado = 0.0;
            foreach ($pagos as $p) {
                $totalPagado += (float) ($p['valor'] ?? 0);
            }

            if ($totalPagado < $totalVenta && !empty($pagos)) {
                $faltante = $totalVenta - $totalPagado;
                $pagos[0]['valor'] = (float) ($pagos[0]['valor'] ?? 0) + $faltante;
            }
        }

        // ---------- 3. Armar $data ----------
        $data = [
            'id_cliente'           => $borrador['id_cliente']          ?? null,
            'id_resolucion'        => $borrador['id_resolucion_venta'] ?? null,
            'id_bodega'            => $borrador['id_bodega']           ?? null,
            'fecha_manual'         => $borrador['fecha_manual']        ?? now()->format('Y-m-d'),
            'documento_referencia' => $borrador['documento_referencia']?? null,
            'id_vendedor'          => $borrador['id_vendedor']         ?? null,
            'observacion'          => $args['observacion'] ?? $borrador['observacion'] ?? null,
            'propina'              => $propina,
            'productos'            => $productos,
            'pagos'                => $pagos,
        ];

        // ---------- 4. Validaciones mínimas ----------
        $faltantes = [];
        if (!$data['id_cliente'])      $faltantes[] = 'cliente';
        if (!$data['id_resolucion'])   $faltantes[] = 'resolución';
        if (!$data['id_bodega'])       $faltantes[] = 'bodega';
        if (empty($data['productos'])) $faltantes[] = 'productos';
        if (empty($data['pagos']))     $faltantes[] = 'pagos';

        if ($faltantes) {
            return ['success' => false, 'message' => 'Faltan datos: ' . implode(', ', $faltantes)];
        }

        // ---------- 5. Delegar al servicio ----------
        $resultado = app(VentaCreatorService::class)
            ->crear($data, $idUser, $idEmpresa);

        if (empty($resultado['success'])) {
            $errores = $resultado['errores'] ?? ['Error desconocido.'];
            return [
                'success' => false,
                'message' => is_array($errores) ? json_encode($errores) : $errores,
            ];
        }

        // ---------- 6. Guardar resultado ----------
        $state->setResultado($resultado['venta']);

        return [
            'success' => true,
            'venta'   => $resultado['venta'],
            'message' => "Venta {$resultado['venta']['referencia']} creada con éxito.",
        ];
    }
}
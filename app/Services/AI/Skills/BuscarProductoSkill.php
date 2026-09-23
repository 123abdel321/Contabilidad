<?php

namespace App\Services\AI\Skills;

use App\Models\Sistema\FacProductos;
use App\Services\AI\Estado;

class BuscarProductoSkill extends Skill
{
    public function name(): string
    {
        return 'buscar_producto';
    }

    public function description(): string
    {
        return 'Busca productos por código o nombre. Soporta singular/plural y varias palabras en cualquier orden. Si encuentra uno solo, lo fija en el borrador. Si encuentra varios, los deja como candidatos.';
    }

    public function parameters(): array
    {
        return [
            'busqueda' => [
                'type' => 'string',
                'description' => 'Código o nombre del producto a buscar.',
                'required' => true,
            ],
            'cantidad' => [
                'type' => 'number',
                'description' => 'Cantidad que el usuario quiere del producto. Si no la indica, se asume 1.',
                'required' => false,
            ],
        ];
    }

    public function run(array $args, Estado $state): array
    {
        $busqueda = trim($args['busqueda'] ?? '');
        $cantidad = (float) ($args['cantidad'] ?? 1);

        if ($busqueda === '') {
            return [
                'success'   => false,
                'message'   => 'Debe indicar código o nombre del producto.',
                'productos' => [],
            ];
        }

        if ($cantidad <= 0) $cantidad = 1;

        // ---------------------------------------------------------
        // 1. Tokenizar y generar variantes (singular/plural)
        // ---------------------------------------------------------
        $tokens = array_values(array_filter(preg_split('/\s+/', $busqueda)));

        $gruposVariantes = [];
        foreach ($tokens as $token) {
            $gruposVariantes[] = $this->generarVariantes($token);
        }

        // ---------------------------------------------------------
        // 2. Construir la consulta con AND entre grupos y OR entre variantes
        //    (todas las palabras deben aparecer en algún campo,
        //     en cualquier orden, con cualquier variante)
        // ---------------------------------------------------------
        $query = FacProductos::query();

        $query->where(function ($q) use ($gruposVariantes) {
            foreach ($gruposVariantes as $variantes) {
                $q->where(function ($sub) use ($variantes) {
                    foreach ($variantes as $v) {
                        $sub->orWhere('codigo', 'like', "%{$v}%")
                            ->orWhere('nombre', 'like', "%{$v}%");
                    }
                });
            }
        });

        // ---------------------------------------------------------
        // 3. Orden por relevancia (código exacto, nombre exacto, luego por relevancia)
        // ---------------------------------------------------------
        $busquedaNormalizada = mb_strtolower($busqueda, 'UTF-8');
        $query->orderByRaw(
            "CASE
                WHEN LOWER(codigo) = ? THEN 0
                WHEN LOWER(nombre) = ? THEN 1
                WHEN LOWER(nombre) LIKE ? THEN 2
                WHEN LOWER(nombre) LIKE ? THEN 3
                ELSE 4
            END",
            [$busquedaNormalizada, $busquedaNormalizada, $busquedaNormalizada . '%', '%' . $busquedaNormalizada . '%']
        );

        $productos = $query->limit(10)->get([
            'id', 'codigo', 'nombre', 'precio_inicial', 'tipo_producto',
            'id_familia',
        ]);

        $lista = $productos->map(fn ($p) => $this->formatear($p))->values()->toArray();

        if (empty($lista)) {
            return [
                'success'   => false,
                'message'   => "No se encontraron productos que coincidan con '{$busqueda}'.",
                'productos' => [],
            ];
        }

        // Un solo resultado → lo fijamos en el borrador
        if (count($lista) === 1) {
            $this->fijarProductoEnBorrador($state, $lista[0], $cantidad);
            $state->limpiarCandidatos('producto');

            return [
                'success'   => true,
                'producto'  => $lista[0],
                'productos' => $lista,
                'message'   => "Producto '{$lista[0]['nombre']}' agregado al borrador con cantidad {$cantidad}.",
            ];
        }

        // Varios → candidatos para que el usuario elija
        $state->setCandidatos('producto', $lista);

        return [
            'success'   => true,
            'productos' => $lista,
            'message'   => 'Se encontraron varios productos. Pide al usuario que elija uno.',
        ];
    }

    // =========================================================
    // Variantes morfológicas (singular / plural)
    // =========================================================

    /**
     * Genera variantes de un token para soportar singular/plural.
     *
     *  "cascos"  → ["cascos", "casco"]
     *  "casco"   → ["casco", "cascos"]
     *  "camion"  → ["camion", "camions", "camiones"]
     *  "luces"   → ["luces", "luz", "luce"]
     *  "luz"     → ["luz", "luzs", "luces"]
     *  "guante"  → ["guante", "guantes"]
     */
    private function generarVariantes(string $token): array
    {
        $variantes = [$token];
        $lower     = mb_strtolower($token, 'UTF-8');
        $len       = mb_strlen($lower, 'UTF-8');

        if ($len < 3) {
            // Palabras muy cortas: no vale la pena generar variantes
            return array_unique($variantes);
        }

        // ---------- Ya viene en plural → intentar singular ----------
        // "cascos" → "casco"
        if (mb_substr($lower, -1) === 's') {
            $variantes[] = mb_substr($token, 0, -1);

            // "camiones" → "camion"
            if (mb_substr($lower, -2) === 'es') {
                $variantes[] = mb_substr($token, 0, -2);
            }
        }

        // ---------- Ya viene en singular → intentar plural ----------
        $ultimaLetra = mb_substr($lower, -1);

        // Palabras que terminan en vocal: se agrega "s"
        // "casco" → "cascos", "guante" → "guantes"
        if (in_array($ultimaLetra, ['a', 'e', 'i', 'o', 'u'])) {
            $variantes[] = $token . 's';
        } else {
            // Terminan en consonante: se agrega "es"
            // "camion" → "camiones", "ciudad" → "ciudades"
            $variantes[] = $token . 'es';
            $variantes[] = $token . 's'; // por si acaso
        }

        // ---------- Manejo especial de -z → -ces ----------
        // "luz" → "luces", "vez" → "veces"
        if ($ultimaLetra === 'z') {
            $variantes[] = mb_substr($token, 0, -1) . 'ces';
        }

        return array_values(array_unique($variantes));
    }

    /**
     * Agrega (o actualiza) el producto en el array de productos del borrador.
     * Si ya existe el mismo id_producto, suma la cantidad.
     */
    private function fijarProductoEnBorrador(Estado $state, array $producto, float $cantidad): void
    {
        $productos = $state->borrador('productos', []);
        if (!is_array($productos)) $productos = [];

        $ivaIncluidoVar = \App\Models\Sistema\VariablesEntorno::where('nombre', 'iva_incluido')->first();
        $ivaIncluido    = (bool) ($ivaIncluidoVar->valor ?? false);

        $precio         = (float) $producto['precio'];
        $ivaPorcentaje  = (float) $producto['iva_porcentaje'];
        $descuentoValor = 0.0;
        $descuentoPct   = 0.0;

        $totalPorCantidad = $precio * $cantidad;
        $baseConDescuento = $totalPorCantidad - $descuentoValor;

        $ivaValor = 0.0;
        if ($ivaPorcentaje > 0) {
            if ($ivaIncluido) {
                $ivaValor = round($baseConDescuento * ($ivaPorcentaje / ($ivaPorcentaje + 100)), 2);
            } else {
                $ivaValor = round($baseConDescuento * ($ivaPorcentaje / 100), 2);
            }
        }

        $subtotal = $ivaIncluido
            ? round($baseConDescuento - $ivaValor, 2)
            : round($baseConDescuento, 2);

        $total = $ivaIncluido
            ? round($baseConDescuento, 2)
            : round($baseConDescuento + $ivaValor, 2);

        $item = [
            'id_producto'          => $producto['id'],
            'codigo'               => $producto['codigo'],
            'nombre'               => $producto['nombre'],
            'cantidad'             => $cantidad,
            'costo'                => $precio,
            'subtotal'             => $subtotal,
            'descuento_porcentaje' => $descuentoPct,
            'descuento_valor'      => $descuentoValor,
            'iva_porcentaje'       => $ivaPorcentaje,
            'iva_valor'            => $ivaValor,
            'total'                => $total,
            'concepto'             => null,
            'es_combo'             => $producto['es_combo'],
        ];

        $encontrado = false;
        foreach ($productos as &$existente) {
            if ((int) $existente['id_producto'] === (int) $producto['id']) {
                $existente['cantidad'] += $cantidad;
                $existente = $this->recalcularProducto($existente, $ivaIncluido);
                $encontrado = true;
                break;
            }
        }
        unset($existente);

        if (!$encontrado) {
            $productos[] = $item;
        }

        $state->setBorrador('productos', $productos);
    }

    private function recalcularProducto(array $item, bool $ivaIncluido): array
    {
        $cantidad       = (float) $item['cantidad'];
        $precio         = (float) $item['costo'];
        $ivaPorcentaje  = (float) $item['iva_porcentaje'];
        $descuentoValor = (float) ($item['descuento_valor'] ?? 0);

        $totalPorCantidad = $precio * $cantidad;
        $baseConDescuento = $totalPorCantidad - $descuentoValor;

        $ivaValor = 0.0;
        if ($ivaPorcentaje > 0) {
            if ($ivaIncluido) {
                $ivaValor = round($baseConDescuento * ($ivaPorcentaje / ($ivaPorcentaje + 100)), 2);
            } else {
                $ivaValor = round($baseConDescuento * ($ivaPorcentaje / 100), 2);
            }
        }

        $item['subtotal'] = $ivaIncluido
            ? round($baseConDescuento - $ivaValor, 2)
            : round($baseConDescuento, 2);

        $item['iva_valor'] = $ivaValor;

        $item['total'] = $ivaIncluido
            ? round($baseConDescuento, 2)
            : round($baseConDescuento + $ivaValor, 2);

        return $item;
    }

    private function formatear(FacProductos $p): array
    {
        $ivaPorcentaje = $this->ivaPorcentajePorFamilia($p);

        return [
            'id'             => $p->id,
            'codigo'         => $p->codigo,
            'nombre'         => $p->nombre,
            'precio'         => (float) $p->precio_inicial,
            'iva_porcentaje' => $ivaPorcentaje,
            'es_combo'       => (int) $p->tipo_producto === 2,
        ];
    }

    private function ivaPorcentajePorFamilia(FacProductos $p): float
    {
        $familia = $p->familia()->with('cuenta_venta_iva.impuesto')->first();

        if (
            $familia &&
            $familia->cuenta_venta_iva &&
            $familia->cuenta_venta_iva->impuesto
        ) {
            return (float) $familia->cuenta_venta_iva->impuesto->porcentaje;
        }

        return 0.0;
    }
}
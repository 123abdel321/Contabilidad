<?php

namespace App\Services\AI\Skills;

use App\Models\Sistema\FacResoluciones;
use App\Models\Empresas\UsuarioPermisos;

class BuscarResolucionSkill extends Skill
{
    public function name(): string
    {
        return 'buscar_resolucion';
    }

    public function description(): string
    {
        return 'Busca resoluciones (POS, facturación electrónica, notas, etc.) asignadas al usuario o por nombre/prefijo. Indica siempre el contexto de la operación para filtrar los tipos correctos.';
    }

    public function parameters(): array
    {
        return [
            'busqueda' => [
                'type' => 'string',
                'description' => 'Nombre, prefijo o número de resolución a buscar. Opcional si se usan las asignadas al usuario.',
                'required' => false,
            ],
            'contexto' => [
                'type' => 'string',
                'description' => 'Operación para la que se busca la resolución: "venta", "nota_credito", "nota_debito", "documento_soporte", etc.',
                'required' => true,
            ],
            'tipos' => [
                'type' => 'array',
                'description' => 'Tipos específicos de resolución permitidos (opcional, sobreescribe el mapa por defecto del contexto). Valores válidos: pos, factura_electronica, contingencia, nota_debito, nota_credito, documento_equivalente.',
                'required' => false,
            ],
        ];
    }

    /**
     * Mapa de contexto → tipos de resolución permitidos.
     * Se usan las constantes de FacResoluciones.
     */
    private function tiposPorContexto(): array
    {
        return [
            'venta' => [
                FacResoluciones::TIPO_POS,
                FacResoluciones::TIPO_FACTURA_ELECTRONICA,
            ],
            'pos' => [
                FacResoluciones::TIPO_POS,
            ],
            'factura_electronica' => [
                FacResoluciones::TIPO_FACTURA_ELECTRONICA,
            ],
            'contingencia' => [
                FacResoluciones::TIPO_POS,
                FacResoluciones::TIPO_FACTURA_ELECTRONICA,
            ],
            'nota_credito' => [
                FacResoluciones::TIPO_NOTA_CREDITO,
            ],
            'nota_debito' => [
                FacResoluciones::TIPO_NOTA_DEBITO,
            ],
            'documento_soporte' => [
                FacResoluciones::TIPO_DOCUEMNTO_EQUIVALENTE,
            ],
        ];
    }

    /**
     * Mapa de alias de tipo → constante.
     */
    private function mapaTipos(): array
    {
        return [
            'pos'                  => FacResoluciones::TIPO_POS,
            'factura_electronica'  => FacResoluciones::TIPO_FACTURA_ELECTRONICA,
            'factura electronica'  => FacResoluciones::TIPO_FACTURA_ELECTRONICA,
            'nota_debito'          => FacResoluciones::TIPO_NOTA_DEBITO,
            'nota_debito'          => FacResoluciones::TIPO_NOTA_DEBITO,
            'nota_credito'         => FacResoluciones::TIPO_NOTA_CREDITO,
            'contingencia'         => FacResoluciones::TIPO_FACTURA_ELECTRONICA,
            'documento_equivalente'=> FacResoluciones::TIPO_DOCUEMNTO_EQUIVALENTE,
            'documento_soporte'    => FacResoluciones::TIPO_DOCUEMNTO_EQUIVALENTE,
        ];
    }

    public function run(array $args, array &$state): array
    {
        $busqueda = trim($args['busqueda'] ?? '');
        $contexto = strtolower(trim($args['contexto'] ?? ''));

        if ($contexto === '') {
            return [
                'success' => false,
                'message' => 'Debe indicar el contexto de la operación (venta, nota_credito, etc.).',
                'resoluciones' => [],
            ];
        }

        // Resolver tipos permitidos
        $tiposPermitidos = $this->resolverTipos($contexto, $args['tipos'] ?? null);

        if (empty($tiposPermitidos)) {
            return [
                'success' => false,
                'message' => "Contexto no reconocido: {$contexto}.",
                'resoluciones' => [],
            ];
        }

        // 1) Por permisos del usuario logueado
        $idsPermitidos = $this->idsResolucionesDelUsuario($state);
        $lista         = [];
        $origen        = 'permisos_usuario';

        if (!empty($idsPermitidos)) {
            $lista = $this->queryResoluciones(
                ids: $idsPermitidos,
                tipos: $tiposPermitidos,
                busqueda: $busqueda
            );
        }

        // 2) Fallback: búsqueda global por nombre/prefijo/número
        if (empty($lista) && $busqueda !== '') {
            $origen = 'busqueda_global';
            $lista  = $this->queryResoluciones(
                ids: null,
                tipos: $tiposPermitidos,
                busqueda: $busqueda
            );
        }

        // 3) Si no hay búsqueda y sí permisos: devolver todas las permitidas del contexto
        if (empty($lista) && $busqueda === '' && !empty($idsPermitidos)) {
            $lista = $this->queryResoluciones(
                ids: $idsPermitidos,
                tipos: $tiposPermitidos,
                busqueda: ''
            );
        }

        if (empty($lista)) {
            return [
                'success' => false,
                'message' => 'No se encontraron resoluciones disponibles para este contexto.',
                'contexto' => $contexto,
                'resoluciones' => [],
            ];
        }

        // Guardar en el estado (clave por contexto para no pisar otras)
        $claveId  = "id_resolucion_{$contexto}";
        $claveObj = "resolucion_{$contexto}";

        if (count($lista) === 1) {
            $state[$claveId]  = $lista[0]['id'];
            $state[$claveObj] = $lista[0];
        } else {
            $state["resoluciones_candidatas_{$contexto}"] = $lista;
        }

        return [
            'success'      => true,
            'contexto'     => $contexto,
            'origen'       => $origen,
            'resoluciones' => $lista,
        ];
    }

    /**
     * Resuelve la lista de tipos (constantes) permitidos.
     */
    private function resolverTipos(string $contexto, ?array $tiposCustom): array
    {
        if (!empty($tiposCustom)) {
            $mapa = $this->mapaTipos();
            $res  = [];
            foreach ($tiposCustom as $t) {
                $key = strtolower(trim($t));
                if (isset($mapa[$key])) {
                    $res[] = $mapa[$key];
                }
            }
            return array_values(array_unique($res));
        }

        return $this->tiposPorContexto()[$contexto] ?? [];
    }

    /**
     * Ejecuta la consulta a FacResoluciones.
     */
    private function queryResoluciones(?array $ids, array $tipos, string $busqueda): array
    {
        $query = FacResoluciones::query()->whereIn('tipo_resolucion', $tipos);

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        if ($busqueda !== '') {
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('prefijo', 'like', "%{$busqueda}%")
                  ->orWhere('numero_resolucion', 'like', "%{$busqueda}%");
            });
        }

        return $query->limit(10)
            ->get()
            ->map(fn ($r) => $this->formatear($r))
            ->values()
            ->toArray();
    }

    /**
     * IDs de resoluciones asignadas al usuario logueado.
     * Lee usuario_permisos.ids_resolucion_responsable (ej: "1,2,3").
     */
    private function idsResolucionesDelUsuario(array $state): array
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

        return $query->pluck('ids_resolucion_responsable')
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

    private function formatear(FacResoluciones $r): array
    {
        return [
            'id'                => $r->id,
            'nombre'            => $r->nombre,
            'nombre_completo'   => $r->nombre_completo,
            'prefijo'           => $r->prefijo,
            'consecutivo'       => $r->consecutivo,
            'numero_resolucion' => $r->numero_resolucion,
            'tipo_resolucion'   => $r->tipo_resolucion,
            'tipo_label'        => FacResoluciones::TIPO_RESOLUCION[$r->tipo_resolucion] ?? null,
            'fecha'             => $r->fecha,
            'vigencia'          => $r->vigencia,
            'es_valida'         => $r->is_valid,
            'es_activa'         => $r->is_active,
        ];
    }
}
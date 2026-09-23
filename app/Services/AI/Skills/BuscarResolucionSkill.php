<?php

namespace App\Services\AI\Skills;

use App\Models\Sistema\FacResoluciones;
use App\Models\Empresas\UsuarioPermisos;
use App\Services\AI\Estado;

class BuscarResolucionSkill extends Skill
{
    public function name(): string
    {
        return 'buscar_resolucion';
    }

    public function description(): string
    {
        return 'Busca resoluciones (POS, facturación electrónica, notas, etc.) asignadas al usuario o por nombre/prefijo. Indica siempre el contexto de la operación.';
    }

    public function parameters(): array
    {
        return [
            'busqueda' => [
                'type' => 'string',
                'description' => 'Nombre, prefijo o número de resolución. Opcional.',
                'required' => false,
            ],
            'contexto' => [
                'type' => 'string',
                'description' => 'Operación: "venta", "nota_credito", etc.',
                'required' => true,
            ],
            'auto' => [
                'type' => 'boolean',
                'description' => 'Si es true, la skill elige la primera resolución automáticamente y no pregunta.',
                'required' => false,
            ],
        ];
    }

    public function run(array $args, Estado $state): array
    {
        $busqueda = trim($args['busqueda'] ?? '');
        $contexto = strtolower(trim($args['contexto'] ?? ''));

        if ($contexto === '') {
            return [
                'success' => false,
                'message' => 'Debe indicar el contexto de la operación.',
                'resoluciones' => [],
            ];
        }

        $tiposPermitidos = $this->resolverTipos($contexto, $args['tipos'] ?? null);

        if (empty($tiposPermitidos)) {
            return [
                'success' => false,
                'message' => "Contexto no reconocido: {$contexto}.",
                'resoluciones' => [],
            ];
        }

        $idsPermitidos = $this->idsResolucionesDelUsuario($state);
        $lista         = [];

        if (!empty($idsPermitidos)) {
            $lista = $this->queryResoluciones($idsPermitidos, $tiposPermitidos, $busqueda);
        }

        if (empty($lista) && $busqueda !== '') {
            $lista = $this->queryResoluciones(null, $tiposPermitidos, $busqueda);
        }

        if (empty($lista) && $busqueda === '' && !empty($idsPermitidos)) {
            $lista = $this->queryResoluciones($idsPermitidos, $tiposPermitidos, '');
        }

        if (empty($lista)) {
            return [
                'success' => false,
                'message' => 'No se encontraron resoluciones disponibles.',
                'contexto' => $contexto,
                'resoluciones' => [],
            ];
        }

        $auto = (bool) ($args['auto'] ?? false);

        if ($auto || count($lista) === 1) {
            $state->setBorrador("id_resolucion_{$contexto}", $lista[0]['id']);
            $state->setBorrador("resolucion_{$contexto}", $lista[0]);
            $state->limpiarCandidatos("resolucion_{$contexto}");

            return [
                'success'      => true,
                'contexto'     => $contexto,
                'resolucion'   => $lista[0],
                'resoluciones' => $lista,
                'message'      => "Resolución '{$lista[0]['nombre']}' fijada en el borrador.",
            ];
        }

        $state->setCandidatos("resolucion_{$contexto}", $lista);

        return [
            'success'      => true,
            'contexto'     => $contexto,
            'resoluciones' => $lista,
            'message'      => 'Se encontraron varias resoluciones. Pide al usuario que elija una.',
        ];
    }

    private function tiposPorContexto(): array
    {
        return [
            'venta' => [FacResoluciones::TIPO_POS, FacResoluciones::TIPO_FACTURA_ELECTRONICA],
            'pos'   => [FacResoluciones::TIPO_POS],
            'factura_electronica' => [FacResoluciones::TIPO_FACTURA_ELECTRONICA],
            'contingencia' => [FacResoluciones::TIPO_POS, FacResoluciones::TIPO_FACTURA_ELECTRONICA],
            'nota_credito' => [FacResoluciones::TIPO_NOTA_CREDITO],
            'nota_debito'  => [FacResoluciones::TIPO_NOTA_DEBITO],
            'documento_soporte' => [FacResoluciones::TIPO_DOCUEMNTO_EQUIVALENTE],
        ];
    }

    private function mapaTipos(): array
    {
        return [
            'pos'                   => FacResoluciones::TIPO_POS,
            'factura_electronica'   => FacResoluciones::TIPO_FACTURA_ELECTRONICA,
            'factura electronica'   => FacResoluciones::TIPO_FACTURA_ELECTRONICA,
            'nota_debito'           => FacResoluciones::TIPO_NOTA_DEBITO,
            'nota_credito'          => FacResoluciones::TIPO_NOTA_CREDITO,
            'contingencia'          => FacResoluciones::TIPO_FACTURA_ELECTRONICA,
            'documento_equivalente' => FacResoluciones::TIPO_DOCUEMNTO_EQUIVALENTE,
            'documento_soporte'     => FacResoluciones::TIPO_DOCUEMNTO_EQUIVALENTE,
        ];
    }

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

    private function idsResolucionesDelUsuario(Estado $state): array
    {
        $idUser    = $state->idUser();
        $idEmpresa = $state->idEmpresa();

        if (!$idUser) return [];

        $query = UsuarioPermisos::query()->where('id_user', $idUser);
        if ($idEmpresa) {
            $query->where('id_empresa', $idEmpresa);
        }

        return $query->pluck('ids_resolucion_responsable')
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
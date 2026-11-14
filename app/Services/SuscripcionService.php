<?php

namespace App\Services;

use DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessProvisionedDatabase;
// MODELS
use App\Models\User;
use App\Models\Empresas\Empresa;
use App\Models\Empresas\PagosSuscripcion;
use App\Models\Empresas\EmpresaSuscripcion;
use App\Models\Empresas\EmpresaComponentesSuscripcion;

class SuscripcionService
{

    /**
     * Aplica un pago aprobado a la suscripción: avanza fechas, crea módulos nuevos
     * y recalcula el precio. NO abre ni cierra transacción (lo hace quien la llama).
     */
    public function aplicarPagoASuscripcion(PagosSuscripcion $pago, array $detalle): EmpresaSuscripcion
    {
        $idEmpresa = $pago->id_empresa;
        $empresa   = Empresa::find($idEmpresa);

        if (!$empresa) {
            throw new Exception('Empresa no encontrada para el pago ' . $pago->id);
        }

        $suscripcion = EmpresaSuscripcion::where('id_empresa', $idEmpresa)
            ->where('estado', '1')
            ->first();

        if (!$suscripcion) {
            throw new Exception('No hay suscripción activa para extender (empresa ' . $idEmpresa . ')');
        }

        $meses = max(1, (int) ($detalle['meses'] ?? 1));
        $hoy   = Carbon::now()->startOfDay();

        $actuales = EmpresaComponentesSuscripcion::where('id_empresa', $idEmpresa)
            ->where('id_empresa_suscripcion', $suscripcion->id)
            ->has('componente')
            ->get();

        // Estado global (mismo criterio que calcularDesglosePago)
        $fechaProximoCobro = $actuales->min('fecha_siguiente_cobro');
        $estado = 'nuevo';
        if ($fechaProximoCobro) {
            $estado = Carbon::parse($fechaProximoCobro)->startOfDay()->lt($hoy) ? 'vencido' : 'al_dia';
        }

        // ── Componentes existentes ──
        foreach ($actuales as $c) {
            if ($estado === 'vencido') {
                // Avanza desde la fecha vencida: cada mes pagado cubre un mes de deuda
                $base = $c->fecha_siguiente_cobro
                    ? Carbon::parse($c->fecha_siguiente_cobro)->startOfDay()
                    : $hoy->copy()->startOfMonth()->subDay();

                $c->fecha_inicio_suscripcion = $base->copy()->addDay()->format('Y-m-d');
                $c->fecha_siguiente_cobro    = $base->copy()->addMonthsNoOverflow($meses)->format('Y-m-d');

            } elseif ($estado === 'al_dia') {
                $fechaActual = $c->fecha_siguiente_cobro
                    ? Carbon::parse($c->fecha_siguiente_cobro)->startOfDay()
                    : $hoy->copy();
                $c->fecha_siguiente_cobro = $fechaActual->copy()->addMonthsNoOverflow($meses)->format('Y-m-d');

            } else { // nuevo (proporcional hasta fin de mes + meses-1)
                $c->fecha_inicio_suscripcion = $hoy->format('Y-m-d');
                $c->fecha_siguiente_cobro    = $hoy->copy()->startOfMonth()
                    ->addMonthsNoOverflow($meses)->subDay()->format('Y-m-d');
            }
            $c->save();
        }

        // ── Fecha de fin para módulos nuevos ──
        // Vencido / al día: misma cobertura que la base (ya actualizada arriba).
        // Nuevo: hasta fin de mes + (meses-1).
        $fechaNuevos = $hoy->copy()->startOfMonth()->addMonthsNoOverflow($meses)->subDay()->format('Y-m-d');
        if ($estado !== 'nuevo' && $actuales->isNotEmpty()) {
            $fechaNuevos = $actuales->min('fecha_siguiente_cobro');
        }

        // ── Componentes nuevos ──
        foreach (($detalle['componentes'] ?? []) as $c) {
            if (empty($c['es_nuevo'])) continue;

            $existe = EmpresaComponentesSuscripcion::where('id_empresa', $idEmpresa)
                ->where('id_empresa_suscripcion', $suscripcion->id)
                ->where('id_componente', $c['id_componente'])
                ->exists();
            if ($existe) continue;

            EmpresaComponentesSuscripcion::create([
                'id_empresa'               => $idEmpresa,
                'id_empresa_suscripcion'   => $suscripcion->id,
                'id_componente'            => $c['id_componente'],
                'cantidad'                 => 1,
                'precio'                   => (float) $c['precio'],
                'descuento'                => 0,
                'fecha_inicio_suscripcion' => $hoy->format('Y-m-d'),
                'fecha_siguiente_cobro'    => $fechaNuevos,
                'created_by'               => $empresa->id_usuario_owner ?: 0,
                'updated_by'               => $empresa->id_usuario_owner ?: 0,
            ]);
        }

        // ── Recalcular precio de la suscripción ──
        $total = 0;
        foreach (EmpresaComponentesSuscripcion::where('id_empresa', $idEmpresa)
                    ->where('id_empresa_suscripcion', $suscripcion->id)
                    ->has('componente')->get() as $c) {
            $cant = max(1, (int) ($c->cantidad ?? 1));
            $desc = (float) ($c->descuento ?? 0);
            $total += ((float) $c->precio - $desc) * $cant;
        }
        $suscripcion->precio = $total;
        $suscripcion->save();

        return $suscripcion;
    }
}
<?php

namespace App\Services;

use DB;
use App\Models\Sistema\Presupuesto;
use App\Models\Sistema\PresupuestoDetalle;
use App\Models\Sistema\PlanCuentas;
use Illuminate\Support\Facades\Log;

class PresupuestoService
{
    /**
     * Sincroniza una cuenta con todos los presupuestos activos.
     * 
     * @param int $cuentaId ID de la cuenta en plan_cuentas (ya actualizada)
     * @param int $userId ID del usuario
     * @param string|null $oldCuenta Número de cuenta ANTES de actualizar (para buscarlo en presupuesto)
     */
    public function syncAccountWithBudgets(int $cuentaId, int $userId, ?string $oldCuenta = null): void
    {
        $cuenta = PlanCuentas::find($cuentaId);
        if (!$cuenta || !$cuenta->auxiliar) {
            return;
        }

        $tipoPresupuesto = $this->getTipoPresupuestoFromCuenta($cuenta);
        if ($tipoPresupuesto === null) {
            return;
        }

        $anioActual = date('Y');
        $presupuestos = Presupuesto::where('tipo', $tipoPresupuesto)
            ->where('periodo', $anioActual)
            ->get();

        if ($presupuestos->isEmpty()) {
            return;
        }

        foreach ($presupuestos as $presupuesto) {
            // Buscar por el número de cuenta VIEJO (si se proporcionó) o el nuevo
            $searchCuenta = $oldCuenta ?? $cuenta->cuenta;
            
            $detalle = PresupuestoDetalle::where('id_presupuesto', $presupuesto->id)
                ->where('cuenta', $searchCuenta)
                ->first();

            if (!$detalle) {
                // Si no existe, crear nuevo registro con ceros
                PresupuestoDetalle::create([
                    'id_presupuesto' => $presupuesto->id,
                    'id_padre' => $cuenta->id_padre,
                    'cuenta' => $cuenta->cuenta,
                    'nombre' => $cuenta->nombre,
                    'auxiliar' => 1,
                    'presupuesto' => 0,
                    'diferencia' => 0,
                    'enero' => 0,
                    'febrero' => 0,
                    'marzo' => 0,
                    'abril' => 0,
                    'mayo' => 0,
                    'junio' => 0,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => 0,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            } else {
                // Actualizar con los nuevos valores (número de cuenta y nombre)
                $detalle->update([
                    'cuenta' => $cuenta->cuenta,
                    'nombre' => $cuenta->nombre,
                    'id_padre' => $cuenta->id_padre,
                    'updated_by' => $userId,
                ]);
            }
        }

        // Recalcular totales del primer presupuesto (opcional, según necesidad)
        if ($presupuestos->isNotEmpty()) {
            $this->recalculateTotals($presupuestos->first()->id);
        }
    }

    private function getTipoPresupuestoFromCuenta(PlanCuentas $cuenta): ?int
    {
        $primerDigito = substr($cuenta->cuenta, 0, 1);
        if ($primerDigito === '4') return 1;
        if ($primerDigito === '5') return 2;
        return null;
    }

    private function recalculateTotals(int $presupuestoId): void
    {
        $sumas = PresupuestoDetalle::where('id_presupuesto', $presupuestoId)
            ->where('auxiliar', 1)
            ->select(
                DB::raw('SUM(presupuesto) as total_presupuesto'),
                DB::raw('SUM(diferencia) as total_diferencia'),
                DB::raw('SUM(enero) as total_enero'),
                DB::raw('SUM(febrero) as total_febrero'),
                DB::raw('SUM(marzo) as total_marzo'),
                DB::raw('SUM(abril) as total_abril'),
                DB::raw('SUM(mayo) as total_mayo'),
                DB::raw('SUM(junio) as total_junio'),
                DB::raw('SUM(julio) as total_julio'),
                DB::raw('SUM(agosto) as total_agosto'),
                DB::raw('SUM(septiembre) as total_septiembre'),
                DB::raw('SUM(octubre) as total_octubre'),
                DB::raw('SUM(noviembre) as total_noviembre'),
                DB::raw('SUM(diciembre) as total_diciembre')
            )->first();

        if ($sumas) {
            PresupuestoDetalle::where('id_presupuesto', $presupuestoId)
                ->where('auxiliar', 2)
                ->update([
                    'presupuesto' => $sumas->total_presupuesto ?? 0,
                    'diferencia' => $sumas->total_diferencia ?? 0,
                    'enero' => $sumas->total_enero ?? 0,
                    'febrero' => $sumas->total_febrero ?? 0,
                    'marzo' => $sumas->total_marzo ?? 0,
                    'abril' => $sumas->total_abril ?? 0,
                    'mayo' => $sumas->total_mayo ?? 0,
                    'junio' => $sumas->total_junio ?? 0,
                    'julio' => $sumas->total_julio ?? 0,
                    'agosto' => $sumas->total_agosto ?? 0,
                    'septiembre' => $sumas->total_septiembre ?? 0,
                    'octubre' => $sumas->total_octubre ?? 0,
                    'noviembre' => $sumas->total_noviembre ?? 0,
                    'diciembre' => $sumas->total_diciembre ?? 0,
                ]);
        }

        $this->updateParentRows($presupuestoId);
    }

    private function updateParentRows(int $presupuestoId): void
    {
        $parents = PresupuestoDetalle::where('id_presupuesto', $presupuestoId)
            ->where('auxiliar', 0)
            ->get();

        foreach ($parents as $parent) {
            $sumHijos = PresupuestoDetalle::where('id_presupuesto', $presupuestoId)
                ->where('id_padre', $parent->id)
                ->select(
                    DB::raw('SUM(presupuesto) as total_presupuesto'),
                    DB::raw('SUM(diferencia) as total_diferencia'),
                    DB::raw('SUM(enero) as total_enero'),
                    DB::raw('SUM(febrero) as total_febrero'),
                    DB::raw('SUM(marzo) as total_marzo'),
                    DB::raw('SUM(abril) as total_abril'),
                    DB::raw('SUM(mayo) as total_mayo'),
                    DB::raw('SUM(junio) as total_junio'),
                    DB::raw('SUM(julio) as total_julio'),
                    DB::raw('SUM(agosto) as total_agosto'),
                    DB::raw('SUM(septiembre) as total_septiembre'),
                    DB::raw('SUM(octubre) as total_octubre'),
                    DB::raw('SUM(noviembre) as total_noviembre'),
                    DB::raw('SUM(diciembre) as total_diciembre')
                )->first();

            if ($sumHijos) {
                $parent->update([
                    'presupuesto' => $sumHijos->total_presupuesto ?? 0,
                    'diferencia' => $sumHijos->total_diferencia ?? 0,
                    'enero' => $sumHijos->total_enero ?? 0,
                    'febrero' => $sumHijos->total_febrero ?? 0,
                    'marzo' => $sumHijos->total_marzo ?? 0,
                    'abril' => $sumHijos->total_abril ?? 0,
                    'mayo' => $sumHijos->total_mayo ?? 0,
                    'junio' => $sumHijos->total_junio ?? 0,
                    'julio' => $sumHijos->total_julio ?? 0,
                    'agosto' => $sumHijos->total_agosto ?? 0,
                    'septiembre' => $sumHijos->total_septiembre ?? 0,
                    'octubre' => $sumHijos->total_octubre ?? 0,
                    'noviembre' => $sumHijos->total_noviembre ?? 0,
                    'diciembre' => $sumHijos->total_diciembre ?? 0,
                ]);
            }
        }
    }
}
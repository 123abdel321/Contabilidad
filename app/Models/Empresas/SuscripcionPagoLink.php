<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuscripcionPagoLink extends Model
{
    use HasFactory;

    protected $table = 'suscripcion_pago_links';

    protected $fillable = [
        'id_empresa',
        'token',
        'created_by',
        'usado_at',
        'veces_usado',
        'activo',
        'expira_at',
    ];

    protected $casts = [
        'usado_at'    => 'datetime',
        'expira_at'   => 'datetime',
        'activo'      => 'boolean',
        'veces_usado' => 'integer',
    ];

    // ============================================================
    // RELACIONES
    // ============================================================

    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresas\Empresa::class, 'id_empresa');
    }

    public function creador()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * ¿El link es válido para usarse?
     */
    public function esValido(): bool
    {
        if (! $this->activo) {
            return false;
        }

        if ($this->expira_at && $this->expira_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Marca el link como usado (después de un pago exitoso).
     */
    public function marcarComoUsado(): void
    {
        $this->update([
            'usado_at'    => now(),
            'veces_usado' => $this->veces_usado + 1,
        ]);
    }

    /**
     * URL pública lista para enviar.
     */
    public function getUrlPublicaAttribute(): string
    {
        return url('/pagar/' . $this->token);
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeActivos($query)
    {
        return $query->where('activo', true)
            ->where(function ($q) {
                $q->whereNull('expira_at')
                  ->orWhere('expira_at', '>', now());
            });
    }
}
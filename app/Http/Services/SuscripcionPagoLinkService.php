<?php

namespace App\Http\Services;

use App\Models\Empresas\SuscripcionPagoLink;
use Illuminate\Support\Str;

class SuscripcionPagoLinkService
{
    /**
     * Obtiene el link de una empresa. Si no existe, lo crea.
     *
     * @param  int       $idEmpresa
     * @param  int|null  $createdBy
     * @return SuscripcionPagoLink
     */
    public function obtenerOCrear(int $idEmpresa, ?int $createdBy = null): SuscripcionPagoLink
    {
        $link = SuscripcionPagoLink::where('id_empresa', $idEmpresa)->first();

        if ($link) {
            // Si estaba inactivo, lo reactivamos (por si acaso)
            if (! $link->activo) {
                $link->update(['activo' => true]);
            }
            return $link;
        }

        return $this->crear($idEmpresa, $createdBy);
    }

    /**
     * Crea un nuevo link para una empresa.
     */
    public function crear(int $idEmpresa, ?int $createdBy = null): SuscripcionPagoLink
    {
        return SuscripcionPagoLink::create([
            'id_empresa'  => $idEmpresa,
            'token'       => $this->generarTokenUnico(),
            'created_by'  => $createdBy,
            'activo'      => true,
            'expira_at'   => null, // sin expiración por ahora
        ]);
    }

    /**
     * Busca un link por su token y valida que siga activo.
     *
     * @return SuscripcionPagoLink|null
     */
    public function buscarPorToken(string $token): ?SuscripcionPagoLink
    {
        $link = SuscripcionPagoLink::where('token', $token)
            ->with('empresa')
            ->first();

        if (! $link || ! $link->esValido()) {
            return null;
        }

        return $link;
    }

    /**
     * Marca un link como usado (después de un pago exitoso).
     */
    public function marcarUsado(SuscripcionPagoLink $link): void
    {
        $link->marcarComoUsado();
    }

    /**
     * Revoca un link (por si el admin quiere invalidarlo).
     */
    public function revocar(int $idEmpresa): bool
    {
        $link = SuscripcionPagoLink::where('id_empresa', $idEmpresa)->first();
        if (! $link) {
            return false;
        }
        return $link->update(['activo' => false]);
    }

    /**
     * Regenera el token (invalida el anterior).
     */
    public function regenerar(int $idEmpresa, ?int $createdBy = null): SuscripcionPagoLink
    {
        SuscripcionPagoLink::where('id_empresa', $idEmpresa)
            ->update(['activo' => false]);

        return $this->crear($idEmpresa, $createdBy);
    }

    /**
     * Genera un token único de 48 caracteres.
     */
    protected function generarTokenUnico(): string
    {
        do {
            $token = Str::random(48);
        } while (SuscripcionPagoLink::where('token', $token)->exists());

        return $token;
    }
}
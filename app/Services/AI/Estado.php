<?php

namespace App\Services\AI;

use App\Services\AI\Flujos\Flujo;

class Estado
{
    protected array $data;

    public function __construct(array $data = [])
    {
        $this->data = array_merge([
            'contexto'   => [],
            'flujo'      => null,
            'borrador'   => [],
            'candidatos' => [],
            'historial'  => [],
            'resultado'  => null,
        ], $data);
    }

    // ---------------------------------------------------------
    // Contexto (sesión)
    // ---------------------------------------------------------
    public function setContexto(string $clave, $valor): self
    {
        $this->data['contexto'][$clave] = $valor;
        return $this;
    }

    public function contexto(string $clave, $default = null)
    {
        return $this->data['contexto'][$clave] ?? $default;
    }

    public function idUser(): ?int
    {
        return $this->data['contexto']['id_user'] ?? null;
    }

    public function idEmpresa(): ?int
    {
        return $this->data['contexto']['id_empresa'] ?? null;
    }

    // ---------------------------------------------------------
    // Flujo activo
    // ---------------------------------------------------------
    public function setFlujo(?string $flujo): self
    {
        $this->data['flujo'] = $flujo;
        return $this;
    }

    public function flujo(): ?string
    {
        return $this->data['flujo'];
    }

    public function iniciarFlujo(Flujo $flujo): self
    {
        $this->data['flujo']    = $flujo->nombre();
        $this->data['borrador'] = [];
        $this->data['candidatos'] = [];
        $this->data['resultado']  = null;
        return $this;
    }

    // ---------------------------------------------------------
    // Borrador
    // ---------------------------------------------------------
    public function setBorrador(string $clave, $valor): self
    {
        $this->data['borrador'][$clave] = $valor;
        return $this;
    }

    public function borrador(?string $clave = null, $default = null)
    {
        if ($clave === null) {
            return $this->data['borrador'];
        }
        return $this->data['borrador'][$clave] ?? $default;
    }

    public function tieneBorrador(string $clave): bool
    {
        return !empty($this->data['borrador'][$clave]);
    }

    // ---------------------------------------------------------
    // Candidatos (resultados de búsquedas múltiples)
    // ---------------------------------------------------------
    public function setCandidatos(string $tipo, array $lista): self
    {
        $this->data['candidatos'][$tipo] = $lista;
        return $this;
    }

    public function candidatos(string $tipo): array
    {
        return $this->data['candidatos'][$tipo] ?? [];
    }

    public function limpiarCandidatos(string $tipo): self
    {
        unset($this->data['candidatos'][$tipo]);
        return $this;
    }

    // ---------------------------------------------------------
    // Historial
    // ---------------------------------------------------------
    public function historial(): array
    {
        return $this->data['historial'];
    }

    public function setHistorial(array $historial): self
    {
        $this->data['historial'] = $historial;
        return $this;
    }

    public function agregarHistorial(array $item): self
    {
        $this->data['historial'][] = $item;
        return $this;
    }

    // ---------------------------------------------------------
    // Resultado final
    // ---------------------------------------------------------
    public function setResultado($resultado): self
    {
        $this->data['resultado'] = $resultado;
        return $this;
    }

    public function resultado()
    {
        return $this->data['resultado'];
    }

    // ---------------------------------------------------------
    // Utilidades
    // ---------------------------------------------------------

    /**
     * Devuelve los campos requeridos que aún NO están en el borrador.
     */
    public function faltantes(Flujo $flujo): array
    {
        $faltantes = [];
        foreach ($flujo->camposRequeridos() as $campo) {
            if (empty($this->data['borrador'][$campo])) {
                $faltantes[] = $campo;
            }
        }
        return $faltantes;
    }

    /**
     * Devuelve un array plano con todo (para persistir en sesión/BD).
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
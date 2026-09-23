<?php

namespace App\Services\AI\Flujos;

interface Flujo
{
    /**
     * Nombre interno del flujo (usado como clave en $state['flujo']).
     */
    public function nombre(): string;

    /**
     * Etiqueta legible del flujo.
     */
    public function etiqueta(): string;

    /**
     * Skills que este flujo permite ejecutar.
     * El orquestador solo expone estas tools a OpenAI.
     */
    public function skillsPermitidas(): array;

    /**
     * Campos que DEBEN estar en $state['borrador'] para poder finalizar.
     * Se usan para saber cuándo el flujo está listo.
     */
    public function camposRequeridos(): array;

    /**
     * Mapeo: campo del borrador → skill que lo llena.
     * Útil para saber qué le falta al flujo sin adivinar.
     */
    public function camposConSkill(): array;

    /**
     * System prompt específico del flujo.
     * Se le pasa a OpenAI como 'instructions'.
     */
    public function systemPrompt(): string;

    /**
     * Skill que ejecuta el cierre de la operación (crear_venta, crear_compra, etc.).
     * Debe existir siempre.
     */
    public function skillFinal(): string;
}
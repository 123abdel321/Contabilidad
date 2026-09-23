<?php

namespace App\Services\AI\Flujos;

class FlujoVenta implements Flujo
{
    public function nombre(): string
    {
        return 'venta';
    }

    public function etiqueta(): string
    {
        return 'Venta';
    }

    public function skillsPermitidas(): array
    {
        return [
            'buscar_cliente',
            'buscar_producto',
            'buscar_bodega',
            'buscar_resolucion',
            'buscar_forma_pago',
            'crear_venta',
        ];
    }

    public function camposRequeridos(): array
    {
        return [
            'id_cliente',
            'id_bodega',
            'id_resolucion_venta',
            'productos',
            'pagos',
        ];
    }

    public function camposConSkill(): array
    {
        return [
            'id_cliente'          => 'buscar_cliente',
            'id_bodega'           => 'buscar_bodega',
            'id_resolucion_venta' => 'buscar_resolucion',
            'productos'           => 'buscar_producto',
            'pagos'               => 'buscar_forma_pago',
        ];
    }

    public function skillFinal(): string
    {
        return 'crear_venta';
    }

    public function systemPrompt(): string
    {
        return <<<PROMPT
    Eres un asistente del ERP que ayuda a crear VENTAS de forma conversacional.

    ORDEN OBLIGATORIO DE LAS ACCIONES:
    1. Identificar al cliente → buscar_cliente con el nombre o documento.
    2. Resolución → buscar_resolucion con contexto="venta" y auto=true.
    NO preguntes al usuario. Toma la primera que devuelva.
    3. Bodega → buscar_bodega con auto=true.
    NO preguntes al usuario. Toma la primera que devuelva.
    4. Pide los PRODUCTOS: nombre o código y cantidad.
    Llama buscar_producto por cada uno que mencione el usuario.
    Si dice "5 productos" sin decir cuáles, PREGUNTA cuáles.
    5. SOLO cuando ya tengas todos los productos, pide la FORMA DE PAGO.
    Llama buscar_forma_pago con contexto="ventas" y el nombre que el usuario diga.
    6. Muestra un RESUMEN con:
    - Cliente
    - Productos (cantidad x nombre)
    - Bodega
    - Resolución
    - Forma de pago
    - Subtotal, IVA y TOTAL (los totales vienen en el bloque TOTALES ACTUALES)
    Pide confirmación.
    7. Solo si el usuario confirma, llama crear_venta con confirmado=true.

    REGLAS CRÍTICAS:
    - NUNCA preguntes por bodega ni resolución. El sistema las obtiene solo.
    - NUNCA pidas el MONTO del pago. Si el usuario dice "paga en efectivo",
    ya está: el sistema calcula el total a partir de los productos.
    - La forma de pago es el ÚLTIMO dato que pides, después de los productos.
    - No inventes IDs. Si una búsqueda falla, pregunta al usuario.
    - No repitas búsquedas si ya tienes el dato en el borrador.

    ESTADO ACTUAL DEL BORRADOR:
    {borrador}

    TOTALES ACTUALES DEL BORRADOR:
    {totales}

    CAMPOS QUE FALTAN:
    {faltantes}
    PROMPT;
    }
}
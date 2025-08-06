<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inf_ventas_acumulada_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_venta_acumulada');
            $table->integer('id_nit')->nullable();
            $table->integer('id_cuenta')->nullable();
            $table->integer('id_usuario')->nullable();
            $table->integer('id_comprobante')->nullable();
            $table->integer('id_centro_costos')->nullable();
            $table->string('cuenta', 10)->nullable();
            $table->string('nombre_cuenta', 100)->nullable();
            $table->string('numero_documento', 50)->nullable();
            $table->string('nombre_nit', 100)->nullable();
            $table->string('razon_social', 200)->nullable();
            $table->string('codigo_cecos', 50)->nullable();
            $table->string('nombre_cecos', 200)->nullable();
            $table->string('codigo_bodega', 50)->nullable();
            $table->string('nombre_bodega', 200)->nullable();
            $table->string('codigo_producto', 50)->nullable();
            $table->string('nombre_producto', 200)->nullable();
            $table->string('codigo_comprobante', 50)->nullable();
            $table->string('nombre_comprobante', 200)->nullable();
            $table->string('documento_referencia', 50)->nullable();
            $table->string('consecutivo', 50)->nullable();
            $table->string('observacion', 200)->nullable();
            $table->string('fecha_manual', 200)->nullable();
            $table->decimal('cantidad', 15)->nullable();
            $table->decimal('costo', 15)->nullable();
            $table->decimal('subtotal', 15)->nullable();
            $table->decimal('descuento_porcentaje', 15)->nullable();
            $table->decimal('rete_fuente_porcentaje', 15)->nullable();
            $table->decimal('descuento_valor', 15)->nullable();
            $table->decimal('iva_porcentaje', 15)->nullable();
            $table->decimal('iva_valor', 15)->nullable();
            $table->decimal('total', 15)->nullable();
            $table->dateTime('fecha_creacion')->nullable();
            $table->dateTime('fecha_edicion')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('nivel')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inf_ventas_acumulada_detalles');
    }
};

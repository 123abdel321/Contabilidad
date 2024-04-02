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
        Schema::create('inf_resumen_comprobante_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_resumen_comprobante');
            $table->integer('id_nit')->nullable();
            $table->integer('id_cuenta')->nullable();
            $table->integer('id_usuario')->nullable();
            $table->integer('id_comprobante')->nullable();
            $table->integer('id_centro_costos')->nullable();
            $table->string('cuenta', 100)->nullable();
            $table->string('nombre_cuenta', 100)->nullable();
            $table->string('numero_documento', 50)->nullable();
            $table->string('nombre_nit', 100)->nullable();
            $table->string('razon_social', 100)->nullable();
            $table->string('codigo_cecos', 50)->nullable();
            $table->string('nombre_cecos', 100)->nullable();
            $table->string('codigo_comprobante', 50)->nullable();
            $table->string('nombre_comprobante', 100)->nullable();
            $table->string('documento_referencia', 50)->nullable();
            $table->string('consecutivo', 50)->nullable();
            $table->string('concepto', 200)->nullable();
            $table->string('fecha_manual', 200)->nullable();
            $table->decimal('debito', 15)->nullable();
            $table->decimal('credito', 15)->nullable();
            $table->decimal('diferencia', 15)->nullable();
            $table->integer('registros')->default(1)->nullable();
            $table->integer('nivel')->nullable();
            $table->dateTime('fecha_creacion')->nullable();
            $table->dateTime('fecha_edicion')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inf_resumen_comprobante_detalles');
    }
};

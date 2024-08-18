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
        Schema::connection('informes')->create('inf_resultado_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_resultado');
            $table->integer('id_nit')->nullable();
            $table->integer('id_cuenta')->nullable();
            $table->string('cuenta', 100)->nullable();
            $table->integer('auxiliar')->nullable();
            $table->string('nombre_cuenta', 100)->nullable();
            $table->string('numero_documento', 100)->nullable();
            $table->string('nombre_nit', 100)->nullable();
            $table->string('razon_social', 100)->nullable();
            $table->decimal('saldo_anterior', 15);
            $table->decimal('debito', 15);
            $table->decimal('credito', 15);
            $table->decimal('saldo_final', 15);
            $table->decimal('ppto_anterior', 15);
            $table->decimal('ppto_movimiento', 15);
            $table->decimal('ppto_acumulado', 15);
            $table->decimal('ppto_diferencia', 15);
            $table->decimal('ppto_porcentaje', 15);
            $table->decimal('ppto_porcentaje_acumulado', 15);
            $table->integer('nivel')->nullable();
            $table->integer('errores')->nullable();
            $table->integer('documentos_totales')->nullable();
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
        Schema::connection('informes')->dropIfExists('inf_balance_detalles');
    }
};

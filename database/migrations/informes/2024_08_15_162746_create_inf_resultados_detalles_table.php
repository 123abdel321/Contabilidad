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

            $table->decimal('saldo_anterior', 15, 2)->default(0);
            $table->decimal('enero', 15, 2)->default(0);
            $table->decimal('febrero', 15, 2)->default(0);
            $table->decimal('marzo', 15, 2)->default(0);
            $table->decimal('abril', 15, 2)->default(0);
            $table->decimal('mayo', 15, 2)->default(0);
            $table->decimal('junio', 15, 2)->default(0);
            $table->decimal('julio', 15, 2)->default(0);
            $table->decimal('agosto', 15, 2)->default(0);
            $table->decimal('septiembre', 15, 2)->default(0);
            $table->decimal('octubre', 15, 2)->default(0);
            $table->decimal('noviembre', 15, 2)->default(0);
            $table->decimal('diciembre', 15, 2)->default(0);

            $table->decimal('saldo_final', 15, 2)->default(0);
            $table->decimal('ppto_anterior', 15, 2)->default(0);
            $table->decimal('ppto_movimiento', 15, 2)->default(0);
            $table->decimal('ppto_acumulado', 15, 2)->default(0);
            $table->decimal('ppto_diferencia', 15, 2)->default(0);
            $table->decimal('ppto_porcentaje', 15, 2)->default(0);
            $table->decimal('ppto_porcentaje_acumulado', 15, 2)->default(0);

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

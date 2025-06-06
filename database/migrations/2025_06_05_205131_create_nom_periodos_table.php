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
        Schema::create('nom_periodos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->default('Personalizado')->comment('quincenal, mensual, personalizado');
            $table->char('dias_salario', 10)->default('30')->comment('Dias de salario por cada periodo');
            $table->integer('horas_dia')->default(8)->comment('Horas laboradas al dia. 8: turno completo, 4: medio tiempo, 0: especificado por el usuario');
            $table->boolean('tipo_dia_pago')->default(false)->comment('0:ordinal, 1:calendario');
            $table->string('periodo_dias_ordinales')->nullable()->default('31')->comment('Separado por comas.');
            $table->string('periodo_dias_calendario')->nullable()->comment('Numero fijo que se contaran seguido, ej: 14 días.');
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
        Schema::dropIfExists('nom_periodos');
    }
};

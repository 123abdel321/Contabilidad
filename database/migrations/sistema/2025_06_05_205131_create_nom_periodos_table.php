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
            $table->string('nombre')->default('Personalizado')->comment('Tipo de período de nómina: Quincenal, Mensual o Personalizado (configuración especial)');
            $table->char('dias_salario', 10)->default('30')->comment('Cantidad de días de salario que comprende cada período de pago. Valor típico: 30 para mensual, 15 para quincenal');
            $table->integer('horas_dia')->default(8)->comment('Horas laborales diarias estándar: 8 (jornada completa), 4 (media jornada), 0 (horario personalizado definido por usuario)');
            $table->boolean('tipo_dia_pago')->default(false)->comment('Método de cálculo del día de pago: 0=Ordinal (días específicos del mes), 1=Calendario (intervalos fijos de días)');
            $table->string('periodo_dias_ordinales')->nullable()->default('31')->comment('Días específicos del mes para pago (ordinal), separados por comas. Ej: "15,30" para pagos quincenales');
            $table->string('periodo_dias_calendario')->nullable()->comment('Intervalo fijo de días para pago (calendario). Ej: "14" para pagos cada 2 semanas');
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

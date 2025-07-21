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
        Schema::create('nom_vacaciones', function (Blueprint $table) {
            $table->id();
            $table->integer('id_empleado')->comment('ID del empleado asociado a las vacaciones');
            $table->integer('id_contrato')->comment('ID del contrato del empleado, para seguimiento de acumulados y trazabilidad');
            $table->boolean('metodo')->default(0)->comment('Método de liquidación: 0=fijo, 1=variable');
            $table->date('fecha_inicio')->comment('Fecha de inicio del período de vacaciones (asignada manualmente por el usuario)');
            $table->date('fecha_fin')->comment('Fecha de finalización del período de vacaciones (calculada automáticamente según los días)');
            $table->integer('dias_habiles')->default(1)->comment('Cantidad de días hábiles de vacaciones disfrutados por el empleado');
            $table->integer('dias_compensados')->default(0)->comment('Cantidad de días compensados en dinero en lugar de disfrute');
            $table->integer('dias_no_habiles')->default(0)->comment('Cantidad de días no hábiles dentro del período de vacaciones (domingos o sábados según política de la empresa)');
            $table->decimal('promedio_otros', 12, 2)->default(0)->comment('Promedio de otros conceptos salariales (ej. horas extras, recargos) para liquidación variable');
            $table->decimal('salario_dia', 12, 2)->default(0)->comment('Valor del salario diario o promedio diario para método variable');
            $table->decimal('valor_dia_vacaciones', 12, 2)->default(0)->comment('Valor del día de vacaciones (salario diario + promedio otros conceptos)');
            $table->decimal('total_disfrutado', 12, 2)->default(0)->comment('Valor total de las vacaciones disfrutadas en dinero');
            $table->decimal('total_compensado', 12, 2)->default(0)->comment('Valor total de las vacaciones compensadas en dinero');
            $table->char('observacion', 200)->nullable()->comment('Observaciones adicionales sobre la liquidación de vacaciones');
            $table->decimal('salario_base', 12, 2)->default(0)->comment('Salario base del mes anterior, usado para cálculo de la PILA durante vacaciones');
            $table->integer('updated_by')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nom_vacaciones');
    }
};

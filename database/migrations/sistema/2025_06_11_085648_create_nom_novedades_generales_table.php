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
        Schema::create('nom_novedades_generales', function (Blueprint $table) {
            $table->id();
            $table->integer('relation_id')->nullable();
            $table->integer('relation_type')->nullable();
            $table->integer('id_empleado')->comment('ID del empleado al que se le registra la novedad');
            $table->integer('id_periodo_pago')->comment('ID del período de pago en el que se aplica la novedad');
            $table->integer('id_concepto')->comment('ID del concepto de nómina asociado (ej. horas extras, bonificaciones, descuentos, etc.)');
            $table->integer('tipo_unidad')->default(2)->comment('Tipo de unidad aplicada: 0 = horas, 1 = días, 2 = valor fijo');
            $table->integer('unidades')->nullable()->comment('Cantidad de unidades aplicadas (horas o días); NULL si tipo_unidad es valor');
            $table->decimal('valor', 12, 2)->default(0)->comment('Valor calculado resultante (puede ser cantidad * tarifa o un valor fijo)');
            $table->decimal('porcentaje', 7, 4)->nullable()->comment('Porcentaje aplicado sobre la base para calcular el valor');
            $table->decimal('base', 12, 2)->nullable()->comment('Base sobre la que se calcula el porcentaje o multiplicación');
            $table->char('observacion', 200)->default("")->comment('Observaciones o comentarios adicionales sobre la novedad');
            $table->date('fecha_inicio')->nullable()->comment('Fecha de inicio de la novedad (especialmente para Nómina Electrónica)');
            $table->date('fecha_fin')->nullable()->comment('Fecha de fin de la novedad (especialmente para Nómina Electrónica)');
            $table->time('hora_inicio')->nullable()->comment('Hora de inicio (solo para novedades horarias en Nómina Electrónica)');
            $table->time('hora_fin')->nullable()->comment('Hora de fin (solo para novedades horarias en Nómina Electrónica)');
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
        Schema::dropIfExists('nom_novedades_generales');
    }
};

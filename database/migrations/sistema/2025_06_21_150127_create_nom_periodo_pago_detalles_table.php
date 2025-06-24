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
        Schema::create('nom_periodo_pago_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_periodo_pago');
            $table->integer('id_concepto');
            $table->tinyInteger('tipo_unidad')->default(2)->comment('Tipo de unidad usada para el cálculo: 0 = horas, 1 = días, 2 = valor fijo');
            $table->integer('unidades')->nullable()->comment('Cantidad de unidades (horas o días según tipo_unidad). NULL si se trata de un valor fijo');
            $table->decimal('valor', 12, 2)->default(0)->comment('Valor total calculado para el concepto (puede ser fijo o basado en unidades)');
            $table->decimal('porcentaje', 7, 4)->nullable()->comment('Porcentaje aplicado al concepto, si corresponde');
            $table->decimal('base', 12, 2)->nullable()->comment('Base sobre la cual se aplicó el porcentaje o se calcularon las unidades');
            $table->string('observacion', 200)->default('');
            $table->date('fecha_inicio')->nullable()->comment('Fecha inicial del evento (usado en Novedades Electrónicas - NE)');
            $table->date('fecha_fin')->nullable()->comment('Fecha final del evento (usado en Novedades Electrónicas - NE)');
            $table->time('hora_inicio', 0)->nullable()->comment('Hora inicial del evento (usado en NE cuando aplica por horas)');
            $table->time('hora_fin', 0)->nullable()->comment('Hora final del evento (usado en NE cuando aplica por horas)');
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
        Schema::dropIfExists('nom_periodo_pago_detalles');
    }
};

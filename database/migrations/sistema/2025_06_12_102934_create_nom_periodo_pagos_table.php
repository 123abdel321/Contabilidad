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
        Schema::create('nom_periodo_pagos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_empleado')->comment('Id del empleado');
            $table->integer('id_contrato');
            $table->date('fecha_inicio_periodo')->comment('Fecha incremental calculada por el sistema a partir del contrato');
            $table->date('fecha_fin_periodo');
            $table->integer('estado')->default(0)->comment('0: pendiente, 1: causado, 2: pagado');
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
        Schema::dropIfExists('nom_periodo_pagos');
    }
};

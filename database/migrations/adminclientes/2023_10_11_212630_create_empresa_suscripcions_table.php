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
        Schema::create('empresa_suscripcions', function (Blueprint $table) {
            $table->id();
            $table->integer('id_empresa');
            $table->integer('id_suscripcion');
            $table->integer('id_forma_pago');
            $table->integer('dias_para_pagar')->comment('Dias que tiene para pagar antes de ser desconectado	');
            $table->integer('dias_de_gracia')->comment('Dias de gracia para pagar	');
            $table->date('fecha_inicio_suscripcion')->nullable();
            $table->date('fecha_inicio_facturacion')->nullable();
            $table->dateTime('fecha_siguiente_pago')->nullable();
            $table->boolean('estado')->comment('0 - INACTIVA, 1 - ACTIVA');
            $table->integer('duracion');
            $table->integer('precio');
            $table->integer('descuento');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa_suscripcions');
    }
};

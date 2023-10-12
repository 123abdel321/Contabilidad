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
        Schema::create('empresa_componentes_suscripcions', function (Blueprint $table) {
            $table->id();
            $table->integer('id_empresa');
            $table->integer('id_empresa_suscripcion');
            $table->integer('id_componente');
            $table->integer('cantidad')->nullable();
            $table->integer('precio');
            $table->date('fecha_siguiente_cobro')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa_componentes_suscripcions');
    }
};

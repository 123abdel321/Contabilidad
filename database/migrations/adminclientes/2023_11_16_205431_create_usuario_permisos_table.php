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
        Schema::create('usuario_permisos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_user')->nullable();
            $table->string('id_empresa')->nullable();
            $table->string('ids_permission')->nullable();
            $table->string('ids_bodegas_responsable')->nullable();
            $table->string('ids_resolucion_responsable')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_permisos');
    }
};

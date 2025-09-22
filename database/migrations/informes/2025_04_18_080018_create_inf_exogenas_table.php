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
        Schema::create('inf_exogenas', function (Blueprint $table) {
            $table->id();
            $table->integer('id_empresa');
            $table->integer('year');
            $table->integer('id_exogena_formato');
            $table->integer('id_exogena_formato_concepto')->nullable();
            $table->integer('id_nit')->nullable();
            $table->integer('exporta_excel')->nullable()->comment('1: Exportando, 2: Exportado');
            $table->integer('estado')->nullable()->comment('0: Error; 1: Proceso; 2: Generado;');
            $table->string('archivo_excel')->nullable();
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
        Schema::dropIfExists('inf_exogenas');
    }
};

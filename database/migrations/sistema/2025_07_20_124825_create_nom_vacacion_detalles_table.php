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
        Schema::create('nom_vacacion_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_vacaciones')->comment('Contrato al que pertenece para hacer seguimiento de acumulados');
            $table->char('concepto')->comment('texto descriptivo del concepto "codigo - nombre"');
            $table->date('fecha');
            $table->decimal('valor', 12);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nom_vacacion_detalles');
    }
};

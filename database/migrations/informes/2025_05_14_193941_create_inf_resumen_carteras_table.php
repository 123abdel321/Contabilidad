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
        Schema::create('inf_resumen_carteras', function (Blueprint $table) {
            $table->id();
            $table->integer('id_empresa')->nullable();
            $table->date('fecha_hasta')->nullable();
            $table->integer('dias_mora')->nullable();
            $table->json('cuentas')->nullable();
            $table->integer('estado')->nullable()->comment('0: Error; 1: Proceso; 2: Generado;');
            $table->integer('exporte')->nullable()->comment('1: Exportando, 2: Exportado');
            $table->string('url_excel')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inf_resumen_carteras');
    }
};

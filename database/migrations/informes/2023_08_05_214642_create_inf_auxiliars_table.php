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
        Schema::connection('informes')->create('inf_auxiliars', function (Blueprint $table) {
            $table->id();
            $table->integer('id_empresa');
            $table->integer('id_nit')->nullable();
            $table->integer('id_cuenta')->nullable();
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
            $table->integer('exporta_excel')->nullable()->comment('1: Exportando, 2: Exportado');
            $table->string('archivo_excel')->nullable();
            $table->integer('estado')->nullable()->comment('0: Error; 1: Proceso; 2: Generado;');
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
        Schema::connection('informes')->dropIfExists('inf_auxiliars');
    }
};

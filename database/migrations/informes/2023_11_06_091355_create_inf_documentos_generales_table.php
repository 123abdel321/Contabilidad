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
        Schema::create('inf_documentos_generales', function (Blueprint $table) {
            $table->id();
            $table->integer('id_empresa');
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
            $table->integer('id_nit')->nullable();
            $table->integer('id_cuenta')->nullable();
            $table->integer('id_usuario')->nullable();
            $table->integer('id_comprobante')->nullable();
            $table->integer('id_centro_costos')->nullable();
            $table->integer('documento_referencia')->nullable();
            $table->integer('consecutivo')->nullable();
            $table->integer('concepto')->nullable();
            $table->string('agrupar')->nullable();
            $table->integer('agrupado')->nullable()->comment('0: Normal, 1: Niveles');;
            $table->integer('exporta_excel')->nullable()->comment('1: Exportando, 2: Exportado');
            $table->string('archivo_excel')->nullable();
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
        Schema::dropIfExists('inf_documentos_generales');
    }
};

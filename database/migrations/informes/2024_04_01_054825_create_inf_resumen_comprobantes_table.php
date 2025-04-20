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
        Schema::create('inf_resumen_comprobantes', function (Blueprint $table) {
            $table->id();
            $table->integer('id_empresa');
            $table->date('fecha_desde')->nullable();
            $table->date('fecha_hasta')->nullable();
            $table->integer('id_comprobante')->nullable();
            $table->integer('id_cuenta')->nullable();
            $table->integer('id_nit')->nullable();
            $table->integer('agrupado')->nullable();
            $table->integer('exporta_excel')->nullable()->comment('1: Exportando, 2: Exportado');
            $table->string('archivo_excel')->nullable();
            $table->integer('detalle')->default(0)->nullable()->comment('0: No, 1: Si');
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
        Schema::dropIfExists('inf_resumen_comprobantes');
    }
};

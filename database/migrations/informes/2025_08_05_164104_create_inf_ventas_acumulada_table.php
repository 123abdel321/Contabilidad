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
        Schema::create('inf_ventas_acumuladas', function (Blueprint $table) {
            $table->id();
            $table->integer('id_empresa');
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
            $table->integer('id_tipo_informe')->nullable();
            $table->integer('id_nit')->nullable();
            $table->integer('id_resolucion')->nullable();
            $table->integer('id_bodega')->nullable();
            $table->integer('id_producto')->nullable();
            $table->integer('id_usuario')->nullable();
            $table->string('documento_referencia')->nullable();
            $table->integer('id_forma_pago')->nullable();
            $table->integer('detallar_venta')->nullable()->comment('0: Normal, 1: Niveles');
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
        Schema::dropIfExists('inf_ventas_acumuladas');
    }
};

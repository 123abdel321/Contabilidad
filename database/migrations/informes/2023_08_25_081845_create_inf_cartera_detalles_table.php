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
        Schema::connection('informes')->create('inf_cartera_detalles', function (Blueprint $table) {  
            $table->id();
            $table->integer('id_cartera');
            $table->integer('id_nit')->nullable();
            $table->integer('id_cuenta')->nullable();
            $table->integer('id_centro_costos')->nullable();
            $table->integer('id_comprobante')->nullable();
            $table->integer('nivel')->nullable();
            $table->string('cuenta', 10)->nullable();
            $table->string('nombre_cuenta', 100)->nullable();
            $table->string('numero_documento', 50)->nullable();
            $table->string('nombre_nit', 100)->nullable();
            $table->string('razon_social', 100)->nullable();
            $table->string('documento_referencia', 50)->nullable();
            $table->decimal('saldo_anterior', 15)->nullable();
            $table->decimal('total_facturas', 15)->nullable();
            $table->decimal('total_abono', 15)->nullable();
            $table->decimal('saldo', 15)->nullable();            
            $table->string('codigo_comprobante', 50)->nullable();
            $table->string('nombre_comprobante', 100)->nullable();
            $table->string('codigo_cecos', 50)->nullable();
            $table->string('nombre_cecos', 100)->nullable();
            $table->string('fecha_manual', 200)->nullable();
            $table->string('dias_cumplidos', 10)->nullable();
            $table->string('concepto', 200)->nullable();
            $table->string('consecutivo', 50)->nullable();
            $table->dateTime('fecha_creacion')->nullable();
            $table->dateTime('fecha_edicion')->nullable();
            $table->string('detalle', 10)->nullable();
            $table->string('detalle_group', 10)->nullable();
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
        Schema::dropIfExists('inf_cartera_detalles');
    }
};

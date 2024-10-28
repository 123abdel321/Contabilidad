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
        Schema::create('con_gasto_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_gasto');
            $table->integer('id_concepto_gastos');
            $table->integer('id_cuenta_gasto')->nullable();
            $table->integer('id_cuenta_iva')->nullable();
            $table->integer('id_cuenta_retencion')->nullable();
            $table->integer('id_cuenta_retencion_declarante')->nullable();
            $table->string('observacion', 200)->nullable();
            $table->decimal('subtotal', 15)->default(0);
            $table->decimal('aiu_porcentaje', 5)->nullable()->default(0);
            $table->decimal('aiu_valor', 15)->nullable()->default(0);
            $table->decimal('descuento_porcentaje', 5)->default(0);
            $table->decimal('rete_fuente_porcentaje', 5)->default(0);
            $table->decimal('descuento_valor', 15)->default(0);
            $table->decimal('rete_fuente_valor', 15)->default(0);
            $table->decimal('iva_porcentaje', 5)->default(0);
            $table->decimal('iva_valor', 15)->default(0);
            $table->decimal('total', 15)->default(0);
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
        Schema::dropIfExists('con_gasto_detalles');
    }
};

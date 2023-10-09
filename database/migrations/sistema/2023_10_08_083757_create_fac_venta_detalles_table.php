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
        Schema::create('fac_venta_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_venta');
            $table->integer('id_cuenta_venta')->nullable();
            $table->integer('id_cuenta_venta_retencion')->nullable();
            $table->integer('id_cuenta_venta_iva')->nullable();
            $table->integer('id_cuenta_venta_descuento')->nullable();
            $table->string('descripcion', 200)->nullable();
            $table->decimal('cantidad', 15)->default(0);
            $table->decimal('costo', 15)->default(0);
            $table->decimal('subtotal', 15)->default(0);
            $table->decimal('descuento_porcentaje', 5)->default(0);
            $table->decimal('rete_fuente_porcentaje', 5)->default(0);
            $table->decimal('descuento_valor', 15)->default(0);
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
        Schema::dropIfExists('fac_venta_detalles');
    }
};

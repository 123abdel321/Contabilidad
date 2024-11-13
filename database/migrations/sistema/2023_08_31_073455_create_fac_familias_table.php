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
        Schema::create('fac_familias', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 60)->unique();
            $table->string('nombre', 200);
            $table->boolean('inventario')->default(0)->nullable();
            $table->integer('id_cuenta_venta')->nullable();
            $table->integer('id_cuenta_venta_retencion')->nullable();
            $table->integer('id_cuenta_venta_devolucion')->nullable();
            $table->integer('id_cuenta_venta_iva')->nullable();
            $table->integer('id_cuenta_venta_descuento')->nullable();
            $table->integer('id_cuenta_venta_devolucion_iva')->nullable();
            $table->integer('id_cuenta_venta_impuestos')->nullable();
            $table->integer('id_cuenta_compra')->nullable();
            $table->integer('id_cuenta_compra_retencion')->nullable();
            $table->integer('id_cuenta_compra_devolucion')->nullable();
            $table->integer('id_cuenta_compra_iva')->nullable();
            $table->integer('id_cuenta_compra_descuento')->nullable();
            $table->integer('id_cuenta_compra_devolucion_iva')->nullable();
            $table->integer('id_cuenta_compra_impuestos')->nullable();
            $table->integer('id_cuenta_inventario')->nullable();
            $table->integer('id_cuenta_costos')->nullable();
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
        Schema::dropIfExists('fac_familias');
    }
};

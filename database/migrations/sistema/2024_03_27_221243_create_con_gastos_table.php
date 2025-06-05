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
        Schema::create('con_gastos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_proveedor');
            $table->integer('id_concepto')->nullable();
            $table->integer('id_comprobante')->nullable();
            $table->integer('id_centro_costos')->nullable();
            $table->integer('id_cuenta_rete_fuente')->nullable();
            $table->dateTime('fecha_manual')->nullable();
            $table->string('consecutivo', 20);
            $table->string('documento_referencia', 50);
            $table->decimal('subtotal', 15);
            $table->decimal('total_iva', 15);
            $table->decimal('total_descuento', 15);
            $table->decimal('total_rete_fuente', 15);
            $table->decimal('total_rete_ica', 15);
            $table->decimal('porcentaje_rete_fuente', 15);
            $table->decimal('total_gasto', 15);
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
        Schema::dropIfExists('con_gastos');
    }
};

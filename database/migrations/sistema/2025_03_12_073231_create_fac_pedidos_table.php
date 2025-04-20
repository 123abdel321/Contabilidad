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
        Schema::create('fac_pedidos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_cliente');
            $table->integer('id_bodega')->nullable();
            $table->integer('id_centro_costos')->nullable();
            $table->integer('id_ubicacion')->nullable();
            $table->integer('id_vendedor')->nullable();
            $table->integer('id_venta')->nullable();
            $table->string('consecutivo', 20);
            $table->decimal('subtotal', 15);
            $table->decimal('total_iva', 15);
            $table->decimal('total_descuento', 15);
            $table->decimal('total_rete_fuente', 15);
            $table->decimal('total_cambio', 15)->default(0);
            $table->decimal('porcentaje_rete_fuente', 15);
            $table->decimal('total_factura', 15);
            $table->integer('estado')->default(1)->comment('0 - Anulado/Eliminado, 1 - Guardado, 2 - Facturado');
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
        Schema::dropIfExists('fac_pedidos');
    }
};

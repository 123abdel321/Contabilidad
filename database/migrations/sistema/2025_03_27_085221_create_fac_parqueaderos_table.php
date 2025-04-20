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
        Schema::create('fac_parqueaderos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_cliente');
            $table->integer('id_bodega')->nullable();
            $table->integer('id_centro_costos')->nullable();
            $table->integer('id_vendedor')->nullable();
            $table->integer('id_venta')->nullable();
            $table->integer('id_producto')->nullable();
            $table->string('placa', 20);
            $table->string('tipo', 20)->default(1)->comment('1 - Carro, 2 - Moto, 3 - Otros');
            $table->string('fecha_inicio', 30)->nullable();
            $table->string('fecha_fin', 30)->nullable();
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
        Schema::dropIfExists('parqueaderos');
    }
};

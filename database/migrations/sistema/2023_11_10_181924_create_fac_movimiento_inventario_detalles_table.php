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
        Schema::create('fac_movimiento_inventario_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_movimiento_inventario');
            $table->integer('id_producto');
            $table->decimal('cantidad', 15)->default(0);
            $table->decimal('costo', 15)->default(0);
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
        Schema::dropIfExists('fac_movimiento_inventario_detalles');
    }
};

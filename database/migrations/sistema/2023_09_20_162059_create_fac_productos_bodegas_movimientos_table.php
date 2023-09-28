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
        Schema::create('fac_productos_bodegas_movimientos', function (Blueprint $table) {
            $table->id();
            $table->integer('relation_id');
            $table->integer('relation_type');
            $table->integer('id_producto');
            $table->integer('id_bodega');
            $table->decimal('cantidad_anterior', 15)->default(0);
            $table->decimal('cantidad', 15)->default(0);
            $table->integer('tipo_tranferencia')->default(0)->comment('0:Creacion, 1:Cargue, 2:Descargue, 3:traslado');
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
        Schema::dropIfExists('fac_productos_bodegas_movimientos');
    }
};

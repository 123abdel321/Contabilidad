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
        Schema::create('fac_movimiento_inventarios', function (Blueprint $table) {
            $table->id();
            $table->integer('id_nit')->nullable();
            $table->integer('id_cargue_descargues');
            $table->integer('id_comprobante')->nullable();
            $table->integer('id_centro_costos')->nullable();
            $table->integer('id_cuenta_debito')->nullable();
            $table->integer('id_cuenta_credito')->nullable();
            $table->integer('id_bodega_origen')->nullable();
            $table->integer('id_bodega_destino')->nullable();
            $table->integer('tipo')->default(0)->comment('0:Descargue, 1:Cargue, 2:Traslado');
            $table->decimal('cantidad', 15);
            $table->decimal('total_movimiento', 15);
            $table->string('consecutivo', 20);
            $table->date('fecha_manual')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->unique(['consecutivo', 'id_comprobante']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fac_movimiento_inventarios');
    }
};

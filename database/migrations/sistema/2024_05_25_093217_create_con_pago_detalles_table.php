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
        Schema::create('con_pago_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_pago')->nullable();
            $table->integer('id_cuenta')->nullable();
            $table->integer('id_nit')->nullable();
            $table->date('fecha_manual')->nullable();
            $table->string('documento_referencia', 20)->nullable();
            $table->string('consecutivo', 20)->nullable();
            $table->string('concepto', 600)->nullable();
            $table->decimal('total_factura', 15)->nullable();
            $table->decimal('total_abono', 15)->nullable();
            $table->decimal('total_saldo', 15)->nullable();
            $table->decimal('nuevo_saldo', 15)->nullable();
            $table->decimal('total_anticipo', 15)->nullable();
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
        Schema::dropIfExists('con_recibo_detalles');
    }
};

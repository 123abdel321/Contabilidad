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
        Schema::create('con_concepto_gastos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->nullable();
            $table->string('codigo', 100)->nullable();
            $table->integer('id_cuenta_gasto')->nullable();
            $table->integer('id_cuenta_iva')->nullable();
            $table->integer('id_cuenta_retencion')->nullable();
            $table->integer('id_cuenta_retencion_declarante')->nullable();
            $table->integer('id_cuenta_reteica')->nullable();
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
        Schema::dropIfExists('con_concepto_gastos');
    }
};

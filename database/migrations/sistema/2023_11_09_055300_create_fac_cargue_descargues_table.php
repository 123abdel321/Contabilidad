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
        Schema::create('fac_cargue_descargues', function (Blueprint $table) {
            $table->id();
            $table->integer('id_comprobante');
            $table->integer('id_nit')->nullable();
            $table->integer('id_cuenta_debito')->nullable();
            $table->integer('id_cuenta_credito')->nullable();
            $table->string('nombre', 200);
            $table->integer('tipo')->default(0)->comment('0:Descargue, 1:Cargue, 2:Traslado');
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
        Schema::dropIfExists('fac_cargue_descargues');
    }
};

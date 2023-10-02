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
        Schema::create('fac_formas_pagos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_cuenta')->index();
            $table->integer('id_tipo_formas_pago')->index();
            $table->string('nombre', 100);
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
        Schema::dropIfExists('fac_formas_pagos');
    }
};

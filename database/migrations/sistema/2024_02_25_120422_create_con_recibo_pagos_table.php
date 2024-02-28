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
        Schema::create('con_recibo_pagos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_recibo');
            $table->integer('id_forma_pago');
            $table->decimal('valor', 15);
            $table->decimal('saldo', 15);
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
        Schema::dropIfExists('con_recibo_pagos');
    }
};

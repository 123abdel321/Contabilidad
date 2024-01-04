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
        Schema::create('fac_vendedores', function (Blueprint $table) {
            $table->id();
            $table->integer('id_nit');
            $table->string('plazo_dias', 10)->default(0)->nullable();
            $table->string('porcentaje_comision', 10)->default(0)->nullable();
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
        Schema::dropIfExists('fac_vendedores');
    }
};

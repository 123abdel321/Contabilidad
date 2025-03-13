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
        Schema::create('fac_bodegas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 60)->unique();
            $table->string('nombre', 200);
            $table->string('ubicacion', 200)->nullable();
            $table->integer('id_centro_costos')->nullable();
            $table->integer('id_responsable')->nullable();
            $table->integer('id_cuenta_cartera')->nullable();
            $table->integer('consecutivo')->default(1)->nullable();
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
        Schema::dropIfExists('fac_bodegas');
    }
};

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
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->integer('id_nit')->nullable();
            $table->integer('id_ubicacion')->nullable();
            $table->dateTime('fecha_inicio', precision: 0)->nullable();
            $table->dateTime('fecha_fin', precision: 0)->nullable();
            $table->longText('observacion')->nullable();
            $table->integer('estado')->default(0);
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
        Schema::dropIfExists('reservas');
    }
};

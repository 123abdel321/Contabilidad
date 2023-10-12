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
        Schema::create('componentes_suscripcions', function (Blueprint $table) {
            $table->id();
            $table->integer('id_padre')->nullable();
            $table->string('nombre', 100)->default('');
            $table->boolean('tipo')->comment('0:bandera; 1:cantidad');
            $table->integer('rango_desde')->nullable()->comment('Si es tipo cantidad');
            $table->integer('rango_hasta')->nullable()->comment('Si es tipo cantidad');
            $table->integer('precio')->default(0);
            $table->boolean('automatico')->default(false);
            $table->boolean('descuento')->default(false);
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
        Schema::dropIfExists('componentes_suscripcions');
    }
};

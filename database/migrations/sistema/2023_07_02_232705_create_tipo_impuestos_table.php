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
        Schema::create('tipo_impuestos', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('codigo', 10);
            $table->string('nombre', 60);
            $table->boolean('es_retencion')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_impuestos');
    }
};

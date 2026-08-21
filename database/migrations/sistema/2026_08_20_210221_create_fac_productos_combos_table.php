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
        Schema::create('fac_productos_combos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_combo')->nullable();
            $table->integer('id_producto')->nullable();
            $table->decimal('costo', 15, 5)->default(0);
            $table->decimal('cantidad', 15, 5)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fac_productos_combos');
    }
};

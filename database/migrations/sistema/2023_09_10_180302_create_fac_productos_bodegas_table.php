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
        Schema::create('fac_productos_bodegas', function (Blueprint $table) {
            $table->id();
            $table->integer('id_producto')->nullable();
            $table->integer('id_bodega')->nullable();
            $table->decimal('cantidad', 15)->default(0);
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
        Schema::dropIfExists('fac_productos_bodegas');
    }
};

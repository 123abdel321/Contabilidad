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
        Schema::create('inf_estado_actuales', function (Blueprint $table) {
            $table->id();
            $table->integer('id_empresa');
            $table->integer('id_comprobante')->nullable();
            $table->string('year', 20)->nullable();
            $table->string('month', 20)->nullable();
            $table->integer('detalle')->default(0)->nullable()->comment('0: No, 1: Si');
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
        Schema::dropIfExists('inf_estado_actuales');
    }
};

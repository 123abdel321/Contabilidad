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
        Schema::create('inf_estado_actual_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_estado_actual');
            $table->string('mes', 20)->nullable();
            $table->string('year', 20)->nullable();
            $table->string('comprobantes', 20)->nullable();
            $table->string('registros', 20)->nullable();
            $table->string('errores', 20)->nullable();
            $table->string('documentos', 20)->nullable();
            $table->string('total', 20)->nullable();
            $table->decimal('debito', 15)->nullable();
            $table->decimal('credito', 15)->nullable();
            $table->decimal('diferencia', 15)->nullable();
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
        Schema::dropIfExists('inf_estado_actual_detalles');
    }
};

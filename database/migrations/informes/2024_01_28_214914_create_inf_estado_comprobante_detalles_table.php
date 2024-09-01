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
        Schema::create('inf_estado_comprobante_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_estado_comprobante');
            $table->string('codigo_comprobante')->nullable();
            $table->string('nombre_comprobante')->nullable();
            $table->string('year', 15)->nullable();
            $table->string('documentos', 15)->nullable();
            $table->string('registros', 15)->nullable();
            $table->decimal('debito', 15)->nullable();
            $table->decimal('credito', 15)->nullable();
            $table->decimal('diferencia', 15)->nullable();
            $table->string('nombre_tipo_comprobante')->nullable();
            $table->string('errores', 15)->nullable();
            $table->tinyInteger('total')->default(0);
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
        Schema::dropIfExists('inf_estado_comprobante_detalles');
    }
};

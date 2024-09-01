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
        Schema::create('documentos_imports', function (Blueprint $table) {
            $table->id();
            $table->string('documento_nit', 100)->nullable();
            $table->string('nombre_nit', 200)->nullable();
            $table->string('cuenta_contable', 100)->nullable();
            $table->string('nombre_cuenta', 200)->nullable();
            $table->string('codigo_cecos', 100)->nullable();
            $table->string('nombre_cecos', 200)->nullable();
            $table->string('codigo_comprobante', 100)->nullable();
            $table->string('nombre_comprobante', 200)->nullable();
            $table->string('consecutivo', 100)->nullable();
            $table->string('documento_referencia', 100)->nullable();
            $table->date('fecha_manual')->nullable();
            $table->decimal('debito', 15)->nullable();
            $table->decimal('credito', 15)->nullable();
            $table->string('concepto', 100)->nullable();
            $table->string('total_errores', 100)->nullable();
            $table->longText('errores')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_imports');
    }
};

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
        Schema::connection('informes')->create('inf_balance_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_balance');
            $table->integer('id_cuenta')->nullable();
            $table->string('cuenta', 10)->nullable();
            $table->string('nombre_cuenta', 100)->nullable();
            $table->integer('auxiliar')->nullable();
            $table->decimal('saldo_anterior', 15);
            $table->decimal('debito', 15);
            $table->decimal('credito', 15);
            $table->decimal('saldo_final', 15);
            $table->integer('documentos_totales')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('informes')->dropIfExists('inf_balance_detalles');
    }
};

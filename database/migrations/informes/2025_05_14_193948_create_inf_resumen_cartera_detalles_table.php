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
        Schema::create('inf_resumen_cartera_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_resumen_cartera')->nullable();
            $table->integer('id_nit')->nullable();
            $table->string('nombre_nit', 100)->nullable();
            $table->string('numero_documento', 50)->nullable();
            $table->decimal('saldo_final', 15)->nullable();
            $table->integer('dias_mora')->nullable();
            $table->string('ubicacion', 50)->nullable();
            $table->decimal('cuenta_1', 15)->nullable();
            $table->decimal('cuenta_2', 15)->nullable();
            $table->decimal('cuenta_3', 15)->nullable();
            $table->decimal('cuenta_4', 15)->nullable();
            $table->decimal('cuenta_5', 15)->nullable();
            $table->decimal('cuenta_6', 15)->nullable();
            $table->decimal('cuenta_7', 15)->nullable();
            $table->decimal('cuenta_8', 15)->nullable();
            $table->decimal('cuenta_9', 15)->nullable();
            $table->decimal('cuenta_10', 15)->nullable();
            $table->decimal('cuenta_11', 15)->nullable();
            $table->decimal('cuenta_12', 15)->nullable();
            $table->decimal('cuenta_13', 15)->nullable();
            $table->decimal('cuenta_14', 15)->nullable();
            $table->decimal('cuenta_15', 15)->nullable();
            $table->decimal('cuenta_16', 15)->nullable();
            $table->decimal('cuenta_17', 15)->nullable();
            $table->decimal('cuenta_18', 15)->nullable();
            $table->decimal('cuenta_19', 15)->nullable();
            $table->decimal('cuenta_20', 15)->nullable();
            $table->decimal('cuenta_21', 15)->nullable();
            $table->decimal('cuenta_22', 15)->nullable();
            $table->decimal('cuenta_23', 15)->nullable();
            $table->decimal('cuenta_24', 15)->nullable();
            $table->decimal('cuenta_25', 15)->nullable();
            $table->decimal('cuenta_26', 15)->nullable();
            $table->decimal('cuenta_27', 15)->nullable();
            $table->decimal('cuenta_28', 15)->nullable();
            $table->decimal('cuenta_29', 15)->nullable();
            $table->decimal('cuenta_30', 15)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inf_resumen_cartera_detalles');
    }
};

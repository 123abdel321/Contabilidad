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
        Schema::connection('informes')->create('inf_resultados', function (Blueprint $table) {
            $table->id();
            $table->integer('id_empresa');
            $table->integer('id_cuenta')->nullable();
            $table->integer('id_cecos')->nullable();
            $table->integer('id_nit')->nullable();
            $table->string('cuenta_desde')->nullable();
            $table->string('cuenta_hasta')->nullable();
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
            $table->integer('tipo')->nullable();
            $table->integer('nivel')->nullable();
            $table->integer('exporta_excel')->nullable()->comment('1: Exportando, 2: Exportado');
            $table->string('archivo_excel')->nullable();
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
        Schema::connection('informes')->dropIfExists('inf_balances');
    }
};

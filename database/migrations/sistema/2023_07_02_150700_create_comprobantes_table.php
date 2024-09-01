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
        Schema::create('comprobantes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('codigo', 10)->unique();
            $table->string('nombre', 200);
            $table->integer('tipo_comprobante')->comment('0:ingresos, 1:egresos, 2:compras, 3:ventas, 4:otros, 5:gastos, 6:gastos')->default('0');
            $table->integer('tipo_consecutivo')->comment('0:normal, 1:mensual')->default('0');
            $table->string('consecutivo_siguiente', 11)->default('1');
            $table->boolean('imprimir_en_capturas')->nullable();
            $table->boolean('tipo_impresion')->nullable()->comment('0: POS, 1: Media Carta, 2: Carta');
            $table->boolean('tesoreria')->nullable();
            $table->integer('maestra_padre')->nullable();
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
        Schema::dropIfExists('comprobantes');
    }
};

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
        Schema::create('nom_configuracion_provisiones', function (Blueprint $table) {
            $table->id();
            $table->integer('tipo')->nullable()->comment(
                'Tipo de provisión: 0 = Parafiscal (SENA, ICBF, Cajas de compensación), ' .
                '1 = Seguridad Social (salud, pensión, ARL), 2 = Prestaciones Sociales (cesantías, primas, etc.)'
            );
            $table->string('nombre', 50)->nullable()->comment('Nombre o descripción de la provisión (ej. Prima, Cesantías, ARL, etc.)');
            $table->decimal('porcentaje', 7, 4)->default(0)->comment('Porcentaje que se aplica a la base salarial para esta provisión');
            $table->integer('id_cuenta_administrativos')->nullable()->comment('ID de la cuenta contable para empleados administrativos');
            $table->integer('id_cuenta_operativos')->nullable()->comment('ID de la cuenta contable para empleados operativos');
            $table->integer('id_cuenta_ventas')->nullable()->comment('ID de la cuenta contable para empleados del área de ventas');
            $table->integer('id_cuenta_otros')->nullable()->comment('ID de la cuenta contable para otras áreas o categorías');
            $table->integer('id_cuenta_por_pagar')->nullable()->comment('ID de la cuenta contable para provisiones por pagar');
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
        Schema::dropIfExists('nom_configuracion_provisiones');
    }
};

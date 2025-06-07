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
        Schema::create('nom_conceptos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_concepto')->comment('Nombre del concepto según la Nómina Electrónica');
            $table->char('codigo', 10)->comment('Código identificador del concepto');
            $table->char('nombre', 100)->comment('Nombre descriptivo del concepto');
            $table->integer('id_cuenta_administrativos')->nullable()->comment('Cuenta contable para el área administrativa');
            $table->integer('id_cuenta_operativos')->nullable()->comment('Cuenta contable para el área operativa');
            $table->integer('id_cuenta_ventas')->nullable()->comment('Cuenta contable para el área de ventas');
            $table->integer('id_cuenta_otros')->nullable()->comment('Cuenta contable para otras áreas');
            $table->decimal('porcentaje', 7, 4)->nullable()->comment('Porcentaje aplicado sobre el concepto base');
            $table->integer('id_concepto_porcentaje')->nullable()->comment('Concepto base sobre el cual se calcula el porcentaje');
            $table->integer('unidad')->default(0)->comment('Unidad de medida: 0 = horas, 1 = días, 2 = valor');
            $table->decimal('valor_mensual', 12)->nullable()->comment('Valor mensual base, que se divide entre horas o días según la unidad');
            $table->boolean('concepto_fijo')->default(false)->comment('Indica si el concepto es fijo (true) o manual (false)');
            $table->boolean('base_retencion')->default(false)->comment('Aplica para la base de retención en la fuente');
            $table->boolean('base_sena')->default(false)->comment('Aplica para la base de aportes al SENA');
            $table->boolean('base_icbf')->default(false)->comment('Aplica para la base de aportes al ICBF');
            $table->boolean('base_caja_compensacion')->default(false)->comment('Aplica para la base de aportes a Caja de Compensación');
            $table->boolean('base_salud')->default(false)->comment('Aplica para la base de aportes a salud');
            $table->boolean('base_pension')->default(false)->comment('Aplica para la base de aportes a pensión');
            $table->boolean('base_arl')->default(false)->comment('Aplica para la base de aportes a riesgos laborales (ARL)');
            $table->boolean('base_vacacion')->default(false)->comment('Aplica para la base de liquidación de vacaciones');
            $table->boolean('base_prima')->default(false)->comment('Aplica para la base de liquidación de prima');
            $table->boolean('base_cesantia')->default(false)->comment('Aplica para la base de liquidación de cesantías');
            $table->boolean('base_interes_cesantia')->default(false)->comment('Aplica para la base de cálculo de intereses sobre cesantías');
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
        Schema::dropIfExists('nom_conceptos');
    }
};

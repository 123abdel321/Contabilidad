<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNomConceptosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_conceptos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tipo_concepto')->comment('Nombre usado para NE');
            $table->char('codigo', 10);
            $table->char('nombre', 100);
            $table->integer('id_cuenta_administrativos')->nullable();
            $table->integer('id_cuenta_operativos')->nullable();
            $table->integer('id_cuenta_ventas')->nullable();
            $table->integer('id_cuenta_otros')->nullable();
            $table->decimal('porcentaje', 7, 4)->nullable()->comment('Si es null, es porque no maneja porcentaje');
            $table->integer('id_concepto_porcentaje')->nullable()->comment('Concepto que se usa como base para sacar porcentaje');
            $table->integer('unidad')->default(0)->comment('0:horas, 1:dias, 2:valor');
            $table->decimal('valor_mensual', 12)->nullable()->comment('Base de la cual se va a dividir en horas o días');
            $table->boolean('concepto_fijo')->default(false)->comment('0:manual, 1:fijo');
            $table->boolean('base_retencion')->default(false);
            $table->boolean('base_sena')->default(false);
            $table->boolean('base_icbf')->default(false);
            $table->boolean('base_caja_compensacion')->default(false);
            $table->boolean('base_salud')->default(false);
            $table->boolean('base_pension')->default(false);
            $table->boolean('base_arl')->default(false);
            $table->boolean('base_vacacion')->default(false);
            $table->boolean('base_prima')->default(false);
            $table->boolean('base_cesantia')->default(false);
            $table->boolean('base_interes_cesantia')->default(false);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nom_conceptos');
    }
}

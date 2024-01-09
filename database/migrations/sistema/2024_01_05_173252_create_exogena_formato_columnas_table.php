<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConExogenaFormatoColumnasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exogena_formato_columnas', function (Blueprint $table) {
            $table->id();
            $table->integer('id_exogena_formato');
            $table->string('id_tipo_concepto_nomina')->nullable();
            $table->integer('id_columna_porcentaje_base')->nullable();
            $table->text('columna', 150);
            $table->boolean('acumulado');
            $table->boolean('saldo');
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('con_exogena_formato_columnas');
    }
}

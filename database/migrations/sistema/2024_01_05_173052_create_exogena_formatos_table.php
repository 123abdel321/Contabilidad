<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExogenaFormatosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exogena_formatos', function (Blueprint $table) {
            $table->id();
            $table->integer('formato');
            $table->boolean('tipo_documento');
            $table->boolean('numero_documento');
            $table->boolean('digito_verificacion');
            $table->boolean('primer_apellido');
            $table->boolean('segundo_apellido');
            $table->boolean('primer_nombre');
            $table->boolean('otros_nombres');
            $table->boolean('razon_social');
            $table->boolean('direccion');
            $table->boolean('departamento');
            $table->boolean('municipio');
            $table->boolean('pais');
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
        Schema::dropIfExists('con_exogena_formatos');
    }
}

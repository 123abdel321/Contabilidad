<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConCuentaExogenaFormatosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cuenta_exogena_formatos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_cuenta');
            $table->integer('id_exogena_formato');
            $table->integer('id_exogena_formato_concepto')->nullable();
            $table->integer('id_exogena_formato_columna');
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
        Schema::dropIfExists('cuenta_exogena_formatos');
    }
}

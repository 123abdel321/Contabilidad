<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBaseDatosProvisionadasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('base_datos_provisionadas', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('hash', 32)->unique('hash');
            $table->integer('estado')->comment('1-disponible, 2-ocupada');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('base_datos_provisionadas');
    }
}

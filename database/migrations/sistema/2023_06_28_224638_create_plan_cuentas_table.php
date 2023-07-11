<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanCuentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan_cuentas', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_padre')->nullable()->index('id_padre');
            $table->integer('id_tipo_cuenta')->nullable()->index('id_tipo_cuenta');
            $table->integer('id_impuesto')->nullable()->index('id_impuesto');
            $table->string('cuenta', 15);
            $table->string('nombre', 100);
            $table->boolean('auxiliar')->nullable();
            $table->boolean('pasarela')->default(0)->comment('check si es una cuenta usada para sacar extracto de pasarela');
            $table->boolean('exige_nit');
            $table->boolean('exige_documento_referencia');
            $table->boolean('exige_concepto');
            $table->boolean('exige_centro_costos');
			$table->boolean('naturaleza_cuenta')->nullable()->comment('0:debito, 1:credito');
			$table->boolean('cuenta_corriente')->default(0);
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
        Schema::dropIfExists('plan_cuentas');
    }
}

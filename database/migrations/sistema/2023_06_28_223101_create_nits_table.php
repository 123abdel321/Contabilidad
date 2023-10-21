<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nits', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_tipo_documento')->index();
            $table->integer('id_ciudad')->nullable()->index();
            $table->integer('id_departamento')->nullable()->index();
            $table->integer('id_pais')->nullable()->index();
            $table->integer('id_actividad_econo')->nullable()->index();
            $table->integer('id_banco')->nullable()->index();
            $table->longText('id_responsabilidades')->nullable();
            $table->string('numero_documento', 30)->unique();
            $table->string('digito_verificacion', 30)->nullable();
            $table->boolean('empleado')->nullable()->default(false);
            $table->boolean('tipo_contribuyente')->comment('1 - Persona jurídica; 2 - Persona natural');
            $table->string('primer_apellido', 60)->nullable();
            $table->string('segundo_apellido', 60)->nullable();
            $table->string('primer_nombre', 60)->nullable();
            $table->string('otros_nombres', 60)->nullable();
            $table->string('razon_social', 120)->nullable();
            $table->string('nombre_comercial', 120)->nullable()->default('');
            $table->string('direccion', 100)->nullable();
            $table->string('email', 250)->nullable();
            $table->string('email_recepcion_factura_electronica', 60)->nullable();
            $table->string('telefono_1', 30)->nullable();
            $table->string('telefono_2', 30)->nullable();
            $table->boolean('tipo_cuenta_banco')->default(false)->comment('0 - Ahorro, 1 - Corriente')->nullable();
            $table->string('cuenta_bancaria', 50)->nullable();
            $table->smallInteger('plazo')->nullable();
            $table->decimal('cupo', 15)->nullable();
            $table->decimal('descuento', 15)->nullable();
            $table->boolean('no_calcular_iva')->default(false);
            $table->boolean('inactivar')->nullable();
            $table->longText('observaciones')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * 
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nits');
    }
}

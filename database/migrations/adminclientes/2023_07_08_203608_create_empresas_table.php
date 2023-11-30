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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->integer('id_nit')->nullable();
            $table->integer('estado')->default(0)->comment('0: Inactivo temporalmente (acceso limitado solo a super), 1: Activo, 2: Periodo de gracia (Activo pero con mensajes de alerta de pago), 3: Moroso (Inactivo por falta de pago), 4: Retirado (Para hacer seguimiento), 5: en instalacion');
            $table->string('servidor', 100);
            $table->string('token_db', 200)->nullable();
            $table->string('nombre', 200)->nullable();
            $table->string('primer_apellido', 60)->nullable();
            $table->string('segundo_apellido', 60)->nullable();
            $table->string('primer_nombre', 60)->nullable();
            $table->string('otros_nombres', 60)->nullable();
            $table->boolean('tipo_contribuyente')->comment('1 - Persona jurídica; 2 - Persona natural');
            $table->string('razon_social', 120)->nullable();
            $table->string('nit', 100)->comment('Sin digito de verificacion')->nullable();
            $table->integer('dv')->comment('Digito de verificacion')->nullable();
            $table->string('codigos_responsabilidades', 200)->default('')->comment('Códigos de responsabilidades separados por comas por si se necesitan llevar el control de más aparte de la 07');
            // $table->integer('id_actividad_economica')->nullable();
            $table->string('notas_negociacion', 500)->nullable();
            // $table->string('pais', 100)->comment('info');
            // $table->string('departamento', 100)->comment('info');
            $table->string('logo', 300)->nullable();
            // $table->integer('id_rut');
            $table->date('fecha_retiro')->nullable();
            $table->date('fecha_ultimo_cierre')->nullable();
            // $table->string('ciudad', 100)->comment('info');
            $table->string('direccion', 200);
            // $table->string('geo_locations', 500)->comment('Array JSON {lat, lng}');
            $table->string('telefono', 50);
            $table->string('hash', 100);
            $table->integer('id_empresa_referido')->nullable()->comment('Id padre empresa');
            $table->integer('id_usuario_owner')->comment('Id del usuario dueño de esta empresa');
            // $table->integer('fecha_primer_contacto');
            // $table->integer('fecha_ultimo_contacto');
            // $table->mediumText('descripcion_actividad_empresa')->comment('Descripcion del funcionamiento de la empresa');
            // $table->string('notas_empresa', 500);
            // $table->integer('cli_medio_acercamiento')->comment('Medio por el cual se contacto (tel, mail, publicidad, etc)');
            // $table->string('cotizacion_valor', 50)->comment('Puede estar vacio si esta pendiente de definir');
            // $table->integer('cotizacion_cantidad_empleados');
            // $table->integer('cotizacion_cantidad_facturas_mensuales');
            // $table->integer('cotizacion_puntos_venta');
            // $table->integer('calificacion')->default(1)->comment('0:malo,1:normal,2:bueno');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};

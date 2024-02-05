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
        Schema::create('nits_imports', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_documento', 100)->nullable();
            $table->string('numero_documento', 100)->nullable();
            $table->string('digito_verificacion', 100)->nullable();
            $table->string('primer_nombre', 100)->nullable();
            $table->string('otros_nombres', 100)->nullable();
            $table->string('primer_apellido', 100)->nullable();
            $table->string('segundo_apellido', 100)->nullable();
            $table->string('razon_social', 100)->nullable();
            $table->string('direccion', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('telefono_1', 100)->nullable();
            $table->string('plazo', 100)->nullable();
            $table->string('cupo', 100)->nullable();
            $table->string('observaciones', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nits_imports');
    }
};

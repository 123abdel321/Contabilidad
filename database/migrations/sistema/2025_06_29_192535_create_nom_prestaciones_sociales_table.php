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
        Schema::create('nom_prestaciones_sociales', function (Blueprint $table) {
            $table->id();
            $table->integer('id_empleado');
            $table->date('fecha');
            $table->string('concepto', 50);
            $table->decimal('base', 12)->default(0);
            $table->decimal('porcentaje', 7, 4)->default(0);
            $table->decimal('provision', 12)->default(0);
            $table->integer('id_administradora')->nullable();
            $table->integer('id_cuenta_debito')->nullable();
            $table->integer('id_cuenta_credito')->nullable();
            $table->tinyInteger('editado')->default(0);
            $table->integer('updated_by')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nom_provision_prestaciones_sociales');
    }
};

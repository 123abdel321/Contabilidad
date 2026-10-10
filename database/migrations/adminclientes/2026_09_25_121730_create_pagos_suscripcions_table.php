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
        Schema::create('pagos_suscripciones', function (Blueprint $table) {
            $table->id();
            $table->integer('id_empresa')->nullable();
            $table->string('id_usuario')->nullable();
            $table->string('token_db')->nullable();
            $table->string('detalle_json')->nullable();
            $table->decimal('valor', 15);
            $table->integer('estado')->default(1)->comment('0:Rechazado, 1:Aprobado, 2:Pendiente')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_suscripciones');
    }
};

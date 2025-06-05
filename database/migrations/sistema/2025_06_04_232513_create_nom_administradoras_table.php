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
        Schema::create('nom_administradoras', function (Blueprint $table) {
            $table->id();
            $table->integer('tipo')->comment('0: EPS, 1: AFP, 2: ARL, 3: CCF');
            $table->string('codigo', 6);
            $table->integer('id_nit');
            $table->string('descripcion')->nullable();
            $table->integer('liquidada')->default(0)->comment('0: activa, 1: liquidada');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nom_administradoras');
    }
};

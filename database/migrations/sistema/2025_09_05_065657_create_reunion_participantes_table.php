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
        Schema::create('reunion_participantes', function (Blueprint $table) {
            $table->id();
            $table->integer('id_reunion');
            $table->integer('id_usuario');
            $table->boolean('asistio')->default(false);
            $table->text('comentarios')->nullable();
            $table->timestamps();
            
            $table->index('id_reunion');
            $table->index('id_usuario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reunion_participantes');
    }
};

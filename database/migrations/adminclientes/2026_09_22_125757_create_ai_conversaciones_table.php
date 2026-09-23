<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_conversaciones', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id')->unique();
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_empresa');
            $table->string('flujo')->nullable();
            $table->json('contexto')->nullable();
            $table->json('borrador')->nullable();
            $table->json('candidatos')->nullable();
            $table->json('historial')->nullable();
            $table->json('resultado')->nullable();
            $table->boolean('cerrada')->default(false);
            $table->timestamps();

            $table->index(['id_user', 'id_empresa']);
            $table->index('cerrada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_conversaciones');
    }
};
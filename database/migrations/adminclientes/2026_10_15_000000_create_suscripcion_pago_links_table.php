<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suscripcion_pago_links', function (Blueprint $table) {
            $table->id();

            // 🔑 Relación con la empresa (usa la misma conexión que tus otras tablas)
            $table->unsignedBigInteger('id_empresa')->unique();

            // 🔑 Token único y opaco (no es el id de la empresa)
            $table->string('token', 64)->unique();

            // 🔑 Auditoría
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('usado_at')->nullable();      // última vez que se pagó
            $table->unsignedInteger('veces_usado')->default(0);

            // 🔑 Por si luego quieres revocar / expirar
            $table->boolean('activo')->default(true);
            $table->timestamp('expira_at')->nullable();

            $table->timestamps();

            $table->index('id_empresa');
            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suscripcion_pago_links');
    }
};
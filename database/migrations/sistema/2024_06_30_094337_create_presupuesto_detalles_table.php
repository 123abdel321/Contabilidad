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
        Schema::create('presupuesto_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_presupuesto')->nullable();
            $table->integer('id_padre')->nullable();
            $table->integer('es_grupo')->nullable();
            $table->string('cuenta', 60)->nullable();
            $table->string('nombre', 200)->nullable();
            $table->decimal('presupuesto', 15)->default(0);
            $table->decimal('diferencia', 15)->default(0);
            $table->decimal('enero', 15)->default(0);
            $table->decimal('febrero', 15)->default(0);
            $table->decimal('marzo', 15)->default(0);
            $table->decimal('abril', 15)->default(0);
            $table->decimal('mayo', 15)->default(0);
            $table->decimal('junio', 15)->default(0);
            $table->decimal('julio', 15)->default(0);
            $table->decimal('agosto', 15)->default(0);
            $table->decimal('septiembre', 15)->default(0);
            $table->decimal('octubre', 15)->default(0);
            $table->decimal('noviembre', 15)->default(0);
            $table->decimal('diciembre', 15)->default(0);
            $table->integer('auxiliar')->default(0);
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
        Schema::dropIfExists('presupuesto_detalles');
    }
};

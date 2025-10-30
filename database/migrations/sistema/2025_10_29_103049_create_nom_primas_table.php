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
        Schema::create('nom_primas', function (Blueprint $table) {
            $table->id();
            $table->integer('id_empleado');
            $table->date('fecha_inicio');
			$table->date('fecha_fin');
            $table->decimal('base', 12, 2)->default(0);
            $table->integer('dias');
            $table->decimal('promedio', 12, 2)->default(0);
            $table->decimal('valor', 12, 2)->default(0);
            $table->decimal('dias_promedio', 12, 2)->default(0);
            $table->boolean('editado')->default(0);
            $table->integer('id_periodo_pago');
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
        Schema::dropIfExists('nom_primas');
    }
};

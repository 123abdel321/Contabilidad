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
        Schema::create('fac_productos_precios_imports', function (Blueprint $table) {
            $table->id();
            $table->integer('id_producto')->nullable();
            $table->string('row', 60)->unique();
            $table->string('codigo', 60)->unique();
            $table->string('nombre', 200);
            $table->decimal('precio', 15)->default(0);
            $table->decimal('precio_inicial', 15)->default(0);
            $table->decimal('precio_promedio', 15)->default(0);
            $table->mediumText('observacion')->nullable();
            $table->integer('estado')->default(0)->comment('0:Con errores, 1:Producto igual, 2:Producto para actualizar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fac_productos_precios_imports');
    }
};

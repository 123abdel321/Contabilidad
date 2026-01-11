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
        Schema::create('fac_productos_imports', function (Blueprint $table) {
            $table->id();
            $table->integer('id_producto')->nullable();
            $table->string('row', 60)->nullable();
            $table->string('codigo', 60)->nullable();
            $table->string('nombre', 200);
            $table->integer('id_familia')->nullable();
            $table->integer('id_bodega')->nullable();
            $table->decimal('costo', 15, 5)->default(0);
            $table->decimal('venta', 15, 5)->default(0);
            $table->decimal('existencias', 15, 5)->default(0);
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
        Schema::dropIfExists('fac_productos_imports');
    }
};

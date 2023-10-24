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
        Schema::create('fac_productos', function (Blueprint $table) {
            $table->id();
            $table->string('imagen', 300)->nullable();
            $table->integer('id_padre')->nullable();
            $table->integer('id_familia')->nullable();
            $table->integer('tipo_producto')->default(0)->comment('0:Producto, 1:Servicio, 2:Combo');
            $table->string('codigo', 60)->unique();
            $table->string('nombre', 200);
            $table->decimal('precio', 15)->default(0);
            $table->decimal('precio_inicial', 15)->default(0);
            $table->decimal('precio_minimo', 15)->default(0);
            $table->decimal('porcentaje_utilidad', 20, 20)->default(0);
            $table->decimal('valor_utilidad', 15)->default(0);
            $table->integer('variante')->default(0)->comment('0:no, 1:si');
            $table->boolean('estado')->default(true);
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
        Schema::dropIfExists('fac_productos');
    }
};

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
        Schema::create('fac_resoluciones', function (Blueprint $table) {
            $table->id();
            $table->integer('id_comprobante')->nullable();
            $table->string('nombre', 100);
            $table->string('prefijo', 25)->nullable();
            $table->integer('consecutivo');
            $table->string('numero_resolucion', 250);
            $table->integer('tipo_impresion')->comment('0: POS, 1: Media Carta, 2: Carta, 3: Personalizada');
            $table->integer('tipo_resolucion')->default(0)->comment('0:Computador, 1: POS, 2: Facturacion electronica, 3: Contingencia, 4: Nota debito, 5: Nota credito, 6: Documento Equivalente/Soporte');
            $table->date('fecha');
            $table->integer('vigencia');
            $table->integer('consecutivo_desde');
            $table->integer('consecutivo_hasta');
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fac_resoluciones');
    }
};

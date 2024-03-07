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
        Schema::create('con_recibos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_nit');
            $table->integer('id_comprobante');
            $table->integer('id_vendedor')->nullable();
            $table->date('fecha_manual')->nullable();
            $table->string('consecutivo', 20);
            $table->decimal('total_abono', 15);
            $table->decimal('total_anticipo', 15);
            $table->mediumText('observacion')->nullable();
            $table->integer('estado')->default(1)->comment('0:Rechazado, 1:Aprobado, 2:Pendiente')->nullable();
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
        Schema::dropIfExists('con_recibos');
    }
};

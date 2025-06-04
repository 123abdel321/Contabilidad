<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fac_documentos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_comprobante')->index();
            $table->integer('id_nit')->nullable();
            $table->date('fecha_manual');
            $table->time('hora_manual')->default(Carbon::now()->toTimeString());
            $table->string('token_factura', 255)->nullable();
            $table->string('consecutivo', 20);
            $table->decimal('debito', 15);
            $table->decimal('credito', 15);
            $table->decimal('saldo_final', 15);
            $table->boolean('anulado')->default(false);
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
        Schema::dropIfExists('fac_documentos');
    }
};

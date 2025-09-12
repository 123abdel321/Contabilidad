<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class CreateDocumentosGeneralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documentos_generals', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_nit')->nullable()->index();
            $table->integer('id_cuenta')->nullable()->index();
            $table->integer('id_comprobante')->nullable()->index();
            $table->integer('id_centro_costos')->nullable()->index();
            $table->integer('relation_id');
            $table->integer('relation_type');
            $table->integer('auxiliar')->default(0)->comment('0:no, 1:si');
            $table->dateTime('fecha_manual')->nullable()->index();
            $table->string('consecutivo', 20);
            $table->string('documento_referencia', 20)->nullable()->index();
            $table->decimal('debito', 15)->default(0);
            $table->decimal('credito', 15)->default(0);
            $table->decimal('saldo', 15)->nullable();
            $table->string('concepto', 600)->nullable();
            $table->boolean('anulado')->default(false)->index();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            // Índices compuestos para las consultas más críticas
            $table->index(['fecha_manual', 'anulado']);
            $table->index(['id_cuenta', 'fecha_manual', 'anulado']);
            $table->index(['id_nit', 'fecha_manual', 'anulado']);
            $table->index(['documento_referencia', 'fecha_manual', 'anulado']);
            $table->index(['id_cuenta', 'id_nit', 'documento_referencia', 'anulado']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documentos_generals');
    }
}

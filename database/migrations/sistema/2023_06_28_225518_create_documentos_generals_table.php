<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->integer('id_nit')->nullable();
            $table->integer('id_cuenta')->nullable();
            $table->integer('id_comprobante')->nullable();
            $table->integer('id_centro_costos')->nullable()->index();
            $table->integer('relation_id');
            $table->integer('relation_type');
            $table->integer('auxiliar')->default(0)->comment('0:no, 1:si');;
            $table->date('fecha_manual')->nullable();
            $table->string('consecutivo', 20);
            $table->string('documento_referencia', 20)->nullable();
            $table->decimal('debito', 15)->default(0);
            $table->decimal('credito', 15)->default(0);
            $table->decimal('saldo', 15)->nullable();
            $table->string('concepto', 600)->nullable();
            $table->boolean('anulado')->default(false);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
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

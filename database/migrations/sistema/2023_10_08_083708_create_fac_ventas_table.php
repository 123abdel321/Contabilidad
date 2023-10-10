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
        Schema::create('fac_ventas', function (Blueprint $table) {
            $table->id();
            $table->integer('id_cliente');
            $table->integer('id_resolucion');
            $table->integer('id_comprobante')->nullable();
            $table->integer('id_centro_costos');
            $table->integer('id_bodega')->nullable();
            $table->date('fecha_manual')->nullable();
            $table->string('consecutivo', 20);
            $table->string('documento_referencia', 20)->nullable();
            $table->decimal('subtotal', 15);
            $table->decimal('total_iva', 15);
            $table->decimal('total_descuento', 15);
            $table->decimal('total_rete_fuente', 15);
            $table->decimal('porcentaje_rete_fuente', 15);
            $table->decimal('total_factura', 15);
            $table->mediumText('observacion')->nullable();
            $table->string('codigo_tipo_documento_dian', 3)->comment('
                Factura de Venta Nacional: 01,
                Factura de Venta Exportación: 02,
                Nota crédito: 91,
                Nota debito: 92
            ');
            $table->string('fe_codigo_identificador', 100)->nullable()->comment('CUFE, CUDE');
            $table->dateTime('fe_fecha_validacion')->nullable();
            $table->dateTime('fe_fecha_envio_correo')->nullable();
            $table->boolean('fe_estado_acuse')->nullable()->comment('	0: Pendiente, 1: Aprobada, 2: Rechazada');
            $table->string('fe_codigo_qr', 1000)->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->unique(['consecutivo', 'id_resolucion']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fac_ventas');
    }
};

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
        Schema::create('inf_exogena_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_exogena');
            $table->integer('id_exogena_formato')->nullable();
            $table->integer('id_exogena_formato_concepto')->nullable();
            $table->integer('id_nit')->nullable();
            $table->integer('concepto')->nullable();
            $table->string('cuenta', 15);
            $table->string('tipo_documento');
            $table->string('numero_documento', 30);
            $table->string('digito_verificacion', 1)-> nullable();
            $table->string('primer_apellido', 60)->nullable();
            $table->string('segundo_apellido', 60)->nullable();
            $table->string('primer_nombre', 60)->nullable();
            $table->string('otros_nombres', 60)->nullable();
            $table->string('razon_social', 120)->nullable();
            $table->string('direccion', 100)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->string('municipio', 100)->nullable();
            $table->string('pais', 100)->nullable();
            $table->decimal('pago_cuenta_deducible', 15)->nullable();
            $table->decimal('pago_cuenta_no_deducible', 15)->nullable();
            $table->decimal('iva_mayor_deducible', 15)->nullable();
            $table->decimal('iva_mayor_no_deducible', 15)->nullable();
            $table->decimal('retencion_practicada_renta', 15)->nullable();
            $table->decimal('retencion_asumida_renta', 15)->nullable();
            $table->decimal('retencion_iva_practicado_comun', 15)->nullable();
            $table->decimal('retencion_practicada_iva_no_domiciliado', 15)->nullable();
            $table->decimal('impuesto_descontable', 15)->nullable();
            $table->decimal('iva_descontable_por_devoluciones_en_ventas', 15)->nullable();
            $table->decimal('impuesto_generado', 15)->nullable();
            $table->decimal('iva_generado_por_devoluciones_en_compras', 15)->nullable();
            $table->decimal('impuesto_al_consumo', 15)->nullable();
            $table->decimal('ingresos_brutos_recibidos', 15)->nullable();
            $table->decimal('devoluciones_rebajas_y_descuentos', 15)->nullable();
            $table->decimal('pagos_por_salario', 15)->nullable();
            $table->decimal('pagos_por_emolumentos_eclesiasticos', 15)->nullable();
            $table->decimal('pagos_por_honorarios', 15)->nullable();
            $table->decimal('pagos_por_servicios', 15)->nullable();
            $table->decimal('pagos_por_comisiones', 15)->nullable();
            $table->decimal('pagos_por_prestaciones_sociales', 15)->nullable();
            $table->decimal('pagos_por_viaticos', 15)->nullable();
            $table->decimal('pagos_por_gastos_de_representacion', 15)->nullable();
            $table->decimal('pagos_por_compensaciones', 15)->nullable();
            $table->decimal('otros_pagos', 15)->nullable();
            $table->decimal('pagos_realizados_con_bonos_electronicos', 15)->nullable();
            $table->decimal('cesantias_e_intereses_de_cesantias', 15)->nullable();
            $table->decimal('pensiones_de_jubilacion_vejez_o_invalidez', 15)->nullable();
            $table->decimal('aportes_obligatorios_por_salud', 15)->nullable();
            $table->decimal('aportes_obligatorios_a_fondos_de_pensiones', 15)->nullable();
            $table->decimal('aportes_voluntarios_a_fondos_de_pensiones_voluntarias', 15)->nullable();
            $table->decimal('aportes_a_cuentas_afc', 15)->nullable();
            $table->decimal('valor_de_las_retenciones_en_la_fuente', 15)->nullable();
            $table->decimal('valor_acumulado_del_pago', 15)->nullable();
            $table->decimal('retencion_en_la_fuente_que_le_practicaron', 15)->nullable();
            $table->decimal('saldo_cuentas_por_cobrar', 15)->nullable();
            $table->decimal('saldo_cuentas_por_pagar', 15)->nullable();
            $table->decimal('saldo', 15)->nullable();
            $table->decimal('valor', 15)->nullable();
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
        Schema::dropIfExists('inf_exogena_detalles');
    }
};

<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfExogenaDetalle extends Model
{
    use HasFactory;

    protected $connection = 'informes';

	protected $table = 'inf_exogena_detalles';

	protected $fillable = [
		'id_exogena',
		'id_exogena_formato',
		'id_exogena_formato_concepto',
		'id_nit',
		'concepto',
		'cuenta',
		'tipo_documento',
		'numero_documento',
		'digito_verificacion',
		'primer_apellido',
		'segundo_apellido',
		'primer_nombre',
		'otros_nombres',
		'razon_social',
		'direccion',
		'departamento',
		'municipio',
		'pais',
		'pago_cuenta_deducible',
		'pago_cuenta_no_deducible',
		'iva_mayor_deducible',
		'iva_mayor_no_deducible',
		'retencion_practicada_renta',
		'retencion_asumida_renta',
		'retencion_iva_practicado_comun',
		'retencion_practicada_iva_no_domiciliado',
		'impuesto_descontable',
		'iva_descontable_por_devoluciones_en_ventas',
		'impuesto_generado',
		'iva_generado_por_devoluciones_en_compras',
		'impuesto_al_consumo',
		'ingresos_brutos_recibidos',
		'devoluciones_rebajas_y_descuentos',
		'pagos_por_salario',
		'pagos_por_emolumentos_eclesiasticos',
		'pagos_por_honorarios',
		'pagos_por_servicios',
		'pagos_por_comisiones',
		'pagos_por_prestaciones_sociales',
		'pagos_por_viaticos',
		'pagos_por_gastos_de_representacion',
		'pagos_por_compensaciones_trabajo_asociado_cooperativo',
		'otros_pagos',
		'pagos_realizados_con_bonos_electronicos_o_de_papel_de_servicio_cheques_tarjetas_vales_etc',
		'cesantias_e_intereses_de_cesantias_efectivamente_pagadas_consignadas_o_reconocidas_en_el_periodo',
		'pensiones_de_jubilacion_vejez_o_invalidez',
		'aportes_obligatorios_por_salud',
		'aportes_obligatorios_a_fondos_de_pensiones_y_solidaridad_pensional_y_aportes_voluntarios_al_-_rais',
		'aportes_voluntarios_a_fondos_de_pensiones_voluntarias',
		'aportes_a_cuentas_afc',
		'valor_de_las_retenciones_en_la_fuente_por_pagos_de_rentas_de_trabajo_o_pensiones',
		'valor_acumulado_del_pago_o_abono_sujeto_a_retencion_en_la_fuente',
		'retencion_en_la_fuente_que_le_practicaron',
		'saldo_cuentas_por_cobrar',
		'saldo_cuentas_por_pagar',
		'saldo',
		'valor',
		'created_by',
		'updated_by',
	];
}

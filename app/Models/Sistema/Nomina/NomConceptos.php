<?php

namespace App\Models\Sistema\Nomina;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//MODELS
use App\Models\Sistema\PlanCuentas;

class NomConceptos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "nom_conceptos";

    protected $fillable = [
        'tipo_concepto',
        'codigo',
        'nombre',
        'id_cuenta_administrativos',
        'id_cuenta_operativos',
        'id_cuenta_ventas',
        'id_cuenta_otros',
        'porcentaje',
        'id_concepto_porcentaje',
        'unidad',
        'valor_mensual',
        'concepto_fijo',
        'base_retencion',
        'base_sena',
        'base_icbf',
        'base_caja_compensacion',
        'base_salud',
        'base_pension',
        'base_arl',
        'base_vacacion',
        'base_prima',
        'base_cesantia',
        'base_interes_cesantia',
        'created_by',
        'updated_by',
    ];

    protected $appends = ['tipo_concepto_nombre'];

    public function getTipoConceptoNombreAttribute()
    {
        $conceptos = $this->getConceptosNomina();
        
        if (array_key_exists($this->tipo_concepto, $conceptos)) {
            return $conceptos[$this->tipo_concepto];
        }

        return 'Desconocido';
    }

    public function cuenta_administrativos()
    {
        return $this->belongsTo(PlanCuentas::class, "id_cuenta_administrativos");
	}

	public function cuenta_operativos()
    {
        return $this->belongsTo(PlanCuentas::class, "id_cuenta_operativos");
	}

	public function cuenta_ventas()
    {
        return $this->belongsTo(PlanCuentas::class, "id_cuenta_ventas");
	}

	public function cuenta_otros()
    {
        return $this->belongsTo(PlanCuentas::class, "id_cuenta_otros");
    }

    protected function getConceptosNomina()
    {
        return [
            'basico' => 'Básicos',
            'auxilio_transporte' => 'Auxilio de transporte',
            'viatico_manu_aloj_s' => 'Manutención y/o alojamiento',
            'viatico_manu_aloj_ns' => 'Manutención y/o alojamiento no salariales',
            'heds' => 'Horas extras diurnas',
            'hens' => 'Horas extras nocturnas',
            'hrns' => 'Horas recargo nocturno',
            'heddfs' => 'Horas extras diurnas festivas',
            'hrddfs' => 'Horas recargo diurnas festivas',
            'hendfs' => 'Horas extras nocturnas festivas',
            'hrndfs' => 'Horas recargo nocturno festivas',
            'vacaciones_comunes' => 'Vacaciones comunes',
            'vacaciones_compensadas' => 'Vacaciones compensadas',
            'primas' => 'Primas',
            'cesantias' => 'Cesantías',
            'incapacidades' => 'Incapacidades',
            'licencia_mp' => 'Licencia de maternidad o paternidad',
            'licencia_r' => 'Licencia remunerada',
            'licencia_nr' => 'Licencia no remunerada',
            'bonificaciones' => 'Bonificaciones',
            'bonificacion_s' => 'Bonificación salarial',
            'bonificacion_ns' => 'Bonificación no salarial',
            'auxilios' => 'Auxilios',
            'auxilio_s' => 'Auxilio salarial',
            'auxilio_ns' => 'Auxilio no salarial',
            'huelgas_legales' => 'Huelgas legales',
            'otros_conceptos' => 'Otros conceptos',
            'concepto_s' => 'Concepto salarial',
            'concepto_ns' => 'Concepto no salarial',
            'pago_s' => 'Pago salarial',
            'pago_ns' => 'Pago no salarial',
            'pago_alimentacion_s' => 'Pago alimentación salarial',
            'pago_alimentacion_ns' => 'Pago alimentación no salarial',
            'compensaciones' => 'Compensaciones',
            'bono_epctv_s' => 'Bonos electr, cheques, etc',
            'comisiones' => 'Comisiones',
            'pagos_terceros' => 'Pagos terceros',
            'anticipos' => 'Anticipos',
            'dotacion' => 'Dotación',
            'apoyo_sost' => 'Apoyo sostenible',
            'teletrabajo' => 'Teletrabajo',
            'bonif_retiro' => 'Bonificación retiro',
            'indemnizacion' => 'Indemnización',
            'salud' => 'Salud',
            'fondo_pension' => 'Fondo pensión',
            'fondo_sp' => 'Fondo de seguridad pensional',
            'sindicatos' => 'Sindicatos',
            'sanciones' => 'Sanciones',
            'libranzas' => 'Libranzas',
            'otras_deducciones' => 'Otras deducciones',
            'pension_voluntaria' => 'Pensión voluntaria',
            'retencion_fuente' => 'Retención fuente',
            'ica' => 'ICA',
            'afc' => 'Ahorro fomento a la construcción',
            'cooperativa' => 'Cooperativa',
            'embargo_fiscal' => 'Embargo fiscal',
            'plan_complementarios' => 'Plan complementarios',
            'educacion' => 'Educación',
            'reintegro' => 'Reintegro',
            'deuda' => 'Deuda'
        ];
    }
}

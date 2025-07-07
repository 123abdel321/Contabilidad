<?php

namespace App\Models\Sistema\Nomina;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\CentroCostos;

class NomContratos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "nom_contratos";

    public const TIPOS_EMPLEADO = [
		'administrativos',
		'operativos',
		'ventas',
		'otros',
	];

    const ESTADO_INACTIVO = 0;
	const ESTADO_ACTIVO = 1;
	const ESTADO_FINALIZADO = 2;

    const TIPO_SALARIO_NORMAL = 0;
	const TIPO_SALARIO_HONORARIOS = 1;
	const TIPO_SALARIO_INTEGRAL = 2;
	const TIPO_SALARIO_SERVICIOS = 3;
	const TIPO_SALARIO_PRACTICANTE = 4;

    const TIPO_EMPLEADO_ADMINISTRATIVO = 0;
	const TIPO_EMPLEADO_OPERATIVO = 1;
	const TIPO_EMPLEADO_VENTAS = 2;
	const TIPO_EMPLEADO_OTROS = 3;

    const TIPO_TERMINO_INDEFINIDO = 0;
    const TIPO_TERMINO_FIJO = 1;
    const TIPO_TERMINO_OBRA_LABOR = 2;
    const TIPO_TERMINO_TRANSITORIO = 3;

    protected $fillable = [
        'id_empleado',
        'id_periodo',
        'id_concepto_basico',
        'fecha_inicio_contrato',
        'fecha_fin_contrato',
        'estado',
        'termino',
        'tipo_salario',
        'tipo_empleado',
        'id_centro_costo',
        'id_oficio',
        'salario',
        'tipo_cotizante',
        'subtipo_cotizante',
        'id_fondo_salud',
        'id_fondo_pension',
        'id_fondo_cesantias',
        'id_fondo_caja_compensacion',
        'id_fondo_arl',
        'nivel_riesgo_arl',
        'porcentaje_arl',
        'metodo_retencion',
        'porcentaje_fijo',
        'disminucion_defecto_retencion',
        'auxilio_transporte',
        'talla_camisa',
        'talla_pantalon',
        'talla_zapatos',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    public function nit()
    {
        return $this->belongsTo(Nits::class, 'id_empleado');
	}

    public function periodo()
    {
        return $this->belongsTo(NomPeriodos::class, 'id_periodo');
	}

    public function periodo_pago()
    {
        return $this->belongsTo(NomPeriodoPagos::class, 'id', 'id_contrato');
	}

    public function concepto_basico()
    {
        return $this->belongsTo(NomConceptos::class, 'id_concepto_basico');
	}

    public function cecos()
    {
        return $this->belongsTo(CentroCostos::class, 'id_centro_costo');
	}

    public function fondo_salud()
    {
        return $this->belongsTo(NomAdministradoras::class, 'id_fondo_salud');
	}

    public function fondo_pension()
    {
        return $this->belongsTo(NomAdministradoras::class, 'id_fondo_pension');
	}

    public function fondo_cesantias()
    {
        return $this->belongsTo(NomAdministradoras::class, 'id_fondo_cesantias');
	}

    public function fondo_caja_compensacion()
    {
        return $this->belongsTo(NomAdministradoras::class, 'id_fondo_caja_compensacion');
	}

    public function fondo_arl()
    {
        return $this->belongsTo(NomAdministradoras::class, 'id_fondo_arl');
	}
}

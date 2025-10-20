<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
//MODELS
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\Nomina\NomContratos;

class Nits extends Model
{
    use HasFactory;

    protected $connection = 'sam';

	protected $table = "nits";

	const TIPO_CONTRIBUYENTE_PERSONA_JURIDICA = 1;
	const TIPO_CONTRIBUYENTE_PERSONA_NATURAL = 2;

    protected $fillable = [
		'id_tipo_documento',
		'id_ciudad',
		'id_departamento',
		'id_pais',
		'id_banco',
		'id_responsabilidades',
		'id_actividad_economica',
		'id_vendedor',
		'numero_documento',
		'digito_verificacion',
		'empleado',
		'tipo_contribuyente',
		'primer_apellido',
		'segundo_apellido',
		'primer_nombre',
		'otros_nombres',
		'razon_social',
		'nombre_comercial',
		'direccion',
		'apartamentos',
		'email',
		'email_1',
		'email_2',
		'email_recepcion_factura_electronica',
		'telefono_1',
		'telefono_2',
		'tipo_cuenta_banco',
		'tipo_contribuyente',
		'cuenta_bancaria',
		'plazo',
		'cupo',
		'descuento',
		'no_calcular_iva',
		'inactivar',
		'observaciones',
		'email_1',
		'email_2',
		'logo_nit',
		'declarante',
		'sumar_aiu',
		'porcentaje_aiu',
		'porcentaje_reteica',
		'created_by',
		'updated_by',
		'created_at',
		'updated_at',
	];

	protected $appends = ['nombre_completo', 'text'];

	public function tipo_documento()
    {
        return $this->belongsTo(TipoDocumentos::class, "id_tipo_documento");
    }

	public function vendedor()
    {
        return $this->belongsTo(FacVendedores::class, "id_vendedor");
    }

	public function ciudad() {
		return $this->belongsTo('App\Models\Empresas\Ciudades', 'id_ciudad', 'id');
	}

	public function departamento() {
		return $this->belongsTo('App\Models\Empresas\Departamentos', 'id_departamento', 'id');
	}
	
	public function pais() {
		return $this->belongsTo('App\Models\Empresas\Paises', 'id_pais', 'id');
	}

	public function actividad_economica() {
		return $this->belongsTo('App\Models\Empresas\ActividadesEconomicas', 'id_actividad_economica', 'id');
	}

	public function getNombreCompletoAttribute()
	{
		if($this->razon_social) return $this->razon_social;

		return "$this->primer_nombre $this->otros_nombres $this->primer_apellido $this->segundo_apellido";
	}

	public function contrato()
    {
        return $this->belongsTo(NomContratos::class, "id", "id_empleado");
    }

	protected function getTextAttribute()
    {
        // 1. Obtener la variable de entorno (asumiendo que este modelo está disponible)
        $ubicacion_maximoph = VariablesEntorno::where('nombre', 'ubicacion_maximoph')->first();
        $use_maximoph_logic = $ubicacion_maximoph && $ubicacion_maximoph->valor;

        $primer_nombre = $this->primer_nombre;
        $otros_nombres = $this->otros_nombres;
        $primer_apellido = $this->primer_apellido;
        $segundo_apellido = $this->segundo_apellido;
        $razon_social = $this->razon_social;
        $numero_documento = $this->numero_documento;
        $apartamentos = $this->apartamentos;

        // Concatenación de nombres para personas naturales
        $nombre_completo_natural = trim(implode(' ', array_filter([$primer_nombre, $otros_nombres, $primer_apellido, $segundo_apellido])));


        if ($use_maximoph_logic) {
            // Lógica de ubicacion_maximoph (más compleja)
            if ($apartamentos) {
                if ($razon_social) {
                    return "{$razon_social} - {$apartamentos}";
                } else {
                    return "{$nombre_completo_natural} - {$apartamentos}";
                }
            } else {
                if ($razon_social) {
                    return "{$numero_documento} - {$razon_social}";
                } else {
                    return "{$numero_documento} - {$nombre_completo_natural}";
                }
            }
        }

        // Lógica por defecto (la primera lógica de tu comboNit)
        if ($razon_social) {
            return "{$numero_documento} - {$razon_social}";
        } else {
            return "{$numero_documento} - {$nombre_completo_natural}";
        }
    }

}

<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
		'id_actividad_econo',
		'id_banco',
		'id_responsabilidades',
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
		'email',
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
		'logo_nit',
		'created_by',
		'updated_by',
		'created_at',
		'updated_at',
	];

	protected $appends = ['nombre_completo'];

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

	public function getNombreCompletoAttribute()
	{
		if($this->razon_social) return $this->razon_social;

		return "$this->primer_nombre $this->otros_nombres $this->primer_apellido $this->segundo_apellido";
	}

}

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
		'observaciones'
	];

	public function tipo_documento()
    {
        return $this->belongsTo("App\Models\Sistema\TipoDocumentos", "id_tipo_documento");
    }

}

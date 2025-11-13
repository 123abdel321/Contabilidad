<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = 'empresas';

    const ESTADO_INACTIVO = 0;
    const ESTADO_ACTIVO = 1;
    const ESTADO_PERIODO_GRACIA = 2;
    const ESTADO_MOROSO = 3;
    const ESTADO_RETIRADO = 4;
    const ESTADO_INSTALANDO = 5;

    protected $fillable = [
        'id_nit',
        'estado',
        'servidor',
        'token_db',
        'nombre',
        'primer_apellido',
        'segundo_apellido',
        'primer_nombre',
        'otros_nombres',
        'tipo_contribuyente',
        'razon_social',
        'nit',
        'dv',
        'codigos_responsabilidades',
        'notas_negociacion',
        'logo',
        'fecha_retiro',
        'direccion',
        'email',
        'telefono',
        'hash',
        'id_empresa_referido',
        'id_usuario_owner',
        'fecha_ultimo_cierre'
	];

    public function usuario () {
		return $this->hasOne("App\Models\User", "id", "id_usuario_owner");
	}

    public function suscripciones () {
		return $this->hasMany("App\Models\Empresas\EmpresaSuscripcion", "id_empresa");
	}

    public function componentes () {
      	return $this->hasMany("App\Models\Empresas\EmpresaComponentesSuscripcion", "id_empresa");
    }

    public function suscripcionActiva () {
        return $this->hasOne('App\Models\Empresas\EmpresaSuscripcion', 'id_empresa')
			->where('estado', 1);
    }

    public function getNombreEstadoAttribute()
    {
        switch ($this->estado) {
            case self::ESTADO_INACTIVO:
                return 'Inactivo';
            case self::ESTADO_ACTIVO:
                return 'Activo';
            case self::ESTADO_PERIODO_GRACIA:
                return 'Periodo de Gracia';
            case self::ESTADO_MOROSO:
                return 'Moroso';
            case self::ESTADO_RETIRADO:
                return 'Retirado';
            case self::ESTADO_INSTALANDO:
                return 'Instalando';
            default:
                return 'Desconocido';
        }
    }

    public function getBadgeEstadoAttribute()
    {
        $badges = [
            self::ESTADO_INACTIVO => '<span class="badge bg-secondary">Inactivo</span>',
            self::ESTADO_ACTIVO => '<span class="badge bg-success">Activo</span>',
            self::ESTADO_PERIODO_GRACIA => '<span class="badge bg-warning">Periodo de Gracia</span>',
            self::ESTADO_MOROSO => '<span class="badge bg-danger">Moroso</span>',
            self::ESTADO_RETIRADO => '<span class="badge bg-dark">Retirado</span>',
            self::ESTADO_INSTALANDO => '<span class="badge bg-info">Instalando</span>',
        ];
        
        return $badges[$this->estado] ?? '<span class="badge bg-light">Desconocido</span>';
    }

}

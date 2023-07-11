<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Model;

class BaseDatosProvisionada extends Model
{
	protected $table = 'base_datos_provisionadas';

	public $timestamps = false;

	public $incrementing = false;

	public $primaryKey = 'hash';

	public $keyType = 'string';

	protected $fillable = [
		'hash',
		'estado', // 1 - Disponible, 2 - Ocupada
	];

	const DISPONIBLE = 1;
	const OCUPADO = 2;

	public function scopeAvailable($query)
	{
		return $query->where('estado', self::DISPONIBLE);
	}

	public function ocupar()
	{
		$this->estado = self::OCUPADO;
		$this->save();
	}
}

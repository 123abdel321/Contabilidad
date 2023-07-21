<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paises extends Model
{
    use HasFactory;

	protected $connection = 'mysql';

    protected $table = 'paises';

	protected $fillable = [
		'id',
		'nombre'
	];
}

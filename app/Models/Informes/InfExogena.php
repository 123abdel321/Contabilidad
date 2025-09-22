<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfExogena extends Model
{
    use HasFactory;

    protected $connection = 'informes';

	protected $table = "inf_exogenas";

	protected $fillable = [
		'id_empresa',
		'year',
		'id_exogena_formato',
		'id_exogena_formato_concepto',
		'id_nit',
		'exporta_excel',
        'archivo_excel',
        'estado',
		'created_by',
		'updated_by',
		'created_at',
        'updated_at'
	];
}

<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacVendedores extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_vendedores";

    protected $fillable = [
        'id',
        'id_nit',
        'plazo_dias',
        'porcentaje_comision',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    public function nit()
    {
        return $this->belongsTo(Nits::class, 'id_nit');
	}
}

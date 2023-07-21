<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacDocumentos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_documentos";

    protected $fillable = [
        'id_comprobante',
        'fecha_manual',
        'consecutivo',
        'debito',
        'credito',
        'saldo_final',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    public function documentos()
    {
        return $this->morphMany('App\Models\Sistema\DocumentosGeneral', 'relation');
	}

    public function comprobante()
	{
		return $this->belongsTo('App\Models\Sistema\Comprobantes', 'id_comprobante');
	}
}

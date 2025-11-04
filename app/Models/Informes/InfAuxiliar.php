<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\PlanCuentas;

class InfAuxiliar extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_auxiliars";

    protected $fillable = [
        'id',
        'id_empresa',
        'id_nit',
        'id_cuenta',
        'fecha_desde',
        'fecha_hasta',
        'estado',
        'exporta_excel',
        'archivo_excel',
        'exporta_pdf',
        'archivo_pdf',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    public function detalle(): BelongsToMany
    {
        return $this->belongsToMany(InfAuxiliarDetalle::class, 'id_auxiliar', 'id');
    }

    public function nit()
    {
        return $this->belongsTo(Nits::class, 'id_nit');
	}

    public function cuenta()
    {
        return $this->belongsTo(PlanCuentas::class, 'id_cuenta');
	}

}

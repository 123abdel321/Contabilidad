<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VariablesEntorno extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "variables_entornos";

    protected $fillable = [
        'nombre',
        'valor',
        'created_by',
        'updated_by'
    ];

    public function comprobante()
	{
		return $this->belongsTo(Comprobantes::class, 'valor');
	}
}

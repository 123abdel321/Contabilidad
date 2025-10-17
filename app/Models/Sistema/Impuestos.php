<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Impuestos extends Model
{
  use HasFactory;

  protected $connection = 'sam';

  protected $table = "impuestos";

  protected $fillable = [
    'id',
    'id_tipo_impuesto',
    'nombre',
    'base',
    'porcentaje',
  ];

  public function tipo_impuesto()
  {
      return $this->belongsTo(TipoImpuestos::class, "id_tipo_impuesto");
  }

}

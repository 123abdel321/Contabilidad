<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDocumentos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "tipos_documentos";

    public $fillable = [
      'codigo',
      'nombre'
    ];
}

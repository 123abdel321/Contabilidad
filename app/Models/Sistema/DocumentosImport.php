<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentosImport extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "documentos_imports";

    protected $fillable = [
        'nombre_nit',
        'nombre_cuenta',
        'nombre_cecos',
        'nombre_comprobante',
        'documento_nit',
        'cuenta_contable',
        'codigo_cecos',
        'codigo_comprobante',
        'consecutivo',
        'documento_referencia',
        'fecha_manual',
        'debito',
        'credito',
        'concepto',
        'total_errores',
        'errores',
    ];
}

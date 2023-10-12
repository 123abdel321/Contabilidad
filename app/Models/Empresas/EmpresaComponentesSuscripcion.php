<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaComponentesSuscripcion extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = 'empresa_componentes_suscripcions';

    protected $fillable = [
        'id_empresa',
        'id_empresa_suscripcion',
        'id_componente',
        'cantidad',
        'precio',
        'fecha_siguiente_cobro',
        'created_by',
        'updated_by'
    ];

    public function componente (){
        return $this->belongsTo("App\Models\Empresas\ComponentesSuscripcion", "id_componente");
    }
}

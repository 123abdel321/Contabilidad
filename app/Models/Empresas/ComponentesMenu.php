<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComponentesMenu extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = 'componentes_menus';

    protected $fillable = [
        'id_padre',
        'id_componente',
        'nombre',
        'tipo',
        'estado',
        'created_by',
        'updated_by'
	];

    public function padre (){
        return $this->belongsTo("App\Models\Empresas\ComponentesMenu", "id_padre");
    }

    public function componente (){
        return $this->belongsTo("App\Models\Empresas\ComponentesMenu", "id_padre");
    }

}

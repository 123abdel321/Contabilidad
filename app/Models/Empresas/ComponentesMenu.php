<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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
        'tipo_menu',
        'nombre',
        'url',
        'icon',
        'code_name',
        'estado',
        'created_by',
        'updated_by'
	];

    public function padre (){
        return $this->belongsTo("App\Models\Empresas\ComponentesMenu", "id_padre");
    }
    
    public function permisos (){
        return $this->hasMany(Permission::class, "id_componente_menu")->orderBy('id_componente_menu');
    }
}

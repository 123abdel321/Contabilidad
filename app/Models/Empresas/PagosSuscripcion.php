<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//MODELS
use App\Models\User;

class PagosSuscripcion extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = 'pagos_suscripciones';

    protected $fillable = [
        'id_empresa',
        'id_usuario',
        'token_db',
        'detalle_json',
        'valor',
        'estado',
    ];

    public function usuario(){
		return $this->belongsTo(User::class, "id_usuario");
	}
}

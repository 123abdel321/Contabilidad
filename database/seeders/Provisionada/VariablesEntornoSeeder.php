<?php

namespace Database\Seeders\Provisionada;

use Illuminate\Database\Seeder;

class BodegasSeeder extends Seeder
{
    public function run()
    {
        \DB::table('variables_entornos')->truncate();

        \DB::table('variables_entornos')->insert([
            [
                'id' => 1,
                'nombre' => 'iva_incluido',
                'valor' => '1',
            ],
            [
                'id' => 2,
                'nombre' => 'capturar_documento_descuadrado',
                'valor' => '',
            ],
            [
                'id' => 3,
                'nombre' => 'valor_uvt',
                'valor' => '0',
            ],
            [
                'id' => 4,
                'nombre' => 'vendedores_ventas',
                'valor' => '',
            ],
        ]);
    }
    
}

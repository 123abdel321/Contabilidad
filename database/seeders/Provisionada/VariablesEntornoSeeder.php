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
                'nombre' => 'capturar_documento_descuadrado',
                'valor' => '0',
            ],
            [
                'id' => 2,
                'nombre' => 'id_comprobante_compra',
                'valor' => '3',
            ],
            [
                'id' => 3,
                'nombre' => 'id_cuenta_cobrar',
                'valor' => '13',
            ],
        ]);
    }
    
}

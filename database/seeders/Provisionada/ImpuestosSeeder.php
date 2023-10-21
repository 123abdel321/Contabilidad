<?php

namespace Database\Seeders\Provisionada;

use Illuminate\Database\Seeder;

class ImpuestosSeeder extends Seeder
{
    public function run()
    {
        \DB::table('impuestos')->truncate();

        \DB::table('impuestos')->insert([
            [
                'id' => 1,
                'id_tipo_impuesto' => 1,
                'nombre' => 'IVA',
                'base' => 0,
                'porcentaje' => 19
            ],
            [
                'id' => 2,
                'id_tipo_impuesto' => 1,
                'nombre' => 'IVA',
                'base' => 0,
                'porcentaje' => 16
            ],
            [
                'id' => 3,
                'id_tipo_impuesto' => 1,
                'nombre' => 'IVA',
                'base' => 0,
                'porcentaje' => 5
            ],
            [
                'id' => 4,
                'id_tipo_impuesto' => 1,
                'nombre' => 'IVA',
                'base' => 0,
                'porcentaje' => 0
            ],
            [
                'id' => 5,
                'id_tipo_impuesto' => 1,
                'nombre' => 'RETENCIÓN EN LA FUENTE',
                'base' => 1000000,
                'porcentaje' => 8
            ],
        ]);
    }
}

<?php

namespace Database\Seeders\Provisionada;

use Illuminate\Database\Seeder;

class BodegasSeeder extends Seeder
{
    public function run()
    {
        \DB::table('fac_bodegas')->truncate();

        \DB::table('fac_bodegas')->insert(array (
            0 =>
            array (
                'id' => 1,
                'codigo' => '01',
                'nombre' => 'PRINCIPAL',
                'ubicacion' => '',
                'id_centro_costos' => 1,
            ),
        ));
    }
}

<?php

namespace Database\Seeders\Provisionada;

use Illuminate\Database\Seeder;

class BodegasSeeder extends Seeder
{
    public function run()
    {
        \DB::table('fac_bodegas')->truncate();

        \DB::table('fac_bodegas')->insert([
            [
                'id' => 1,
                'codigo' => '01',
                'nombre' => 'PRINCIPAL',
                'ubicacion' => 'DIRECCIÓN',
                'id_cuenta_carta' => 4,
                'id_centro_costos' => 1
            ]
        ]);
    }
}

<?php

namespace Database\Seeders\Provisionada;

use Illuminate\Database\Seeder;

class CentroCostosSeeder extends Seeder
{
    public function run()
    {
        \DB::table('centro_costos')->truncate();

        \DB::table('centro_costos')->insert(array (
            0 =>
            array (
                'id' => 1,
                'codigo' => '01',
                'nombre' => 'ADMINISTRACION'
            ),
        ));
    }
}

<?php

namespace Database\Seeders\Provisionada;

use Illuminate\Database\Seeder;

class CentroCostosSeeder extends Seeder
{
    public function run()
    {
        \DB::table('centro_costos')->truncate();

        \DB::table('centro_costos')->insert([
            [
                'id' => 1,
                'codigo' => '01',
                'nombre' => 'ADMINISTRACION'
            ]
        ]);
    }
}

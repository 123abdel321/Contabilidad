<?php

namespace Database\Seeders\Provisionada;

use Illuminate\Database\Seeder;

class NitsSeeder extends Seeder
{
    public function run()
    {
        \DB::table('nits')->truncate();

        \DB::table('nits')->insert(array (
            0 =>
            array (
                'id' => 1,
                'id_tipo_documento' => '3',
                'numero_documento' => '22222222',
                'tipo_contribuyente' => '2',
                'primer_apellido' => 'DOE',
                'primer_nombre' => 'JHON',
                'observaciones' => 'CLIENTE POR DEFECTO',
            ),
        ));
    }
}

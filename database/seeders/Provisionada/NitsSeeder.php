<?php

namespace Database\Seeders\Provisionada;

use Illuminate\Database\Seeder;

class NitsSeeder extends Seeder
{
    public function run()
    {
        \DB::table('nits')->truncate();

        \DB::table('nits')->insert([
            'id' => 1,
            'id_tipo_documento' => '3',
            'numero_documento' => '222222222222',
            'tipo_contribuyente' => '2',
            'primer_apellido' => 'CONSUMIDOR',
            'primer_nombre' => 'FINAL',
            'observaciones' => 'CLIENTE POR DEFECTO',
        ]);
    }
}

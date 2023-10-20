<?php

namespace Database\Seeders\Provisionada;

use Illuminate\Database\Seeder;

class TipoImpuestosSeeder extends Seeder
{

    public function run()
    {
        \DB::table('tipo_impuestos')->truncate();

        \DB::table('tipo_impuestos')->insert([
            [
                'id' => 1,
                'codigo' => '01',
                'nombre' => 'IVA',
                'es_retencion' => 0,
            ],
            [
                'id' => 2,
                'codigo' => '02',
                'nombre' => 'IC',
                'es_retencion' => 0,
            ],
            [
                'id' => 3,
                'codigo' => '03',
                'nombre' => 'ICA',
                'es_retencion' => 0,
            ],
            [
                'id' => 4,
                'codigo' => '04',
                'nombre' => 'INC',
                'es_retencion' => 0,
            ],
            [
                'id' => 5,
                'codigo' => '05',
                'nombre' => 'Retención sobre el IVA',
                'es_retencion' => 0,
            ],
            [
                'id' => 6,
                'codigo' => '06',
                'nombre' => 'Retención sobre fuente por renta ',
                'es_retencion' => 0,
            ],
            [
                'id' => 7,
                'codigo' => '07',
                'nombre' => 'Retención sobre el ICA ',
                'es_retencion' => 0,
            ],
            [
                'id' => 8,
                'codigo' => '20',
                'nombre' => 'FtoHorticultura',
                'es_retencion' => 0,
            ],
            [
                'id' => 9,
                'codigo' => '21',
                'nombre' => 'Timbre',
                'es_retencion' => 0,
            ],
            [
                'id' => 10,
                'codigo' => '22',
                'nombre' => 'Bolsas',
                'es_retencion' => 0,
            ],
            [
                'id' => 11,
                'codigo' => '23',
                'nombre' => 'INCarbono',
                'es_retencion' => 0,
            ],
            [
                'id' => 12,
                'codigo' => '24',
                'nombre' => 'INCombustibles',
                'es_retencion' => 0,
            ],
            [
                'id' => 13,
                'codigo' => '25',
                'nombre' => 'Sobretasa Combustibles',
                'es_retencion' => 0,
            ],
            [
                'id' => 14,
                'codigo' => '26',
                'nombre' => 'Sordicom',
                'es_retencion' => 0,
            ],
            [
                'id' => 15,
                'codigo' => 'ZZ',
                'nombre' => 'Otros tributos, tasas, contribuciones, y similares',
                'es_retencion' => 0,
            ],
        ]);
    }
}

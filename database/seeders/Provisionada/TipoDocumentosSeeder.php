<?php

namespace Database\Seeders\Provisionada;

use Illuminate\Database\Seeder;

class TipoDocumentosSeeder extends Seeder
{
    
    public function run()
    {
        \DB::table('tipos_documentos')->truncate();

        \DB::table('tipos_documentos')->insert(array (
            0 =>
            array (
                'id' => 1,
                'codigo' => '11',
                'nombre' => 'Registro civil',
            ),
            1 =>
            array (
                'id' => 2,
                'codigo' => '12',
                'nombre' => 'Tarjeta de identidad',
            ),
            2 =>
            array (
                'id' => 3,
                'codigo' => '13',
                'nombre' => 'Cédula de ciudadanía',
            ),
            3 =>
            array (
                'id' => 4,
                'codigo' => '21',
                'nombre' => 'Tarjeta de extranjería',
            ),
            4 =>
            array (
                'id' => 5,
                'codigo' => '22',
                'nombre' => 'Cédula de extranjería',
            ),
            5 =>
            array (
                'id' => 6,
                'codigo' => '31',
                'nombre' => 'NIT',
            ),
            6 =>
            array (
                'id' => 7,
                'codigo' => '41',
                'nombre' => 'Pasaporte',
            ),
            7 =>
            array (
                'id' => 8,
                'codigo' => '42',
                'nombre' => 'Documento de identificación extranjero',
            ),
            8 =>
            array (
                'id' => 9,
                'codigo' => '43',
                'nombre' => 'Sin identificación o para uso de la DIAN',
            ),
            9 =>
            array (
                'id' => 10,
                'codigo' => '50',
                'nombre' => 'NIT de otro país',
            ),
            10 =>
            array (
                'id' => 11,
                'codigo' => '91',
                'nombre' => 'NUIP *',
            ),
            11 =>
            array (
                'id' => 12,
                'codigo' => '99',
                'nombre' => 'Activo fijo',
            ),
            12 =>
            array (
                'id' => 13,
                'codigo' => '',
                'nombre' => 'Permiso Especial de Permanencia',
            ),
        ));
    }
}

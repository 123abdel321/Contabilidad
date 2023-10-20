<?php

namespace Database\Seeders\Provisionada;

use Illuminate\Database\Seeder;

class ComprobantesSeeder extends Seeder
{
    public function run()
    {
        \DB::table('comprobantes')->truncate();

        \DB::table('comprobantes')->insert([
            [
                'id' => 1,
                'codigo' => '01',
                'nombre' => 'INGRESOS',
                'tipo_comprobante' => 0,
                'tipo_consecutivo' => 0,
                'consecutivo_siguiente' => 1,
                'tesoreria' => 0,
                'maestra_padre' => NULL,
            ],
            [
                'id' => 2,
                'codigo' => '02',
                'nombre' => 'EGRESOS',
                'tipo_comprobante' => 1,
                'tipo_consecutivo' => 0,
                'consecutivo_siguiente' => 1,
                'tesoreria' => 0,
                'maestra_padre' => NULL,
            ],
            [
                'id' => 3,
                'codigo' => '03',
                'nombre' => 'COMPRAS',
                'tipo_comprobante' => 2,
                'tipo_consecutivo' => 0,
                'consecutivo_siguiente' => 1,
                'tesoreria' => 0,
                'maestra_padre' => NULL,
            ],
			[
                'id' => 4,
                'codigo' => '08',
                'nombre' => 'NOTAS CONTABLES',
                'tipo_comprobante' => 4,
                'tipo_consecutivo' => 1,
                'consecutivo_siguiente' => 1,
                'tesoreria' => 0,
                'maestra_padre' => NULL,
            ],
			[
                'id' => 5,
                'codigo' => '09',
                'nombre' => 'SALDOS INICIALES',
                'tipo_comprobante' => 4,
                'tipo_consecutivo' => 0,
                'consecutivo_siguiente' => 1,
                'tesoreria' => 0,
                'maestra_padre' => NULL,
            ],
			[
                'id' => 6,
                'codigo' => '12',
                'nombre' => 'NOMINA',
                'tipo_comprobante' => 4,
                'tipo_consecutivo' => 0,
                'consecutivo_siguiente' => 1,
                'tesoreria' => 0,
                'maestra_padre' => NULL,
            ],
			[
                'id' => 7,
                'codigo' => '13',
                'nombre' => 'PARAFISCALES',
                'tipo_comprobante' => 4,
                'tipo_consecutivo' => 0,
                'consecutivo_siguiente' => 1,
                'tesoreria' => 0,
                'maestra_padre' => NULL,
            ],
			[
                'id' => 8,
                'codigo' => '14',
                'nombre' => 'PRESTACIONES SOCIALES',
                'tipo_comprobante' => 4,
                'tipo_consecutivo' => 1,
                'consecutivo_siguiente' => 1,
                'tesoreria' => 0,
                'maestra_padre' => NULL,
            ],
			[
                'id' => 9,
                'codigo' => '99',
                'nombre' => 'CIERRE ANUAL',
                'tipo_comprobante' => 5,
                'tipo_consecutivo' => 0,
                'consecutivo_siguiente' => 1,
                'tesoreria' => 0,
                'maestra_padre' => NULL,
            ],
            [
                'id' => 10,
                'codigo' => '04',
                'nombre' => 'VENTAS',
                'tipo_comprobante' => 3,
                'tipo_consecutivo' => 0,
                'consecutivo_siguiente' => 1,
                'tesoreria' => 0,
                'maestra_padre' => NULL,
            ],
        ]);
    }
}

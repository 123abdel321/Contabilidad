<?php

namespace Database\Seeders\Provisionada;

use Illuminate\Database\Seeder;

class ExogenaFormatoConceptosProvisionalSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('exogena_formato_conceptos')->truncate();

        \DB::table('exogena_formato_conceptos')->insert([
            // Conceptos formato 1001
            [
                'id' => 1,
                'id_exogena_formato' => 1,
                'concepto' => 4004,
            ],
            [
                'id' => 2,
                'id_exogena_formato' => 1,
                'concepto' => 5001,
            ],
            [
                'id' => 3,
                'id_exogena_formato' => 1,
                'concepto' => 5002,
            ],
            [
                'id' => 4,
                'id_exogena_formato' => 1,
                'concepto' => 5003,
            ],
            [
                'id' => 5,
                'id_exogena_formato' => 1,
                'concepto' => 5004,
            ],
            [
                'id' => 6,
                'id_exogena_formato' => 1,
                'concepto' => 5005,
            ],
            [
                'id' => 7,
                'id_exogena_formato' => 1,
                'concepto' => 5006,
            ],
            [
                'id' => 8,
                'id_exogena_formato' => 1,
                'concepto' => 5007,
            ],
            [
                'id' => 9,
                'id_exogena_formato' => 1,
                'concepto' => 5008,
            ],
            [
                'id' => 10,
                'id_exogena_formato' => 1,
                'concepto' => 5010,
            ],
            [
                'id' => 11,
                'id_exogena_formato' => 1,
                'concepto' => 5011,
            ],
            [
                'id' => 12,
                'id_exogena_formato' => 1,
                'concepto' => 5012,
            ],
            [
                'id' => 13,
                'id_exogena_formato' => 1,
                'concepto' => 5014,
            ],
            [
                'id' => 14,
                'id_exogena_formato' => 1,
                'concepto' => 5015,
            ],
            [
                'id' => 15,
                'id_exogena_formato' => 1,
                'concepto' => 5016,
            ],
            [
                'id' => 16,
                'id_exogena_formato' => 1,
                'concepto' => 5019,
            ],
            [
                'id' => 17,
                'id_exogena_formato' => 1,
                'concepto' => 5020,
            ],
            [
                'id' => 18,
                'id_exogena_formato' => 1,
                'concepto' => 5026,
            ],
            [
                'id' => 19,
                'id_exogena_formato' => 1,
                'concepto' => 5028,
            ],
            [
                'id' => 20,
                'id_exogena_formato' => 1,
                'concepto' => 5055,
            ],
            [
                'id' => 21,
                'id_exogena_formato' => 1,
                'concepto' => 5058,
            ],
            [
                'id' => 22,
                'id_exogena_formato' => 1,
                'concepto' => 8207
            ],

            // Conceptos formato 1003
            [
                'id' => 23,
                'id_exogena_formato' => 2,
                'concepto' => 1302,
            ],
            [
                'id' => 24,
                'id_exogena_formato' => 2,
                'concepto' => 1303,
            ],
            [
                'id' => 25,
                'id_exogena_formato' => 2,
                'concepto' => 1304,
            ],
            [
                'id' => 26,
                'id_exogena_formato' => 2,
                'concepto' => 1308,
            ],

            // Conceptos formato 1007
            [
                'id' => 27,
                'id_exogena_formato' => 5,
                'concepto' => 4001,
            ],
            [
                'id' => 28,
                'id_exogena_formato' => 5,
                'concepto' => 4002,
            ],
            [
                'id' => 29,
                'id_exogena_formato' => 5,
                'concepto' => 4004,
            ],

            // Conceptos formato 1008
            [
                'id' => 30,
                'id_exogena_formato' => 6,
                'concepto' => 1315,
            ],
            [
                'id' => 31,
                'id_exogena_formato' => 6,
                'concepto' => 1316,
            ],
            [
                'id' => 32,
                'id_exogena_formato' => 6,
                'concepto' => 1317,
            ],
            [
                'id' => 33,
                'id_exogena_formato' => 6,
                'concepto' => 1318,
            ],

            // Conceptos formato 1009
            [
                'id' => 34,
                'id_exogena_formato' => 7,
                'concepto' => 2201,
            ],
            [
                'id' => 35,
                'id_exogena_formato' => 7,
                'concepto' => 2202,
            ],
            [
                'id' => 36,
                'id_exogena_formato' => 7,
                'concepto' => 2203,
            ],
            [
                'id' => 37,
                'id_exogena_formato' => 7,
                'concepto' => 2204,
            ],
            [
                'id' => 38,
                'id_exogena_formato' => 7,
                'concepto' => 2205,
            ],
            [
                'id' => 39,
                'id_exogena_formato' => 7,
                'concepto' => 2206,
            ],
            [
                'id' => 40,
                'id_exogena_formato' => 7,
                'concepto' => 2208,
            ],

            // Conceptos formato 1011
            [
                'id' => 41,
                'id_exogena_formato' => 8,
                'concepto' => 5016,
            ],
            [
                'id' => 42,
                'id_exogena_formato' => 8,
                'concepto' => 8205,
            ],

            // Conceptos formato 1012
            [
                'id' => 43,
                'id_exogena_formato' => 9,
                'concepto' => 1110,
            ],
            [
                'id' => 44,
                'id_exogena_formato' => 9,
                'concepto' => 1204,
            ],
            [
                'id' => 45,
                'id_exogena_formato' => 9,
                'concepto' => 1205,
            ],
        ]);
    }
}

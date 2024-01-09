<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PlanCuentasTiposTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('plan_cuentas_tipos')->delete();
        
        \DB::table('plan_cuentas_tipos')->insert(array (
            0 => 
            array (
                'id' => 2,
                'id_cuenta' => 4,
                'id_tipo_cuenta' => 2,
                'created_at' => '2023-12-22 10:38:43',
                'updated_at' => '2023-12-22 10:38:43',
            ),
            1 => 
            array (
                'id' => 3,
                'id_cuenta' => 5,
                'id_tipo_cuenta' => 2,
                'created_at' => '2023-12-22 10:38:43',
                'updated_at' => '2023-12-22 10:38:43',
            ),
            2 => 
            array (
                'id' => 4,
                'id_cuenta' => 6,
                'id_tipo_cuenta' => 2,
                'created_at' => '2023-12-22 10:38:43',
                'updated_at' => '2023-12-22 10:38:43',
            ),
            3 => 
            array (
                'id' => 6,
                'id_cuenta' => 8,
                'id_tipo_cuenta' => 2,
                'created_at' => '2023-12-22 10:38:43',
                'updated_at' => '2023-12-22 10:38:43',
            ),
            4 => 
            array (
                'id' => 7,
                'id_cuenta' => 9,
                'id_tipo_cuenta' => 2,
                'created_at' => '2023-12-22 10:38:43',
                'updated_at' => '2023-12-22 10:38:43',
            ),
            5 => 
            array (
                'id' => 8,
                'id_cuenta' => 10,
                'id_tipo_cuenta' => 2,
                'created_at' => '2023-12-22 10:38:43',
                'updated_at' => '2023-12-22 10:38:43',
            ),
            6 => 
            array (
                'id' => 10,
                'id_cuenta' => 15,
                'id_tipo_cuenta' => 2,
                'created_at' => '2023-12-22 10:38:43',
                'updated_at' => '2023-12-22 10:38:43',
            ),
            7 => 
            array (
                'id' => 11,
                'id_cuenta' => 16,
                'id_tipo_cuenta' => 2,
                'created_at' => '2023-12-22 10:38:43',
                'updated_at' => '2023-12-22 10:38:43',
            ),
            8 => 
            array (
                'id' => 12,
                'id_cuenta' => 17,
                'id_tipo_cuenta' => 2,
                'created_at' => '2023-12-22 10:38:43',
                'updated_at' => '2023-12-22 10:38:43',
            ),
            9 => 
            array (
                'id' => 13,
                'id_cuenta' => 18,
                'id_tipo_cuenta' => 2,
                'created_at' => '2023-12-22 10:38:43',
                'updated_at' => '2023-12-22 10:38:43',
            ),
            10 => 
            array (
                'id' => 16,
                'id_cuenta' => 151,
                'id_tipo_cuenta' => 3,
                'created_at' => '2023-12-22 10:42:10',
                'updated_at' => '2023-12-22 10:42:10',
            ),
            11 => 
            array (
                'id' => 17,
                'id_cuenta' => 152,
                'id_tipo_cuenta' => 3,
                'created_at' => '2023-12-22 10:42:10',
                'updated_at' => '2023-12-22 10:42:10',
            ),
            12 => 
            array (
                'id' => 18,
                'id_cuenta' => 153,
                'id_tipo_cuenta' => 3,
                'created_at' => '2023-12-22 10:42:10',
                'updated_at' => '2023-12-22 10:42:10',
            ),
            13 => 
            array (
                'id' => 19,
                'id_cuenta' => 154,
                'id_tipo_cuenta' => 3,
                'created_at' => '2023-12-22 10:42:10',
                'updated_at' => '2023-12-22 10:42:10',
            ),
            14 => 
            array (
                'id' => 20,
                'id_cuenta' => 155,
                'id_tipo_cuenta' => 3,
                'created_at' => '2023-12-22 10:42:10',
                'updated_at' => '2023-12-22 10:42:10',
            ),
            15 => 
            array (
                'id' => 21,
                'id_cuenta' => 156,
                'id_tipo_cuenta' => 3,
                'created_at' => '2023-12-22 10:42:10',
                'updated_at' => '2023-12-22 10:42:10',
            ),
            16 => 
            array (
                'id' => 22,
                'id_cuenta' => 157,
                'id_tipo_cuenta' => 3,
                'created_at' => '2023-12-22 10:42:10',
                'updated_at' => '2023-12-22 10:42:10',
            ),
            17 => 
            array (
                'id' => 24,
                'id_cuenta' => 168,
                'id_tipo_cuenta' => 3,
                'created_at' => '2023-12-22 10:42:10',
                'updated_at' => '2023-12-22 10:42:10',
            ),
            18 => 
            array (
                'id' => 25,
                'id_cuenta' => 169,
                'id_tipo_cuenta' => 3,
                'created_at' => '2023-12-22 10:42:10',
                'updated_at' => '2023-12-22 10:42:10',
            ),
            19 => 
            array (
                'id' => 26,
                'id_cuenta' => 170,
                'id_tipo_cuenta' => 3,
                'created_at' => '2023-12-22 10:42:10',
                'updated_at' => '2023-12-22 10:42:10',
            ),
            20 => 
            array (
                'id' => 28,
                'id_cuenta' => 173,
                'id_tipo_cuenta' => 7,
                'created_at' => '2023-12-22 10:42:10',
                'updated_at' => '2023-12-22 10:42:10',
            ),
            21 => 
            array (
                'id' => 29,
                'id_cuenta' => 174,
                'id_tipo_cuenta' => 7,
                'created_at' => '2023-12-22 10:42:10',
                'updated_at' => '2023-12-22 10:42:10',
            ),
            22 => 
            array (
                'id' => 30,
                'id_cuenta' => 175,
                'id_tipo_cuenta' => 7,
                'created_at' => '2023-12-22 10:42:10',
                'updated_at' => '2023-12-22 10:42:10',
            ),
            23 => 
            array (
                'id' => 31,
                'id_cuenta' => 176,
                'id_tipo_cuenta' => 7,
                'created_at' => '2023-12-22 10:42:10',
                'updated_at' => '2023-12-22 10:42:10',
            ),
            24 => 
            array (
                'id' => 32,
                'id_cuenta' => 177,
                'id_tipo_cuenta' => 7,
                'created_at' => '2023-12-22 10:42:10',
                'updated_at' => '2023-12-22 10:42:10',
            ),
            25 => 
            array (
                'id' => 33,
                'id_cuenta' => 178,
                'id_tipo_cuenta' => 7,
                'created_at' => '2023-12-22 10:42:11',
                'updated_at' => '2023-12-22 10:42:11',
            ),
            26 => 
            array (
                'id' => 34,
                'id_cuenta' => 179,
                'id_tipo_cuenta' => 7,
                'created_at' => '2023-12-22 10:42:11',
                'updated_at' => '2023-12-22 10:42:11',
            ),
            27 => 
            array (
                'id' => 36,
                'id_cuenta' => 213,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            28 => 
            array (
                'id' => 37,
                'id_cuenta' => 214,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            29 => 
            array (
                'id' => 38,
                'id_cuenta' => 215,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            30 => 
            array (
                'id' => 39,
                'id_cuenta' => 216,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            31 => 
            array (
                'id' => 40,
                'id_cuenta' => 217,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            32 => 
            array (
                'id' => 41,
                'id_cuenta' => 218,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            33 => 
            array (
                'id' => 42,
                'id_cuenta' => 219,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            34 => 
            array (
                'id' => 43,
                'id_cuenta' => 220,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            35 => 
            array (
                'id' => 44,
                'id_cuenta' => 221,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            36 => 
            array (
                'id' => 45,
                'id_cuenta' => 222,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            37 => 
            array (
                'id' => 46,
                'id_cuenta' => 223,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            38 => 
            array (
                'id' => 47,
                'id_cuenta' => 224,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            39 => 
            array (
                'id' => 48,
                'id_cuenta' => 225,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            40 => 
            array (
                'id' => 49,
                'id_cuenta' => 226,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            41 => 
            array (
                'id' => 50,
                'id_cuenta' => 227,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            42 => 
            array (
                'id' => 51,
                'id_cuenta' => 228,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            43 => 
            array (
                'id' => 52,
                'id_cuenta' => 229,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            44 => 
            array (
                'id' => 53,
                'id_cuenta' => 230,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            45 => 
            array (
                'id' => 54,
                'id_cuenta' => 231,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            46 => 
            array (
                'id' => 55,
                'id_cuenta' => 232,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            47 => 
            array (
                'id' => 56,
                'id_cuenta' => 233,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            48 => 
            array (
                'id' => 57,
                'id_cuenta' => 234,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            49 => 
            array (
                'id' => 58,
                'id_cuenta' => 235,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            50 => 
            array (
                'id' => 59,
                'id_cuenta' => 236,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            51 => 
            array (
                'id' => 60,
                'id_cuenta' => 237,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            52 => 
            array (
                'id' => 61,
                'id_cuenta' => 238,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            53 => 
            array (
                'id' => 62,
                'id_cuenta' => 239,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            54 => 
            array (
                'id' => 63,
                'id_cuenta' => 240,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            55 => 
            array (
                'id' => 64,
                'id_cuenta' => 241,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            56 => 
            array (
                'id' => 65,
                'id_cuenta' => 242,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            57 => 
            array (
                'id' => 66,
                'id_cuenta' => 243,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            58 => 
            array (
                'id' => 67,
                'id_cuenta' => 244,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            59 => 
            array (
                'id' => 68,
                'id_cuenta' => 245,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            60 => 
            array (
                'id' => 69,
                'id_cuenta' => 246,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            61 => 
            array (
                'id' => 70,
                'id_cuenta' => 247,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            62 => 
            array (
                'id' => 71,
                'id_cuenta' => 248,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            63 => 
            array (
                'id' => 72,
                'id_cuenta' => 249,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            64 => 
            array (
                'id' => 73,
                'id_cuenta' => 250,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            65 => 
            array (
                'id' => 75,
                'id_cuenta' => 269,
                'id_tipo_cuenta' => 7,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            66 => 
            array (
                'id' => 76,
                'id_cuenta' => 270,
                'id_tipo_cuenta' => 7,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            67 => 
            array (
                'id' => 77,
                'id_cuenta' => 271,
                'id_tipo_cuenta' => 7,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            68 => 
            array (
                'id' => 80,
                'id_cuenta' => 274,
                'id_tipo_cuenta' => 7,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            69 => 
            array (
                'id' => 81,
                'id_cuenta' => 275,
                'id_tipo_cuenta' => 7,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            70 => 
            array (
                'id' => 83,
                'id_cuenta' => 296,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            71 => 
            array (
                'id' => 84,
                'id_cuenta' => 297,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:38',
                'updated_at' => '2023-12-22 10:42:38',
            ),
            72 => 
            array (
                'id' => 85,
                'id_cuenta' => 298,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            73 => 
            array (
                'id' => 86,
                'id_cuenta' => 299,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            74 => 
            array (
                'id' => 87,
                'id_cuenta' => 300,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            75 => 
            array (
                'id' => 88,
                'id_cuenta' => 301,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            76 => 
            array (
                'id' => 89,
                'id_cuenta' => 302,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            77 => 
            array (
                'id' => 90,
                'id_cuenta' => 303,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            78 => 
            array (
                'id' => 91,
                'id_cuenta' => 304,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            79 => 
            array (
                'id' => 92,
                'id_cuenta' => 305,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            80 => 
            array (
                'id' => 93,
                'id_cuenta' => 306,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            81 => 
            array (
                'id' => 94,
                'id_cuenta' => 307,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            82 => 
            array (
                'id' => 95,
                'id_cuenta' => 308,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            83 => 
            array (
                'id' => 96,
                'id_cuenta' => 309,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            84 => 
            array (
                'id' => 97,
                'id_cuenta' => 310,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            85 => 
            array (
                'id' => 98,
                'id_cuenta' => 311,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            86 => 
            array (
                'id' => 99,
                'id_cuenta' => 312,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            87 => 
            array (
                'id' => 100,
                'id_cuenta' => 313,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            88 => 
            array (
                'id' => 101,
                'id_cuenta' => 314,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            89 => 
            array (
                'id' => 102,
                'id_cuenta' => 315,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            90 => 
            array (
                'id' => 103,
                'id_cuenta' => 316,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            91 => 
            array (
                'id' => 104,
                'id_cuenta' => 317,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            92 => 
            array (
                'id' => 105,
                'id_cuenta' => 318,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            93 => 
            array (
                'id' => 106,
                'id_cuenta' => 319,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            94 => 
            array (
                'id' => 107,
                'id_cuenta' => 320,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            95 => 
            array (
                'id' => 108,
                'id_cuenta' => 321,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            96 => 
            array (
                'id' => 109,
                'id_cuenta' => 322,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            97 => 
            array (
                'id' => 110,
                'id_cuenta' => 323,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            98 => 
            array (
                'id' => 111,
                'id_cuenta' => 324,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            99 => 
            array (
                'id' => 112,
                'id_cuenta' => 325,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            100 => 
            array (
                'id' => 113,
                'id_cuenta' => 326,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            101 => 
            array (
                'id' => 114,
                'id_cuenta' => 327,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            102 => 
            array (
                'id' => 115,
                'id_cuenta' => 328,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            103 => 
            array (
                'id' => 116,
                'id_cuenta' => 329,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            104 => 
            array (
                'id' => 117,
                'id_cuenta' => 330,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            105 => 
            array (
                'id' => 118,
                'id_cuenta' => 331,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            106 => 
            array (
                'id' => 119,
                'id_cuenta' => 332,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            107 => 
            array (
                'id' => 120,
                'id_cuenta' => 333,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            108 => 
            array (
                'id' => 121,
                'id_cuenta' => 334,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            109 => 
            array (
                'id' => 122,
                'id_cuenta' => 335,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            110 => 
            array (
                'id' => 123,
                'id_cuenta' => 336,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            111 => 
            array (
                'id' => 124,
                'id_cuenta' => 337,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            112 => 
            array (
                'id' => 125,
                'id_cuenta' => 338,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            113 => 
            array (
                'id' => 126,
                'id_cuenta' => 339,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            114 => 
            array (
                'id' => 127,
                'id_cuenta' => 340,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            115 => 
            array (
                'id' => 128,
                'id_cuenta' => 341,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            116 => 
            array (
                'id' => 129,
                'id_cuenta' => 342,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            117 => 
            array (
                'id' => 130,
                'id_cuenta' => 343,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            118 => 
            array (
                'id' => 131,
                'id_cuenta' => 344,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            119 => 
            array (
                'id' => 132,
                'id_cuenta' => 345,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            120 => 
            array (
                'id' => 133,
                'id_cuenta' => 346,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            121 => 
            array (
                'id' => 134,
                'id_cuenta' => 347,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            122 => 
            array (
                'id' => 135,
                'id_cuenta' => 348,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            123 => 
            array (
                'id' => 136,
                'id_cuenta' => 349,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            124 => 
            array (
                'id' => 137,
                'id_cuenta' => 350,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            125 => 
            array (
                'id' => 138,
                'id_cuenta' => 351,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            126 => 
            array (
                'id' => 139,
                'id_cuenta' => 352,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            127 => 
            array (
                'id' => 140,
                'id_cuenta' => 353,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            128 => 
            array (
                'id' => 141,
                'id_cuenta' => 354,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            129 => 
            array (
                'id' => 142,
                'id_cuenta' => 355,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            130 => 
            array (
                'id' => 143,
                'id_cuenta' => 356,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            131 => 
            array (
                'id' => 144,
                'id_cuenta' => 357,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            132 => 
            array (
                'id' => 145,
                'id_cuenta' => 358,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            133 => 
            array (
                'id' => 146,
                'id_cuenta' => 359,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            134 => 
            array (
                'id' => 147,
                'id_cuenta' => 360,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            135 => 
            array (
                'id' => 148,
                'id_cuenta' => 361,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            136 => 
            array (
                'id' => 149,
                'id_cuenta' => 362,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            137 => 
            array (
                'id' => 150,
                'id_cuenta' => 363,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            138 => 
            array (
                'id' => 151,
                'id_cuenta' => 364,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            139 => 
            array (
                'id' => 152,
                'id_cuenta' => 365,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            140 => 
            array (
                'id' => 153,
                'id_cuenta' => 366,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            141 => 
            array (
                'id' => 154,
                'id_cuenta' => 367,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            142 => 
            array (
                'id' => 155,
                'id_cuenta' => 368,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            143 => 
            array (
                'id' => 156,
                'id_cuenta' => 369,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            144 => 
            array (
                'id' => 157,
                'id_cuenta' => 370,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            145 => 
            array (
                'id' => 158,
                'id_cuenta' => 371,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            146 => 
            array (
                'id' => 159,
                'id_cuenta' => 372,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            147 => 
            array (
                'id' => 160,
                'id_cuenta' => 373,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            148 => 
            array (
                'id' => 162,
                'id_cuenta' => 375,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            149 => 
            array (
                'id' => 163,
                'id_cuenta' => 376,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            150 => 
            array (
                'id' => 164,
                'id_cuenta' => 377,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            151 => 
            array (
                'id' => 165,
                'id_cuenta' => 378,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            152 => 
            array (
                'id' => 166,
                'id_cuenta' => 379,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            153 => 
            array (
                'id' => 167,
                'id_cuenta' => 380,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            154 => 
            array (
                'id' => 168,
                'id_cuenta' => 381,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            155 => 
            array (
                'id' => 169,
                'id_cuenta' => 382,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            156 => 
            array (
                'id' => 170,
                'id_cuenta' => 383,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            157 => 
            array (
                'id' => 171,
                'id_cuenta' => 384,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            158 => 
            array (
                'id' => 172,
                'id_cuenta' => 385,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            159 => 
            array (
                'id' => 173,
                'id_cuenta' => 386,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            160 => 
            array (
                'id' => 174,
                'id_cuenta' => 387,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            161 => 
            array (
                'id' => 175,
                'id_cuenta' => 388,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            162 => 
            array (
                'id' => 176,
                'id_cuenta' => 389,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            163 => 
            array (
                'id' => 177,
                'id_cuenta' => 390,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            164 => 
            array (
                'id' => 178,
                'id_cuenta' => 391,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            165 => 
            array (
                'id' => 179,
                'id_cuenta' => 392,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            166 => 
            array (
                'id' => 180,
                'id_cuenta' => 393,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            167 => 
            array (
                'id' => 181,
                'id_cuenta' => 394,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            168 => 
            array (
                'id' => 182,
                'id_cuenta' => 395,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            169 => 
            array (
                'id' => 183,
                'id_cuenta' => 396,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            170 => 
            array (
                'id' => 184,
                'id_cuenta' => 397,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            171 => 
            array (
                'id' => 185,
                'id_cuenta' => 398,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            172 => 
            array (
                'id' => 186,
                'id_cuenta' => 399,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            173 => 
            array (
                'id' => 187,
                'id_cuenta' => 400,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:42:39',
                'updated_at' => '2023-12-22 10:42:39',
            ),
            174 => 
            array (
                'id' => 188,
                'id_cuenta' => 401,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            175 => 
            array (
                'id' => 189,
                'id_cuenta' => 402,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            176 => 
            array (
                'id' => 190,
                'id_cuenta' => 403,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            177 => 
            array (
                'id' => 191,
                'id_cuenta' => 404,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            178 => 
            array (
                'id' => 192,
                'id_cuenta' => 405,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            179 => 
            array (
                'id' => 193,
                'id_cuenta' => 406,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            180 => 
            array (
                'id' => 194,
                'id_cuenta' => 407,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            181 => 
            array (
                'id' => 195,
                'id_cuenta' => 408,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            182 => 
            array (
                'id' => 196,
                'id_cuenta' => 409,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            183 => 
            array (
                'id' => 197,
                'id_cuenta' => 410,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            184 => 
            array (
                'id' => 198,
                'id_cuenta' => 411,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            185 => 
            array (
                'id' => 199,
                'id_cuenta' => 412,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            186 => 
            array (
                'id' => 200,
                'id_cuenta' => 413,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            187 => 
            array (
                'id' => 201,
                'id_cuenta' => 414,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            188 => 
            array (
                'id' => 202,
                'id_cuenta' => 415,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            189 => 
            array (
                'id' => 203,
                'id_cuenta' => 416,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            190 => 
            array (
                'id' => 204,
                'id_cuenta' => 417,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            191 => 
            array (
                'id' => 205,
                'id_cuenta' => 418,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            192 => 
            array (
                'id' => 206,
                'id_cuenta' => 419,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            193 => 
            array (
                'id' => 207,
                'id_cuenta' => 420,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            194 => 
            array (
                'id' => 208,
                'id_cuenta' => 421,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            195 => 
            array (
                'id' => 209,
                'id_cuenta' => 422,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            196 => 
            array (
                'id' => 210,
                'id_cuenta' => 423,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            197 => 
            array (
                'id' => 211,
                'id_cuenta' => 424,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            198 => 
            array (
                'id' => 212,
                'id_cuenta' => 425,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            199 => 
            array (
                'id' => 213,
                'id_cuenta' => 426,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            200 => 
            array (
                'id' => 214,
                'id_cuenta' => 427,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            201 => 
            array (
                'id' => 215,
                'id_cuenta' => 428,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            202 => 
            array (
                'id' => 216,
                'id_cuenta' => 429,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            203 => 
            array (
                'id' => 217,
                'id_cuenta' => 430,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            204 => 
            array (
                'id' => 218,
                'id_cuenta' => 431,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            205 => 
            array (
                'id' => 219,
                'id_cuenta' => 432,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            206 => 
            array (
                'id' => 220,
                'id_cuenta' => 433,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            207 => 
            array (
                'id' => 221,
                'id_cuenta' => 434,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            208 => 
            array (
                'id' => 222,
                'id_cuenta' => 435,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            209 => 
            array (
                'id' => 223,
                'id_cuenta' => 436,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            210 => 
            array (
                'id' => 224,
                'id_cuenta' => 437,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            211 => 
            array (
                'id' => 225,
                'id_cuenta' => 438,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            212 => 
            array (
                'id' => 226,
                'id_cuenta' => 439,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            213 => 
            array (
                'id' => 227,
                'id_cuenta' => 440,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            214 => 
            array (
                'id' => 228,
                'id_cuenta' => 441,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            215 => 
            array (
                'id' => 229,
                'id_cuenta' => 442,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            216 => 
            array (
                'id' => 230,
                'id_cuenta' => 443,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            217 => 
            array (
                'id' => 231,
                'id_cuenta' => 444,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            218 => 
            array (
                'id' => 232,
                'id_cuenta' => 445,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            219 => 
            array (
                'id' => 233,
                'id_cuenta' => 446,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            220 => 
            array (
                'id' => 234,
                'id_cuenta' => 447,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            221 => 
            array (
                'id' => 235,
                'id_cuenta' => 448,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:22',
                'updated_at' => '2023-12-22 10:47:22',
            ),
            222 => 
            array (
                'id' => 236,
                'id_cuenta' => 449,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            223 => 
            array (
                'id' => 237,
                'id_cuenta' => 450,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            224 => 
            array (
                'id' => 238,
                'id_cuenta' => 451,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            225 => 
            array (
                'id' => 239,
                'id_cuenta' => 452,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            226 => 
            array (
                'id' => 240,
                'id_cuenta' => 453,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            227 => 
            array (
                'id' => 241,
                'id_cuenta' => 454,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            228 => 
            array (
                'id' => 242,
                'id_cuenta' => 455,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            229 => 
            array (
                'id' => 243,
                'id_cuenta' => 456,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            230 => 
            array (
                'id' => 244,
                'id_cuenta' => 457,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            231 => 
            array (
                'id' => 245,
                'id_cuenta' => 458,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            232 => 
            array (
                'id' => 246,
                'id_cuenta' => 459,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            233 => 
            array (
                'id' => 247,
                'id_cuenta' => 460,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            234 => 
            array (
                'id' => 248,
                'id_cuenta' => 461,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            235 => 
            array (
                'id' => 249,
                'id_cuenta' => 462,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            236 => 
            array (
                'id' => 250,
                'id_cuenta' => 463,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            237 => 
            array (
                'id' => 251,
                'id_cuenta' => 464,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            238 => 
            array (
                'id' => 252,
                'id_cuenta' => 465,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            239 => 
            array (
                'id' => 253,
                'id_cuenta' => 466,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            240 => 
            array (
                'id' => 254,
                'id_cuenta' => 467,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            241 => 
            array (
                'id' => 255,
                'id_cuenta' => 468,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            242 => 
            array (
                'id' => 256,
                'id_cuenta' => 469,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            243 => 
            array (
                'id' => 257,
                'id_cuenta' => 470,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            244 => 
            array (
                'id' => 258,
                'id_cuenta' => 471,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            245 => 
            array (
                'id' => 259,
                'id_cuenta' => 472,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            246 => 
            array (
                'id' => 260,
                'id_cuenta' => 473,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            247 => 
            array (
                'id' => 261,
                'id_cuenta' => 474,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            248 => 
            array (
                'id' => 262,
                'id_cuenta' => 475,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            249 => 
            array (
                'id' => 263,
                'id_cuenta' => 476,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            250 => 
            array (
                'id' => 264,
                'id_cuenta' => 477,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            251 => 
            array (
                'id' => 265,
                'id_cuenta' => 478,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            252 => 
            array (
                'id' => 266,
                'id_cuenta' => 479,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            253 => 
            array (
                'id' => 267,
                'id_cuenta' => 480,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            254 => 
            array (
                'id' => 268,
                'id_cuenta' => 481,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            255 => 
            array (
                'id' => 269,
                'id_cuenta' => 482,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            256 => 
            array (
                'id' => 270,
                'id_cuenta' => 483,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            257 => 
            array (
                'id' => 271,
                'id_cuenta' => 484,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            258 => 
            array (
                'id' => 272,
                'id_cuenta' => 485,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            259 => 
            array (
                'id' => 273,
                'id_cuenta' => 486,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            260 => 
            array (
                'id' => 274,
                'id_cuenta' => 487,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            261 => 
            array (
                'id' => 275,
                'id_cuenta' => 488,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            262 => 
            array (
                'id' => 276,
                'id_cuenta' => 489,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            263 => 
            array (
                'id' => 277,
                'id_cuenta' => 490,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            264 => 
            array (
                'id' => 278,
                'id_cuenta' => 491,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            265 => 
            array (
                'id' => 279,
                'id_cuenta' => 492,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            266 => 
            array (
                'id' => 280,
                'id_cuenta' => 493,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            267 => 
            array (
                'id' => 281,
                'id_cuenta' => 494,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            268 => 
            array (
                'id' => 282,
                'id_cuenta' => 495,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            269 => 
            array (
                'id' => 283,
                'id_cuenta' => 496,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            270 => 
            array (
                'id' => 284,
                'id_cuenta' => 497,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            271 => 
            array (
                'id' => 285,
                'id_cuenta' => 498,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            272 => 
            array (
                'id' => 286,
                'id_cuenta' => 499,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            273 => 
            array (
                'id' => 287,
                'id_cuenta' => 500,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            274 => 
            array (
                'id' => 288,
                'id_cuenta' => 501,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            275 => 
            array (
                'id' => 289,
                'id_cuenta' => 502,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            276 => 
            array (
                'id' => 290,
                'id_cuenta' => 503,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            277 => 
            array (
                'id' => 291,
                'id_cuenta' => 504,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            278 => 
            array (
                'id' => 292,
                'id_cuenta' => 505,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            279 => 
            array (
                'id' => 293,
                'id_cuenta' => 506,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            280 => 
            array (
                'id' => 294,
                'id_cuenta' => 507,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            281 => 
            array (
                'id' => 295,
                'id_cuenta' => 508,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            282 => 
            array (
                'id' => 296,
                'id_cuenta' => 509,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            283 => 
            array (
                'id' => 297,
                'id_cuenta' => 510,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            284 => 
            array (
                'id' => 298,
                'id_cuenta' => 511,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            285 => 
            array (
                'id' => 299,
                'id_cuenta' => 512,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            286 => 
            array (
                'id' => 300,
                'id_cuenta' => 513,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            287 => 
            array (
                'id' => 301,
                'id_cuenta' => 514,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            288 => 
            array (
                'id' => 302,
                'id_cuenta' => 515,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            289 => 
            array (
                'id' => 303,
                'id_cuenta' => 516,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            290 => 
            array (
                'id' => 304,
                'id_cuenta' => 517,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            291 => 
            array (
                'id' => 305,
                'id_cuenta' => 518,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            292 => 
            array (
                'id' => 306,
                'id_cuenta' => 519,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            293 => 
            array (
                'id' => 307,
                'id_cuenta' => 520,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            294 => 
            array (
                'id' => 308,
                'id_cuenta' => 521,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            295 => 
            array (
                'id' => 309,
                'id_cuenta' => 522,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            296 => 
            array (
                'id' => 310,
                'id_cuenta' => 523,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            297 => 
            array (
                'id' => 311,
                'id_cuenta' => 524,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            298 => 
            array (
                'id' => 312,
                'id_cuenta' => 525,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            299 => 
            array (
                'id' => 313,
                'id_cuenta' => 526,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            300 => 
            array (
                'id' => 314,
                'id_cuenta' => 527,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            301 => 
            array (
                'id' => 315,
                'id_cuenta' => 528,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            302 => 
            array (
                'id' => 316,
                'id_cuenta' => 529,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            303 => 
            array (
                'id' => 317,
                'id_cuenta' => 530,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            304 => 
            array (
                'id' => 318,
                'id_cuenta' => 531,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            305 => 
            array (
                'id' => 319,
                'id_cuenta' => 532,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            306 => 
            array (
                'id' => 320,
                'id_cuenta' => 533,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            307 => 
            array (
                'id' => 321,
                'id_cuenta' => 534,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            308 => 
            array (
                'id' => 322,
                'id_cuenta' => 535,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            309 => 
            array (
                'id' => 323,
                'id_cuenta' => 536,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            310 => 
            array (
                'id' => 324,
                'id_cuenta' => 537,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            311 => 
            array (
                'id' => 325,
                'id_cuenta' => 538,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            312 => 
            array (
                'id' => 326,
                'id_cuenta' => 539,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            313 => 
            array (
                'id' => 327,
                'id_cuenta' => 540,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            314 => 
            array (
                'id' => 328,
                'id_cuenta' => 541,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            315 => 
            array (
                'id' => 329,
                'id_cuenta' => 542,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            316 => 
            array (
                'id' => 330,
                'id_cuenta' => 543,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            317 => 
            array (
                'id' => 331,
                'id_cuenta' => 544,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            318 => 
            array (
                'id' => 332,
                'id_cuenta' => 545,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            319 => 
            array (
                'id' => 333,
                'id_cuenta' => 546,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            320 => 
            array (
                'id' => 334,
                'id_cuenta' => 547,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            321 => 
            array (
                'id' => 335,
                'id_cuenta' => 548,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            322 => 
            array (
                'id' => 336,
                'id_cuenta' => 549,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            323 => 
            array (
                'id' => 337,
                'id_cuenta' => 550,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            324 => 
            array (
                'id' => 338,
                'id_cuenta' => 551,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            325 => 
            array (
                'id' => 339,
                'id_cuenta' => 552,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            326 => 
            array (
                'id' => 340,
                'id_cuenta' => 553,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:23',
                'updated_at' => '2023-12-22 10:47:23',
            ),
            327 => 
            array (
                'id' => 341,
                'id_cuenta' => 554,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            328 => 
            array (
                'id' => 342,
                'id_cuenta' => 555,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            329 => 
            array (
                'id' => 343,
                'id_cuenta' => 556,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            330 => 
            array (
                'id' => 344,
                'id_cuenta' => 557,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            331 => 
            array (
                'id' => 345,
                'id_cuenta' => 558,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            332 => 
            array (
                'id' => 346,
                'id_cuenta' => 559,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            333 => 
            array (
                'id' => 347,
                'id_cuenta' => 560,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            334 => 
            array (
                'id' => 348,
                'id_cuenta' => 561,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            335 => 
            array (
                'id' => 349,
                'id_cuenta' => 562,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            336 => 
            array (
                'id' => 350,
                'id_cuenta' => 563,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            337 => 
            array (
                'id' => 351,
                'id_cuenta' => 564,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            338 => 
            array (
                'id' => 352,
                'id_cuenta' => 565,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            339 => 
            array (
                'id' => 353,
                'id_cuenta' => 566,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            340 => 
            array (
                'id' => 354,
                'id_cuenta' => 567,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            341 => 
            array (
                'id' => 355,
                'id_cuenta' => 568,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            342 => 
            array (
                'id' => 356,
                'id_cuenta' => 569,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            343 => 
            array (
                'id' => 357,
                'id_cuenta' => 570,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            344 => 
            array (
                'id' => 358,
                'id_cuenta' => 571,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            345 => 
            array (
                'id' => 359,
                'id_cuenta' => 572,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            346 => 
            array (
                'id' => 360,
                'id_cuenta' => 573,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            347 => 
            array (
                'id' => 361,
                'id_cuenta' => 574,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            348 => 
            array (
                'id' => 362,
                'id_cuenta' => 575,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            349 => 
            array (
                'id' => 363,
                'id_cuenta' => 576,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            350 => 
            array (
                'id' => 364,
                'id_cuenta' => 577,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            351 => 
            array (
                'id' => 365,
                'id_cuenta' => 578,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            352 => 
            array (
                'id' => 366,
                'id_cuenta' => 579,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            353 => 
            array (
                'id' => 367,
                'id_cuenta' => 580,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            354 => 
            array (
                'id' => 368,
                'id_cuenta' => 581,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            355 => 
            array (
                'id' => 369,
                'id_cuenta' => 582,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            356 => 
            array (
                'id' => 370,
                'id_cuenta' => 583,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            357 => 
            array (
                'id' => 371,
                'id_cuenta' => 584,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            358 => 
            array (
                'id' => 372,
                'id_cuenta' => 585,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            359 => 
            array (
                'id' => 373,
                'id_cuenta' => 586,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            360 => 
            array (
                'id' => 374,
                'id_cuenta' => 587,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            361 => 
            array (
                'id' => 375,
                'id_cuenta' => 588,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            362 => 
            array (
                'id' => 376,
                'id_cuenta' => 589,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            363 => 
            array (
                'id' => 377,
                'id_cuenta' => 590,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            364 => 
            array (
                'id' => 378,
                'id_cuenta' => 591,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            365 => 
            array (
                'id' => 379,
                'id_cuenta' => 592,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            366 => 
            array (
                'id' => 380,
                'id_cuenta' => 593,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            367 => 
            array (
                'id' => 381,
                'id_cuenta' => 594,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            368 => 
            array (
                'id' => 382,
                'id_cuenta' => 595,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            369 => 
            array (
                'id' => 383,
                'id_cuenta' => 596,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            370 => 
            array (
                'id' => 384,
                'id_cuenta' => 597,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            371 => 
            array (
                'id' => 385,
                'id_cuenta' => 598,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            372 => 
            array (
                'id' => 386,
                'id_cuenta' => 599,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            373 => 
            array (
                'id' => 387,
                'id_cuenta' => 600,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            374 => 
            array (
                'id' => 388,
                'id_cuenta' => 601,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            375 => 
            array (
                'id' => 389,
                'id_cuenta' => 602,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            376 => 
            array (
                'id' => 390,
                'id_cuenta' => 603,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            377 => 
            array (
                'id' => 391,
                'id_cuenta' => 604,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            378 => 
            array (
                'id' => 392,
                'id_cuenta' => 605,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            379 => 
            array (
                'id' => 393,
                'id_cuenta' => 606,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            380 => 
            array (
                'id' => 394,
                'id_cuenta' => 607,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            381 => 
            array (
                'id' => 395,
                'id_cuenta' => 608,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            382 => 
            array (
                'id' => 396,
                'id_cuenta' => 609,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            383 => 
            array (
                'id' => 397,
                'id_cuenta' => 610,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:47:24',
                'updated_at' => '2023-12-22 10:47:24',
            ),
            384 => 
            array (
                'id' => 398,
                'id_cuenta' => 753,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            385 => 
            array (
                'id' => 399,
                'id_cuenta' => 754,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            386 => 
            array (
                'id' => 400,
                'id_cuenta' => 755,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            387 => 
            array (
                'id' => 401,
                'id_cuenta' => 756,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            388 => 
            array (
                'id' => 402,
                'id_cuenta' => 757,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            389 => 
            array (
                'id' => 403,
                'id_cuenta' => 758,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            390 => 
            array (
                'id' => 404,
                'id_cuenta' => 759,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            391 => 
            array (
                'id' => 405,
                'id_cuenta' => 760,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            392 => 
            array (
                'id' => 406,
                'id_cuenta' => 761,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            393 => 
            array (
                'id' => 407,
                'id_cuenta' => 762,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            394 => 
            array (
                'id' => 408,
                'id_cuenta' => 763,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            395 => 
            array (
                'id' => 409,
                'id_cuenta' => 764,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            396 => 
            array (
                'id' => 410,
                'id_cuenta' => 765,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            397 => 
            array (
                'id' => 411,
                'id_cuenta' => 766,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            398 => 
            array (
                'id' => 412,
                'id_cuenta' => 767,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            399 => 
            array (
                'id' => 413,
                'id_cuenta' => 768,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            400 => 
            array (
                'id' => 414,
                'id_cuenta' => 769,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            401 => 
            array (
                'id' => 415,
                'id_cuenta' => 770,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            402 => 
            array (
                'id' => 416,
                'id_cuenta' => 771,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            403 => 
            array (
                'id' => 417,
                'id_cuenta' => 772,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            404 => 
            array (
                'id' => 418,
                'id_cuenta' => 773,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            405 => 
            array (
                'id' => 419,
                'id_cuenta' => 774,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            406 => 
            array (
                'id' => 420,
                'id_cuenta' => 775,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            407 => 
            array (
                'id' => 421,
                'id_cuenta' => 776,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            408 => 
            array (
                'id' => 422,
                'id_cuenta' => 777,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            409 => 
            array (
                'id' => 423,
                'id_cuenta' => 778,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            410 => 
            array (
                'id' => 424,
                'id_cuenta' => 779,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            411 => 
            array (
                'id' => 425,
                'id_cuenta' => 780,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            412 => 
            array (
                'id' => 426,
                'id_cuenta' => 781,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            413 => 
            array (
                'id' => 427,
                'id_cuenta' => 782,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            414 => 
            array (
                'id' => 428,
                'id_cuenta' => 783,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            415 => 
            array (
                'id' => 429,
                'id_cuenta' => 784,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            416 => 
            array (
                'id' => 430,
                'id_cuenta' => 785,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            417 => 
            array (
                'id' => 431,
                'id_cuenta' => 786,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            418 => 
            array (
                'id' => 432,
                'id_cuenta' => 787,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            419 => 
            array (
                'id' => 433,
                'id_cuenta' => 788,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            420 => 
            array (
                'id' => 434,
                'id_cuenta' => 789,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            421 => 
            array (
                'id' => 435,
                'id_cuenta' => 790,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            422 => 
            array (
                'id' => 436,
                'id_cuenta' => 791,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            423 => 
            array (
                'id' => 437,
                'id_cuenta' => 792,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            424 => 
            array (
                'id' => 438,
                'id_cuenta' => 793,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            425 => 
            array (
                'id' => 439,
                'id_cuenta' => 794,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            426 => 
            array (
                'id' => 440,
                'id_cuenta' => 795,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            427 => 
            array (
                'id' => 441,
                'id_cuenta' => 796,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            428 => 
            array (
                'id' => 442,
                'id_cuenta' => 797,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            429 => 
            array (
                'id' => 443,
                'id_cuenta' => 798,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            430 => 
            array (
                'id' => 444,
                'id_cuenta' => 799,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:25',
                'updated_at' => '2023-12-22 10:47:25',
            ),
            431 => 
            array (
                'id' => 446,
                'id_cuenta' => 801,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:42',
                'updated_at' => '2023-12-22 10:47:42',
            ),
            432 => 
            array (
                'id' => 448,
                'id_cuenta' => 803,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:42',
                'updated_at' => '2023-12-22 10:47:42',
            ),
            433 => 
            array (
                'id' => 449,
                'id_cuenta' => 804,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:42',
                'updated_at' => '2023-12-22 10:47:42',
            ),
            434 => 
            array (
                'id' => 450,
                'id_cuenta' => 805,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:42',
                'updated_at' => '2023-12-22 10:47:42',
            ),
            435 => 
            array (
                'id' => 451,
                'id_cuenta' => 806,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:42',
                'updated_at' => '2023-12-22 10:47:42',
            ),
            436 => 
            array (
                'id' => 452,
                'id_cuenta' => 807,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:42',
                'updated_at' => '2023-12-22 10:47:42',
            ),
            437 => 
            array (
                'id' => 454,
                'id_cuenta' => 809,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:42',
                'updated_at' => '2023-12-22 10:47:42',
            ),
            438 => 
            array (
                'id' => 455,
                'id_cuenta' => 810,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:42',
                'updated_at' => '2023-12-22 10:47:42',
            ),
            439 => 
            array (
                'id' => 456,
                'id_cuenta' => 811,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:42',
                'updated_at' => '2023-12-22 10:47:42',
            ),
            440 => 
            array (
                'id' => 457,
                'id_cuenta' => 812,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:42',
                'updated_at' => '2023-12-22 10:47:42',
            ),
            441 => 
            array (
                'id' => 458,
                'id_cuenta' => 813,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:42',
                'updated_at' => '2023-12-22 10:47:42',
            ),
            442 => 
            array (
                'id' => 460,
                'id_cuenta' => 815,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            443 => 
            array (
                'id' => 461,
                'id_cuenta' => 816,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            444 => 
            array (
                'id' => 462,
                'id_cuenta' => 817,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            445 => 
            array (
                'id' => 463,
                'id_cuenta' => 818,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            446 => 
            array (
                'id' => 464,
                'id_cuenta' => 819,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            447 => 
            array (
                'id' => 465,
                'id_cuenta' => 820,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            448 => 
            array (
                'id' => 466,
                'id_cuenta' => 821,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            449 => 
            array (
                'id' => 467,
                'id_cuenta' => 822,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            450 => 
            array (
                'id' => 468,
                'id_cuenta' => 823,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            451 => 
            array (
                'id' => 469,
                'id_cuenta' => 824,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            452 => 
            array (
                'id' => 470,
                'id_cuenta' => 825,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            453 => 
            array (
                'id' => 471,
                'id_cuenta' => 826,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            454 => 
            array (
                'id' => 472,
                'id_cuenta' => 827,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            455 => 
            array (
                'id' => 473,
                'id_cuenta' => 828,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            456 => 
            array (
                'id' => 474,
                'id_cuenta' => 829,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            457 => 
            array (
                'id' => 475,
                'id_cuenta' => 830,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            458 => 
            array (
                'id' => 476,
                'id_cuenta' => 831,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            459 => 
            array (
                'id' => 477,
                'id_cuenta' => 832,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            460 => 
            array (
                'id' => 478,
                'id_cuenta' => 833,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            461 => 
            array (
                'id' => 479,
                'id_cuenta' => 834,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            462 => 
            array (
                'id' => 482,
                'id_cuenta' => 837,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            463 => 
            array (
                'id' => 483,
                'id_cuenta' => 838,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            464 => 
            array (
                'id' => 484,
                'id_cuenta' => 839,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            465 => 
            array (
                'id' => 486,
                'id_cuenta' => 841,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            466 => 
            array (
                'id' => 487,
                'id_cuenta' => 842,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            467 => 
            array (
                'id' => 488,
                'id_cuenta' => 843,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            468 => 
            array (
                'id' => 489,
                'id_cuenta' => 844,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            469 => 
            array (
                'id' => 490,
                'id_cuenta' => 845,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            470 => 
            array (
                'id' => 491,
                'id_cuenta' => 846,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            471 => 
            array (
                'id' => 492,
                'id_cuenta' => 847,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            472 => 
            array (
                'id' => 493,
                'id_cuenta' => 848,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            473 => 
            array (
                'id' => 494,
                'id_cuenta' => 849,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            474 => 
            array (
                'id' => 495,
                'id_cuenta' => 850,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            475 => 
            array (
                'id' => 496,
                'id_cuenta' => 851,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            476 => 
            array (
                'id' => 497,
                'id_cuenta' => 852,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            477 => 
            array (
                'id' => 498,
                'id_cuenta' => 853,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            478 => 
            array (
                'id' => 499,
                'id_cuenta' => 854,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            479 => 
            array (
                'id' => 500,
                'id_cuenta' => 855,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            480 => 
            array (
                'id' => 501,
                'id_cuenta' => 856,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            481 => 
            array (
                'id' => 502,
                'id_cuenta' => 857,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            482 => 
            array (
                'id' => 503,
                'id_cuenta' => 858,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            483 => 
            array (
                'id' => 504,
                'id_cuenta' => 859,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            484 => 
            array (
                'id' => 505,
                'id_cuenta' => 860,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            485 => 
            array (
                'id' => 506,
                'id_cuenta' => 861,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            486 => 
            array (
                'id' => 507,
                'id_cuenta' => 862,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            487 => 
            array (
                'id' => 508,
                'id_cuenta' => 863,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            488 => 
            array (
                'id' => 509,
                'id_cuenta' => 864,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            489 => 
            array (
                'id' => 510,
                'id_cuenta' => 865,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            490 => 
            array (
                'id' => 511,
                'id_cuenta' => 866,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            491 => 
            array (
                'id' => 512,
                'id_cuenta' => 867,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            492 => 
            array (
                'id' => 513,
                'id_cuenta' => 868,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            493 => 
            array (
                'id' => 514,
                'id_cuenta' => 869,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            494 => 
            array (
                'id' => 515,
                'id_cuenta' => 870,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            495 => 
            array (
                'id' => 516,
                'id_cuenta' => 871,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            496 => 
            array (
                'id' => 517,
                'id_cuenta' => 872,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            497 => 
            array (
                'id' => 518,
                'id_cuenta' => 873,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            498 => 
            array (
                'id' => 519,
                'id_cuenta' => 874,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            499 => 
            array (
                'id' => 520,
                'id_cuenta' => 875,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
        ));
        \DB::table('plan_cuentas_tipos')->insert(array (
            0 => 
            array (
                'id' => 521,
                'id_cuenta' => 876,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            1 => 
            array (
                'id' => 522,
                'id_cuenta' => 877,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            2 => 
            array (
                'id' => 523,
                'id_cuenta' => 878,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            3 => 
            array (
                'id' => 524,
                'id_cuenta' => 879,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            4 => 
            array (
                'id' => 525,
                'id_cuenta' => 880,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            5 => 
            array (
                'id' => 526,
                'id_cuenta' => 881,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            6 => 
            array (
                'id' => 527,
                'id_cuenta' => 882,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            7 => 
            array (
                'id' => 528,
                'id_cuenta' => 883,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            8 => 
            array (
                'id' => 529,
                'id_cuenta' => 884,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            9 => 
            array (
                'id' => 530,
                'id_cuenta' => 885,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            10 => 
            array (
                'id' => 531,
                'id_cuenta' => 886,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:43',
                'updated_at' => '2023-12-22 10:47:43',
            ),
            11 => 
            array (
                'id' => 532,
                'id_cuenta' => 887,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            12 => 
            array (
                'id' => 533,
                'id_cuenta' => 888,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            13 => 
            array (
                'id' => 534,
                'id_cuenta' => 889,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            14 => 
            array (
                'id' => 535,
                'id_cuenta' => 890,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            15 => 
            array (
                'id' => 536,
                'id_cuenta' => 891,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            16 => 
            array (
                'id' => 537,
                'id_cuenta' => 892,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            17 => 
            array (
                'id' => 538,
                'id_cuenta' => 893,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            18 => 
            array (
                'id' => 539,
                'id_cuenta' => 894,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            19 => 
            array (
                'id' => 540,
                'id_cuenta' => 895,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            20 => 
            array (
                'id' => 541,
                'id_cuenta' => 896,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            21 => 
            array (
                'id' => 542,
                'id_cuenta' => 897,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            22 => 
            array (
                'id' => 543,
                'id_cuenta' => 898,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            23 => 
            array (
                'id' => 545,
                'id_cuenta' => 905,
                'id_tipo_cuenta' => 16,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            24 => 
            array (
                'id' => 546,
                'id_cuenta' => 906,
                'id_tipo_cuenta' => 16,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            25 => 
            array (
                'id' => 548,
                'id_cuenta' => 908,
                'id_tipo_cuenta' => 16,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            26 => 
            array (
                'id' => 550,
                'id_cuenta' => 910,
                'id_tipo_cuenta' => 16,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            27 => 
            array (
                'id' => 551,
                'id_cuenta' => 911,
                'id_tipo_cuenta' => 16,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            28 => 
            array (
                'id' => 552,
                'id_cuenta' => 912,
                'id_tipo_cuenta' => 16,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            29 => 
            array (
                'id' => 553,
                'id_cuenta' => 913,
                'id_tipo_cuenta' => 16,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            30 => 
            array (
                'id' => 554,
                'id_cuenta' => 914,
                'id_tipo_cuenta' => 16,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            31 => 
            array (
                'id' => 555,
                'id_cuenta' => 915,
                'id_tipo_cuenta' => 16,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            32 => 
            array (
                'id' => 556,
                'id_cuenta' => 916,
                'id_tipo_cuenta' => 16,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            33 => 
            array (
                'id' => 557,
                'id_cuenta' => 917,
                'id_tipo_cuenta' => 16,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            34 => 
            array (
                'id' => 558,
                'id_cuenta' => 918,
                'id_tipo_cuenta' => 16,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            35 => 
            array (
                'id' => 560,
                'id_cuenta' => 920,
                'id_tipo_cuenta' => 9,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            36 => 
            array (
                'id' => 561,
                'id_cuenta' => 921,
                'id_tipo_cuenta' => 9,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            37 => 
            array (
                'id' => 562,
                'id_cuenta' => 922,
                'id_tipo_cuenta' => 9,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            38 => 
            array (
                'id' => 563,
                'id_cuenta' => 923,
                'id_tipo_cuenta' => 9,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            39 => 
            array (
                'id' => 564,
                'id_cuenta' => 924,
                'id_tipo_cuenta' => 9,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            40 => 
            array (
                'id' => 565,
                'id_cuenta' => 925,
                'id_tipo_cuenta' => 9,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            41 => 
            array (
                'id' => 566,
                'id_cuenta' => 926,
                'id_tipo_cuenta' => 9,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            42 => 
            array (
                'id' => 567,
                'id_cuenta' => 927,
                'id_tipo_cuenta' => 9,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            43 => 
            array (
                'id' => 568,
                'id_cuenta' => 928,
                'id_tipo_cuenta' => 9,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            44 => 
            array (
                'id' => 569,
                'id_cuenta' => 929,
                'id_tipo_cuenta' => 9,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            45 => 
            array (
                'id' => 570,
                'id_cuenta' => 930,
                'id_tipo_cuenta' => 9,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            46 => 
            array (
                'id' => 571,
                'id_cuenta' => 931,
                'id_tipo_cuenta' => 9,
                'created_at' => '2023-12-22 10:47:44',
                'updated_at' => '2023-12-22 10:47:44',
            ),
            47 => 
            array (
                'id' => 572,
                'id_cuenta' => 1079,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:45',
                'updated_at' => '2023-12-22 10:47:45',
            ),
            48 => 
            array (
                'id' => 574,
                'id_cuenta' => 1081,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:45',
                'updated_at' => '2023-12-22 10:47:45',
            ),
            49 => 
            array (
                'id' => 575,
                'id_cuenta' => 1082,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:45',
                'updated_at' => '2023-12-22 10:47:45',
            ),
            50 => 
            array (
                'id' => 576,
                'id_cuenta' => 1083,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:45',
                'updated_at' => '2023-12-22 10:47:45',
            ),
            51 => 
            array (
                'id' => 577,
                'id_cuenta' => 1084,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:45',
                'updated_at' => '2023-12-22 10:47:45',
            ),
            52 => 
            array (
                'id' => 578,
                'id_cuenta' => 1085,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:45',
                'updated_at' => '2023-12-22 10:47:45',
            ),
            53 => 
            array (
                'id' => 579,
                'id_cuenta' => 1086,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:45',
                'updated_at' => '2023-12-22 10:47:45',
            ),
            54 => 
            array (
                'id' => 580,
                'id_cuenta' => 1087,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:45',
                'updated_at' => '2023-12-22 10:47:45',
            ),
            55 => 
            array (
                'id' => 581,
                'id_cuenta' => 1088,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:45',
                'updated_at' => '2023-12-22 10:47:45',
            ),
            56 => 
            array (
                'id' => 582,
                'id_cuenta' => 1089,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:45',
                'updated_at' => '2023-12-22 10:47:45',
            ),
            57 => 
            array (
                'id' => 583,
                'id_cuenta' => 1090,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:45',
                'updated_at' => '2023-12-22 10:47:45',
            ),
            58 => 
            array (
                'id' => 584,
                'id_cuenta' => 1091,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:45',
                'updated_at' => '2023-12-22 10:47:45',
            ),
            59 => 
            array (
                'id' => 585,
                'id_cuenta' => 1092,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:45',
                'updated_at' => '2023-12-22 10:47:45',
            ),
            60 => 
            array (
                'id' => 586,
                'id_cuenta' => 1093,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:45',
                'updated_at' => '2023-12-22 10:47:45',
            ),
            61 => 
            array (
                'id' => 587,
                'id_cuenta' => 1094,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:45',
                'updated_at' => '2023-12-22 10:47:45',
            ),
            62 => 
            array (
                'id' => 588,
                'id_cuenta' => 1095,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:45',
                'updated_at' => '2023-12-22 10:47:45',
            ),
            63 => 
            array (
                'id' => 589,
                'id_cuenta' => 1096,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:45',
                'updated_at' => '2023-12-22 10:47:45',
            ),
            64 => 
            array (
                'id' => 590,
                'id_cuenta' => 1097,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:45',
                'updated_at' => '2023-12-22 10:47:45',
            ),
            65 => 
            array (
                'id' => 591,
                'id_cuenta' => 1098,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:46',
                'updated_at' => '2023-12-22 10:47:46',
            ),
            66 => 
            array (
                'id' => 592,
                'id_cuenta' => 1099,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:46',
                'updated_at' => '2023-12-22 10:47:46',
            ),
            67 => 
            array (
                'id' => 593,
                'id_cuenta' => 1100,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:46',
                'updated_at' => '2023-12-22 10:47:46',
            ),
            68 => 
            array (
                'id' => 594,
                'id_cuenta' => 1101,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:46',
                'updated_at' => '2023-12-22 10:47:46',
            ),
            69 => 
            array (
                'id' => 595,
                'id_cuenta' => 1102,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:46',
                'updated_at' => '2023-12-22 10:47:46',
            ),
            70 => 
            array (
                'id' => 596,
                'id_cuenta' => 1103,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:46',
                'updated_at' => '2023-12-22 10:47:46',
            ),
            71 => 
            array (
                'id' => 597,
                'id_cuenta' => 1104,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:46',
                'updated_at' => '2023-12-22 10:47:46',
            ),
            72 => 
            array (
                'id' => 598,
                'id_cuenta' => 1105,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:46',
                'updated_at' => '2023-12-22 10:47:46',
            ),
            73 => 
            array (
                'id' => 599,
                'id_cuenta' => 1106,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:46',
                'updated_at' => '2023-12-22 10:47:46',
            ),
            74 => 
            array (
                'id' => 600,
                'id_cuenta' => 1107,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:46',
                'updated_at' => '2023-12-22 10:47:46',
            ),
            75 => 
            array (
                'id' => 601,
                'id_cuenta' => 1108,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:46',
                'updated_at' => '2023-12-22 10:47:46',
            ),
            76 => 
            array (
                'id' => 602,
                'id_cuenta' => 1109,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:46',
                'updated_at' => '2023-12-22 10:47:46',
            ),
            77 => 
            array (
                'id' => 603,
                'id_cuenta' => 1110,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:46',
                'updated_at' => '2023-12-22 10:47:46',
            ),
            78 => 
            array (
                'id' => 604,
                'id_cuenta' => 1111,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:46',
                'updated_at' => '2023-12-22 10:47:46',
            ),
            79 => 
            array (
                'id' => 605,
                'id_cuenta' => 1112,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-22 10:47:46',
                'updated_at' => '2023-12-22 10:47:46',
            ),
            80 => 
            array (
                'id' => 606,
                'id_cuenta' => 1233,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            81 => 
            array (
                'id' => 608,
                'id_cuenta' => 1235,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            82 => 
            array (
                'id' => 609,
                'id_cuenta' => 1236,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            83 => 
            array (
                'id' => 610,
                'id_cuenta' => 1237,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            84 => 
            array (
                'id' => 611,
                'id_cuenta' => 1238,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            85 => 
            array (
                'id' => 612,
                'id_cuenta' => 1239,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            86 => 
            array (
                'id' => 613,
                'id_cuenta' => 1240,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            87 => 
            array (
                'id' => 614,
                'id_cuenta' => 1241,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            88 => 
            array (
                'id' => 615,
                'id_cuenta' => 1242,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            89 => 
            array (
                'id' => 616,
                'id_cuenta' => 1243,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            90 => 
            array (
                'id' => 617,
                'id_cuenta' => 1244,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            91 => 
            array (
                'id' => 618,
                'id_cuenta' => 1245,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            92 => 
            array (
                'id' => 619,
                'id_cuenta' => 1246,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            93 => 
            array (
                'id' => 620,
                'id_cuenta' => 1247,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            94 => 
            array (
                'id' => 621,
                'id_cuenta' => 1248,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            95 => 
            array (
                'id' => 622,
                'id_cuenta' => 1249,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            96 => 
            array (
                'id' => 623,
                'id_cuenta' => 1250,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            97 => 
            array (
                'id' => 624,
                'id_cuenta' => 1251,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            98 => 
            array (
                'id' => 625,
                'id_cuenta' => 1252,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            99 => 
            array (
                'id' => 626,
                'id_cuenta' => 1253,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            100 => 
            array (
                'id' => 627,
                'id_cuenta' => 1254,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            101 => 
            array (
                'id' => 628,
                'id_cuenta' => 1255,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            102 => 
            array (
                'id' => 629,
                'id_cuenta' => 1256,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            103 => 
            array (
                'id' => 630,
                'id_cuenta' => 1257,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            104 => 
            array (
                'id' => 631,
                'id_cuenta' => 1258,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            105 => 
            array (
                'id' => 632,
                'id_cuenta' => 1259,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            106 => 
            array (
                'id' => 633,
                'id_cuenta' => 1260,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            107 => 
            array (
                'id' => 634,
                'id_cuenta' => 1261,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            108 => 
            array (
                'id' => 635,
                'id_cuenta' => 1262,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            109 => 
            array (
                'id' => 636,
                'id_cuenta' => 1263,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            110 => 
            array (
                'id' => 637,
                'id_cuenta' => 1264,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            111 => 
            array (
                'id' => 638,
                'id_cuenta' => 1265,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            112 => 
            array (
                'id' => 639,
                'id_cuenta' => 1266,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            113 => 
            array (
                'id' => 640,
                'id_cuenta' => 1267,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            114 => 
            array (
                'id' => 641,
                'id_cuenta' => 1268,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            115 => 
            array (
                'id' => 642,
                'id_cuenta' => 1269,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            116 => 
            array (
                'id' => 643,
                'id_cuenta' => 1270,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            117 => 
            array (
                'id' => 644,
                'id_cuenta' => 1271,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            118 => 
            array (
                'id' => 645,
                'id_cuenta' => 1272,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            119 => 
            array (
                'id' => 646,
                'id_cuenta' => 1273,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            120 => 
            array (
                'id' => 647,
                'id_cuenta' => 1274,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            121 => 
            array (
                'id' => 648,
                'id_cuenta' => 1275,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            122 => 
            array (
                'id' => 649,
                'id_cuenta' => 1276,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            123 => 
            array (
                'id' => 650,
                'id_cuenta' => 1277,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            124 => 
            array (
                'id' => 651,
                'id_cuenta' => 1278,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:05',
                'updated_at' => '2023-12-22 10:48:05',
            ),
            125 => 
            array (
                'id' => 652,
                'id_cuenta' => 1279,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            126 => 
            array (
                'id' => 653,
                'id_cuenta' => 1280,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            127 => 
            array (
                'id' => 654,
                'id_cuenta' => 1281,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            128 => 
            array (
                'id' => 655,
                'id_cuenta' => 1282,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            129 => 
            array (
                'id' => 656,
                'id_cuenta' => 1283,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            130 => 
            array (
                'id' => 657,
                'id_cuenta' => 1284,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            131 => 
            array (
                'id' => 658,
                'id_cuenta' => 1285,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            132 => 
            array (
                'id' => 659,
                'id_cuenta' => 1286,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            133 => 
            array (
                'id' => 660,
                'id_cuenta' => 1287,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            134 => 
            array (
                'id' => 661,
                'id_cuenta' => 1288,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            135 => 
            array (
                'id' => 662,
                'id_cuenta' => 1289,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            136 => 
            array (
                'id' => 663,
                'id_cuenta' => 1290,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            137 => 
            array (
                'id' => 664,
                'id_cuenta' => 1291,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            138 => 
            array (
                'id' => 665,
                'id_cuenta' => 1292,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            139 => 
            array (
                'id' => 666,
                'id_cuenta' => 1293,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            140 => 
            array (
                'id' => 667,
                'id_cuenta' => 1294,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            141 => 
            array (
                'id' => 668,
                'id_cuenta' => 1295,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            142 => 
            array (
                'id' => 669,
                'id_cuenta' => 1296,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            143 => 
            array (
                'id' => 670,
                'id_cuenta' => 1297,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            144 => 
            array (
                'id' => 671,
                'id_cuenta' => 1298,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            145 => 
            array (
                'id' => 672,
                'id_cuenta' => 1299,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            146 => 
            array (
                'id' => 673,
                'id_cuenta' => 1300,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            147 => 
            array (
                'id' => 674,
                'id_cuenta' => 1301,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            148 => 
            array (
                'id' => 675,
                'id_cuenta' => 1302,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            149 => 
            array (
                'id' => 676,
                'id_cuenta' => 1303,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            150 => 
            array (
                'id' => 677,
                'id_cuenta' => 1304,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            151 => 
            array (
                'id' => 678,
                'id_cuenta' => 1305,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            152 => 
            array (
                'id' => 679,
                'id_cuenta' => 1306,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            153 => 
            array (
                'id' => 680,
                'id_cuenta' => 1307,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            154 => 
            array (
                'id' => 681,
                'id_cuenta' => 1308,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            155 => 
            array (
                'id' => 682,
                'id_cuenta' => 1309,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            156 => 
            array (
                'id' => 683,
                'id_cuenta' => 1310,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            157 => 
            array (
                'id' => 684,
                'id_cuenta' => 1311,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            158 => 
            array (
                'id' => 685,
                'id_cuenta' => 1312,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            159 => 
            array (
                'id' => 686,
                'id_cuenta' => 1313,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            160 => 
            array (
                'id' => 687,
                'id_cuenta' => 1314,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            161 => 
            array (
                'id' => 688,
                'id_cuenta' => 1315,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            162 => 
            array (
                'id' => 689,
                'id_cuenta' => 1316,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            163 => 
            array (
                'id' => 690,
                'id_cuenta' => 1317,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            164 => 
            array (
                'id' => 691,
                'id_cuenta' => 1318,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            165 => 
            array (
                'id' => 692,
                'id_cuenta' => 1319,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            166 => 
            array (
                'id' => 693,
                'id_cuenta' => 1320,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            167 => 
            array (
                'id' => 694,
                'id_cuenta' => 1321,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            168 => 
            array (
                'id' => 695,
                'id_cuenta' => 1322,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            169 => 
            array (
                'id' => 696,
                'id_cuenta' => 1323,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            170 => 
            array (
                'id' => 697,
                'id_cuenta' => 1324,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            171 => 
            array (
                'id' => 698,
                'id_cuenta' => 1325,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            172 => 
            array (
                'id' => 699,
                'id_cuenta' => 1326,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            173 => 
            array (
                'id' => 700,
                'id_cuenta' => 1327,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            174 => 
            array (
                'id' => 701,
                'id_cuenta' => 1328,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            175 => 
            array (
                'id' => 702,
                'id_cuenta' => 1329,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            176 => 
            array (
                'id' => 703,
                'id_cuenta' => 1330,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            177 => 
            array (
                'id' => 704,
                'id_cuenta' => 1331,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            178 => 
            array (
                'id' => 705,
                'id_cuenta' => 1332,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            179 => 
            array (
                'id' => 706,
                'id_cuenta' => 1333,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            180 => 
            array (
                'id' => 707,
                'id_cuenta' => 1334,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            181 => 
            array (
                'id' => 708,
                'id_cuenta' => 1335,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            182 => 
            array (
                'id' => 709,
                'id_cuenta' => 1336,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            183 => 
            array (
                'id' => 710,
                'id_cuenta' => 1337,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            184 => 
            array (
                'id' => 711,
                'id_cuenta' => 1338,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            185 => 
            array (
                'id' => 712,
                'id_cuenta' => 1339,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            186 => 
            array (
                'id' => 713,
                'id_cuenta' => 1340,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            187 => 
            array (
                'id' => 714,
                'id_cuenta' => 1341,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            188 => 
            array (
                'id' => 715,
                'id_cuenta' => 1342,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            189 => 
            array (
                'id' => 716,
                'id_cuenta' => 1343,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            190 => 
            array (
                'id' => 717,
                'id_cuenta' => 1344,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            191 => 
            array (
                'id' => 718,
                'id_cuenta' => 1345,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            192 => 
            array (
                'id' => 719,
                'id_cuenta' => 1346,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            193 => 
            array (
                'id' => 720,
                'id_cuenta' => 1347,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            194 => 
            array (
                'id' => 721,
                'id_cuenta' => 1348,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            195 => 
            array (
                'id' => 722,
                'id_cuenta' => 1349,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            196 => 
            array (
                'id' => 723,
                'id_cuenta' => 1350,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            197 => 
            array (
                'id' => 724,
                'id_cuenta' => 1351,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            198 => 
            array (
                'id' => 725,
                'id_cuenta' => 1352,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            199 => 
            array (
                'id' => 726,
                'id_cuenta' => 1353,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            200 => 
            array (
                'id' => 727,
                'id_cuenta' => 1354,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            201 => 
            array (
                'id' => 728,
                'id_cuenta' => 1355,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            202 => 
            array (
                'id' => 729,
                'id_cuenta' => 1356,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            203 => 
            array (
                'id' => 730,
                'id_cuenta' => 1357,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            204 => 
            array (
                'id' => 731,
                'id_cuenta' => 1358,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            205 => 
            array (
                'id' => 732,
                'id_cuenta' => 1359,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            206 => 
            array (
                'id' => 733,
                'id_cuenta' => 1360,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            207 => 
            array (
                'id' => 734,
                'id_cuenta' => 1361,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            208 => 
            array (
                'id' => 735,
                'id_cuenta' => 1362,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            209 => 
            array (
                'id' => 736,
                'id_cuenta' => 1363,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            210 => 
            array (
                'id' => 737,
                'id_cuenta' => 1364,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            211 => 
            array (
                'id' => 738,
                'id_cuenta' => 1365,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            212 => 
            array (
                'id' => 739,
                'id_cuenta' => 1366,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            213 => 
            array (
                'id' => 740,
                'id_cuenta' => 1367,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            214 => 
            array (
                'id' => 741,
                'id_cuenta' => 1368,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            215 => 
            array (
                'id' => 742,
                'id_cuenta' => 1369,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            216 => 
            array (
                'id' => 743,
                'id_cuenta' => 1370,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:06',
                'updated_at' => '2023-12-22 10:48:06',
            ),
            217 => 
            array (
                'id' => 744,
                'id_cuenta' => 1371,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            218 => 
            array (
                'id' => 745,
                'id_cuenta' => 1372,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            219 => 
            array (
                'id' => 746,
                'id_cuenta' => 1373,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            220 => 
            array (
                'id' => 747,
                'id_cuenta' => 1374,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            221 => 
            array (
                'id' => 748,
                'id_cuenta' => 1375,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            222 => 
            array (
                'id' => 749,
                'id_cuenta' => 1376,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            223 => 
            array (
                'id' => 750,
                'id_cuenta' => 1377,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            224 => 
            array (
                'id' => 751,
                'id_cuenta' => 1378,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            225 => 
            array (
                'id' => 752,
                'id_cuenta' => 1379,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            226 => 
            array (
                'id' => 753,
                'id_cuenta' => 1380,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            227 => 
            array (
                'id' => 754,
                'id_cuenta' => 1381,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            228 => 
            array (
                'id' => 755,
                'id_cuenta' => 1382,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            229 => 
            array (
                'id' => 756,
                'id_cuenta' => 1383,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            230 => 
            array (
                'id' => 757,
                'id_cuenta' => 1384,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            231 => 
            array (
                'id' => 758,
                'id_cuenta' => 1385,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            232 => 
            array (
                'id' => 759,
                'id_cuenta' => 1386,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            233 => 
            array (
                'id' => 760,
                'id_cuenta' => 1387,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            234 => 
            array (
                'id' => 761,
                'id_cuenta' => 1388,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            235 => 
            array (
                'id' => 762,
                'id_cuenta' => 1389,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            236 => 
            array (
                'id' => 763,
                'id_cuenta' => 1390,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            237 => 
            array (
                'id' => 764,
                'id_cuenta' => 1391,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            238 => 
            array (
                'id' => 765,
                'id_cuenta' => 1392,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            239 => 
            array (
                'id' => 766,
                'id_cuenta' => 1393,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            240 => 
            array (
                'id' => 767,
                'id_cuenta' => 1394,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            241 => 
            array (
                'id' => 768,
                'id_cuenta' => 1395,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            242 => 
            array (
                'id' => 769,
                'id_cuenta' => 1396,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            243 => 
            array (
                'id' => 770,
                'id_cuenta' => 1397,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            244 => 
            array (
                'id' => 771,
                'id_cuenta' => 1398,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            245 => 
            array (
                'id' => 772,
                'id_cuenta' => 1399,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            246 => 
            array (
                'id' => 773,
                'id_cuenta' => 1400,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            247 => 
            array (
                'id' => 774,
                'id_cuenta' => 1401,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            248 => 
            array (
                'id' => 775,
                'id_cuenta' => 1402,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            249 => 
            array (
                'id' => 776,
                'id_cuenta' => 1403,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            250 => 
            array (
                'id' => 777,
                'id_cuenta' => 1404,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            251 => 
            array (
                'id' => 778,
                'id_cuenta' => 1405,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            252 => 
            array (
                'id' => 779,
                'id_cuenta' => 1406,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            253 => 
            array (
                'id' => 780,
                'id_cuenta' => 1407,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            254 => 
            array (
                'id' => 781,
                'id_cuenta' => 1408,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            255 => 
            array (
                'id' => 782,
                'id_cuenta' => 1409,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            256 => 
            array (
                'id' => 783,
                'id_cuenta' => 1410,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            257 => 
            array (
                'id' => 784,
                'id_cuenta' => 1411,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            258 => 
            array (
                'id' => 785,
                'id_cuenta' => 1412,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            259 => 
            array (
                'id' => 786,
                'id_cuenta' => 1413,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            260 => 
            array (
                'id' => 787,
                'id_cuenta' => 1414,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            261 => 
            array (
                'id' => 788,
                'id_cuenta' => 1415,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            262 => 
            array (
                'id' => 789,
                'id_cuenta' => 1416,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            263 => 
            array (
                'id' => 790,
                'id_cuenta' => 1417,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            264 => 
            array (
                'id' => 791,
                'id_cuenta' => 1418,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            265 => 
            array (
                'id' => 792,
                'id_cuenta' => 1419,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            266 => 
            array (
                'id' => 793,
                'id_cuenta' => 1420,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            267 => 
            array (
                'id' => 794,
                'id_cuenta' => 1421,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            268 => 
            array (
                'id' => 795,
                'id_cuenta' => 1422,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            269 => 
            array (
                'id' => 796,
                'id_cuenta' => 1423,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            270 => 
            array (
                'id' => 797,
                'id_cuenta' => 1424,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            271 => 
            array (
                'id' => 798,
                'id_cuenta' => 1425,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            272 => 
            array (
                'id' => 799,
                'id_cuenta' => 1426,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            273 => 
            array (
                'id' => 800,
                'id_cuenta' => 1427,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            274 => 
            array (
                'id' => 801,
                'id_cuenta' => 1428,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            275 => 
            array (
                'id' => 802,
                'id_cuenta' => 1429,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            276 => 
            array (
                'id' => 803,
                'id_cuenta' => 1430,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            277 => 
            array (
                'id' => 804,
                'id_cuenta' => 1431,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            278 => 
            array (
                'id' => 805,
                'id_cuenta' => 1432,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            279 => 
            array (
                'id' => 806,
                'id_cuenta' => 1433,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            280 => 
            array (
                'id' => 807,
                'id_cuenta' => 1434,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            281 => 
            array (
                'id' => 808,
                'id_cuenta' => 1435,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            282 => 
            array (
                'id' => 809,
                'id_cuenta' => 1436,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            283 => 
            array (
                'id' => 810,
                'id_cuenta' => 1437,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            284 => 
            array (
                'id' => 811,
                'id_cuenta' => 1438,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            285 => 
            array (
                'id' => 812,
                'id_cuenta' => 1439,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            286 => 
            array (
                'id' => 813,
                'id_cuenta' => 1440,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            287 => 
            array (
                'id' => 814,
                'id_cuenta' => 1441,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            288 => 
            array (
                'id' => 815,
                'id_cuenta' => 1442,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            289 => 
            array (
                'id' => 816,
                'id_cuenta' => 1443,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            290 => 
            array (
                'id' => 817,
                'id_cuenta' => 1444,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            291 => 
            array (
                'id' => 818,
                'id_cuenta' => 1445,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            292 => 
            array (
                'id' => 819,
                'id_cuenta' => 1446,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            293 => 
            array (
                'id' => 820,
                'id_cuenta' => 1447,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            294 => 
            array (
                'id' => 821,
                'id_cuenta' => 1448,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            295 => 
            array (
                'id' => 822,
                'id_cuenta' => 1449,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            296 => 
            array (
                'id' => 823,
                'id_cuenta' => 1450,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            297 => 
            array (
                'id' => 824,
                'id_cuenta' => 1451,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            298 => 
            array (
                'id' => 825,
                'id_cuenta' => 1452,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            299 => 
            array (
                'id' => 826,
                'id_cuenta' => 1453,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            300 => 
            array (
                'id' => 827,
                'id_cuenta' => 1454,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            301 => 
            array (
                'id' => 828,
                'id_cuenta' => 1455,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            302 => 
            array (
                'id' => 829,
                'id_cuenta' => 1456,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            303 => 
            array (
                'id' => 830,
                'id_cuenta' => 1457,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            304 => 
            array (
                'id' => 831,
                'id_cuenta' => 1458,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            305 => 
            array (
                'id' => 832,
                'id_cuenta' => 1459,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            306 => 
            array (
                'id' => 833,
                'id_cuenta' => 1460,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            307 => 
            array (
                'id' => 834,
                'id_cuenta' => 1461,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            308 => 
            array (
                'id' => 835,
                'id_cuenta' => 1462,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            309 => 
            array (
                'id' => 836,
                'id_cuenta' => 1463,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            310 => 
            array (
                'id' => 837,
                'id_cuenta' => 1464,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            311 => 
            array (
                'id' => 838,
                'id_cuenta' => 1465,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            312 => 
            array (
                'id' => 839,
                'id_cuenta' => 1466,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:07',
                'updated_at' => '2023-12-22 10:48:07',
            ),
            313 => 
            array (
                'id' => 840,
                'id_cuenta' => 1467,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            314 => 
            array (
                'id' => 841,
                'id_cuenta' => 1468,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            315 => 
            array (
                'id' => 842,
                'id_cuenta' => 1469,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            316 => 
            array (
                'id' => 843,
                'id_cuenta' => 1470,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            317 => 
            array (
                'id' => 844,
                'id_cuenta' => 1471,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            318 => 
            array (
                'id' => 845,
                'id_cuenta' => 1472,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            319 => 
            array (
                'id' => 846,
                'id_cuenta' => 1473,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            320 => 
            array (
                'id' => 847,
                'id_cuenta' => 1474,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            321 => 
            array (
                'id' => 848,
                'id_cuenta' => 1475,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            322 => 
            array (
                'id' => 849,
                'id_cuenta' => 1476,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            323 => 
            array (
                'id' => 850,
                'id_cuenta' => 1477,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            324 => 
            array (
                'id' => 851,
                'id_cuenta' => 1478,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            325 => 
            array (
                'id' => 852,
                'id_cuenta' => 1479,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            326 => 
            array (
                'id' => 853,
                'id_cuenta' => 1480,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            327 => 
            array (
                'id' => 854,
                'id_cuenta' => 1481,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            328 => 
            array (
                'id' => 855,
                'id_cuenta' => 1482,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            329 => 
            array (
                'id' => 856,
                'id_cuenta' => 1483,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            330 => 
            array (
                'id' => 857,
                'id_cuenta' => 1484,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            331 => 
            array (
                'id' => 858,
                'id_cuenta' => 1485,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            332 => 
            array (
                'id' => 859,
                'id_cuenta' => 1486,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            333 => 
            array (
                'id' => 860,
                'id_cuenta' => 1487,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            334 => 
            array (
                'id' => 861,
                'id_cuenta' => 1488,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            335 => 
            array (
                'id' => 862,
                'id_cuenta' => 1489,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            336 => 
            array (
                'id' => 863,
                'id_cuenta' => 1490,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            337 => 
            array (
                'id' => 864,
                'id_cuenta' => 1491,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            338 => 
            array (
                'id' => 865,
                'id_cuenta' => 1492,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            339 => 
            array (
                'id' => 866,
                'id_cuenta' => 1493,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            340 => 
            array (
                'id' => 867,
                'id_cuenta' => 1494,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            341 => 
            array (
                'id' => 868,
                'id_cuenta' => 1495,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            342 => 
            array (
                'id' => 869,
                'id_cuenta' => 1496,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            343 => 
            array (
                'id' => 870,
                'id_cuenta' => 1497,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            344 => 
            array (
                'id' => 871,
                'id_cuenta' => 1498,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            345 => 
            array (
                'id' => 872,
                'id_cuenta' => 1499,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            346 => 
            array (
                'id' => 873,
                'id_cuenta' => 1500,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            347 => 
            array (
                'id' => 874,
                'id_cuenta' => 1501,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            348 => 
            array (
                'id' => 875,
                'id_cuenta' => 1502,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            349 => 
            array (
                'id' => 876,
                'id_cuenta' => 1503,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            350 => 
            array (
                'id' => 877,
                'id_cuenta' => 1504,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            351 => 
            array (
                'id' => 878,
                'id_cuenta' => 1505,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            352 => 
            array (
                'id' => 879,
                'id_cuenta' => 1506,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            353 => 
            array (
                'id' => 880,
                'id_cuenta' => 1507,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            354 => 
            array (
                'id' => 881,
                'id_cuenta' => 1508,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            355 => 
            array (
                'id' => 882,
                'id_cuenta' => 1509,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            356 => 
            array (
                'id' => 883,
                'id_cuenta' => 1510,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            357 => 
            array (
                'id' => 884,
                'id_cuenta' => 1511,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            358 => 
            array (
                'id' => 885,
                'id_cuenta' => 1512,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            359 => 
            array (
                'id' => 886,
                'id_cuenta' => 1513,
                'id_tipo_cuenta' => 11,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            360 => 
            array (
                'id' => 887,
                'id_cuenta' => 1514,
                'id_tipo_cuenta' => 11,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            361 => 
            array (
                'id' => 888,
                'id_cuenta' => 1515,
                'id_tipo_cuenta' => 11,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            362 => 
            array (
                'id' => 889,
                'id_cuenta' => 1516,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            363 => 
            array (
                'id' => 890,
                'id_cuenta' => 1517,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            364 => 
            array (
                'id' => 891,
                'id_cuenta' => 1518,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            365 => 
            array (
                'id' => 892,
                'id_cuenta' => 1519,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            366 => 
            array (
                'id' => 893,
                'id_cuenta' => 1520,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            367 => 
            array (
                'id' => 894,
                'id_cuenta' => 1521,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            368 => 
            array (
                'id' => 895,
                'id_cuenta' => 1522,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            369 => 
            array (
                'id' => 896,
                'id_cuenta' => 1523,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            370 => 
            array (
                'id' => 897,
                'id_cuenta' => 1524,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            371 => 
            array (
                'id' => 898,
                'id_cuenta' => 1525,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            372 => 
            array (
                'id' => 899,
                'id_cuenta' => 1526,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            373 => 
            array (
                'id' => 900,
                'id_cuenta' => 1527,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            374 => 
            array (
                'id' => 901,
                'id_cuenta' => 1528,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            375 => 
            array (
                'id' => 902,
                'id_cuenta' => 1529,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            376 => 
            array (
                'id' => 903,
                'id_cuenta' => 1530,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            377 => 
            array (
                'id' => 904,
                'id_cuenta' => 1531,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            378 => 
            array (
                'id' => 905,
                'id_cuenta' => 1532,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            379 => 
            array (
                'id' => 906,
                'id_cuenta' => 1533,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            380 => 
            array (
                'id' => 907,
                'id_cuenta' => 1534,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            381 => 
            array (
                'id' => 908,
                'id_cuenta' => 1535,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            382 => 
            array (
                'id' => 909,
                'id_cuenta' => 1536,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            383 => 
            array (
                'id' => 910,
                'id_cuenta' => 1537,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            384 => 
            array (
                'id' => 911,
                'id_cuenta' => 1538,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            385 => 
            array (
                'id' => 912,
                'id_cuenta' => 1539,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            386 => 
            array (
                'id' => 913,
                'id_cuenta' => 1540,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            387 => 
            array (
                'id' => 914,
                'id_cuenta' => 1541,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            388 => 
            array (
                'id' => 915,
                'id_cuenta' => 1542,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            389 => 
            array (
                'id' => 916,
                'id_cuenta' => 1543,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            390 => 
            array (
                'id' => 917,
                'id_cuenta' => 1544,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            391 => 
            array (
                'id' => 918,
                'id_cuenta' => 1545,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            392 => 
            array (
                'id' => 919,
                'id_cuenta' => 1546,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            393 => 
            array (
                'id' => 920,
                'id_cuenta' => 1547,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            394 => 
            array (
                'id' => 921,
                'id_cuenta' => 1548,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            395 => 
            array (
                'id' => 922,
                'id_cuenta' => 1549,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            396 => 
            array (
                'id' => 923,
                'id_cuenta' => 1550,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            397 => 
            array (
                'id' => 924,
                'id_cuenta' => 1551,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            398 => 
            array (
                'id' => 925,
                'id_cuenta' => 1552,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            399 => 
            array (
                'id' => 926,
                'id_cuenta' => 1553,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            400 => 
            array (
                'id' => 927,
                'id_cuenta' => 1554,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            401 => 
            array (
                'id' => 928,
                'id_cuenta' => 1555,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            402 => 
            array (
                'id' => 929,
                'id_cuenta' => 1556,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            403 => 
            array (
                'id' => 930,
                'id_cuenta' => 1557,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            404 => 
            array (
                'id' => 931,
                'id_cuenta' => 1558,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            405 => 
            array (
                'id' => 932,
                'id_cuenta' => 1559,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            406 => 
            array (
                'id' => 933,
                'id_cuenta' => 1560,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            407 => 
            array (
                'id' => 934,
                'id_cuenta' => 1561,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            408 => 
            array (
                'id' => 935,
                'id_cuenta' => 1562,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            409 => 
            array (
                'id' => 936,
                'id_cuenta' => 1563,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            410 => 
            array (
                'id' => 937,
                'id_cuenta' => 1564,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            411 => 
            array (
                'id' => 938,
                'id_cuenta' => 1565,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            412 => 
            array (
                'id' => 939,
                'id_cuenta' => 1566,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            413 => 
            array (
                'id' => 940,
                'id_cuenta' => 1567,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            414 => 
            array (
                'id' => 941,
                'id_cuenta' => 1568,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            415 => 
            array (
                'id' => 942,
                'id_cuenta' => 1569,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            416 => 
            array (
                'id' => 943,
                'id_cuenta' => 1570,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:08',
                'updated_at' => '2023-12-22 10:48:08',
            ),
            417 => 
            array (
                'id' => 944,
                'id_cuenta' => 1571,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            418 => 
            array (
                'id' => 945,
                'id_cuenta' => 1572,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            419 => 
            array (
                'id' => 946,
                'id_cuenta' => 1573,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            420 => 
            array (
                'id' => 947,
                'id_cuenta' => 1574,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            421 => 
            array (
                'id' => 948,
                'id_cuenta' => 1575,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            422 => 
            array (
                'id' => 949,
                'id_cuenta' => 1576,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            423 => 
            array (
                'id' => 950,
                'id_cuenta' => 1577,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            424 => 
            array (
                'id' => 951,
                'id_cuenta' => 1578,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            425 => 
            array (
                'id' => 952,
                'id_cuenta' => 1579,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            426 => 
            array (
                'id' => 953,
                'id_cuenta' => 1580,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            427 => 
            array (
                'id' => 954,
                'id_cuenta' => 1581,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            428 => 
            array (
                'id' => 955,
                'id_cuenta' => 1582,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            429 => 
            array (
                'id' => 956,
                'id_cuenta' => 1583,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            430 => 
            array (
                'id' => 957,
                'id_cuenta' => 1584,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            431 => 
            array (
                'id' => 958,
                'id_cuenta' => 1585,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            432 => 
            array (
                'id' => 959,
                'id_cuenta' => 1586,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            433 => 
            array (
                'id' => 960,
                'id_cuenta' => 1587,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            434 => 
            array (
                'id' => 961,
                'id_cuenta' => 1588,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            435 => 
            array (
                'id' => 962,
                'id_cuenta' => 1589,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            436 => 
            array (
                'id' => 963,
                'id_cuenta' => 1590,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            437 => 
            array (
                'id' => 964,
                'id_cuenta' => 1591,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            438 => 
            array (
                'id' => 965,
                'id_cuenta' => 1592,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            439 => 
            array (
                'id' => 966,
                'id_cuenta' => 1593,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            440 => 
            array (
                'id' => 967,
                'id_cuenta' => 1594,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            441 => 
            array (
                'id' => 968,
                'id_cuenta' => 1595,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            442 => 
            array (
                'id' => 969,
                'id_cuenta' => 1596,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            443 => 
            array (
                'id' => 970,
                'id_cuenta' => 1597,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            444 => 
            array (
                'id' => 971,
                'id_cuenta' => 1598,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            445 => 
            array (
                'id' => 972,
                'id_cuenta' => 1599,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            446 => 
            array (
                'id' => 973,
                'id_cuenta' => 1600,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:09',
                'updated_at' => '2023-12-22 10:48:09',
            ),
            447 => 
            array (
                'id' => 974,
                'id_cuenta' => 1601,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:22',
                'updated_at' => '2023-12-22 10:48:22',
            ),
            448 => 
            array (
                'id' => 975,
                'id_cuenta' => 1602,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:22',
                'updated_at' => '2023-12-22 10:48:22',
            ),
            449 => 
            array (
                'id' => 976,
                'id_cuenta' => 1603,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:22',
                'updated_at' => '2023-12-22 10:48:22',
            ),
            450 => 
            array (
                'id' => 977,
                'id_cuenta' => 1604,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:22',
                'updated_at' => '2023-12-22 10:48:22',
            ),
            451 => 
            array (
                'id' => 978,
                'id_cuenta' => 1605,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:22',
                'updated_at' => '2023-12-22 10:48:22',
            ),
            452 => 
            array (
                'id' => 979,
                'id_cuenta' => 1606,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:22',
                'updated_at' => '2023-12-22 10:48:22',
            ),
            453 => 
            array (
                'id' => 980,
                'id_cuenta' => 1607,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:22',
                'updated_at' => '2023-12-22 10:48:22',
            ),
            454 => 
            array (
                'id' => 981,
                'id_cuenta' => 1608,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:22',
                'updated_at' => '2023-12-22 10:48:22',
            ),
            455 => 
            array (
                'id' => 982,
                'id_cuenta' => 1609,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:22',
                'updated_at' => '2023-12-22 10:48:22',
            ),
            456 => 
            array (
                'id' => 983,
                'id_cuenta' => 1610,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:22',
                'updated_at' => '2023-12-22 10:48:22',
            ),
            457 => 
            array (
                'id' => 984,
                'id_cuenta' => 1611,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:22',
                'updated_at' => '2023-12-22 10:48:22',
            ),
            458 => 
            array (
                'id' => 985,
                'id_cuenta' => 1612,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:22',
                'updated_at' => '2023-12-22 10:48:22',
            ),
            459 => 
            array (
                'id' => 986,
                'id_cuenta' => 1613,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:22',
                'updated_at' => '2023-12-22 10:48:22',
            ),
            460 => 
            array (
                'id' => 987,
                'id_cuenta' => 1614,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:22',
                'updated_at' => '2023-12-22 10:48:22',
            ),
            461 => 
            array (
                'id' => 988,
                'id_cuenta' => 1615,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            462 => 
            array (
                'id' => 989,
                'id_cuenta' => 1616,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            463 => 
            array (
                'id' => 990,
                'id_cuenta' => 1617,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            464 => 
            array (
                'id' => 991,
                'id_cuenta' => 1618,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            465 => 
            array (
                'id' => 992,
                'id_cuenta' => 1619,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            466 => 
            array (
                'id' => 993,
                'id_cuenta' => 1620,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            467 => 
            array (
                'id' => 994,
                'id_cuenta' => 1621,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            468 => 
            array (
                'id' => 995,
                'id_cuenta' => 1622,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            469 => 
            array (
                'id' => 996,
                'id_cuenta' => 1623,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            470 => 
            array (
                'id' => 997,
                'id_cuenta' => 1624,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            471 => 
            array (
                'id' => 998,
                'id_cuenta' => 1625,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            472 => 
            array (
                'id' => 999,
                'id_cuenta' => 1626,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            473 => 
            array (
                'id' => 1000,
                'id_cuenta' => 1627,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            474 => 
            array (
                'id' => 1001,
                'id_cuenta' => 1628,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            475 => 
            array (
                'id' => 1002,
                'id_cuenta' => 1629,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            476 => 
            array (
                'id' => 1003,
                'id_cuenta' => 1630,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            477 => 
            array (
                'id' => 1004,
                'id_cuenta' => 1631,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            478 => 
            array (
                'id' => 1005,
                'id_cuenta' => 1632,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            479 => 
            array (
                'id' => 1006,
                'id_cuenta' => 1633,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            480 => 
            array (
                'id' => 1007,
                'id_cuenta' => 1634,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            481 => 
            array (
                'id' => 1008,
                'id_cuenta' => 1635,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            482 => 
            array (
                'id' => 1009,
                'id_cuenta' => 1636,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            483 => 
            array (
                'id' => 1010,
                'id_cuenta' => 1637,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            484 => 
            array (
                'id' => 1011,
                'id_cuenta' => 1638,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            485 => 
            array (
                'id' => 1012,
                'id_cuenta' => 1639,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            486 => 
            array (
                'id' => 1013,
                'id_cuenta' => 1640,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            487 => 
            array (
                'id' => 1014,
                'id_cuenta' => 1641,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            488 => 
            array (
                'id' => 1015,
                'id_cuenta' => 1642,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            489 => 
            array (
                'id' => 1016,
                'id_cuenta' => 1643,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            490 => 
            array (
                'id' => 1017,
                'id_cuenta' => 1644,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            491 => 
            array (
                'id' => 1018,
                'id_cuenta' => 1645,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            492 => 
            array (
                'id' => 1019,
                'id_cuenta' => 1646,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            493 => 
            array (
                'id' => 1020,
                'id_cuenta' => 1647,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            494 => 
            array (
                'id' => 1021,
                'id_cuenta' => 1648,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            495 => 
            array (
                'id' => 1022,
                'id_cuenta' => 1649,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            496 => 
            array (
                'id' => 1023,
                'id_cuenta' => 1650,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            497 => 
            array (
                'id' => 1024,
                'id_cuenta' => 1651,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            498 => 
            array (
                'id' => 1025,
                'id_cuenta' => 1652,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            499 => 
            array (
                'id' => 1026,
                'id_cuenta' => 1653,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
        ));
        \DB::table('plan_cuentas_tipos')->insert(array (
            0 => 
            array (
                'id' => 1027,
                'id_cuenta' => 1654,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            1 => 
            array (
                'id' => 1028,
                'id_cuenta' => 1655,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            2 => 
            array (
                'id' => 1029,
                'id_cuenta' => 1656,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            3 => 
            array (
                'id' => 1030,
                'id_cuenta' => 1657,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            4 => 
            array (
                'id' => 1031,
                'id_cuenta' => 1658,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            5 => 
            array (
                'id' => 1032,
                'id_cuenta' => 1659,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            6 => 
            array (
                'id' => 1033,
                'id_cuenta' => 1660,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            7 => 
            array (
                'id' => 1034,
                'id_cuenta' => 1661,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            8 => 
            array (
                'id' => 1035,
                'id_cuenta' => 1662,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            9 => 
            array (
                'id' => 1036,
                'id_cuenta' => 1663,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            10 => 
            array (
                'id' => 1037,
                'id_cuenta' => 1664,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            11 => 
            array (
                'id' => 1038,
                'id_cuenta' => 1665,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            12 => 
            array (
                'id' => 1039,
                'id_cuenta' => 1666,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            13 => 
            array (
                'id' => 1040,
                'id_cuenta' => 1667,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            14 => 
            array (
                'id' => 1041,
                'id_cuenta' => 1668,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            15 => 
            array (
                'id' => 1042,
                'id_cuenta' => 1669,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            16 => 
            array (
                'id' => 1043,
                'id_cuenta' => 1670,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            17 => 
            array (
                'id' => 1044,
                'id_cuenta' => 1671,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            18 => 
            array (
                'id' => 1045,
                'id_cuenta' => 1672,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            19 => 
            array (
                'id' => 1046,
                'id_cuenta' => 1673,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            20 => 
            array (
                'id' => 1047,
                'id_cuenta' => 1674,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            21 => 
            array (
                'id' => 1048,
                'id_cuenta' => 1675,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            22 => 
            array (
                'id' => 1049,
                'id_cuenta' => 1676,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            23 => 
            array (
                'id' => 1050,
                'id_cuenta' => 1677,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            24 => 
            array (
                'id' => 1051,
                'id_cuenta' => 1678,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            25 => 
            array (
                'id' => 1052,
                'id_cuenta' => 1679,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            26 => 
            array (
                'id' => 1053,
                'id_cuenta' => 1680,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            27 => 
            array (
                'id' => 1054,
                'id_cuenta' => 1681,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            28 => 
            array (
                'id' => 1055,
                'id_cuenta' => 1682,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            29 => 
            array (
                'id' => 1056,
                'id_cuenta' => 1683,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            30 => 
            array (
                'id' => 1057,
                'id_cuenta' => 1684,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            31 => 
            array (
                'id' => 1058,
                'id_cuenta' => 1685,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:23',
                'updated_at' => '2023-12-22 10:48:23',
            ),
            32 => 
            array (
                'id' => 1059,
                'id_cuenta' => 1686,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            33 => 
            array (
                'id' => 1060,
                'id_cuenta' => 1687,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            34 => 
            array (
                'id' => 1061,
                'id_cuenta' => 1688,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            35 => 
            array (
                'id' => 1062,
                'id_cuenta' => 1689,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            36 => 
            array (
                'id' => 1063,
                'id_cuenta' => 1690,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            37 => 
            array (
                'id' => 1064,
                'id_cuenta' => 1691,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            38 => 
            array (
                'id' => 1065,
                'id_cuenta' => 1692,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            39 => 
            array (
                'id' => 1066,
                'id_cuenta' => 1693,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            40 => 
            array (
                'id' => 1067,
                'id_cuenta' => 1694,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            41 => 
            array (
                'id' => 1068,
                'id_cuenta' => 1695,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            42 => 
            array (
                'id' => 1069,
                'id_cuenta' => 1696,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            43 => 
            array (
                'id' => 1070,
                'id_cuenta' => 1697,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            44 => 
            array (
                'id' => 1071,
                'id_cuenta' => 1698,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            45 => 
            array (
                'id' => 1072,
                'id_cuenta' => 1699,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            46 => 
            array (
                'id' => 1073,
                'id_cuenta' => 1700,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            47 => 
            array (
                'id' => 1074,
                'id_cuenta' => 1701,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            48 => 
            array (
                'id' => 1075,
                'id_cuenta' => 1702,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            49 => 
            array (
                'id' => 1076,
                'id_cuenta' => 1703,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            50 => 
            array (
                'id' => 1077,
                'id_cuenta' => 1704,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            51 => 
            array (
                'id' => 1078,
                'id_cuenta' => 1705,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            52 => 
            array (
                'id' => 1079,
                'id_cuenta' => 1706,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            53 => 
            array (
                'id' => 1080,
                'id_cuenta' => 1707,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            54 => 
            array (
                'id' => 1082,
                'id_cuenta' => 1709,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            55 => 
            array (
                'id' => 1084,
                'id_cuenta' => 1711,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            56 => 
            array (
                'id' => 1085,
                'id_cuenta' => 1712,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            57 => 
            array (
                'id' => 1086,
                'id_cuenta' => 1713,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            58 => 
            array (
                'id' => 1087,
                'id_cuenta' => 1714,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            59 => 
            array (
                'id' => 1088,
                'id_cuenta' => 1715,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            60 => 
            array (
                'id' => 1089,
                'id_cuenta' => 1716,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            61 => 
            array (
                'id' => 1090,
                'id_cuenta' => 1717,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            62 => 
            array (
                'id' => 1091,
                'id_cuenta' => 1718,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            63 => 
            array (
                'id' => 1092,
                'id_cuenta' => 1719,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            64 => 
            array (
                'id' => 1093,
                'id_cuenta' => 1720,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            65 => 
            array (
                'id' => 1094,
                'id_cuenta' => 1721,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            66 => 
            array (
                'id' => 1095,
                'id_cuenta' => 1722,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            67 => 
            array (
                'id' => 1096,
                'id_cuenta' => 1723,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            68 => 
            array (
                'id' => 1097,
                'id_cuenta' => 1724,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            69 => 
            array (
                'id' => 1098,
                'id_cuenta' => 1725,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            70 => 
            array (
                'id' => 1099,
                'id_cuenta' => 1726,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            71 => 
            array (
                'id' => 1100,
                'id_cuenta' => 1727,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            72 => 
            array (
                'id' => 1101,
                'id_cuenta' => 1728,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            73 => 
            array (
                'id' => 1102,
                'id_cuenta' => 1729,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            74 => 
            array (
                'id' => 1103,
                'id_cuenta' => 1730,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            75 => 
            array (
                'id' => 1104,
                'id_cuenta' => 1731,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            76 => 
            array (
                'id' => 1105,
                'id_cuenta' => 1732,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            77 => 
            array (
                'id' => 1106,
                'id_cuenta' => 1733,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            78 => 
            array (
                'id' => 1107,
                'id_cuenta' => 1734,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            79 => 
            array (
                'id' => 1108,
                'id_cuenta' => 1735,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            80 => 
            array (
                'id' => 1109,
                'id_cuenta' => 1736,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            81 => 
            array (
                'id' => 1110,
                'id_cuenta' => 1737,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            82 => 
            array (
                'id' => 1111,
                'id_cuenta' => 1738,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            83 => 
            array (
                'id' => 1112,
                'id_cuenta' => 1739,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            84 => 
            array (
                'id' => 1113,
                'id_cuenta' => 1740,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            85 => 
            array (
                'id' => 1114,
                'id_cuenta' => 1741,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            86 => 
            array (
                'id' => 1115,
                'id_cuenta' => 1742,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            87 => 
            array (
                'id' => 1116,
                'id_cuenta' => 1743,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            88 => 
            array (
                'id' => 1117,
                'id_cuenta' => 1744,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            89 => 
            array (
                'id' => 1118,
                'id_cuenta' => 1745,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            90 => 
            array (
                'id' => 1119,
                'id_cuenta' => 1746,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            91 => 
            array (
                'id' => 1120,
                'id_cuenta' => 1747,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            92 => 
            array (
                'id' => 1121,
                'id_cuenta' => 1748,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            93 => 
            array (
                'id' => 1122,
                'id_cuenta' => 1749,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            94 => 
            array (
                'id' => 1123,
                'id_cuenta' => 1750,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            95 => 
            array (
                'id' => 1124,
                'id_cuenta' => 1751,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            96 => 
            array (
                'id' => 1125,
                'id_cuenta' => 1752,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            97 => 
            array (
                'id' => 1126,
                'id_cuenta' => 1753,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            98 => 
            array (
                'id' => 1127,
                'id_cuenta' => 1754,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            99 => 
            array (
                'id' => 1128,
                'id_cuenta' => 1755,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:24',
                'updated_at' => '2023-12-22 10:48:24',
            ),
            100 => 
            array (
                'id' => 1129,
                'id_cuenta' => 1756,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            101 => 
            array (
                'id' => 1130,
                'id_cuenta' => 1757,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            102 => 
            array (
                'id' => 1131,
                'id_cuenta' => 1758,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            103 => 
            array (
                'id' => 1132,
                'id_cuenta' => 1759,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            104 => 
            array (
                'id' => 1133,
                'id_cuenta' => 1760,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            105 => 
            array (
                'id' => 1134,
                'id_cuenta' => 1761,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            106 => 
            array (
                'id' => 1135,
                'id_cuenta' => 1762,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            107 => 
            array (
                'id' => 1136,
                'id_cuenta' => 1763,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            108 => 
            array (
                'id' => 1137,
                'id_cuenta' => 1764,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            109 => 
            array (
                'id' => 1138,
                'id_cuenta' => 1765,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            110 => 
            array (
                'id' => 1139,
                'id_cuenta' => 1766,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            111 => 
            array (
                'id' => 1140,
                'id_cuenta' => 1767,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            112 => 
            array (
                'id' => 1141,
                'id_cuenta' => 1768,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            113 => 
            array (
                'id' => 1142,
                'id_cuenta' => 1769,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            114 => 
            array (
                'id' => 1143,
                'id_cuenta' => 1770,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            115 => 
            array (
                'id' => 1144,
                'id_cuenta' => 1771,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            116 => 
            array (
                'id' => 1145,
                'id_cuenta' => 1772,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            117 => 
            array (
                'id' => 1146,
                'id_cuenta' => 1773,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            118 => 
            array (
                'id' => 1147,
                'id_cuenta' => 1774,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            119 => 
            array (
                'id' => 1148,
                'id_cuenta' => 1775,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            120 => 
            array (
                'id' => 1149,
                'id_cuenta' => 1776,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            121 => 
            array (
                'id' => 1150,
                'id_cuenta' => 1777,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            122 => 
            array (
                'id' => 1151,
                'id_cuenta' => 1778,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            123 => 
            array (
                'id' => 1152,
                'id_cuenta' => 1779,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            124 => 
            array (
                'id' => 1153,
                'id_cuenta' => 1780,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            125 => 
            array (
                'id' => 1154,
                'id_cuenta' => 1781,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            126 => 
            array (
                'id' => 1155,
                'id_cuenta' => 1782,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            127 => 
            array (
                'id' => 1156,
                'id_cuenta' => 1783,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            128 => 
            array (
                'id' => 1157,
                'id_cuenta' => 1784,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            129 => 
            array (
                'id' => 1158,
                'id_cuenta' => 1785,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            130 => 
            array (
                'id' => 1159,
                'id_cuenta' => 1786,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            131 => 
            array (
                'id' => 1160,
                'id_cuenta' => 1787,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            132 => 
            array (
                'id' => 1161,
                'id_cuenta' => 1788,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            133 => 
            array (
                'id' => 1162,
                'id_cuenta' => 1789,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            134 => 
            array (
                'id' => 1163,
                'id_cuenta' => 1790,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            135 => 
            array (
                'id' => 1164,
                'id_cuenta' => 1791,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            136 => 
            array (
                'id' => 1165,
                'id_cuenta' => 1792,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            137 => 
            array (
                'id' => 1166,
                'id_cuenta' => 1793,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            138 => 
            array (
                'id' => 1167,
                'id_cuenta' => 1794,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            139 => 
            array (
                'id' => 1168,
                'id_cuenta' => 1795,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            140 => 
            array (
                'id' => 1169,
                'id_cuenta' => 1796,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            141 => 
            array (
                'id' => 1170,
                'id_cuenta' => 1797,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            142 => 
            array (
                'id' => 1171,
                'id_cuenta' => 1798,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            143 => 
            array (
                'id' => 1172,
                'id_cuenta' => 1799,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            144 => 
            array (
                'id' => 1173,
                'id_cuenta' => 1800,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            145 => 
            array (
                'id' => 1174,
                'id_cuenta' => 1801,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            146 => 
            array (
                'id' => 1175,
                'id_cuenta' => 1802,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            147 => 
            array (
                'id' => 1176,
                'id_cuenta' => 1803,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            148 => 
            array (
                'id' => 1177,
                'id_cuenta' => 1804,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            149 => 
            array (
                'id' => 1178,
                'id_cuenta' => 1805,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            150 => 
            array (
                'id' => 1179,
                'id_cuenta' => 1806,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            151 => 
            array (
                'id' => 1180,
                'id_cuenta' => 1807,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            152 => 
            array (
                'id' => 1181,
                'id_cuenta' => 1808,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            153 => 
            array (
                'id' => 1182,
                'id_cuenta' => 1809,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            154 => 
            array (
                'id' => 1183,
                'id_cuenta' => 1810,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            155 => 
            array (
                'id' => 1184,
                'id_cuenta' => 1811,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            156 => 
            array (
                'id' => 1185,
                'id_cuenta' => 1812,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            157 => 
            array (
                'id' => 1186,
                'id_cuenta' => 1813,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            158 => 
            array (
                'id' => 1187,
                'id_cuenta' => 1814,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            159 => 
            array (
                'id' => 1188,
                'id_cuenta' => 1815,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            160 => 
            array (
                'id' => 1189,
                'id_cuenta' => 1816,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            161 => 
            array (
                'id' => 1190,
                'id_cuenta' => 1817,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            162 => 
            array (
                'id' => 1191,
                'id_cuenta' => 1818,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            163 => 
            array (
                'id' => 1192,
                'id_cuenta' => 1819,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            164 => 
            array (
                'id' => 1193,
                'id_cuenta' => 1820,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            165 => 
            array (
                'id' => 1194,
                'id_cuenta' => 1821,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            166 => 
            array (
                'id' => 1195,
                'id_cuenta' => 1822,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:25',
                'updated_at' => '2023-12-22 10:48:25',
            ),
            167 => 
            array (
                'id' => 1196,
                'id_cuenta' => 1823,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            168 => 
            array (
                'id' => 1197,
                'id_cuenta' => 1824,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            169 => 
            array (
                'id' => 1198,
                'id_cuenta' => 1825,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            170 => 
            array (
                'id' => 1199,
                'id_cuenta' => 1826,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            171 => 
            array (
                'id' => 1200,
                'id_cuenta' => 1827,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            172 => 
            array (
                'id' => 1201,
                'id_cuenta' => 1828,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            173 => 
            array (
                'id' => 1202,
                'id_cuenta' => 1829,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            174 => 
            array (
                'id' => 1203,
                'id_cuenta' => 1830,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            175 => 
            array (
                'id' => 1204,
                'id_cuenta' => 1831,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            176 => 
            array (
                'id' => 1205,
                'id_cuenta' => 1832,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            177 => 
            array (
                'id' => 1206,
                'id_cuenta' => 1833,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            178 => 
            array (
                'id' => 1207,
                'id_cuenta' => 1834,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            179 => 
            array (
                'id' => 1208,
                'id_cuenta' => 1835,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            180 => 
            array (
                'id' => 1209,
                'id_cuenta' => 1836,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            181 => 
            array (
                'id' => 1210,
                'id_cuenta' => 1837,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            182 => 
            array (
                'id' => 1211,
                'id_cuenta' => 1838,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            183 => 
            array (
                'id' => 1212,
                'id_cuenta' => 1839,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            184 => 
            array (
                'id' => 1213,
                'id_cuenta' => 1840,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            185 => 
            array (
                'id' => 1214,
                'id_cuenta' => 1841,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            186 => 
            array (
                'id' => 1215,
                'id_cuenta' => 1842,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            187 => 
            array (
                'id' => 1216,
                'id_cuenta' => 1843,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            188 => 
            array (
                'id' => 1217,
                'id_cuenta' => 1844,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            189 => 
            array (
                'id' => 1218,
                'id_cuenta' => 1845,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            190 => 
            array (
                'id' => 1219,
                'id_cuenta' => 1846,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            191 => 
            array (
                'id' => 1220,
                'id_cuenta' => 1847,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            192 => 
            array (
                'id' => 1221,
                'id_cuenta' => 1848,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            193 => 
            array (
                'id' => 1222,
                'id_cuenta' => 1849,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            194 => 
            array (
                'id' => 1223,
                'id_cuenta' => 1850,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            195 => 
            array (
                'id' => 1224,
                'id_cuenta' => 1851,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            196 => 
            array (
                'id' => 1225,
                'id_cuenta' => 1852,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            197 => 
            array (
                'id' => 1226,
                'id_cuenta' => 1853,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            198 => 
            array (
                'id' => 1227,
                'id_cuenta' => 1854,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            199 => 
            array (
                'id' => 1228,
                'id_cuenta' => 1855,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            200 => 
            array (
                'id' => 1229,
                'id_cuenta' => 1856,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            201 => 
            array (
                'id' => 1230,
                'id_cuenta' => 1857,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            202 => 
            array (
                'id' => 1231,
                'id_cuenta' => 1858,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            203 => 
            array (
                'id' => 1232,
                'id_cuenta' => 1859,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            204 => 
            array (
                'id' => 1233,
                'id_cuenta' => 1860,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            205 => 
            array (
                'id' => 1234,
                'id_cuenta' => 1861,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            206 => 
            array (
                'id' => 1235,
                'id_cuenta' => 1862,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            207 => 
            array (
                'id' => 1236,
                'id_cuenta' => 1863,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            208 => 
            array (
                'id' => 1237,
                'id_cuenta' => 1864,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            209 => 
            array (
                'id' => 1238,
                'id_cuenta' => 1865,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            210 => 
            array (
                'id' => 1239,
                'id_cuenta' => 1866,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            211 => 
            array (
                'id' => 1240,
                'id_cuenta' => 1867,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            212 => 
            array (
                'id' => 1241,
                'id_cuenta' => 1868,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            213 => 
            array (
                'id' => 1242,
                'id_cuenta' => 1869,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            214 => 
            array (
                'id' => 1243,
                'id_cuenta' => 1870,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            215 => 
            array (
                'id' => 1244,
                'id_cuenta' => 1871,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            216 => 
            array (
                'id' => 1245,
                'id_cuenta' => 1872,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            217 => 
            array (
                'id' => 1246,
                'id_cuenta' => 1873,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            218 => 
            array (
                'id' => 1247,
                'id_cuenta' => 1874,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            219 => 
            array (
                'id' => 1248,
                'id_cuenta' => 1875,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            220 => 
            array (
                'id' => 1249,
                'id_cuenta' => 1876,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            221 => 
            array (
                'id' => 1250,
                'id_cuenta' => 1877,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            222 => 
            array (
                'id' => 1251,
                'id_cuenta' => 1878,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            223 => 
            array (
                'id' => 1252,
                'id_cuenta' => 1879,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            224 => 
            array (
                'id' => 1253,
                'id_cuenta' => 1880,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            225 => 
            array (
                'id' => 1254,
                'id_cuenta' => 1881,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            226 => 
            array (
                'id' => 1255,
                'id_cuenta' => 1882,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            227 => 
            array (
                'id' => 1256,
                'id_cuenta' => 1883,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            228 => 
            array (
                'id' => 1257,
                'id_cuenta' => 1884,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            229 => 
            array (
                'id' => 1258,
                'id_cuenta' => 1885,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            230 => 
            array (
                'id' => 1259,
                'id_cuenta' => 1886,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            231 => 
            array (
                'id' => 1260,
                'id_cuenta' => 1887,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            232 => 
            array (
                'id' => 1261,
                'id_cuenta' => 1888,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            233 => 
            array (
                'id' => 1262,
                'id_cuenta' => 1889,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            234 => 
            array (
                'id' => 1263,
                'id_cuenta' => 1890,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:26',
                'updated_at' => '2023-12-22 10:48:26',
            ),
            235 => 
            array (
                'id' => 1264,
                'id_cuenta' => 1891,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            236 => 
            array (
                'id' => 1265,
                'id_cuenta' => 1892,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            237 => 
            array (
                'id' => 1266,
                'id_cuenta' => 1893,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            238 => 
            array (
                'id' => 1267,
                'id_cuenta' => 1894,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            239 => 
            array (
                'id' => 1268,
                'id_cuenta' => 1895,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            240 => 
            array (
                'id' => 1269,
                'id_cuenta' => 1896,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            241 => 
            array (
                'id' => 1270,
                'id_cuenta' => 1897,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            242 => 
            array (
                'id' => 1271,
                'id_cuenta' => 1898,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            243 => 
            array (
                'id' => 1272,
                'id_cuenta' => 1899,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            244 => 
            array (
                'id' => 1273,
                'id_cuenta' => 1900,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            245 => 
            array (
                'id' => 1274,
                'id_cuenta' => 1901,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            246 => 
            array (
                'id' => 1275,
                'id_cuenta' => 1902,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            247 => 
            array (
                'id' => 1276,
                'id_cuenta' => 1903,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            248 => 
            array (
                'id' => 1277,
                'id_cuenta' => 1904,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            249 => 
            array (
                'id' => 1278,
                'id_cuenta' => 1905,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            250 => 
            array (
                'id' => 1279,
                'id_cuenta' => 1906,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            251 => 
            array (
                'id' => 1280,
                'id_cuenta' => 1907,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            252 => 
            array (
                'id' => 1281,
                'id_cuenta' => 1908,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            253 => 
            array (
                'id' => 1282,
                'id_cuenta' => 1909,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            254 => 
            array (
                'id' => 1283,
                'id_cuenta' => 1910,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            255 => 
            array (
                'id' => 1284,
                'id_cuenta' => 1911,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            256 => 
            array (
                'id' => 1285,
                'id_cuenta' => 1912,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            257 => 
            array (
                'id' => 1286,
                'id_cuenta' => 1913,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            258 => 
            array (
                'id' => 1287,
                'id_cuenta' => 1914,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            259 => 
            array (
                'id' => 1288,
                'id_cuenta' => 1915,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            260 => 
            array (
                'id' => 1289,
                'id_cuenta' => 1916,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            261 => 
            array (
                'id' => 1290,
                'id_cuenta' => 1917,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            262 => 
            array (
                'id' => 1291,
                'id_cuenta' => 1918,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            263 => 
            array (
                'id' => 1292,
                'id_cuenta' => 1919,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            264 => 
            array (
                'id' => 1293,
                'id_cuenta' => 1920,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            265 => 
            array (
                'id' => 1294,
                'id_cuenta' => 1921,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            266 => 
            array (
                'id' => 1295,
                'id_cuenta' => 1922,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            267 => 
            array (
                'id' => 1296,
                'id_cuenta' => 1923,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            268 => 
            array (
                'id' => 1297,
                'id_cuenta' => 1924,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            269 => 
            array (
                'id' => 1298,
                'id_cuenta' => 1925,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            270 => 
            array (
                'id' => 1299,
                'id_cuenta' => 1926,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            271 => 
            array (
                'id' => 1300,
                'id_cuenta' => 1927,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            272 => 
            array (
                'id' => 1301,
                'id_cuenta' => 1928,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            273 => 
            array (
                'id' => 1302,
                'id_cuenta' => 1929,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            274 => 
            array (
                'id' => 1303,
                'id_cuenta' => 1930,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            275 => 
            array (
                'id' => 1304,
                'id_cuenta' => 1931,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            276 => 
            array (
                'id' => 1305,
                'id_cuenta' => 1932,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            277 => 
            array (
                'id' => 1306,
                'id_cuenta' => 1933,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            278 => 
            array (
                'id' => 1307,
                'id_cuenta' => 1934,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            279 => 
            array (
                'id' => 1308,
                'id_cuenta' => 1935,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            280 => 
            array (
                'id' => 1309,
                'id_cuenta' => 1936,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            281 => 
            array (
                'id' => 1310,
                'id_cuenta' => 1937,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            282 => 
            array (
                'id' => 1311,
                'id_cuenta' => 1938,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            283 => 
            array (
                'id' => 1312,
                'id_cuenta' => 1939,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            284 => 
            array (
                'id' => 1313,
                'id_cuenta' => 1940,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            285 => 
            array (
                'id' => 1314,
                'id_cuenta' => 1941,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            286 => 
            array (
                'id' => 1315,
                'id_cuenta' => 1942,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            287 => 
            array (
                'id' => 1316,
                'id_cuenta' => 1943,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            288 => 
            array (
                'id' => 1317,
                'id_cuenta' => 1944,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            289 => 
            array (
                'id' => 1318,
                'id_cuenta' => 1945,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            290 => 
            array (
                'id' => 1319,
                'id_cuenta' => 1946,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            291 => 
            array (
                'id' => 1320,
                'id_cuenta' => 1947,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            292 => 
            array (
                'id' => 1321,
                'id_cuenta' => 1948,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            293 => 
            array (
                'id' => 1322,
                'id_cuenta' => 1949,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            294 => 
            array (
                'id' => 1323,
                'id_cuenta' => 1950,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            295 => 
            array (
                'id' => 1324,
                'id_cuenta' => 1951,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            296 => 
            array (
                'id' => 1325,
                'id_cuenta' => 1952,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            297 => 
            array (
                'id' => 1326,
                'id_cuenta' => 1953,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            298 => 
            array (
                'id' => 1327,
                'id_cuenta' => 1954,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            299 => 
            array (
                'id' => 1328,
                'id_cuenta' => 1955,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            300 => 
            array (
                'id' => 1329,
                'id_cuenta' => 1956,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            301 => 
            array (
                'id' => 1330,
                'id_cuenta' => 1957,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            302 => 
            array (
                'id' => 1331,
                'id_cuenta' => 1958,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            303 => 
            array (
                'id' => 1332,
                'id_cuenta' => 1959,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            304 => 
            array (
                'id' => 1333,
                'id_cuenta' => 1960,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            305 => 
            array (
                'id' => 1334,
                'id_cuenta' => 1961,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            306 => 
            array (
                'id' => 1335,
                'id_cuenta' => 1962,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:27',
                'updated_at' => '2023-12-22 10:48:27',
            ),
            307 => 
            array (
                'id' => 1336,
                'id_cuenta' => 1963,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            308 => 
            array (
                'id' => 1337,
                'id_cuenta' => 1964,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            309 => 
            array (
                'id' => 1338,
                'id_cuenta' => 1965,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            310 => 
            array (
                'id' => 1339,
                'id_cuenta' => 1966,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            311 => 
            array (
                'id' => 1340,
                'id_cuenta' => 1967,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            312 => 
            array (
                'id' => 1341,
                'id_cuenta' => 1968,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            313 => 
            array (
                'id' => 1342,
                'id_cuenta' => 1969,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            314 => 
            array (
                'id' => 1343,
                'id_cuenta' => 1970,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            315 => 
            array (
                'id' => 1344,
                'id_cuenta' => 1971,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            316 => 
            array (
                'id' => 1345,
                'id_cuenta' => 1972,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            317 => 
            array (
                'id' => 1346,
                'id_cuenta' => 1973,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            318 => 
            array (
                'id' => 1347,
                'id_cuenta' => 1974,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            319 => 
            array (
                'id' => 1348,
                'id_cuenta' => 1975,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            320 => 
            array (
                'id' => 1349,
                'id_cuenta' => 1976,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            321 => 
            array (
                'id' => 1350,
                'id_cuenta' => 1977,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            322 => 
            array (
                'id' => 1351,
                'id_cuenta' => 1978,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            323 => 
            array (
                'id' => 1352,
                'id_cuenta' => 1979,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            324 => 
            array (
                'id' => 1353,
                'id_cuenta' => 1980,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            325 => 
            array (
                'id' => 1354,
                'id_cuenta' => 1981,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            326 => 
            array (
                'id' => 1355,
                'id_cuenta' => 1982,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            327 => 
            array (
                'id' => 1356,
                'id_cuenta' => 1983,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            328 => 
            array (
                'id' => 1357,
                'id_cuenta' => 1984,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            329 => 
            array (
                'id' => 1358,
                'id_cuenta' => 1985,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            330 => 
            array (
                'id' => 1359,
                'id_cuenta' => 1986,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            331 => 
            array (
                'id' => 1360,
                'id_cuenta' => 1987,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            332 => 
            array (
                'id' => 1361,
                'id_cuenta' => 1988,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            333 => 
            array (
                'id' => 1362,
                'id_cuenta' => 1989,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            334 => 
            array (
                'id' => 1363,
                'id_cuenta' => 1990,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            335 => 
            array (
                'id' => 1364,
                'id_cuenta' => 1991,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            336 => 
            array (
                'id' => 1365,
                'id_cuenta' => 1992,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            337 => 
            array (
                'id' => 1366,
                'id_cuenta' => 1993,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            338 => 
            array (
                'id' => 1367,
                'id_cuenta' => 1994,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            339 => 
            array (
                'id' => 1368,
                'id_cuenta' => 1995,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            340 => 
            array (
                'id' => 1369,
                'id_cuenta' => 1996,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            341 => 
            array (
                'id' => 1370,
                'id_cuenta' => 1997,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            342 => 
            array (
                'id' => 1371,
                'id_cuenta' => 1998,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            343 => 
            array (
                'id' => 1372,
                'id_cuenta' => 1999,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            344 => 
            array (
                'id' => 1373,
                'id_cuenta' => 2000,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:28',
                'updated_at' => '2023-12-22 10:48:28',
            ),
            345 => 
            array (
                'id' => 1374,
                'id_cuenta' => 2001,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            346 => 
            array (
                'id' => 1375,
                'id_cuenta' => 2002,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            347 => 
            array (
                'id' => 1376,
                'id_cuenta' => 2003,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            348 => 
            array (
                'id' => 1377,
                'id_cuenta' => 2004,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            349 => 
            array (
                'id' => 1378,
                'id_cuenta' => 2005,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            350 => 
            array (
                'id' => 1379,
                'id_cuenta' => 2006,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            351 => 
            array (
                'id' => 1380,
                'id_cuenta' => 2007,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            352 => 
            array (
                'id' => 1381,
                'id_cuenta' => 2008,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            353 => 
            array (
                'id' => 1382,
                'id_cuenta' => 2009,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            354 => 
            array (
                'id' => 1383,
                'id_cuenta' => 2010,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            355 => 
            array (
                'id' => 1384,
                'id_cuenta' => 2011,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            356 => 
            array (
                'id' => 1385,
                'id_cuenta' => 2012,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            357 => 
            array (
                'id' => 1386,
                'id_cuenta' => 2013,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            358 => 
            array (
                'id' => 1387,
                'id_cuenta' => 2014,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            359 => 
            array (
                'id' => 1388,
                'id_cuenta' => 2015,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            360 => 
            array (
                'id' => 1389,
                'id_cuenta' => 2016,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            361 => 
            array (
                'id' => 1390,
                'id_cuenta' => 2017,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            362 => 
            array (
                'id' => 1391,
                'id_cuenta' => 2018,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            363 => 
            array (
                'id' => 1392,
                'id_cuenta' => 2019,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            364 => 
            array (
                'id' => 1393,
                'id_cuenta' => 2020,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            365 => 
            array (
                'id' => 1394,
                'id_cuenta' => 2021,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            366 => 
            array (
                'id' => 1395,
                'id_cuenta' => 2022,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            367 => 
            array (
                'id' => 1396,
                'id_cuenta' => 2023,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            368 => 
            array (
                'id' => 1397,
                'id_cuenta' => 2024,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            369 => 
            array (
                'id' => 1398,
                'id_cuenta' => 2025,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            370 => 
            array (
                'id' => 1399,
                'id_cuenta' => 2026,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            371 => 
            array (
                'id' => 1400,
                'id_cuenta' => 2027,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            372 => 
            array (
                'id' => 1401,
                'id_cuenta' => 2028,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            373 => 
            array (
                'id' => 1402,
                'id_cuenta' => 2029,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            374 => 
            array (
                'id' => 1403,
                'id_cuenta' => 2030,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            375 => 
            array (
                'id' => 1404,
                'id_cuenta' => 2031,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            376 => 
            array (
                'id' => 1405,
                'id_cuenta' => 2032,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            377 => 
            array (
                'id' => 1406,
                'id_cuenta' => 2033,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            378 => 
            array (
                'id' => 1407,
                'id_cuenta' => 2034,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            379 => 
            array (
                'id' => 1408,
                'id_cuenta' => 2035,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            380 => 
            array (
                'id' => 1409,
                'id_cuenta' => 2036,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            381 => 
            array (
                'id' => 1410,
                'id_cuenta' => 2037,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            382 => 
            array (
                'id' => 1411,
                'id_cuenta' => 2038,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            383 => 
            array (
                'id' => 1412,
                'id_cuenta' => 2039,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            384 => 
            array (
                'id' => 1413,
                'id_cuenta' => 2040,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            385 => 
            array (
                'id' => 1414,
                'id_cuenta' => 2041,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            386 => 
            array (
                'id' => 1415,
                'id_cuenta' => 2042,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            387 => 
            array (
                'id' => 1416,
                'id_cuenta' => 2043,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            388 => 
            array (
                'id' => 1417,
                'id_cuenta' => 2044,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            389 => 
            array (
                'id' => 1418,
                'id_cuenta' => 2045,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            390 => 
            array (
                'id' => 1419,
                'id_cuenta' => 2046,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            391 => 
            array (
                'id' => 1420,
                'id_cuenta' => 2047,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            392 => 
            array (
                'id' => 1421,
                'id_cuenta' => 2048,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            393 => 
            array (
                'id' => 1422,
                'id_cuenta' => 2049,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            394 => 
            array (
                'id' => 1423,
                'id_cuenta' => 2050,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            395 => 
            array (
                'id' => 1424,
                'id_cuenta' => 2051,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            396 => 
            array (
                'id' => 1425,
                'id_cuenta' => 2052,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            397 => 
            array (
                'id' => 1426,
                'id_cuenta' => 2053,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            398 => 
            array (
                'id' => 1427,
                'id_cuenta' => 2054,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            399 => 
            array (
                'id' => 1428,
                'id_cuenta' => 2055,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            400 => 
            array (
                'id' => 1429,
                'id_cuenta' => 2056,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            401 => 
            array (
                'id' => 1430,
                'id_cuenta' => 2057,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            402 => 
            array (
                'id' => 1431,
                'id_cuenta' => 2058,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            403 => 
            array (
                'id' => 1432,
                'id_cuenta' => 2059,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            404 => 
            array (
                'id' => 1433,
                'id_cuenta' => 2060,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            405 => 
            array (
                'id' => 1434,
                'id_cuenta' => 2061,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            406 => 
            array (
                'id' => 1435,
                'id_cuenta' => 2062,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            407 => 
            array (
                'id' => 1436,
                'id_cuenta' => 2063,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            408 => 
            array (
                'id' => 1437,
                'id_cuenta' => 2064,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            409 => 
            array (
                'id' => 1438,
                'id_cuenta' => 2065,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:46',
                'updated_at' => '2023-12-22 10:48:46',
            ),
            410 => 
            array (
                'id' => 1439,
                'id_cuenta' => 2066,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            411 => 
            array (
                'id' => 1440,
                'id_cuenta' => 2067,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            412 => 
            array (
                'id' => 1441,
                'id_cuenta' => 2068,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            413 => 
            array (
                'id' => 1442,
                'id_cuenta' => 2069,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            414 => 
            array (
                'id' => 1443,
                'id_cuenta' => 2070,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            415 => 
            array (
                'id' => 1444,
                'id_cuenta' => 2071,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            416 => 
            array (
                'id' => 1445,
                'id_cuenta' => 2072,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            417 => 
            array (
                'id' => 1446,
                'id_cuenta' => 2073,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            418 => 
            array (
                'id' => 1447,
                'id_cuenta' => 2074,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            419 => 
            array (
                'id' => 1448,
                'id_cuenta' => 2075,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            420 => 
            array (
                'id' => 1449,
                'id_cuenta' => 2076,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            421 => 
            array (
                'id' => 1450,
                'id_cuenta' => 2077,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            422 => 
            array (
                'id' => 1451,
                'id_cuenta' => 2078,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            423 => 
            array (
                'id' => 1452,
                'id_cuenta' => 2079,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            424 => 
            array (
                'id' => 1453,
                'id_cuenta' => 2080,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            425 => 
            array (
                'id' => 1454,
                'id_cuenta' => 2081,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            426 => 
            array (
                'id' => 1455,
                'id_cuenta' => 2082,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            427 => 
            array (
                'id' => 1456,
                'id_cuenta' => 2083,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            428 => 
            array (
                'id' => 1457,
                'id_cuenta' => 2084,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            429 => 
            array (
                'id' => 1458,
                'id_cuenta' => 2085,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            430 => 
            array (
                'id' => 1459,
                'id_cuenta' => 2086,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            431 => 
            array (
                'id' => 1460,
                'id_cuenta' => 2087,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            432 => 
            array (
                'id' => 1461,
                'id_cuenta' => 2088,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            433 => 
            array (
                'id' => 1462,
                'id_cuenta' => 2089,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            434 => 
            array (
                'id' => 1463,
                'id_cuenta' => 2090,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            435 => 
            array (
                'id' => 1464,
                'id_cuenta' => 2091,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            436 => 
            array (
                'id' => 1465,
                'id_cuenta' => 2092,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            437 => 
            array (
                'id' => 1466,
                'id_cuenta' => 2093,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            438 => 
            array (
                'id' => 1467,
                'id_cuenta' => 2094,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            439 => 
            array (
                'id' => 1468,
                'id_cuenta' => 2095,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            440 => 
            array (
                'id' => 1469,
                'id_cuenta' => 2096,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            441 => 
            array (
                'id' => 1470,
                'id_cuenta' => 2097,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            442 => 
            array (
                'id' => 1471,
                'id_cuenta' => 2098,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            443 => 
            array (
                'id' => 1472,
                'id_cuenta' => 2099,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            444 => 
            array (
                'id' => 1473,
                'id_cuenta' => 2100,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            445 => 
            array (
                'id' => 1474,
                'id_cuenta' => 2101,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            446 => 
            array (
                'id' => 1475,
                'id_cuenta' => 2102,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            447 => 
            array (
                'id' => 1476,
                'id_cuenta' => 2103,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            448 => 
            array (
                'id' => 1477,
                'id_cuenta' => 2104,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            449 => 
            array (
                'id' => 1478,
                'id_cuenta' => 2105,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            450 => 
            array (
                'id' => 1479,
                'id_cuenta' => 2106,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            451 => 
            array (
                'id' => 1480,
                'id_cuenta' => 2107,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            452 => 
            array (
                'id' => 1481,
                'id_cuenta' => 2108,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            453 => 
            array (
                'id' => 1482,
                'id_cuenta' => 2109,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            454 => 
            array (
                'id' => 1483,
                'id_cuenta' => 2110,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            455 => 
            array (
                'id' => 1484,
                'id_cuenta' => 2111,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            456 => 
            array (
                'id' => 1485,
                'id_cuenta' => 2112,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            457 => 
            array (
                'id' => 1486,
                'id_cuenta' => 2113,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            458 => 
            array (
                'id' => 1487,
                'id_cuenta' => 2114,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            459 => 
            array (
                'id' => 1488,
                'id_cuenta' => 2115,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            460 => 
            array (
                'id' => 1489,
                'id_cuenta' => 2116,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            461 => 
            array (
                'id' => 1490,
                'id_cuenta' => 2117,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            462 => 
            array (
                'id' => 1491,
                'id_cuenta' => 2118,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            463 => 
            array (
                'id' => 1492,
                'id_cuenta' => 2119,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            464 => 
            array (
                'id' => 1493,
                'id_cuenta' => 2120,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            465 => 
            array (
                'id' => 1494,
                'id_cuenta' => 2121,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            466 => 
            array (
                'id' => 1495,
                'id_cuenta' => 2122,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            467 => 
            array (
                'id' => 1496,
                'id_cuenta' => 2123,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            468 => 
            array (
                'id' => 1497,
                'id_cuenta' => 2124,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            469 => 
            array (
                'id' => 1498,
                'id_cuenta' => 2125,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            470 => 
            array (
                'id' => 1499,
                'id_cuenta' => 2126,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            471 => 
            array (
                'id' => 1500,
                'id_cuenta' => 2127,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            472 => 
            array (
                'id' => 1501,
                'id_cuenta' => 2128,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            473 => 
            array (
                'id' => 1502,
                'id_cuenta' => 2129,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            474 => 
            array (
                'id' => 1503,
                'id_cuenta' => 2130,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            475 => 
            array (
                'id' => 1504,
                'id_cuenta' => 2131,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            476 => 
            array (
                'id' => 1505,
                'id_cuenta' => 2132,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            477 => 
            array (
                'id' => 1506,
                'id_cuenta' => 2133,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            478 => 
            array (
                'id' => 1507,
                'id_cuenta' => 2134,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            479 => 
            array (
                'id' => 1508,
                'id_cuenta' => 2135,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            480 => 
            array (
                'id' => 1509,
                'id_cuenta' => 2136,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            481 => 
            array (
                'id' => 1510,
                'id_cuenta' => 2137,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            482 => 
            array (
                'id' => 1511,
                'id_cuenta' => 2138,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            483 => 
            array (
                'id' => 1512,
                'id_cuenta' => 2139,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:47',
                'updated_at' => '2023-12-22 10:48:47',
            ),
            484 => 
            array (
                'id' => 1513,
                'id_cuenta' => 2140,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            485 => 
            array (
                'id' => 1514,
                'id_cuenta' => 2141,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            486 => 
            array (
                'id' => 1515,
                'id_cuenta' => 2142,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            487 => 
            array (
                'id' => 1516,
                'id_cuenta' => 2143,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            488 => 
            array (
                'id' => 1517,
                'id_cuenta' => 2144,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            489 => 
            array (
                'id' => 1518,
                'id_cuenta' => 2145,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            490 => 
            array (
                'id' => 1519,
                'id_cuenta' => 2146,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            491 => 
            array (
                'id' => 1520,
                'id_cuenta' => 2147,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            492 => 
            array (
                'id' => 1521,
                'id_cuenta' => 2148,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            493 => 
            array (
                'id' => 1522,
                'id_cuenta' => 2149,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            494 => 
            array (
                'id' => 1523,
                'id_cuenta' => 2150,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            495 => 
            array (
                'id' => 1525,
                'id_cuenta' => 2152,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            496 => 
            array (
                'id' => 1527,
                'id_cuenta' => 2154,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            497 => 
            array (
                'id' => 1528,
                'id_cuenta' => 2155,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            498 => 
            array (
                'id' => 1529,
                'id_cuenta' => 2156,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            499 => 
            array (
                'id' => 1530,
                'id_cuenta' => 2157,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
        ));
        \DB::table('plan_cuentas_tipos')->insert(array (
            0 => 
            array (
                'id' => 1531,
                'id_cuenta' => 2158,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            1 => 
            array (
                'id' => 1532,
                'id_cuenta' => 2159,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            2 => 
            array (
                'id' => 1533,
                'id_cuenta' => 2160,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            3 => 
            array (
                'id' => 1534,
                'id_cuenta' => 2161,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            4 => 
            array (
                'id' => 1535,
                'id_cuenta' => 2162,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            5 => 
            array (
                'id' => 1536,
                'id_cuenta' => 2163,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            6 => 
            array (
                'id' => 1537,
                'id_cuenta' => 2164,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            7 => 
            array (
                'id' => 1538,
                'id_cuenta' => 2165,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            8 => 
            array (
                'id' => 1539,
                'id_cuenta' => 2166,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            9 => 
            array (
                'id' => 1540,
                'id_cuenta' => 2167,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            10 => 
            array (
                'id' => 1541,
                'id_cuenta' => 2168,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            11 => 
            array (
                'id' => 1542,
                'id_cuenta' => 2169,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            12 => 
            array (
                'id' => 1543,
                'id_cuenta' => 2170,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            13 => 
            array (
                'id' => 1544,
                'id_cuenta' => 2171,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            14 => 
            array (
                'id' => 1545,
                'id_cuenta' => 2172,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            15 => 
            array (
                'id' => 1546,
                'id_cuenta' => 2173,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            16 => 
            array (
                'id' => 1547,
                'id_cuenta' => 2174,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            17 => 
            array (
                'id' => 1548,
                'id_cuenta' => 2175,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            18 => 
            array (
                'id' => 1549,
                'id_cuenta' => 2176,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            19 => 
            array (
                'id' => 1550,
                'id_cuenta' => 2177,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            20 => 
            array (
                'id' => 1551,
                'id_cuenta' => 2178,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            21 => 
            array (
                'id' => 1552,
                'id_cuenta' => 2179,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            22 => 
            array (
                'id' => 1553,
                'id_cuenta' => 2180,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            23 => 
            array (
                'id' => 1554,
                'id_cuenta' => 2181,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            24 => 
            array (
                'id' => 1555,
                'id_cuenta' => 2182,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            25 => 
            array (
                'id' => 1556,
                'id_cuenta' => 2183,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            26 => 
            array (
                'id' => 1557,
                'id_cuenta' => 2184,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            27 => 
            array (
                'id' => 1558,
                'id_cuenta' => 2185,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            28 => 
            array (
                'id' => 1559,
                'id_cuenta' => 2186,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            29 => 
            array (
                'id' => 1560,
                'id_cuenta' => 2187,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            30 => 
            array (
                'id' => 1561,
                'id_cuenta' => 2188,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            31 => 
            array (
                'id' => 1562,
                'id_cuenta' => 2189,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            32 => 
            array (
                'id' => 1563,
                'id_cuenta' => 2190,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            33 => 
            array (
                'id' => 1564,
                'id_cuenta' => 2191,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            34 => 
            array (
                'id' => 1565,
                'id_cuenta' => 2192,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            35 => 
            array (
                'id' => 1566,
                'id_cuenta' => 2193,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            36 => 
            array (
                'id' => 1567,
                'id_cuenta' => 2194,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            37 => 
            array (
                'id' => 1568,
                'id_cuenta' => 2195,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            38 => 
            array (
                'id' => 1569,
                'id_cuenta' => 2196,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            39 => 
            array (
                'id' => 1570,
                'id_cuenta' => 2197,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            40 => 
            array (
                'id' => 1571,
                'id_cuenta' => 2198,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            41 => 
            array (
                'id' => 1572,
                'id_cuenta' => 2199,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            42 => 
            array (
                'id' => 1573,
                'id_cuenta' => 2200,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            43 => 
            array (
                'id' => 1574,
                'id_cuenta' => 2201,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            44 => 
            array (
                'id' => 1575,
                'id_cuenta' => 2202,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            45 => 
            array (
                'id' => 1576,
                'id_cuenta' => 2203,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            46 => 
            array (
                'id' => 1577,
                'id_cuenta' => 2204,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            47 => 
            array (
                'id' => 1578,
                'id_cuenta' => 2205,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            48 => 
            array (
                'id' => 1579,
                'id_cuenta' => 2206,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:48',
                'updated_at' => '2023-12-22 10:48:48',
            ),
            49 => 
            array (
                'id' => 1580,
                'id_cuenta' => 2207,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            50 => 
            array (
                'id' => 1581,
                'id_cuenta' => 2208,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            51 => 
            array (
                'id' => 1582,
                'id_cuenta' => 2209,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            52 => 
            array (
                'id' => 1583,
                'id_cuenta' => 2210,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            53 => 
            array (
                'id' => 1584,
                'id_cuenta' => 2211,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            54 => 
            array (
                'id' => 1585,
                'id_cuenta' => 2212,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            55 => 
            array (
                'id' => 1586,
                'id_cuenta' => 2213,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            56 => 
            array (
                'id' => 1587,
                'id_cuenta' => 2214,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            57 => 
            array (
                'id' => 1588,
                'id_cuenta' => 2215,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            58 => 
            array (
                'id' => 1589,
                'id_cuenta' => 2216,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            59 => 
            array (
                'id' => 1590,
                'id_cuenta' => 2217,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            60 => 
            array (
                'id' => 1591,
                'id_cuenta' => 2218,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            61 => 
            array (
                'id' => 1592,
                'id_cuenta' => 2219,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            62 => 
            array (
                'id' => 1593,
                'id_cuenta' => 2220,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            63 => 
            array (
                'id' => 1594,
                'id_cuenta' => 2221,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            64 => 
            array (
                'id' => 1595,
                'id_cuenta' => 2222,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            65 => 
            array (
                'id' => 1596,
                'id_cuenta' => 2223,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            66 => 
            array (
                'id' => 1597,
                'id_cuenta' => 2224,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            67 => 
            array (
                'id' => 1598,
                'id_cuenta' => 2225,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            68 => 
            array (
                'id' => 1599,
                'id_cuenta' => 2226,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            69 => 
            array (
                'id' => 1600,
                'id_cuenta' => 2227,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            70 => 
            array (
                'id' => 1601,
                'id_cuenta' => 2228,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            71 => 
            array (
                'id' => 1602,
                'id_cuenta' => 2229,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            72 => 
            array (
                'id' => 1603,
                'id_cuenta' => 2230,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            73 => 
            array (
                'id' => 1604,
                'id_cuenta' => 2231,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            74 => 
            array (
                'id' => 1605,
                'id_cuenta' => 2232,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            75 => 
            array (
                'id' => 1606,
                'id_cuenta' => 2233,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            76 => 
            array (
                'id' => 1607,
                'id_cuenta' => 2234,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            77 => 
            array (
                'id' => 1608,
                'id_cuenta' => 2235,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            78 => 
            array (
                'id' => 1609,
                'id_cuenta' => 2236,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            79 => 
            array (
                'id' => 1610,
                'id_cuenta' => 2237,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            80 => 
            array (
                'id' => 1611,
                'id_cuenta' => 2238,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            81 => 
            array (
                'id' => 1612,
                'id_cuenta' => 2239,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            82 => 
            array (
                'id' => 1613,
                'id_cuenta' => 2240,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            83 => 
            array (
                'id' => 1614,
                'id_cuenta' => 2241,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            84 => 
            array (
                'id' => 1615,
                'id_cuenta' => 2242,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            85 => 
            array (
                'id' => 1616,
                'id_cuenta' => 2243,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            86 => 
            array (
                'id' => 1617,
                'id_cuenta' => 2244,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            87 => 
            array (
                'id' => 1618,
                'id_cuenta' => 2245,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            88 => 
            array (
                'id' => 1619,
                'id_cuenta' => 2246,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            89 => 
            array (
                'id' => 1620,
                'id_cuenta' => 2247,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            90 => 
            array (
                'id' => 1621,
                'id_cuenta' => 2248,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            91 => 
            array (
                'id' => 1622,
                'id_cuenta' => 2249,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            92 => 
            array (
                'id' => 1623,
                'id_cuenta' => 2250,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            93 => 
            array (
                'id' => 1624,
                'id_cuenta' => 2251,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            94 => 
            array (
                'id' => 1625,
                'id_cuenta' => 2252,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            95 => 
            array (
                'id' => 1626,
                'id_cuenta' => 2253,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            96 => 
            array (
                'id' => 1627,
                'id_cuenta' => 2254,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            97 => 
            array (
                'id' => 1628,
                'id_cuenta' => 2255,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            98 => 
            array (
                'id' => 1629,
                'id_cuenta' => 2256,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            99 => 
            array (
                'id' => 1630,
                'id_cuenta' => 2257,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            100 => 
            array (
                'id' => 1631,
                'id_cuenta' => 2258,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            101 => 
            array (
                'id' => 1632,
                'id_cuenta' => 2259,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            102 => 
            array (
                'id' => 1633,
                'id_cuenta' => 2260,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            103 => 
            array (
                'id' => 1634,
                'id_cuenta' => 2261,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            104 => 
            array (
                'id' => 1635,
                'id_cuenta' => 2262,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            105 => 
            array (
                'id' => 1636,
                'id_cuenta' => 2263,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            106 => 
            array (
                'id' => 1637,
                'id_cuenta' => 2264,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            107 => 
            array (
                'id' => 1638,
                'id_cuenta' => 2265,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            108 => 
            array (
                'id' => 1639,
                'id_cuenta' => 2266,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            109 => 
            array (
                'id' => 1640,
                'id_cuenta' => 2267,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            110 => 
            array (
                'id' => 1641,
                'id_cuenta' => 2268,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            111 => 
            array (
                'id' => 1642,
                'id_cuenta' => 2269,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            112 => 
            array (
                'id' => 1643,
                'id_cuenta' => 2270,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            113 => 
            array (
                'id' => 1644,
                'id_cuenta' => 2271,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            114 => 
            array (
                'id' => 1645,
                'id_cuenta' => 2272,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            115 => 
            array (
                'id' => 1646,
                'id_cuenta' => 2273,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:49',
                'updated_at' => '2023-12-22 10:48:49',
            ),
            116 => 
            array (
                'id' => 1647,
                'id_cuenta' => 2274,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            117 => 
            array (
                'id' => 1648,
                'id_cuenta' => 2275,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            118 => 
            array (
                'id' => 1649,
                'id_cuenta' => 2276,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            119 => 
            array (
                'id' => 1650,
                'id_cuenta' => 2277,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            120 => 
            array (
                'id' => 1651,
                'id_cuenta' => 2278,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            121 => 
            array (
                'id' => 1652,
                'id_cuenta' => 2279,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            122 => 
            array (
                'id' => 1653,
                'id_cuenta' => 2280,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            123 => 
            array (
                'id' => 1654,
                'id_cuenta' => 2281,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            124 => 
            array (
                'id' => 1655,
                'id_cuenta' => 2282,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            125 => 
            array (
                'id' => 1656,
                'id_cuenta' => 2283,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            126 => 
            array (
                'id' => 1657,
                'id_cuenta' => 2284,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            127 => 
            array (
                'id' => 1658,
                'id_cuenta' => 2285,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            128 => 
            array (
                'id' => 1659,
                'id_cuenta' => 2286,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            129 => 
            array (
                'id' => 1660,
                'id_cuenta' => 2287,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            130 => 
            array (
                'id' => 1661,
                'id_cuenta' => 2288,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            131 => 
            array (
                'id' => 1662,
                'id_cuenta' => 2289,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            132 => 
            array (
                'id' => 1663,
                'id_cuenta' => 2290,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            133 => 
            array (
                'id' => 1664,
                'id_cuenta' => 2291,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            134 => 
            array (
                'id' => 1665,
                'id_cuenta' => 2292,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            135 => 
            array (
                'id' => 1666,
                'id_cuenta' => 2293,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            136 => 
            array (
                'id' => 1667,
                'id_cuenta' => 2294,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            137 => 
            array (
                'id' => 1668,
                'id_cuenta' => 2295,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            138 => 
            array (
                'id' => 1669,
                'id_cuenta' => 2296,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            139 => 
            array (
                'id' => 1670,
                'id_cuenta' => 2297,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            140 => 
            array (
                'id' => 1671,
                'id_cuenta' => 2298,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            141 => 
            array (
                'id' => 1672,
                'id_cuenta' => 2299,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            142 => 
            array (
                'id' => 1673,
                'id_cuenta' => 2300,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            143 => 
            array (
                'id' => 1674,
                'id_cuenta' => 2301,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            144 => 
            array (
                'id' => 1675,
                'id_cuenta' => 2302,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            145 => 
            array (
                'id' => 1676,
                'id_cuenta' => 2303,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            146 => 
            array (
                'id' => 1677,
                'id_cuenta' => 2304,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            147 => 
            array (
                'id' => 1678,
                'id_cuenta' => 2305,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            148 => 
            array (
                'id' => 1679,
                'id_cuenta' => 2306,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            149 => 
            array (
                'id' => 1680,
                'id_cuenta' => 2307,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            150 => 
            array (
                'id' => 1681,
                'id_cuenta' => 2308,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            151 => 
            array (
                'id' => 1682,
                'id_cuenta' => 2309,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            152 => 
            array (
                'id' => 1683,
                'id_cuenta' => 2310,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            153 => 
            array (
                'id' => 1684,
                'id_cuenta' => 2311,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            154 => 
            array (
                'id' => 1685,
                'id_cuenta' => 2312,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            155 => 
            array (
                'id' => 1686,
                'id_cuenta' => 2313,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            156 => 
            array (
                'id' => 1687,
                'id_cuenta' => 2314,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            157 => 
            array (
                'id' => 1688,
                'id_cuenta' => 2315,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            158 => 
            array (
                'id' => 1689,
                'id_cuenta' => 2316,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            159 => 
            array (
                'id' => 1690,
                'id_cuenta' => 2317,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            160 => 
            array (
                'id' => 1691,
                'id_cuenta' => 2318,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            161 => 
            array (
                'id' => 1692,
                'id_cuenta' => 2319,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            162 => 
            array (
                'id' => 1693,
                'id_cuenta' => 2320,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            163 => 
            array (
                'id' => 1694,
                'id_cuenta' => 2321,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            164 => 
            array (
                'id' => 1695,
                'id_cuenta' => 2322,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            165 => 
            array (
                'id' => 1696,
                'id_cuenta' => 2323,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            166 => 
            array (
                'id' => 1697,
                'id_cuenta' => 2324,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            167 => 
            array (
                'id' => 1698,
                'id_cuenta' => 2325,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            168 => 
            array (
                'id' => 1699,
                'id_cuenta' => 2326,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            169 => 
            array (
                'id' => 1700,
                'id_cuenta' => 2327,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            170 => 
            array (
                'id' => 1701,
                'id_cuenta' => 2328,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            171 => 
            array (
                'id' => 1702,
                'id_cuenta' => 2329,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            172 => 
            array (
                'id' => 1703,
                'id_cuenta' => 2330,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            173 => 
            array (
                'id' => 1704,
                'id_cuenta' => 2331,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            174 => 
            array (
                'id' => 1705,
                'id_cuenta' => 2332,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            175 => 
            array (
                'id' => 1706,
                'id_cuenta' => 2333,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            176 => 
            array (
                'id' => 1707,
                'id_cuenta' => 2334,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            177 => 
            array (
                'id' => 1708,
                'id_cuenta' => 2335,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            178 => 
            array (
                'id' => 1709,
                'id_cuenta' => 2336,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            179 => 
            array (
                'id' => 1710,
                'id_cuenta' => 2337,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            180 => 
            array (
                'id' => 1711,
                'id_cuenta' => 2338,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            181 => 
            array (
                'id' => 1712,
                'id_cuenta' => 2339,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            182 => 
            array (
                'id' => 1713,
                'id_cuenta' => 2340,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            183 => 
            array (
                'id' => 1714,
                'id_cuenta' => 2341,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            184 => 
            array (
                'id' => 1715,
                'id_cuenta' => 2342,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:50',
                'updated_at' => '2023-12-22 10:48:50',
            ),
            185 => 
            array (
                'id' => 1716,
                'id_cuenta' => 2343,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            186 => 
            array (
                'id' => 1717,
                'id_cuenta' => 2344,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            187 => 
            array (
                'id' => 1718,
                'id_cuenta' => 2345,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            188 => 
            array (
                'id' => 1719,
                'id_cuenta' => 2346,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            189 => 
            array (
                'id' => 1720,
                'id_cuenta' => 2347,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            190 => 
            array (
                'id' => 1721,
                'id_cuenta' => 2348,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            191 => 
            array (
                'id' => 1722,
                'id_cuenta' => 2349,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            192 => 
            array (
                'id' => 1723,
                'id_cuenta' => 2350,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            193 => 
            array (
                'id' => 1724,
                'id_cuenta' => 2351,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            194 => 
            array (
                'id' => 1725,
                'id_cuenta' => 2352,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            195 => 
            array (
                'id' => 1726,
                'id_cuenta' => 2353,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            196 => 
            array (
                'id' => 1727,
                'id_cuenta' => 2354,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            197 => 
            array (
                'id' => 1728,
                'id_cuenta' => 2355,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            198 => 
            array (
                'id' => 1729,
                'id_cuenta' => 2356,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            199 => 
            array (
                'id' => 1730,
                'id_cuenta' => 2357,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            200 => 
            array (
                'id' => 1731,
                'id_cuenta' => 2358,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            201 => 
            array (
                'id' => 1732,
                'id_cuenta' => 2359,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            202 => 
            array (
                'id' => 1733,
                'id_cuenta' => 2360,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            203 => 
            array (
                'id' => 1734,
                'id_cuenta' => 2361,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            204 => 
            array (
                'id' => 1735,
                'id_cuenta' => 2362,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            205 => 
            array (
                'id' => 1736,
                'id_cuenta' => 2363,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            206 => 
            array (
                'id' => 1737,
                'id_cuenta' => 2364,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            207 => 
            array (
                'id' => 1738,
                'id_cuenta' => 2365,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            208 => 
            array (
                'id' => 1739,
                'id_cuenta' => 2366,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            209 => 
            array (
                'id' => 1740,
                'id_cuenta' => 2367,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            210 => 
            array (
                'id' => 1741,
                'id_cuenta' => 2368,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            211 => 
            array (
                'id' => 1742,
                'id_cuenta' => 2369,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            212 => 
            array (
                'id' => 1743,
                'id_cuenta' => 2370,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            213 => 
            array (
                'id' => 1744,
                'id_cuenta' => 2371,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            214 => 
            array (
                'id' => 1745,
                'id_cuenta' => 2372,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            215 => 
            array (
                'id' => 1746,
                'id_cuenta' => 2373,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            216 => 
            array (
                'id' => 1747,
                'id_cuenta' => 2374,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            217 => 
            array (
                'id' => 1748,
                'id_cuenta' => 2375,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            218 => 
            array (
                'id' => 1749,
                'id_cuenta' => 2376,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            219 => 
            array (
                'id' => 1750,
                'id_cuenta' => 2377,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            220 => 
            array (
                'id' => 1751,
                'id_cuenta' => 2378,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            221 => 
            array (
                'id' => 1752,
                'id_cuenta' => 2379,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            222 => 
            array (
                'id' => 1753,
                'id_cuenta' => 2380,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            223 => 
            array (
                'id' => 1754,
                'id_cuenta' => 2381,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            224 => 
            array (
                'id' => 1755,
                'id_cuenta' => 2382,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            225 => 
            array (
                'id' => 1756,
                'id_cuenta' => 2383,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            226 => 
            array (
                'id' => 1757,
                'id_cuenta' => 2384,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            227 => 
            array (
                'id' => 1758,
                'id_cuenta' => 2385,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            228 => 
            array (
                'id' => 1759,
                'id_cuenta' => 2386,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            229 => 
            array (
                'id' => 1760,
                'id_cuenta' => 2387,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            230 => 
            array (
                'id' => 1761,
                'id_cuenta' => 2388,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            231 => 
            array (
                'id' => 1762,
                'id_cuenta' => 2389,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            232 => 
            array (
                'id' => 1763,
                'id_cuenta' => 2390,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            233 => 
            array (
                'id' => 1764,
                'id_cuenta' => 2391,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            234 => 
            array (
                'id' => 1765,
                'id_cuenta' => 2392,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            235 => 
            array (
                'id' => 1766,
                'id_cuenta' => 2393,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            236 => 
            array (
                'id' => 1767,
                'id_cuenta' => 2394,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            237 => 
            array (
                'id' => 1768,
                'id_cuenta' => 2395,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            238 => 
            array (
                'id' => 1769,
                'id_cuenta' => 2396,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            239 => 
            array (
                'id' => 1770,
                'id_cuenta' => 2397,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            240 => 
            array (
                'id' => 1771,
                'id_cuenta' => 2398,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            241 => 
            array (
                'id' => 1772,
                'id_cuenta' => 2399,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            242 => 
            array (
                'id' => 1773,
                'id_cuenta' => 2400,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:48:51',
                'updated_at' => '2023-12-22 10:48:51',
            ),
            243 => 
            array (
                'id' => 1774,
                'id_cuenta' => 2401,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            244 => 
            array (
                'id' => 1775,
                'id_cuenta' => 2402,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            245 => 
            array (
                'id' => 1776,
                'id_cuenta' => 2403,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            246 => 
            array (
                'id' => 1777,
                'id_cuenta' => 2404,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            247 => 
            array (
                'id' => 1778,
                'id_cuenta' => 2405,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            248 => 
            array (
                'id' => 1779,
                'id_cuenta' => 2406,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            249 => 
            array (
                'id' => 1780,
                'id_cuenta' => 2407,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            250 => 
            array (
                'id' => 1781,
                'id_cuenta' => 2408,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            251 => 
            array (
                'id' => 1782,
                'id_cuenta' => 2409,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            252 => 
            array (
                'id' => 1783,
                'id_cuenta' => 2410,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            253 => 
            array (
                'id' => 1784,
                'id_cuenta' => 2411,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            254 => 
            array (
                'id' => 1785,
                'id_cuenta' => 2412,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            255 => 
            array (
                'id' => 1786,
                'id_cuenta' => 2413,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            256 => 
            array (
                'id' => 1787,
                'id_cuenta' => 2414,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            257 => 
            array (
                'id' => 1788,
                'id_cuenta' => 2415,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            258 => 
            array (
                'id' => 1789,
                'id_cuenta' => 2416,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            259 => 
            array (
                'id' => 1790,
                'id_cuenta' => 2417,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            260 => 
            array (
                'id' => 1791,
                'id_cuenta' => 2418,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            261 => 
            array (
                'id' => 1792,
                'id_cuenta' => 2419,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            262 => 
            array (
                'id' => 1793,
                'id_cuenta' => 2420,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            263 => 
            array (
                'id' => 1794,
                'id_cuenta' => 2421,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            264 => 
            array (
                'id' => 1795,
                'id_cuenta' => 2422,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            265 => 
            array (
                'id' => 1796,
                'id_cuenta' => 2423,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            266 => 
            array (
                'id' => 1797,
                'id_cuenta' => 2424,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            267 => 
            array (
                'id' => 1798,
                'id_cuenta' => 2425,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            268 => 
            array (
                'id' => 1799,
                'id_cuenta' => 2426,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            269 => 
            array (
                'id' => 1800,
                'id_cuenta' => 2427,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            270 => 
            array (
                'id' => 1801,
                'id_cuenta' => 2428,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            271 => 
            array (
                'id' => 1802,
                'id_cuenta' => 2429,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            272 => 
            array (
                'id' => 1803,
                'id_cuenta' => 2430,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            273 => 
            array (
                'id' => 1804,
                'id_cuenta' => 2431,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            274 => 
            array (
                'id' => 1805,
                'id_cuenta' => 2432,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:05',
                'updated_at' => '2023-12-22 10:50:05',
            ),
            275 => 
            array (
                'id' => 1806,
                'id_cuenta' => 2433,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            276 => 
            array (
                'id' => 1807,
                'id_cuenta' => 2434,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            277 => 
            array (
                'id' => 1808,
                'id_cuenta' => 2435,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            278 => 
            array (
                'id' => 1809,
                'id_cuenta' => 2436,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            279 => 
            array (
                'id' => 1810,
                'id_cuenta' => 2437,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            280 => 
            array (
                'id' => 1811,
                'id_cuenta' => 2438,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            281 => 
            array (
                'id' => 1812,
                'id_cuenta' => 2439,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            282 => 
            array (
                'id' => 1813,
                'id_cuenta' => 2440,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            283 => 
            array (
                'id' => 1814,
                'id_cuenta' => 2441,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            284 => 
            array (
                'id' => 1815,
                'id_cuenta' => 2442,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            285 => 
            array (
                'id' => 1816,
                'id_cuenta' => 2443,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            286 => 
            array (
                'id' => 1817,
                'id_cuenta' => 2444,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            287 => 
            array (
                'id' => 1818,
                'id_cuenta' => 2445,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            288 => 
            array (
                'id' => 1819,
                'id_cuenta' => 2446,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            289 => 
            array (
                'id' => 1820,
                'id_cuenta' => 2447,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            290 => 
            array (
                'id' => 1821,
                'id_cuenta' => 2448,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            291 => 
            array (
                'id' => 1822,
                'id_cuenta' => 2449,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            292 => 
            array (
                'id' => 1823,
                'id_cuenta' => 2450,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            293 => 
            array (
                'id' => 1824,
                'id_cuenta' => 2451,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            294 => 
            array (
                'id' => 1825,
                'id_cuenta' => 2452,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            295 => 
            array (
                'id' => 1826,
                'id_cuenta' => 2453,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            296 => 
            array (
                'id' => 1827,
                'id_cuenta' => 2454,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            297 => 
            array (
                'id' => 1828,
                'id_cuenta' => 2455,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            298 => 
            array (
                'id' => 1829,
                'id_cuenta' => 2456,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            299 => 
            array (
                'id' => 1830,
                'id_cuenta' => 2457,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            300 => 
            array (
                'id' => 1831,
                'id_cuenta' => 2458,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            301 => 
            array (
                'id' => 1832,
                'id_cuenta' => 2459,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            302 => 
            array (
                'id' => 1833,
                'id_cuenta' => 2460,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            303 => 
            array (
                'id' => 1834,
                'id_cuenta' => 2461,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            304 => 
            array (
                'id' => 1835,
                'id_cuenta' => 2462,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            305 => 
            array (
                'id' => 1836,
                'id_cuenta' => 2463,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            306 => 
            array (
                'id' => 1837,
                'id_cuenta' => 2464,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            307 => 
            array (
                'id' => 1838,
                'id_cuenta' => 2465,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            308 => 
            array (
                'id' => 1839,
                'id_cuenta' => 2466,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            309 => 
            array (
                'id' => 1840,
                'id_cuenta' => 2467,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            310 => 
            array (
                'id' => 1841,
                'id_cuenta' => 2468,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            311 => 
            array (
                'id' => 1842,
                'id_cuenta' => 2469,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            312 => 
            array (
                'id' => 1843,
                'id_cuenta' => 2470,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            313 => 
            array (
                'id' => 1844,
                'id_cuenta' => 2471,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            314 => 
            array (
                'id' => 1845,
                'id_cuenta' => 2472,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            315 => 
            array (
                'id' => 1846,
                'id_cuenta' => 2473,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            316 => 
            array (
                'id' => 1847,
                'id_cuenta' => 2474,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            317 => 
            array (
                'id' => 1848,
                'id_cuenta' => 2475,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            318 => 
            array (
                'id' => 1849,
                'id_cuenta' => 2476,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            319 => 
            array (
                'id' => 1850,
                'id_cuenta' => 2477,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            320 => 
            array (
                'id' => 1851,
                'id_cuenta' => 2478,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            321 => 
            array (
                'id' => 1852,
                'id_cuenta' => 2479,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            322 => 
            array (
                'id' => 1853,
                'id_cuenta' => 2480,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            323 => 
            array (
                'id' => 1854,
                'id_cuenta' => 2481,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            324 => 
            array (
                'id' => 1855,
                'id_cuenta' => 2482,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            325 => 
            array (
                'id' => 1856,
                'id_cuenta' => 2483,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            326 => 
            array (
                'id' => 1857,
                'id_cuenta' => 2484,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            327 => 
            array (
                'id' => 1858,
                'id_cuenta' => 2485,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            328 => 
            array (
                'id' => 1859,
                'id_cuenta' => 2486,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            329 => 
            array (
                'id' => 1860,
                'id_cuenta' => 2487,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            330 => 
            array (
                'id' => 1861,
                'id_cuenta' => 2488,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            331 => 
            array (
                'id' => 1862,
                'id_cuenta' => 2489,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            332 => 
            array (
                'id' => 1863,
                'id_cuenta' => 2490,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            333 => 
            array (
                'id' => 1864,
                'id_cuenta' => 2491,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            334 => 
            array (
                'id' => 1865,
                'id_cuenta' => 2492,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            335 => 
            array (
                'id' => 1866,
                'id_cuenta' => 2493,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            336 => 
            array (
                'id' => 1867,
                'id_cuenta' => 2494,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            337 => 
            array (
                'id' => 1868,
                'id_cuenta' => 2495,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            338 => 
            array (
                'id' => 1869,
                'id_cuenta' => 2496,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            339 => 
            array (
                'id' => 1870,
                'id_cuenta' => 2497,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            340 => 
            array (
                'id' => 1871,
                'id_cuenta' => 2498,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            341 => 
            array (
                'id' => 1872,
                'id_cuenta' => 2499,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            342 => 
            array (
                'id' => 1873,
                'id_cuenta' => 2500,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            343 => 
            array (
                'id' => 1874,
                'id_cuenta' => 2501,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            344 => 
            array (
                'id' => 1875,
                'id_cuenta' => 2502,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            345 => 
            array (
                'id' => 1876,
                'id_cuenta' => 2503,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            346 => 
            array (
                'id' => 1877,
                'id_cuenta' => 2504,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            347 => 
            array (
                'id' => 1878,
                'id_cuenta' => 2505,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            348 => 
            array (
                'id' => 1879,
                'id_cuenta' => 2506,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            349 => 
            array (
                'id' => 1880,
                'id_cuenta' => 2507,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            350 => 
            array (
                'id' => 1881,
                'id_cuenta' => 2508,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            351 => 
            array (
                'id' => 1882,
                'id_cuenta' => 2509,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:06',
                'updated_at' => '2023-12-22 10:50:06',
            ),
            352 => 
            array (
                'id' => 1883,
                'id_cuenta' => 2510,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            353 => 
            array (
                'id' => 1884,
                'id_cuenta' => 2511,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            354 => 
            array (
                'id' => 1885,
                'id_cuenta' => 2512,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            355 => 
            array (
                'id' => 1886,
                'id_cuenta' => 2513,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            356 => 
            array (
                'id' => 1887,
                'id_cuenta' => 2514,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            357 => 
            array (
                'id' => 1888,
                'id_cuenta' => 2515,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            358 => 
            array (
                'id' => 1889,
                'id_cuenta' => 2516,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            359 => 
            array (
                'id' => 1890,
                'id_cuenta' => 2517,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            360 => 
            array (
                'id' => 1891,
                'id_cuenta' => 2518,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            361 => 
            array (
                'id' => 1892,
                'id_cuenta' => 2519,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            362 => 
            array (
                'id' => 1893,
                'id_cuenta' => 2520,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            363 => 
            array (
                'id' => 1894,
                'id_cuenta' => 2521,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            364 => 
            array (
                'id' => 1895,
                'id_cuenta' => 2522,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            365 => 
            array (
                'id' => 1896,
                'id_cuenta' => 2523,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            366 => 
            array (
                'id' => 1897,
                'id_cuenta' => 2524,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            367 => 
            array (
                'id' => 1898,
                'id_cuenta' => 2525,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            368 => 
            array (
                'id' => 1899,
                'id_cuenta' => 2526,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            369 => 
            array (
                'id' => 1900,
                'id_cuenta' => 2527,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            370 => 
            array (
                'id' => 1901,
                'id_cuenta' => 2528,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            371 => 
            array (
                'id' => 1902,
                'id_cuenta' => 2529,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            372 => 
            array (
                'id' => 1903,
                'id_cuenta' => 2530,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            373 => 
            array (
                'id' => 1904,
                'id_cuenta' => 2531,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            374 => 
            array (
                'id' => 1905,
                'id_cuenta' => 2532,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            375 => 
            array (
                'id' => 1906,
                'id_cuenta' => 2533,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            376 => 
            array (
                'id' => 1907,
                'id_cuenta' => 2534,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            377 => 
            array (
                'id' => 1908,
                'id_cuenta' => 2535,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            378 => 
            array (
                'id' => 1909,
                'id_cuenta' => 2536,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            379 => 
            array (
                'id' => 1910,
                'id_cuenta' => 2537,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            380 => 
            array (
                'id' => 1911,
                'id_cuenta' => 2538,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            381 => 
            array (
                'id' => 1912,
                'id_cuenta' => 2539,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            382 => 
            array (
                'id' => 1913,
                'id_cuenta' => 2540,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            383 => 
            array (
                'id' => 1914,
                'id_cuenta' => 2541,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            384 => 
            array (
                'id' => 1915,
                'id_cuenta' => 2542,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            385 => 
            array (
                'id' => 1916,
                'id_cuenta' => 2543,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            386 => 
            array (
                'id' => 1917,
                'id_cuenta' => 2544,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            387 => 
            array (
                'id' => 1918,
                'id_cuenta' => 2545,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            388 => 
            array (
                'id' => 1919,
                'id_cuenta' => 2546,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            389 => 
            array (
                'id' => 1920,
                'id_cuenta' => 2547,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            390 => 
            array (
                'id' => 1921,
                'id_cuenta' => 2548,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            391 => 
            array (
                'id' => 1922,
                'id_cuenta' => 2549,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            392 => 
            array (
                'id' => 1923,
                'id_cuenta' => 2550,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            393 => 
            array (
                'id' => 1924,
                'id_cuenta' => 2551,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            394 => 
            array (
                'id' => 1925,
                'id_cuenta' => 2552,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            395 => 
            array (
                'id' => 1926,
                'id_cuenta' => 2553,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            396 => 
            array (
                'id' => 1927,
                'id_cuenta' => 2554,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            397 => 
            array (
                'id' => 1928,
                'id_cuenta' => 2555,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            398 => 
            array (
                'id' => 1929,
                'id_cuenta' => 2556,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            399 => 
            array (
                'id' => 1930,
                'id_cuenta' => 2557,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            400 => 
            array (
                'id' => 1931,
                'id_cuenta' => 2558,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            401 => 
            array (
                'id' => 1932,
                'id_cuenta' => 2559,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            402 => 
            array (
                'id' => 1933,
                'id_cuenta' => 2560,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            403 => 
            array (
                'id' => 1934,
                'id_cuenta' => 2561,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            404 => 
            array (
                'id' => 1935,
                'id_cuenta' => 2562,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            405 => 
            array (
                'id' => 1936,
                'id_cuenta' => 2563,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            406 => 
            array (
                'id' => 1937,
                'id_cuenta' => 2564,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            407 => 
            array (
                'id' => 1938,
                'id_cuenta' => 2565,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            408 => 
            array (
                'id' => 1939,
                'id_cuenta' => 2566,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            409 => 
            array (
                'id' => 1940,
                'id_cuenta' => 2567,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            410 => 
            array (
                'id' => 1941,
                'id_cuenta' => 2568,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            411 => 
            array (
                'id' => 1942,
                'id_cuenta' => 2569,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            412 => 
            array (
                'id' => 1943,
                'id_cuenta' => 2570,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            413 => 
            array (
                'id' => 1944,
                'id_cuenta' => 2571,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            414 => 
            array (
                'id' => 1945,
                'id_cuenta' => 2572,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            415 => 
            array (
                'id' => 1946,
                'id_cuenta' => 2573,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            416 => 
            array (
                'id' => 1947,
                'id_cuenta' => 2574,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            417 => 
            array (
                'id' => 1948,
                'id_cuenta' => 2575,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            418 => 
            array (
                'id' => 1949,
                'id_cuenta' => 2576,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            419 => 
            array (
                'id' => 1950,
                'id_cuenta' => 2577,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            420 => 
            array (
                'id' => 1951,
                'id_cuenta' => 2578,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            421 => 
            array (
                'id' => 1952,
                'id_cuenta' => 2579,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            422 => 
            array (
                'id' => 1953,
                'id_cuenta' => 2580,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            423 => 
            array (
                'id' => 1954,
                'id_cuenta' => 2581,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            424 => 
            array (
                'id' => 1955,
                'id_cuenta' => 2582,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            425 => 
            array (
                'id' => 1956,
                'id_cuenta' => 2583,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            426 => 
            array (
                'id' => 1957,
                'id_cuenta' => 2584,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            427 => 
            array (
                'id' => 1958,
                'id_cuenta' => 2585,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            428 => 
            array (
                'id' => 1959,
                'id_cuenta' => 2586,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            429 => 
            array (
                'id' => 1960,
                'id_cuenta' => 2587,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            430 => 
            array (
                'id' => 1961,
                'id_cuenta' => 2588,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            431 => 
            array (
                'id' => 1962,
                'id_cuenta' => 2589,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            432 => 
            array (
                'id' => 1963,
                'id_cuenta' => 2590,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            433 => 
            array (
                'id' => 1964,
                'id_cuenta' => 2591,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            434 => 
            array (
                'id' => 1965,
                'id_cuenta' => 2592,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            435 => 
            array (
                'id' => 1966,
                'id_cuenta' => 2593,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            436 => 
            array (
                'id' => 1967,
                'id_cuenta' => 2594,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:07',
                'updated_at' => '2023-12-22 10:50:07',
            ),
            437 => 
            array (
                'id' => 1968,
                'id_cuenta' => 2595,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            438 => 
            array (
                'id' => 1969,
                'id_cuenta' => 2596,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            439 => 
            array (
                'id' => 1970,
                'id_cuenta' => 2597,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            440 => 
            array (
                'id' => 1971,
                'id_cuenta' => 2598,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            441 => 
            array (
                'id' => 1972,
                'id_cuenta' => 2599,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            442 => 
            array (
                'id' => 1973,
                'id_cuenta' => 2600,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            443 => 
            array (
                'id' => 1974,
                'id_cuenta' => 2601,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            444 => 
            array (
                'id' => 1975,
                'id_cuenta' => 2602,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            445 => 
            array (
                'id' => 1976,
                'id_cuenta' => 2603,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            446 => 
            array (
                'id' => 1977,
                'id_cuenta' => 2604,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            447 => 
            array (
                'id' => 1978,
                'id_cuenta' => 2605,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            448 => 
            array (
                'id' => 1979,
                'id_cuenta' => 2606,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            449 => 
            array (
                'id' => 1980,
                'id_cuenta' => 2607,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            450 => 
            array (
                'id' => 1981,
                'id_cuenta' => 2608,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            451 => 
            array (
                'id' => 1982,
                'id_cuenta' => 2609,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            452 => 
            array (
                'id' => 1983,
                'id_cuenta' => 2610,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            453 => 
            array (
                'id' => 1984,
                'id_cuenta' => 2611,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            454 => 
            array (
                'id' => 1985,
                'id_cuenta' => 2612,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            455 => 
            array (
                'id' => 1986,
                'id_cuenta' => 2613,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            456 => 
            array (
                'id' => 1987,
                'id_cuenta' => 2614,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            457 => 
            array (
                'id' => 1988,
                'id_cuenta' => 2615,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            458 => 
            array (
                'id' => 1989,
                'id_cuenta' => 2616,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            459 => 
            array (
                'id' => 1990,
                'id_cuenta' => 2617,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:08',
                'updated_at' => '2023-12-22 10:50:08',
            ),
            460 => 
            array (
                'id' => 1991,
                'id_cuenta' => 2758,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:09',
                'updated_at' => '2023-12-22 10:50:09',
            ),
            461 => 
            array (
                'id' => 1992,
                'id_cuenta' => 2759,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:09',
                'updated_at' => '2023-12-22 10:50:09',
            ),
            462 => 
            array (
                'id' => 1993,
                'id_cuenta' => 2760,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:09',
                'updated_at' => '2023-12-22 10:50:09',
            ),
            463 => 
            array (
                'id' => 1994,
                'id_cuenta' => 2761,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:09',
                'updated_at' => '2023-12-22 10:50:09',
            ),
            464 => 
            array (
                'id' => 1995,
                'id_cuenta' => 2762,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:09',
                'updated_at' => '2023-12-22 10:50:09',
            ),
            465 => 
            array (
                'id' => 1996,
                'id_cuenta' => 2763,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:09',
                'updated_at' => '2023-12-22 10:50:09',
            ),
            466 => 
            array (
                'id' => 1997,
                'id_cuenta' => 2764,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:09',
                'updated_at' => '2023-12-22 10:50:09',
            ),
            467 => 
            array (
                'id' => 1998,
                'id_cuenta' => 2765,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-22 10:50:09',
                'updated_at' => '2023-12-22 10:50:09',
            ),
            468 => 
            array (
                'id' => 1999,
                'id_cuenta' => 2766,
                'id_tipo_cuenta' => 2,
                'created_at' => '2023-12-22 10:50:09',
                'updated_at' => '2023-12-22 10:50:09',
            ),
            469 => 
            array (
                'id' => 2000,
                'id_cuenta' => 2767,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-22 10:50:09',
                'updated_at' => '2023-12-22 10:50:09',
            ),
            470 => 
            array (
                'id' => 2001,
                'id_cuenta' => 2769,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:09',
                'updated_at' => '2023-12-22 10:50:09',
            ),
            471 => 
            array (
                'id' => 2002,
                'id_cuenta' => 2770,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:09',
                'updated_at' => '2023-12-22 10:50:09',
            ),
            472 => 
            array (
                'id' => 2003,
                'id_cuenta' => 2771,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:09',
                'updated_at' => '2023-12-22 10:50:09',
            ),
            473 => 
            array (
                'id' => 2004,
                'id_cuenta' => 2772,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-22 10:50:09',
                'updated_at' => '2023-12-22 10:50:09',
            ),
            474 => 
            array (
                'id' => 2005,
                'id_cuenta' => 2773,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:50:09',
                'updated_at' => '2023-12-22 10:50:09',
            ),
            475 => 
            array (
                'id' => 2007,
                'id_cuenta' => 2775,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:50:09',
                'updated_at' => '2023-12-22 10:50:09',
            ),
            476 => 
            array (
                'id' => 2008,
                'id_cuenta' => 2776,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-22 10:50:09',
                'updated_at' => '2023-12-22 10:50:09',
            ),
            477 => 
            array (
                'id' => 2009,
                'id_cuenta' => 2777,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-22 10:50:09',
                'updated_at' => '2023-12-22 10:50:09',
            ),
            478 => 
            array (
                'id' => 2022,
                'id_cuenta' => 2153,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-23 11:24:34',
                'updated_at' => '2023-12-23 11:24:34',
            ),
            479 => 
            array (
                'id' => 2023,
                'id_cuenta' => 1710,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-23 11:50:22',
                'updated_at' => '2023-12-23 11:50:22',
            ),
            480 => 
            array (
                'id' => 2024,
                'id_cuenta' => 1708,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-23 11:51:25',
                'updated_at' => '2023-12-23 11:51:25',
            ),
            481 => 
            array (
                'id' => 2026,
                'id_cuenta' => 1234,
                'id_tipo_cuenta' => 6,
                'created_at' => '2023-12-23 12:00:11',
                'updated_at' => '2023-12-23 12:00:11',
            ),
            482 => 
            array (
                'id' => 2027,
                'id_cuenta' => 295,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-23 12:16:19',
                'updated_at' => '2023-12-23 12:16:19',
            ),
            483 => 
            array (
                'id' => 2028,
                'id_cuenta' => 212,
                'id_tipo_cuenta' => 13,
                'created_at' => '2023-12-23 16:53:12',
                'updated_at' => '2023-12-23 16:53:12',
            ),
            484 => 
            array (
                'id' => 2029,
                'id_cuenta' => 167,
                'id_tipo_cuenta' => 3,
                'created_at' => '2023-12-23 16:54:17',
                'updated_at' => '2023-12-23 16:54:17',
            ),
            485 => 
            array (
                'id' => 2030,
                'id_cuenta' => 172,
                'id_tipo_cuenta' => 7,
                'created_at' => '2023-12-23 16:54:39',
                'updated_at' => '2023-12-23 16:54:39',
            ),
            486 => 
            array (
                'id' => 2032,
                'id_cuenta' => 374,
                'id_tipo_cuenta' => 5,
                'created_at' => '2023-12-23 17:19:39',
                'updated_at' => '2023-12-23 17:19:39',
            ),
            487 => 
            array (
                'id' => 2033,
                'id_cuenta' => 840,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-23 18:25:43',
                'updated_at' => '2023-12-23 18:25:43',
            ),
            488 => 
            array (
                'id' => 2034,
                'id_cuenta' => 800,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-23 18:28:01',
                'updated_at' => '2023-12-23 18:28:01',
            ),
            489 => 
            array (
                'id' => 2035,
                'id_cuenta' => 802,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-23 19:53:52',
                'updated_at' => '2023-12-23 19:53:52',
            ),
            490 => 
            array (
                'id' => 2037,
                'id_cuenta' => 814,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-23 19:56:45',
                'updated_at' => '2023-12-23 19:56:45',
            ),
            491 => 
            array (
                'id' => 2038,
                'id_cuenta' => 835,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-23 19:57:23',
                'updated_at' => '2023-12-23 19:57:23',
            ),
            492 => 
            array (
                'id' => 2039,
                'id_cuenta' => 836,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-23 19:57:37',
                'updated_at' => '2023-12-23 19:57:37',
            ),
            493 => 
            array (
                'id' => 2040,
                'id_cuenta' => 808,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-23 19:59:02',
                'updated_at' => '2023-12-23 19:59:02',
            ),
            494 => 
            array (
                'id' => 2042,
                'id_cuenta' => 919,
                'id_tipo_cuenta' => 9,
                'created_at' => '2023-12-23 20:06:38',
                'updated_at' => '2023-12-23 20:06:38',
            ),
            495 => 
            array (
                'id' => 2043,
                'id_cuenta' => 899,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-23 20:29:43',
                'updated_at' => '2023-12-23 20:29:43',
            ),
            496 => 
            array (
                'id' => 2045,
                'id_cuenta' => 2151,
                'id_tipo_cuenta' => 1,
                'created_at' => '2023-12-23 20:59:56',
                'updated_at' => '2023-12-23 20:59:56',
            ),
            497 => 
            array (
                'id' => 2046,
                'id_cuenta' => 2785,
                'id_tipo_cuenta' => 3,
                'created_at' => '2023-12-25 21:08:41',
                'updated_at' => '2023-12-25 21:08:41',
            ),
            498 => 
            array (
                'id' => 2047,
                'id_cuenta' => 2786,
                'id_tipo_cuenta' => 3,
                'created_at' => '2023-12-25 21:08:53',
                'updated_at' => '2023-12-25 21:08:53',
            ),
            499 => 
            array (
                'id' => 2048,
                'id_cuenta' => 2787,
                'id_tipo_cuenta' => 3,
                'created_at' => '2023-12-25 21:09:04',
                'updated_at' => '2023-12-25 21:09:04',
            ),
        ));
        \DB::table('plan_cuentas_tipos')->insert(array (
            0 => 
            array (
                'id' => 2049,
                'id_cuenta' => 2788,
                'id_tipo_cuenta' => 3,
                'created_at' => '2023-12-25 21:09:09',
                'updated_at' => '2023-12-25 21:09:09',
            ),
            1 => 
            array (
                'id' => 2054,
                'id_cuenta' => 2789,
                'id_tipo_cuenta' => 3,
                'created_at' => '2023-12-25 21:16:47',
                'updated_at' => '2023-12-25 21:16:47',
            ),
            2 => 
            array (
                'id' => 2056,
                'id_cuenta' => 2790,
                'id_tipo_cuenta' => 4,
                'created_at' => '2023-12-28 08:34:59',
                'updated_at' => '2023-12-28 08:34:59',
            ),
            3 => 
            array (
                'id' => 2065,
                'id_cuenta' => 1080,
                'id_tipo_cuenta' => 8,
                'created_at' => '2023-12-31 09:57:59',
                'updated_at' => '2023-12-31 09:57:59',
            ),
            4 => 
            array (
                'id' => 2067,
                'id_cuenta' => 3,
                'id_tipo_cuenta' => 2,
                'created_at' => '2023-12-31 10:07:18',
                'updated_at' => '2023-12-31 10:07:18',
            ),
            5 => 
            array (
                'id' => 2068,
                'id_cuenta' => 7,
                'id_tipo_cuenta' => 2,
                'created_at' => '2023-12-31 10:07:27',
                'updated_at' => '2023-12-31 10:07:27',
            ),
            6 => 
            array (
                'id' => 2069,
                'id_cuenta' => 14,
                'id_tipo_cuenta' => 2,
                'created_at' => '2023-12-31 10:07:37',
                'updated_at' => '2023-12-31 10:07:37',
            ),
            7 => 
            array (
                'id' => 2071,
                'id_cuenta' => 907,
                'id_tipo_cuenta' => 16,
                'created_at' => '2023-12-31 10:31:34',
                'updated_at' => '2023-12-31 10:31:34',
            ),
            8 => 
            array (
                'id' => 2072,
                'id_cuenta' => 909,
                'id_tipo_cuenta' => 16,
                'created_at' => '2023-12-31 10:31:58',
                'updated_at' => '2023-12-31 10:31:58',
            ),
            9 => 
            array (
                'id' => 2073,
                'id_cuenta' => 2774,
                'id_tipo_cuenta' => 12,
                'created_at' => '2023-12-31 19:48:16',
                'updated_at' => '2023-12-31 19:48:16',
            ),
            10 => 
            array (
                'id' => 2074,
                'id_cuenta' => 149,
                'id_tipo_cuenta' => 3,
                'created_at' => '2024-01-05 21:28:18',
                'updated_at' => '2024-01-05 21:28:18',
            ),
            11 => 
            array (
                'id' => 2076,
                'id_cuenta' => 150,
                'id_tipo_cuenta' => 3,
                'created_at' => '2024-01-06 18:50:43',
                'updated_at' => '2024-01-06 18:50:43',
            ),
            12 => 
            array (
                'id' => 2077,
                'id_cuenta' => 273,
                'id_tipo_cuenta' => 7,
                'created_at' => '2024-01-06 18:50:55',
                'updated_at' => '2024-01-06 18:50:55',
            ),
            13 => 
            array (
                'id' => 2078,
                'id_cuenta' => 272,
                'id_tipo_cuenta' => 7,
                'created_at' => '2024-01-06 18:50:59',
                'updated_at' => '2024-01-06 18:50:59',
            ),
            14 => 
            array (
                'id' => 2079,
                'id_cuenta' => 268,
                'id_tipo_cuenta' => 7,
                'created_at' => '2024-01-06 20:12:00',
                'updated_at' => '2024-01-06 20:12:00',
            ),
        ));
        
        
    }
}
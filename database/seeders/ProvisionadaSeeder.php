<?php

namespace Database\Seeders;

use Database\Seeders\Provisionada\CentroCostosSeeder;
use Database\Seeders\Provisionada\ComprobantesSeeder;
use Database\Seeders\Provisionada\PlanCuentasSeeder;
use Database\Seeders\Provisionada\TipoCuentasSeeder;
use Database\Seeders\Provisionada\TipoDocumentosSeeder;
use Database\Seeders\Provisionada\TipoImpuestosSeeder;
use Illuminate\Database\Seeder;
use DB;

class ProvisionadaSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET foreign_key_checks = 0');

        $this->call(CentroCostosSeeder::class);
        $this->call(ComprobantesSeeder::class);
        $this->call(PlanCuentasSeeder::class);
        $this->call(TipoCuentasSeeder::class);
        $this->call(TipoDocumentosSeeder::class);
        $this->call(TipoImpuestosSeeder::class);

        DB::statement('SET foreign_key_checks = 1');
    }
}

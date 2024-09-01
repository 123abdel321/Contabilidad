<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;
use Database\Seeders\Provisionada\BodegasSeeder;
use Database\Seeders\Provisionada\ImpuestosSeeder;
use Database\Seeders\Provisionada\TipoCuentasSeeder;
use Database\Seeders\Provisionada\ResolucionesSeeder;
use Database\Seeders\Provisionada\CentroCostosSeeder;
use Database\Seeders\Provisionada\TipoImpuestosSeeder;
use Database\Seeders\Provisionada\TipoDocumentosSeeder;
use Database\Seeders\Provisionada\TiposFormasPagosSeeder;
use Database\Seeders\PropiedadesHorizontales\PlanCuentasTableSeeder;
use Database\Seeders\PropiedadesHorizontales\ComprobantesTableSeeder;
use Database\Seeders\PropiedadesHorizontales\PlanCuentasTiposTableSeeder;


class PropiedadesHorizontalesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET foreign_key_checks = 0');

        $this->call(BodegasSeeder::class);
        $this->call(ImpuestosSeeder::class);
        $this->call(CentroCostosSeeder::class);
        $this->call(ResolucionesSeeder::class);
        $this->call(TipoCuentasSeeder::class);
        $this->call(TipoImpuestosSeeder::class);
        $this->call(TipoDocumentosSeeder::class);
        $this->call(TiposFormasPagosSeeder::class);
        $this->call(PlanCuentasTableSeeder::class);
        $this->call(ComprobantesTableSeeder::class);
        $this->call(PlanCuentasTiposTableSeeder::class);

        DB::statement('SET foreign_key_checks = 1');
    }
}

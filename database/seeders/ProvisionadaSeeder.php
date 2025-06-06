<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;
use Database\Seeders\Provisionada\NitsSeeder;
use Database\Seeders\Provisionada\BodegasSeeder;
use Database\Seeders\Provisionada\FamiliasSeeder;
use Database\Seeders\Provisionada\ImpuestosSeeder;
use Database\Seeders\Provisionada\FormasPagosSeeder;
use Database\Seeders\Provisionada\PlanCuentasSeeder;
use Database\Seeders\Provisionada\TipoCuentasSeeder;
use Database\Seeders\Provisionada\ResolucionesSeeder;
use Database\Seeders\Provisionada\CentroCostosSeeder;
use Database\Seeders\Provisionada\ComprobantesSeeder;
use Database\Seeders\Provisionada\TipoImpuestosSeeder;
use Database\Seeders\Provisionada\TipoDocumentosSeeder;
use Database\Seeders\Provisionada\AdministradorasSeeder;
use Database\Seeders\Provisionada\TiposFormasPagosSeeder;
use Database\Seeders\Provisionada\PlanCuentasTiposSeeder;
use Database\Seeders\Provisionada\ExogenaFormatosProvisionalSeeder;
use Database\Seeders\Provisionada\ExogenaFormatoColumnasProvisionalSeeder;
use Database\Seeders\Provisionada\ExogenaFormatoConceptosProvisionalSeeder;



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

        $this->call(NitsSeeder::class);
        $this->call(BodegasSeeder::class);
        $this->call(FamiliasSeeder::class);
        $this->call(ImpuestosSeeder::class);
        $this->call(CentroCostosSeeder::class);
        $this->call(ComprobantesSeeder::class);
        $this->call(ResolucionesSeeder::class);
        $this->call(FormasPagosSeeder::class);
        $this->call(PlanCuentasSeeder::class);
        $this->call(TipoCuentasSeeder::class);
        $this->call(TipoImpuestosSeeder::class);
        $this->call(TipoDocumentosSeeder::class);
        $this->call(AdministradorasSeeder::class);
        $this->call(TiposFormasPagosSeeder::class);
        $this->call(ExogenaFormatosProvisionalSeeder::class);
        $this->call(ExogenaFormatoColumnasProvisionalSeeder::class);
        $this->call(ExogenaFormatoConceptosProvisionalSeeder::class);
        //NOMINA
        $this->call(NomPeriodosSeeder::class);

        DB::statement('SET foreign_key_checks = 1');
    }
}

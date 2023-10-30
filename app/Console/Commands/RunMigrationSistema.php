<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Empresas\BaseDatosProvisionada;

class RunMigrationSistema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrations:sistema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dbProvisionadas = BaseDatosProvisionada::get();

        try {

            if ($dbProvisionadas->count()) {
                foreach ($dbProvisionadas as $database) {
    
                    setDBInConnection('sam', $database->hash);
    
                    Artisan::call('migrate', [
                        '--force' => true,
                        '--path' => 'database/migrations/sistema',
                        '--database' => 'sam'
                    ]);
    
                    info($database->hash . ' migrando...');
                }
            }
    
            info('Base de datos migrada: ' . $database->hash);

        } catch (Exception $exception) {
			Log::error('Error al generar base de datos provisionada', ['message' => $exception->getMessage()]);
		}
    }
}

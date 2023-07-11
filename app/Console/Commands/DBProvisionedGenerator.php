<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessProvisionedDatabase;
use App\Models\Empresas\BaseDatosProvisionada;

class DBProvisionedGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:provisioned
                            {--quantity= : La cantidad de bases de datos provisionadas a crear}
                            {--now : Si la creación de bases de datos se debe ejecutar inmediatamente o si se deben agregar a una cola}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
	 * Create a new command instance.
	 *
	 * @return void
	 */
    public function __construct()
	{
		parent::__construct();
	}

    /**
     * Execute the console command.
     * 
     * @return mixed
     */
    public function handle()
    {
        $dbProvisionadas = BaseDatosProvisionada::available()
			->orWhere('estado', 0)
			->count();
        $qty = config('db-provisioned.quantity');

        if ($this->option('quantity') || $this->option('now')) {
            $qty = $this->option('quantity') ?? 1;
        } else {
            $qty = $qty - $dbProvisionadas;
        }

        $this->info('cantidad a crear: ' . $qty);
        $this->info('cantidad configurada: ' . config('db-provisioned.quantity'));
		$this->info('db provisionadas existentes: ' . $dbProvisionadas);

        if (!$this->shouldCreateDB($dbProvisionadas) && !$this->option('now') && !$this->option('quantity')) {
			$this->info('La cantidad de de bases de datos provisionadas ya es: ' . $dbProvisionadas);
			exit(1);
		}

        if (!is_numeric($qty) || $qty < 0) {
			$this->error('La cantidad ingresada no es válida');
			exit(2);
		}

        if ($qty > config('db-provisioned.max_quantity')) {
			$this->error('La cantidad máxima es ' . config('db-provisioned.max_quantity'));
			exit(3);
		}

        $newDBs = "";

		if ($this->option('now')) {
			$this->info('inicio: ' . date('Y-m-d H:i:s'));
			$this->info('Generando bases de datos provisionadas...');
		}

        for ($i = 0; $i < $qty; $i++) {
			if ($this->option('now')) {
				ProcessProvisionedDatabase::dispatchSync();
			} else {
				ProcessProvisionedDatabase::dispatch();
			}
		}

        if ($this->option('now')) {
			// $this->info('bases de datos generadas...');

            // $this->info($newDBs);
			// foreach ($newDBs as $idx => $dbName) {
			// }

			$this->info('fin: ' . date('Y-m-d H:i:s'));
		}
    }

    private function shouldCreateDB($dbProvisionadas)
	{
		return $dbProvisionadas < config('db-provisioned.quantity');
	}
}

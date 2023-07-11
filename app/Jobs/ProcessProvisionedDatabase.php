<?php

namespace App\Jobs;

use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Database\Seeders\ProvisionadaSeeder;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Empresas\BaseDatosProvisionada;

class ProcessProvisionedDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
	public $dbName = '';
	public $connectionName = '';
	public $idEmpresa;

    /**
     * Create a new job instance.
	 * 
	 * @return void
     */
    public function __construct($idEmpresa = null)
    {
        $this->idEmpresa = $idEmpresa;
		$this->connectionName = 'provisionada';
    }

    /**
     * Execute the job.
	 * 
	 * @return string
     */
    public function handle()
    {
		$this->dbName = $this->generateUniqueHash();

        try {
			$countDbPending = BaseDatosProvisionada::where('estado', 0)->count();

			if ($countDbPending > config('db-provisioned.quantity', 10)) {
				throw new Exception("Se ha excedido el límite de bases de datos provisionadas en proceso. Bases de datos en proces: $countDbPending");
			}

			createDatabase($this->dbName);

			$dbProvisionada = (new BaseDatosProvisionada())->setConnection('clientes')->create([
				'hash' => $this->dbName,
				'estado' => 0
			]);

			if (!dbExists($this->dbName)) {
				Log::error("La base de datos {$this->dbName} ya existe");

				return;
			}

			if (!config('database.connections.' . $this->connectionName)) {
				copyDBConnection('clientes', $this->connectionName);
			}

			setDBInConnection($this->connectionName, $this->dbName);

			Artisan::call('migrate', [
				'--force' => true,
				'--path' => 'database/migrations/sistema',
				'--database' => $this->connectionName
			]);

			Artisan::call('db:seed', [
				'--force' => true,
				'--database' => $this->connectionName,
				'--class' => ProvisionadaSeeder::class
			]);

			$dbProvisionada->estado = 1;
			$dbProvisionada->save();

			info('Base de datos generada: ' . $this->dbName);

			if ($this->idEmpresa) {
				$this->instalarEmpresa($this->idEmpresa);
			}
			
		} catch (Exception $exception) {
			Log::error('Error al generar base de datos provisionada', ['message' => $exception->getMessage()]);

			$this->dropDb($this->dbName);
		}
    }

	private function instalarEmpresa($idEmpresa)
	{
		$empresa = Empresa::find($idEmpresa);

		if ($empresa->token_db) return;

		$dbProvisionada = BaseDatosProvisionada::available()->first();
		$dbProvisionada->ocupar();

		$empresa->token_db = $dbProvisionada->hash;
		$empresa->estado = 1;
		$empresa->save();

		copyDBConnection($empresa->servidor ?: 'sam', 'sam');
		setDBInConnection('sam', $empresa->token_db);
	}

    private function dropDb($schemaName)
	{
		if (config('database.connections.' . $this->connectionName)) {
			DB::connection($this->connectionName)->statement("DROP DATABASE IF EXISTS $schemaName");
		}
	}

	private function generateUniqueHash()
	{
		$hash = '';
		$prefix = config('db-provisioned.prefix', 'max');
		$trys = 5;

		for ($i = 0; $i < $trys; $i++) {
			$hash = $prefix . '_' . $this->generateHash();
			$hash = strlen($hash) > 32 ? substr($hash, 0, 32) : $hash;

			if (!dbExists($hash)) break;
		}

		return $hash;
	}

	private function generateHash()
	{
		return hash('md5', uniqid('max_', true));
	}

	public function failed($exception)
	{
		Log::error('Error al generar base de datos provisionada desde failed', ['message' => $exception->getMessage()]);

		$this->dropDb($this->dbName);
	}
}

<?php

namespace App\Jobs;

use Illuminate\Http\File;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
//MODEL
use App\Models\Empresas\BackupEmpresa;


class BackupDatabaseJob
{
    use Dispatchable, SerializesModels;

    protected $empresa;
    protected $maxBackups = 10;

    public function __construct($empresas)
    {
        $this->empresa = $empresas;
    }

    public function handle(): void
    {
        copyDBConnection('sam', $this->empresa->token_db);
        setDBInConnection('sam', $this->empresa->token_db);

        $tables = DB::connection('sam')->select('SHOW TABLES');
        $tables = array_map(fn($table) => reset($table), $tables);

        // Nombre del archivo con fecha y hora
        $filename = "{$this->empresa->token_db}_" . date('Y_m_d_H_i_s') . ".sql.gz";
        $filePath = storage_path("app/temp/{$filename}");

        // Configuración de la base de datos
        $dbConfig = config("database.connections.sam");
        $command = "mysqldump --host={$dbConfig['host']} --user={$dbConfig['username']} --password={$dbConfig['password']} {$this->empresa->token_db} | gzip > {$filePath}";

        exec($command, $output, $resultCode);

        if ($resultCode !== 0) {
            \Log::error("Error al generar el backup para {$this->empresa->token_db}");
            return;
        }
        
        // Subir a Digital Ocean Spaces
        $remotePath = "backups-portafolioerp/{$filename}";
        Storage::disk('do_spaces')->putFileAs("backups-portafolioerp", new File($filePath), $filename, 'public');

        // Obtener URL pública
        $url = Storage::disk('do_spaces')->url($remotePath);

        // Registrar en la base de datos
        $this->registerBackup($filename, $url);

        // Limpiar backups antiguos si es necesario
        $this->cleanOldBackups();

        // Eliminar archivo temporal
        unlink($filePath);

        \Log::info("Backup completado para {$this->empresa->token_db}");
    }

    protected function registerBackup($filename, $url)
    {
        BackupEmpresa::create([
            'id_empresa' => $this->empresa->id,
            'url_file' => $url,
            'file_name' => $filename
        ]);
    }

    protected function cleanOldBackups()
    {
        $backups = BackupEmpresa::where('id_empresa', $this->empresa->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        if ($backups->count() > $this->maxBackups) {
            $oldestBackups = $backups->slice($this->maxBackups);
            
            foreach ($oldestBackups as $backup) {
                try {
                    // Eliminar del almacenamiento
                    $path = str_replace(Storage::disk('do_spaces')->url(''), '', $backup->url_file);
                    Storage::disk('do_spaces')->delete($path);
                    
                    // Eliminar registro
                    $backup->delete();
                } catch (\Exception $e) {
                    \Log::error("Error al eliminar backup antiguo: " . $e->getMessage());
                }
            }
        }
    }
}

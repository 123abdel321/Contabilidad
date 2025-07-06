<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File as HttpFile;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
//MODEL
use App\Models\Empresas\BackupEmpresa;


class BackupDatabaseJob implements ShouldQueue

{
    use Dispatchable, Queueable, SerializesModels;

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

        // Asegurar que el directorio temporal exista
        $tempDir = storage_path('app/temp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // Nombre del archivo con fecha y hora
        $filename = "{$this->empresa->token_db}_" . date('Y_m_d_H_i_s') . ".sql.gz";
        $filePath = "{$tempDir}/{$filename}";

        // Ejecutar mysqldump con manejo de credenciales más seguro
        $dbConfig = config("database.connections.sam");

        // Usar variables de entorno para evitar el warning de password en command line
        putenv("MYSQL_PWD={$dbConfig['password']}");
        $command = "mysqldump --host={$dbConfig['host']} --user={$dbConfig['username']} {$this->empresa->token_db} | gzip > {$filePath}";

        exec($command, $output, $resultCode);
        putenv("MYSQL_PWD");

        if ($resultCode !== 0) {
            \Log::error("Error al generar el backup para {$this->empresa->token_db}", [
                'output' => $output,
                'resultCode' => $resultCode
            ]);
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

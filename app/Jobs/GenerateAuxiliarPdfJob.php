<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
//HELPERS
use App\Helpers\Printers\AuxiliarPdf;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Informes\InfAuxiliar;

class GenerateAuxiliarPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 600; // 5 minutos

    public function __construct(
        public int $id_auxiliar,
        public int $user_id,
        public string $has_empresa,
        public InfAuxiliar $auxiliar,
    ) {}

    public function handle()
    {
        try {

            $empresa = Empresa::where('token_db', $this->has_empresa)->first();

            copyDBConnection('sam', 'sam');
            setDBInConnection('sam', $empresa->token_db);
            
            if (!$empresa) {
                throw new \Exception('Empresa no encontrada');
            }

            $auxiliarPdf = (new AuxiliarPdf($empresa, $this->id_auxiliar))
                ->buildPdf()
                ->saveStorage();

            $urlFile = "porfaolioerpbucket.nyc3.digitaloceanspaces.com{$auxiliarPdf}";

            $this->auxiliar->exporta_pdf = 2;
            $this->auxiliar->archivo_pdf = $urlFile;
            $this->auxiliar->save();

            event(new PrivateMessageEvent("informe-auxiliar-{$this->has_empresa}_{$this->user_id}", [
                'tipo' => 'exito',
                'mensaje' => 'PDF de Auxiliar generado con éxito!',
                'titulo' => 'PDF generado',
                'url_file_pdf' => $urlFile,
                'autoclose' => false
            ]));

        } catch (\Exception $e) {
            // Notificar error
            event(new PrivateMessageEvent("informe-auxiliar-{$this->has_empresa}_{$this->user_id}", [
                'tipo' => 'error',
                'mensaje' => 'Error al generar PDF: ' . $e->getMessage(),
                'titulo' => 'Error en generación',
                'autoclose' => false
            ]));
            
            throw $e;
        } finally {
            // ✅ RESTAURAR CONFIGURACIÓN
            ini_restore('memory_limit');
            ini_restore('max_execution_time');
        }
    }

    public function failed(\Throwable $exception)
    {
        // Notificar fallo definitivo
        event(new PrivateMessageEvent('informe-auxiliar-'.$this->has_empresa.'_'.$this->user_id, [
            'tipo' => 'error',
            'mensaje' => 'Falló la generación del PDF después de varios intentos',
            'titulo' => 'Error crítico',
            'autoclose' => false
        ]));
    }
}
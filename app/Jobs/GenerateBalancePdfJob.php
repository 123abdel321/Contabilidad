<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
//HELPERS
use App\Helpers\Printers\BalancePdf;

//MODELS
use App\Models\Empresas\Empresa;

class GenerateBalancePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 300; // 5 minutos

    public function __construct(
        public int $id_balance,
        public int $user_id,
        public string $has_empresa
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

            $balancePdf = (new BalancePdf($empresa, $this->id_balance))
                ->buildPdf()
                ->saveStorage();

            event(new PrivateMessageEvent("informe-balance-{$this->has_empresa}_{$this->user_id}", [
                'tipo' => 'exito',
                'mensaje' => 'PDF de Balance generado con éxito!',
                'titulo' => 'PDF generado',
                'url_file_pdf' => "porfaolioerpbucket.nyc3.digitaloceanspaces.com/{$balancePdf}",
                'autoclose' => false
            ]));

        } catch (\Exception $e) {
            // Notificar error
            event(new PrivateMessageEvent("informe-balance-{$this->has_empresa}_{$this->user_id}", [
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
        event(new PrivateMessageEvent('informe-balance-'.$this->has_empresa.'_'.$this->user_id, [
            'tipo' => 'error',
            'mensaje' => 'Falló la generación del PDF después de varios intentos',
            'titulo' => 'Error crítico',
            'autoclose' => false
        ]));
    }
}
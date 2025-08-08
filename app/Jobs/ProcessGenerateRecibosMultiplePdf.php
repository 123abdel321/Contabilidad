<?php

namespace App\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Events\PrivateMessageEvent;
use App\Models\Sistema\ArchivosCache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\Printers\RecibosPdfMultiple;

class ProcessGenerateRecibosMultiplePdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 500;

    protected $empresa;
    protected $request;
    protected $usuario;

    /**
     * Create a new job instance.
     */
    public function __construct($empresa, $request, $usuario)
    {
        $this->onQueue('long-running');
        $this->empresa = $empresa;
        $this->request = $request;
        $this->usuario = $usuario;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $this->empresa->token_db);

        try {

            $recibosPdf = (new RecibosPdfMultiple($this->empresa, $this->request))
                ->buildPdf()
                ->saveStorage();

            event(new PrivateMessageEvent("informe-documentos-generales-{$this->empresa->token_db}_{$this->usuario}", [
                'tipo' => 'exito',
                'url_file' => "porfaolioerpbucket.nyc3.digitaloceanspaces.com{$recibosPdf}",
                'success' =>  true,
                'action' => 3
            ]));
        } catch (Exception $exception) {
			Log::error('Error al generar PDF de facturación', [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
                'user' => $this->idUser,
                'empresa' => $this->empresa->id,
            ]);
		}
    }

    public function failed($exception)
    {
        Log::error('Error al generar PDF de facturación', [
            'error' => $exception->getMessage(),
            'line' => $exception->getLine(),
            'file' => $exception->getFile(),
            'user' => $this->idUser,
            'empresa' => $this->empresa->id,
        ]);
    }
}

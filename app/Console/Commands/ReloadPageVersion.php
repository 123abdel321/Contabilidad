<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\PrivateMessageEvent;
//MODELS
use App\Models\Empresa\Versiones;

class ReloadPageVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reload-page-version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recargar pagina despues de actualizar versión';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // sleep(30);
        event(new PrivateMessageEvent('canal-general-abdel-castro', [
            'tipo' => 'reloadPage',
        ]));

    }
}

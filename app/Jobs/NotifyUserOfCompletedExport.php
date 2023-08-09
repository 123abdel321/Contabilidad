<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\PrivateMessageEvent;
//MODELS
use App\Models\User;
use App\Models\Empresas\Empresa;

class NotifyUserOfCompletedExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $id_empresa;
    public $url_excel;
    public $informe;
    public $canal;
	public $data;
    public $user;

    public function __construct($canal, $data, $informe, $id_empresa, $url_excel, User $user)
    {
        $this->id_empresa = $id_empresa;
        $this->url_excel = $url_excel;
        $this->informe = $informe;
        $this->canal = $canal;
		$this->data = $data;
		$this->user = $user;
    }

    public function handle(): void
    {
        $empresa = Empresa::find($this->id_empresa);

		copyDBConnection('sam', 'sam');
		setDBInConnection('sam', $empresa->token_db);

        $this->informe->exporta_excel = 2;
        $this->informe->archivo_excel = $this->url_excel;
        $this->informe->save();

        event(new PrivateMessageEvent($this->canal, $this->data));
    }
}

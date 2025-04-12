<?php

namespace App\Helpers\Printers;

use Illuminate\Support\Carbon;
//MODELS
use App\Models\Empresas\Empresa;

class AuxiliarPdf extends AbstractPrinterPdf
{
    public $detalles;
	public $empresa;

    public function __construct(Empresa $empresa, $detalle)
	{
		parent::__construct($empresa);

		copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

		$this->detalles = $detalle;
		$this->empresa = $empresa;
	}

    public function view()
	{
		return 'pdf.informes.auxiliar.auxiliar';
	}

    public function name()
	{
		return 'auxiliar_'.uniqid();
	}

    public function paper()
	{
		return 'landscape';
		return 'portrait';
		return '';
	}

	public function formatPaper()
	{
		if ($this->tipoEmpresion == 1) return [0, 0, 396, 612];
		return 'A4';
	}

    public function data()
    {
        return [
			'empresa' => $this->empresa,
			'auxiliares' => $this->detalles,
			'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
			'usuario' => request()->user()->username
		];
    }
}
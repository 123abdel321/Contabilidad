<?php

namespace App\Helpers\Printers;

use Illuminate\Support\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacParqueadero;
use App\Models\Sistema\PlanCuentas;

class ParqueaderoPdf extends AbstractPrinterPdf
{
    public $parqueadero;
	public $empresa;
	public $tipoEmpresion;

    public function __construct(Empresa $empresa, FacParqueadero $parqueadero)
	{
		parent::__construct($empresa);

		copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

		$this->parqueadero = $parqueadero;
		$this->empresa = $empresa;
	}

    public function view()
	{
		return 'pdf.facturacion.parqueadero-pos';
	}

    public function name()
	{
		return 'parqueadero_'.uniqid();
	}

    public function paper()
	{
		return '';
	}

	public function formatPaper()
	{
		if ($this->tipoEmpresion == 1) return [0, 0, 396, 612];
		return 'A4';
	}

    public function data()
    {
        $this->parqueadero->load([
            'bodega',
            'cliente',
            'producto',
        ]);

        return [
			'empresa' => $this->empresa,
			'factura' => $this->parqueadero,
			'cliente' => $this->parqueadero->cliente,
			'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
			'usuario' => request()->user()->username,
		];
    }
}
<?php

namespace App\Helpers\Printers;

use App\Helpers\Extracto;
use Illuminate\Support\Carbon;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacCompras;

class ComprasPdf extends AbstractPrinterPdf
{
    public $compra;
	public $empresa;

    public function __construct(Empresa $empresa, FacCompras $compra)
	{
		parent::__construct($empresa);

		copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

		$this->compra = $compra;
		$this->empresa = $empresa;
	}

    public function view()
	{
		return 'pdf.facturacion.compras';
	}

    public function name()
	{
		return 'compra_'.uniqid();
	}

    public function paper()
	{
		return '';
	}

    public function data()
    {
        $this->compra->load([
            'proveedor',
            'comprobante',
            'detalles'
        ]);

        return [
			'empresa' => $this->empresa,
			'proveedor' => $this->compra->proveedor,
			'factura' => $this->compra,
			'productos' => $this->compra->detalles,
			'observacion' => $this->compra->observacion,
			'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
			'total_factura' => number_format($this->compra->total_factura)
		];
    }
}
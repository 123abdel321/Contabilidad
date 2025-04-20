<?php

namespace App\Helpers\Printers;

use Illuminate\Support\Carbon;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacCompras;

class ComprasPdf extends AbstractPrinterPdf
{
    public $compra;
	public $empresa;
	public $tipoEmpresion;

    public function __construct(Empresa $empresa, FacCompras $compra)
	{
		parent::__construct($empresa);

		copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

		$this->compra = $compra;
		$this->empresa = $empresa;
		$this->tipoEmpresion = $this->compra->comprobante->tipo_impresion;
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
		if ($this->tipoEmpresion == 1) return 'landscape';
		if ($this->tipoEmpresion == 2) return 'portrait';

		return '';
	}

	public function formatPaper()
	{
		// if ($this->tipoEmpresion == 1) return [0, 0, 396, 612];
		return 'A4';
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
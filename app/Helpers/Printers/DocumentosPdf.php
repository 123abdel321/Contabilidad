<?php

namespace App\Helpers\Printers;

use Illuminate\Support\Carbon;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacDocumentos;

class DocumentosPdf extends AbstractPrinterPdf
{
	public $factura;
	public $empresa;

	public function __construct(Empresa $empresa, FacDocumentos $factura)
	{
		parent::__construct($empresa);

		$this->factura = $factura;
		$this->empresa = $empresa;
	}

    public function view()
	{
		return 'pdf.facturacion.documentos';
	}

    public function name()
	{
		return 'documento_'.uniqid();
	}

    public function paper()
	{
		return '';
	}

    public function data()
	{
		$this->factura->load([
			'comprobante',
			'documentos',
			'documentos.nit',
			'documentos.cuenta',
			'documentos.comprobante',
			'documentos.centro_costos',
		]);

		$observacion = '';

		foreach ($this->factura->documentos as $key => $documento) {
			if($documento->concepto) {
				$observacion = $documento->concepto;
				break;
			}
		}

		return [
			'empresa' => $this->empresa,
			'factura' => $this->factura,
			'documentos' => $this->factura->documentos,
			'observacion' => $observacion,
			'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s')
		];
	}

}

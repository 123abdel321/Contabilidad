<?php

namespace App\Helpers\Printers;

use App\Pdf\Documents\ReciboPdf as ReciboDocumentBuilder;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\ConRecibos;

class RecibosPdf extends AbstractMakePdf
{
    public $recibo;
	public $claveUrl;
    public $tipoEmpresion;

    public function __construct(Empresa $empresa, ConRecibos $recibo, string $claveUrl)
    {
        parent::__construct($empresa);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

        $this->recibo = $recibo;
        $this->empresa = $empresa;
		$this->claveUrl = $claveUrl;
        $this->tipoEmpresion = $this->recibo->comprobante->tipo_impresion;
    }

    public function view()
    {
        return 'pdf.plantilla';
    }

    public function name()
    {
        return 'recibo_' . uniqid();
    }

    public function paper()
    {
        if ($this->tipoEmpresion == 1) return 'landscape';
        if ($this->tipoEmpresion == 2) return 'portrait';
        return '';
    }

    public function formatPaper()
    {
        return 'A4';
    }

    public function data()
    {
        return ReciboDocumentBuilder::build($this->recibo, $this->empresa, $this->claveUrl);
    }

    public function buildPdf()
    {
        $this->view = $this->view();
        $this->name = $this->name();
        $this->data = $this->data();
        $this->paper = $this->paper();
        $this->formato = $this->formatPaper();

        $this->generatePdf();

        return $this;
    }
}
<?php

namespace App\Helpers\Printers;

use App\Pdf\Documents\CompraPdf as CompraDocumentBuilder;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacCompras;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;

class ComprasPdf extends AbstractMakePdf   // ⚠️ ver nota abajo
{
    public $compra;
    public $empresa;
    public $claveUrl;
    public $tipoEmpresion;

    use BegDocumentHelpersTrait;

    public function __construct(Empresa $empresa, FacCompras $compra, string $claveUrl)
    {
        parent::__construct($empresa);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

        $this->compra      = $compra;
        $this->empresa     = $empresa;
        $this->claveUrl    = $claveUrl;
        $this->tipoEmpresion = $this->compra->comprobante->tipo_impresion;
    }

    public function view()
    {
        return 'pdf.plantilla';   // igual que ventas
    }

    public function name()
    {
        return 'compra_' . uniqid();
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
        return CompraDocumentBuilder::build($this->compra, $this->empresa, $this->claveUrl);
    }

    public function buildPdf()
    {
        $this->view    = $this->view();
        $this->name    = $this->name();
        $this->data    = $this->data();
        $this->paper   = $this->paper();
        $this->formato = $this->formatPaper();

        $this->generatePdf();

        return $this;
    }
}
<?php

namespace App\Helpers\Printers;

use App\Pdf\Documents\PagoPdf as PagoDocumentBuilder;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\ConPagos;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;

class PagosPdf extends AbstractMakePdf
{
    public $pago;
    public $claveUrl;
    public $tipoEmpresion;

    use BegDocumentHelpersTrait;

    public function __construct(Empresa $empresa, ConPagos $pago, string $claveUrl)
    {
        parent::__construct($empresa);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

        $this->pago = $pago;
        $this->empresa = $empresa;
        $this->claveUrl = $claveUrl;
        $this->tipoEmpresion = $this->pago->comprobante->tipo_impresion;
    }

    public function view()
    {
        return 'pdf.plantilla';
    }

    public function name()
    {
        return 'pago_' . uniqid();
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
        return PagoDocumentBuilder::build($this->pago, $this->empresa, $this->claveUrl);
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
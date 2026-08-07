<?php

namespace App\Helpers\Printers;

use App\Pdf\Documents\GastoPdf as GastoDocumentBuilder;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\ConGastos;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;

class GastosPdf extends AbstractMakePdf
{
    public $gasto;
    public $claveUrl;
    public $tipoEmpresion;

    use BegDocumentHelpersTrait;

    public function __construct(Empresa $empresa, ConGastos $gasto, string $claveUrl)
    {
        parent::__construct($empresa);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

        $this->gasto = $gasto;
        $this->empresa = $empresa;
        $this->claveUrl = $claveUrl;
        $this->tipoEmpresion = $this->gasto->comprobante->tipo_impresion;
    }

    public function view()
    {
        return 'pdf.plantilla';
    }

    public function name()
    {
        return 'gasto_' . uniqid();
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
        return GastoDocumentBuilder::build($this->gasto, $this->empresa, $this->claveUrl);
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
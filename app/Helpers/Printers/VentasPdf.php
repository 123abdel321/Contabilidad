<?php

namespace App\Helpers\Printers;

use App\Pdf\Documents\VentaPdf as VentasDocumentBuilder;

use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacVentas;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;

class VentasPdf extends AbstractMakePdf
{
    public $venta;
    public $claveUrl;
    public $tipoEmpresion;

    use BegDocumentHelpersTrait;

    public function __construct(Empresa $empresa, FacVentas $venta, string $claveUrl)
    {
        parent::__construct($empresa);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

        $this->venta = $venta;
        $this->empresa = $empresa;
        $this->claveUrl = $claveUrl;
        $this->tipoEmpresion = $this->venta->resolucion->tipo_impresion;
    }

    public function view()
    {
        return 'pdf.plantilla';
    }

    public function name()
    {
        return 'venta_' . uniqid();
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
        return VentasDocumentBuilder::build($this->venta, $this->empresa, $this->claveUrl);
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
<?php

namespace App\Helpers\Printers;

use App\Pdf\Documents\DocumentoPdf as DocumentoDocumentBuilder;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacDocumentos;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;

class DocumentosPdf extends AbstractMakePdf
{
    public $factura;
    public $claveUrl;
    public $tipoEmpresion;
    public $viewOriginal;

    use BegDocumentHelpersTrait;

    public function __construct(Empresa $empresa, FacDocumentos $factura, string $claveUrl, $view = 'pdf.facturacion.documentos')
    {
        parent::__construct($empresa);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

        $this->factura = $factura;
        $this->empresa = $empresa;
        $this->claveUrl = $claveUrl;
        $this->tipoEmpresion = $this->factura->comprobante->tipo_impresion;
        $this->viewOriginal = $view;
    }

    public function view()
    {
        return 'pdf.plantilla';
    }

    public function name()
    {
        return 'documento_' . uniqid();
    }

    public function paper()
    {
        if ($this->viewOriginal == 'pdf.facturacion.documentos') {
            if ($this->tipoEmpresion == 1) return 'landscape';
            if ($this->tipoEmpresion == 2) return 'portrait';
        } else {
            return 'landscape';
        }
        return '';
    }

    public function formatPaper()
    {
        return 'A4';
    }

    public function data()
    {
        return DocumentoDocumentBuilder::build($this->factura, $this->empresa, $this->claveUrl);
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
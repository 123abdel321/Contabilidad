<?php

// app/Helpers/Printers/NominaPdf.php

namespace App\Helpers\Printers;

use App\Pdf\Documents\NominaPdf as NominaDocumentBuilder;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\Nomina\NomPeriodoPagos;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;
use App\Pdf\Mappers\NominaPdfMapper;

class NominaPdf extends AbstractMakePdf
{
    public $periodo;
    public $empresa;
    public $claveUrl;

    use BegDocumentHelpersTrait;

    public function __construct(Empresa $empresa, NomPeriodoPagos $periodo, ?string $claveUrl = null)
    {
        parent::__construct($empresa);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

        $this->periodo  = $periodo;
        $this->empresa  = $empresa;
        $this->claveUrl = $claveUrl;
    }

    // Misma plantilla que ventas: renderiza los blocks del Document
    public function view()        { return 'pdf.nomina'; }
    public function name()        { return 'nomina_' . uniqid(); }
    public function paper()       { return 'portrait'; }
    public function formatPaper() { return 'A4'; }

    public function data()
    {
        return NominaPdfMapper::map($this->periodo, $this->empresa, $this->claveUrl);
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
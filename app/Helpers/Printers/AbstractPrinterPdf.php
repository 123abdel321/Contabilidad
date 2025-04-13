<?php

namespace App\Helpers\Printers;
use Illuminate\Support\Facades\Storage;
//MODELS
use App\Models\Empresas\Empresa;

abstract class AbstractPrinterPdf
{
    public $empresa;
    public $paper;
    public $view;
    public $name;
    public $data;
    public $url;
    public $pdf;

    abstract public function view();
    abstract public function data();
    abstract public function name();
    abstract public function paper();
    abstract public function formatPaper();

    public function __construct(Empresa $empresa) {
        $this->empresa = $empresa;
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

    public function generatePdf()
    {
        $this->pdf = app('dompdf.wrapper');
        $this->pdf->loadView($this->view, $this->data);
        $this->pdf->setPaper('A4', $this->paper);
    }

    public function getPdf()
	{
		if (!$this->pdf) $this->imprimir();

		return $this->pdf->output();
	}

    public function showPdf()
    {
        return $this->pdf->stream($this->name);
    }

    public function getData()
    {
        return $this->data;
    }

    public function saveStorage()
    {
        $pdfBuilder = $this->pdf->output();
        Storage::disk('do_spaces')->put('pdf/'.$this->name, $pdfBuilder);
    }
    
}

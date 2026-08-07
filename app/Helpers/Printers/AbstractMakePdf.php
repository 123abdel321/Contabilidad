<?php

namespace App\Helpers\Printers;

use Illuminate\Support\Facades\Storage;
use PDF;
use App\Models\Empresas\Empresa;
use App\Pdf\Core\Document;

abstract class AbstractMakePdf
{
    public $empresa;
    public $paper;
    public $view;
    public $name;
    public $data;
    public $url;
    public $pdf_binary_content;
    public $formato;

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

    /**
     * Genera el PDF usando Dompdf o detectando si es un Document de bloques.
     */
    public function generatePdf()
    {
        // Si los datos son un Document (objeto), usamos la vista de bloques
        if ($this->data instanceof Document) {
            $viewData = ['document' => $this->data];
            $viewName = 'pdf.plantilla'; // vista unificada
        } else {
            // Modo tradicional: $this->data es un array
            $viewData = $this->data;
            $viewName = $this->view;
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView($viewName, $viewData);
        $pdf->setPaper($this->formato, $this->paper);
        
        $this->pdf_binary_content = $pdf->output();
    }

    public function showPdf()
    {
        if (empty($this->pdf_binary_content)) {
            throw new \Exception('El contenido binario del PDF está vacío. Asegúrate de llamar a buildPdf() primero.');
        }

        return response($this->pdf_binary_content)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $this->name . '.pdf"');
    }

    public function getData()
    {
        return $this->data;
    }

    public function saveStorage()
    {
        if (empty($this->pdf_binary_content)) {
            throw new \Exception('El contenido binario del PDF está vacío. Asegúrate de llamar a buildPdf() primero.');
        }

        $nameFile = "export/{$this->name}.pdf";
        Storage::disk('do_spaces')->put($nameFile, $this->pdf_binary_content, 'public');

        return $nameFile;
    }
}
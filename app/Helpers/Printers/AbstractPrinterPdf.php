<?php

namespace App\Helpers\Printers;
use Illuminate\Support\Facades\Storage;
use PDF; // Agregamos el Facade PDF para que esté disponible en las clases hijas
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
    
    // **NUEVO:** Almacenará el contenido binario del PDF (ya sea de Dompdf o Snappy)
    public $pdf_binary_content; 

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
     * Versión DOMPDF por defecto. Las clases hijas que necesiten 
     * rendimiento deben SOBREESCRIBIR este método usando Snappy.
     */
    public function generatePdf()
    {
        // Esta es la implementación LENTA (Dompdf)
        $pdf = app('dompdf.wrapper');
        $pdf->loadView($this->view, $this->data);
        $pdf->setPaper($this->formato, $this->paper);
        
        // Almacenamos el binario para que saveStorage pueda usarlo
        $this->pdf_binary_content = $pdf->output(); 
    }

    // Eliminamos getPdf y showPdf ya que ahora usamos el contenido binario

    public function getData()
    {
        return $this->data;
    }

    /**
     * Utilizamos el contenido binario almacenado para guardar el archivo.
     */
    public function saveStorage()
    {
        if (empty($this->pdf_binary_content)) {
            throw new \Exception('El contenido binario del PDF está vacío. Asegúrate de llamar a buildPdf() primero.');
        }

        $pdfBuilder = $this->pdf_binary_content;
        $nameFile = "export/{$this->name}.pdf"; // Cambiado a 'export/' para que coincida con tu Job

        // Asumimos que 'do_spaces' es tu disco configurado (DigitalOcean Spaces).
        Storage::disk('do_spaces')->put($nameFile, $pdfBuilder, 'public');

        return $nameFile;
    }
    
}

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
        try {
            // 1. Renderizar el HTML de la vista
            $html = view($this->view(), $this->data())->render();
            
            // 2. Limpiar HTML de posibles problemas (opcional)
            $html = $this->cleanHtmlForPdf($html);

            // 3. Usar Snappy para generar el PDF
            $pdf = PDF::loadHTML($html)
                ->setPaper($this->formatPaper(), $this->paper())
                ->setOption('enable-local-file-access', true)
                ->setOption('no-stop-slow-scripts', true)
                ->setOption('load-error-handling', 'ignore')
                ->setOption('load-media-error-handling', 'ignore')
                ->setOption('orientation', 'landscape')
                ->setOption('page-size', 'A4')
                ->setOption('encoding', 'UTF-8');

            // 4. Almacenar el contenido binario
            $this->pdf_binary_content = $pdf->output();
            
        } catch (\Exception $e) {
            // Fallback: intentar con menos opciones si falla
            $html = view($this->view(), $this->data())->render();
            
            $pdf = PDF::loadHTML($html)
                ->setPaper($this->formatPaper(), $this->paper())
                ->setOption('enable-local-file-access', true)
                ->setOption('load-error-handling', 'ignore');
                
            $this->pdf_binary_content = $pdf->output();
        }
    }

    private function cleanHtmlForPdf($html)
    {
        // Remover tags problemáticas que puedan causar el error "about:blank"
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<link\b[^>]*>/is', '', $html);
        $html = preg_replace('/<meta[^>]*>/is', '', $html);
        
        // Asegurar que las imágenes tengan URLs absolutas
        if (isset($this->empresa->logo)) {
            $html = str_replace(
                'src="' . $this->empresa->logo . '"',
                'src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/' . $this->empresa->logo . '"',
                $html
            );
        }
        
        return $html;
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

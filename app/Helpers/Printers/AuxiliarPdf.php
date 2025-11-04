<?php

namespace App\Helpers\Printers;

use Illuminate\Support\Carbon;
use PDF; // Asegúrate de importar el Facade de Snappy
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Informes\InfAuxiliar;
use App\Models\Informes\InfAuxiliarDetalle;

class AuxiliarPdf extends AbstractPrinterPdf
{
    public $empresa;
    public $id_auxiliar;

    public function __construct(Empresa $empresa, $id_auxiliar)
    {
        parent::__construct($empresa);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

        $this->empresa = $empresa;
        $this->id_auxiliar = $id_auxiliar;
    }

    /**
     * SOBREESCRITO: Implementación de alto rendimiento usando Snappy (wkhtmltopdf).
     * Esto reemplaza la versión de Dompdf de la clase AbstractPrinterPdf.
     */
    public function generatePdf()
    {
        try {
            // 1. Renderizar el HTML de la vista
            $html = view($this->view(), $this->data())->render();
            
            // 2. Limpiar HTML de posibles problemas
            $html = $this->cleanHtmlForPdf($html);

            // 3. Usar Snappy para generar el PDF con TODAS las opciones necesarias
            $pdf = PDF::loadHTML($html)
                ->setPaper($this->formatPaper(), $this->paper())
                ->setOption('enable-local-file-access', true)
                ->setOption('no-stop-slow-scripts', true)
                ->setOption('load-error-handling', 'ignore')
                ->setOption('load-media-error-handling', 'ignore')
                ->setOption('javascript-delay', 5000)
                ->setOption('images', true)
                ->setOption('disable-external-links', false)
                ->setOption('disable-internal-links', false)
                ->setOption('disable-javascript', false)
                ->setOption('dpi', 300)
                ->setOption('margin-top', 10)
                ->setOption('margin-right', 10)
                ->setOption('margin-bottom', 20)
                ->setOption('margin-left', 10)
                ->setOption('encoding', 'UTF-8')
                ->setOption('orientation', 'landscape')
                ->setOption('page-size', 'A4');

            // 4. Almacenar el contenido binario
            $this->pdf_binary_content = $pdf->output();
            
        } catch (\Exception $e) {
            // Fallback: intentar con menos opciones si falla
            \Log::error("Error generando PDF: " . $e->getMessage());
            
            $html = view($this->view(), $this->data())->render();
            $html = $this->cleanHtmlForPdf($html);
            
            $pdf = PDF::loadHTML($html)
                ->setPaper($this->formatPaper(), $this->paper())
                ->setOption('enable-local-file-access', true)
                ->setOption('load-error-handling', 'ignore')
                ->setOption('load-media-error-handling', 'ignore')
                ->setOption('orientation', 'landscape')
                ->setOption('page-size', 'A4');
                
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

    public function view()
    {
        return 'pdf.informes.auxiliar.auxiliar';
    }

    public function name()
    {
        return 'auxiliar_'.uniqid();
    }

    public function paper()
    {
        return 'landscape'; 
    }

    public function formatPaper()
    {
        return 'A4';
    }

    public function data()
    {
        return [
            'empresa' => $this->empresa,
            'auxiliares' => InfAuxiliarDetalle::where('id_auxiliar', $this->id_auxiliar)->get(),
            'auxiliar' => InfAuxiliar::whereId($this->id_auxiliar)->first(),
            'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
            'nombre_informe' => 'AUXILIAR PDF',
            'nombre_empresa' => $this->empresa->razon_social,
            'logo_empresa' => $this->empresa->logo,
            'usuario' => 'Portafolio ERP'
        ];
    }
}

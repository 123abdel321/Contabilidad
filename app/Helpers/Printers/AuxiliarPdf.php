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
        // 1. Renderizar el HTML de la vista
        $html = view($this->view(), $this->data())->render();
        
        // 2. Limpiar HTML más agresivamente
        $html = $this->cleanHtmlForPdf($html);

        // 3. Configurar manualmente el comando de wkhtmltopdf SIN --lowquality
        $pdf = PDF::loadHTML($html)
            ->setOption('enable-local-file-access', true)
            ->setOption('no-stop-slow-scripts', true)
            ->setOption('load-error-handling', 'ignore')
            ->setOption('load-media-error-handling', 'ignore')
            ->setOption('disable-javascript', true) // Deshabilitar JS
            ->setOption('no-background', false)
            ->setOption('images', true)
            ->setOption('disable-smart-shrinking', true)
            ->setOption('dpi', 96)
            ->setOption('margin-top', 10)
            ->setOption('margin-right', 10)
            ->setOption('margin-bottom', 20)
            ->setOption('margin-left', 10)
            ->setOption('orientation', 'landscape')
            ->setOption('page-size', 'A4')
            ->setOption('encoding', 'UTF-8');

        $this->pdf_binary_content = $pdf->output();
    }

    private function cleanHtmlForPdf($html)
    {
        // Remover completamente cualquier referencia a about:blank
        $html = str_replace('about:blank', '', $html);
        
        // Remover scripts, links y meta tags problemáticos
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<link\b[^>]*>/is', '', $html);
        $html = preg_replace('/<meta[^>]*>/is', '', $html);
        
        // Asegurar URLs absolutas para imágenes
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

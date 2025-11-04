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

        // 2. Usar Snappy para generar el PDF (MUCHO más rápido)
        // Usamos las propiedades de papel definidas en los métodos abstractos
        $pdf = PDF::loadHTML($html)
            ->setPaper($this->formatPaper(), $this->paper());

        // 3. Almacenar el contenido binario en la propiedad de la clase Abstracta
        // La clase AbstractPrinterPdf ahora tiene $this->pdf_binary_content y el método saveStorage
        $this->pdf_binary_content = $pdf->output();
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
        // Retorna solo 'landscape'
        // Eliminé los 'return' redundantes que estaban comentados/sin usar
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

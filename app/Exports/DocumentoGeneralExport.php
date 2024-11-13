<?php

namespace App\Exports;

use DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
//MODELS
use App\Models\Informes\InfDocumentosGeneralesDetalle;

class DocumentoGeneralExport implements FromView, WithColumnWidths, WithStyles, WithColumnFormatting, ShouldQueue
{
    use Exportable;

    protected $id_documento_general;

    public function __construct(int $id)
	{
		$this->id_documento_general = $id;
	}

    public function view(): View
	{
		return view('excel.documento.documento', [
			'documentos' => InfDocumentosGeneralesDetalle::where('id_documentos_generales', $this->id_documento_general)->get()
		]);
	}

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->getFont()->setBold(true);
    }

    public function headings(): array
    {
        return [
            'Cuenta', 
            'Nombre Cuenta', 
            'Documento', 
            'Nit', 
            'Comprobante', 
            'Consecutivo', 
            'Centro costos', 
            'Factura', 
            'Debito', 
            'Credito', 
            'Diferencia', 
            'Fecha', 
            'Concepto', 
            'Base', 
            'Porcentaje', 
            'Registros' 
        ];
    }

    public function columnFormats(): array
    {
        return [
			'I' => NumberFormat::FORMAT_CURRENCY_USD,
			'J' => NumberFormat::FORMAT_CURRENCY_USD,
			'K' => NumberFormat::FORMAT_CURRENCY_USD,
			'N' => NumberFormat::FORMAT_CURRENCY_USD,
        ];
	}

    public function columnWidths(): array
    {
        return [
            'A' => 18,
			'B' => 25,
			'C' => 25,
			'D' => 35,
			'E' => 20,
			'F' => 20,
			'G' => 20,
			'H' => 20,
			'I' => 18,
			'J' => 18,
			'K' => 18,
			'L' => 18,
			'M' => 18,
			'N' => 18,
			'O' => 18,
			'P' => 18
        ];
	}
}

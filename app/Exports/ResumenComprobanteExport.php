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
use App\Models\Informes\InfResumenComprobanteDetalle;

class ResumenComprobanteExport implements FromView, WithColumnWidths, WithStyles, WithColumnFormatting, ShouldQueue
{
    use Exportable;

    protected $id_resumen_comprobante;

    public function __construct(int $id)
	{
		$this->id_resumen_comprobante = $id;
	}

    public function view(): View
	{
		return view('excel.comprobante.comprobante', [
			'documentos' => InfResumenComprobanteDetalle::where('id_resumen_comprobante', $this->id_resumen_comprobante)->get()
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
            'Ubicacion',
            'Documento',
            'Fecha',
            'Debito', 
            'Credito', 
            'Diferencia',
            'Concepto',  
            'Registros' 
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_CURRENCY_USD,
			'I' => NumberFormat::FORMAT_CURRENCY_USD,
			'J' => NumberFormat::FORMAT_CURRENCY_USD,
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
            'H' => 18,
            'I' => 18,
            'J' => 18,
            'K' => 25,
            'L' => 18,
        ];
	}
}

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
use App\Models\Informes\InfCartera;
use App\Models\Informes\InfCarteraDetalle;

class CarteraExport implements FromView, WithColumnWidths, WithStyles, WithColumnFormatting, ShouldQueue
{
    use Exportable;

    protected $id_cartera;

    public function __construct(int $id)
	{
		$this->id_cartera = $id;
	}

    public function view(): View
	{
		return view('excel.cartera.cartera', [
            'cabeza' => InfCartera::where('id', $this->id_cartera)->first(),
			'documentos' => InfCarteraDetalle::whereIdCartera($this->id_cartera)->get()
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
            'Nit',
            'Saldo anterior',
            'Total factura',
            'Total abono',
            'Saldo final'
        ];
    }

    public function columnFormats(): array
    {
        return [
			'C' => NumberFormat::FORMAT_CURRENCY_USD,
			'D' => NumberFormat::FORMAT_CURRENCY_USD,
			'E' => NumberFormat::FORMAT_CURRENCY_USD,
			'F' => NumberFormat::FORMAT_CURRENCY_USD,
        ];
	}

    public function columnWidths(): array
    {
        return [
            'A' => 20,
			'B' => 35,
			'C' => 20,
			'D' => 20,
			'E' => 20,
			'F' => 20,
        ];
	}
}

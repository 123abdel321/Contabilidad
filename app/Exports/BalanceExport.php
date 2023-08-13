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
use App\Models\Informes\InfBalanceDetalle;

class BalanceExport implements FromView, WithColumnWidths, WithStyles, WithColumnFormatting, ShouldQueue
{
    use Exportable;

    protected $id_balance;

    public function __construct(int $id)
	{
		$this->id_balance = $id;
	}

    public function view(): View
	{
		return view('excel.balance.balance', [
			'balances' => InfBalanceDetalle::whereIdBalance($this->id_balance)->get()
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
            'Saldo anterior',
            'Debito',
            'Credito',
            'Saldo final'
        ];
    }

    public function columnFormats(): array
    {
        return [
			'B' => NumberFormat::FORMAT_CURRENCY_USD,
			'C' => NumberFormat::FORMAT_CURRENCY_USD,
			'D' => NumberFormat::FORMAT_CURRENCY_USD,
			'E' => NumberFormat::FORMAT_CURRENCY_USD,
        ];
	}

    public function columnWidths(): array
    {
        return [
            'A' => 35,
			'B' => 20,
			'C' => 20,
			'D' => 20,
			'E' => 20,
        ];
	}
}

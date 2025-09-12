<?php

namespace App\Exports;

use DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
//MODELS
use App\Models\Informes\InfBalance;
use App\Models\Informes\InfBalanceDetalle;

class BalanceExport implements FromView, WithColumnWidths, WithStyles, WithColumnFormatting, ShouldQueue
{
    use Exportable;

    protected $empresa;
    protected $id_balance;

    public function __construct(int $id, $empresa)
	{
        $this->id_balance = $id;
		$this->empresa = $empresa;
	}

    public function view(): View
	{
		return view('excel.balance.balance', [
			'balances' => InfBalanceDetalle::whereIdBalance($this->id_balance)->get(),
            'nivel' => InfBalance::find($this->id_balance)->nivel,
            'nombre_informe' => 'BALANCE',
            'nombre_empresa' => $this->empresa->razon_social,
            'logo_empresa' => $this->empresa->logo ?? 'https://app.portafolioerp.com/img/logo_contabilidad.png',
		]);
	}

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('5')->getFont()->setBold(true);

        // Estilo para el nombre empresa
        $sheet->mergeCells('B1:H1'); // Merges celdas para el título
        $sheet->getStyle('B1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 30
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Estilo para el título
        $sheet->mergeCells('B2:H2'); // Merges celdas para el título
        $sheet->getStyle('B2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 20,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Estilo para la fecha
        $sheet->mergeCells('B3:F3');
        $sheet->getStyle('B3')->applyFromArray([
            'font' => [
                'size' => 11,
                'italic' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        // Estilo para los encabezados (fila 4)
        $sheet->getStyle('A5:F5')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Aplica bordes finos a toda la tabla (desde la fila 5 en adelante)
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A5:F{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'Cuenta',
            'Nombre Cuenta',
            'Saldo anterior',
            'Debito',
            'Credito',
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

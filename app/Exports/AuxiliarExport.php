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
use App\Models\Informes\InfAuxiliar;
use App\Models\Informes\InfAuxiliarDetalle;

class AuxiliarExport implements FromView, WithColumnWidths, WithStyles, WithColumnFormatting, ShouldQueue
{
    use Exportable;

    protected $empresa;
    protected $id_auxiliar;

    public function __construct(int $id, $empresa)
	{
		$this->id_auxiliar = $id;
		$this->empresa = $empresa;
	}

    public function view(): View
	{
		return view('excel.auxiliar.auxiliar', [
			'auxiliares' => InfAuxiliarDetalle::whereIdAuxiliar($this->id_auxiliar)->get(),
            'auxiliar' => InfAuxiliar::whereId($this->id_auxiliar)->first(),
            'nombre_informe' => 'AUXILIAR',
            'nombre_empresa' => $this->empresa->razon_social,
            'logo_empresa' => $this->empresa->logo ?? 'https://app.portafolioerp.com/img/logo_contabilidad.png',
		]);
	}

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('7')->getFont()->setBold(true);

        // Estilo para el nombre empresa
        $sheet->mergeCells('B1:H1');
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
        $sheet->mergeCells('B2:H2');
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

        // Estilo para la fecha de generación
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

        // Estilo para los filtros
        $sheet->mergeCells('B4:F4');
        $sheet->getStyle('B4')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        $sheet->getStyle('B5:F5')->applyFromArray([
            'font' => [
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        $sheet->getStyle('B6:F6')->applyFromArray([
            'font' => [
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        // Estilo para los encabezados (fila 7)
        $sheet->getStyle('A7:L7')->applyFromArray([
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

        // Aplica bordes finos a toda la tabla (desde la fila 7 en adelante)
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A7:L{$highestRow}")->applyFromArray([
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
            'Nit',
            'Documento referencia',
            'Saldo anterior',
            'Debito',
            'Credito',
            'Saldo final'
        ];
    }

    public function columnFormats(): array
    {
        return [
			'E' => NumberFormat::FORMAT_CURRENCY_USD,
			'F' => NumberFormat::FORMAT_CURRENCY_USD,
			'G' => NumberFormat::FORMAT_CURRENCY_USD,
			'H' => NumberFormat::FORMAT_CURRENCY_USD,
        ];
	}

    public function columnWidths(): array
    {
        return [
            'A' => 35,
			'B' => 35,
			'C' => 25,
			'D' => 18,
			'E' => 20,
			'F' => 20,
			'G' => 20,
			'H' => 20,
			'I' => 25,
			'J' => 18,
			'K' => 18,
			'L' => 35,
        ];
	}
}

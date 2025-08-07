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
use App\Models\Informes\InfVentasAcumuladaDetalle;

class VentasAcumuladasExport implements FromView, WithColumnWidths, WithStyles, WithColumnFormatting, ShouldQueue
{
    use Exportable;

    protected $empresa;
    protected $id_venta_acumulada;

    public function __construct(int $id, $empresa)
	{
		$this->empresa = $empresa;
        $this->id_venta_acumulada = $id;
	}

    public function view(): View
	{
		return view('excel.ventas_acumuladas.ventas_acumuladas', [
			'documentos' => InfVentasAcumuladaDetalle::whereIdVentaAcumulada($this->id_venta_acumulada)->get(),
            'nombre_informe' => 'VENTAS ACUMULADAS',
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
        $sheet->mergeCells('B3:C3');
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
        $sheet->getStyle('A5:O5')->applyFromArray([
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
        $sheet->getStyle("A5:O{$highestRow}")->applyFromArray([
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
            "Factura",      //A
            "Cliente",      //B
            "Bodega",       //C
            "Fecha",        //D
            "Código",       //E
            "Producto",     //F
            "Cantidad",     //G
            "Costo",        //H
            "Subtotal",     //I
            "Iva %",        //J
            "Iva",          //K
            "Descuent %",   //L
            "Descuento",    //M
            "Total",        //N
            "Vendedor",     //O
        ];
    }

    public function columnFormats(): array
    {
        return [
			'G' => NumberFormat::FORMAT_CURRENCY_USD,
			'H' => NumberFormat::FORMAT_CURRENCY_USD,
			'I' => NumberFormat::FORMAT_CURRENCY_USD,
			'K' => NumberFormat::FORMAT_CURRENCY_USD,
			'M' => NumberFormat::FORMAT_CURRENCY_USD,
			'N' => NumberFormat::FORMAT_CURRENCY_USD,
        ];
	}

    public function columnWidths(): array
    {
        return [
            'A' => 35,
			'B' => 35,
			'C' => 20,
			'D' => 18,
			'E' => 18,
			'F' => 25,
			'G' => 18,
			'H' => 20,
			'I' => 20,
			'J' => 18,
			'K' => 20,
			'L' => 18,
			'M' => 20,
			'N' => 20,
			'O' => 35,
        ];
	}
}

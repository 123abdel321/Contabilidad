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
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
//SPREADSHEET
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Informes\InfImpuestos;
use App\Models\Informes\InfImpuestosDetalle;

class ImpuestoExport implements FromView, WithColumnWidths, WithStyles, WithColumnFormatting, ShouldQueue
{
    use Exportable;

    protected $nivel;
    protected $empresa;
    protected $id_impuesto;

    public function __construct(int $id, $empresa)
	{
		$this->id_impuesto = $id;
		$this->empresa = $empresa;

	}

    public function view(): View
	{
        $cabeza = InfImpuestos::find($this->id_impuesto);
        $documentos = InfImpuestosDetalle::where('id_impuestos', $this->id_impuesto)->get();
        $nit = $cabeza->id_nit ? Nits::find($cabeza->id_nit) : null;
        $this->nivel = $cabeza->nivel;

		return view('excel.impuesto.impuesto', [
            'nivel' => $cabeza->nivel,
            'tipo_informe' => $cabeza->tipo_informe,
			'documentos' => $documentos,
            'encabezado' => (object)[
                'nombre_informe' => 'Impuesto',
                'nombre_empresa' => $this->empresa->nombre_empresa,
                'logo_empresa' => $this->empresa->logo_empresa ?? 'https://app.portafolioerp.com/img/logo_contabilidad.png',
                'filtros' => [
                    'Fecha' => $cabeza->fecha_desde || $cabeza->fecha_hasta
                        ? ($cabeza->fecha_desde ?? 'No especificado') . ' al ' . ($cabeza->fecha_hasta ?? 'No especificado')
                        : null,
                    'Nit' => $nit ? "$nit->numero_documento - $nit->nombre_completo" : null
                ]
            ]
		]);
	}

    public function styles(Worksheet $sheet)
    {
        $ultimaLetra = $this->nivel == 3 ? 'I' : 'F';

        // Estilo para el nombre empresa (fila 1, columna B hasta L)
        $sheet->mergeCells("B1:{$ultimaLetra}1");
        $sheet->getStyle('B1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 18,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Estilo para el título (fila 2, columna B hasta L)
        $sheet->mergeCells("B2:{$ultimaLetra}2");
        $sheet->getStyle('B2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Estilo para la fecha de generación (fila 3, columna B hasta L)
        $sheet->mergeCells("B3:{$ultimaLetra}3");
        $sheet->getStyle('B3')->applyFromArray([
            'font' => [
                'size' => 11,
                'italic' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        // Estilo para los filtros (fila 4, columna B hasta L)
        $sheet->mergeCells("B4:{$ultimaLetra}4");
        $sheet->getStyle('B4')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        // El resto de tus estilos...
        $sheet->getStyle("B5:{$ultimaLetra}5")->applyFromArray([
            'font' => [
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        // Estilo para los encabezados (fila 5, todas las columnas A-L)
        $sheet->getStyle("A6:{$ultimaLetra}6")->applyFromArray([
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

        // Aplica bordes finos a toda la tabla
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A6:{$ultimaLetra}{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        // Ajustar altura de filas del encabezado
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(15);
        $sheet->getRowDimension(4)->setRowHeight(40);
        
        // Dar ancho a la columna del logo
        $sheet->getColumnDimension('A')->setWidth(15);
    }

    public function headings(): array
    {
        return [];
    }

    public function columnFormats(): array
    {
        return [
			'E' => NumberFormat::FORMAT_CURRENCY_USD,
			'F' => NumberFormat::FORMAT_CURRENCY_USD,
        ];
	}

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 25,
            'C' => 18,
            'D' => 20,
            'E' => 20,
            'F' => 25,
            'G' => 18,
            'H' => 18,
            'I' => 35,
        ];
	}
}

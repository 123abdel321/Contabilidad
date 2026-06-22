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
use App\Models\Informes\InfDocumentosGenerales;
use App\Models\Informes\InfDocumentosGeneralesDetalle;

class DocumentoGeneralExport implements FromView, WithColumnWidths, WithStyles, WithColumnFormatting, ShouldQueue
{
    use Exportable;

    protected $empresa;
    protected $id_documento_general;

    public function __construct(int $id, $empresa)
	{
		$this->empresa = $empresa;
		$this->id_documento_general = $id;
	}

    public function view(): View
	{
        $cabeza = InfDocumentosGenerales::find($this->id_documento_general);
        $documentos = InfDocumentosGeneralesDetalle::where('id_documentos_generales', $this->id_documento_general)->get();
        $nit = $cabeza->id_nit ? Nits::find($cabeza->id_nit) : null;

		return view('excel.documento.documento', [
			'documentos' => $documentos,
            'encabezado' => (object)[
                'nombre_informe' => 'Documentos generales',
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
        $ultimaLetra = 'Q';

        $sheet->getStyle('6')->getFont()->setBold(true);

        // Estilo para el nombre empresa
        $sheet->mergeCells("B1:{$ultimaLetra}1");
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
        $sheet->mergeCells("B2:{$ultimaLetra}2");
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

        // Estilo para los filtros
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

        $sheet->getStyle("B5:{$ultimaLetra}5")->applyFromArray([
            'font' => [
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        // Estilo para los encabezados (fila 6)
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

        // Aplica bordes finos a toda la tabla (desde la fila 7 en adelante)
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A7:{$ultimaLetra}{$highestRow}")->applyFromArray([
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
            'Documento', 
            'Nit',
            'Ubicacion',
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
            'I' => 20,
            'J' => 18,
            'K' => 18,
            'L' => 18,
            'M' => 18,
            'N' => 18,
            'O' => 18,
            'P' => 18,
            'Q' => 18
        ];
	}
}

<?php

namespace App\Exports;

use DB;
use Carbon\Carbon;
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
use App\Models\Informes\InfResultado;
use App\Models\Informes\InfResultadoDetalle;

class ResultadoExport implements FromView, WithColumnWidths, WithStyles, WithColumnFormatting, ShouldQueue
{
    use Exportable;

    protected $cabeza;
    protected $empresa;
    protected $id_resultado;
    protected $fecha_desde;
    protected $fecha_hasta;
    protected $mesesMostrar = [];

    public function __construct(int $id, $empresa)
    {
        $this->cabeza = InfResultado::find($id);
        $this->id_resultado = $id;
        $this->empresa = $empresa;
        $this->fecha_desde = $this->cabeza->fecha_desde;
        $this->fecha_hasta = $this->cabeza->fecha_hasta;
        $this->calcularMeses();
    }

    private function calcularMeses()
    {
        $start = Carbon::parse($this->fecha_desde);
        $end = Carbon::parse($this->fecha_hasta);
        $meses = [];
        while ($start->month <= $end->month) {
            $meses[] = strtolower($start->locale('es')->monthName);
            $start->addMonth();
        }
        $this->mesesMostrar = $meses;
    }

    public function view(): View
    {
        $documentos = InfResultadoDetalle::whereIdResultado($this->id_resultado)->get();
        $nit = $this->cabeza->id_nit ? Nits::find($this->cabeza->id_nit) : null;

        return view('excel.resultado.resultado', [
            'resultados' => $documentos,
            'nivel' => $this->cabeza->nivel,
            'mesesMostrar' => $this->mesesMostrar,
            'encabezado' => (object)[
                'nombre_informe' => 'Resultados',
                'nombre_empresa' => $this->empresa->nombre_empresa,
                'logo_empresa' => $this->empresa->logo_empresa ?? 'https://app.portafolioerp.com/img/logo_contabilidad.png',
                'filtros' => [
                    'Fecha' => $this->cabeza->fecha_desde || $this->cabeza->fecha_hasta
                        ? ($this->cabeza->fecha_desde ?? 'No especificado') . ' al ' . ($this->cabeza->fecha_hasta ?? 'No especificado')
                        : null,
                    'Nit' => $nit ? "$nit->numero_documento - $nit->nombre_completo" : null
                ]
            ]            
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $ultimaLetra = $this->getUltimaColumna();

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

    public function columnWidths(): array
    {
        $widths = [
            'A' => 20, // Cuenta
            'B' => 35, // Nombre Cuenta
            'C' => 20, // Saldo anterior
        ];

        $col = 'D';
        foreach ($this->mesesMostrar as $mes) {
            $widths[$col] = 18;
            $col++;
        }

        // Columnas de saldo final y presupuestos
        $widths[$col] = 20; // Saldo final
        $col++;
        $widths[$col] = 20; // Ppto anterior
        $col++;
        $widths[$col] = 20; // Ppto movimiento
        $col++;
        $widths[$col] = 20; // Ppto acumulado
        $col++;
        $widths[$col] = 20; // Ppto diferencia
        $col++;
        $widths[$col] = 20; // Ppto porcentaje
        $col++;
        $widths[$col] = 30; // Ppto porcentaje acumulado

        return $widths;
    }

    public function columnFormats(): array
    {
        $formats = [
            'C' => NumberFormat::FORMAT_CURRENCY_USD, // Saldo anterior
        ];

        $col = 'D';
        foreach ($this->mesesMostrar as $mes) {
            $formats[$col] = NumberFormat::FORMAT_CURRENCY_USD;
            $col++;
        }

        // Saldo final y presupuestos (excepto porcentajes)
        $formats[$col] = NumberFormat::FORMAT_CURRENCY_USD; // Saldo final
        $col++;
        $formats[$col] = NumberFormat::FORMAT_CURRENCY_USD; // Ppto anterior
        $col++;
        $formats[$col] = NumberFormat::FORMAT_CURRENCY_USD; // Ppto movimiento
        $col++;
        $formats[$col] = NumberFormat::FORMAT_CURRENCY_USD; // Ppto acumulado
        $col++;
        $formats[$col] = NumberFormat::FORMAT_CURRENCY_USD; // Ppto diferencia
        // Los porcentajes no tienen formato de moneda

        return $formats;
    }

    private function getUltimaColumna(): string
    {
        $numColumnas = 3 + count($this->mesesMostrar) + 7; // 3 fijas iniciales + meses + 7 fijas finales
        
        $letra = '';
        while ($numColumnas > 0) {
            $mod = ($numColumnas - 1) % 26;
            $letra = chr(65 + $mod) . $letra;
            $numColumnas = intval(($numColumnas - $mod) / 26);
        }
        return $letra;
    }
}
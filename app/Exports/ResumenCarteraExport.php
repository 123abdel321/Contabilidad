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
use App\Models\Informes\InfResumenCartera;
use App\Models\Informes\InfResumenCarteraDetalle;

class ResumenCarteraExport implements FromView, WithColumnWidths, WithStyles, WithColumnFormatting, ShouldQueue
{
    use Exportable;

    protected $cuentas;
    protected $empresa;
    protected $filtros;
    protected $columnWidths;
    protected $id_resumen_cartera;
    protected $tipo_informe;
    protected $columnasExcel = [ 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH' ];

    public function __construct(int $id, $empresa, $filtros, $tipo_informe = 'resumen_general')
	{
        $this->empresa = $empresa;
        $this->filtros = $filtros;
		$this->id_resumen_cartera = $id;
        $this->tipo_informe = $tipo_informe;

        $resumenCartera = InfResumenCartera::where('id', $id)->first();
        $cuentas = json_decode($resumenCartera->cuentas);

        $this->cuentas[] = 'DOCUMENTO';
        if ($tipo_informe == 'resumen_general') {
            $this->cuentas[] = 'NOMBRE';
            $this->cuentas[] = 'UBICACIÓN';
        }

        $this->columnWidths['A'] = 20;
        $this->columnWidths['B'] = 35;
        $this->columnWidths['C'] = 18;

        $lastColumn = null;
        foreach ($cuentas as $key => $cuenta) {
            $lastColumn = $key;
            $this->cuentas[] = $cuenta->nombre_cuenta;
            $this->columnWidths[$this->columnasExcel[$key]] = 25;
        }

        if ($this->tipo_informe != 'resumen_general') {
            $this->cuentas[] = 'TOTAL ABONO';
            $this->cuentas[] = 'FECHA MANUAL';
        }

        $this->cuentas[] = 'SALDO FINAL';
        if ($this->tipo_informe == 'resumen_general') {
            $this->cuentas[] = 'MORA';
        }

        $this->columnWidths[$this->columnasExcel[$lastColumn + 1]] = 20;
        $this->columnWidths[$this->columnasExcel[$lastColumn + 2]] = 15;
	}

    public function view(): View
	{
		return view('excel.resumen_cartera.resumen_cartera', [
            'resumen' => InfResumenCartera::whereId($this->id_resumen_cartera)->first(),
            'cuentas' => $this->cuentas,
            'tipo_informe' => $this->tipo_informe,
            'nombre_informe' => $this->tipo_informe == 'resumen_general' ? 'RESUMEN CARTERA GENERAL' : 'RESUMEN CARTERA INDIVIDUAL',
            'nombre_empresa' => $this->empresa->nombre_empresa,
            'logo_empresa' => $this->empresa->logo_empresa,
            'filtros' => $this->filtros,
            'detalles' => InfResumenCarteraDetalle::whereIdResumenCartera($this->id_resumen_cartera)->get()
		]);
	}

    public function styles(Worksheet $sheet)
    {
        $ultimaLetra = array_key_last($this->columnWidths);

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
        return $this->cuentas;
    }

    public function columnFormats(): array
    {
        return [];
	}

    public function columnWidths(): array
    {
        return $this->columnWidths;
	}
}

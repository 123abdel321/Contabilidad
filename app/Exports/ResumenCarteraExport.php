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
use App\Models\Informes\InfResumenCartera;
use App\Models\Informes\InfResumenCarteraDetalle;

class ResumenCarteraExport implements FromView, WithColumnWidths, WithStyles, WithColumnFormatting, ShouldQueue
{
    use Exportable;

    protected $cuentas;
    protected $columnWidths;
    protected $id_resumen_cartera;
    protected $columnasExcel = [ 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH' ];

    public function __construct(int $id)
	{
		$this->id_resumen_cartera = $id;

        $resumenCartera = InfResumenCartera::where('id', $id)->first();
        $cuentas = json_decode($resumenCartera->cuentas);

        $this->cuentas[] = 'DOCUMENTO';
        $this->cuentas[] = 'NOMBRE';
        $this->cuentas[] = 'UBICACIÓN';
        $this->columnWidths['A'] = 20;
        $this->columnWidths['B'] = 35;
        $this->columnWidths['C'] = 18;

        $lastColumn = null;
        foreach ($cuentas as $key => $cuenta) {
            $lastColumn = $key;
            $this->cuentas[] = $cuenta->nombre_cuenta;
            $this->columnWidths[$this->columnasExcel[$key]] = 25;
        }

        $this->cuentas[] = 'SALDO FINAL';
        $this->cuentas[] = 'MORA';
        $this->columnWidths[$this->columnasExcel[$lastColumn + 1]] = 20;
        $this->columnWidths[$this->columnasExcel[$lastColumn + 2]] = 15;
	}

    public function view(): View
	{
		return view('excel.resumen_cartera.resumen_cartera', [
            'cuentas' => $this->cuentas,
			'detalles' => InfResumenCarteraDetalle::whereIdResumenCartera($this->id_resumen_cartera)->get()
		]);
	}

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->getFont()->setBold(true);
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

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
use App\Models\Informes\InfVentasAcumuladaDetalle;

class VentasAcumuladasExport implements FromView, WithColumnWidths, WithStyles, WithColumnFormatting, ShouldQueue
{
    use Exportable;

    protected $id_venta_acumulada;

    public function __construct(int $id)
	{
		$this->id_venta_acumulada = $id;
	}

    public function view(): View
	{
		return view('excel.ventas_acumuladas.ventas_acumuladas', [
			'documentos' => InfVentasAcumuladaDetalle::whereIdVentaAcumulada($this->id_venta_acumulada)->get()
		]);
	}

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->getFont()->setBold(true);
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

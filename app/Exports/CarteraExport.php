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
use App\Models\Informes\InfCartera;
use App\Models\Informes\InfCarteraDetalle;

class CarteraExport implements FromView, WithColumnWidths, WithStyles, WithColumnFormatting, ShouldQueue
{
    use Exportable;

    protected $id_cartera;
    protected $empresa;
    protected $cabeza;

    public function __construct(int $id, $empresa)
	{
		$this->id_cartera = $id;
        $this->empresa = $empresa;
		$this->cabeza = InfCartera::where('id', $this->id_cartera)->first();

	}

    public function view(): View
	{
        $platilla = 'excel.cartera.cartera';
        if ($this->cabeza->tipo_informe == 'por_edades') {
            $platilla = 'excel.cartera.edades';
        }
        
		return view($platilla, [
            'cabeza' => $this->cabeza,
			'documentos' => InfCarteraDetalle::whereIdCartera($this->id_cartera)->get(),
            'nombre_informe' => 'CARTERA',
            'nombre_empresa' => $this->empresa->razon_social,
            'logo_empresa' => $this->empresa->logo ?? 'https://app.portafolioerp.com/img/logo_contabilidad.png',
		]);
	}

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->getFont()->setBold(true);
    }

    public function headings(): array
    {
        $headings = [
            'Cuenta',
            'Nit',
            'Saldo anterior',
            'Total factura',
            'Total abono',
            'Saldo final'
        ];
        if ($this->cabeza->tipo_informe == 'por_edades') {
            $headings = [
                'Documento',
                'Nombre',
                'Ubicación',
                'Detalle',
                'De 0 a 30',
                'De 30 a 60',
                'De 60 a 90',
                'Más de 90',
                'Saldo fina'
            ];
        }
        return $headings;
    }

    public function columnFormats(): array
    {
        $columnFormats = [
			'E' => NumberFormat::FORMAT_CURRENCY_USD,
			'F' => NumberFormat::FORMAT_CURRENCY_USD,
			'G' => NumberFormat::FORMAT_CURRENCY_USD,
			'H' => NumberFormat::FORMAT_CURRENCY_USD,
        ];
        if ($this->cabeza->tipo_informe == 'por_edades') {
            $columnFormats = [
                'E' => NumberFormat::FORMAT_CURRENCY_USD,
                'F' => NumberFormat::FORMAT_CURRENCY_USD,
                'G' => NumberFormat::FORMAT_CURRENCY_USD,
                'H' => NumberFormat::FORMAT_CURRENCY_USD,
                'I' => NumberFormat::FORMAT_CURRENCY_USD,
            ];
        }
        return $columnFormats;
	}

    public function columnWidths(): array
    {
        return [
            'A' => 20,
			'B' => 35,
			'C' => 17,
			'D' => 17,
			'E' => 20,
			'F' => 20,
			'G' => 20,
			'H' => 20,
			'I' => 20,
        ];
	}
}

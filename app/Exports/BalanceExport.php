<?php

namespace App\Exports;

use DB;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BalanceExport implements FromCollection, WithStyles, WithHeadings, WithColumnWidths
{
    use Exportable;

    protected $fecha_desde;
    protected $fecha_hasta;
    protected $documento_referencia;

    public function __construct($request)
    {
        $this->fecha_desde = $request->get('year_desde'). '-'.$request->get('fecha_hasta'). '-01';
        $this->fecha_hasta = $request->get('year_desde'). '-12-31';
        $this->documento_referencia = $request->has('documento_referencia') ? $request->get('documento_referencia') : NULL;
    }

    public function collection()
    {
        $wheres = '';
        $fecha_desde = $this->fecha_desde;
        $fecha_hasta = $this->fecha_hasta;

        if($this->documento_referencia && $this->documento_referencia){
            $wheres.= ' AND DG.documento_referencia = '.$this->documento_referencia;
        }

        $query = "SELECT
                CONCAT(numero_documento, ' - ', nombre_nit) AS nit,
                documento_referencia,
                SUM(saldo_anterior) AS saldo_anterior,
                SUM(debito) AS debito,
                SUM(credito) AS credito,
                SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final
            FROM ((
                SELECT
                    N.id AS id_nit,
                    N.numero_documento,
                    CONCAT(N.otros_nombres, ' ', N.primer_apellido) AS nombre_nit,
                    N.razon_social,
                    PC.id AS id_cuenta,
                    PC.cuenta,
                    PC.nombre AS nombre_cuenta,
                    DG.documento_referencia,
                    SUM(debito) - SUM(credito) AS saldo_anterior,
                    0 AS debito,
                    0 AS credito,
                    0 AS saldo_final
                FROM
                    documentos_generals DG
                    
                LEFT JOIN nits N ON DG.id_nit = N.id
                LEFT JOIN plan_cuentas PC ON DG.id_cuenta = PC.id
                    
                WHERE DG.fecha_manual < '$fecha_desde'
                    $wheres
                    
                GROUP BY DG.documento_referencia
                )
                    UNION
                (
                    SELECT
                        N.id AS id_nit,
                        N.numero_documento,
                        CONCAT(N.otros_nombres, ' ', N.primer_apellido) AS nombre_nit,
                        N.razon_social,
                        PC.id AS id_cuenta,
                        PC.cuenta,
                        PC.nombre AS nombre_cuenta,
                        DG.documento_referencia,
                        0 AS saldo_anterior,
                        SUM(DG.debito) AS debito,
                        SUM(DG.credito) AS credito,
                        SUM(DG.debito) - SUM(DG.credito) AS saldo_final
                    FROM
                        documentos_generals DG
                        
                    LEFT JOIN nits N ON DG.id_nit = N.id
                    LEFT JOIN plan_cuentas PC ON DG.id_cuenta = PC.id
                        
                    WHERE DG.fecha_manual >= '$fecha_desde'
                        AND DG.fecha_manual <= '$fecha_hasta'
                        $wheres
                        
                    GROUP BY DG.documento_referencia
                )) AS auxiliar
                GROUP BY documento_referencia
                ORDER BY cuenta, id_nit, documento_referencia";
                
        return collect(DB::select($query));
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->getFont()->setBold(true);
    }

    public function headings(): array
    {
        return [
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
			'C' => NumberFormat::FORMAT_CURRENCY_USD,
			'D' => NumberFormat::FORMAT_CURRENCY_USD,
			'E' => NumberFormat::FORMAT_CURRENCY_USD,
			'F' => NumberFormat::FORMAT_CURRENCY_USD,
        ];
	}

    public function columnWidths(): array
    {
        return [
            'A' => 35,
			'B' => 15,
			'C' => 20,
			'D' => 20,
			'E' => 20,
			'F' => 20,
        ];
	}
}

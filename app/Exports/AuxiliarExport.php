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

class AuxiliarExport implements FromCollection, WithStyles, WithHeadings, WithColumnWidths
{
    use Exportable;

    protected $fecha_desde;
    protected $fecha_hasta;
    protected $id_cuenta;
    protected $id_nit;

    public function __construct($request)
    {
        $this->fecha_desde = $request->has('fecha_desde') ? $request->get('fecha_desde') : NULL;
        $this->fecha_hasta = $request->has('fecha_hasta') ? $request->get('fecha_hasta') : NULL;
        $this->id_cuenta = $request->has('id_cuenta') ? $request->get('id_cuenta') : NULL;
        $this->id_nit = $request->has('id_nit') ? $request->get('id_nit') : NULL;
    }

    public function collection()
    {
        $wheres = '';
        $fecha_desde = $this->fecha_desde;
        $fecha_hasta = $this->fecha_hasta;

        if($this->id_cuenta){
            $wheres.= ' AND PC.cuenta = '.$this->id_cuenta;
        }

        if($this->id_nit){
            $wheres.= ' AND N.id = '.$this->id_nit;
        }

        $query = "SELECT
                CONCAT(cuenta, ' - ', nombre_cuenta) AS cuenta,
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
                    
                GROUP BY DG.id_cuenta, DG.id_nit, DG.documento_referencia
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
                        
                    GROUP BY DG.id_cuenta, DG.id_nit, DG.documento_referencia
                )) AS auxiliar
                GROUP BY id_cuenta, id_nit, documento_referencia
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
			'D' => NumberFormat::FORMAT_CURRENCY_USD,
			'E' => NumberFormat::FORMAT_CURRENCY_USD,
			'F' => NumberFormat::FORMAT_CURRENCY_USD,
			'G' => NumberFormat::FORMAT_CURRENCY_USD,
        ];
	}

    public function columnWidths(): array
    {
        return [
            'A' => 35,
			'B' => 35,
			'C' => 15,
			'D' => 20,
			'E' => 20,
			'F' => 20,
			'G' => 20,
        ];
	}
}

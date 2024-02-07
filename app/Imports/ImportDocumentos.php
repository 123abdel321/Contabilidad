<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
//MODELS
use App\Models\Sistema\DocumentosImport;

class ImportDocumentos implements ToCollection, WithHeadingRow, WithProgressBar
{
    use Importable;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if ($row['debito'] || $row['credito']) {
                DocumentosImport::create([
                    'documento_nit' => $row['documento_nit'],
                    'cuenta_contable' => $row['cuenta_contable'],
                    'codigo_cecos' => $row['codigo_cecos'],
                    'codigo_comprobante' => $row['codigo_comprobante'],
                    'consecutivo' => $row['consecutivo'],
                    'documento_referencia' => $row['documento_referencia'],
                    'fecha_manual' => $row['fecha_manual'],
                    'debito' => $row['debito'],
                    'credito' => $row['credito'],
                    'concepto' => $row['concepto'],
                ]);
            }
        }
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function customValidationAttributes()
    {
        return [
            '0' => 'documento_nit',
            '1' => 'cuenta_contable',
            '2' => 'codigo_cecos',
            '3' => 'codigo_comprobante',
            '4' => 'consecutivo',
            '5' => 'documento_referencia',
            '6' => 'fecha_manual',
            '7' => 'debito',
            '8' => 'credito',
            '9' => 'concepto',
        ];
    }

}

<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
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
                
                $fecha_manual = $this->parseFecha($row['fecha_manual']);
                
                DocumentosImport::create([
                    'documento_nit' => $row['documento_nit'],
                    'cuenta_contable' => $row['cuenta_contable'],
                    'codigo_cecos' => $row['codigo_cecos'],
                    'codigo_comprobante' => $row['codigo_comprobante'],
                    'consecutivo' => $row['consecutivo'],
                    'documento_referencia' => $row['documento_referencia'],
                    'fecha_manual' => $fecha_manual,
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

     protected function parseFecha($fecha, $hora = null)
    {
        $fechaObj = null;
        
        // Parsear la fecha
        if ($fecha && str_contains($fecha, '/')) {
            $fechaObj = Carbon::parse($fecha);
        } else if ($fecha && str_contains($fecha, '-')) {
            $fechaObj = Carbon::parse($fecha);
        } else if (is_numeric($fecha)) {
            $fechaObj = Carbon::instance(Date::excelToDateTimeObject($fecha));
        }
        
        if (!$fechaObj) {
            return null;
        }
        
        // Formatear la fecha base
        $fechaFormateada = $fechaObj->format('Y-m-d');
        
        // Si hay hora, agregarla
        if (isset($hora)) {
            try {
                if (is_numeric($hora)) {
                $horaObj = Carbon::instance(Date::excelToDateTimeObject($hora));
                
                } else {
                    // Intenta parsear la hora en diferentes formatos comunes
                    $horaObj = Carbon::createFromFormat('H:i:s', $hora) ?:
                            Carbon::createFromFormat('H:i', $hora) ?:
                            Carbon::parse($hora);
                }
                
                $horaFormateada = $horaObj->format('H:i:s');
                return $fechaFormateada . ' ' . $horaFormateada;
            } catch (\Exception $e) {
                return $fechaFormateada;
            }
        }
        return $fechaFormateada;
    }

}

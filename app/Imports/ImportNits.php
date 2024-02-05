<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
//MODELS
use App\Models\Sistema\NitsImport;

class ImportNits implements ToCollection, WithHeadingRow, WithProgressBar
{
    use Importable;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if ($row['numero_documento'] && $row['email']) {
                NitsImport::create([
                    'tipo_documento' => $row['tipo_documento'],
                    'numero_documento' => $row['numero_documento'],
                    'digito_verificacion' => $row['digito_verificacion'],
                    'primer_nombre' => $row['primer_nombre'],
                    'otros_nombres' => $row['otros_nombres'],
                    'primer_apellido' => $row['primer_apellido'],
                    'segundo_apellido' => $row['segundo_apellido'],
                    'razon_social' => $row['razon_social'],
                    'direccion' => $row['direccion'],
                    'email' => $row['email'],
                    'telefono_1' => $row['telefono_1'],
                    'plazo' => $row['plazo'],
                    'cupo' => $row['cupo'],
                    'observaciones' => $row['observaciones'],
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
            '0' => 'tipo_documento',
            '1' => 'numero_documento',
            '2' => 'digito_verificacion',
            '3' => 'primer_nombre',
            '4' => 'otros_nombres',
            '5' => 'primer_apellido',
            '6' => 'segundo_apellido',
            '7' => 'razon_social',
            '8' => 'direccion',
            '9' => 'email',
            '10' => 'telefono_1',
            '11' => 'plazo',
            '12' => 'cupo',
            '13' => 'observaciones',
        ];
    }

}

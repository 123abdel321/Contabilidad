<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
//MODELS
use App\Models\Sistema\FacProductos;

class ProductosPreciosImport implements ToCollection, WithValidation, WithHeadingRow, WithProgressBar
{
    use Importable;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            $producto = FacProductos::where('codigo', $row['codigo'])
                ->first();

            $porcentajeUtilidad = floatval($producto->porcentaje_utilidad) / 100;
            $producto->precio_inicial = $row['costo'];
            $producto->valor_utilidad = $row['costo'] * $porcentajeUtilidad;
            $producto->precio = $row['costo'] * ($porcentajeUtilidad + 1);
            $producto->save();
        }
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function customValidationAttributes()
    {
        return [
            '0' => 'codigo',
            '1' => 'costo'
        ];
    }

    public function rules(): array
    {
        return [
            '*.codigo' => 'required|string|min:1|exists:sam.fac_productos,codigo',
            '*.costo' => 'required|min:0',
        ];
    }

}

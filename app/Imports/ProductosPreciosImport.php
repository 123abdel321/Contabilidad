<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
//MODELS
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacProductosPreciosImport;

class ProductosPreciosImport implements ToCollection, WithHeadingRow, WithProgressBar
{
    use Importable;

    public function collection(Collection $rows)
    {
        $columna = 2;
        foreach ($rows as $row) {

            $estado = 0;
            $observacion = 'Producto no encontrado';

            $producto = FacProductos::where('codigo', $row['codigo'])
                ->first();

            if (!$row['codigo']) {
                $observacion = 'Producto sin costo';
                FacProductosPreciosImport::create([
                    'id_producto' => '',
                    'row' => $columna,
                    'codigo' => $row['codigo'],
                    'nombre' => '',
                    'precio' => '',
                    'precio_inicial' => $row['costo'],
                    'observacion' => $observacion,
                    'estado' => $estado,
                ]);
            } else if ($producto) {

                $porcentajeUtilidad = floatval($producto->porcentaje_utilidad) / 100;

                if ($row['costo'] == $producto->precio_inicial) {
                    $estado = 1;
                    $observacion = 'Precio sin actualizar';
                } else {
                    $estado = 2;
                    $observacion = 'Producto con cambios';
                }

                FacProductosPreciosImport::create([
                    'id_producto' => $producto->id,
                    'row' => $columna,
                    'codigo' => $producto->codigo,
                    'nombre' => $producto->nombre,
                    'precio' => $row['costo'] * ($porcentajeUtilidad + 1),
                    'precio_inicial' => $row['costo'],
                    'observacion' => $observacion,
                    'estado' => $estado,
                ]);
            } else {
                FacProductosPreciosImport::create([
                    'id_producto' => '',
                    'row' => $columna,
                    'codigo' => $row['codigo'],
                    'nombre' => '',
                    'precio' => '',
                    'precio_inicial' => $row['costo'],
                    'observacion' => $observacion,
                    'estado' => $estado,
                ]);
            }
            $columna++;
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

}

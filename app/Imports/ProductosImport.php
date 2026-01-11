<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\PrivateMessageEvent;
use Illuminate\Support\Facades\Validator;
//EXCEL
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
//QUEUE
use Illuminate\Contracts\Queue\ShouldQueue;
// MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\FacFamilias;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacProductosImport;

class ProductosImport implements
    ToCollection,
    WithHeadingRow,
    WithChunkReading,
    WithBatchInserts,
    ShouldQueue,
    WithEvents
{
    use Importable;

    protected $url_notification = null;
    protected $empresa = null;
    protected $connectionName = 'sam';

    public function __construct(string $url_notification, $empresa_id)
    {
        $this->url_notification = $url_notification;
        $this->empresa = Empresa::find($empresa_id);
        
        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $this->empresa->token_db);
        $this->connectionName = 'sam';
    }
    
    private $processedRows = 0;
    private $errors = [];

    public function collection(Collection $rows)
    {
        $batchData = [];
        
        foreach ($rows as $index => $row) {
            $rowNumber = $this->headingRow() + $index + 1; // Fila real en Excel
            $estado = 0;
            $observacionGeneral = '';

            try {
                // Validar que las columnas necesarias existan
                if (!isset($row['cod_producto']) && !isset($row['nombre'])) {
                    continue;
                }
                
                // Validar datos
                $validationResult = $this->validateRow($row, $rowNumber);
                
                if ($validationResult['fails']) {
                    $estado = 1;
                    $observacionGeneral = $validationResult['errors'];
                }

                $bodega = null;
                $familia = null;

                if ($row['cod_bodega'] && empty($validationResult['errors'])) {
                    $bodega = FacBodegas::on($this->connectionName)
                        ->where('codigo', $row['cod_bodega'])
                        ->select('id')
                        ->first();
                }

                if ($row['cod_familia'] && empty($validationResult['errors'])) {
                    $familia = FacFamilias::on($this->connectionName)
                        ->where('codigo', $row['cod_familia'])
                        ->select('id')
                        ->first();
                }

                $batchData[] = [
                    'codigo' => $row['cod_producto'] ?? '',
                    'nombre' => $row['nombre'] ?? '',
                    'id_familia' => $familia ? $familia->id : null,
                    'id_bodega' => $bodega ? $bodega->id : null,
                    'costo' => floatval($row['costo'] ?? 0),
                    'venta' => floatval($row['precio'] ?? 0),
                    'existencias' => intval($row['existencias'] ?? 0),
                    'observacion' => $observacionGeneral,
                    'row' => $rowNumber,
                    'estado' => $estado,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                // Insertar en lotes de 200
                if (count($batchData) >= 200) {
                    $this->insertBatch($batchData);
                    $batchData = [];
                }
                
            } catch (\Exception $e) {
                Log::error("Error en fila {$rowNumber}: " . $e->getMessage());
                $this->errors[] = [
                    'row' => $rowNumber,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        // Insertar los registros restantes
        if (!empty($batchData)) {
            $this->insertBatch($batchData);
        }
    }
    
    private function validateRow($row, $rowNumber): array
    {
        $errors = [];
        
        // Validación manual
        if (empty($row['cod_producto'])) {
            $errors[] = 'El código de producto es requerido';
        } else {
            // Verificar unicidad manualmente
            $exists = FacProductos::on($this->connectionName)
                ->where('codigo', $row['cod_producto'])
                ->exists();
                
            if ($exists) {
                $errors[] = 'El código de producto ya existe';
            }
        }
        
        if (empty($row['nombre'])) {
            $errors[] = 'El nombre es requerido';
        } elseif (strlen($row['nombre']) > 255) {
            $errors[] = 'El nombre no puede exceder 255 caracteres';
        }
        
        if (empty($row['cod_familia'])) {
            $errors[] = 'El código de familia es requerido';
        } elseif (strlen($row['cod_familia']) < 1 || strlen($row['cod_familia']) > 10) {
            $errors[] = 'El código de familia debe tener entre 1 y 10 caracteres';
        } else {
            $familiaExists = FacFamilias::on($this->connectionName)
                ->where('codigo', $row['cod_familia'])
                ->exists();
                
            if (!$familiaExists) {
                $errors[] = 'El código de familia no existe';
            }
        }
        
        if (empty($row['cod_bodega'])) {
            $errors[] = 'El código de bodega es requerido';
        } elseif (strlen($row['cod_bodega']) < 1 || strlen($row['cod_bodega']) > 10) {
            $errors[] = 'El código de bodega debe tener entre 1 y 10 caracteres';
        } else {
            $bodegaExists = FacBodegas::on($this->connectionName)
                ->where('codigo', $row['cod_bodega'])
                ->exists();
                
            if (!$bodegaExists) {
                $errors[] = 'El código de bodega no existe';
            }
        }
        
        if (empty($row['costo']) || !is_numeric($row['costo']) || $row['costo'] < 0) {
            $errors[] = 'El costo debe ser un número mayor o igual a 0';
        }
        
        if (empty($row['precio']) || !is_numeric($row['precio']) || $row['precio'] < 0) {
            $errors[] = 'El precio debe ser un número mayor o igual a 0';
        }
        
        if (!isset($row['existencias']) || !is_numeric($row['existencias']) || $row['existencias'] < 0) {
            $errors[] = 'Las existencias deben ser un número entero mayor o igual a 0';
        }
        
        return [
            'fails' => !empty($errors),
            'errors' => implode(', ', $errors)
        ];
    }
    
    private function insertBatch(array $batchData)
    {
        try {
            DB::connection($this->connectionName)
                ->table('fac_productos_imports')
                ->insert($batchData);
        } catch (\Exception $e) {
            Log::error('Error al insertar batch: ' . $e->getMessage());
            foreach ($batchData as $data) {
                try {
                    FacProductosImport::on($this->connectionName)->create($data);
                } catch (\Exception $singleError) {
                    $this->errors[] = [
                        'row' => $data['row'],
                        'error' => 'Error específico: ' . $singleError->getMessage()
                    ];
                }
            }
        }
    }

    // Eliminar el método rules() ya que no lo usamos más
    // public function rules(): array { ... }

    // También puedes eliminar customValidationAttributes()

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function(AfterImport $event) {
                // Enviar errores si existen
                if (!empty($this->errors)) {
                    Log::error('Errores en importación:', $this->errors);
                }
                
                event(new PrivateMessageEvent("importador-productos-" . $this->url_notification, [
                    'tipo' => 'exito',
                    'mensaje' => 'Carga de plantilla de productos finalizado totalmente!',
                    'titulo' => 'Plantilla de productos',
                    'autoclose' => false
                ]));
            },
        ];
    }
    
    public function chunkSize(): int
    {
        return 200;
    }
    
    public function batchSize(): int
    {
        return 200;
    }

    public function headingRow(): int
    {
        return 2;
    }
}
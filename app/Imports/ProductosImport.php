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

    public function __construct(string $url_notification, $empresa_id)
    {
        $this->url_notification = $url_notification;
        $this->empresa = Empresa::find($empresa_id);
        
        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $this->empresa->token_db);
    }
    
    private $rowNumber = 2;
    private $errors = [];
    private $messages = [
        'required' => 'El campo :attribute es requerido.',
        'exists' => 'El :attribute es inválido.',
        'numeric' => 'El campo :attribute debe ser un valor numérico.',
        'string' => 'El campo :attribute debe ser texto',
        'array' => 'El campo :attribute debe ser un arreglo.',
        'date' => 'El campo :attribute debe ser una fecha válida.',
    ];

    public function collection(Collection $rows)
    {
        $batchData = [];
        
        foreach ($rows as $row) {

            $estado = 0;
            $observacionGeneral = '';

            try {
                // Validar que las columnas necesarias existan
                if (!isset($row['cod_producto']) && !isset($row['nombre'])) {
                    continue;
                }
                
                $this->rowNumber++;
                $validator = Validator::make($row->toArray(), $this->rules(), $this->messages);

                if ($validator->fails()){
                    $estado = 1;
                    $errors = $validator->errors()->all();
                    $observacionGeneral = implode(", ", $errors);
                }

                $bodega = null;
                $familia = null;

                if ($row['cod_bodega']) {
                    $bodega = FacBodegas::where('codigo', $row['cod_bodega'])
                        ->select('id')
                        ->first();
                }

                if ($row['cod_familia']) {
                    $familia = FacFamilias::where('codigo', $row['cod_familia'])
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
                    'row' => $this->rowNumber,
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
                Log::error("Error en fila {$this->rowNumber}: " . $e->getMessage());
            }
        }
        // Insertar los registros restantes
        if (!empty($batchData)) {
            $this->insertBatch($batchData);
        }
    }
    
    private function insertBatch(array $batchData)
    {
        try {
            DB::connection('sam')->table('fac_productos_imports')->insert($batchData);
        } catch (\Exception $e) {
            Log::error('Error al insertar batch: ' . $e->getMessage());
            // Puedes intentar insertar uno por uno para identificar el problema
            foreach ($batchData as $data) {
                try {
                    FacProductosImport::create($data);
                } catch (\Exception $singleError) {
                    $this->errors[] = [
                        'row' => $data['row'],
                        'error' => 'Error específico: ' . $singleError->getMessage()
                    ];
                }
            }
        }
    }

    public function rules(): array
    {
        return [
            'cod_producto' => 'required|string|unique:sam.fac_productos,codigo',
            'nombre' => 'required|string|max:255',
            'cod_familia' => 'required|min:1|max:10|string|exists:sam.fac_familias,codigo',
            'cod_bodega' => 'required|min:1|max:10|string|exists:sam.fac_bodegas,codigo',
            'costo' => 'required|numeric|min:0',
            'precio' => 'required|numeric|min:0',
            'existencias' => 'required|integer|min:0',
        ];
    }

    public function customValidationAttributes()
    {
        return [
            '0' => 'cod_producto',
            '1' => 'nombre',
            '2' => 'costo',
            '3' => 'precio',
            '4' => 'cod_familia',
            '5' => 'cod_bodega',
            '6' => 'existencias',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function(AfterImport $event) {
                // Este código solo se ejecuta cuando TODOS los chunks han finalizado
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
        return 200; // Procesar 200 filas por chunk
    }
    
    public function batchSize(): int
    {
        return 200; // Insertar 200 registros por lote
    }

    public function headingRow(): int
    {
        return 2;
    }
}
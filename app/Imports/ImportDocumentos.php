<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\PrivateMessageEvent;
use Illuminate\Support\Facades\Validator;
//EXCEL
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Events\AfterImport;
// use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
//QUEUE
use Illuminate\Contracts\Queue\ShouldQueue;
// MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\CentroCostos;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\DocumentosImport;

class ImportDocumentos implements
    ToCollection,
    WithHeadingRow,
    WithChunkReading,
    WithBatchInserts,
    ShouldQueue
    // WithEvents
{
    use Importable;

    protected $empresa = null;
    protected $rowsWithErrors = 0;
    protected $totalValidRows = 0;
    protected $connectionName = 'sam';
    protected $url_notification = null;

    private $errors = [];
    private $processedRows = 0;

    public function __construct(string $url_notification, $empresa_id)
    {
        $this->url_notification = $url_notification;
        $this->empresa = Empresa::find($empresa_id);
    }

    public function collection(Collection $rows)
    {
        $validRows = 0;
        $batchData = [];

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $this->empresa->token_db);

        $this->totalValidRows = 0;

        foreach ($rows as $row) {
            if (isset($row['debito']) || isset($row['credito'])) {
                $this->totalValidRows++;
            }
        }

        if (!$this->totalValidRows) {
            return;
        }

        // Enviar evento inicial con el total de filas a procesar (solo las válidas)
        event(new PrivateMessageEvent("importador-documentos-" . $this->url_notification, [
            'name' => 'progress',
            'tipo' => 'info',
            'mensaje' => "Iniciando procesamiento de {$this->totalValidRows} registros...",
            'titulo' => 'Importación de documentos',
            'progress' => 0,
            'processed' => 0,
            'total' => $this->totalValidRows,
            'stage' => 'preparing',
            'autoclose' => false
        ]));

        foreach ($rows as $index => $row) {
            $rowNumber = $this->headingRow() + $index + 1;
            $estado = 0;
            $observacionGeneral = '';

            // Validar que las columnas necesarias existan
            if (!isset($row['debito']) && !isset($row['credito'])) {
                continue;
            }

            $validRows++;

            try {
                // Validar datos
                $validationResult = $this->validateRow($row, $rowNumber);
                $row = $validationResult['row'];

                if ($validationResult['fails']) {
                    $estado = 1;
                    $this->rowsWithErrors++;
                    $observacionGeneral = $validationResult['errors'];
                }

                $batchData[] = [
                    'documento_nit' => $row['documento_nit'],
                    'cuenta_contable' => $row['cuenta_contable'],
                    'codigo_cecos' => $row['cod_cecos'],
                    'codigo_comprobante' => $row['cod_comprobante'],
                    'consecutivo' => $row['consecutivo'],
                    'documento_referencia' => $row['doc_referencia'],
                    'fecha_manual' => $row['fecha_manual'],
                    'debito' => $row['debito'],
                    'credito' => $row['credito'],
                    'concepto' => $row['concepto'],
                    'nombre_nit' => $row['nombre_nit'],
                    'nombre_cuenta' => $row['nombre_cuenta'],
                    'nombre_cecos' => $row['nombre_cecos'],
                    'nombre_comprobante' => $row['nombre_comprobante'],
                    'errores' => $observacionGeneral,
                    'total_errores' => $rowNumber,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                // Enviar evento de progreso cada 100 filas válidas procesadas
                if ($validRows % 34 === 0) {
                    $progress = round(($validRows / $this->totalValidRows) * 100);
                    event(new PrivateMessageEvent("importador-documentos-" . $this->url_notification, [
                        'name' => 'progress',
                        'tipo' => 'info',
                        'mensaje' => "Procesados {$validRows} de {$this->totalValidRows} registros. Errores: {$this->rowsWithErrors}",
                        'titulo' => 'Importación de documentos',
                        'progress' => $progress,
                        'processed' => $validRows,
                        'total' => $this->totalValidRows,
                        'stage' => 'processing',
                        'autoclose' => false
                    ]));
                }

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

        // Enviar evento de finalización
        event(new PrivateMessageEvent("importador-documentos-" . $this->url_notification, [
            'name' => 'progress',
            'tipo' => 'success',
            'mensaje' => "Procesamiento completado: {$validRows} registros procesados, {$this->rowsWithErrors} con errores.",
            'titulo' => 'Importación de documentos',
            'progress' => 100,
            'processed' => $validRows,
            'total' => $this->totalValidRows,
            'stage' => 'completed',
            'autoclose' => false
        ]));
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

    private function validateRow($row, $rowNumber): array
    {
        $errors = [];
        $documentoNit = $row['documento_nit'];
        $cuentasContables = $row['cuenta_contable'];
        $cuentasContablesDb = null;
        $codigoCecos = $row['cod_cecos'];
        $codigoComprobante = $row['cod_comprobante'];
        $consecutivo = $row['consecutivo'];
        $documentoReferencia = $row['doc_referencia'];
        $debito = (float)$row['debito'];
        $credito = (float)$row['credito'];
        $concepto = $row['concepto'];
        $fechaManual = $this->parseFecha($row['fecha_manual']);

        $row['nombre_cuenta'] = null;
        $row['nombre_nit'] = null;
        $row['nombre_comprobante'] = null;
        $row['nombre_cecos'] = null;

        // VALIDAR CUENTA CONTABLE
        if (empty($cuentasContables)) {
            $errors[] = 'La Cuenta contable es requerida';
        } else {
            // Verificar unicidad manualmente
            $exists = PlanCuentas::on($this->connectionName)
                ->where('cuenta', $cuentasContables)
                ->first();

            $cuentasContablesDb = $exists;
                
            if ($exists) {
                $row['nombre_cuenta'] =  "{$exists->cuenta} - {$exists->nombre}";
            } else {
                $errors[] = "La cuenta contable: {$cuentasContables} no existe";
            }
        }

        //VALIDAR NIT
        if (empty($documentoNit)) {
            $errors[] = 'El Documento nit es requerido';
        } else {
            // Verificar unicidad manualmente
            $exists = Nits::on($this->connectionName)
                ->where('numero_documento', $documentoNit)
                ->first();
                
            if ($exists) {
                $row['nombre_nit'] =  "{$exists->numero_documento} - {$exists->nombre_completo}";
            } else {
                $errors[] = "El numero de documento: {$documentoNit} no existe";
            }
        }

        //VALIDAR COMPROBANTE
        if (empty($codigoComprobante)) {
            $errors[] = 'El Comprobante es requerido';
        } else {
            // Verificar unicidad manualmente
            $exists = Comprobantes::on($this->connectionName)
                ->where('codigo', $codigoComprobante)
                ->first();
                
            if ($exists) {
                $row['nombre_comprobante'] =  "{$exists->codigo} - {$exists->nombre}";
            } else {
                $errors[] = "El Código del comprobante: {$codigoComprobante} no existe";
            }
        }

        //VALIDAR CENTRO DE COSTOS
        if (empty($codigoCecos) && $cuentasContablesDb && $cuentasContablesDb->exige_centro_costos) {
            $errors[] = 'Centro de costos es requerido para la cuenta contable';
        } else if (!empty($codigoCecos)) {
            // Verificar unicidad manualmente
            $exists = CentroCostos::on($this->connectionName)
                ->where('codigo', $codigoCecos)
                ->first();

            if ($exists) {
                $row['nombre_cecos'] =  "{$exists->codigo} - {$exists->nombre}";
            } else {
                $errors[] = "El codigo cecos: {$codigoCecos} no existe";
            }
        }

        //VALIDAR VALORES
        if ($debito && $credito) {
            $errors[] = "No puede tener valor en el credito & debito";
        }

        if ($debito < 0) {
            $errors[] = "El debito no puede ser menos que 0";
        }

        if ($credito < 0) {
            $errors[] = "El credito no puede ser menos que 0";
        }

        //VALIDAR FECHA MANUAL
        if (!$fechaManual) {
            $errors[] = "La fecha manual es requerida";
        }

        return [
            'row' => $row,
            'fails' => !empty($errors),
            'errors' => implode(', ', $errors)
        ];
    }

    private function insertBatch(array $batchData)
    {
        try {
            DB::connection($this->connectionName)
                ->table('documentos_imports')
                ->insert($batchData);
        } catch (\Exception $e) {
            Log::error('Error al insertar batch: ' . $e->getMessage());
            foreach ($batchData as $data) {
                try {
                    DocumentosImport::on($this->connectionName)->create($data);
                } catch (\Exception $singleError) {
                    $this->errors[] = [
                        'row' => $data['row'],
                        'error' => 'Error específico: ' . $singleError->getMessage()
                    ];
                }
            }
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function(AfterImport $event) {
                // Enviar errores si existen
                if (!empty($this->errors)) {
                    Log::error('Errores en importación:', $this->errors);
                }
                
                event(new PrivateMessageEvent("importador-documentos-" . $this->url_notification, [
                    'name' => 'carga',
                    'tipo' => 'exito',
                    'mensaje' => 'Carga de plantilla de documentos finalizado totalmente!',
                    'titulo' => 'Plantilla de documentos',
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

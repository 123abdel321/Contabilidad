<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Events\PrivateMessageEvent;
//MODELS
use App\Models\User;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacProductosImport;
use App\Models\Sistema\FacProductosBodegas;

class ProcessImportProductos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hora
    public $tries = 3;
    public $backoff = [60, 180, 300];
    protected $id_usuario;
    protected $id_empresa;
    protected $totalRecords = 0;
    protected $processedRecords = 0;
    protected $correctRecords = 0;
    protected $errorRecords = 0;

    public function __construct($id_empresa, $id_usuario)
    {
        $this->id_empresa = $id_empresa;
        $this->id_usuario = $id_usuario;
    }

    public function handle()
    {
        $this->empresa = Empresa::find($this->id_empresa);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $this->empresa->token_db);

        try {
            // Contar total de registros a procesar
            $this->totalRecords = FacProductosImport::where('estado', 0)->count();
            
            // Enviar evento de inicio
            event(new PrivateMessageEvent("importador-productos-" . $this->empresa->token_db.'_'.$this->id_usuario, [
                'name' => 'progress',
                'tipo' => 'info',
                'mensaje' => 'Iniciando carga de productos al sistema...',
                'titulo' => 'Carga de productos',
                'progress' => 0,
                'processed' => 0,
                'total' => $this->totalRecords,
                'stage' => 'preparing'
            ]));

            DB::connection('sam')->beginTransaction();

            // Procesar en chunks para evitar memory issues
            FacProductosImport::where('estado', 0)
                ->chunkById(1000, function ($imports) {
                    foreach ($imports as $import) {
                        $this->processProduct($import);
                        $this->processedRecords++;
                        
                        // Enviar evento de progreso cada 100 registros procesados
                        if ($this->processedRecords % 100 === 0) {
                            $progress = $this->totalRecords > 0 
                                ? round(($this->processedRecords / $this->totalRecords) * 100) 
                                : 0;
                            
                            event(new PrivateMessageEvent("importador-productos-" . $this->empresa->token_db.'_'.$this->id_usuario, [
                                'name' => 'progress',
                                'tipo' => 'info',
                                'mensaje' => 'Cargando productos al sistema...',
                                'titulo' => 'Carga de productos',
                                'progress' => $progress,
                                'processed' => $this->processedRecords,
                                'total' => $this->totalRecords,
                                'stage' => 'processing'
                            ]));
                        }
                    }
                });

            // Eliminar registros procesados correctamente
            FacProductosImport::where('estado', 0)->delete();

            DB::connection('sam')->commit();

            // Enviar evento de completado exitoso
            event(new PrivateMessageEvent("importador-productos-" . $this->empresa->token_db.'_'.$this->id_usuario, [
                'name' => 'progress',
                'tipo' => 'success',
                'mensaje' => '¡Carga de productos completada exitosamente!',
                'titulo' => 'Carga de productos',
                'progress' => 100,
                'processed' => $this->processedRecords,
                'total' => $this->totalRecords,
                'stage' => 'completed'
            ]));

            // Enviar evento final de importación
            event(new PrivateMessageEvent("importador-productos-" . $this->empresa->token_db.'_'.$this->id_usuario, [
                'name' => 'import',
                'tipo' => 'exito',
                'mensaje' => 'Importador de productos finalizado totalmente!',
                'titulo' => 'Importador de productos',
                'autoclose' => false
            ]));

        } catch (\Exception $exception) {
            DB::connection('sam')->rollback();
            
            // Enviar evento de error
            event(new PrivateMessageEvent("importador-productos-" . $this->empresa->token_db.'_'.$this->id_usuario, [
                'name' => 'progress',
                'tipo' => 'error',
                'mensaje' => 'Error durante la carga de productos: ' . $exception->getMessage(),
                'titulo' => 'Carga de productos',
                'progress' => 0,
                'processed' => $this->processedRecords,
                'total' => $this->totalRecords,
                'stage' => 'error'
            ]));
            
            throw $exception;
        }        
    }
    
    private function processProduct(FacProductosImport $import)
    {
        DB::transaction(function () use ($import) {
            try {
                $margen = ($import->venta > 0) 
                    ? (($import->venta - $import->costo) / $import->venta) * 100 
                    : 0;
                // Buscar o crear producto
                $producto = FacProductos::Create([
                    'codigo' => $import->codigo,
                    'nombre' => $import->nombre,
                    'id_familia' => $import->id_familia,
                    'precio_inicial' => $import->costo,
                    'precio_minimo' => $import->costo,
                    'precio' => $import->venta,
                    'porcentaje_utilidad' => $margen,
                    'tipo_producto' => 0,
                    'estado' => 1
                ]);

                if ($import->existencias) {
                    FacProductosBodegas::where('id_producto', $producto->id)
                        ->where('id_bodega', $import->id_bodega)
                        ->update([
                            'cantidad' => $import->existencias
                        ]);
                }
                
                $this->correctRecords++;
                
            } catch (\Exception $e) {
                $this->errorRecords++;
                // Puedes registrar el error si lo necesitas
                // Log::error("Error procesando producto {$import->codigo}: " . $e->getMessage());
            }
        });
    }
}
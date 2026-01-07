<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Helpers\Eco\SendEcoEmail;
//MODEL
use App\Models\Empresas\Empresa;

class SendSingleEmail implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;
    public $maxExceptions = 1;

    public function __construct(
        public Empresa $empresa,
        public string $email,
        public array $emailData,
        public array $filterData,
        public string $filePath,
        public string $ecoToken,
        public string $view
    ) {}

    public function handle()
    {
        try {
            copyDBConnection('sam', 'sam');
            setDBInConnection('sam', $this->empresa->token_db);

            $htmlContent = View::make($this->view, $this->emailData)->render();
            
            $subject = $this->emailData['asunto'] ?? 'Notificación de ' . $this->empresa->razon_social;
            
            $attachments = [];
            
            if (!empty($this->filePath)) {
                // Verificar si es una URL o una ruta local
                if (filter_var($this->filePath, FILTER_VALIDATE_URL)) {
                    // Es una URL - descargar el contenido
                    $pdfResponse = Http::timeout(30)->get($this->filePath);
                    
                    if ($pdfResponse->successful()) {
                        $fileContentBase64 = base64_encode($pdfResponse->body());
                        
                        // Obtener nombre del archivo de la URL
                        $fileName = basename(parse_url($this->filePath, PHP_URL_PATH));
                        if (empty($fileName) || $fileName === '/') {
                            $fileName = 'documento_adjunto.pdf';
                        }
                        
                        // Determinar MIME type
                        $mimeType = $this->getMimeTypeFromFileName($fileName);
                        
                        $attachments[] = [
                            "contenido" => $fileContentBase64,
                            "nombre" => $fileName,
                            "mime" => $mimeType
                        ];
                    } else {
                        Log::warning('SendSingleEmail: No se pudo descargar el archivo desde la URL.', [
                            'filePath' => $this->filePath,
                            'status' => $pdfResponse->status(),
                        ]);
                    }
                } else {
                    // Es una ruta local - leer el archivo directamente
                    if (file_exists($this->filePath)) {
                        $fileContent = file_get_contents($this->filePath);
                        $fileContentBase64 = base64_encode($fileContent);
                        
                        // Obtener nombre del archivo de la ruta
                        $fileName = basename($this->filePath);
                        
                        // Determinar MIME type
                        $mimeType = mime_content_type($this->filePath) ?: 
                            $this->getMimeTypeFromFileName($fileName);
                        
                        $attachments[] = [
                            "contenido" => $fileContentBase64,
                            "nombre" => $fileName,
                            "mime" => $mimeType
                        ];
                    } else {
                        Log::warning('SendSingleEmail: Archivo local no encontrado.', [
                            'filePath' => $this->filePath,
                        ]);
                    }
                }
            }

            $metadata = array_merge($this->emailData, [
                'contexto' => $this->view,
                'envio_id' => '',
                'empresa_token' => $this->empresa->token_db,
            ]);

            $sendEcoEmail = new SendEcoEmail(
                $this->email,
                $subject,
                $htmlContent,
                $metadata,
                $this->filterData,
                $attachments
            );

            $sendEcoEmail->setToken($this->ecoToken)->send();
    
        } catch (\Throwable $exception) {
            Log::error('SendSingleEmail falló', [
                'email' => $this->email,
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'pdf_path' => $this->filePath,
            ]);
            throw $exception; 
        }
    }

    private function getMimeTypeFromFileName(string $fileName): string
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'zip' => 'application/zip',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    public function failed(\Throwable $exception)
    {
        Log::error('SendSingleEmail falló', [
            'email' => $this->email,
            'error' => $exception->getMessage(),
            'line' => $exception->getLine(),
            'pdf_path' => $this->filePath,
        ]);
    }
}
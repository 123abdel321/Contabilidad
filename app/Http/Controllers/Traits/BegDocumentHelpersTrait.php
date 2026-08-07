<?php

namespace App\Http\Controllers\Traits;

use ZipArchive;
use Carbon\Carbon;
use DateTimeImmutable;
use App\Mail\GeneralEmail;
use App\Helpers\BegEmailSender;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacVentas;
use App\Models\Sistema\envioEmail;
use App\Models\Sistema\FacResoluciones;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\DocumentosGeneral;
//JOBS
use App\Jobs\SendSingleEmail;

trait BegDocumentHelpersTrait
{
	public function sendEmailFactura(string $has_empresa, string $email, FacVentas $factura, $pdf = null, $xml = null)
	{
		$empresa = Empresa::where('token_db', $has_empresa)->first();
		$ecoToken = VariablesEntorno::where('nombre', 'eco_login')->first();
		$ecoToken = $ecoToken?->valor ?? null;

		if (!$ecoToken) {
			return true;
		}

		$zip = null;
		$file = null;
		$pdfContent = null;

		if ($pdf) {
			// Si es una URL, la obtenemos con HTTP
			if (filter_var($pdf, FILTER_VALIDATE_URL)) {
				$response = Http::get($pdf);
				if ($response->successful()) {
					$pdfContent = $response->body();
				} else {
					// Manejar error: el PDF no se pudo descargar
					\Log::error("No se pudo descargar el PDF desde: $pdf");
				}
			} else {
				// Si es una ruta local, la leemos con Storage
				$pdfContent = Storage::disk('do_spaces')->get($pdf);
			}
		}

		if($this->isFe($factura)) {
			$xml = $xml ?: $this->getXml($factura);
			$zip = $this->generateZip($factura->documento_referencia_fe, $pdfContent, $xml);
		}

		if ($zip) $file = $zip;
		else $file = $pdf;

		$filterData = [
			'id_nit' => $factura->cliente->id_nit,
			'nombre_completo' => $factura->cliente->nombre_completo,
			'numero_documento' => $factura->cliente->numero_documento,
			'email' => $factura->cliente->email,
		];

		$emailData = [
			'cliente' => $factura->cliente,
			'factura' => $factura,
			'empresa' => $empresa
		];

		SendSingleEmail::dispatch(
			$empresa,
			$email,
			$emailData,
			$filterData,
			$file,
			$ecoToken,
			'emails.capturas.factura',
		);

		return true;
	}

    public function getXml(FacVentas $venta)
	{

		if ($venta->fe_xml_file) {
			$file = Storage::disk('do_spaces')->get("/{$venta->fe_xml_file}");
			if ($file) {
				return $file;
			}
		}
		
		$bearerToken = VariablesEntorno::where('nombre', 'token_key_fe')->first();
        $bearerToken = $bearerToken ? $bearerToken->valor : '';
		$url = 'https://fe.portafolioerp.com/api/ubl2.1/invoice/xml?number='.$venta->documento_referencia_fe;

		$response = Http::withHeaders([
			'Content-Type' => 'application/json',
			'X-Requested-With' => 'XMLHttpRequest',
			'Authorization' => 'Bearer ' . $bearerToken,
		])
			->get($url)
			->throw()
			->json();

		if (array_key_exists("base64Bytes", $response)) {
			return base64_decode($response['base64Bytes']);
		}

		return '';
	}

    public function isFe(FacVentas $factura)
	{
		if($factura instanceof FacVentas) {
			return $factura->resolucion->tipo_resolucion == FacResoluciones::TIPO_FACTURA_ELECTRONICA;
		}

		return $factura->resolucion->tipo_resolucion == FacResoluciones::TIPO_FACTURA_ELECTRONICA;
	}

    public function generateZip($filename, $pdfContent, $xmlContent)
	{
		$zip = new ZipArchive();
		$tempPath = storage_path('app/temp');

		if (!File::exists($tempPath)) {
			File::makeDirectory($tempPath, 0755, true);
		}

		$zipFilename = "$tempPath/$filename.zip";

		try {
			if ($zip->open($zipFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
				// Agregamos el contenido binario del PDF
				$zip->addFromString($filename . '.pdf', $pdfContent);
				$zip->addFromString($filename . '.xml', $xmlContent);
				$zip->close();
				return $zipFilename;
			}
		} catch (\Exception $e) {
			\Log::error("Error al crear ZIP: " . $e->getMessage());
		}

		return '';
	}

	private function isComprobanteInUse($idComprobante, $relationType = 2) : bool
	{
		$documentos = DocumentosGeneral::where('id_comprobante', $idComprobante)
			->whereNotNull('relation_id')
			->whereNotNull('relation_type')
			->where('relation_type', '!=', $relationType)
			->count();

		return $documentos > 0 ? true : false;
	}

	private function isFechaCierreLimit($fecha_manual)
	{
		$fechaCierre = VariablesEntorno::where('nombre', 'fecha_ultimo_cierre')->first();
		$fechaCierre = $fechaCierre ? $fechaCierre->valor : NULL;

		if (!$fechaCierre) {
			return false;
		}

		$fechaCierre = Carbon::parse($fechaCierre);
        $fechaManual = Carbon::parse($fecha_manual);
		
		if ($fechaManual < $fechaCierre) {
			return true;
		}
		return false;
	}

	private function filterCapturaMensual($captura, $fecha_manual)
	{
		$fecha = Carbon::parse($fecha_manual);
		$startOfMonth = $fecha->copy()->startOfMonth();
		$endOfMonth = $fecha->copy()->endOfMonth();

		$captura->whereBetween('fecha_manual', [$startOfMonth, $endOfMonth]);
	}

	public function numeroALetras($numero, $moneda = 'pesos', $centimos = 'centavos') {
		$numero = number_format($numero, 2, '.', '');
		$partes = explode('.', $numero);
		$entero = (int)$partes[0];
		$decimal = (int)$partes[1];

		$letras = $this->convertirNumeroGrande($entero);

		if ($entero == 1) {
			$letras .= ' ' . rtrim($moneda, 's');
		} else {
			$letras .= ' ' . $moneda;
		}

		if ($decimal > 0) {
			$letras .= ' CON ' . $this->convertirNumeroGrande($decimal);
			$letras .= ($decimal == 1) ? ' ' . rtrim($centimos, 's') : ' ' . $centimos;
		}

		return ucfirst(strtolower($letras));
	}

	public function convertirNumeroGrande($n)
	{
		if ($n == 0) return 'CERO';

		$partes = [];
		$millones = floor($n / 1000000);
		if ($millones > 0) {
			$partes[] = ($millones == 1) ? 'UN MILLON' : $this->convertirNumero($millones) . ' MILLONES';
			$n -= $millones * 1000000;
		}

		$miles = floor($n / 1000);
		if ($miles > 0) {
			$partes[] = ($miles == 1) ? 'MIL' : $this->convertirNumero($miles) . ' MIL';
			$n -= $miles * 1000;
		}

		if ($n > 0) {
			$partes[] = $this->convertirNumero($n);
		}

		return implode(' ', $partes);
	}

	public function convertirNumero($n)
	{
		$unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
		$decenas  = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
		$centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];
		$especiales = [
			10 => 'DIEZ', 11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE',
			15 => 'QUINCE', 16 => 'DIECISÉIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO', 19 => 'DIECINUEVE',
			20 => 'VEINTE', 21 => 'VEINTIUN', 22 => 'VEINTIDÓS', 23 => 'VEINTITRÉS', 24 => 'VEINTICUATRO',
			25 => 'VEINTICINCO', 26 => 'VEINTISÉIS', 27 => 'VEINTISIETE', 28 => 'VEINTIOCHO', 29 => 'VEINTINUEVE'
		];

		if ($n == 0) return 'CERO';
		if ($n == 100) return 'CIEN';
		if ($n < 30 && isset($especiales[$n])) return $especiales[$n];

		$c = (int)($n / 100);
		$resto = $n % 100;
		$d = (int)($resto / 10);
		$u = $resto % 10;

		$texto = '';
		if ($c > 0) {
			$texto .= $centenas[$c] . ' ';
		}

		if ($d > 0) {
			if ($d == 1 && $u > 0) {
				$texto .= $especiales[10 + $u] . ' ';
			} elseif ($d == 2 && $u > 0) {
				$texto .= $especiales[20 + $u] ?? '' . ' ';
			} else {
				$texto .= $decenas[$d];
				if ($u > 0) {
					$texto .= ' Y ' . $unidades[$u];
				}
				$texto .= ' ';
			}
		} elseif ($u > 0) {
			$texto .= $unidades[$u] . ' ';
		}

		return trim($texto);
	}

	public function generarClavePDF($has_empresa, $id_comprobante, $consecutivo, $fecha_manual)
    {
        $data = [
            'has_empresa' => $has_empresa,
            'id_comprobante' => $id_comprobante,
            'consecutivo' => $consecutivo,
            'fecha_manual' => $fecha_manual,
        ];

        $json = json_encode($data);
        $compressed = gzcompress($json, 9);

        return rtrim(strtr(base64_encode($compressed), '+/', '-_'), '=');
    }

}

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

        if($this->isFe($factura)) {
			$xml = $xml ?: $this->getXml($factura);
			$zip = $this->generateZip($factura->documento_referencia_fe, $pdf, $xml);
		}

		if ($pdf) {
			$path = stripslashes($pdf);
			$baseUrl = "https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/";
			
			if (!str_contains($path, $baseUrl)) {
				$pdf = $baseUrl . $path;
			}
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

    public function generateZip($filename, $pdf, $xml)
	{
		$zip = new ZipArchive();
		$tempPath = storage_path('app/temp');

		if(!File::exists($tempPath)) {
			File::makeDirectory($tempPath);
		}

		$zipFilename = "$tempPath/$filename.zip";

		try {
			if($zip->open($zipFilename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
				$zip->addFromString($filename.'.pdf', $pdf);
				$zip->addFromString($filename.'.xml', $xml);
				$zip->close();

				return $zipFilename;
			}
		} catch (\Exception $exception) {
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
		$fechaCierre = VariablesEntorno::where('nombre', 'token_key_fe')->first();
		$fechaCierre = $fechaCierre ? $fechaCierre->valor : NULL;

		if (!$fechaCierre) {
			return false;
		}

		$fechaCierre = DateTimeImmutable::createFromFormat('Y-m-d', $fechaCierre);
        $fechaManual = DateTimeImmutable::createFromFormat('Y-m-d', $fecha_manual);

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
}

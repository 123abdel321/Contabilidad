<?php

namespace App\Http\Controllers\Traits;

use ZipArchive;
use Carbon\Carbon;
use App\Mail\GeneralEmail;
use App\Helpers\BegEmailSender;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacVentas;
use App\Models\Sistema\envioEmail;
use App\Models\Sistema\FacResoluciones;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\DocumentosGeneral;

trait BegDocumentHelpersTrait
{
	public function sendEmailFactura(string $has_empresa, string $email, FacVentas $factura, $pdf = null, $xml = null)
	{
		$empresa = Empresa::where('token_db', $has_empresa)->first();

        if($this->isFe($factura)) {
			$xml = $xml ?: $this->getXml($factura);
			$zip = $this->generateZip($factura->documento_referencia_fe, $pdf, $xml);

			Mail::to($email)
				->cc('noreply@maximoph.com')
				->bcc('bcc@maximoph.com')
				->send(new GeneralEmail($empresa->razon_social, 'emails.capturas.factura', [
					'cliente' => $factura->cliente,
					'factura' => $factura,
					'empresa' => $empresa
				], $zip));
		} else {
			Mail::to($email)
				->cc('noreply@maximoph.com')
				->bcc('bcc@maximoph.com')
				->send(new GeneralEmail($empresa->razon_social, 'emails.capturas.factura', [
					'cliente' => $factura->cliente,
					'factura' => $factura,
					'empresa' => $empresa
				], $pdf));
		}

		return true;
	}

    public function getXml(FacVentas $documento)
	{
		$bearerToken = VariablesEntorno::where('nombre', 'token_key_fe')->first();
        $bearerToken = $bearerToken ? $bearerToken->valor	: '';
		$url = 'http://localhost:6666/api/ubl2.1/invoice/xml?number='.$documento->documento_referencia_fe;;

		$response = Http::withHeaders([
			'Content-Type' => 'application/json',
			'X-Requested-With' => 'XMLHttpRequest',
			'Authorization' => 'Bearer ' . $bearerToken,
		])
			->get($url)
			->throw()
			->json();

		return base64_decode($response['base64Bytes']);
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
        $fechaManual = DateTimeImmutable::createFromFormat('Y-m-d', $request->get('fecha_manual'));

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

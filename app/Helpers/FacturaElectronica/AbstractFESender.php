<?php

namespace App\Helpers\FacturaElectronica;

use App\Models\Sistema\VariablesEntorno;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

abstract class AbstractFESender
{
	protected $factura;
	protected $pagos;
	protected $cliente;
	protected $detalles;
	protected $softwareProviderId;
	protected $url = 'https://fe.portafolioerp.com/api/ubl2.1';

	public function __construct($factura)
	{
		$iva_inlucido = VariablesEntorno::where('nombre', 'iva_incluido')->first();
		$iva_inlucido = $iva_inlucido ? $iva_inlucido->valor : false;
		$factura->loadMissing($this->getRelationShips());
		
		$this->factura = $factura;
		$this->pagos = $factura->pagos;
		$this->cliente = $factura->cliente;
		$this->detalles = $factura->detalles;
		$this->iva_inluido = $iva_inlucido;
	}

	public abstract function getExtraParams(): array;
	public abstract function getEndpoint(): string;
	public abstract function getRelationShips(): array;

	private function getConfigApiFe(): array
	{
		$entorno = VariablesEntorno::whereIn('nombre', ['token_key_fe', 'set_test_id_fe', 'software_provider_id'])->get();

		$softwareProviderId = '';
		$bearerToken = '';
		$setTestId = '';

		if (count($entorno)) {
			$bearerToken = $entorno->firstWhere('nombre', 'token_key_fe');
			$bearerToken = $bearerToken ? $bearerToken->valor : '';

			$setTestId = $entorno->firstWhere('nombre', 'set_test_id_fe');
			$setTestId = $setTestId && $setTestId->valor ? '/' . $setTestId->valor	: '';

			$softwareProviderId = $entorno->firstWhere('nombre', 'software_provider_id');
			$softwareProviderId = $softwareProviderId ? $softwareProviderId->valor : '';
		}

		$this->softwareProviderId =  $softwareProviderId;

		return [$bearerToken, $setTestId];
	}

	public function getUrl()
	{
		$enviroment = env('APP_ENV') == 'local' ? '' : '';

		return $this->url . $this->getEndpoint() . $enviroment;
	}

	public function send()
	{
		[$bearerToken, $setTestId] = $this->getConfigApiFe();
		$params = $this->getParams();
		// dd($params, json_encode($params));
		$url = $this->getUrl() . $setTestId;

		$response = Http::withHeaders([
			'Content-Type' => 'application/json',
			'X-Requested-With' => 'XMLHttpRequest',
			'Authorization' => 'Bearer ' . $bearerToken
		])->post($url, $params);

		$data = (object) $response->json();
		
		info(json_encode($data));
		
		if (property_exists($data, 'status') && $data->status == 200) {
			return [
				"status" => $data->status,
				"cufe" => $data->response['cufe'],
				"xml_url" => $data->response["XmlUrl"],
				"mensaje" => $data->response['StatusDescription'],
				"zip_key" => ''
			];
		}

		if (property_exists($data, 'status') && $data->status == 400) {
			// Verificar si existe CUFE y si el error es por documento ya procesado
			$hasCufe = isset($data->response['cufe']);
			$isAlreadyProcessed = false;
			
			// Buscar el mensaje "Documento procesado anteriormente"
			if (isset($data->response['mensajesValidacion']['string'])) {
				foreach ($data->response['mensajesValidacion']['string'] as $mensaje) {
					if (strpos($mensaje, 'Documento procesado anteriormente') !== false) {
						$isAlreadyProcessed = true;
						break;
					}
				}
			}
			
			// Si tiene CUFE y es por documento ya procesado, tratarlo como éxito
			if ($hasCufe && $isAlreadyProcessed) {
				return [
					"zip_key" => '',
					"status" => 200, // Cambiamos a 200 para indicar éxito
					"cufe" => $data->response['cufe'],
					"xml_url" => '', // No hay URL porque ya fue procesado
					"mensaje" => "Documento ya fue procesado anteriormente",
				];
			}
		}

		if (property_exists($data, 'response')) {
			$response = $data->response;
			if (array_key_exists('unexpected', $response)) {
				$erroresOrganizados = [];

				foreach ($response['unexpected'] as $error) {
					// Dividir cada error en la parte de la regla y el mensaje
					$partes = explode(', ', $error, 2);
					
					if (count($partes) === 2) {
						$regla = $partes[0];
						$mensaje = $partes[1];
						
						// Agregar al arreglo organizado
						$erroresOrganizados[$regla] = [$mensaje];
					}
				}

				return [
					"status" => 500,
					"message_object" => 'Error al generar la factura',
					"error_message" => $erroresOrganizados,
					'json_response' => json_encode($params),
					"zip_key" => null
				];
			}
		}

		if (property_exists($data, 'status') && $data->status >= 500) {
			return [
				"status" => 500,
				"message_object" => ["Error interno: https://fe.portafolioerp.com"]
			];
		}

		if (property_exists($data, 'status') && $data->status >= 400) {
			
			$statusDescription = $data->data['StatusDescription'];
			$errorMessage = $data->data['ErrorMessage'];
			$zipKey = $data->data['ZipKey'];

			return [
				"status" => $data->status,
				"message_object" => $statusDescription,
				"error_message" => $errorMessage,
				'json_response' => json_encode($params),
				"zip_key" => $zipKey
			];
		}

		if(property_exists($data, 'cuds')){
			return [
				"status" => 200,
				"cuds" => $data->cuds,
				"mensaje" => $data->message,
				"xml" => $data->invoicexml,
				"data" => $data,
				"zip_key" => null
			];
		}

		if (!property_exists($data, 'data')) {
			if (property_exists($data, 'status')) {
				return [
					"status" => $data->status,
					"message_object" => $data->message,
					"error_message" => $data->errors,
					'json_response' => json_encode($params),
					"zip_key" => null
				];
			}
			if (property_exists($data, 'errors')) {
				return [
					"status" => 500,
					"message_object" => $data->message,
					"error_message" => $data->errors,
					'json_response' => json_encode($params),
					"zip_key" => null
				];
			}
		}
		
		$zipKey = $data->data->ResponseDian['Envelope']['Body']['SendTestSetAsyncResponse']['SendTestSetAsyncResult']['ZipKey'];

		return [
			"zip_key" => $zipKey,
			"status" => $data->status,
			"cufe" => $data->data["cufe"],
			"xml_url" => $data->data["XmlUrl"],
			"mensaje" => $data->data["StatusDescription"],
		];
	}

	public function getParams(): array
	{
		$params = array(
			'number' => $this->factura->consecutivo,
			'prefix' => $this->factura->resolucion->prefijo,
			'type_document_id' => CodigoDocumentoDianTypes::getIdTipoDocumentoDian(($this->factura->codigo_tipo_documento_dian)),
			'date' => date_format(date_create($this->factura->fecha_manual), 'Y-m-d'),
			'time' => date_format(date_create($this->factura->created_at), 'H:i:s'),
			'software-provider' => [
				'provider_id' => $this->softwareProviderId
			],
			'allowance_charges' => [],
			'tax_totals' => $this->taxTotals([1]),
			"withholding_tax_totals" => $this->taxTotals([5, 6]),
		);
		
		if (empty($params["withholding_tax_totals"])) unset($params["withholding_tax_totals"]);
		return array_merge($params, $this->getExtraParams());
	}

	protected function paymentForm()
	{
		$paymentForm = [];

		foreach ($this->pagos as $pago) {

			// 1. DETERMINAR payment_form_id (1=Contado, 2=Crédito)
			$esCredito = false;
			
			// Si la venta tiene fecha de vencimiento (campo que debes agregar o calcular)
			if (property_exists($this->factura, 'fecha_vencimiento') && $this->factura->fecha_vencimiento) {
				$esCredito = $this->factura->fecha_vencimiento > $this->factura->fecha_manual;
			}
			
			// O también podrías revisar si el tipo de forma de pago es "Crédito" (código 1 en fac_tipo_formas_pagos)
			$tipoCodigo = $pago->forma_pago->tipoFormaPago->codigo ?? '';
			if ($tipoCodigo == '1') {
				$esCredito = true; // "Instrumento no definido" suele usarse para crédito
			}
			
			$payment_form_id = $esCredito ? 2 : 1;

			// 2. OBTENER payment_method_id desde fac_tipo_formas_pagos.codigo
			$payment_method_id = $tipoCodigo ?: 'ZZZ'; // 'ZZZ' = Acuerdo mutuo (fallback)

			$item = [
				'payment_form_id' => $payment_form_id,
				'payment_method_id' => $payment_method_id,
			];

			// Solo enviar payment_due_date si es crédito
			if ($payment_form_id == 2) {
				// Usar la fecha de vencimiento de la venta, o la del pago, o calcular +30 días
				$dueDate = $this->factura->fecha_vencimiento ?? 
						date('Y-m-d', strtotime($this->factura->fecha_manual . ' + 30 days'));
				$item['payment_due_date'] = $dueDate;
			}

			$paymentForm[] = $item;
		}

		return $paymentForm;
	}

	protected function invoiceLines()
	{
		$invoiceLines = [];
		
		foreach ($this->detalles as $key => $detalle) {

		 	$line_extension_amount = $this->iva_inluido ?
				$detalle->subtotal :
				($detalle->costo * $detalle->cantidad) - $detalle->descuento_valor;

			$price_amount = $this->iva_inluido ? 
				number_format($detalle->subtotal / $detalle->cantidad, 2, '.', '') :
				(intval($detalle->iva_porcentaje)
					? number_format($detalle->costo * (1 + ($detalle->iva_porcentaje / 100)), 2, '.', '')
					: $detalle->costo);// Con IVA calculado

			$invoiceLines[] = [
				"unit_measure_id" => 642, // Unidad de medida que se maneja
				"invoiced_quantity" => $detalle->cantidad, // Cantidad de productos
				"line_extension_amount" => number_format($line_extension_amount, 2, '.', ''), // Total producto incluyento impuestos
				"free_of_charge_indicator" => false, // Indica si el producto es una muestra gratis
				// "allowance_charges" => $this->totalDescuento($detalle),
				"tax_totals" => $this->taxTotalsDetalle($detalle, [1, 5]),
				"description" => $detalle->producto->nombre, // Descripcion del producto
				"code" => $detalle->producto->codigo, // (SKU) Codigo del producto
				"type_item_identification_id" => 1, //
				"price_amount" => $price_amount, // Precio total del producto incluyendo impuestos
				"base_quantity" => 1 // unidad base
			];
		}
		return $invoiceLines;
	}

	public function taxTotals($taxs = [1, 5, 6])
	{
		//ESTRUCTURA A ENTREGAR
		$dataTaxTotals = $decoreTax = [
			"iva" => [],
			"reteIva" => [],
			"reteFuente" => [],
		];
		//AGREGAR DETALLE DE LOS ITEMS
		foreach ($this->detalles as $detalle) {
			foreach ($taxs as $tax) {

				switch ($tax) {
					case 1: //IVA
						if (!empty($dIva = $this->taxTotalsDetalle($detalle, [1]))) $dataTaxTotals['iva'][] = $dIva[0];
						break;
					case 5: // RETE IVA
						if (!empty($dRete = $this->taxTotalsDetalle($detalle, [5])) && intval($dRete[0]["tax_amount"])) $dataTaxTotals['reteIva'][] = $dRete[0];
						break;
					case 6: // RETE FUENTE
						if (!empty($dFuente = $this->taxTotalsDetalle($detalle, [6])) && intval($dFuente[0]["tax_amount"])) $dataTaxTotals['reteFuente'][] = $dFuente[0];
						break;
					default:
						break;
				}
			}
		}

		$taxTotals = [];
    
		// Procesar IVA (tax_id = 1)
		if (!empty($dataTaxTotals['iva'])) {
			$agrupados = $this->agruparPorPorcentaje($dataTaxTotals['iva']);
			$taxTotals[] = $this->buildTaxTotal(1, $agrupados);
		}
		
		// Procesar RETE IVA (tax_id = 5)
		if (!empty($dataTaxTotals['reteIva'])) {
			$agrupados = $this->agruparPorPorcentaje($dataTaxTotals['reteIva']);
			$taxTotals[] = $this->buildTaxTotal(5, $agrupados);
		}
		
		// Procesar RETE FUENTE (tax_id = 6)
		if (!empty($dataTaxTotals['reteFuente'])) {
			$agrupados = $this->agruparPorPorcentaje($dataTaxTotals['reteFuente']);
			$taxTotals[] = $this->buildTaxTotal(6, $agrupados);
		}
		
		return $taxTotals;
	}
	/**
	 * @param object $detalle
	 * @param array $data = 1: IVA, 5: RETE IVA, 6: RETE FUENTE
	 *
	 * @return array
	 */
	protected function taxTotalsDetalle($detalle, $data = [1, 5, 6])
	{
		$taxTotalsDetalle = [];
		$impuestos = $this->impuesto($detalle);

		foreach ($impuestos as $impuesto) {
			$existencia = $data ? in_array($impuesto['tax_id'], $data) : true;
			if ($existencia) {
				
				if (intval($impuesto['tax_amount']) || $impuesto['tax_id'] == 1) {
					$taxTotalsDetalle[] = $this->decoreTax($impuesto);
				}
			}
		}

		return $taxTotalsDetalle;
	}

	private function totalDescuento($detalle)
	{
		$descuento[] = [
			"charge_indicator" => false,
			"allowance_charge_reason" => $detalle->valor_descuento,
			"amount" => $detalle->valor_descuento,
			"base_amount" => $detalle->valor_bruto
		];
		return $descuento;
	}

	private function impuesto($detalle)
	{
		// Calcular base correcta según si IVA está incluido o no
		$base_iva = $detalle->subtotal;
		$base_retefuente = $detalle->subtotal;
		$base_reteiva = $detalle->iva_valor;

		$impuestos = [
			[
				"tax_id" => 1, //IVA
				"tax_amount" => $detalle->iva_valor,
				"percent" => $detalle->iva_porcentaje ?? "0.00",
				"taxable_amount" => number_format($base_iva, 2, '.', '')
			],
			[
				"tax_id" => 5, // RETE IVA
				"tax_amount" => "0.00",
				"tax_amount" => $detalle->valor_rete_iva ?? "0.00",
				"percent" => $detalle->porcentaje_rete_iva ?? "0.00",
				"taxable_amount" => number_format($base_reteiva, 2, '.', '')
			],
			[
				"tax_id" => 6, // RETE FUENTE
				"tax_amount" => $this->factura->total_rete_fuente,
				"percent" => $detalle->porcentaje_rete_fuente ?? "0.00",
				"taxable_amount" => number_format($base_retefuente, 2, '.', '')
			],
		];
		return $impuestos;
	}

	private function decoreTax($data)
	{
		return [
			"tax_id" => $data['tax_id'],
			"tax_amount" => $data['tax_amount'],
			"percent" => floatval($data['tax_amount']) > 0 ? $data['percent'] : '0.00',
			"taxable_amount" => number_format($data['taxable_amount'], 2, '.', '')
		];
	}

	/**
	 * Agrupa los impuestos por porcentaje y suma las bases y montos
	 * 
	 * @param array $items
	 * @return array
	 */
	private function agruparPorPorcentaje($items)
	{
		$agrupados = [];
		
		foreach ($items as $item) {
			$percent = $item['percent'];
			
			if (!isset($agrupados[$percent])) {
				$agrupados[$percent] = [
					'tax_id' => $item['tax_id'],
					'tax_amount' => 0,
					'percent' => $percent,
					'taxable_amount' => 0,
					'items' => []
				];
			}
			
			$agrupados[$percent]['tax_amount'] += floatval($item['tax_amount']);
			$agrupados[$percent]['taxable_amount'] += floatval($item['taxable_amount']);
			$agrupados[$percent]['items'][] = $item;
		}
		
		return $agrupados;
	}

	/**
	 * Construye el objeto tax_total para la DIAN
	 * 
	 * @param int $taxId
	 * @param array $agrupados
	 * @return array
	 */
	private function buildTaxTotal($taxId, $agrupados)
	{
		// Caso 1: un solo porcentaje → estructura plana (sin tax_subtotal)
		if (count($agrupados) === 1) {
			$grupo = reset($agrupados);
			return [
				'tax_id' => $taxId,
				'tax_amount' => round($grupo['tax_amount'], 2),
				'percent' => floatval($grupo['percent']),
				'taxable_amount' => round($grupo['taxable_amount'], 2)
			];
		}
		
		// Caso 2: múltiples porcentajes → usar tax_subtotal (sin percent/taxable_amount raíz)
		$totalTaxAmount = 0;
		$taxSubtotal = [];
		foreach ($agrupados as $percent => $grupo) {
			$totalTaxAmount += $grupo['tax_amount'];
			$taxSubtotal[] = [
				'tax_id' => $taxId,
				'tax_amount' => number_format(round($grupo['tax_amount'], 2), 2, '.', ''),
				'percent' => number_format(floatval($percent), 2, '.', ''),
				'taxable_amount' => number_format(round($grupo['taxable_amount'], 2), 2, '.', '')
			];
		}
		return [
			'tax_id' => $taxId,
			'tax_amount' => round($totalTaxAmount, 2),
			'tax_subtotal' => $taxSubtotal
		];
	}
}

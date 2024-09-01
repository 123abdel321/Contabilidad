<?php

namespace App\Helpers\FacturaElectronica;

use App\Models\Sistema\VariablesEntorno;
use Illuminate\Support\Facades\Http;

abstract class AbstractFESender
{
	protected $factura;
	protected $pagos;
	protected $cliente;
	protected $detalles;
	protected $url = 'http://fe.portafolioerp.com/api/ubl2.1';

	public function __construct($factura)
	{
		$factura->loadMissing($this->getRelationShips());
		$this->factura = $factura;
		$this->pagos = $factura->pagos;
		$this->cliente = $factura->cliente;
		$this->detalles = $factura->detalles;
	}

	public abstract function getExtraParams(): array;
	public abstract function getEndpoint(): string;
	public abstract function getRelationShips(): array;

	private function getConfigApiFe(): array
	{
		$entorno = VariablesEntorno::whereIn('nombre', ['token_key_fe', 'set_test_id_fe'])->get();
		$bearerToken = '';
		$setTestId = '';

		if (count($entorno)) {
			$bearerToken = $entorno->firstWhere('nombre', 'token_key_fe');
			$bearerToken = $bearerToken ? $bearerToken->valor	: '';

			$setTestId = $entorno->firstWhere('nombre', 'set_test_id_fe');
			$setTestId = $setTestId && $setTestId->valor ? '/' . $setTestId->valor	: '';
		}

		return [$bearerToken, $setTestId];
	}

	public function getUrl()
	{
		$enviroment = env('APP_ENV') == 'local' ? '/test' : '';

		return $this->url . $this->getEndpoint() . $enviroment;
	}

	public function send()
	{
		[$bearerToken, $setTestId] = $this->getConfigApiFe();
		$params = $this->getParams();
		$url = $this->getUrl() . $setTestId;

		$response = Http::withHeaders([
			'Content-Type' => 'application/json',
			'X-Requested-With' => 'XMLHttpRequest',
			'Authorization' => 'Bearer ' . $bearerToken
		])->post($url, $params);

		$data = (object) $response->json();
		info(json_encode($data));

		if ($response->status() >= 500 || (property_exists($data, 'response') && isset($data->response['trace']))) {
				return ["status" => 500, "message_object" => ["Error interno: http://facturaelectronica.begranda.com"]];
		}

		if ($response->status() >= 400) {
			if (property_exists($data, 'response') && isset($data->response['mensajesValidacion'])) {
					return ["status" => 422, "message_object" => $data->response['mensajesValidacion']['string']];
			}

			return ["status" => $response->status(), "mensaje" => $data->message, "message_object" => (array)$data->errors];
		}

		if (property_exists($data, 'status') && $data->status >= 400 && !isset($data->response['cufe'])) {
			$message = property_exists($data, 'message') ? $data->message : 'Error inesperado Dian.';

			if (isset($data->response['mensajesValidacion'])) {
				return ["status" => 422, "message_object" => $data->response['mensajesValidacion']['string']];
			}

			if (isset($data->response['object']) && isset($data->response['object']['envelope'])) {
				$message = $data->response['object']['Envelope']['Body']['Fault']['Reaseon']['Text']['_value'];
			}


			return ["status" => $data->status, "message_object" => [$message]];
		}

		if(property_exists($data, 'cuds')){
			return [
				"status" => 200,
				"cuds" => $data->cuds,
				"mensaje" => $data->message,
				"xml" => $data->invoicexml,
				"data" => $data
			];
		}

		return [
				"status" => $data->status,
				"cufe" => $data->response["cufe"],
				"mensaje" => $data->response["mensaje"],
				"xml" => $data->response["xml"]
		];
	}

	public function getParams(): array
	{
		$params = array(
			'number' => $this->factura->consecutivo, // Consecutive
			'prefix' => $this->factura->resolucion->prefijo, // 'prefix' => "SETP"
			'type_document_id' => CodigoDocumentoDianTypes::getIdTipoDocumentoDian(($this->factura->codigo_tipo_documento_dian)), // id tipo documento dian
			'date' => date_format(date_create($this->factura->fecha_manual), 'Y-m-d'),
			'time' => date_format(date_create($this->factura->created_at), 'H:i:s'),
			'allowance_charges' => [], // Cargos por subsidio
			'tax_totals' => $this->taxTotals([1, 5]), // Total impuestos
			'legal_monetary_totals' => [ // Legal monetary totals
				'line_extension_amount' => number_format($this->factura->subtotal, 2, '.', ''), // Total con Impuestos
				'tax_exclusive_amount' => number_format($this->factura->subtotal, 2, '.', ''), // Total sin impuestos pero con descuentos
				'tax_inclusive_amount' => $this->factura->total_factura, // Total con Impuestos
				'allowance_total_amount' => "0.00", // Descuentos nivel de factura
				'charge_total_amount' => "0.00", // Cargos
				'payable_amount' => $this->factura->total_factura, // Valor total a pagar
			],
			"holding_tax_totals" => $this->taxTotals([6]),
		);
		return array_merge($params, $this->getExtraParams());
	}

	protected function paymentForm()
	{
		$paymentForm = [];
		foreach ($this->pagos as $key => $pago) {
			$paymentForm[] = [
				'payment_form_id' => $pago->id_venta,
				'payment_method_id' => $pago->id_forma_pago,
				'payment_due_date' => date_format(date_create($pago->created_at), 'Y-m-d')
			];
		}
		return $paymentForm;
	}

	protected function invoiceLines()
	{
		$invoiceLines = [];
		
		foreach ($this->detalles as $key => $detalle) {
			$invoiceLines[] = [
				"unit_measure_id" => 642, // Unidad de medida que se maneja
				"invoiced_quantity" => $detalle->cantidad, // Cantidad de productos
				"line_extension_amount" => $detalle->subtotal, // Total producto incluyento impuestos
				"free_of_charge_indicator" => false, // Indica si el producto es una muestra gratis
				"allowance_charges" => $this->totalDescuento($detalle),
				"tax_totals" => $this->taxTotalsDetalle($detalle, [1, 5]),
				"description" => $detalle->producto->nombre, // Descripcion del producto
				"code" => $detalle->producto->codigo, // (SKU) Codigo del producto
				"type_item_identification_id" => 1, //
				"price_amount" => $detalle->total, // Precio total del producto incluyendo impuestos
				"base_quantity" => $detalle->cantidad // unidad base
			];
		}
		return $invoiceLines;
	}

	public function taxTotals($taxs = [1, 5, 6])
	{
		$taxTotals = [];
		$dataTaxTotals = $decoreTax = [
			"iva" => [],
			"reteIva" => [],
			"reteFuente" => [],
		];
		foreach ($this->detalles as $detalle) {
			foreach ($taxs as $tax) {
				switch ($tax) {
					case 1: //IVA
						if (!empty($dIva = $this->taxTotalsDetalle($detalle, [1]))) $dataTaxTotals['iva'][] = $dIva[0];
						break;
					case 5: // RETE IVA
						if (!empty($dRete = $this->taxTotalsDetalle($detalle, [5]))) $dataTaxTotals['reteIva'][] = $dRete[0];
						break;
					case 6: // RETE FUENTE
						if (!empty($dFuente = $this->taxTotalsDetalle($detalle, [6]))) $dataTaxTotals['reteFuente'][] = $dFuente[0];
						break;
					default:
						break;
				}
			}
		}
		
		foreach ($dataTaxTotals as $key => $impuestos) {
			$data = null;
			foreach ($impuestos as $ke => $impuesto) {
				if (!$data) { // ENTRA POR PRIMERA VEZ
					$data = $this->decoreTax($impuesto);
					$data["tax_subtotal"] = [];
				} else if ($data["percent"] == $impuesto['percent']) { // ENCUENTRA EL MISMO %
					$data["tax_amount"] =  number_format($data["tax_amount"] + $impuesto['tax_amount'], 2, '.', '');
					$data["taxable_amount"] = number_format($data["taxable_amount"] + $impuesto['taxable_amount'], 2, '.', '');
				} else if (!$data["tax_subtotal"]) { // SI SON DIFERENTES % Y NO SE HA CREADO EL []SUBTOTAL
					$data["tax_subtotal"][] = $this->decoreTax($data);
					$data["percent"] = 0;
					$data["tax_subtotal"][] = $this->decoreTax($impuesto);
				} else { // SI SON DIFERENTES % Y YA SE CREO EL []SUBTOTAL
					$exists = false;
					foreach ($data["tax_subtotal"] as $k => $tax_subtotal) { //BUSCAR [KEY] CORRESPONDIENTE AL %
						if ($tax_subtotal["percent"] == $impuesto['percent']) {
							$exists = $k;
						}
					}
					if ($exists == 0 || $exists) { //SUMAR EL %
						$sumTax = $data["tax_subtotal"][$exists]["taxable_amount"] + $impuesto['taxable_amount'];
						$data["tax_subtotal"][$exists]["tax_amount"] = number_format($data["tax_subtotal"][$exists]["tax_amount"] + $impuesto['tax_amount'], 2, '.', '');
						$data["tax_subtotal"][$exists]["taxable_amount"] = number_format($sumTax, 2, '.', '');
					} else {
						$data["tax_subtotal"][] = $this->decoreTax($impuesto);
					}
				}
			}
			if ($data && $data['tax_subtotal'] && count($data['tax_subtotal']) > 0) { // SI TIENE SUBTOTAL VOLVER A CALCULAR
				$data["percent"] = 0;
				$data["tax_amount"] = 0;
				$data["taxable_amount"] = 0;
				foreach ($data['tax_subtotal'] as $k => $v) {
					$data["tax_amount"] += $v["tax_amount"];
					$data["taxable_amount"] += $v["taxable_amount"];
				}
			}
			if ($data) $decoreTax[$key] = $data;
		}
		foreach ($decoreTax as $key => $value) {
			if (count($value)) {
				$taxTotals[] =  $value;
			}
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
				$taxTotalsDetalle[] = $this->decoreTax($impuesto);
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
		$impuestos = [
			[
				"tax_id" => 1, //IVA
				"tax_amount" => $detalle->iva_valor,
				"percent" => $detalle->iva_porcentaje,
				"taxable_amount" => number_format($detalle->subtotal, 2, '.', '')
			],
			[
				"tax_id" => 5, // RETE IVA
				"tax_amount" => "0.00",
				// "tax_amount" => $detalle->valor_rete_iva,
				"percent" => "0.00",
				// "percent" => $detalle->porcentaje_rete_iva,
				"taxable_amount" => number_format($detalle->subtotal, 2, '.', '')
			],
			[
				"tax_id" => 6, // RETE FUENTE
				"tax_amount" => $this->factura->total_rete_fuente,
				"percent" => $detalle->porcentaje_rete_fuente,
				"taxable_amount" => number_format($detalle->subtotal, 2, '.', '')
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
}

<?php

namespace App\Helpers\NominaElectronica;

class NominaElectronicaSender extends AbstractNESender
{
	private $endpoint = '/payroll';

	public function getEndpoint(): string
	{
		return $this->url . $this->endpoint;
	}

	public function getExtraParams(): array
	{
		return [
			'type_document_id' => 9,
			'prefix' => 'BGNI',
			'notes' => 'ENVIO DE NOMINA ELECTRONICA INDIVIDUAL',
		];
	}

}

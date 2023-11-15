<?php

namespace App\Helpers\FacturaElectronica;

use Illuminate\Support\Arr;

class CodigoDocumentoDianTypes
{
	const VENTA_NACIONAL = '01';
	const VENTA_EXPORTACION = '02';
	const FACTURA_CONTINGENCIA = '03';
	const NOMINA_INDIVIDUAL = '07';
	const ZIP = '';
	const NOTA_CREDITO = '91';
	const NOTA_DEBITO = '92';
	const DOCUMENTO_SOPORTE = '11';

	const ID_TIPOS_DOCUMENTO_DIAN = [
		self::VENTA_NACIONAL => 1,
		self::VENTA_EXPORTACION => 2,
		self::VENTA_EXPORTACION => 3,
		self::NOTA_CREDITO => 4,
		self::NOTA_DEBITO => 5,
		self::ZIP => 6,
		self::NOMINA_INDIVIDUAL => 1,
		self::DOCUMENTO_SOPORTE => 11
	];

	/**
	 * Retora un array asociativo donde la key es el id document type
	 * y el value es el codigo tipo documento dian
	 *
	 * @return array
	 */
	static function getTipoDocumentosDian(): array
	{
		return array_flip(self::ID_TIPOS_DOCUMENTO_DIAN);
	}

/**
 * @param string $codigoTipoDocumentoDian
 *
 * Devuelve el id document type pasando por parámetro el código tipo deocumento dian
 *
 * @return int
 */
	static function getIdTipoDocumentoDian(string $codigoTipoDocumentoDian): int
	{
		return Arr::get(self::ID_TIPOS_DOCUMENTO_DIAN, $codigoTipoDocumentoDian);
	}
}

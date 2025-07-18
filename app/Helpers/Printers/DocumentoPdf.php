<?php

namespace App\Helpers\Printers;

use App\Helpers\Extracto;
use Illuminate\Support\Carbon;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Empresas\Ciudades;
use App\Models\Sistema\Nits;
use App\Models\Sistema\FacDocumentos;

class DocumentoPdf extends AbstractPrinterPdf
{
	public $factura;
	public $empresa;

	public function __construct(Empresa $empresa)
	{
		parent::__construct($empresa);

		copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);
		
		$this->empresa = $empresa;
	}

    public function view()
	{
		return 'pdf.facturacion.documentos';
	}

    public function name()
	{
		return 'documento_'.uniqid();
	}

    public function paper()
	{
		return '';
	}

	public function formatPaper()
	{
		// if ($this->tipoEmpresion == 1) return [0, 0, 396, 612];
		return 'A4';
	}

    public function data()
	{
		$this->factura->load([
			'comprobante',
			'documentos',
			'documentos.nit',
			'documentos.cuenta',
			'documentos.comprobante',
			'documentos.centro_costos',
		]);

		$nit = NULL;
		$documentos = [];
		$observacion = NULL;
		$totalFactura = 0;
		$calcularTotal = false;

		if($this->factura->comprobante && $this->factura->comprobante->tipo_comprobante != 4) {
			$calcularTotal = true;
		}

		$nombre_usuario = 'PROVEEDOR';

		if ($this->factura->comprobante && $this->factura->comprobante->tipo_comprobante == 0 || $this->factura->comprobante->tipo_comprobante == 3) {
			$nombre_usuario = 'CLIENTE';
		}

		foreach ($this->factura->documentos as $documento) {
			//AGREGAR SALDO A CUENTAS CON DOCUMENTO DE REFENCIA
			if($documento->documento_referencia) {
				
				$extracto = (new Extracto(
					$documento->id_nit,
					null,
					$documento->documento_referencia
				))->actual()->first();
				if(!$nit) {
					$cuidad = '';
					if($extracto->id_ciudad) {
						$cuidad = Ciudades::whereId($extracto->id_ciudad)->first();
						
						if($cuidad) $cuidad = $cuidad->nombre;
					}

					$nit = (object)[
						'nombre_nit' => $extracto->nombre_nit,
						'telefono' =>  $extracto->telefono_1,
						'email' => $extracto->email,
						'direccion' => $extracto->direccion,
						'tipo_documento' => $extracto->tipo_documento,
						'numero_documento' => $extracto->numero_documento,
						"ciudad" => $cuidad,
					];
				}

				$documento->saldo = $extracto->saldo;
			}
			//TOMAR PRIMERA OBSERVACIÓN
			if($documento->concepto && !$observacion) {
				$observacion = $documento->concepto;
			}
			//TOMAR PRIMER NIT
			if($documento->id_nit && !$nit) {
				$getNit = Nits::whereId($documento->id_nit)->with('ciudad')->first();
				// dd($getNit->ciudad->nombre);
				if($getNit){ 
					$nit = (object)[
						'nombre_nit' => $getNit->nombre_completo,
						'telefono' =>  $getNit->telefono_1,
						'email' => $getNit->email,
						'direccion' => $getNit->direccion,
						'tipo_documento' => $getNit->tipo_documento->nombre,
						'numero_documento' => $getNit->numero_documento,
						"ciudad" => $getNit->ciudad ? $getNit->ciudad->nombre_completo : '',
					];
				}
			}
			//CALCULAR TOTAL INGRESO/EGRESO
			if($calcularTotal && mb_substr($documento->cuenta->cuenta, 0, 2) == '11'){
				$totalFactura+= $documento->cuenta->naturaleza_cuenta == 1 ? $documento->debito : $documento->credito;
			}
			//CALCULAR TOTAL VENTAS
			if($calcularTotal && mb_substr($documento->cuenta->cuenta, 0, 2) == '13'){
				$totalFactura+= $documento->cuenta->naturaleza_cuenta == 1 ? $documento->debito : $documento->credito;
			}
			//CALCULAR TOTAL COMPRAS
			if($calcularTotal && mb_substr($documento->cuenta->cuenta, 0, 2) == '22'){
				$totalFactura+= $documento->cuenta->naturaleza_cuenta == 1 ? $documento->debito : $documento->credito;
			}

			$documentos[] = $documento;
		}

		return [
			'empresa' => $this->empresa,
			'nit' => $nit,
			'factura' => $this->factura,
			'documentos' => $documentos,
			'observacion' => $observacion,
			'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
			'total_factura' => number_format($totalFactura),
			'nombre_usuario' => $nombre_usuario,
		];
	}

}

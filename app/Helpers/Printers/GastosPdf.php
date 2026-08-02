<?php

namespace App\Helpers\Printers;

use Illuminate\Support\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\ConGastos;
use App\Models\Sistema\PlanCuentas;

class GastosPdf extends AbstractPrinterPdf
{
    public $gasto;
	public $empresa;
    public $claveUrl;
	public $tipoEmpresion;

	use BegDocumentHelpersTrait;

    public function __construct(Empresa $empresa, ConGastos $gasto, string $claveUrl)
	{
		parent::__construct($empresa);

		copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

		$this->gasto = $gasto;
		$this->empresa = $empresa;
		$this->claveUrl = $claveUrl;
		$this->tipoEmpresion = $this->gasto->comprobante->tipo_impresion;
	}

    public function view()
	{
		return 'pdf.facturacion.gastos';
	}

    public function name()
	{
		return 'gasto_'.uniqid();
	}

    public function paper()
	{
		if ($this->tipoEmpresion == 1) return 'landscape';
		if ($this->tipoEmpresion == 2) return 'portrait';

		return '';
	}

	public function formatPaper()
	{
		// if ($this->tipoEmpresion == 1) return [0, 0, 396, 612];
		return 'A4';
	}

    public function data()
    {
        $this->gasto->load([
			'cecos',
            'proveedor',
			'comprobante',
            'detalles.concepto',
			'pagos.forma_pago'
        ]);

		$getProveedor = Nits::whereId($this->gasto->id_proveedor)->with('ciudad')->first();
		$proveedor = null;

		if($getProveedor){ 
			$proveedor = (object)[
				'nombre_nit' => $getProveedor->nombre_completo,
				'telefono' =>  $getProveedor->telefono_1,
				'email' => $getProveedor->email,
				'direccion' => $getProveedor->direccion,
				'tipo_documento' => $getProveedor->tipo_documento->nombre,
				'numero_documento' => $getProveedor->numero_documento,
				"ciudad" => $getProveedor->ciudad ? $getProveedor->ciudad->nombre_completo : '',
			];
		}

		$baseUrl = config('app.url');
		$urlValidarArchivo = "{$baseUrl}/documentos-generales-pdf?code={$this->claveUrl}";
		
		$svg = QrCode::format('svg')->size(300)->generate($urlValidarArchivo);
        $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($svg);

		$cliente = (object)[
			'titulo' => "PROVEEDOR",
			'nombre_cliente' => $proveedor->nombre_nit,
			'datos_adicionales' => [
				(object)[
					'icono' => 'building',
					'titulo' => $proveedor->tipo_documento == 'Cédula de ciudadanía' ? 'Cédula' : $proveedor->tipo_documento,
					'valor' => $proveedor->numero_documento
				],
				(object)[
					'icono' => 'location',
					'titulo' => 'Dirección',
					'valor' => $proveedor->direccion
				],
				// (object)[
				// 	'icono' => 'city',
				// 	'titulo' => 'Ciudad',
				// 	'valor' => $proveedor->ciudad
				// ],
				(object)[
					'icono' => 'phone',
					'titulo' => 'Teléfono',
					'valor' => $proveedor->telefono
				],
				(object)[
					'icono' => 'mail',
					'titulo' => 'Email',
					'valor' => $proveedor->email
				],
			]
		];

		$informacionPdf = (object)[
			'titulo' => "INFORMACIÓN DEL GASTO",
			'datos_adicionales' => [
				(object)[
					'icono' => 'box',
					'titulo' => 'Centro de costos',
					'valor' => "{$this->gasto->cecos->codigo} - {$this->gasto->cecos->nombre}"
				],
				(object)[
					'icono' => 'file',
					'titulo' => 'Documento referencia',
					'valor' => $this->gasto->documento_referencia
				],
				(object)[
					'icono' => 'ticket',
					'titulo' => 'Comprobante',
					'valor' => "{$this->gasto->comprobante->codigo} - {$this->gasto->comprobante->nombre}"
				],
				(object)[
					'icono' => 'user',
					'titulo' => 'Usuario',
					'valor' => request()->user() ? request()->user()->username : 'Portafolio ERP'
				],
				(object)[
					'icono' => 'tag',
					'titulo' => 'Tipo de gasto',
					'valor' => 'Gasto operacional'
				],
			]
		];

        return [
			'empresa' => $this->empresa,
			'cliente' => $cliente,
			'informacion_pdf' => $informacionPdf,
			'proveedor' => $proveedor,
			'gasto' => $this->gasto,
			'detalles' => $this->gasto->detalles,
			'pagos' => $this->gasto->pagos,
			'qrCode' => $qrCodeBase64,
			'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
			'monto_letras' => $this->numeroALetras($this->gasto->total_gasto),
			'usuario' => request()->user() ? request()->user()->username : 'Portafolio ERP'
		];
    }
}
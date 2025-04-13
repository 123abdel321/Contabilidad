<?php

namespace App\Helpers\Printers;

use Illuminate\Support\Carbon;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\ConGastos;
use App\Models\Sistema\PlanCuentas;

class GastosPdf extends AbstractPrinterPdf
{
    public $gasto;
	public $empresa;
	public $tipoEmpresion;

    public function __construct(Empresa $empresa, ConGastos $gasto)
	{
		parent::__construct($empresa);

		copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

		$this->gasto = $gasto;
		$this->empresa = $empresa;
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
		
        return [
			'empresa' => $this->empresa,
			'proveedor' => $proveedor,
			'gasto' => $this->gasto,
			'detalles' => $this->gasto->detalles,
			'pagos' => $this->gasto->pagos,
			'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
			'usuario' => request()->user() ? request()->user()->username : 'Portafolio ERP'
		];
    }
}
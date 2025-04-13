<?php

namespace App\Helpers\Printers;

use Illuminate\Support\Carbon;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\ConPagos;
use App\Models\Sistema\PlanCuentas;

class PagosPdf extends AbstractPrinterPdf
{
    public $pago;
	public $empresa;
	public $tipoEmpresion;

    public function __construct(Empresa $empresa, ConPagos $pago)
	{
		parent::__construct($empresa);

		copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

		$this->pago = $pago;
		$this->empresa = $empresa;
		$this->tipoEmpresion = $this->pago->comprobante->tipo_impresion;
	}

    public function view()
	{
		return 'pdf.facturacion.pagos';
	}

    public function name()
	{
		return 'pago_'.uniqid();
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
        $this->pago->load([
            'nit',
            'detalles.cuenta',
			'pagos.forma_pago'
        ]);

		$getNit = Nits::whereId($this->pago->id_nit)->with('ciudad')->first();
		$nit = null;

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
		
        return [
			'empresa' => $this->empresa,
			'nit' => $nit,
			'pago' => $this->pago,
			'detalles' => $this->pago->detalles,
			'pagos' => $this->pago->pagos,
			'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
			'usuario' => request()->user() ? request()->user()->username : 'MaximoPH'
		];
    }
}
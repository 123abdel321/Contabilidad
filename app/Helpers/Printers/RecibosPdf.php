<?php

namespace App\Helpers\Printers;

use App\Helpers\Extracto;
use Illuminate\Support\Carbon;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\ConRecibos;
use App\Models\Sistema\PlanCuentas;

class RecibosPdf extends AbstractPrinterPdf
{
    public $recibo;
	public $empresa;
	public $tipoEmpresion;

    public function __construct(Empresa $empresa, ConRecibos $recibo)
	{
		parent::__construct($empresa);

		copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

		$this->recibo = $recibo;
		$this->empresa = $empresa;
		$this->tipoEmpresion = $this->recibo->comprobante->tipo_impresion;
	}

    public function view()
	{
		return 'pdf.facturacion.recibos';
	}

    public function name()
	{
		return 'recibo_'.uniqid();
	}

    public function paper()
	{
		if ($this->tipoEmpresion == 1) return 'landscape';
		if ($this->tipoEmpresion == 2) return 'portrait';

		return '';
	}

    public function data()
    {
        $this->recibo->load([
            'nit',
            'detalles.cuenta',
			'pagos.forma_pago',
			'documentos'
        ]);

		$nit = null;
		$saldo = 0;
		$saldoAnterior = 0;
		$getNit = Nits::whereId($this->recibo->id_nit)->with('ciudad')->first();

		if($getNit){ 
			$nit = (object)[
				'nombre_nit' => $getNit->nombre_completo,
				'telefono' =>  $getNit->telefono_1,
				'email' => $getNit->email,
				'direccion' => $getNit->direccion,
				'tipo_documento' => $getNit->tipo_documento->nombre,
				'numero_documento' => $getNit->numero_documento,
				'ciudad' => $getNit->ciudad ? $getNit->ciudad->nombre_completo : '',
				'apartamentos' => $getNit->apartamentos ? $getNit->apartamentos : ''
			];
		}
		
		$extractos = (new Extracto(
			$getNit->id,
			3
		))->actual()->get();

		$fechaAnterior = Carbon::parse($this->recibo->fecha_manual); 

		$extractoAnterior = (new Extracto(
			$getNit->id,
			3,
			null,
			$fechaAnterior
		))->actual()->get();

		if (count($extractos)) {
			foreach ($extractos as $extracto) {
				$saldo+= floatval($extracto->saldo);
			}
		}

		if (count($extractoAnterior)) {
			foreach ($extractoAnterior as $extracto) {
				$saldoAnterior+= floatval($extracto->saldo);
			}
		}

        return [
			'empresa' => $this->empresa,
			'nit' => $nit,
			'recibo' => $this->recibo,
			'detalles' => $this->recibo->detalles,
			'pagos' => $this->recibo->pagos,
			'saldo' => $saldo,
			'saldoAnterior' => $saldoAnterior + $this->recibo->total_abono,
			'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
			'usuario' => request()->user() ? request()->user()->username : 'MaximoPH'
		];
    }
}
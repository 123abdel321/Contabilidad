<?php

namespace App\Http\Controllers\Configuracion;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\Impuestos;
use App\Models\Sistema\TipoImpuestos;
use App\Models\Sistema\VariablesEntorno;

class EntornoController extends Controller
{
    protected $messages = null;

    public function __construct()
	{
		$this->messages = [
            'required' => 'El campo :attribute es requerido.',
            'exists' => 'El :attribute es inválido.',
            'numeric' => 'El campo :attribute debe ser un valor numérico.',
            'string' => 'El campo :attribute debe ser texto',
            'array' => 'El campo :attribute debe ser un arreglo.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
        ];
	}

    public function index(Request $request)
    {
        $data = [
            'variables_entorno' => VariablesEntorno::get()
        ];
        
        return view('pages.configuracion.entorno.entorno-view', $data);
    }

    public function updateEntorno(Request $request)
    {
        try {
            DB::connection('sam')->beginTransaction();

            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

            copyDBConnection($empresa->servidor ?: 'sam', 'sam');
            setDBInConnection('sam', $empresa->token_db);

            $variablesEntorno = [
                'iva_incluido',
                'capturar_documento_descuadrado',
                'valor_uvt'
            ];

            foreach ($variablesEntorno as $variable) {
                VariablesEntorno::where('nombre', $variable)->update([
                    'valor' => $request->get($variable)
                ]);

                if ($variable == 'valor_uvt') {
                    
                    $retenciones = Impuestos::where('total_uvt', '>', 0)->get();

                    foreach ($retenciones as $retencion) {
                        $retencion->base = 0;
                        if ($request->get('valor_uvt')) {
                            $retencion->base = $retencion->total_uvt * $request->get($variable);
                        }
                        $retencion->save();
                    }
                }
            }

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Entorno actualizado con exito!'
            ]);

        } catch (Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }
}
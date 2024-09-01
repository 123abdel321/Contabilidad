<?php

namespace App\Http\Controllers\Importador;

use DB;
use Illuminate\Http\Request;
use App\Imports\ImportNits;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\NitsImport;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\TipoDocumentos;

class NitsImportadorController extends Controller
{
    protected $messages = null;
    protected $rowErrors = 0;

    public function __construct()
	{
		$this->messages = [
            'required' => 'El campo :attribute es requerido.',
        ];
	}

	public function index ()
    {
        return view('pages.importador.nits.nits-view');
    }

    public function importar (Request $request)
    {
        $rules = [
            'file_import_nits' => 'required|mimes:xlsx'
        ];
        
        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file_import_nits');

            NitsImport::truncate();

            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

            $import = new ImportNits($empresa->razon_social);
            $import->import($file);

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Cédulas nits cargadas con exito!'
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

            return response()->json([
                'success'=>	false,
                'data' => $e->failures(),
                'message'=> 'Error al cargar cédulas nits'
            ]);
        }
    }

    public function generate (Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

        $nitsImportados = NitsImport::orderBy($columnName,$columnSortOrder)
            ->where('numero_documento', 'like', '%' .$searchValue . '%')
            ->orWhere('primer_nombre', 'like', '%' .$searchValue . '%')
            ->orWhere('otros_nombres', 'like', '%' .$searchValue . '%')
            ->orWhere('primer_apellido', 'like', '%' .$searchValue . '%')
            ->orWhere('segundo_apellido', 'like', '%' .$searchValue . '%')
            ->orWhere('razon_social', 'like', '%' .$searchValue . '%')
            ->orWhere('direccion', 'like', '%' .$searchValue . '%')
            ->orWhere('email', 'like', '%' .$searchValue . '%')
            ->orWhere('telefono_1', 'like', '%' .$searchValue . '%')
            ->orWhere('plazo', 'like', '%' .$searchValue . '%')
            ->orWhere('cupo', 'like', '%' .$searchValue . '%')
            ->orWhere('observaciones', 'like', '%' .$searchValue . '%');
            
        $nitsImportadosTotals = $nitsImportados->get();

        $cuentasPaginate = $nitsImportados->skip($start)
            ->take($rowperpage);

        $dataNistValidar = $cuentasPaginate->get();

        if (count($dataNistValidar)) {
            foreach ($dataNistValidar as $dataNit) {
                $this->rowErrors = 0;
                $dataNit->id = $dataNit->id + 1;
                $dataNit->erroes = $this->getErrores($dataNit);
                $dataNit->tipo_documento = $this->tipoDocumento($dataNit->tipo_documento);
                $dataNit->total_erroes = $this->rowErrors;
            }
        }

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $nitsImportadosTotals->count(),
            'iTotalDisplayRecords' => $nitsImportadosTotals->count(),
            'data' => $dataNistValidar,
            'perPage' => $rowperpage,
            'message'=> 'Nits excel generado con exito!'
        ]);
    }
    
    public function exportar (Request $request)
    {
        return response()->json([
            'success'=>	true,
            'url' => 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/import/importador_nits.xlsx',
            'message'=> 'Url generada con exito'
        ]);
        
    }

    public function actualizar (Request $request)
    {
        $nitsImportados = NitsImport::get();
        
        try {
            DB::connection('sam')->beginTransaction();

            if (count($nitsImportados)) {
                
                foreach ($nitsImportados as $nit) {

                    $idTipoDocumento = TipoDocumentos::where('codigo', $nit->tipo_documento)->first();
                    $this->rowErrors = 0;
                    $errores = $this->getErrores($nit);

                    if ($this->rowErrors) {
                        DB::connection('sam')->rollback();
                        return response()->json([
                            "success"=>false,
                            'data' => $errores,
                            "message"=>'Archivo con errores'
                        ], 422);
                    }

                    Nits::create([
                        'id_tipo_documento' => $idTipoDocumento->id,
                        'id_vendedor' => $nit->id_vendedor,
                        'numero_documento' => $nit->numero_documento,
                        'tipo_contribuyente' => $idTipoDocumento->id == 6 ? 1 : 2,
                        'primer_apellido' => $nit->primer_apellido,
                        'segundo_apellido' => $nit->segundo_apellido,
                        'primer_nombre' => $nit->primer_nombre,
                        'otros_nombres' => $nit->otros_nombres,
                        'razon_social' => $nit->razon_social,
                        'direccion' => $nit->direccion,
                        'email' => $nit->email,
                        'telefono_1' => $nit->telefono_1,
                        'observaciones' => $nit->observaciones,
                        'email_1' => $nit->email_1,
                        'email_2' => $nit->email_2,
                        'plazo' => $nit->plazo,
                        'cupo' => $nit->cupo,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id,
                    ]);
                }

                NitsImport::whereNotNull('id')->delete();
    
                DB::connection('sam')->commit();
            }

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Productos precios actualizados con exito!'
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

    private function getErrores($dataNit)
    {
        $errores = '';
        //VALIDAR TIPO DE DOCUMENTO
        if ($dataNit->tipo_documento) {
            $existe = TipoDocumentos::where('codigo', $dataNit->tipo_documento);
            if (!$existe->count()) {
                $this->rowErrors+= 1;
                $errores.='El código tipo de documento no existe <br/>';
            }
        } else {
            $this->rowErrors+= 1;
            $errores.='El tipo de documento es requerido <br/>';
        }
        //VALIDAR NUMERO DOCUMENTO
        if ($dataNit->numero_documento) {
            $existe = Nits::where('numero_documento', $dataNit->numero_documento);
            if ($existe->count()) {
                $this->rowErrors+= 1;
                $errores.='El numero de documento: '.$dataNit->numero_documento.' debe ser unico <br/>';
            }
        } else {
            $this->rowErrors+= 1;
            $errores.='El numero de documento es requerido <br/>';
        }
        //VALIDAR EMAIL
        if ($dataNit->email) {
            $existe = Nits::where('email', $dataNit->email);
            if ($existe->count()) {
                $this->rowErrors+= 1;
                $errores.='El email: '.$dataNit->email.' debe ser unico <br/>';
            }
        } else {
            $this->rowErrors+= 1;
            $errores.='El email es requerido <br/>';
        }
        //VALIDAR NOMBRES
        if (!$dataNit->razon_social && !$dataNit->primer_nombre) {
            $this->rowErrors+= 1;
            $errores.='La razon social o el primero nombre son requerido <br/>';
        }

        return $errores;
    }

    private function tipoDocumento($tipoDocumento)
    {
        if ($tipoDocumento) {
            $tipoDocumento = TipoDocumentos::where('codigo', $tipoDocumento)->first();
            return $tipoDocumento->codigo. ' - ' .$tipoDocumento->nombre;
        }
        return $tipoDocumento;
    }

}
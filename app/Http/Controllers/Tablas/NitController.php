<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\TipoDocumentos;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Empresas\ResponsabilidadesTributarias;


class NitController extends Controller
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

    public function index ()
    {
        $responsabilidades = ResponsabilidadesTributarias::get();

        $data = [
            'responsabilidades' => $responsabilidades
        ];

        return view('pages.tablas.nits.nits-view', $data);
    }

    public function generate (Request $request)
    {
        try {
            if ($request->get("length")) {
                $draw = $request->get('draw');
                $start = $request->get("start");
                $rowperpage = 15; // Rows display per page
    
                $columnIndex_arr = $request->get('order');
                $columnName_arr = $request->get('columns');
                $order_arr = $request->get('order');
                $search_arr = $request->get('search');
                $columnIndex = null;
                $columnName = null;
                $columnSortOrder = null;
                $searchValue = null;

                if (isset($columnIndex_arr)) $columnIndex = $columnIndex_arr[0]['column'];
                if (isset($columnName_arr)) $columnName = $columnName_arr[$columnIndex]['data'];
                if (isset($order_arr)) $columnSortOrder = $order_arr[0]['dir'];
                if (isset($search_arr)) $searchValue = $search_arr['value'];
    
                $nits = Nits::with('tipo_documento', 'ciudad', 'vendedor.nit', 'actividad_economica')
                    ->select(
                        '*',
                        DB::raw("DATE_FORMAT(nits.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                        DB::raw("DATE_FORMAT(nits.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                        'nits.created_by',
                        'nits.updated_by'
                    );

                if ($columnIndex == '1') {
                    $nits->orderBy('razon_social',$columnSortOrder)
                        ->orderBy('primer_apellido',$columnSortOrder)
                        ->orderBy('otros_nombres',$columnSortOrder)
                        ->orderBy('primer_apellido',$columnSortOrder)
                        ->orderBy('segundo_apellido',$columnSortOrder);

                } else if ($columnIndex == '6') {
                    // $nits->orderBy('ciudad.nombre_completo');
                } else if ($columnIndex == '8') {

                } else if ($columnIndex == '9') {

                } else if ($columnIndex == '10') {
                    $nits->orderBy('created_at',$columnSortOrder);
                } else if ($columnIndex == '11') {
                    $nits->orderBy('updated_at',$columnSortOrder);
                } else if ($columnName) {
                    $nits->orderBy($columnName,$columnSortOrder);
                }
                
                if($searchValue) {
                    $nits->where('numero_documento', 'like', '%' .$searchValue . '%')
                        ->orWhere('primer_apellido', 'like', '%' .$searchValue . '%')
                        ->orWhere('segundo_apellido', 'like', '%' .$searchValue . '%')
                        ->orWhere('primer_nombre', 'like', '%' .$searchValue . '%')
                        ->orWhere('otros_nombres', 'like', '%' .$searchValue . '%')
                        ->orWhere('email', 'like', '%' .$searchValue . '%')
                        ->orWhere('telefono_1', 'like', '%' .$searchValue . '%')
                        ->orWhere('razon_social', 'like', '%' .$searchValue . '%')
                        ->orWhereHas('tipo_documento', function ($query) use($searchValue) {
                            $query->where('nombre', 'like', '%' .$searchValue . '%');
                        });
                }

                $totalNits = $nits->count();
                
                $nitsPaginate = $nits->skip($start)
                    ->take($rowperpage);
    
                return response()->json([
                    'success'=>	true,
                    'draw' => $draw,
                    'iTotalRecords' => $totalNits,
                    'iTotalDisplayRecords' => $totalNits,
                    'data' => $nitsPaginate->get(),
                    'perPage' => $rowperpage,
                    'message'=> 'Nits generados con exito!'
                ]);

            } else {
                $nits = Nits::with('tipo_documento', 'ciudad', 'vendedor.nit')->whereNotNull('id');

                if ($request->get("id")) {
                    $nits->where('id', $request->get("id"));
                }

                if ($request->get("numero_documento")) {
                    $nits->where('numero_documento', $request->get("numero_documento"));
                }

                return response()->json([
                    'success'=>	true,
                    'data' => $nits->get(),
                    'message'=> 'Nits generados con exito!'
                ]);
            }
        } catch (Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function create (Request $request)
    {
        $rules = [
			'id_tipo_documento' => 'required|exists:sam.tipos_documentos,id',
			'id_ciudad' => 'nullable|exists:clientes.ciudades,id',
			'id_vendedor' => 'nullable|exists:sam.fac_vendedores,id',
            'observaciones' => 'nullable|string',
			'id_actividad_econo' => 'nullable|exists:sam.actividades_economicas,id',
			'numero_documento' => 'required|unique:sam.nits,numero_documento|max:30',
			'digito_verificacion' => "nullable|between:0,9|numeric",
			// 'tipo_contribuyente' => 'required|in:1,2',
			'primer_apellido' => 'nullable|string|max:60',
			'segundo_apellido' => 'nullable|string|max:60',
			'primer_nombre' => 'nullable|string|max:60|',
			'otros_nombres' => 'nullable|string|max:60',
			// 'razon_social' => 'nullable|string|max:120|required_if:tipo_contribuyente,'.Nits::TIPO_CONTRIBUYENTE_PERSONA_JURIDICA, // Campo requerido si el tipo contribuyente es persona jurídica (id: 1)
			'razon_social' => 'nullable|string|max:60',
			'nombre_comercial' => 'nullable|string|max:120',
			'direccion' => 'nullable|min:3|max:100',
			'email' => 'nullable|email|max:250',
			'email_1' => 'nullable|email|max:250',
			'email_2' => 'nullable|email|max:250',
			'email_recepcion_factura_electronica' => 'nullable|email|max:60',
			'telefono_1' => 'nullable|numeric|digits_between:1,30',
			'telefono_2' => 'nullable|numeric|digits_between:1,30',
			'tipo_cuenta_banco' => 'nullable|in:0,1',
			'cuenta_bancaria' => 'nullable|string|max:20',
			'plazo' => 'nullable|numeric|min:0|digits_between:1,3',
			'cupo' => 'nullable|numeric|min:0|digits_between:1,13',
			'descuento' => 'nullable|numeric|min:0|max:100',
			'no_calcular_iva' => 'nullable|boolean',
            'porcentaje_aiu' => 'nullable|numeric',
			'inactivar' => 'nullable',
            // 'declarante' => 'nullable'
		];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        DB::connection('sam')->beginTransaction();

        $responsabilidades = NULL;
        if ($request->get('id_responsabilidades')) {
            $responsabilidades = implode(",", $request->get('id_responsabilidades'));
        }

        try {
            $nit = Nits::create([
                'id_tipo_documento' => $request->get('id_tipo_documento'),
                'id_vendedor' => $request->get('id_vendedor'),
                'id_responsabilidades' => $responsabilidades,
                'id_actividad_economica' => $request->get('id_actividad_economica'),
                'numero_documento' => $request->get('numero_documento'),
                'tipo_contribuyente' => $request->get('id_tipo_documento') == '6' ? 1 : 2,
                'primer_apellido' => $request->get('primer_apellido'),
                'segundo_apellido' => $request->get('segundo_apellido'),
                'primer_nombre' => $request->get('primer_nombre'),
                'otros_nombres' => $request->get('otros_nombres'),
                'razon_social' => $request->get('razon_social'),
                'direccion' => $request->get('direccion'),
                'email' => $request->get('email'),
                'email_1' => $request->get('email_1'),
                'email_2' => $request->get('email_2'),
                'telefono_1' => $request->get('telefono_1'),
                'porcentaje_aiu' => $request->get('porcentaje_aiu'),
                'porcentaje_reteica' => $request->get('porcentaje_reteica'),
                'id_ciudad' => $request->get('id_ciudad'),
                'observaciones' => $request->get('observaciones'),
                // 'declarante' => $request->get('declarante'),
                'sumar_aiu' => $request->get('sumar_aiu'),
                'proveedor' => $request->get('proveedor') ? 1 : 0,
                'plazo' => 0,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ]);

            if($request->avatar) {
                $image = $request->avatar;
                $ext = explode(";", explode("/",explode(",", $image)[0])[1])[0];
                $image = str_replace('data:image/'.$ext.';base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = 'profile_'.$nit->id.'_'.uniqid().'.'. $ext;
                Storage::disk('do_spaces')->put('imagen/profile/'.$imageName, base64_decode($image), 'public');
                $nit->logo_nit = 'imagen/profile/'.$imageName;
                $nit->save();
            }

			DB::connection('sam')->commit();
    
            return response()->json([
                'success'=>	true,
                'data' => $nit,
                'message'=> 'Nit creado con exito!'
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

    public function update (Request $request)
    {
        $nitActual = Nits::find($request->get('id'));

        if($nitActual->numero_documento != $request->get('numero_documento')){
            $nitsExist = Nits::where('numero_documento', $request->get('numero_documento'));
            if($nitsExist->count() > 0){
                return response()->json([
                    'success'=>	false,
                    'data' => '',
                    'message'=> 'El numero de documento ya esta siendo usado!'
                ]);
            }
        }

        $responsabilidades = NULL;
        if ($request->get('id_responsabilidades')) {
            $responsabilidades = implode(",", $request->get('id_responsabilidades'));
        }

        Nits::where('id', $request->get('id'))
            ->update([
                'id_tipo_documento' => $request->get('id_tipo_documento'),
                'id_vendedor' => $request->get('id_vendedor'),
                'numero_documento' => $request->get('numero_documento'),
                'id_responsabilidades' => $responsabilidades,
                'id_actividad_economica' => $request->get('id_actividad_economica'),
                'tipo_contribuyente' => $request->get('tipo_contribuyente'),
                'primer_apellido' => $request->get('primer_apellido'),
                'segundo_apellido' => $request->get('segundo_apellido'),
                'primer_nombre' => $request->get('primer_nombre'),
                'otros_nombres' => $request->get('otros_nombres'),
                'razon_social' => $request->get('razon_social'),
                'direccion' => $request->get('direccion'),
                'email' => $request->get('email'),
                'email_1' => $request->get('email_1'),
                'email_2' => $request->get('email_2'),
                'telefono_1' => $request->get('telefono_1'),
                'id_ciudad' => $request->get('id_ciudad'),
                'observaciones' => $request->get('observaciones'),
                'porcentaje_aiu' => $request->get('porcentaje_aiu'),
                'porcentaje_reteica' => $request->get('porcentaje_reteica'),
                'proveedor' => $request->get('proveedor'),
                'sumar_aiu' => $request->get('sumar_aiu'),
                'updated_by' => request()->user()->id,
            ]);

        $nit = Nits::where('id', $request->get('id'))->with('tipo_documento', 'vendedor')->first();

        if($request->avatar) {
            $image = $request->avatar;
            $ext = explode(";", explode("/",explode(",", $image)[0])[1])[0];
            $image = str_replace('data:image/'.$ext.';base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = 'profile_'.$nit->id.'_'.uniqid().'.'. $ext;
            
            Storage::disk('do_spaces')->put('imagen/profile/'.$imageName, base64_decode($image), 'public');
            $nit->logo_nit = 'imagen/profile/'.$imageName;
            $nit->save();
        }

        return response()->json([
            'success'=>	true,
            'data' => $nit,
            'message'=> 'Nit actualizado con exito!'
        ]);
    }

    public function delete (Request $request)
    {
        $documentos = DocumentosGeneral::where('id_nit', $request->get('id'));
        if($documentos->count() > 0) {
            return response()->json([
                'success'=>	false,
                'data' => '',
                'message'=> 'Este nit tiene transacciones contables, no puede ser eliminado!'
            ]);
        }

        Nits::where('id', $request->get('id'))->delete();

        return response()->json([
            'success'=>	true,
            'data' => '',
            'message'=> 'Nit eliminado con exito!'
        ]);
    }

    public function comboTipoDocumento(Request $request)
    {
        $tipoDocumento = TipoDocumentos::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(codigo, ' - ', nombre) as text")
        );

        if ($request->get("q")) {
            $tipoDocumento->where('codigo', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $tipoDocumento->paginate(40);

    }

    public function comboNit(Request $request)
    {
        // Eliminas la búsqueda de $ubicacion_maximoph y toda la lógica $text = DB::raw(...)
        // Laravel ya manejará el campo 'text' a través del Accessor en el modelo Nits.

        $totalRows = $request->has("totalRows") ? $request->get("totalRows") : 20;

        // Seleccionas solo los campos de la base de datos que la función y el Accessor necesitan.
        // El campo 'text' se agregará automáticamente al final.
        $nits = Nits::select(
            'id',
            'id_tipo_documento',
            'id_ciudad',
            'primer_nombre',
            \DB::raw('numero_documento AS segundo_nombre'), // Mantienes esto para compatibilidad
            'primer_apellido',
            'segundo_apellido',
            'razon_social', // Necesario para el Accessor
            'numero_documento', // Necesario para el Accessor
            'apartamentos', // Necesario para el Accessor
            'otros_nombres', // Necesario para el Accessor
            'email',
            'sumar_aiu',
            'porcentaje_aiu',
            'porcentaje_reteica',
            'id_responsabilidades',
            \DB::raw('telefono_1 AS telefono')
            // No incluyas $text aquí.
        );

        if ($request->get("id_nits")) {
            $nits->whereIn('id', $request->get("id_nits"));
        } else {
            // La lógica de búsqueda (where/orWhere) se mantiene igual
            // porque se basa en campos de la base de datos como numero_documento, razon_social, etc.
            if ($request->get("q")) {
                $nits->where('numero_documento', 'LIKE', '%' .$request->get("q") . '%')
                    ->orWhere('segundo_apellido', 'LIKE', '%' . $request->get("q") . '%')
                    ->orWhere('primer_nombre', 'LIKE', '%' . $request->get("q") . '%')
                    ->orWhere('otros_nombres', 'LIKE', '%' . $request->get("q") . '%')
                    ->orWhere('razon_social', 'LIKE', '%' . $request->get("q") . '%')
                    ->orWhere('email', 'LIKE', $request->get("q") . '%')
                    ->orWhere(\DB::raw("CONCAT(FORMAT(numero_documento, 0),'-',digito_verificacion,' - ',razon_social)"), "like", $request->get("q") . "%")
                    ->orWhere(\DB::raw("CONCAT(FORMAT(numero_documento, 0),' - ',razon_social)"), "like", $request->get("q") . "%")
                    ->orWhere(\DB::raw("CONCAT_WS(' ',FORMAT(numero_documento, 0),'-',primer_nombre,primer_apellido,segundo_apellido)"), "like", $request->get("q") . "%")
                    ->orWhere(\DB::raw("CONCAT_WS(' ',FORMAT(numero_documento, 0),'-',primer_nombre,otros_nombres,primer_apellido,segundo_apellido)"), "like", $request->get("q") . "%")
                    ->orWhere(\DB::raw("CONCAT_WS(' ',primer_nombre,primer_apellido,segundo_apellido)"), "like", "%" . $request->get("q") . "%")
                    ->orWhere(\DB::raw("CONCAT_WS(' ',primer_nombre,otros_nombres,primer_apellido,segundo_apellido)"), "like", "%" . $request->get("q") . "%")
                    ->orWhere('primer_apellido', 'LIKE', '%' . $request->get("q") . '%')
                    ->orWhere('apartamentos', 'LIKE', $request->get("q") . '%')
                    ->orWhere('observaciones', 'LIKE', $request->get("q") . '%')
                    ->orWhere('direccion', 'LIKE', $request->get("q") . '%');
            }

            if ($request->get("search")) {
                $nits->where('numero_documento', 'LIKE', '%' . $request->get("search") . '%')
                    ->orWhere('segundo_apellido', 'LIKE', '%' . $request->get("search") . '%')
                    ->orWhere('primer_nombre', 'LIKE', '%' . $request->get("search") . '%')
                    ->orWhere('otros_nombres', 'LIKE', '%' . $request->get("search") . '%')
                    ->orWhere('razon_social', 'LIKE', '%' . $request->get("search") . '%')
                    ->orWhere('email', 'LIKE', '%' . $request->get("search") . '%')
                    ->orWhere(\DB::raw("CONCAT(FORMAT(numero_documento, 0),'-',digito_verificacion,' - ',razon_social)"), "like", "%" . $request->get("search") . "%")
                    ->orWhere(\DB::raw("CONCAT(FORMAT(numero_documento, 0),' - ',razon_social)"), "like", "%" . $request->get("search") . "%")
                    ->orWhere(\DB::raw("CONCAT_WS(' ',FORMAT(numero_documento, 0),'-',primer_nombre,primer_apellido,segundo_apellido)"), "like", "%" . $request->get("search") . "%")
                    ->orWhere(\DB::raw("CONCAT_WS(' ',FORMAT(numero_documento, 0),'-',primer_nombre,otros_nombres,primer_apellido,segundo_apellido)"), "like", "%" . $request->get("search") . "%")
                    ->orWhere(\DB::raw("CONCAT_WS(' ',primer_nombre,primer_apellido,segundo_apellido)"), "like", "%" . $request->get("search") . "%")
                    ->orWhere(\DB::raw("CONCAT_WS(' ',primer_nombre,otros_nombres,primer_apellido,segundo_apellido)"), "like", "%" . $request->get("search") . "%")
                    ->orWhere('primer_apellido', 'LIKE', '%' . $request->get("search") . '%')
                    ->orWhere('apartamentos', 'LIKE', '%' . $request->get("search") . '%')
                    ->orWhere('observaciones', 'LIKE', '%' . $request->get("search") . '%')
                    ;
            }
        }

        // La paginación automáticamente aplicará el Accessor 'text' a cada resultado.
        return $nits->paginate($totalRows);
    }

    public function comboEmpleado(Request $request)
    {
        $totalRows = $request->has("totalRows") ? $request->get("totalRows") : 20;

        $nits = Nits::has('contrato')
            ->select(
                'id',
                'id_tipo_documento',
                'id_ciudad',
                'primer_nombre',
                \DB::raw('numero_documento AS segundo_nombre'),
                'primer_apellido',
                'segundo_apellido',
                'email',
                // 'declarante',
                'sumar_aiu',
                'porcentaje_aiu',
                'porcentaje_reteica',
                'apartamentos',
                'id_responsabilidades',
                \DB::raw('telefono_1 AS telefono'),
                \DB::raw("(CASE
                    WHEN id IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN CONCAT(numero_documento, ' - ', razon_social)
                    WHEN id IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT( numero_documento, ' - ', CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido))
                    ELSE NULL
                END) AS text")
            );

        if ($request->get("q")) {
            $nits->where('numero_documento', $request->get("q"))
                ->orWhere('segundo_apellido', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('primer_nombre', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('otros_nombres', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('razon_social', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('email', 'LIKE', $request->get("q") . '%')
                ->orWhere(DB::raw("CONCAT(FORMAT(numero_documento, 0),'-',digito_verificacion,' - ',razon_social)"), "like", $request->get("q") . "%")
                ->orWhere(DB::raw("CONCAT(FORMAT(numero_documento, 0),' - ',razon_social)"), "like", $request->get("q") . "%")
                ->orWhere(DB::raw("CONCAT_WS(' ',FORMAT(numero_documento, 0),'-',primer_nombre,primer_apellido,segundo_apellido)"), "like", $request->get("q") . "%")
                ->orWhere(DB::raw("CONCAT_WS(' ',FORMAT(numero_documento, 0),'-',primer_nombre,otros_nombres,primer_apellido,segundo_apellido)"), "like", $request->get("q") . "%")
                ->orWhere(DB::raw("CONCAT_WS(' ',primer_nombre,primer_apellido,segundo_apellido)"), "like", "%" . $request->get("q") . "%")
                ->orWhere(DB::raw("CONCAT_WS(' ',primer_nombre,otros_nombres,primer_apellido,segundo_apellido)"), "like", "%" . $request->get("q") . "%")
                ->orWhere('primer_apellido', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('apartamentos', 'LIKE', $request->get("q") . '%')
                ->orWhere('observaciones', 'LIKE', $request->get("q") . '%')
                ;
        }

        if ($request->get("search")) {
            $nits->where('numero_documento', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('segundo_apellido', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('primer_nombre', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('otros_nombres', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('razon_social', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('email', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere(DB::raw("CONCAT(FORMAT(numero_documento, 0),'-',digito_verificacion,' - ',razon_social)"), "like", "%" . $request->get("search") . "%")
                ->orWhere(DB::raw("CONCAT(FORMAT(numero_documento, 0),' - ',razon_social)"), "like", "%" . $request->get("search") . "%")
                ->orWhere(DB::raw("CONCAT_WS(' ',FORMAT(numero_documento, 0),'-',primer_nombre,primer_apellido,segundo_apellido)"), "like", "%" . $request->get("search") . "%")
                ->orWhere(DB::raw("CONCAT_WS(' ',FORMAT(numero_documento, 0),'-',primer_nombre,otros_nombres,primer_apellido,segundo_apellido)"), "like", "%" . $request->get("search") . "%")
                ->orWhere(DB::raw("CONCAT_WS(' ',primer_nombre,primer_apellido,segundo_apellido)"), "like", "%" . $request->get("search") . "%")
                ->orWhere(DB::raw("CONCAT_WS(' ',primer_nombre,otros_nombres,primer_apellido,segundo_apellido)"), "like", "%" . $request->get("search") . "%")
                ->orWhere('primer_apellido', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('apartamentos', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('observaciones', 'LIKE', $request->get("q") . '%')
                ;
        }

        return $nits->paginate($totalRows);
    }

    public function getNitInfo (Request $request)
    {
        $nit = Nits::where('id', $request->get('id_nit'))
            ->select(
                '*',
                DB::raw("CASE
                    WHEN id IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END AS nombre_nit")
            )
            ->with('ciudad');

        // $nit = Nits::whereNumeroDocumento($request->get("numero_documento"));

        return response()->json([
            "success"=>true,
            "data"=>$nit->first()
        ], 200);
    }

}

<?php

namespace App\Http\Controllers\Importador;

use DB;
use Exception;
use Illuminate\Http\Request;
use App\Imports\ImportNits;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Empresas\Ciudades;
use App\Models\Sistema\NitsImport;
use App\Models\Sistema\TipoDocumentos;

class NitsImportadorController extends Controller
{
    protected $messages = null;

    public function __construct()
    {
        $this->messages = [
            'required' => 'El campo :attribute es requerido.',
        ];
    }

    public function index()
    {
        return view('pages.importador.nits.nits-view');
    }

    /* ============================================================
       PASO 2 — Cargar Excel a tabla temporal
       ============================================================ */
    public function importar(Request $request)
    {
        $rules = [
            'file_import_nits' => 'required|mimes:xlsx'
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $file = $request->file('file_import_nits');
            NitsImport::truncate();

            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
            $actualizarValores = $request->has('actualizar_valores');

            $import = new ImportNits($empresa->razon_social, $actualizarValores);
            $import->import($file);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [],
                'message' => $actualizarValores
                    ? 'Cédulas/NITs cargados en modo ACTUALIZAR. Revisa la tabla antes de procesar.'
                    : 'Cédulas/NITs cargados en modo CREAR. Revisa la tabla antes de procesar.'
            ]);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Error al cargar cédulas/NITs: ' . $e->getMessage()
            ], 422);
        }
    }

    /* ============================================================
       DataTable server-side
       ============================================================ */
    public function generate(Request $request)
    {
        $draw            = $request->get('draw');
        $start           = (int) $request->get('start', 0);
        $rowperpage      = (int) $request->get('length', 20);
        $order_arr       = $request->get('order', []);
        $columnName_arr  = $request->get('columns', []);
        $search_arr      = $request->get('search', []);

        $columnIndex     = $order_arr[0]['column'] ?? 0;
        $columnDataRaw   = $columnName_arr[$columnIndex]['data'] ?? 'id';
        $columnSortOrder = $order_arr[0]['dir'] ?? 'asc';

        // ── Mapa: nombre lógico del DataTable → columna real de BD ──
        // Las columnas 0 y 1 son virtuales ("function") y no se pueden ordenar en SQL.
        // El resto usa el mismo nombre que la columna de `nits_imports`.
        $columnasOrdenables = [
            'id'                  => 'id',
            'tipo_documento'      => 'tipo_documento',
            'numero_documento'    => 'numero_documento',
            'digito_verificacion' => 'digito_verificacion',
            'primer_nombre'       => 'primer_nombre',
            'otros_nombres'       => 'otros_nombres',
            'primer_apellido'     => 'primer_apellido',
            'segundo_apellido'    => 'segundo_apellido',
            'razon_social'        => 'razon_social',
            'direccion'           => 'direccion',
            'ciudad_display'      => 'ciudad',
            'email'               => 'email',
            'telefono_1'          => 'telefono_1',
            'plazo'               => 'plazo',
            'cupo'                => 'cupo',
            'observaciones'       => 'observaciones',
            'email_1'             => 'email_1',
            'email_2'             => 'email_2',
            'errores'             => 'observacion',
            'total_errores'       => 'observacion',
        ];

        // Si el DataTable manda "function" o algo no mapeado, ordenamos por `id` por defecto
        $columnName = $columnasOrdenables[$columnDataRaw] ?? 'id';
        $searchValue     = $search_arr['value'] ?? '';

        $query = NitsImport::query();

        if ($searchValue !== '') {
            $query->where(function ($q) use ($searchValue) {
                $q->where('numero_documento', 'like', '%' . $searchValue . '%')
                  ->orWhere('primer_nombre',   'like', '%' . $searchValue . '%')
                  ->orWhere('otros_nombres',   'like', '%' . $searchValue . '%')
                  ->orWhere('primer_apellido', 'like', '%' . $searchValue . '%')
                  ->orWhere('segundo_apellido','like', '%' . $searchValue . '%')
                  ->orWhere('razon_social',    'like', '%' . $searchValue . '%')
                  ->orWhere('direccion',       'like', '%' . $searchValue . '%')
                  ->orWhere('email',           'like', '%' . $searchValue . '%')
                  ->orWhere('telefono_1',      'like', '%' . $searchValue . '%')
                  ->orWhere('plazo',           'like', '%' . $searchValue . '%')
                  ->orWhere('cupo',            'like', '%' . $searchValue . '%')
                  ->orWhere('observaciones',   'like', '%' . $searchValue . '%');
            });
        }

        $totalRecords = (clone $query)->count();

        // Primero los que tienen errores (para que el usuario los vea)
        $dataNits = $query
            ->orderByRaw('CASE WHEN estado IN (1,3) THEN 0 ELSE 1 END ASC')
            ->orderBy($columnName, $columnSortOrder)
            ->skip($start)
            ->take($rowperpage)
            ->get();

        foreach ($dataNits as $nit) {
            $nit->id = $nit->id + 1;
            $nit->tipo_documento = $this->tipoDocumento($nit->tipo_documento);

            $estado = (int) $nit->estado;
            $nit->accion = in_array($estado, [0, 1], true) ? 'crear' : 'actualizar';

            // 👇 NUEVO: mostrar ciudad resuelta o el texto con error
            $nit->ciudad_display = $nit->id_ciudad
                ? optional(Ciudades::find($nit->id_ciudad))->nombre_completo
                : $nit->ciudad;

            $nit->errores       = $nit->observacion ?? '';
            $nit->total_errores = $nit->observacion
                ? substr_count($nit->observacion, '<br/>')
                : 0;
        }

        return response()->json([
            'success'              => true,
            'draw'                 => (int) $draw,
            'iTotalRecords'        => $totalRecords,
            'iTotalDisplayRecords' => $totalRecords,
            'data'                 => $dataNits,
            'perPage'              => $rowperpage,
            'message'              => 'NITs cargados con éxito.',
        ]);
    }

    public function exportar(Request $request)
    {
        return response()->json([
            'success' => true,
            'url' => 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/import/Importador%20de%20Cedulas_Nits.xlsx',
            'message' => 'Url generada con exito'
        ]);
    }

    /* ============================================================
       PASO 3 — Ejecutar creación / actualización
       AQUÍ NO SE VALIDA NADA (todo ya se validó en el Import).
       Solo se ejecuta la acción correspondiente según `estado`.
       ============================================================ */
    public function actualizar(Request $request)
    {
        // Solo filas válidas: 0 = Crear OK, 2 = Actualizar OK
        $nitsValidos = NitsImport::whereIn('estado', [0, 2])->get();

        if (!count($nitsValidos)) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'No hay NITs válidos para procesar. Corrige los errores primero.'
            ], 422);
        }

        try {
            DB::connection('sam')->beginTransaction();

            $creados = 0;
            $actualizados = 0;
            $omitidos = 0;

            foreach ($nitsValidos as $nit) {
                if ((int) $nit->estado === 2) {
                    // ══════════ ACTUALIZAR ══════════
                    $nitExistente = Nits::where('numero_documento', $nit->numero_documento)->first();

                    if (!$nitExistente) {
                        $omitidos++;
                        continue;
                    }

                    $nitExistente->primer_nombre    = $nit->primer_nombre;
                    $nitExistente->otros_nombres    = $nit->otros_nombres;
                    $nitExistente->primer_apellido  = $nit->primer_apellido;
                    $nitExistente->segundo_apellido = $nit->segundo_apellido;
                    $nitExistente->razon_social     = $nit->razon_social;
                    $nitExistente->direccion        = $nit->direccion;

                    // 👇 NUEVO: solo sobreescribe si vino ciudad resuelta
                    if ($nit->id_ciudad) {
                        $nitExistente->id_ciudad = $nit->id_ciudad;
                    }

                    if ($nit->email)         $nitExistente->email         = $nit->email;
                    if ($nit->email_1)       $nitExistente->email_1       = $nit->email_1;
                    if ($nit->email_2)       $nitExistente->email_2       = $nit->email_2;
                    if ($nit->telefono_1)    $nitExistente->telefono_1    = $nit->telefono_1;
                    if ($nit->observaciones) $nitExistente->observaciones = $nit->observaciones;

                    $nitExistente->updated_by = $request->user()->id;
                    $nitExistente->save();

                    $actualizados++;

                } else {
                    // ══════════ CREAR ══════════
                    $tipoDocumento = TipoDocumentos::where('codigo', $nit->tipo_documento)->first();

                    if (!$tipoDocumento) {
                        $omitidos++;
                        continue;
                    }

                    if (Nits::where('numero_documento', $nit->numero_documento)->exists()) {
                        $omitidos++;
                        continue;
                    }

                    Nits::create([
                        'id_tipo_documento'  => $tipoDocumento->id,
                        'id_ciudad'          => $nit->id_ciudad,
                        'numero_documento'   => $nit->numero_documento,
                        'digito_verificacion'=> $nit->digito_verificacion,
                        'tipo_contribuyente' => $tipoDocumento->id == 6 ? 1 : 2,
                        'primer_nombre'      => $nit->primer_nombre,
                        'otros_nombres'      => $nit->otros_nombres,
                        'primer_apellido'    => $nit->primer_apellido,
                        'segundo_apellido'   => $nit->segundo_apellido,
                        'razon_social'       => $nit->razon_social,
                        'direccion'          => $nit->direccion,
                        'email'              => $nit->email,
                        'telefono_1'         => $nit->telefono_1,
                        'observaciones'      => $nit->observaciones,
                        'email_1'            => $nit->email_1,
                        'email_2'            => $nit->email_2,
                        'plazo'              => $nit->plazo,
                        'cupo'               => $nit->cupo,
                        'created_by'         => $request->user()->id,
                        'updated_by'         => $request->user()->id,
                    ]);

                    $creados++;
                }
            }

            // Borrar solo las filas procesadas (las que tenían error quedan para que las veas)
            NitsImport::whereIn('estado', [0, 2])->delete();

            DB::connection('sam')->commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'creados' => $creados,
                    'actualizados' => $actualizados,
                    'omitidos' => $omitidos,
                ],
                'message' => "Procesados: {$creados} creados, {$actualizados} actualizados, {$omitidos} omitidos."
            ]);

        } catch (Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
            ], 422);
        }
    }

    /* ============================================================
       Helpers
       ============================================================ */
    private function tipoDocumento($tipoDocumento)
    {
        if ($tipoDocumento) {
            $tipo = TipoDocumentos::where('codigo', $tipoDocumento)->first();
            if ($tipo) {
                return $tipo->codigo . ' - ' . $tipo->nombre;
            }
        }
        return $tipoDocumento;
    }
}
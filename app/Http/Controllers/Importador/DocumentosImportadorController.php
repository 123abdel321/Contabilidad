<?php

namespace App\Http\Controllers\Importador;

use DB;
use App\Helpers\Documento;
use Illuminate\Http\Request;
use App\Imports\ImportDocumentos;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\CentroCostos;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\FacDocumentos;
use App\Models\Sistema\DocumentosImport;
use App\Models\Sistema\DocumentosGeneral;

class DocumentosImportadorController extends Controller
{
    use BegConsecutiveTrait;

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
        return view('pages.importador.documentos.documentos-view');
    }

    public function importar (Request $request)
    {
        $rules = [
            'file_import_documentos' => 'required|mimes:xlsx'
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
            $file = $request->file('file_import_documentos');

            DocumentosImport::truncate();

            $import = new ImportDocumentos();
            $import->import($file);

            $this->agregarValidaciones();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Documentos cargados con exito!'
            ]);

        } catch (Exception $e) {

            return response()->json([
                'success'=>	false,
                'data' => $e->failures(),
                'message'=> 'Error al cargar cédulas documentos'
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

        $documentosImportados = DocumentosImport::orderBy($columnName,$columnSortOrder)
            ->where('documento_nit', 'like', '%' .$searchValue . '%')
            ->orWhere('cuenta_contable', 'like', '%' .$searchValue . '%')
            ->orWhere('codigo_cecos', 'like', '%' .$searchValue . '%')
            ->orWhere('codigo_comprobante', 'like', '%' .$searchValue . '%')
            ->orWhere('consecutivo', 'like', '%' .$searchValue . '%')
            ->orWhere('documento_referencia', 'like', '%' .$searchValue . '%')
            ->orWhere('fecha_manual', 'like', '%' .$searchValue . '%')
            ->orWhere('debito', 'like', '%' .$searchValue . '%')
            ->orWhere('credito', 'like', '%' .$searchValue . '%')
            ->orWhere('concepto', 'like', '%' .$searchValue . '%');
            
        $documentosImportadosTotals = $documentosImportados->get();

        $cuentasPaginate = $documentosImportados->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $documentosImportadosTotals->count(),
            'iTotalDisplayRecords' => $documentosImportadosTotals->count(),
            'data' => $cuentasPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Documentos generado con exito!'
        ]);
    }
    
    public function exportar (Request $request)
    {
        return response()->json([
            'success'=>	true,
            'url' => 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/import/importador_documentos.xlsx',
            'message'=> 'Url generada con exito'
        ]);
        
    }

    public function actualizar (Request $request)
    {
        $documentosImportados = DocumentosImport::get();
        $documentosImportadosErrores = DocumentosImport::where('total_errores', '>', 0);
        
        try {
            DB::connection('sam')->beginTransaction();

            if ($documentosImportadosErrores->count()) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>'El documento importado tiene errores pedientes por solucionar'
                ], 422);
            }
            $documentosAgrupados = [];


            if (count($documentosImportados)) {
                
                foreach ($documentosImportados as $documentoPrevio) {
                    $fechaFormat = date("Ym", strtotime($documentoPrevio->fecha_manual));
                    $documentosAgrupados[$fechaFormat.'-'.$documentoPrevio->codigo_comprobante.'-'.$documentoPrevio->consecutivo][] = $documentoPrevio;
                }
                foreach ($documentosAgrupados as $documentoImportado) {
                    $debito = 0;
                    $credito = 0;
                    $tokenFactura = $this->generateTokenDocumento();
                    $comprobante = Comprobantes::where('codigo', $documentoImportado[0]->codigo_comprobante)->first();

                    $facDocumento = FacDocumentos::create([
                        'id_comprobante' => $comprobante->id,
                        'fecha_manual' => $documentoImportado[0]->fecha_manual,
                        'consecutivo' => $documentoImportado[0]->consecutivo,
                        'token_factura' => $tokenFactura,
                        'debito' => 0,
                        'credito' => 0,
                        'saldo_final' => 0,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id,
                    ]);

                    $primerIdNit = null;
			        $documentoGeneral = new Documento(
                        $comprobante->id,
                        $facDocumento,
                        $documentoImportado[0]->fecha_manual,
                        $documentoImportado[0]->consecutivo
                    );

                    foreach ($documentoImportado as $data) {
                        $debito+= $data->debito;
				        $credito+= $data->credito;

                        $cecos = CentroCostos::where('codigo', $data->codigo_cecos)->first();
                        $nit = Nits::where('numero_documento', $data->documento_nit)->first();
                        $cuenta = PlanCuentas::where('cuenta', $data->cuenta_contable)->first();
                        
                        $doc = [
                            'id_cuenta' => $cuenta->id,
                            'id_nit' => $nit ? $nit->id : '',
                            'documento_referencia' => $data->documento_referencia,
                            'id_centro_costos' => $cecos ? $cecos->id : '',
                            'concepto' => $data->concepto,
                            'fecha_manual' => $data->fecha_manual,
                            'consecutivo' => $data->consecutivo,
                            'id_comprobante' => $comprobante->id,
                            'debito' => $data->debito,
                            'credito' => $data->credito,
                            'saldo' => '',
                            'naturaleza' => '',
                        ];

                        $naturaleza = null;

                        if ($doc['debito'] > 0) {
                            $naturaleza = PlanCuentas::DEBITO;
                        } else if ($doc['credito'] > 0) {
                            $naturaleza = PlanCuentas::CREDITO;
                        }

                        if (array_key_exists('cuenta', $doc)) {
                            $doc['id_cuenta'] = PlanCuentas::whereCuenta($doc['cuenta'])->value('id');
                            unset($doc['cuenta']);
                        }

                        if (array_key_exists('codigo_centro_costos', $doc)) {
                            $doc['id_centro_costos'] = CentroCostos::whereCodigo($doc['codigo_centro_costos'])->value('id');
                            unset($doc['codigo_centro_costos']);
                        }

                        if (array_key_exists('numero_documento', $doc)) {
                            $doc['id_nit'] = Nits::whereNumeroDocumento($doc['numero_documento'])->value('id');
                            if(!$primerIdNit) $primerIdNit = $doc['id_nit'];
                            unset($doc['numero_documento']);
                        } else {
                            if(!$primerIdNit) $primerIdNit = $doc['id_nit'];
                        }

                        $doc['created_by'] = request()->user()->id;
				        $doc['updated_by'] = request()->user()->id;
                        $doc['consecutivo'] = $data->consecutivo;

                        $doc = new DocumentosGeneral($doc);
				        $documentoGeneral->addRow($doc, $naturaleza);
                    }

                    if (!$documentoGeneral->save()) {

                        DB::connection('sam')->rollback();
                        return response()->json([
                            'success'=>	false,
                            'data' => [],
                            'message'=> $documentoGeneral->getErrors()
                        ], 200);
                    }

                    $facDocumento->debito = $debito;
                    $facDocumento->credito = $credito;
                    $facDocumento->id_nit = $primerIdNit;
                    $facDocumento->saldo_final = $debito - $credito;
                    $facDocumento->updated_by = request()->user()->id;
                    $facDocumento->save();

                    $this->updateConsecutivo($comprobante->id, $documentoImportado[0]->consecutivo);
                }
                DocumentosImport::whereNotNull('id')->delete();
                DB::connection('sam')->commit();
            }


            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Documentos importados con exito!'
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

    public function validar (Request $request)
    {
        try {

            $this->agregarValidaciones();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Documentos validados con exito!'
            ]);

        } catch (Exception $e) {

            return response()->json([
                'success'=>	false,
                'data' => $e->failures(),
                'message'=> 'Error al validar documentos importados'
            ]);
        }
    }

    private function generateTokenDocumento()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 64; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    private function agregarValidaciones ()
    {
        $documentosImportados = DocumentosImport::get();

        if (count($documentosImportados)) {
            foreach ($documentosImportados as $documentoImportado) {
                $errores = '';
                $totalErrores = 0;
                $cuentasContables = PlanCuentas::where('cuenta', $documentoImportado->cuenta_contable)->first();
    
                $documentoImportado->nombre_cuenta = $documentoImportado->cuenta_contable;
                $documentoImportado->nombre_nit = $documentoImportado->documento_nit;
                $documentoImportado->nombre_cecos = $documentoImportado->codigo_cecos;
                $documentoImportado->nombre_comprobante = $documentoImportado->codigo_comprobante;

                //VALIDAR CUENTAS CONTABLES
                if ($documentoImportado->cuenta_contable) {
                    if ($cuentasContables) {
                        $documentoImportado->nombre_cuenta = $cuentasContables->cuenta. ' - '.$cuentasContables->nombre;
                    } else {
                        $errores.= 'La cuenta contable no existe. <br/>';
                        $totalErrores++;
                    }
                } else {
                    $errores.= 'La cuenta contable es requerida. <br/>';
                    $totalErrores++;
                }

                //VALIDAR NIT
                if ($documentoImportado->documento_nit) {
                    $existeNit = Nits::where('numero_documento', $documentoImportado->documento_nit)->first();
                    if ($existeNit) {
                        $documentoImportado->nombre_nit = $existeNit->numero_documento.' - '.$existeNit->nombre_completo;
                    } else {
                        $errores.= 'El documento nit no existe. <br/>';
                        $totalErrores++;
                    }
                } else if ($cuentasContables && $cuentasContables->exige_nit) {
                    $errores.= 'La cuenta contable exige nit. <br/>';
                    $totalErrores++;
                }
                //VALIDAR CENTRO DE COSTOS
                if ($documentoImportado->codigo_cecos) {
                    $existeCecos = CentroCostos::where('codigo', $documentoImportado->codigo_cecos)->first();
                    if ($existeCecos) {
                        $documentoImportado->nombre_cecos = $existeCecos->codigo. ' - '.$existeCecos->nombre;
                    } else {
                        $errores.= 'El centro de costos no existe. <br/>';
                        $totalErrores++;
                    }
                } else if ($cuentasContables && $cuentasContables->exige_centro_costos) {
                    $errores.= 'La cuenta contable exige centro de costos. <br/>';
                    $totalErrores++;
                }
                //VALIDAR COMPROBANTE
                if ($documentoImportado->codigo_comprobante) {
                    $existeComprobante = Comprobantes::where('codigo', $documentoImportado->codigo_comprobante)->first();
                    if ($existeComprobante) {
                        $documentoImportado->nombre_comprobante = $existeComprobante->codigo. ' - '.$existeComprobante->nombre;
                    } else {
                        $errores.= 'El codigo comprobante no existe. <br/>';
                        $totalErrores++;
                    }
                } else {
                    $errores.= 'El codigo comprobante es requerido. <br/>';
                    $totalErrores++;
                }
                //VALIDAR CREDITO & DEBITO
                if (!$documentoImportado->debito && !$documentoImportado->credito) {
                    $errores.= 'El registro debe tener valores. <br/>';
                    $totalErrores++;
                }
    
                $documentoImportado->errores = $errores;
                $documentoImportado->total_errores = $totalErrores;
    
                $documentoImportado->save();
            }
        }

    }

}
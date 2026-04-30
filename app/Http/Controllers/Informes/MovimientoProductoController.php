<?php

namespace App\Http\Controllers\Informes;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Bus;
use App\Http\Controllers\Controller;
use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacVentas;
use App\Models\Sistema\FacCompras;
use App\Models\Sistema\FacProductosBodegasMovimiento;

class MovimientoProductoController extends Controller
{
    public function index ()
    {
        return view('pages.contabilidad.movimiento_producto.movimiento_producto-view');
    }

    public function generate(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = 20;

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $searchValue = $request->get('search');
        $searchValue = isset($searchValue) ? $searchValue["value"] : null;

        $columnIndex = $columnIndex_arr[0]['column'];
        $columnName = $columnName_arr[$columnIndex]['data'];
        $columnSortOrder = $order_arr[0]['dir'];

        // Parámetros de filtro
        $tipoInforme = $request->get('tipo_informe'); // Asegúrate de que coincida con el nombre enviado
        $idCliente = $request->get('id_cliente');
        $idProducto = $request->get('id_producto');
        $fechaDesde = $request->get('fecha_desde');
        $fechaHasta = $request->get('fecha_hasta');

        $query = FacProductosBodegasMovimiento::with(['bodega', 'producto', 'relation'])
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion")
            );

        // ----------------------------
        // Filtro por tipo de informe
        // ----------------------------
        if ($tipoInforme) {
            switch ($tipoInforme) {
                case 'venta':
                    $query->where('relation_type', 4)
                        ->whereDoesntHaveMorph('relation', [FacVentas::class], function ($q) {
                            $q->where('codigo_tipo_documento_dian', CodigoDocumentoDianTypes::NOTA_CREDITO);
                        });
                    break;

                case 'devolución':
                    $query->where('relation_type', 4)
                        ->whereHasMorph('relation', [FacVentas::class], function ($q) {
                            $q->where('codigo_tipo_documento_dian', CodigoDocumentoDianTypes::NOTA_CREDITO);
                        });
                    break;

                case 'compra':
                    $query->where('relation_type', 3);
                    break;

                case 'cargue':
                    $query->where('relation_type', 5)->where('tipo_tranferencia', 1);
                    break;

                case 'descargue':
                    $query->where('relation_type', 5)->where('tipo_tranferencia', 2);
                    break;

                case 'traslado':
                    $query->where('relation_type', 5)->where('tipo_tranferencia', 3);
                    break;

                default:
                    // General: no filtrar por tipo
                    break;
            }
        }

        // ----------------------------
        // Filtro por producto
        // ----------------------------
        if ($idProducto) {
            $query->where('id_producto', $idProducto);
        }

        // ----------------------------
        // Filtro por cliente
        // Solo para movimientos que sean ventas (relation_type = 4)
        // Si necesitas compras, añade otro OR con relation_type = 3 y relación con FacCompras
        // ----------------------------
        if ($idCliente) {
            $query->where(function ($sub) use ($idCliente) {
                $sub->where('relation_type', 4)
                    ->whereHasMorph('relation', [FacVentas::class], function ($q) use ($idCliente) {
                        $q->where('id_cliente', $idCliente);
                    })
                ->orWhere(function ($q) use ($idCliente) {
                    $q->where('relation_type', 3)
                      ->whereHasMorph('relation', [FacCompras::class], function ($rel) use ($idCliente) {
                          $rel->where('id_proveedor', $idCliente);
                      });
                });
            });
        }

        // ----------------------------
        // Filtro por fechas
        // ----------------------------
        if ($fechaDesde && $fechaHasta) {
            $desde = Carbon::parse($fechaDesde);
            $hasta = Carbon::parse($fechaHasta);
            $query->whereBetween('created_at', [$desde, $hasta]);
        }

        // Ordenamiento por defecto (puedes usar el enviado por DataTable)
        $query->orderBy('id', 'ASC');

        $total = $query->count();
        $productosMovimiento = $query->skip($start)->take($rowperpage)->get();

        return response()->json([
            'success' => true,
            'draw' => $draw,
            'iTotalRecords' => $total,
            'iTotalDisplayRecords' => $total,
            'data' => $productosMovimiento,
            'perPage' => $rowperpage,
            'message' => 'Movimientos cargados correctamente.'
        ]);
    }
}

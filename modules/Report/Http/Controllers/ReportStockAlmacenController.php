<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade as PDF;
use Modules\Report\Exports\QuotationExport;
use Illuminate\Http\Request;
use Modules\Report\Traits\ReportTrait;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\Company;
use App\Models\Tenant\Item;
use App\Models\Tenant\Rate;
use App\Models\Tenant\Warehouse;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Report\Exports\StockAlmacenExport;
use Modules\Report\Http\Resources\ReportStockAlmacenCollection;

class ReportStockAlmacenController extends Controller
{

    use ReportTrait;

    public function index() {

        return view('report::stock.index');
    }

    public function datosSP()
    {

        $sp = DB::connection('tenant')->select("CALL SP_StockAlmacen(?,?);",[request()->query('warehouse_id'),request()->query('item_id')]);
        $sp1 = array();
        $sp2 = [];
        foreach($sp as $row)
        {
            foreach($row as $key => $data)
            {
                array_push($sp1, $data);
                array_push($sp2, $key);
            }
            break;
        }
        $collection = collect($sp);
        $per_page = (config('tenant.items_per_page'));
        $page = request()->query('page') ?? 1;
        $paginatedItems = $collection->slice(($page - 1) * $per_page, $per_page)->all();
        $paginatedCollection = new LengthAwarePaginator($paginatedItems, count($collection), $per_page, $page);
        $paginatedCollection['datos'] = $sp2;
        return new ReportStockAlmacenCollection($paginatedCollection);
    }


    public function pdf(Request $request) {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;
        $records = DB::connection('tenant')->select("CALL SP_StockAlmacen(?,?);",[request()->query('warehouse_id'),request()->query('item_id')]);
        $sp1 = array();
        $sp2 = [];
        foreach($records as $row)
        {
            foreach($row as $key => $data)
            {
                array_push($sp1, $data);
                array_push($sp2, $key);
            }
            break;
        }
        $usuario_log = Auth::user();
        $fechaActual = date('d/m/Y');

        $pdf = PDF::loadView('report::stock.stock_pdf', compact("records", "company", "establishment", "usuario_log", "sp2"))->setPaper('a3', 'landscape');

        $filename = 'Reporte_Stock_Almacen_'.date('YmdHis');

        return $pdf->download($filename.'.pdf');
    }

    public function excel(Request $request) {

        $company = Company::first();
        Log::info($request->input('warehouse_id'));
        $records = DB::connection('tenant')->select("CALL SP_StockAlmacen(?,?);",[$request['warehouse_id'],$request['item_id']]);
        $filters = $request->all();
        $usuario_log = Auth::user();
        $fechaActual = date('d/m/Y');
        $sp1 = array();
        $sp2 = [];
        foreach($records as $row)
        {
            foreach($row as $key => $data)
            {
                array_push($sp1, $data);
                array_push($sp2, $key);
            }
            break;
        }

        return (new StockAlmacenExport)
                ->records($records)
                ->company($company)
                ->sp2($sp2)
                ->usuario_log($usuario_log)
                ->fechaActual($fechaActual)
                ->download('Reporte_Stock_Almacen_'.Carbon::now().'.xlsx');

    }

    public function tables(){

        $warehouses = Warehouse::all()->transform(function($row){
            return [
                'id' => $row->id,
                'name' => $row->description,
            ];
        });

        $items = Item::get()->transform(function($row){
            return[
                'id' => $row->id,
                'name' =>$row->name,
            ];
        });

        return compact("warehouses","items");
    }
}

<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\ItemController;
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
use Modules\Item\Models\Brand;
use Modules\Item\Models\Category;
use Modules\Report\Exports\StockAlmacenExport;
use Modules\Report\Http\Resources\ReportStockAlmacenCollection;
use SebastianBergmann\Environment\Console;

class ReportStockAlmacenController extends Controller
{

    use ReportTrait;

    public function index() {

        return view('report::stock.index');
    }

    public function datosSP()
    {
        $linea = request()->query('linea');
        if(request()->query('linea') === 'NA' || (request()->query('linea')) === null)
        {
            $linea = '';
        }
        $sp = DB::connection('tenant')->select("CALL SP_StockAlmacen(?,?,?,?,?);",[request()->query('warehouse_id'),request()->query('item_id'),request()->query('categorie_id'),request()->query('brand_id'), $linea]);
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
        $linea = $request['linea'];
        if($request['linea'] === 'NA' || $request['linea'] === null)
        {
            $linea = '';
        }
        //$sp = DB::connection('tenant')->select("CALL SP_StockAlmacen(?,?,?,?,?);",[request()->query('warehouse_id'),request()->query('item_id'),request()->query('categorie_id'),request()->query('brand_id'), $linea]);

        $records = DB::connection('tenant')->select("CALL SP_StockAlmacen(?,?,?,?,?);",[$request['warehouse_id'],$request['item_id'],$request['categorie_id'],$request['brand_id'], $linea]);
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

        $linea = $request['linea'];
        if($request['linea'] === 'NA' || $request['linea'] === null)
        {
            $linea = '';
        }
        //$sp = DB::connection('tenant')->select("CALL SP_StockAlmacen(?,?,?,?,?);",[request()->query('warehouse_id'),request()->query('item_id'),request()->query('categorie_id'),request()->query('brand_id'), $linea]);

        $records = DB::connection('tenant')->select("CALL SP_StockAlmacen(?,?,?,?,?);",[$request['warehouse_id'],$request['item_id'],$request['categorie_id'],$request['brand_id'], $linea]);
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
                'name' =>$row->name.' / '.$row->description.' / '.$row->internal_id.' / '.$row->model.' / '.$row->factory_code,
            ];
        });

        $brands = Brand::get()->transform(function($row){
            return[
                'id' => $row->id,
                'name' =>$row->name,
            ];
        });
        $categories = Category::get()->transform(function($row){
            return[
                'id' => $row->id,
                'name' =>$row->name,
            ];
        });

        $categorys =  new ItemController();
        $categories = $categorys->getCategoriesTree();
        $categories[] = ['value' => 0,'label' => 'Todas las categorías'];

        return compact("warehouses","items","brands","categories");
    }
}

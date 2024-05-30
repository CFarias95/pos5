<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use App\Models\Tenant\SaleNote;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Hyn\Tenancy\Events\Websites\Migrated;
use Hyn\Tenancy\Listeners\Database\MigratesTenants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Item\Models\Brand;
use Modules\Item\Models\Category;
use Modules\Report\Exports\ReportDetailSaleExport;
use Modules\Report\Exports\StatusClientExport;
use Modules\Report\Http\Resources\DocumentCollection;
use Modules\Report\Http\Resources\SaleNoteCollection;
use Modules\Report\Traits\ReportTrait;
use Modules\Report\Http\Resources\ReporteVentasCollection;

class ReporteVentasDetalleController extends Controller
{

    use ReportTrait;

    public function index()
    {
        return view('report::reporte_ventas_detalle.index');
    }

    public function filter()
    {
        $persons = $this->getPersons('customers');
        $brands = Brand::orderBy('name','asc')->get();
        $categories = Category::where('parent_id',null)->get();

        return compact('persons','brands','categories');
    }



    public function records(Request $request)
    {
        $desde = $request->desde;
        $hasta = $request->hasta;
        $customer = $request->customer;
        $brand_id = $request->brand_id;
        $categorie_id = $request->category_id;

        $data = DB::connection('tenant')->select('CALL SP_ReporteVentasDetalle(?,?,?,?,?);',[$desde,$hasta,$customer,$brand_id,$categorie_id]);

        $collection = collect($data);
        $per_page = (config('tenant.items_per_page'));
        $page = request()->query('page') ?? 1;
        if($data && count($data) > 0 ){
            $header = get_object_vars($data[0]);
        }else{
            $header = [];
        }

        $paginatedItems = $collection->slice(($page - 1) * $per_page, $per_page)->all();

        $paginatedCollection = new LengthAwarePaginator($paginatedItems, count($collection), $per_page, $page);


        return compact('paginatedCollection', 'header');
    }

    public function excel(Request $request)
    {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;
        $desde = $request->desde;
        $hasta = $request->hasta;
        $customer = $request->customer;
        $brand_id = $request->brand_id;
        $categorie_id = $request->category_id;

        $records = DB::connection('tenant')->select('CALL SP_ReporteVentasDetalle(?,?,?,?,?);',[$desde,$hasta,$customer,$brand_id,$categorie_id]);

        $documentExport = new ReportDetailSaleExport();
        $documentExport
            ->records($records)
            ->company($company)
            ->establishment($establishment);
        return $documentExport->download('Reporte_detalle_ventas' . Carbon::now() . '.xlsx');
    }


    public function getCategories($records, $is_service)
    {

        $aux_categories = collect([]);

        foreach ($records as $document) {

            $id_categories = $document->items->filter(function ($row) use ($is_service) {
                return (($is_service) ? (!is_null($row->relation_item->category_id) && $row->item->unit_type_id === 'ZZ') : !is_null($row->relation_item->category_id));
            })->pluck('relation_item.category_id');

            foreach ($id_categories as $value) {
                $aux_categories->push($value);
            }
        }
        return Category::whereIn('id', $aux_categories->unique()->toArray())->get();
    }


}

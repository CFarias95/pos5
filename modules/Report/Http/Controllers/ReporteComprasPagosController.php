<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use App\Models\Tenant\Person;
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
use Modules\Item\Models\Category;
use Modules\Report\Exports\ReportDetailSaleExport;
use Modules\Report\Exports\ReportPurchasePaymentsExport;
use Modules\Report\Exports\StatusClientExport;
use Modules\Report\Http\Resources\DocumentCollection;
use Modules\Report\Http\Resources\SaleNoteCollection;
use Modules\Report\Traits\ReportTrait;
use Modules\Report\Http\Resources\ReporteVentasCollection;

class ReporteComprasPagosController extends Controller
{

    use ReportTrait;

    public function index()
    {
        return view('report::report_purchase_payments.index');
    }

    public function filter()
    {
        $persons =  Person::where('type','suppliers')->get()->transform(function($row){
            return [
                'id' => $row->id,
                'description' => $row->number.' - '.$row->name,
                'name' => $row->name,
                'number' => $row->number,
                'identity_document_type_id' => $row->identity_document_type_id,
            ];
        });
        return compact('persons');
    }

    public function records(Request $request)
    {
        $desde = $request->desde;
        $hasta = $request->hasta;
        $supplier = $request->supplier_id;
        $multipay = ($request->multipay === true || $request->multipay === 'true')? 'SI':'NO';

        Log::info('MULTIPAY: '.$multipay);

        $data = DB::connection('tenant')->select('CALL SP_pagos(?,?,?,?);',[$supplier,$desde,$hasta,$multipay]);

        $collection = collect($data);
        $per_page = (config('tenant.items_per_page'));
        $page = request()->query('page') ?? 1;
        if(isset($data[0]) ){
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
        $supplier = $request->supplier_id;
        $multipay = (isset($request->multipay) && ($request->multipay == true || $request->multipay == 'true'))? 'SI':'NO';

        $records = DB::connection('tenant')->select('CALL SP_pagos(?,?,?,?);',[$supplier,$desde,$hasta,$multipay]);

        $documentExport = new ReportPurchasePaymentsExport();
        $documentExport
            ->records($records)
            ->company($company)
            ->establishment($establishment);
        return $documentExport->download('Reporte_Pagos_' . Carbon::now() . '.xlsx');
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

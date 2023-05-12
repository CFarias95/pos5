<?php

namespace Modules\Report\Http\Controllers;

use App\Models\Tenant\Catalogs\DocumentType;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade as PDF;
use Modules\Report\Exports\PurchaseExport;
use Illuminate\Http\Request;
use Modules\Report\Traits\ReportTrait;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\Company;
use App\Models\Tenant\PurchaseItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Purchase\Http\Controllers\PurchaseOrderController;
use Modules\Purchase\Models\PurchaseOrder;
use Modules\Purchase\Models\PurchaseOrderItem;
use Modules\Report\Exports\PurchaseOrderExport;
use Modules\Report\Http\Resources\PurchaseCollection;
use Modules\Report\Http\Resources\PurchaseOrderCollection;

class ReportPurchaseController extends Controller
{
    use ReportTrait;


    public function filter() {

        $document_types = DocumentType::whereIn('id', ['01', '03','GU75', 'NE76'])->get();

        $persons = $this->getPersons('suppliers');
        $sellers = $this->getSellers();

        $establishments = Establishment::all()->transform(function($row) {
            return [
                'id' => $row->id,
                'name' => $row->description
            ];
        });

        return compact('document_types','establishments', 'persons', 'sellers');
    }

    public function orderfilter() {

        $document_types = DocumentType::whereIn('id', ['01', '03','GU75', 'NE76'])->get();

        $persons = $this->getPersons('suppliers');
        $sellers = $this->getSellers();

        $establishments = Establishment::all()->transform(function($row) {
            return [
                'id' => $row->id,
                'name' => $row->description
            ];
        });

        $orders = PurchaseOrder::get();

        return compact('document_types','establishments', 'persons', 'sellers','orders');
    }

    public function index(Request $request)
    {
        $apply_conversion_to_pen = $this->applyConversiontoPen($request);

        return view('report::purchases.index', compact('apply_conversion_to_pen'));
    }

    public function orders(Request $request)
    {
        $apply_conversion_to_pen = $this->applyConversiontoPen($request);

        return view('report::purchases.quotation', compact('apply_conversion_to_pen'));
    }

    public function records(Request $request)
    {
        $records = $this->getRecords($request->all(), Purchase::class);

        return new PurchaseCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function orderRecords(Request $request)
    {
        $ordernC = $request->input('order');

        $compra = Purchase::where('purchase_order_id',$ordernC)->get();
        $records = null;
        if($compra->count() > 0){
            $records = PurchaseItem::where('purchase_id',$compra[0]->id)->paginate(config('tenant.items_per_page'));
            return new PurchaseOrderCollection($records);
        }else{
            $records = PurchaseItem::where('purchase_id','CARLOS')->paginate(config('tenant.items_per_page'));
            return new PurchaseOrderCollection($records);
        }
    }

    public function pdf(Request $request) {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;
        $records = $this->getRecords($request->all(), Purchase::class)->get();
        $filters = $request->all();

        $pdf = PDF::loadView('report::purchases.report_pdf', compact("records", "company", "establishment", "filters"))->setPaper('a4', 'landscape');

        $filename = 'Reporte_Compras_'.date('YmdHis');

        return $pdf->download($filename.'.pdf');
    }

    public function orderExcel(Request $request) {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;
        $ordernC = $request->input('order');
        $compra = Purchase::where('purchase_order_id',$ordernC)->get();
        $records1 = null;
        if($compra->count()>0){
            $records1 = PurchaseItem::where('purchase_id',$compra[0]->id)->paginate(100);
        }else{
            $records1 = PurchaseItem::where('purchase_id','CARLOS')->paginate(100);
        }
        $records = new PurchaseOrderCollection($records1);
        $filters = $request->all();

        Log::info("RECORDS: " . json_encode($records));
        return (new PurchaseOrderExport)
                ->records($records)
                ->company($company)
                ->establishment($establishment)
                ->filters($filters)
                ->download('Reporte_CompraVsOrdenCompra_'.Carbon::now().'.xlsx');

    }

    public function excel(Request $request) {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;
        $records = $this->getRecords($request->all(), Purchase::class)->get();
        $filters = $request->all();

        return (new PurchaseExport)
                ->records($records)
                ->company($company)
                ->establishment($establishment)
                ->filters($filters)
                ->download('Reporte_Compras_'.Carbon::now().'.xlsx');

    }
}

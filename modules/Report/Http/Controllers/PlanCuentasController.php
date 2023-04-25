<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Traits\OfflineTrait;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Traits\FinanceTrait;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Modules\Report\Http\Resources\PlanCuentasCollection;

class PlanCuentasController extends Controller
{
    use FinanceTrait;
    use OfflineTrait;
    use StorageDocument;

    protected $quotation;
    protected $account_entry;
    protected $company;


    public function index()
    {
        $company = Company::select('soap_type_id')->first();
        $soap_company = $company->soap_type_id;
        $generate_order_note_from_quotation = Configuration::getRecordIndividualColumn('generate_order_note_from_quotation');

        return view('report::plan_cuentas.index', compact('soap_company', 'generate_order_note_from_quotation'));
    }


    public function datosSP()
    {
        $sp = DB::connection('tenant')->select("CALL SP_PlanCuentas();");
        $collection = collect($sp);
        $per_page = (config('tenant.items_per_page'));
        $page = request() -> query('page') ?? 1;
        $paginatedItems = $collection->slice(($page - 1) * $per_page, $per_page)->all();
        $paginatedCollection = new LengthAwarePaginator($paginatedItems, count($collection),$per_page, $page);

        return new PlanCuentasCollection($paginatedCollection);
    }

    public function pdf(Request $request)
    {

        $company = Company::first();
        $records = DB::connection('tenant')->select("CALL SP_PlanCuentas();");
        $usuario_log = Auth::user();
        $fechaActual = date('d/m/Y');

        $pdf = PDF::loadView('report::plan_cuentas.plan_cuenta_pdf', compact("records", "company", "usuario_log", "request"));

        $filename = 'Reporte_Plan_Ventas_' . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }

    public function excel(Request $request)
    {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;

        $documentTypeId = null;
        if ($request->has('document_type_id')) {
            $documentTypeId = str_replace('"', '', $request->document_type_id);
        }
        $documentType = DocumentType::find($documentTypeId);
        if ($documentType != null) {
            $classType = $documentType->getCurrentRelatiomClass();
            $records = $this->getRecords($request->all(), $classType);
            $records = $records->get();
        } else {
            $records_documents = $this->getRecords($request->all(), Document::class)->select(
                'id',
                'document_type_id',
                'group_id',
                'soap_type_id',
                'date_of_issue',
                'time_of_issue',
                'currency_type_id',
                'series',
                'establishment_id',
                'number',
                'purchase_order',
                'state_type_id',
                'total_exportation',
                'total_exonerated',
                'total_unaffected',
                'total_free',
                'total_taxed',
                'total_igv',
                'total',
                'total_isc',
                'total_charge',
                'plate_number',
                'customer_id',
                'user_id',
                'seller_id'
            )->with(['person' => function ($query) {
                $query->select('id', 'name', 'number');
            }])->with(['soap_type' => function ($q) {
                $q->select('id', 'description');
            }])->with(['state_type' => function ($y) {
                $y->select('id', 'description');
            }])->with(['user' => function ($y) {
                $y->select('id', 'name');
            }])->get();

            $records_sales = $this->getRecords($request->all(), SaleNote::class)->select(
                'id',
                'state_type_id',
                'soap_type_id',
                'date_of_issue',
                'time_of_issue',
                'due_date',
                'currency_type_id',
                'series',
                'establishment_id',
                'number',
                'purchase_order',
                'total_exportation',
                'total_exonerated',
                'total_unaffected',
                'total_free',
                'total_taxed',
                'total_igv',
                'total',
                'total_isc',
                'plate_number',
                'observation',
                'document_id',
                'customer_id',
                'user_id',
                'seller_id'
            )->with(['customer' => function ($query) {
                $query->select('id', 'name', 'number');
            }])->with(['soap_type' => function ($q) {
                $q->select('id', 'description');
            }])->with(['state_type' => function ($y) {
                $y->select('id', 'description');
            }])->with(['user' => function ($y) {
                $y->select('id', 'name');
            }])->get();

            //$records_documents = $records_documents->put('class', 'Document');
            //$records_documents = $records_documents->put('class', 'SaleNote');
            $records = $records_documents->concat($records_sales);
        }


        $filters = $request->all();

        //get categories
        $categories = [];
        $categories_services = [];

        if ($request->include_categories == "true") {
            $categories = $this->getCategories($records, false);
            $categories_services = $this->getCategories($records, true);
        }

        $documentExport = new StatusClientExport();
        $documentExport
            ->records($records)
            ->company($company)
            ->establishment($establishment)
            ->filters($filters)
            ->categories($categories)
            ->categories_services($categories_services);
        // return $documentExport->view();
        return $documentExport->download('Reporte_estado_de_cuenta' . Carbon::now() . '.xlsx');
    }
}

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
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Report\Exports\PlanCuentasExport;
use Modules\Report\Http\Resources\PlanCuentasCollection;


class PlanCuentasController extends Controller
{
    use FinanceTrait;
    use OfflineTrait;
    use StorageDocument;

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
        $page = request()->query('page') ?? 1;
        $paginatedItems = $collection->slice(($page - 1) * $per_page, $per_page)->all();
        $paginatedCollection = new LengthAwarePaginator($paginatedItems, count($collection), $per_page, $page);
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

    public function excel()
    {
        $company = Company::first();
        $records = DB::connection('tenant')->select("CALL SP_PlanCuentas();");
        $usuario_log = Auth::user();
        $fechaActual = date('d/m/Y');

        $documentExport = new PlanCuentasExport();
        $documentExport
            ->records($records)
            ->company($company)
            ->usuario_log($usuario_log)
            ->fechaActual($fechaActual);

        return $documentExport->download('Reporte_plan_de_cuenta' . Carbon::now() . '.xlsx');
    }
}

<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Resources\Tenant\BalanceGeneralCollection;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Exports\BalanceGeneralExport;
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


class BalanceGeneralController extends Controller
{
    /*use FinanceTrait;
    use OfflineTrait;
    use StorageDocument;

    protected $company;*/


    public function index() {

        return view('tenant.balance_general.index');
    }


    public function datosSP(Request $request)
    {
        $detalle = null;
        //Log::info($request);
        if($request->d == 'true')
        {
            $detalle = 1;
        };
        if($request->d == 'false'){
            $detalle = 0;
        }
        //Log::info($detalle);
        $sp = DB::connection('tenant')->select("CALL SP_Balancegeneral(?,?,?);", [$detalle, $request->date_start, $request->date_end]);
        //Log::info($sp);
        $collection = collect($sp);
        $per_page = (config('tenant.items_per_page'));
        $page = request()->query('page') ?? 1;
        $paginatedItems = $collection->slice(($page - 1) * $per_page, $per_page)->all();
        $paginatedCollection = new LengthAwarePaginator($paginatedItems, count($collection), $per_page, $page);

        return new BalanceGeneralCollection($paginatedCollection);
    }

    public function pdf(Request $request)
    {
        $detalle = null;
        if($request->d == 'true')
        {
            $detalle = 1;
        };
        if($request->d == 'false'){
            $detalle = 0;
        }
        $company = Company::first();
        $records = DB::connection('tenant')->select("CALL SP_Balancegeneral(?,?,?);", [$detalle, $request->date_start, $request->date_end]);
        $usuario_log = Auth::user();
        $fechaActual = date('d/m/Y');

        $pdf = PDF::loadView('tenant.balance_general.balance_general_pdf', compact("records", "company", "usuario_log", "request"));

        $filename = 'Reporte_Balance_General_' . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }

    public function excel(Request $request)
    {
        $detalle = null;
        if($request->d == 'true')
        {
            $detalle = 1;
        };
        if($request->d == 'false'){
            $detalle = 0;
        }
        $company = Company::first();
        $records = DB::connection('tenant')->select("CALL SP_Balancegeneral(?,?,?);", [$detalle, $request->date_start, $request->date_end]);
        $usuario_log = Auth::user();
        $fechaActual = date('d/m/Y');

        $documentExport = new BalanceGeneralExport();
        $documentExport
            ->records($records)
            ->company($company)
            ->usuario_log($usuario_log)
            ->fechaActual($fechaActual);

        return $documentExport->download('Reporte_balance_general' . Carbon::now() . '.xlsx');
    }
}
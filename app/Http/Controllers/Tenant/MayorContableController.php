<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Resources\Tenant\MayorContableCollection;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Models\Tenant\AccountMovement;
use App\Traits\OfflineTrait;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Traits\FinanceTrait;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Exports\MayorContableExport;


class MayorContableController extends Controller
{
    /*use FinanceTrait;
    use OfflineTrait;
    use StorageDocument;

    protected $company;*/


    public function index() {

        return view('tenant.mayor_contable.index');
    }


    public function datosSP(Request $request)
    {
        //Log::info($request);
        $sp = DB::connection('tenant')->select("CALL SP_Mayorcontable(?,?,?);", [$request->date_start, $request->date_end,  $request->cuenta]);
        //Log::info($sp);
        $collection = collect($sp);
        $per_page = (config('tenant.items_per_page'));
        $page = request()->query('page') ?? 1;
        $paginatedItems = $collection->slice(($page - 1) * $per_page, $per_page)->all();
        $paginatedCollection = new LengthAwarePaginator($paginatedItems, count($collection), $per_page, $page);

        return new MayorContableCollection($paginatedCollection);
    }
    
    public function cuentas()
    {
        $cuentas = AccountMovement::get();
        $codigo = array();
        foreach($cuentas as $cuenta)
        {
            array_push($codigo, $cuenta->code);
        }
        return $codigo;
    }

    public function pdf(Request $request)
    {

        $company = Company::first();
        $records = DB::connection('tenant')->select("CALL SP_Mayorcontable(?,?,?);", [$request->date_start, $request->date_end,  $request->cuenta]);
        $usuario_log = Auth::user();
        $fechaActual = date('d/m/Y');

        $pdf = PDF::loadView('tenant.mayor_contable.mayor_contable_pdf', compact("records", "company", "usuario_log", "request"));
        $pdf->setPaper('A4', 'landscape');
        $filename = 'Reporte_Mayor_Contable_' . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }

    public function excel(Request $request)
    {
        $company = Company::first();
        $records = DB::connection('tenant')->select("CALL SP_Mayorcontable(?,?,?);", [$request->date_start, $request->date_end,  $request->cuenta]);
        $usuario_log = Auth::user();
        $fechaActual = date('d/m/Y');

        $documentExport = new MayorContableExport();
        $documentExport
            ->records($records)
            ->company($company)
            ->usuario_log($usuario_log)
            ->fechaActual($fechaActual);

        return $documentExport->download('Reporte_extracto_cuentas' . Carbon::now() . '.xlsx');
    }
}
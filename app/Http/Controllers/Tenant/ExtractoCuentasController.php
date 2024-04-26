<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Resources\Tenant\ExtractoCuentasCollection;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Exports\ExtractoCuentasExport;
use App\Models\Tenant\AccountMovement;
use App\Traits\OfflineTrait;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Traits\FinanceTrait;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;



class ExtractoCuentasController extends Controller
{
    /*use FinanceTrait;
    use OfflineTrait;
    use StorageDocument;

    protected $company;*/


    public function index() {

        return view('tenant.extracto_cuentas.index');
    }


    public function datosSP(Request $request)
    {
        //Log::info($request);
        $sp = DB::connection('tenant')->select("CALL SP_Extractocuenta(?,?,?);", [$request->date_start, $request->date_end,  $request->cuenta]);
        //Log::info($sp);
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

        return new ExtractoCuentasCollection($paginatedCollection);
    }

    public function cuentas()
    {
        $cuentas = AccountMovement::orderBy('code','asc')->get()->transform(function($row){
            return[
                'id'=> $row->code,
                'name' => $row->description,
            ];
        });

        return compact("cuentas");
    }

    public function pdf(Request $request)
    {

        $company = Company::first();
        $records = DB::connection('tenant')->select("CALL SP_Extractocuenta(?,?,?);", [$request->date_start, $request->date_end,  $request->cuenta]);
        $usuario_log = Auth::user();
        $fechaActual = date('d/m/Y');


        $pdf = PDF::loadView('tenant.extracto_cuentas.extracto_cuentas_pdf', compact("records", "company", "usuario_log", "request"));
        $pdf->setPaper('A4', 'landscape');
        $filename = 'Reporte_Extracto_Cuentas_' . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }

    public function excel(Request $request)
    {
        $company = Company::first();
        $records = DB::connection('tenant')->select("CALL SP_Extractocuenta(?,?,?);", [$request->date_start, $request->date_end,  $request->cuenta]);
        $usuario_log = Auth::user();
        $fechaActual = date('d/m/Y');

        $documentExport = new ExtractoCuentasExport();
        $documentExport
            ->records($records)
            ->company($company)
            ->usuario_log($usuario_log)
            ->fechaActual($fechaActual);

        return $documentExport->download('Reporte_extracto_cuentas' . Carbon::now() . '.xlsx');
    }
}

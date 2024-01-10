<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//use App\Models\Tenant\Establishment;
//use App\Models\Tenant\Document;
use App\Models\Tenant\Company;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\Report\Http\Resources\ReporteAnticiposClienteProveedorCollection;

class ReporteClienteProveedorAnticipoController extends Controller
{
    public function index() {

        return view('report::anticipos.index');
    }

    public function datosSP(Request $request)
    {
        $sp = DB::connection('tenant')->select("CALL SP_Reporte_Anticipo_ClienteProveedor(?,?);", [$request->date_start, $request->date_end]);
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

        return new ReporteAnticiposClienteProveedorCollection($paginatedCollection);
    }

    public function pdf(Request $request)
    {
        $company = Company::first();
        $records = DB::connection('tenant')->select("CALL SP_Reporte_Anticipo_ClienteProveedor(?,?);", [$request->date_start, $request->date_end]);
        
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

        $pdf = PDF::loadView('report::anticipos.anticipos_cliente_proveedor', compact("records", "company", "usuario_log", "request", "sp2"));

        $filename = 'Reporte_Anticipos_Cliente_Proveedor' . date('YmdHis');

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
        $pormeses = null;
        if($request->pormeses == 'false')
        {
            $pormeses = 0;
        }
        if($request->pormeses == 'true')
        {
            $pormeses = 1;
        }

        $company = Company::first();
        $records = DB::connection('tenant')->select("CALL SP_Balancegeneral(?,?,?,?);", [$detalle, $request->date_start, $request->date_end, $pormeses]);
        
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

        $documentExport = new BalanceGeneralExport();
        $documentExport
            ->records($records)
            ->company($company)
            ->usuario_log($usuario_log)
            ->fechaActual($fechaActual)
            ->sp2($sp2);

        return $documentExport->download('Reporte_balance_general' . Carbon::now() . '.xlsx');
    }
}
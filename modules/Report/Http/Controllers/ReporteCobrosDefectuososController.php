<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Company;
use App\Models\Tenant\Person;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\Report\Exports\CobrosDefectuososExport;
use Modules\Report\Http\Resources\ReporteCobrosDefectuososCollection;

class ReporteCobrosDefectuososController extends Controller
{
    public function index() {

        return view('report::cobros_defectuosos.index');
    }

    public function datosSP(Request $request)
    {
        $sp = DB::connection('tenant')->select("CALL SP_Cobros_Defectuosos(?,?,?,?);", [$request->client_id, $request->date_start, $request->date_end, $asiento]);
        //Log::info($sp);
        $total = 0;
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
        foreach($sp as $row)
        {
            if((float)$row->Valor_pagado >= 0)
            {
                $total += (float)$row->Valor_pagado;
            }
        }

        $collection = collect($sp);
        $per_page = (config('tenant.items_per_page'));
        $page = request()->query('page') ?? 1;
        $paginatedItems = $collection->slice(($page - 1) * $per_page, $per_page)->all();
        $paginatedCollection = new LengthAwarePaginator($paginatedItems, count($collection), $per_page, $page);
        $paginatedCollection['datos'] = $sp2;
        $paginatedCollection['total'] = $total;

        return new ReporteCobrosDefectuososCollection($paginatedCollection);
    }

    public function tables()
    {
        $persons = Person::where('type', 'customers')->get()->transform(function($row) {
            return [
                'id' => $row->id,
                'name' => $row->name
            ];
        });
        /*Log::info('persons - '.json_encode($persons));

        array_push($persons, ['id'=>'0', 'name'=>'Todos Clientes']);*/

        return compact('persons');
    }

    public function pdf(Request $request)
    {
        $company = Company::first();
        $records = DB::connection('tenant')->select("CALL SP_Cobros_Defectuosos(?,?,?,?);", [$request->client_id, $request->date_start, $request->date_end, $request->asiento]);
        
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

        $pdf = PDF::loadView('report::cobros_defectuosos.cobros_defectuosos_pdf', compact("records", "company", "usuario_log", "request", "sp2"))->setPaper('a4', 'landscape');

        $filename = 'Reporte_Cobros_Efectuados_' . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }

    public function excel(Request $request)
    {
        $company = Company::first();
        $records = DB::connection('tenant')->select("CALL SP_Cobros_Defectuosos(?,?,?,?);", [$request->client_id, $request->date_start, $request->date_end, $request->asiento]);
        
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

        $documentExport = new CobrosDefectuososExport();
        $documentExport
            ->records($records)
            ->company($company)
            ->usuario_log($usuario_log)
            ->fechaActual($fechaActual)
            ->sp2($sp2);

        return $documentExport->download('Reporte_Cobros_Efectuados_' . Carbon::now() . '.xlsx');
    }
}
<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportDinardapController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('report::dinardap.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }
    /**
     * Generate and download an Excel file based on the DINARDAP report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function report(Request $request)
    {
        $inicio = $request->input('inicio');
        $fin = $request->input('fin');
        $corte = $request->input('corte');
        $company = Company::first()->number;

        // Execute the stored procedure
        $results = DB::connection('tenant')->select("CALL SP_Dinardap(?, ?, ?)", [$inicio, $fin, $corte]);

        // Generate the content for the txt file
        $content = '';
        foreach ($results as $row) {
            $line = implode('|', array_values((array)$row));
            $content .= $line . "\n";
        }

        // Generate the filename based on the end date
        $filename = $company.date('dmY', strtotime($fin)) . '.txt';

        // Create a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'dinardap_');
        file_put_contents($tempFile, $content);

        // Return the file as a download
        return response()->download($tempFile, $filename, [
            'Content-Type' => 'text/plain',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Retrieve records based on specified date ranges.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function records(Request $request)
    {
        $inicio = $request->input('inicio');
        $fin = $request->input('fin');
        $corte = $request->input('corte');

        // Execute the stored procedure
        $results = DB::connection('tenant')->select("CALL SP_Dinardap(?, ?, ?)", [$inicio, $fin, $corte]);

        // Return the results
        return $results;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

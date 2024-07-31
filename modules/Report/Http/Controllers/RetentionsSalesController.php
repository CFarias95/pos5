<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

class RetentionsSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('report::sale_retentions.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }
    /**
     * Get the list of customers for the tables.
     *
     * @return \Illuminate\Http\Response
     */
    public function tables()
    {
        $customers = DB::connection('tenant')
            ->table('persons')
            ->where('type', 'customers')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return compact('customers');
    }

    /**
     * Retrieve records for retention sales.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function records(Request $request)
    {
        try{

            $customer_id = $request->input('customer_id');
            $month = $request->input('month') ?: '00';
            $type = $request->input('type');

            // Execute the stored procedure in the tenant database
            $records = DB::connection('tenant')->select("CALL SP_retentions_customers(?, ?, ?)", [$customer_id, $month, $type]);

            // Return the records response
            return[
                'success' => true,
                'data' => $records
            ];

        }catch(Exception $ex){
            Log::error("No se puedieron recuperar los datos del SP SP_retentions_customers");
            Log::error($ex->getMessage());
            return[
                'success' => false,
                'message' => 'No se pudieron recuperar los datos'
            ];
        }

    }

    /**
     * Generate and download an Excel file with retention sales data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function excel(Request $request)
    {
        try {
            $customer_id = $request->input('customer_id');
            $month = $request->input('month') ?: '00';
            $type = $request->input('type');

            // Execute the stored procedure in the tenant database
            $records = DB::connection('tenant')->select("CALL SP_retentions_customers(?, ?, ?)", [$customer_id, $month, $type]);

            $processedData = array_map(function ($row) {
                return array_map(function ($value) {
                    if (is_object($value)) {
                        // Convert object to string representation
                        return json_encode($value);
                    } elseif (is_array($value)) {
                        // Handle nested arrays
                        return json_encode($value);
                    }
                    return $value;
                }, (array)$row);
            }, $records);

            // Create a new Excel file
            $excel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $excel->getActiveSheet();

            // Add headers
            if (!empty($records)) {
                $headers = array_keys((array)$records[0]);
                $sheet->fromArray([$headers], NULL, 'A1');
            }

            // Add data
            $sheet->fromArray($processedData, NULL, 'A2');

            // Create the Excel writer
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($excel);

            // Set the file name
            $fileName = 'Extracto_retenciones_Ventas_' . date('Ymd') . '.xlsx';

            // Create a temporary file
            $tempFile = tempnam(sys_get_temp_dir(), $fileName);

            // Save the Excel file to the temporary file
            $writer->save($tempFile);

            // Return the file as a download
            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);

        } catch(Exception $ex) {
            Log::error("No se pudo generar el archivo Excel de SP_retentions_customers");
            Log::error($ex->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'No se pudo generar el archivo Excel'
            ], 500);
        }
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

<?php

namespace Modules\Account\Http\Controllers;


use Carbon\Carbon;
use App\Models\Tenant\Item;
use Illuminate\Http\Request;
use App\Models\Tenant\Document;
use App\Http\Controllers\Controller;
use App\Models\Tenant\AccountingEntries;
use App\Models\Tenant\AccountingEntryItems;
use App\Models\Tenant\AccountMovement;
use App\Models\Tenant\Advance;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\PurchasePayment;
use App\Models\Tenant\Retention;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Account\Exports\ReconciliationExport;
use Modules\Account\Models\CompanyAccount;
use Modules\Account\Exports\ReportAccountingAdsoftExport;
use Modules\Account\Exports\ReportAccountingConcarExport;
use Modules\Account\Exports\ReportAccountingFoxcontExport;
use Modules\Account\Exports\ReportAccountingContasisExport;
use Modules\Account\Exports\ReportAccountingSumeriusExport;
use Modules\Account\Http\Resources\BankReconciliationCollection;
use Modules\Account\Http\Resources\ReconciliationCollection;
use Modules\Account\Models\BankReconciliation;
use Barryvdh\DomPDF\Facade as PDF;
use App\Models\Tenant\User;

class BankReconciliationController extends Controller
{
    public function index()
    {
        return view('account::bank_reconciliation.index');
    }

    public function download(Request $request)
    {
        $type = $request->input('type');
        $month = $request->input('month');

        $d_start = Carbon::parse($month . '-01')->format('Y-m-d');
        $d_end = Carbon::parse($month . '-01')->endOfMonth()->format('Y-m-d');

        $records = $this->getDocuments($d_start, $d_end);
        $filename = 'Reporte_' . ucfirst($type) . '_Ventas_' . date('YmdHis');

        switch ($type) {
            case 'concar':
                $data = [
                    'records' => $this->getStructureConcar($this->getAllDocuments($d_start, $d_end)),
                ];

                $report = (new ReportAccountingConcarExport)
                    ->data($data)
                    ->download($filename . '.xlsx');

                return $report;

            case 'siscont':

                $records = $this->getStructureSiscont($records);

                $temp = tempnam(sys_get_temp_dir(), 'txt');
                $file = fopen($temp, 'w+');
                foreach ($records as $record) {
                    $line = implode('', $record);
                    fwrite($file, $line . "\r\n");
                }
                fclose($file);

                return response()->download($temp, $filename . '.txt');

            case 'foxcont':

                $data = [
                    'records' => $this->getStructureFoxcont($records),
                ];

                return (new ReportAccountingFoxcontExport)
                    ->data($data)
                    ->download($filename . '.xlsx');

            case 'contasis':

                $data = [
                    'records' => $this->getStructureContasis($records),
                ];

                return (new ReportAccountingContasisExport)
                    ->data($data)
                    ->download($filename . '.xlsx');

            case 'adsoft':

                $data = [
                    'records' => $this->getStructureAdsoft($records),
                ];

                return (new ReportAccountingAdsoftExport)
                    ->data($data)
                    ->download($filename . '.xlsx');
            case 'sumerius':

                $data = [
                    'records' => $this->getStructureSumerius($records),
                ];

                return (new ReportAccountingSumeriusExport)
                    ->data($data)
                    ->download($filename . '.xlsx');
        }
    }

    //recupera todos los datos de accounting entrys items
    public function records(Request $request)
    {
        $records = $this->getRecords($request);
        return new BankReconciliationCollection($records->paginate(config('tenant.items_per_page')));
    }

    // Retorna la informacion aplicando filtros
    public function getRecords($request)
    {
        $fecha = $request->month;
        $cta = $request->account_id;

        $records = BankReconciliation::query();

        if (isset($fecha)) {

            $records->where('month',$fecha.'-01');
        }
        if (isset($cta)) {
            $records->where('account_id',$cta);
        }

        return $records->orderBy('id','desc');
    }

    //retorna la lista de valores para filtrar
    public function columns()
    {
        $ctas = AccountMovement::get();
        $ctas = $ctas->transform(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->code.' - '.$row->description
            ];
        });

        return compact('ctas');
    }

    //funciona para puntear los asientos contables
    public function reconciliate($reconciliationId,$id)
    {
        $record = AccountingEntryItems::findOrFail($id);
        if ($record) {
            $record->bank_reconciliated = true;
            $record->bank_reconciliation_id = $reconciliationId;
            $record->date_bank_reconciliated = date('Y-m-d H:i:s');
            $record->save();
            return [
                'success' => true,
                'message' => "Conciliacion registrada"
            ];
        } else {
            return [
                'success' => false,
                'message' => "No se pudo conciliar el registro"
            ];
        }
    }

    public function unconciliate($id)
    {
        $record = AccountingEntryItems::findOrFail($id);
        if ($record) {
            $record->bank_reconciliated = false;
            $record->bank_reconciliation_id = null;
            $record->date_bank_reconciliated = null;
            $record->save();
            return [
                'success' => true,
                'message' => "Se desconcilio el movimiento"
            ];
        } else {
            return [
                'success' => false,
                'message' => "No se pudo desconciliar el registro"
            ];
        }
    }



    public function excel(Request $request)
    {
        $records = $this->getRecords($request);
        $records= new ReconciliationCollection($records->get());

        $company = Company::get();

        Log::info("Datos enviado");
        Log::info(json_encode($records));

        return (new ReconciliationExport)
        ->company($company)
        ->records($records)
        ->download('Punteo_Contable' . '.xlsx');

    }

    public function pdf($id)
    {
        $records = BankReconciliation::where('id',$id)->get();
        $monthDate = substr($records->first()->month, 0, -3);
        $startDate = date('Y-m-01', strtotime("$monthDate +1 month"));
        //Log::info('monthDate - '.$monthDate);
        //Log::info('startDate - '.$startDate);
        $company = Company::first();
        $usuario_log = Auth::user();
        $fechaActual = date('d/m/Y');
        $user = User::where('id', $records[0]->user_id)->first();
        $account = AccountMovement::where('id', $records[0]->account_id)->first();
        $entries = AccountingEntryItems::where('bank_reconciliation_id', $records[0]->id)->with('account')->get();
        
        $data = AccountingEntryItems::query();

        if($account->id){
            $data->where('account_movement_id',$account->id);
        }

        if($monthDate){

            $startDate = date('Y-m-31', strtotime($monthDate));

            $mov = AccountingEntries::where('seat_date', '<=', $startDate)->orderBy('seat_date','asc')->get()->transform(function($row){
                return[
                    'id' =>$row->id
                ];
            });
            //Log::info('mov - '.$mov);

            $data->whereIn('accounting_entrie_id',$mov);
            //Log::info('data1 - '.json_encode($data->get()));
        }

        $data->orderBy('accounting_entrie_id', 'asc')->get()->transform(function($row){
            $accountingEntrie = AccountingEntries::find($row->accounting_entrie_id);
            return[
                'entry' => $accountingEntrie->filename,
                'date' => $accountingEntrie->seat_date,
                'comment' => $accountingEntrie->comment,
                'debe' => round($row->debe,2),
                'haber' => round($row->haber,2),
                'bank_reconciliated' => $row->bank_reconciliated,
                'id' => $row->id,
            ];
        });
        
        $data1 = $data->get();
        //Log::info('data - '.json_encode($data1));

        $pdf = PDF::loadView('account::bank_reconciliation.pdf', compact("records", "company", "fechaActual", "usuario_log", "user", "account", "entries", "data1"));

        $filename = 'Bank_Reconciliation_' .  date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }

    public function movements(Request $request){

        $account = $request->account_id;
        $month = $request->month;
        $id = $request->id;

        if(strlen($month) > 7){
            $month = substr($month,0,7);
        }


        $data = AccountingEntryItems::query();

        if($account){
            $data->where('account_movement_id',$account);
        }

        if($month){

            $mov = AccountingEntries::where('seat_date','like',$month.'%')->orderBy('seat_date','asc')->get()->transform(function($row){
                return[
                    'id' =>$row->id
                ];
            });

            $data->whereIn('accounting_entrie_id',$mov);
        }


        return $data->orderBy('accounting_entrie_id', 'asc')->get()->transform(function($row){
            $accountingEntrie = AccountingEntries::find($row->accounting_entrie_id);
            return[
                'entry' => $accountingEntrie->filename,
                'date' => $accountingEntrie->seat_date,
                'comment' => $accountingEntrie->comment,
                'debe' => round($row->debe,2),
                'haber' => round($row->haber,2),
                'bank_reconciliated' => $row->bank_reconciliated,
                'id' => $row->id,
            ];
        });
    }

    public function store( Request $request){
        $id = $request->id;
        try{
            if($id){
                $record = BankReconciliation::find($id);
                $record->fill($request->toArray());
                $record->save();
                return[
                    'success'=>true,
                    'message'=>'Se ha actualizado la información correctamente.'
                ];
            }else{
                $validar = BankReconciliation::where('account_id',$request->account_id)->where('month',$request->month.'-01')->get();
                if($validar && $validar->count() > 0){
                    return[
                        'success'=>false,
                        'message'=>'Ya se encuentra una conciliacion para la cuenta en la fecha '.$request->month
                    ];
                }
                $record = new BankReconciliation();
                $record->fill($request->toArray());
                $record->month = $request->month.'-01';
                $record->user_id = auth()->user()->id;
                $record->save();
                return[
                    'success'=>true,
                    'message'=>'Se ha generado la conciliacion bancaria con éxito.',
                ];
            }
        }catch(Exception $ex){
            Log::error('Error BankReconciliationController '.$ex->getMessage());
            return[
                'success'=> false,
                'message'=>'Se ha generado un error en BankReconciliationController '.$ex->getMessage()
            ];
        }
    }

    public function record($id){
        $data = BankReconciliation::find($id);
        return $data;
    }

    public function close($id){

        $data = BankReconciliation::find($id);
        $data->status = 1;
        $data->save();
        return [
            'success'=> true,
            'message'=> 'La conciliación se ha cerrado exitosamente.'
        ];
    }

}

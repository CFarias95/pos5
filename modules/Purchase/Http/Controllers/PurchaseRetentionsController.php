<?php

namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AccountingEntries;
use App\Models\Tenant\AccountingEntryItems;
use App\Models\Tenant\AccountMovement;
use App\Models\Tenant\Advance;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\CreditNotesPayment;
use Modules\Purchase\Http\Resources\PurchasePaymentCollection;
use Modules\Purchase\Http\Requests\PurchasePaymentRequest;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Person;
use App\Models\Tenant\PurchasePayment;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchaseFee;
use App\Models\Tenant\Retention;
use App\Models\Tenant\RetentionTypePurchase;
use App\Models\Tenant\RetentionTypesPurchase;
use Exception;
use Illuminate\Http\Request;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Finance\Traits\FilePaymentTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Finance\Http\Controllers\ToPayController;
use Modules\Finance\Models\GlobalPayment;
use Modules\Purchase\Http\Resources\PurchaseRetentionsCollection;

class PurchaseRetentionsController extends Controller
{
    use FinanceTrait, FilePaymentTrait;
    public function index()
    {
        return view('purchase::purchase-retentions.index');
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
        return new PurchaseRetentionsCollection($records->paginate(config('tenant.items_per_page')));
    }

    // Retorna la informacion aplicando filtros
    public function getRecords($request)
    {
        $fecha = $request->month;
        $cta = $request->account_id;

        $records = RetentionTypesPurchase::query();

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
                'message' => "Conciliacion reguistrada"
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

            $mov = AccountingEntries::where('seat_date','like',$month.'%')->get()->transform(function($row){
                return[
                    'id' =>$row->id
                ];
            });

            $data->whereIn('accounting_entrie_id',$mov);
        }


        return $data->get()->transform(function($row){
            $accountingEntrie = AccountingEntries::find($row->accounting_entrie_id);
            return[
                'entry' => $accountingEntrie->filename,
                'date' => $accountingEntrie->seat_date,
                'comment' => $accountingEntrie->comment,
                'debe' => $row->debe,
                'haber' => $row->haber,
                'bank_reconciliated' => $row->bank_reconciliated,
                'id' => $row->id,
            ];
        });
    }

    public function store( Request $request){
        $type = $request->type;
        $id = $request->id;
        try{
            if($type == 'Edit'){
                $record = RetentionTypesPurchase::find($id);
                $record->fill($request->toArray());
                $record->save();
                return[
                    'success'=>true,
                    'message'=>'Se ha actualizado la información correctamente.'
                ];
            }else{

                if(isset($request->code2) && $request->code2 != null  && $request->code2 != ''){
                    $validar = RetentionTypesPurchase::where('code',$request->code)->where('code2',$request->code2)->first();
                }else{
                    $validar = RetentionTypesPurchase::where('code',$request->code)->first();
                }

                $lastID = RetentionTypesPurchase::orderBy('id', 'desc')->first();
                if($validar && $validar->count() > 0){
                    return[
                        'success'=>false,
                        'message'=>'El código {$id} ya se encuentra registrado'
                    ];
                }
                $record = new RetentionTypesPurchase();
                $record->fill($request->toArray());
                $record->id = ($lastID) ? intVal($lastID->id) + 1: '01';
                $record->save();
                return[
                    'success'=>true,
                    'message'=>'Se ha registrado un nuevo tipo de retencion',
                ];
            }
        }catch(Exception $ex){
            Log::error('Error PurchaseRetentionsController '.$ex->getMessage());
            return[
                'success'=> false,
                'message'=>'Se ha generado un error en PurchaseRetentionsController '.$ex->getMessage()
            ];
        }
    }

    public function record($id){
        $data = RetentionTypesPurchase::find($id);
        return $data;
    }

}

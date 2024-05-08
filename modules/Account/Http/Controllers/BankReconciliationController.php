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

    public function pdfConciliations($id){
        $bankReconciliation = BankReconciliation::where('id',$id)->first();
        $monthsEnd = substr($bankReconciliation->month, 0, -3).'-31';
        $monthsStart = $bankReconciliation->month;
        Log::info('monthDate - '.$monthsEnd);
        Log::info('startDate - '.$monthsStart);
        $company = Company::first();
        $usuario_log = Auth::user();
        $fechaActual = date('d/m/Y');
        $user = User::where('id', $bankReconciliation->user_id)->first();
        $account = AccountMovement::where('id', $bankReconciliation->account_id)->first();

        $saldo_contable = AccountingEntryItems::where('account_movement_id',$bankReconciliation->account_id)->where('bank_reconciliated',1);
        $saldo_contable->join('accounting_entries', function ($join) use($monthsStart) {
            $join->on('accounting_entry_items.accounting_entrie_id', '=', 'accounting_entries.id')
                ->where('accounting_entries.seat_date','<',$monthsStart);
        });

        //Log::info('Saldo Contable: '.json_encode($saldo_contable->get()));

        $SaldoDebe = $saldo_contable->sum('debe');
        $SaldoHaber = $saldo_contable->sum('haber');

        Log::info('Total Debe: '.$SaldoDebe);
        Log::info('Total Haber: '.$SaldoHaber);

        $SaldoContableValue = 0;
        $SaldoContableValue += $SaldoDebe - $SaldoHaber;

        Log::info('Saldo Contable '.$SaldoContableValue);
        Log::info('Conciliados: '.json_encode($saldo_contable->get()));

    }

    public function pdf($id)
    {
        $bankReconciliation = BankReconciliation::where('id',$id)->first();
        $monthsEnd = substr($bankReconciliation->month, 0, -3).'-31';
        $monthsStart = $bankReconciliation->month;
        Log::info('monthDate - '.$monthsEnd);
        Log::info('startDate - '.$monthsStart);
        $company = Company::first();
        $usuario_log = Auth::user();
        $fechaActual = date('d/m/Y');
        $user = User::where('id', $bankReconciliation->user_id)->first();
        $account = AccountMovement::where('id', $bankReconciliation->account_id)->first();

        $saldo_contable = AccountingEntryItems::where('account_movement_id',$bankReconciliation->account_id)->where('bank_reconciliated',1);
        $saldo_contable->join('accounting_entries', function ($join) use($monthsStart,$monthsEnd) {
            $join->on('accounting_entry_items.accounting_entrie_id', '=', 'accounting_entries.id')
                ->where('accounting_entries.seat_date','>=',$monthsStart)
                ->where('accounting_entries.seat_date','<=',$monthsEnd);
        });

        //Log::info('Saldo Contable: '.json_encode($saldo_contable->get()));

        $SaldoDebe = $saldo_contable->sum('debe');
        $SaldoHaber = $saldo_contable->sum('haber');

        Log::info('Total Debe: '.$SaldoDebe);
        Log::info('Total Haber: '.$SaldoHaber);

        $SaldoContable = 0;
        $SaldoContable = $SaldoDebe - $SaldoHaber;
        $saldosIniciales = AccountingEntryItems::where('account_movement_id',$bankReconciliation->account_id)->where('bank_reconciliated',0)
                            ->join('accounting_entries', function ($join) use($monthsStart) {
                                $join->on('accounting_entry_items.accounting_entrie_id', '=', 'accounting_entries.id')
                                    ->where('accounting_entries.seat_date','<',$monthsStart)
                                    ->where('accounting_entries.comment','like','%Asiento Inicial%');
                            })->get();

        Log::info('Saldo Contable '.$SaldoContable);
        Log::info('Saldo Inicial: '.json_encode($saldosIniciales));

        if($saldosIniciales->count() > 0){
            foreach($saldosIniciales as $sini){
                $SaldoContable += $sini->debe;
                $SaldoContable -= $sini->haber;
                Log::info('Saldo Inicial DEBE:'.$sini->debe . ' - HABER:'. $sini->haber);
            }
        }



        $chequesGNC = [];
        $chequesGNCTotales = 0;

        //RECUPERAR LOS CHEQUES GIRADOS NO COBRADOS
        $PurchasePaymnets = PurchasePayment::where('payment_method_type_id','13')->where('date_of_payment','<=', $monthsEnd)
                                            ->select(DB::raw('CONCAT("PC",id) as id1, CONCAT("%PC",id,";%") as id2'));

        if($PurchasePaymnets->count() > 0 ){
            Log::info('PurchasePaymnets: '.json_encode($PurchasePaymnets->get()));
            $accountingEntries = AccountingEntries::where('seat_date','<=', $monthsEnd);
            $accountingEntries->joinSub($PurchasePaymnets,'purchase_paymentsP', function ($join){
                $join->on('accounting_entries.document_id','purchase_paymentsP.id1')
                     ->orOn('accounting_entries.document_id', 'like','purchase_paymentsP.id2');
            });

            $chequesGNC = AccountingEntryItems::where('account_movement_id',$bankReconciliation->account_id)->where('bank_reconciliated',0);
            $chequesGNC->joinSub($accountingEntries,'accounting_entries', function ($join) use($monthsEnd) {
                $join->on('accounting_entry_items.accounting_entrie_id', 'accounting_entries.id');
            });
            $chequesGNC->join('accounting_entries','accounting', function ($join) use($monthsStart,$monthsEnd) {
                $join->on('accounting_entry_items.accounting_entrie_id', 'accounting.id')
                    ->where('accounting.comment','like','%CHEQUE GIRADO Y NO COBRADO%')
                    ->where('accounting.seat_date','<=', $monthsEnd)
                    ->where('accounting_entries.seat_date','>=',$monthsStart);
            });

            $chequesGNC = $chequesGNC->get()->transform(function($row) use($chequesGNCTotales){
                return[
                    'entry' => $row->filename,
                    'date' => $row->seat_date,
                    'comment' => $row->comment,
                    'debe' => round($row->debe,2),
                    'haber' => round($row->haber,2) * -1,
                    'bank_reconciliated' => $row->bank_reconciliated,
                    'id' => $row->id,
                ];
            });
            $chequesGNCTotales += $chequesGNC->sum('debe') + $chequesGNC->sum('haber');
            $accountingEntriesIds = $accountingEntries->get()->transform(function($row){
                return[
                    'id'=>$row->id
                ];
            });
            Log::info('chequesGNC: '.json_encode($chequesGNC));
        }


        ////RECUPERAR LOS CHEQUES ANTICIPADOS
        $chequesANT = [];
        $chequesANTTotales = 0;
        $DocumentPayments = DocumentPayment::where('payment_method_type_id','13')->where('date_of_payment','<=', $monthsEnd)
                                            ->select(DB::raw('CONCAT("CF",id) as id1, CONCAT("%CF",id,";%") as id2'));
        if($DocumentPayments->count() > 0 ){
            Log::info('DocumentPayments: '.json_encode($DocumentPayments->get()));
            $accountingEntriesD = AccountingEntries::where('seat_date','<=', $monthsEnd);
            $accountingEntriesD->joinSub($DocumentPayments,'document_paymentsD', function ($join){
                $join->on('accounting_entries.document_id','document_paymentsD.id1')
                        ->orOn('accounting_entries.document_id', 'like','document_paymentsD.id2');
            });

            $chequesANT = AccountingEntryItems::where('account_movement_id',$bankReconciliation->account_id)->where('bank_reconciliated',0);
            $chequesANT->joinSub($accountingEntriesD,'accounting_entriesD', function ($join) {
                $join->on('accounting_entry_items.accounting_entrie_id', '=', 'accounting_entriesD.id');
            });

            $chequesANT = $chequesANT->get()->transform(function($row) use ($chequesANTTotales){
                return[
                    'entry' => $row->filename,
                    'date' => $row->seat_date,
                    'comment' => $row->comment,
                    'debe' => round($row->debe,2),
                    'haber' => round($row->haber,2) * -1,
                    'bank_reconciliated' => $row->bank_reconciliated,
                    'id' => $row->id,
                ];
            });
            $chequesANTTotales += $chequesANT->sum('debe') + $chequesANT->sum('haber');
            $accountingEntriesDIds = $accountingEntriesD->get()->transform(function($row){
                return[
                    'id'=>$row->id
                ];
            });
            Log::info('chequesANT: '.json_encode($chequesANT));
        }


        //DEPOSITOS NO EFECTIVIZADOS
        $depositosNETotales = 0;
        $accountingEntriesNE = AccountingEntries::where('seat_date','<=', $monthsEnd);
        $depositosNE = AccountingEntryItems::where('account_movement_id',$bankReconciliation->account_id)->where('bank_reconciliated',0);
        $depositosNE->joinSub($accountingEntriesNE,'accounting_entries_ne', function ($join) {
            $join->on('accounting_entry_items.accounting_entrie_id', '=', 'accounting_entries_ne.id')
            ->where('accounting_entries_ne.comment','not like','%Asiento Inicial%');
        });

        if(isset($accountingEntriesDIds)){
            $depositosNE->whereNotIn('accounting_entrie_id',$accountingEntriesDIds);
        }
        if(isset($accountingEntriesIds)){
            $depositosNE->whereNotIn('accounting_entrie_id',$accountingEntriesIds);
        }
        $depositosNE->join('accounting_entries', function ($join) {
            $join->on('accounting_entry_items.accounting_entrie_id', '=', 'accounting_entries.id');
        });
        $depositosNE = $depositosNE->get()->transform(function($row) use($depositosNETotales){
            return[
                'entry' => $row->filename,
                'date' => $row->seat_date,
                'comment' => $row->comment,
                'debe' => round($row->debe,2),
                'haber' => round($row->haber,2) * -1,
                'bank_reconciliated' => $row->bank_reconciliated,
                'id' => $row->id,
            ];
        });
        $depositosNETotales +=  $depositosNE->sum('debe') + $depositosNE->sum('haber');
        $pdf = PDF::loadView('account::bank_reconciliation.pdf', compact("bankReconciliation", "company", "fechaActual", "usuario_log", "user", "account","chequesGNC","chequesGNCTotales", "chequesANT","chequesANTTotales", "depositosNE","SaldoContable",'depositosNETotales'));
        $filename = 'Bank_Reconciliation_' .  date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }

    public function movements(Request $request){

        $account = $request->account_id;
        $month = $request->month;
        $id = $request->id;
        $bankReconciliation = BankReconciliation::where('id',$id)->first();
        $monthsEnd = substr($bankReconciliation->month, 0, -3).'-31';
        $monthsStart = $bankReconciliation->month;


        if(strlen($month) > 7){
            $month = substr($month,0,7);
        }


        $data = AccountingEntryItems::query();

        if($account){
            $data->where('account_movement_id',$account);
        }

        if($month){

            $mov = AccountingEntries::where('comment','not like','%Asiento Inicial%')->where('seat_date','>=',$monthsStart)->where('seat_date','<=',$monthsEnd)->orderBy('seat_date','asc')->get()->transform(function($row){
                return[
                    'id' =>$row->id
                ];
            });

            $movementsExtras = AccountingEntryItems::where('account_movement_id',$account)
                                ->where('bank_reconciliated',0)
                                ->join('accounting_entries', function ($join) use($monthsStart) {
                                    $join->on('accounting_entry_items.accounting_entrie_id', '=', 'accounting_entries.id')
                                        ->where('accounting_entries.seat_date','<',$monthsStart)
                                        ->where('accounting_entries.comment','not like','%Asiento Inicial%');
                                })->select(DB::raw('accounting_entries.id'))->get()
                                ->transform(function($row){
                                    return[
                                        'id' =>$row->id
                                    ];
                                });
            //Log::info('anterioies: ');
            //Log::info(json_encode($movementsExtras));
            $dataArray = array_merge($mov->toArray(),$movementsExtras->toArray());
            //Log::info('Arrays: '.json_encode($dataArray));
            $data->whereIn('accounting_entrie_id',$dataArray);
            //$data->whereIn('accounting_entrie_id',$movementsExtras);
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

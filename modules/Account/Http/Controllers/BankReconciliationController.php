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
use Modules\Account\Http\Resources\ReconciliationCollection;

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

        //return $records->get();
        return new ReconciliationCollection($records->paginate(config('tenant.items_per_page')));
    }

    // Retorna la informacion aplicando filtros
    public function getRecords($request)
    {
        $fecha = $request->date;
        $cta = $request->cta;
        $include = $request->include;
        $reference = $request->reference;

        $records = AccountingEntries::where('document_id', 'like', 'CF%');
        $records2 = AccountingEntries::where('document_id', 'like', 'PC%');
        $records3 = AccountingEntries::where('document_id', 'not like', 'PC%')->where('document_id', 'not like', 'CF%');

        if(isset($reference) && $reference != ''){

            $reference = str_replace(' ','',$reference);

            if(str_contains($reference,'A,')){
                Log::info('Referencia a buscr : '.str_replace('A,','',$reference));
                $id = Advance::select('id')->where('reference','like','%'. trim(str_replace('A,','',$reference)).'%')->get();

                $idPDocu = DocumentPayment::whereIn('reference',$id)->get()->transform(function($row){return['CF'.$row->id];});
                $idPPurc = PurchasePayment::whereIn('reference',$id)->get()->transform(function($row){return['PC'.$row->id];});

                $records->whereIn('document_id',$idPDocu);
                $records2->whereIn('document_id',$idPPurc);
                //$records3->whereIn('document_id',$idPPurc)->whereIn('document_id',$idPDocu);

            }
            elseif(str_contains($reference,'R,')){
                Log::info('Retencial a buscar : '.str_replace('R,','',$reference));
                $id = Retention::select('id')->where('ubl_version','like','%'. trim(str_replace('R,','',$reference)).'%')->get();

                $idPDocu = DocumentPayment::whereIn('reference',$id)->get()->transform(function($row){return['CF'.$row->id];});
                $idPPurc = PurchasePayment::whereIn('reference',$id)->get()->transform(function($row){return['PC'.$row->id];});

                $records->whereIn('document_id',$idPDocu);
                $records2->whereIn('document_id',$idPPurc);
                //$records3->whereIn('document_id',$idPPurc)->whereIn('document_id',$idPDocu);

            }else{

                $idPDocu = DocumentPayment::select('id')->where('reference','like','%'.$reference.'%')->get()->transform(function($row){return['CF'.$row->id];});
                $idPPurc = PurchasePayment::select('id')->where('reference','like','%'.$reference.'%')->get()->transform(function($row){return['PC'.$row->id];});

                $records->whereIn('document_id',$idPDocu);
                $records2->whereIn('document_id',$idPPurc);
                //$records3->whereIn('document_id',$idPPurc)->whereIn('document_id',$idPDocu);
            }
        }

        if (isset($fecha)) {
            $records->where('seat_date', $fecha);
            $records2->where('seat_date', $fecha);
            $records3->where('seat_date', $fecha);
        }

        $records->join('accounting_entry_items', function ($join) use($include,$cta){
            $join->on('accounting_entry_items.accounting_entrie_id', '=', 'accounting_entries.id');
            if(isset($include) && $include != 2){
                $join->where('accounting_entry_items.reconciliation','=',$include);
            }
            if(isset($cta)){
                $join->where('accounting_entry_items.account_movement_id','=', $cta);
            }
        });
        $records->select('accounting_entry_items.*');

        $records2->join('accounting_entry_items', function ($join) use($include,$cta){
            $join->on('accounting_entry_items.accounting_entrie_id', '=', 'accounting_entries.id');
            if(isset($include) && $include != 2){
                $join->where('accounting_entry_items.reconciliation','=',$include);
            }
            if(isset($cta)){
                $join->where('accounting_entry_items.account_movement_id','=', $cta);
            }
        });
        $records2->select('accounting_entry_items.*');

        $records3->join('accounting_entry_items', function ($join) use($include,$cta){
            $join->on('accounting_entry_items.accounting_entrie_id', '=', 'accounting_entries.id');
            if(isset($include) && $include != 2){
                $join->where('accounting_entry_items.reconciliation','=',$include);
            }
            if(isset($cta)){
                $join->where('accounting_entry_items.account_movement_id','=', $cta);
            }
        });
        $records3->select('accounting_entry_items.*');


        $data = $records->union($records2)->union($records3);
        return $data->orderBy('id','desc');
    }

    //retorna la lista de valores para filtrar
    public function columns()
    {

        $ctas = AccountMovement::get();

        $ctas = $ctas->transform(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->description
            ];
        });

        return compact('ctas');
    }

    //funciona para puntear los asientos contables
    public function reconciliate($id)
    {
        $record = AccountingEntryItems::find($id);
        if ($record) {
            $record->reconciliation = true;
            $record->user_id_reconciliation = Auth()->user()->id;
            $record->reconciliation_date = date('Y-m-d H:i:s');
            $record->save();
            return [
                'success' => true,
                'message' => "Linea del asiento punteada correctamente"
            ];
        } else {
            return [
                'success' => false,
                'message' => "No se pudo puntear la linea del asiento contable"
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

}

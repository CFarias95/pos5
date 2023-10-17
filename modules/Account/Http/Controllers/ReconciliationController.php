<?php
namespace Modules\Account\Http\Controllers;

use Carbon\Carbon;
use App\Models\Tenant\Item;
use Illuminate\Http\Request;
use App\Models\Tenant\Document;
use App\Http\Controllers\Controller;
use App\Models\Tenant\AccountingEntries;
use App\Models\Tenant\AccountingEntryItems;
use App\Models\Tenant\Configuration;
use Modules\Account\Models\CompanyAccount;
use Modules\Account\Exports\ReportAccountingAdsoftExport;
use Modules\Account\Exports\ReportAccountingConcarExport;
use Modules\Account\Exports\ReportAccountingFoxcontExport;
use Modules\Account\Exports\ReportAccountingContasisExport;
use Modules\Account\Exports\ReportAccountingSumeriusExport;
use Modules\Account\Http\Resources\ReconciliationCollection;

class ReconciliationController extends Controller
{
    public function index()
    {
        return view('account::accounting_reconciliation.index');
    }

    public function download(Request $request)
    {
        $type = $request->input('type');
        $month = $request->input('month');

        $d_start = Carbon::parse($month.'-01')->format('Y-m-d');
        $d_end = Carbon::parse($month.'-01')->endOfMonth()->format('Y-m-d');

        $records = $this->getDocuments($d_start, $d_end);
        $filename = 'Reporte_'.ucfirst($type).'_Ventas_'.date('YmdHis');

        switch ($type) {
            case 'concar':
                $data = [
                    'records' => $this->getStructureConcar($this->getAllDocuments($d_start, $d_end)),
                ];

                $report = (new ReportAccountingConcarExport)
                            ->data($data)
                            ->download($filename.'.xlsx');

                return $report;

            case 'siscont':

                $records = $this->getStructureSiscont($records);

                $temp = tempnam(sys_get_temp_dir(), 'txt');
                $file = fopen($temp, 'w+');
                foreach ($records as $record)
                {
                    $line = implode('', $record);
                    fwrite($file, $line."\r\n");
                }
                fclose($file);

                return response()->download($temp, $filename.'.txt');

            case 'foxcont':

                $data = [
                    'records' => $this->getStructureFoxcont($records),
                ];

                return (new ReportAccountingFoxcontExport)
                    ->data($data)
                    ->download($filename.'.xlsx');

            case 'contasis':

                $data = [
                    'records' => $this->getStructureContasis($records),
                ];

                return (new ReportAccountingContasisExport)
                    ->data($data)
                    ->download($filename.'.xlsx');

            case 'adsoft':

                $data = [
                    'records' => $this->getStructureAdsoft($records),
                ];

                return (new ReportAccountingAdsoftExport)
                    ->data($data)
                    ->download($filename.'.xlsx');
            case 'sumerius':

                $data = [
                    'records' => $this->getStructureSumerius($records),
                ];

                return (new ReportAccountingSumeriusExport)
                    ->data($data)
                    ->download($filename.'.xlsx');
        }
    }

    //recupera todos los datos de accounting entrys items
    public function records(){

        $records = AccountingEntries::where('document_id','like','CF%')->orWhere('document_id','like','PC%');
        //return $records;
        return new ReconciliationCollection($records->paginate(config('tenant.items_per_page')));

    }
     public function columns(){
        return[
            'revided1' => 'Ya punteados'
        ];

     }

}

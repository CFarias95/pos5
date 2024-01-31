<?php

namespace Modules\Finance\Http\Controllers;

use App\CoreFacturalo\Helpers\Functions\GeneralPdfHelper;
use App\CoreFacturalo\Template;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Finance\Models\GlobalPayment;
use App\Models\Tenant\Cash;
use App\Models\Tenant\User;
use App\Http\Resources\Tenant\UserCollection;
use App\Models\System\Configuration;
use App\Models\Tenant\BankAccount;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Tenant\Company;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Finance\Http\Resources\GlobalPaymentCollection;
use Modules\Finance\Exports\ToPayAllExport;
use Modules\Finance\Exports\ToPayExport;
use Barryvdh\DomPDF\Facade as PDF;
use App\Models\Tenant\Establishment;
use Carbon\Carbon;
use App\Models\Tenant\Person;
use App\Models\Tenant\Purchase;
use Exception;
use Modules\Finance\Helpers\ToPay;
use Modules\Finance\Exports\ToPaymentMethodDayExport;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Http\Requests\Tenant\PosDatedRequest;
use App\Models\Tenant\AccountingEntries;
use App\Models\Tenant\AccountingEntryItems;
use App\Models\Tenant\AccountMovement;
use App\Models\Tenant\Configuration as TenantConfiguration;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\PurchaseFee;
use App\Models\Tenant\PurchasePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ToPayController extends Controller
{

    use FinanceTrait;
    use StorageDocument;

    protected $company;
    protected $sale_note;

    public function index()
    {

        return view('finance::to_pay.index');
    }


    public function filter()
    {

        $supplier_temp = Person::whereType('suppliers')->orderBy('name')->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->number . ' - ' . $row->name,
                'name' => $row->name,
                'number' => $row->number,
                'identity_document_type_id' => $row->identity_document_type_id,
            ];
        });
        $supplier = [];
        $supplier[] = [
            'id' => 0,
            'description' => 'Todos',
            'name' => 'Todos',
            'number' => '',
            'identity_document_type_id' => '',
        ];
        $suppliers = array_merge($supplier, $supplier_temp->toArray());

        $query_users = User::all();
        if (auth()->user()->type === 'admin') {
            $newUser = new User(['id' => 0, 'name' => 'Seleccionar Todos']);
            $query_users = $query_users->add($newUser)->sortBy('id');
        }
        $users = new UserCollection($query_users);
        $establishments = [];
        $establishments[] = [
            'id' => 0,
            'name' => 'Todos',
        ];
        $establishments = collect($establishments);
        Establishment::all()->transform(
            function ($row)  use (&$establishments) {
                $establishments[]  = [
                    'id' => $row->id,
                    'name' => $row->description
                ];
            }
        );
        $payment_method_types = PaymentMethodType::where('is_cash',1)->get();

        $payment_destinations = $this->getPaymentDestinations();
        $accounts = AccountMovement::get()->transform(function($row){
            return [
                'id' => $row->id,
                'description' => $row->code.'-'.$row->description,
            ];
        });

        return compact('suppliers', 'establishments', 'users', 'accounts', 'payment_destinations', 'payment_method_types');
    }


    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function records(Request $request)
    {
        $data = $request->all();
        if ($request->establishment_id === 0) {
            $data['withBankLoan'] = 1;
            $data['stablishmentTopaidAll'] = 1; // Lista todos los establecimients
        }

        return [
            //'records' => ToPay::getToPay($data)
            'records' => $this->getRecordsBySP($request)
        ];
    }

    public function getRecordsBySP(Request $request){

        $external = $request['external'] ?? 'NO';
        $establishment_id = $request['establishment_id'] ?? 0;
        $period = $request['period'] ?? 0;
        $date_start = $request['date_start'] ?? 0;
        $date_end = $request['date_end'] ?? 0;
        $month_start = $request['month_start'] ?? 0;
        $month_end = $request['month_end'] ?? 0;
        $customer_id = $request['supplier_id'] ?? [0];
        $user_id = $request['user_id'] ?? 0;
        $importe = $request['importe'] ?? 0;
        $include_liquidated = $request['include_liquidated'] ?? 0;
        $tipo = 0;
        $d_start = null;
        $d_end = null;


        switch ($period) {
            case 'month':
                $tipo = 1;
                $d_start = Carbon::parse($month_start . '-01')->format('Y/m/d');
                $d_end = Carbon::parse($month_start . '-01')->endOfMonth()->format('Y/m/d');
                break;
            case 'between_months':
                $tipo = 1;
                $d_start = Carbon::parse($month_start . '-01')->format('Y/m/d');
                $d_end = Carbon::parse($month_end . '-01')->endOfMonth()->format('Y/m/d');
                break;
            case 'date':
                $tipo = 1;
                $d_start = Carbon::parse($date_start)->format('Y/m/d');
                $d_end = $date_start;
                break;
            case 'between_dates':
                $tipo = 1;
                $d_start = Carbon::parse($date_start)->format('Y/m/d');
                $d_end = Carbon::parse($date_end)->format('Y/m/d');
                break;
            case 'expired':
                $tipo = 2;
                $d_start = $date_start;
                $d_end = $date_end;
                break;
            case 'posdated':
                $tipo = 3;
                $d_start = $date_start;
                $d_end = $date_end;
                break;
        }

        if($include_liquidated === 'true'){

            $include_liquidated = 1;

        }elseif($include_liquidated === true){

            $include_liquidated = 1;

        }else{
            $include_liquidated = 0;
        }

        if($external == 'SI'){
            $person = User::where('number',$user_id)->first();
            if(isset($person) == false){
                return;
            }
            $user_id = $person->id;
        }
        Log::info('ToPayController '. $include_liquidated);
        $data = DB::connection('tenant')->select('CALL SP_CuentarPorPagar(?,?,?,?,?,?,?,?)',[$establishment_id,json_encode($customer_id),$user_id,$importe,$include_liquidated,$tipo,$d_start,$d_end]);

        return $data;

    }

    /**
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function toPayAll()
    {

        return Excel::download(new ToPayAllExport, 'TCuentasPorPagar.xlsx');
    }


    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function toPay(Request $request)
    {

        $company = Company::first();
        $export = new ToPayExport();
        $records = ToPay::getToPay($request->all());
        $export->company($company)
            ->records($records);
        return $export->download('Reporte_Cuentas_Por_Pagar' . Carbon::now() . '.xlsx');
    }


    public function reportPaymentMethodDays(Request $request)
    {
        // 'records' => (new ToPay())->getToPay($request->all())

        $all_records = (new ToPay())->getToPay($request->all());

        $records = collect($all_records)->where('total_to_pay', '>', 0)->where('type', 'purchase')->map(function ($row) {
            $row['difference_days'] = Carbon::parse($row['date_of_issue'])->diffInDays($row['date_of_due']);
            return $row;
        });

        $company = Company::first();

        return (new ToPaymentMethodDayExport)
            ->company($company)
            ->records($records)
            ->download('Reporte_C_Pagar_F_Pago' . Carbon::now() . '.xlsx');
    }


    public function pdf(Request $request)
    {

        $records = (new ToPay())->getToPay($request->all());

        $company = Company::first();

        $pdf = PDF::loadView('finance::to_pay.report_pdf', compact("records", "company"));

        $filename = 'Reporte_Cuentas_Por_Pagar_' . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }

    public function toPrint($format, $id)
    {

        $purchase_payment = PurchasePayment::where('id', $id)->get();
        $account_entry = AccountingEntries::where('document_id', 'PC'.$id)->orWhere('document_id','like','%PC'.$id.';%')->get();
        $filename = null;
        $payments = null;

        if($purchase_payment[0]->multipay == 'SI'){

            $sequential = $purchase_payment[0]->sequential;
            $filename = 'MULTIPAGO-'.$sequential;
            $payments = PurchasePayment::where('sequential',$sequential)->get();
            $payments->transform(function($row){
                $fee = PurchaseFee::find($row->fee_id);
                return[
                    'document_serie' => $row->purchase->series,
                    'document_number' => $row->purchase->number,
                    'document_fee' => $fee->number,
                    'client_number' => $row->purchase->supplier->number,
                    'client_name' => $row->purchase->supplier->name,
                    'establishment' =>$row->purchase->establishment->code,
                    'comment' => $row->purchase->observation,
                    'payment' => $row->payment,
                    'to_pay' => $fee->amount - $row->payment,
                    'sequential' => $row->sequential,
                ];
            });

        }else{

            $payments = $purchase_payment->transform(function($row) use($filename){
                $fee = PurchaseFee::find($row->fee_id);
                $filename = $row->purchase->filename;
                return[
                    'document_serie' => $row->purchase->series,
                    'document_number' => $row->purchase->number,
                    'document_fee' => $fee->number,
                    'client_number' => $row->purchase->supplier->number,
                    'client_name' => $row->purchase->supplier->name,
                    'establishment' =>$row->purchase->establishment->code,
                    'comment' => $row->purchase->observation,
                    'payment' => $row->payment,
                    'to_pay' => $fee->amount - $row->payment,
                    'sequential' => $row->sequential,
                ];
            });
        }

        $this->reloadPDF1($payments, $format, $filename, $account_entry);
        $temp = tempnam(sys_get_temp_dir(), 'to-pay');
        file_put_contents($temp, $this->getStorage($filename, 'to-pay'));
        return response()->file($temp, GeneralPdfHelper::pdfResponseFileHeaders($filename));
    }

    private function reloadPDF1($payment, $format, $filename, $account_entry)
    {
        $this->createPdf1($payment, $format, $filename, $account_entry);
    }

    public function createPdf1($payments = null, $format_pdf = null, $filename = null, $account_entry = null)
    {

        ini_set("pcre.backtrack_limit", "5000000");
        $template = new Template();
        $pdf = new Mpdf();

        $company = ($this->company != null) ? $this->company : Company::active();
        $configuration = Configuration::first();
        $establishment = Establishment::find(auth()->user()->establishment_id);
        $user = User::find($account_entry[0]->user_id);
        $base_template = 'default';
        $html = $template->pdf2($base_template, "to-pay", $company, $payments, $format_pdf, $account_entry, $establishment, $user);


        /* cuentas por pagar formato a4 */
        $pdf_font_regular = config('tenant.pdf_name_regular');
        $pdf_font_bold = config('tenant.pdf_name_bold');

        if ($pdf_font_regular != false) {
            $defaultConfig = (new ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            $pdf = new Mpdf([
                'fontDir' => array_merge($fontDirs, [
                    app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
                        DIRECTORY_SEPARATOR . 'pdf' .
                        DIRECTORY_SEPARATOR . $base_template .
                        DIRECTORY_SEPARATOR . 'font')
                ]),
                'fontdata' => $fontData + [
                    'custom_bold' => [
                        'R' => $pdf_font_bold . '.ttf',
                    ],
                    'custom_regular' => [
                        'R' => $pdf_font_regular . '.ttf',
                    ],
                ]
            ]);
        }

        $path_css = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
            DIRECTORY_SEPARATOR . 'pdf' .
            DIRECTORY_SEPARATOR . $base_template .
            DIRECTORY_SEPARATOR . 'style.css');

        $stylesheet = file_get_contents($path_css);

        $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);


        $this->uploadStorage($filename, $pdf->output('', 'S'), 'to-pay');
        //$this->uploadFile1($filename, , , $id);
    }

    public function uploadFile1($filename, $file_content, $file_type)
    {
        $this->uploadStorage($filename, $file_content, $file_type);
    }

     //agregado 19-10-23
     public function PosDatedShow($document_id, $fee_id) {
        $record = PurchaseFee::where('id','=',$fee_id)
        ->where('purchase_id','=',$document_id)
        ->select('id','purchase_id','f_posdated','posdated')->first();
        return $record;
    }

    public function PosDatedUpdate(PosDatedRequest $request)
    {
        $record = PurchaseFee::where('id','=',$request->id)
        ->where('purchase_id','=',$request->purchase_id);
        $record->update([
            'f_posdated'=>$request->f_posdated,
            'posdated'=>$request->posdated
        ]);

        return [
            'success' => true,
            'message' => 'Se ha registrado con éxito',
        ];
    }

    public function generateMultiPay(Request $request){

        try{
            Log::info('Funcion para crear pago multiple To Pay');
            Log::info('generateMultiPay' . json_encode($request));

            $config = TenantConfiguration::first();
            $documentIds = '';
            $documentsSequentials = '';
            $haber = [];
            $sequential = PurchasePayment::latest('id')->first();
            $debeAdicional = 0;
            $haberAdicional = 0;

            foreach ($request->unpaid as $value) {
                Log::info('DATA: ',$value);
                $payment = new PurchasePayment();
                $payment->purchase_id = $value['document_id'];
                $payment->date_of_payment = $request->date_of_payment;
                $payment->payment_method_type_id = $request->payment_method_type_id;
                $payment->has_card = 0;
                $payment->reference = $request->reference;
                //$payment->payment_received = 1;
                $payment->payment = $value['amount'];
                $payment->fee_id = $value['fee_id'];
                $payment->sequential = ($sequential && $sequential->sequential)? $sequential->sequential + 1 : 1;
                $payment->multipay = 'SI';
                $payment->save();

                $row['payment_destination_id'] = $request->payment_destination_id;
                $this->createGlobalPayment($payment, $row);

                $document = Purchase::find($value['document_id']);
                $documentsSequentials .= $document->series.str_pad($document->number,'9','0',STR_PAD_LEFT).' ';

                $documentIds .= 'PC'.$payment->id.';';
                $customer = Person::find($value['customer_id']);
                //Log::info($customer);
                Log::info($config);
                array_push($haber,['account'=>(isset($customer->account) && $customer->account != null)?$customer->account:$config->cta_suppliers,'amount'=>$value['amount']]);

            }

            $comment = 'Multipago '.$documentsSequentials;

            foreach ($request->extras as $value) {
                $debeAdicional += floatVal($value['debe']);
                $haberAdicional += floatVal($value['haber']);
            }

            $lista = AccountingEntries::where('user_id', '=', auth()->user()->id)->latest('id')->first();
            $cabeceraC = new AccountingEntries();
            $cabeceraC->user_id = auth()->user()->id;
            $cabeceraC->seat = ($lista && $lista->seat)? $lista->seat + 1 : 1;
            $cabeceraC->seat_general = ($lista && $lista->seat)? $lista->seat + 1 : 1;
            $cabeceraC->seat_date = $request->date_of_payment;
            $cabeceraC->types_accounting_entrie_id = 1;
            $cabeceraC->comment = $comment;
            $cabeceraC->serie = null;
            $cabeceraC->number = ($lista && $lista->seat)? $lista->seat + 1 : 1;
            $cabeceraC->total_debe = $request->payment + $debeAdicional;
            $cabeceraC->total_haber = $request->payment + $haberAdicional;
            $cabeceraC->revised1 = 0;
            $cabeceraC->user_revised1 = 0;
            $cabeceraC->revised2 = 0;
            $cabeceraC->user_revised2 = 0;
            $cabeceraC->currency_type_id = $config->currency_type_id;
            $cabeceraC->doctype = 1;
            $cabeceraC->is_client = true;
            $cabeceraC->establishment = auth()->user()->establishment;
            $cabeceraC->prefix = 'ASC';
            $cabeceraC->external_id = Str::uuid()->toString();
            $cabeceraC->document_id = $documentIds;

            $cabeceraC->save();
            $cabeceraC->filename = 'ASC-'.$cabeceraC->id.'-'. date('Ymd');
            $cabeceraC->save();

            $detalle = new AccountingEntryItems();
            $ceuntaC = PaymentMethodType::find($request->payment_method_type_id);
            $detalle->accounting_entrie_id = $cabeceraC->id;
            $detalle->account_movement_id = ($ceuntaC && $ceuntaC->countable_acount_payment)?$ceuntaC->countable_acount_payment:$config->cta_paymnets;
            $detalle->seat_line = 1;
            $detalle->debe = 0;
            $detalle->haber = $request->payment + floatVal($debeAdicional) - floatVal($haberAdicional);
            $detalle->save();

            $line = 2;
            foreach ($haber as $key => $value) {

                Log::info('DATA DE HABER : '.json_encode($value));

                $detalle = new AccountingEntryItems();
                $detalle->accounting_entrie_id = $cabeceraC->id;
                $detalle->account_movement_id = $value['account'];
                $detalle->seat_line = $line;
                $detalle->haber = 0;
                $detalle->debe = $value['amount'] ;
                $detalle->save();
                $line += 1;
            }

            foreach ($request->extras as $value) {
                $detalle = new AccountingEntryItems();
                $detalle->accounting_entrie_id = $cabeceraC->id;
                $detalle->account_movement_id = $value['account_id'];
                $detalle->seat_line = $line;
                $detalle->debe = floatVal($value['debe']);
                $detalle->haber = floatVal($value['haber']);
                $detalle->save();
                $line += 1;
            }
            return[
                'success' => true,
                'message' => 'Multi pago generado exitosamente!'
            ];

        }catch(Exception $ex){

            $explode = explode(';',$documentIds);
            foreach ($explode as $value) {
                $payment = PurchasePayment::find(str_replace(['PC',';'],'',$value));
                if (!is_null($payment)) {
                    $payment->delete();
                }
            }
            return[
                'success' => false,
                'message' => 'Multi pago NO generado, '.$ex->getMessage()
            ];
        }
    }
}

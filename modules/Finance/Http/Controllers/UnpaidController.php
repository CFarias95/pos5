<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Finance\Models\GlobalPayment;
use App\Models\Tenant\Cash;
use App\Models\Tenant\BankAccount;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Tenant\Company;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Finance\Http\Resources\GlobalPaymentCollection;
use Modules\Finance\Exports\BalanceExport;
use Barryvdh\DomPDF\Facade as PDF;
use App\Models\Tenant\Establishment;
use Carbon\Carbon;
use App\Models\Tenant\Person;
use Modules\Dashboard\Helpers\DashboardView;
use App\Exports\AccountsReceivable;
use App\Models\Tenant\Configuration;
use Modules\Finance\Exports\UnpaidPaymentMethodDayExport;
use App\Models\Tenant\User;
use App\Models\Tenant\PaymentMethodType;
use Modules\Finance\Http\Resources\UnpaidCollection;
use Modules\Finance\Traits\UnpaidTrait;
use Modules\Item\Models\WebPlatform;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\Document;
use ErrorException;
use Exception;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use App\CoreFacturalo\Template;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Helpers\Functions\GeneralPdfHelper;
use App\Http\Requests\Tenant\PosDatedRequest;
use App\Models\Tenant\DocumentFee;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant\AccountingEntries;
use App\Models\Tenant\DocumentPayment;

use function PHPSTORM_META\type;

class UnpaidController extends Controller
{
    use FinanceTrait, UnpaidTrait;
    use StorageDocument;

    protected $sale_note;
    protected $company;

    public function index()
    {
        $configuration = Configuration::getPublicConfig();
        return view('finance::unpaid.index',compact('configuration'));
    }

    public function filter()
    {
        $customer_temp = Person::whereType('customers')->orderBy('name')->get()->transform(function($row) {
            return [
                'id' => $row->id,
                'description' => $row->number.' - '.$row->name,
                'name' => $row->name,
                'number' => $row->number,
                'identity_document_type_id' => $row->identity_document_type_id,
            ];
        });
        $customer= [];
        $customer[]=[
            'id' => null,
            'description' => 'Todos',
            'name' => 'Todos',
            'number' => '',
            'identity_document_type_id' => '',
        ];
        $customers = array_merge($customer,$customer_temp->toArray());
        $establishments= [];
        $establishments[] = [
            'id' => 0,
            'name' => 'Todos',
        ];
        $establishments = collect($establishments);
        Establishment::all()->transform(function($row)  use(&$establishments){
            $establishments[]  = [
                'id' => $row->id,
                'name' => $row->description
            ]; }
        );

        $users = [];
        if(auth()->user()->type == 'admin')
        {
            $users = User::where('id', '!=', auth()->user()->id)->whereIn('type', ['admin', 'seller'])->get();
        }

        $payment_method_types = PaymentMethodType::whereIn('id', ['05', '08', '09'])->get();
        $web_platforms = WebPlatform::all();
        return compact('customers', 'establishments', 'users', 'payment_method_types','web_platforms');
    }

    public function records(Request $request)
    {
        $records = (new DashboardView())->getUnpaidFilterUser($request->all());
        $config = Configuration::first();

        $data = $this->getRecordsBySP($request);

        $collection = collect($data);
        $per_page = (config('tenant.items_per_page'));
        $page = $request['page'] ?? 1;
        $paginatedItems = $collection->slice(($page - 1) * $per_page, $per_page)->all();
        $paginatedCollection = new LengthAwarePaginator($paginatedItems, count($collection), $per_page, $page);
        return $paginatedCollection;

        return (new UnpaidCollection($records->paginate(config('tenant.items_per_page'))))->additional([
            'configuration' => $config->finances
        ]);
    }

    public function getRecordsBySP(Request $request){

        $external = $request['external'] ?? 'NO';
        $establishment_id = $request['establishment_id'] ?? 0;
        $period = $request['period'] ?? 0;
        $date_start = $request['date_start'] ?? 0;
        $date_end = $request['date_end'] ?? 0;
        $month_start = $request['month_start'] ?? 0;
        $month_end = $request['month_end'] ?? 0;
        $customer_id = $request['customer_id'] ?? 0;
        $user_id = $request['user_id'] ?? 0;
        $purchase_order = $request['purchase_order'] ?? 0;
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
                $d_start = $date_start;
                $d_end = $date_start;
                break;
            case 'between_dates':
                $tipo = 1;
                $d_start = $date_start;
                $d_end = $date_end;
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
        $data = DB::connection('tenant')->select('CALL SP_CuentarPorCobrar(?,?,?,?,?,?,?,?,?)',[$establishment_id, $customer_id,$user_id,$purchase_order,$importe,$include_liquidated,$d_start,$d_end,$tipo]);
        return $data;

    }

    public function updateDateFee(Request $request){
        try{
            $idFee = $request->fee_id;
            $fechaVencimiento = $request->date_of_due;

            $fee = DocumentFee::find($idFee);
            $fee->date = $fechaVencimiento;
            $fee->save();

            return[
                'success' => true,
                'message' => 'Se actualizo la fecha de vencimiento'
            ];
        }catch(Exception $ex){
            return[
                'success' => false,
                'message' => $ex->getMessage()
            ];
        }
    }

    public function generateNewFee(Request $request){
        try{
            $idFee = $request->fee_id;
            $fechaVencimiento = $request->date_of_due;
            $valorFee = $request->amount;

            $fee = DocumentFee::find($idFee);
            $difAmount = $fee->amount - floatval($valorFee);
            $fee->amount = $difAmount;
            $fee->save();

            $number = (DocumentFee::where('document_id', $fee->document_id)->get())->count();

            $feeNew = new DocumentFee();
            $feeNew->document_id = $fee->document_id;
            $feeNew->date = $fechaVencimiento;
            $feeNew->currency_type_id = $fee->currency_type_id;
            $feeNew->amount = floatval($valorFee);
            $feeNew->number = $number + 1;
            $feeNew->save();

            return[
                'success' => true,
                'message' => 'Se creo una nueva cuota: '.$feeNew->id
            ];
        }catch(Exception $ex){
            return[
                'success' => false,
                'message' => $ex->getMessage()
            ];
        }
    }

    public function unpaidall()
    {
        return Excel::download(new AccountsReceivable, 'Allclients.xlsx');
    }


    public function reportPaymentMethodDays(Request $request)
    {

        $all_records = $this->transformRecords((new DashboardView())->getUnpaidFilterUser($request->all())->get());

        $records = collect($all_records)->where('total_to_pay', '>', 0)->where('type', 'document')->map(function($row){
            $row['difference_days'] = Carbon::parse($row['date_of_issue'])->diffInDays($row['date_of_due']);
            return $row;
        });

        $company = Company::first();

        $unpaidPaymentMethodDayExport = new UnpaidPaymentMethodDayExport();
        $unpaidPaymentMethodDayExport
            ->company($company)
            ->records($records);
        return $unpaidPaymentMethodDayExport->download('Reporte_C_Cobrar_F_Pago'.Carbon::now().'.xlsx');

    }


    public function pdf(Request $request) {

        $records = $this->transformRecords((new DashboardView())->getUnpaidFilterUser($request->all())->get());
        $company = Company::first();

        $pdf = PDF::loadView('finance::unpaid.reports.report_pdf', compact("records", "company"));

        $filename = 'Reporte_Cuentas_Por_Cobrar_'.date('YmdHis');

        return $pdf->download($filename.'.pdf');

    }

    public function toPrint($external_id,$type,$format, $id, $index) {
        //Log::info('index'.$index);
        if ($type=='sale') {
            $sale_note = SaleNote::where('external_id', $external_id)->first();
        } else {
            $sale_note = Document::where('external_id', $external_id)->first();
        }

        //Log::info(json_encode($sale_note));

        if (!$sale_note) throw new Exception("El código {$external_id} es inválido, no se encontro la nota de venta relacionada");

        $this->reloadPDF1($sale_note, $format, $sale_note->filename, $id, $index);

        $temp = tempnam(sys_get_temp_dir(), 'unpaid');


        file_put_contents($temp, $this->getStorage($sale_note->filename, 'unpaid'));

        /*
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$sale_note->filename.'"'
        ];
        */

        return response()->file($temp, GeneralPdfHelper::pdfResponseFileHeaders($sale_note->filename));
    }

    private function reloadPDF1($sale_note, $format, $filename, $id, $index) {
        $this->createPdf1($sale_note, $format, $filename, $id, $index);
    }

    public function createPdf1($sale_note = null, $format_pdf = null, $filename = null, $id, $index = 0) {

        ini_set("pcre.backtrack_limit", "5000000");
        $template = new Template();
        $pdf = new Mpdf();

        $this->company = ($this->company != null) ? $this->company : Company::active();
        $this->document = ($sale_note != null) ? $sale_note : $this->sale_note;

        $this->configuration = Configuration::first();
        // $configuration = $this->configuration->formats;
        $base_template = Establishment::find($this->document->establishment_id)->template_pdf;


        //$docs = Document::where('customer_id', $this->document->customer_id)->where('external_id', $this->document->external_id)->first();

        $docs = $this->document;

        $conect = DocumentPayment::where('document_id', $docs->id)->where('fee_id', $id)->get();
        Log::info('createPdf1 DocumentPayment: '.json_encode($conect));

        $i = $conect[$index];
        $account_entry = AccountingEntries::where('document_id', 'CF'.$i->id)->first();

        //Log::info('info1'.json_encode($account_entry));
        //Log::info('index1'.$id);

        $html = $template->pdf1($base_template, "unpaid", $this->company, $this->document, $format_pdf, $id, $account_entry, $index);

        /* cuentas por cobrar formato a4 */
        if (($format_pdf === 'ticket') OR ($format_pdf === 'ticket_58')OR ($format_pdf=='ticket_50')) {

            $width = ($format_pdf === 'ticket_58') ? 56 : 78 ;
            if(config('tenant.enabled_template_ticket_80')) $width = 76;
            if($format_pdf === 'ticket_50') $width = 45;

            $company_logo      = ($this->company->logo) ? 40 : 0;
            $company_name      = (strlen($this->company->name) / 20) * 10;
            $company_address   = (strlen($this->document->establishment->address) / 30) * 10;
            $company_number    = $this->document->establishment->telephone != '' ? '10' : '0';
            $customer_name     = strlen($this->document->customer->name) > '25' ? '10' : '0';
            $customer_address  = (strlen($this->document->customer->address) / 200) * 10;
            $p_order           = $this->document->purchase_order != '' ? '10' : '0';
            //$account_entry     = $this->account_entry;
            $total_exportation = $this->document->total_exportation != '' ? '10' : '0';
            $total_free        = $this->document->total_free != '' ? '10' : '0';
            $total_unaffected  = $this->document->total_unaffected != '' ? '10' : '0';
            $total_exonerated  = $this->document->total_exonerated != '' ? '10' : '0';
            $total_taxed       = $this->document->total_taxed != '' ? '10' : '0';
            $quantity_rows     = count($this->document->items);
            $payments     = $this->document->payments()->count() * 2;
            $discount_global = 0;
            $extra_by_item_description = 0;
            foreach ($this->document->items as $it) {
                if(strlen($it->item->description)>100){
                    $extra_by_item_description +=24;
                }
                if ($it->discounts) {
                    $discount_global = $discount_global + 1;
                }
            }
            $legends = $this->document->legends != '' ? '10' : '0';
            $bank_accounts = BankAccount::count() * 6;

            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    $width,
                    120 +
                    ($quantity_rows * 8)+
                    ($discount_global * 3) +
                    $company_logo +
                    $payments +
                    $company_name +
                    $company_address +
                    $company_number +
                    $customer_name +
                    $customer_address +
                    $p_order +
                    $legends +
                    $bank_accounts +
                    $total_exportation +
                    $total_free +
                    $total_unaffected +
                    $total_exonerated +
                    $extra_by_item_description +
                    $total_taxed],
                'margin_top' => 2,
                'margin_right' => 5,
                'margin_bottom' => 0,
                'margin_left' => 5
            ]);
        }
        else{    $pdf_font_regular = config('tenant.pdf_name_regular');
            $pdf_font_bold = config('tenant.pdf_name_bold');

            if ($pdf_font_regular != false) {
                $defaultConfig = (new ConfigVariables())->getDefaults();
                $fontDirs = $defaultConfig['fontDir'];

                $defaultFontConfig = (new FontVariables())->getDefaults();
                $fontData = $defaultFontConfig['fontdata'];

                $pdf = new Mpdf([
                    'fontDir' => array_merge($fontDirs, [
                        app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.
                                                DIRECTORY_SEPARATOR.'pdf'.
                                                DIRECTORY_SEPARATOR.$base_template.
                                                DIRECTORY_SEPARATOR.'font')
                    ]),
                    'fontdata' => $fontData + [
                        'custom_bold' => [
                            'R' => $pdf_font_bold.'.ttf',
                        ],
                        'custom_regular' => [
                            'R' => $pdf_font_regular.'.ttf',
                        ],
                    ]
                ]);
            }

        }
        $path_css = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.
                                             DIRECTORY_SEPARATOR.'pdf'.
                                             DIRECTORY_SEPARATOR.$base_template.
                                             DIRECTORY_SEPARATOR.'style.css');

        $stylesheet = file_get_contents($path_css);

        $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        $this->uploadFile1($this->document->filename, $pdf->output('', 'S'), 'unpaid', $id);
    }

    public function uploadFile1($filename, $file_content, $file_type, $account_entry)
    {
        $this->uploadStorage($filename, $file_content, $file_type);
    }

    private function reloadPDF($sale_note, $format, $filename) {
        $this->createPdf($sale_note, $format, $filename);
    }

    public function createPdf($sale_note = null, $format_pdf = null, $filename = null) {

        ini_set("pcre.backtrack_limit", "5000000");
        $template = new Template();
        $pdf = new Mpdf();

        $this->company = ($this->company != null) ? $this->company : Company::active();
        $this->document = ($sale_note != null) ? $sale_note : $this->sale_note;

        $this->configuration = Configuration::first();
        // $configuration = $this->configuration->formats;
        $base_template = Establishment::find($this->document->establishment_id)->template_pdf;

        $html = $template->pdf($base_template, "unpaid", $this->company, $this->document, $format_pdf);

        /* cuentas por cobrar formato a4 */
        if (($format_pdf === 'ticket') OR ($format_pdf === 'ticket_58')OR ($format_pdf=='ticket_50')) {

            $width = ($format_pdf === 'ticket_58') ? 56 : 78 ;
            if(config('tenant.enabled_template_ticket_80')) $width = 76;
            if($format_pdf === 'ticket_50') $width = 45;

            $company_logo      = ($this->company->logo) ? 40 : 0;
            $company_name      = (strlen($this->company->name) / 20) * 10;
            $company_address   = (strlen($this->document->establishment->address) / 30) * 10;
            $company_number    = $this->document->establishment->telephone != '' ? '10' : '0';
            $customer_name     = strlen($this->document->customer->name) > '25' ? '10' : '0';
            $customer_address  = (strlen($this->document->customer->address) / 200) * 10;
            $p_order           = $this->document->purchase_order != '' ? '10' : '0';

            $total_exportation = $this->document->total_exportation != '' ? '10' : '0';
            $total_free        = $this->document->total_free != '' ? '10' : '0';
            $total_unaffected  = $this->document->total_unaffected != '' ? '10' : '0';
            $total_exonerated  = $this->document->total_exonerated != '' ? '10' : '0';
            $total_taxed       = $this->document->total_taxed != '' ? '10' : '0';
            $quantity_rows     = count($this->document->items);
            $payments     = $this->document->payments()->count() * 2;
            $discount_global = 0;
            $extra_by_item_description = 0;
            foreach ($this->document->items as $it) {
                if(strlen($it->item->description)>100){
                    $extra_by_item_description +=24;
                }
                if ($it->discounts) {
                    $discount_global = $discount_global + 1;
                }
            }
            $legends = $this->document->legends != '' ? '10' : '0';
            $bank_accounts = BankAccount::count() * 6;

            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    $width,
                    120 +
                    ($quantity_rows * 8)+
                    ($discount_global * 3) +
                    $company_logo +
                    $payments +
                    $company_name +
                    $company_address +
                    $company_number +
                    $customer_name +
                    $customer_address +
                    $p_order +
                    $legends +
                    $bank_accounts +
                    $total_exportation +
                    $total_free +
                    $total_unaffected +
                    $total_exonerated +
                    $extra_by_item_description +
                    $total_taxed],
                'margin_top' => 2,
                'margin_right' => 5,
                'margin_bottom' => 0,
                'margin_left' => 5
            ]);
        }
        else{    $pdf_font_regular = config('tenant.pdf_name_regular');
            $pdf_font_bold = config('tenant.pdf_name_bold');

            if ($pdf_font_regular != false) {
                $defaultConfig = (new ConfigVariables())->getDefaults();
                $fontDirs = $defaultConfig['fontDir'];

                $defaultFontConfig = (new FontVariables())->getDefaults();
                $fontData = $defaultFontConfig['fontdata'];

                $pdf = new Mpdf([
                    'fontDir' => array_merge($fontDirs, [
                        app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.
                                                DIRECTORY_SEPARATOR.'pdf'.
                                                DIRECTORY_SEPARATOR.$base_template.
                                                DIRECTORY_SEPARATOR.'font')
                    ]),
                    'fontdata' => $fontData + [
                        'custom_bold' => [
                            'R' => $pdf_font_bold.'.ttf',
                        ],
                        'custom_regular' => [
                            'R' => $pdf_font_regular.'.ttf',
                        ],
                    ]
                ]);
            }

        }
        $path_css = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.
                                             DIRECTORY_SEPARATOR.'pdf'.
                                             DIRECTORY_SEPARATOR.$base_template.
                                             DIRECTORY_SEPARATOR.'style.css');

        $stylesheet = file_get_contents($path_css);

        $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);


        $this->uploadFile($this->document->filename, $pdf->output('', 'S'), 'unpaid');
    }

    public function uploadFile($filename, $file_content, $file_type)
    {
        $this->uploadStorage($filename, $file_content, $file_type);
    }

    //agregado 18-10-23
    public function PosDatedShow($document_id, $fee_id) {
        $record = DocumentFee::where('id','=',$fee_id)
        ->where('document_id','=',$document_id)
        ->select('id','document_id','f_posdated','posdated')->first();
        return $record;
    }

    public function PosDatedUpdate(PosDatedRequest $request)
    {
        $record = DocumentFee::where('id','=',$request->id)
        ->where('document_id','=',$request->document_id);
        $record->update([
            'f_posdated'=>$request->f_posdated,
            'posdated'=>$request->posdated
        ]);

        return [
            'success' => true,
            'message' => 'Se ha registrado con éxito',
        ];
    }
}

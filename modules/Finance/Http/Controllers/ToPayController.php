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
use App\Models\Tenant\PurchasePayment;
use Illuminate\Support\Facades\Log;

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

        $supplier_temp = Person::whereType('suppliers')->orderBy('name')->take(100)->get()->transform(function ($row) {
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
            'id' => null,
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

        return compact('suppliers', 'establishments', 'users');
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
            'records' => ToPay::getToPay($data)
        ];
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

    public function toPrint($format, $id, $index)
    {

        $sale_note = Purchase::find($id);


        //if (!$sale_note) throw new Exception("El código {$id} es inválido, no se encontro la nota de venta relacionada");
        $this->reloadPDF1($sale_note, $format, $sale_note->filename, $id, $index);
        $temp = tempnam(sys_get_temp_dir(), 'to-pay');


        file_put_contents($temp, $this->getStorage($sale_note->filename, 'to-pay'));

        /*
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$sale_note->filename.'"'
        ];
        */

        return response()->file($temp, GeneralPdfHelper::pdfResponseFileHeaders($sale_note->filename));
    }

    private function reloadPDF1($sale_note, $format, $filename, $id, $index)
    {
        $this->createPdf1($sale_note, $format, $filename, $id, $index);
    }

    public function createPdf1($sale_note = null, $format_pdf = null, $filename = null, $id, $index)
    {

        ini_set("pcre.backtrack_limit", "5000000");
        $template = new Template();
        $pdf = new Mpdf();

        $this->company = ($this->company != null) ? $this->company : Company::active();
        $this->document = ($sale_note != null) ? $sale_note : $this->sale_note;

        $this->configuration = Configuration::first();
        // $configuration = $this->configuration->formats;
        $base_template = Establishment::find($this->document->establishment_id)->template_pdf;
        $payments = PurchasePayment::where('purchase_id', $id)->get();
        $this->document->payments = $payments;
        $html = $template->pdf2($base_template, "to-pay", $this->company, $this->document, $format_pdf, $id, $index);

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


        $this->uploadFile1($this->document->filename, $pdf->output('', 'S'), 'to-pay', $id);
    }

    public function uploadFile1($filename, $file_content, $file_type)
    {
        $this->uploadStorage($filename, $file_content, $file_type);
    }
}

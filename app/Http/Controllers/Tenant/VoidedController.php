<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Facturalo;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\VoidedCollection;
use App\Models\Tenant\Voided;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Tenant\VoidedRequest;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenant\{
    AccountingEntries,
    AccountingEntryItems,
    Document,
    Configuration,
    DocumentPayment,
    VoidedDocument
};
use Illuminate\Support\Facades\Log;

class VoidedController extends Controller
{
    use StorageDocument;

    public function __construct()
    {
        $this->middleware('input.request:voided,web', ['only' => ['store']]);
    }

    public function index()
    {
        return view('tenant.voided.index');
    }

    public function columns()
    {
        return [
            'date_of_issue' => 'Fecha de emisión'
        ];
    }

    public function records(Request $request)
    {

        $voided = Voided::where($request->column, 'like', "%{$request->value}%")
                    ->select(DB::raw("id, external_id, date_of_reference, date_of_issue, ticket, identifier, state_type_id, 'voided' AS 'type'"));

        $summaries = DB::connection('tenant')
                        ->table('summaries')
                        ->select(DB::raw("id, external_id, date_of_reference, date_of_issue, ticket, identifier, state_type_id, 'summaries' AS 'type'"))
                        ->where($request->column, 'like', "%{$request->value}%")
                        ->where('summary_status_type_id', '3');

        return new VoidedCollection($voided->union($summaries)->orderBy('date_of_issue', 'DESC')->paginate(config('tenant.items_per_page')));
    }

    public function store(VoidedRequest $request)
    {
        $validate = $this->validateVoided($request);
        if(!$validate['success']) return $validate;
        $fact = DB::connection('tenant')->transaction(function () use($request) {
            $facturalo = new Facturalo();
            $facturalo->save($request->all());
            $facturalo->createXmlUnsigned();
            $facturalo->signXmlUnsigned();
            return $facturalo;
        });
        $document = $fact->getDocument();
        $idauth = auth()->user()->id;
        foreach($document->documents as $doc){
            $accountrieEntryActual = AccountingEntries::where('document_id','P'.$doc->document_id)->first();
            if($accountrieEntryActual){
                $lista = AccountingEntries::where('user_id', '=', $idauth)->latest('id')->first();
                $ultimo = AccountingEntries::latest('id')->first();
                if (empty($lista)) {
                    $seat = 1;
                } else {

                    $seat = $lista->seat + 1;
                }
                if (empty($ultimo)) {
                    $seat_general = 1;
                } else {
                    $seat_general = $ultimo->seat_general + 1;
                }
                $comment = 'Anular '.$accountrieEntryActual->comment;

                    $cabeceraC = new AccountingEntries();
                    $cabeceraC->fill($accountrieEntryActual->toArray());
                    $cabeceraC->id = null;
                    $cabeceraC->user_id = $idauth;
                    $cabeceraC->seat = $seat;
                    $cabeceraC->serie = 'ANULAR VENTA';
                    $cabeceraC->seat_general = $seat_general;
                    $cabeceraC->seat_date = date('Y-m-d');
                    $cabeceraC->comment = $comment;
                    $cabeceraC->number = $seat;
                    $cabeceraC->total_debe = $accountrieEntryActual->total_haber;
                    $cabeceraC->total_haber = $accountrieEntryActual->total_debe;
                    $cabeceraC->revised1 = 0;
                    $cabeceraC->user_revised1 = 0;
                    $cabeceraC->revised2 = 0;
                    $cabeceraC->user_revised2 = 0;
                    $cabeceraC->external_id = Str::uuid()->toString();
                    $cabeceraC->document_id = $accountrieEntryActual->document_id;
                    $cabeceraC->save();
                    $cabeceraC->filename = 'ASC-'.$cabeceraC->id.'-'. date('Ymd');
                    $cabeceraC->save();

                    $detalleS = AccountingEntryItems::where('accounting_entrie_id',$accountrieEntryActual->id)->get();
                    foreach ($detalleS as $itemActual) {
                        $itemNuevo = new AccountingEntryItems();
                        $itemNuevo->fill($itemActual->toArray());
                        $itemNuevo->id = null;
                        $itemNuevo->accounting_entrie_id = $cabeceraC->id;
                        $itemNuevo->debe = $itemActual->haber;
                        $itemNuevo->haber = $itemActual->debe;
                        $itemNuevo->save();
                    }
            }

            $paymnetsMSI = DocumentPayment::where('document_id',$doc->id)->where('multipay','NO')->get();
            $paymnetsMNO = DocumentPayment::where('document_id',$doc->id)->where('multipay','SI')->orderBy('sequential', 'desc')->get();

            if($paymnetsMSI){

                foreach($paymnetsMSI as $pay){

                    $paymentController = new DocumentPaymentController();
                    $request = new Request();
                    $request['id'] = $pay->id;
                    $request['reference'] = 'Anulado';
                    $request['date_of_payment'] = date('Y-m-d');
                    $paymentController->generateReverse($request);
                }
            }

            if($paymnetsMNO){
                $secu = '';
                foreach($paymnetsMNO as $pay){
                    if($secu != $pay->sequential){
                        $paymentController = new DocumentPaymentController();
                        $request = new Request();
                        $request['id'] = $pay->id;
                        $request['reference'] = 'Anulado';
                        $request['date_of_payment'] = date('Y-m-d');
                        $paymentController->generateReverse($request);
                        $secu = $pay->sequential;
                    }
                }
            }
        }


        return [
            'success' => true,
            'message' => "La anulación {$document->identifier} fue creado correctamente",
        ];
    }


    /**
     * Validaciones previas
     *
     * @param VoidedRequest $request
     * @return array
     */
    public function validateVoided($request)
    {

        $configuration = Configuration::select('restrict_voided_send', 'shipping_time_days_voided')->firstOrFail();
        $voided_date_of_issue = Carbon::parse($request->date_of_issue);

        if($configuration->restrict_voided_send)
        {
            foreach ($request->documents as $row)
            {
                $document = Document::whereFilterWithOutRelations()->select('date_of_issue')->findOrFail($row['document_id']);

                $difference_days = $configuration->shipping_time_days_voided - $document->getDiffInDaysDateOfIssue($voided_date_of_issue);

                if($difference_days < 0)
                {
                    return [
                        'success' => false,
                        'message' => "El documento excede los {$configuration->shipping_time_days_voided} días válidos para ser anulado."
                    ];
                }
            }
        }

        return [
            'success' => true,
            'message' => null
        ];

    }


    public function status($voided_id)
    {
        $document = Voided::find($voided_id);

        $fact = DB::connection('tenant')->transaction(function () use($document) {
            $facturalo = new Facturalo();
            $facturalo->setDocument($document);
            $facturalo->setType('voided');
            $facturalo->statusSummary($document->ticket);
            return $facturalo;
        });
        $voided_doc = VoidedDocument::where('id', $voided_id)->first();
        $doc_payment_seat = DocumentPayment::where('document_id', $voided_doc->document_id)->first();
        if($doc_payment_seat && $doc_payment_seat->count() > 0 ){
            AccountingEntries::where('document_id', 'CF'.$doc_payment_seat->id)->delete();
        }

        AccountingEntries::where('document_id', 'F'.$voided_doc->document_id)->delete();
        $response = $fact->getResponse();

        return [
            'success' => true,
            'message' => $response['description'],
        ];
    }

    public function status_masive()
    {

        $records = Voided::where('state_type_id', '03')->get();

        $fact = DB::connection('tenant')->transaction(function () use($records) {

            foreach ($records as $document) {

                $facturalo = new Facturalo();
                $facturalo->setDocument($document);
                $facturalo->setType('voided');
                $facturalo->statusSummary($document->ticket);
            }
        });

        return [
            'success' => true,
            'message' => "Consulta masiva ejecutada.",
        ];
    }

    public function destroy($voided_id)
    {
        $document = Voided::find($voided_id);
        foreach ($document->documents as $doc)
        {
            $doc->document->update([
                'state_type_id' => '05'
            ]);
        }
        $document->delete();

        return [
            'success' => true,
            'message' => 'Anulación eliminada con éxito'
        ];
    }
}

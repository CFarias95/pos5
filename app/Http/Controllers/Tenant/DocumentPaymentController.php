<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\DocumentPaymentRequest;
use App\Http\Requests\Tenant\DocumentRequest;
use App\Http\Resources\Tenant\DocumentPaymentCollection;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\PaymentMethodType;
use App\Exports\DocumentPaymentExport;
use App\Models\Tenant\AccountingEntries;
use App\Models\Tenant\AccountingEntryItems;
use Exception, Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Finance\Traits\FilePaymentTrait;
use Carbon\Carbon;
use App\Models\Tenant\CashDocumentCredit;
use App\Models\Tenant\Cash;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Person;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentPaymentController extends Controller
{
    use FinanceTrait, FilePaymentTrait;

    public function records($document_id)
    {
        $records = DocumentPayment::where('document_id', $document_id)->get();

        return new DocumentPaymentCollection($records);
    }

    public function tables()
    {
        return [
            'payment_method_types' => PaymentMethodType::all(),
            'payment_destinations' => $this->getPaymentDestinations(),
            'permissions' => auth()->user()->getPermissionsPayment()
        ];
    }

    public function document($document_id)
    {
        $document = Document::find($document_id);

        if ($document->retention) {
            $total = $document->total - $document->retention->amount;
        } else {
            $total = $document->total;
        }

        $total_paid = collect($document->payments)->sum('payment');

        $credit_notes_total = $document->getCreditNotesTotal();

        $total_difference = round($total - $total_paid - $credit_notes_total, 2);
        // $total_difference = round($total - $total_paid, 2);

        return [
            'number_full' => $document->number_full,
            'total_paid' => $total_paid,
            'total' => $total,
            'total_difference' => $total_difference,
            'currency_type_id' => $document->currency_type_id,
            'exchange_rate_sale' => (float) $document->exchange_rate_sale,
            'credit_notes_total' => $credit_notes_total,
            'external_id' => $document->external_id,
        ];

    }

    public function store(DocumentPaymentRequest $request)
    {
        // dd($request->all());

        $id = $request->input('id');

        $data = DB::connection('tenant')->transaction(function () use ($id, $request) {

            $record = DocumentPayment::firstOrNew(['id' => $id]);
            $record->fill($request->all());
            $record->save();
            $this->createGlobalPayment($record, $request->all());
            $this->saveFiles($record, $request, 'documents');

            return $record;
        });

        $document_balance = (object)$this->document($request->document_id);

        if ($document_balance->total_difference < 1) {

            $credit = CashDocumentCredit::where([
                ['status', 'PENDING'],
                ['document_id', $request->document_id]
            ])->first();

            if ($credit) {

                $cash = Cash::where([
                    ['user_id', auth()->user()->id],
                    ['state', true],
                ])->first();

                $credit->status = 'PROCESSED';
                $credit->cash_id_processed = $cash->id;
                $credit->save();

                $req = [
                    'document_id' => $request->document_id,
                    'sale_note_id' => null
                ];

                $cash->cash_documents()->updateOrCreate($req);

            }

        }
        if($id){

            $asientos = AccountingEntries::where('document_id','CF'.$id)->get();
            foreach($asientos as $ass){
                $ass->delete();
            }
        }

        if((Company::active())->countable > 0 ){
            $this->createAccountingEntry($request->document_id, $data);
        }

        return [
            'success' => true,
            'message' => ($id) ? 'Pago editado con éxito' : 'Pago registrado con éxito',
            'id' => $data->id,
        ];
    }

    /* Crear los asientos contables del documento */
    private function createAccountingEntry($document_id, $request){

        $document = Document::find($document_id);
        Log::info('documento created: ' . json_encode($document));
        $entry = (AccountingEntries::get())->last();

        if($document && $document->document_type_id == '01'){

            try{
                $idauth = auth()->user()->id;
                $lista = AccountingEntries::where('user_id', '=', $idauth)->latest('id')->first();
                $ultimo = AccountingEntries::latest('id')->first();
                $configuration = Configuration::first();
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

                $comment = 'Cobro Factura F'. $document->establishment->code . substr($document->series,1). str_pad($document->number,'9','0',STR_PAD_LEFT).' '. $document->customer->name ;

                $total_debe = 0;
                $total_haber = 0;

                $cabeceraC = new AccountingEntries();
                $cabeceraC->user_id = $document->user_id;
                $cabeceraC->seat = $seat;
                $cabeceraC->seat_general = $seat_general;
                $cabeceraC->seat_date = $document->date_of_issue;
                $cabeceraC->types_accounting_entrie_id = 1;
                $cabeceraC->comment = $comment;
                $cabeceraC->serie = null;
                $cabeceraC->number = $seat;
                $cabeceraC->total_debe = $request->payment;
                $cabeceraC->total_haber = $request->payment;
                $cabeceraC->revised1 = 0;
                $cabeceraC->user_revised1 = 0;
                $cabeceraC->revised2 = 0;
                $cabeceraC->user_revised2 = 0;
                $cabeceraC->currency_type_id = $document->currency_type_id;
                $cabeceraC->doctype = $document->document_type_id;
                $cabeceraC->is_client = ($document->customer)?true:false;
                $cabeceraC->establishment_id = $document->establishment_id;
                $cabeceraC->establishment = $document -> establishment;
                $cabeceraC->prefix = 'ASC';
                $cabeceraC->person_id = $document->customer_id;
                $cabeceraC->external_id = Str::uuid()->toString();
                $cabeceraC->document_id = 'CF'.$request->id;

                $cabeceraC->save();
                $cabeceraC->filename = 'ASC-'.$cabeceraC->id.'-'. date('Ymd');
                $cabeceraC->save();

                $customer = Person::find($cabeceraC->person_id);

                $detalle = new AccountingEntryItems();
                $ceuntaC = PaymentMethodType::find($request->payment_method_type_id);
                $detalle->accounting_entrie_id = $cabeceraC->id;
                $detalle->account_movement_id = ($customer->account) ? $customer->account : $configuration->cta_clients;
                $detalle->seat_line = 1;
                $detalle->debe = 0;
                $detalle->haber = $request->payment;
                $detalle->save();

                $detalle2 = new AccountingEntryItems();
                $detalle2->accounting_entrie_id = $cabeceraC->id;
                $detalle2->account_movement_id = ($ceuntaC && $ceuntaC->countable_acount)?$ceuntaC->countable_acount:$configuration->cta_charge;
                $detalle2->seat_line = 2;
                $detalle2->debe = $request->payment;
                $detalle2->haber = 0;
                $detalle2->save();

            }catch(Exception $ex){

                Log::error('Error al intentar generar el asiento contable');
                Log::error($ex->getMessage());
            }

        }else{
            Log::info('tipo de documento no genera asiento contable de momento');
        }

    }

    public function destroy($id)
    {
        $item = DocumentPayment::findOrFail($id);
        $item->delete();

        $asientos = AccountingEntries::where('document_id','CF'.$id)->get();
        foreach($asientos as $ass){
            $ass->delete();
        }
        $asientos2 = AccountingEntries::where('document_id','PC'.$id)->get();
        foreach($asientos2 as $ass){
            $ass->delete();
        }

        return [
            'success' => true,
            'message' => 'Pago eliminado con éxito'
        ];
    }

    public function initialize_balance()
    {

        DB::connection('tenant')->transaction(function () {

            $documents = Document::get();

            foreach ($documents as $document) {

                $total_payments = $document->payments->sum('payment');

                $balance = $document->total - $total_payments;

                if ($balance <= 0) {

                    $document->total_canceled = true;
                    $document->update();

                } else {

                    $document->total_canceled = false;
                    $document->update();
                }

            }

        });

        return [
            'success' => true,
            'message' => 'Acción realizada con éxito'
        ];
    }

    public function report($start, $end, $type = 'pdf')
    {
        $documents = DocumentPayment::whereBetween('date_of_payment', [$start, $end])->get();

        $records = collect($documents)->transform(function ($row) {
            return [
                'id' => $row->id,
                'date_of_payment' => $row->date_of_payment->format('d/m/Y'),
                'payment_method_type_description' => $row->payment_method_type->description,
                'destination_description' => ($row->global_payment) ? $row->global_payment->destination_description : null,
                'change' => $row->change,
                'payment' => $row->payment,
                'reference' => $row->reference,
                'customer' => $row->document->customer->name,
                'number' => $row->document->number_full,
                'total' => $row->document->total,
            ];
        });

        if ($type == 'pdf') {
            $pdf = PDF::loadView('tenant.document_payments.report', compact("records"));

            $filename = "Reporte_Pagos";

            return $pdf->stream($filename . '.pdf');
        } elseif ($type == 'excel') {
            $filename = "Reporte_Pagos";

            // $pdf = PDF::loadView('tenant.document_payments.report', compact("records"))->download($filename.'.xlsx');

            // return $pdf->stream($filename.'.xlsx');

            return (new DocumentPaymentExport)
                ->records($records)
                ->download($filename . Carbon::now() . '.xlsx');
        }

    }

}

<?php
namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AccountingEntries;
use App\Models\Tenant\AccountingEntryItems;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use Modules\Purchase\Http\Resources\PurchasePaymentCollection;
use Modules\Purchase\Http\Requests\PurchasePaymentRequest;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Person;
use App\Models\Tenant\PurchasePayment;
use App\Models\Tenant\Purchase;
use Exception;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Finance\Traits\FilePaymentTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PurchasePaymentController extends Controller
{
    use FinanceTrait, FilePaymentTrait;

    public function records($purchase_id)
    {
        $records = PurchasePayment::where('purchase_id', $purchase_id)->get();

        return new PurchasePaymentCollection($records);
    }

    public function tables()
    {
        return [
            'payment_method_types' => PaymentMethodType::all(),
            'payment_destinations' => $this->getPaymentDestinations()
        ];
    }

    public function purchase($purchase_id)
    {
        $purchase = Purchase::find($purchase_id);

        $total_paid = collect($purchase->payments)->sum('payment');
        $total = $purchase->total;
        $total_difference = round($total - $total_paid, 2);

        return [
            'number_full' => $purchase->number_full,
            'total_paid' => $total_paid,
            'total' => $total,
            'total_difference' => $total_difference
        ];

    }


    public function store(PurchasePaymentRequest $request)
    {
        $id = $request->input('id');

        $data = DB::connection('tenant')->transaction(function () use ($id, $request) {

            $record = PurchasePayment::firstOrNew(['id' => $id]);
            $record->fill($request->all());
            $record->save();
            $this->createGlobalPayment($record, $request->all());
            $this->saveFiles($record, $request, 'purchases');

            return $record;
        });

        if($id){

            $asientos = AccountingEntries::where('document_id','PC'.$id)->get();
            foreach($asientos as $ass){
                $ass->delete();
            }
        }

        if((Company::active())->countable > 0 ){

            $this->createAccountingEntryPayment($data->purchase_id, $data);
        }

        return [
            'success' => true,
            'message' => ($id)?'Pago editado con éxito':'Pago registrado con éxito'
        ];
    }

     /* Crear los asientos contables de los pagos */
     private function createAccountingEntryPayment($document_id,$payment){

        $document = Purchase::find($document_id);
        log::info('Documento type : '.$document->document_type_id);
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

                $comment = 'Pago factura de compra '. substr($document->series,0). str_pad($document->number,'9','0',STR_PAD_LEFT).' '. $document->supplier->name ;

                $total_debe = $payment->payment;
                $total_haber = $payment->payment;

                $cabeceraC = new AccountingEntries();
                $cabeceraC->user_id = $document->user_id;
                $cabeceraC->seat = $seat;
                $cabeceraC->seat_general = $seat_general;
                $cabeceraC->seat_date = $document->date_of_issue;
                $cabeceraC->types_accounting_entrie_id = 1;
                $cabeceraC->comment = $comment;
                $cabeceraC->serie = null;
                $cabeceraC->number = $seat;
                $cabeceraC->total_debe = $total_debe;
                $cabeceraC->total_haber = $total_haber;
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
                $cabeceraC->person_id = $document->supplier_id;
                $cabeceraC->external_id = Str::uuid()->toString();
                $cabeceraC->document_id = 'PC'.$payment->id;

                $cabeceraC->save();
                $cabeceraC->filename = 'ASC-'.$cabeceraC->id.'-'. date('Ymd');
                $cabeceraC->save();

                $customer = Person::find($cabeceraC->person_id);
                $ceuntaC = PaymentMethodType::find($payment->payment_method_type_id);
                $detalle = new AccountingEntryItems();

                $detalle->accounting_entrie_id = $cabeceraC->id;
                $detalle->account_movement_id = ($customer->account) ? $customer->account : $configuration->cta_suppliers;
                $detalle->seat_line = 1;
                $detalle->haber = 0;
                $detalle->debe = $payment->payment;
                $detalle->save();

                $detalle2 = new AccountingEntryItems();
                $detalle2->accounting_entrie_id = $cabeceraC->id;
                $detalle2->account_movement_id = ($ceuntaC && $ceuntaC->countable_acount_payment)?$ceuntaC->countable_acount_payment:$configuration->cta_paymnets;
                $detalle2->seat_line = 2;
                $detalle2->haber = $payment->payment;
                $detalle2->debe =0;
                $detalle2->save();

            }catch(Exception $ex){

                Log::error('Error al intentar generar el asiento contable del pago de compra');
                Log::error($ex->getMessage());
            }


        }else{
            Log::info('tipo de documento no genera asiento contable de momento');
        }

    }

    public function destroy($id)
    {
        $item = PurchasePayment::findOrFail($id);
        $item->delete();

        $asientos = AccountingEntries::where('document_id','PC'.$id)->get();
        foreach($asientos as $ass){
            $ass->delete();
        }

        return [
            'success' => true,
            'message' => 'Pago eliminado con éxito'
        ];
    }



}

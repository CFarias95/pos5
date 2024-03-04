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
use Exception;
use Illuminate\Http\Request;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Finance\Traits\FilePaymentTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Finance\Http\Controllers\ToPayController;
use Modules\Finance\Models\GlobalPayment;

class PurchasePaymentController extends Controller
{
    use FinanceTrait, FilePaymentTrait;

    public function records($purchase_id, $fee_id)
    {
        $records = PurchasePayment::where('purchase_id', $purchase_id)->get();
        if ($fee_id != 'undefined') {

            $records = PurchasePayment::where('fee_id', $fee_id)->get();
        }
        return new PurchasePaymentCollection($records);
    }

    public function tables()
    {
        return [
            'payment_method_types' => PaymentMethodType::all(),
            'payment_destinations' => $this->getPaymentDestinations(),
            'accounts' => AccountMovement::get()->transform(function($row){
                return[
                    'description' => $row->code .' '.$row->description,
                    'id'=> $row->id
                ];

            })
        ];
    }

    public function purchase($purchase_id, $fee_id)
    {
        $purchase = Purchase::find($purchase_id);
        $total_paid = collect($purchase->payments)->sum('payment');
        $total = $purchase->total;
        $total_difference = round($total - $total_paid, 2);

        if (isset($fee_id) && $fee_id != 'undefined' && $fee_id != null) {

            $cuota = (PurchaseFee::find($fee_id))?PurchaseFee::find($fee_id)->amount:0;
            $total_paid = PurchasePayment::where('fee_id', $fee_id)->get()->sum('payment');
            $total_difference = round($cuota - $total_paid, 2);
        }
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

        $fee = PurchaseFee::where('purchase_id', $request->purchase_id)->orderBY('date')->get();


        if ($request['payment_method_type_id'] == '99' && !$id) {

            $reference = $request['reference'];
            $monto = floatval($request['payment']);
            $retention = Retention::find($reference);
            $valor = $retention->total_used;
            $montoUsado = $valor + $monto;
            $retention->total_used = $montoUsado;
            $retention->in_use = true;
            $retention->save();

        } else if ($request['payment_method_type_id'] == '99' && $id) {

            $pagoAnt = PurchasePayment::first(['id' => $id])->payment;
            $reference = $request['reference'];
            $monto = floatval($request['payment']);
            $retention = Retention::find($reference);
            $valor = $retention->total_used;
            $montoUsado = $valor + $monto - $pagoAnt;
            $retention->total_used = $montoUsado;
            $retention->in_use = true;
            $retention->save();

        }else if ($request['payment_method_type_id'] == '16' && $id) {

            $reference = $request['reference'];
            $pagoAnt = PurchasePayment::first(['id' => $id])->payment;
            $monto = floatval($request['payment']);
            $credit = CreditNotesPayment::find($reference);
            $valor = $credit->used;
            $montoUsado = $valor + $monto - $pagoAnt;
            $credit->used = ($montoUsado > 0 )?$montoUsado:0;
            $credit->in_use = ($montoUsado > 0)? true : false;
            $credit->save();
        }else if ($request['payment_method_type_id'] == '16' && !$id) {

            $reference = $request['reference'];
            $monto = floatval($request['payment']);
            $credit = CreditNotesPayment::find($reference);
            $valor = $credit->used;
            $montoUsado = $valor + $monto;
            $credit->used = ($montoUsado > 0 )?$montoUsado:0;
            $credit->in_use = ($montoUsado > 0)? true : false;
            $credit->save();
        }

        if ($fee->count() > 0) {
            $valorPagar = $request->payment;
            $fee_id = $request->input('fee_id');

            foreach ($fee as $cuotas) {

                $sequential = PurchasePayment::orderBy('sequential', 'desc')->first();

                $pago = PurchasePayment::where('fee_id', $cuotas->id)->get();
                $pagado = $pago->sum('payment');
                //Log::info("fee pago:" . json_encode($pago));
                $valorCuota = $cuotas->amount - $pagado;
                $cuotaid = $cuotas->id;

                if (isset($fee_id) && $cuotaid == $fee_id) {
                    if ($valorPagar > 0 && $valorPagar >= $valorCuota) {

                        $data = DB::connection('tenant')->transaction(function () use ($sequential ,$id, $request, $valorCuota, $cuotaid) {

                            $record = PurchasePayment::firstOrNew(['id' => $id]);
                            $record->fill($request->all());
                            $record->payment = $valorCuota;
                            $record->fee_id = $cuotaid;
                            $record->sequential = $sequential->sequential +1;
                            $record->save();

                            $this->createGlobalPayment($record, $request->all());
                            $this->saveFiles($record, $request, 'documents');

                            return $record;
                        });

                        $valorPagar = $valorPagar - $valorCuota;
                    } else if ($valorPagar > 0 && $valorPagar < $valorCuota) {

                        $data = DB::connection('tenant')->transaction(function () use ($sequential, $id, $request, $valorPagar, $cuotaid) {

                            unset($request->id);
                            //$request->payment = $valorPagar;
                            $record = new PurchasePayment();
                            $record->fill($request->all());
                            $record->payment = $valorPagar;
                            $record->fee_id = $cuotaid;
                            $record->sequential = $sequential->sequential + 1;
                            $record->save();

                            $this->createGlobalPayment($record, $request->all());
                            $this->saveFiles($record, $request, 'documents');

                            return $record;
                        });

                        $valorPagar = 0;
                    }
                } else if (isset($fee_id) == false) {
                    if ($valorPagar > 0 && $valorPagar >= $valorCuota) {

                        $data = DB::connection('tenant')->transaction(function () use ($sequential, $id, $request, $valorCuota, $cuotaid) {

                            $record = PurchasePayment::firstOrNew(['id' => $id]);
                            $record->fill($request->all());
                            $record->payment = $valorCuota;
                            $record->fee_id = $cuotaid;
                            $record->sequential = $sequential->sequential + 1;
                            $record->save();

                            $this->createGlobalPayment($record, $request->all());
                            $this->saveFiles($record, $request, 'documents');

                            return $record;
                        });

                        $valorPagar = $valorPagar - $valorCuota;
                    } else if ($valorPagar > 0 && $valorPagar < $valorCuota) {

                        $data = DB::connection('tenant')->transaction(function () use ($sequential, $id, $request, $valorPagar, $cuotaid) {

                            unset($request->id);
                            //$request->payment = $valorPagar;
                            $record = new PurchasePayment();
                            $record->fill($request->all());
                            $record->payment = $valorPagar;
                            $record->fee_id = $cuotaid;
                            $record->sequential = $sequential->sequential + 1;
                            $record->save();

                            $this->createGlobalPayment($record, $request->all());
                            $this->saveFiles($record, $request, 'documents');

                            return $record;
                        });

                        $valorPagar = 0;
                    }
                }
            }
        } else {
            $data = DB::connection('tenant')->transaction(function () use ($id, $request) {
                $sequential = PurchasePayment::orderBy('sequential', 'desc')->first();
                $record = PurchasePayment::firstOrNew(['id' => $id]);
                $record->fill($request->all());
                $record->sequential = $sequential->sequential + 1;
                $record->save();
                $this->createGlobalPayment($record, $request->all());
                $this->saveFiles($record, $request, 'purchases');
                return $record;
            });

            if ($id) {

                $asientos = AccountingEntries::where('document_id', 'PC' . $id)->get();
                foreach ($asientos as $ass) {
                    $ass->delete();
                }
            }
        }

        if ((Company::active())->countable > 0) {

            $this->createAccountingEntryPayment($data->purchase_id, $data);
        }
        $this->verifyPayment($request);

        return [
            'success' => true,
            'message' => ($id) ? 'Pago editado con éxito' : 'Pago registrado con éxito'
        ];
    }

    /*VERIFICAR SI ES PAGO CON ANTICIPO Y ACTUALIZAR */
    public function verifyPayment($request){

        if($request['payment_method_type_id'] == 14 || $request['payment_method_type_id'] == 15){
            //ANTICIPOS DE CLIENTES O PROVEEDORES
            $ref = $request['reference'];
            $acticipo  = Advance::find($ref);
            $acticipo->in_use = true;
            $acticipo->save();
        }
    }
    /* Crear los asientos contables de los pagos */
    private function createAccountingEntryPayment($document_id, $payment)
    {
        $document = Purchase::find($document_id);
        if ($document && $document->document_type_id != '04' ) {

            try {
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

                $comment = $payment->reference.' | Pago factura de compra ' . substr($document->series, 0) . str_pad($document->number, '9', '0', STR_PAD_LEFT) . ' ' . $document->supplier->name;

                $total_debe = $payment->payment;
                $total_haber = $payment->payment;

                $cabeceraC = new AccountingEntries();
                $cabeceraC->user_id = $document->user_id;
                $cabeceraC->seat = $seat;
                $cabeceraC->seat_general = $seat_general;
                $cabeceraC->seat_date = $payment->date_of_payment;
                $cabeceraC->types_accounting_entrie_id = 5;
                $cabeceraC->comment = $comment;
                $cabeceraC->serie = 'PAGO COMPRA';
                $cabeceraC->number = $seat;
                $cabeceraC->total_debe = $total_debe;
                $cabeceraC->total_haber = $total_haber;
                $cabeceraC->revised1 = 0;
                $cabeceraC->user_revised1 = 0;
                $cabeceraC->revised2 = 0;
                $cabeceraC->user_revised2 = 0;
                $cabeceraC->currency_type_id = $document->currency_type_id;
                $cabeceraC->doctype = $document->document_type_id;
                $cabeceraC->is_client = ($document->customer) ? true : false;
                $cabeceraC->establishment_id = $document->establishment_id;
                $cabeceraC->establishment = $document->establishment;
                $cabeceraC->prefix = 'ASC';
                $cabeceraC->person_id = $document->supplier_id;
                $cabeceraC->external_id = Str::uuid()->toString();
                $cabeceraC->document_id = 'PC' . $payment->id;

                $cabeceraC->save();
                $cabeceraC->filename = 'ASC-' . $cabeceraC->id . '-' . date('Ymd');
                $cabeceraC->save();

                $customer = Person::find($cabeceraC->person_id);
                $ceuntaC = PaymentMethodType::find($payment->payment_method_type_id);

                $detalle = new AccountingEntryItems();
                $detalle->accounting_entrie_id = $cabeceraC->id;
                $detalle->account_movement_id = ($customer->account) ? $customer->account : $configuration->cta_suppliers;
                $detalle->seat_line = 1;
                $detalle->haber = 0;
                $detalle->debe = $payment->payment;
                if($detalle->save() == false){
                    $cabeceraC->delete();
                    return;
                    //abort(500,'No se pudo generar el asiento contable del documento');
                }

                if($payment->payment_method_type_id == '99'){

                    $haber = $payment->payment;
                    $reference = $payment->reference;
                    $retention = Retention::find($reference);
                    $detRet = $retention->optional;
                    $seat = 2;

                    foreach ($detRet as $ret) {

                        $valor = (is_array($ret) == true)?floatval($ret['valorRetenido']):floatval($ret->valorRetenido);
                        $haberInterno = 0;
                        if($valor >=  $haber){
                            $haberInterno = $haber;
                            $haber = 0;
                        }
                        if($valor < $haber){
                            $haberInterno = $valor;
                            $haber -=  $valor;
                        }

                        $detalle2 = new AccountingEntryItems();
                        $detalle2->accounting_entrie_id = $cabeceraC->id;
                        $detalle2->account_movement_id = ($ceuntaC && $ceuntaC->countable_acount_payment) ? $ceuntaC->countable_acount_payment : $configuration->cta_paymnets;
                        $detalle2->seat_line = $seat;
                        $detalle2->haber = $haberInterno;
                        $detalle2->debe = 0;
                        if($detalle2->save() == false){
                            $cabeceraC->delete();
                            break;
                            //abort(500,'No se pudo generar el asiento contable del documento');
                        }

                        $seat += 1;
                    }
                }elseif($payment->payment_method_type_id != '14'){
                    $detalle2 = new AccountingEntryItems();
                    $detalle2->accounting_entrie_id = $cabeceraC->id;
                    $detalle2->account_movement_id = ($ceuntaC && $ceuntaC->countable_acount_payment) ? $ceuntaC->countable_acount_payment : $configuration->cta_paymnets;
                    $detalle2->seat_line = 2;
                    $detalle2->haber = $payment->payment;
                    $detalle2->debe = 0;
                    if($detalle2->save() == false){
                        $cabeceraC->delete();
                        return;
                        //abort(500,'No se pudo generar el asiento contable del documento');
                    }
                }


            } catch (Exception $ex) {

                Log::error('Error al intentar generar el asiento contable del pago de compra');
                Log::error($ex->getMessage());
            }
        } else {
            Log::info('tipo de documento no genera asiento contable de momento');
        }
    }

    public function destroy($id)
    {
        $item = PurchasePayment::findOrFail($id);

        if($item->payment_method_type_id == '99'){

            $monto = $item->payment;
            $reference = $item->reference;

            $retention = Retention::find($reference);
            $valor = $retention->total_used;
            $montoUsado = $valor - $monto;
            $retention->total_used = $montoUsado;
            $retention->in_use = ($montoUsado > 0 )?true:false;
            $retention->save();

        }

        if($item->payment_method_type_id == '16'){

            $monto = $item->payment;
            $reference = $item->reference;

            $retention = CreditNotesPayment::find($reference);
            $valor = $retention->amount;
            $montoUsado = $valor - $monto;
            $retention->used = $montoUsado;
            $retention->in_use = ($montoUsado > 0 )?true:false;
            $retention->save();

        }

        $sequential = $item->sequential;
        $multiPAy = $item->multipay;

        $item->delete();

        if($multiPAy == 'SI'){

            Log::info('BORRANDO PAGO CON MULTIPAGO');
            $item = PurchasePayment::where('sequential',$sequential)->get();
            foreach ($item as $value) {
                $value->delete();
            }

            $asientos = AccountingEntries::where('document_id','like','%PC'.$id.';%')->get();
            foreach($asientos as $ass){
                $ass->delete();
            }

        }else{

            $asientos = AccountingEntries::where('document_id', 'PC' . $id)->get();
            foreach ($asientos as $ass) {
                $ass->delete();
            }
        }

        return [
            'success' => true,
            'message' => 'Pago eliminado con éxito'
        ];
    }

    public function generateReverse(Request $request){

        Log::info('generateReverse PURCHACE PAYMENT');

        $id = $request->id;
        $motivo = $request->reference;

        $payment = PurchasePayment::find($id);
        $globalPayment = GlobalPayment::where('payment_id',$id)->where('payment_type','like','%PurchasePayment')->first();
        $sequential = PurchasePayment::orderBy('sequential', 'desc')->first();

        if(isset($payment) && $payment->multipay == 'NO'){
            Log::info('Sin MULTIPAGO');
            $newPayment = new PurchasePayment();
            $newPayment->purchase_id = $payment->purchase_id;
            $newPayment->date_of_payment = date('Y-m-d');
            $newPayment->payment_method_type_id = $payment->payment_method_type_id;
            $newPayment->has_card = $payment->has_card;
            $newPayment->card_brand_id = $payment->card_brand_id;
            $newPayment->reference = $motivo;
            //$newPayment->change = $payment->change;
            $newPayment->payment = $payment->payment * -1;
            //$newPayment->payment_received = $payment->payment_received;
            $newPayment->fee_id = $payment->fee_id;
            $newPayment->postdated = $payment->postdated;
            $newPayment->sequential = $sequential->sequential + 1;
            $newPayment->save();

            $newGlobalPayment = new GlobalPayment();
            $newGlobalPayment->soap_type_id = $globalPayment->soap_type_id;
            $newGlobalPayment->destination_id = $globalPayment->destination_id;
            $newGlobalPayment->destination_type = $globalPayment->destination_type;
            $newGlobalPayment->payment_id = $newPayment->id;
            $newGlobalPayment->payment_type = $globalPayment->payment_type;
            $newGlobalPayment->user_id = $globalPayment->user_id;
            $newGlobalPayment->save();

            $this->createAccountingEntryReverse('PC'.$newPayment->id,$id);

            return [
                'success'=>true,
                'message' => 'Reverso generado de forma exitosa!'
            ];

        }elseif(isset($payment) && $payment->multipay == 'SI'){

            Log::info('Sin MULTIPAGO');
            $multiPays = PurchasePayment::where('sequential',$payment->sequential)->get();
            $paymentsIds = '';
            $sequential = PurchasePayment::orderBy('sequential', 'desc')->first();

            foreach ($multiPays as $value) {
                $paymentM = PurchasePayment::find($value->id);
                $globalPayment = GlobalPayment::where('payment_id',$id)->first();


                $newPayment = new PurchasePayment();
                $newPayment->purchase_id = $paymentM->purchase_id;
                $newPayment->date_of_payment = date('Y-m-d');
                $newPayment->payment_method_type_id = $paymentM->payment_method_type_id;
                $newPayment->has_card = $paymentM->has_card;
                $newPayment->card_brand_id = $paymentM->card_brand_id;
                $newPayment->reference = $motivo;
                //$newPayment->change = $paymentM->change;
                $newPayment->payment = $paymentM->payment * -1;
                //$newPayment->payment_received = $paymentM->payment_received;
                $newPayment->fee_id = $paymentM->fee_id;
                $newPayment->postdated = $paymentM->postdated;
                $newPayment->sequential = $sequential->sequential + 1;
                $newPayment->multipay = 'SI';
                $newPayment->save();

                $paymentsIds .= 'PC'.$newPayment->id.';';

                $newGlobalPayment = new GlobalPayment();
                $newGlobalPayment->soap_type_id = $globalPayment->soap_type_id;
                $newGlobalPayment->destination_id = $globalPayment->destination_id;
                $newGlobalPayment->destination_type = $globalPayment->destination_type;
                $newGlobalPayment->payment_id = $newPayment->id;
                $newGlobalPayment->payment_type = $globalPayment->payment_type;
                $newGlobalPayment->user_id = $globalPayment->user_id;
                $newGlobalPayment->save();
            }

            //$unp = new ToPayController();
            $this->createAccountingEntryReverse($paymentsIds,$id);

            return [
                'success'=>true,
                'message' => 'Reverso generado de forma exitosa!'
            ];

        }else{
            Log::error('No se encontro un pago con el ID: '.$id);
        }


    }

     /* Crear los asientos contables del REVERSO */
    private function createAccountingEntryReverse($documents, $id){

        Log::info('Generando asiento de reverso: PC'.$id);
            try{
                $idauth = auth()->user()->id;
                $lista = AccountingEntries::where('user_id', '=', $idauth)->latest('id')->first();
                $ultimo = AccountingEntries::latest('id')->first();
                $configuration = Configuration::first();
                $accountrieEntryActual = AccountingEntries::where('document_id','PC'.$id)->orWhere('document_id','like','%PC'.$id.';%')->first();
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

                $comment = 'Reverso '.$accountrieEntryActual->comment;

                $cabeceraC = new AccountingEntries();
                $cabeceraC->fill($accountrieEntryActual->toArray());
                $cabeceraC->id = null;
                $cabeceraC->user_id = $idauth;
                $cabeceraC->seat = $seat;
                $cabeceraC->serie = 'REVERSO PAGO COMPRA';
                $cabeceraC->seat_general = $seat_general;
                $cabeceraC->seat_date = date('y-m-d');
                $cabeceraC->comment = $comment;
                $cabeceraC->number = $seat;
                $cabeceraC->total_debe = $accountrieEntryActual->total_haber;
                $cabeceraC->total_haber = $accountrieEntryActual->total_debe;
                $cabeceraC->revised1 = 0;
                $cabeceraC->user_revised1 = 0;
                $cabeceraC->revised2 = 0;
                $cabeceraC->user_revised2 = 0;
                $cabeceraC->external_id = Str::uuid()->toString();
                $cabeceraC->document_id = $documents;

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

            }catch(Exception $ex){

                Log::error('Error al intentar generar el asiento contable');
                Log::error($ex->getMessage());
            }

            /*
        }else{
            Log::info('tipo de documento no genera asiento contable de momento');
        } */

    }

    public function generateExpenses(Request $request){

        $id = $request->id;
        $valor = $request->overPaymentValue;
        $cuenta = $request->overPaymentAccount;

        $entry = AccountingEntries::where('document_id','PC'.$id)->orWhere('document_id','like','%PC'.$id.';%')->first();
        if(isset($entry)){
            $entry->total_debe += $valor;
            $entry->total_haber += $valor;

            $entryItems = AccountingEntryItems::where('accounting_entrie_id',$entry->id)->get();
            foreach($entryItems as $item){
                if($item->haber > 0){
                    $item->haber += $valor;
                    $item->save();
                }
            }

            $detalle = new AccountingEntryItems();
            $detalle->accounting_entrie_id = $entryItems[0]->accounting_entrie_id;
            $detalle->account_movement_id = $cuenta;
            $detalle->seat_line = 3;
            $detalle->debe = $valor;
            $detalle->haber = 0;
            $detalle->save();

            return[
                'success' => true,
                'message' => 'Valor extra agregado al pago'
            ];

        }else{
            return[
                'success' => false,
                'message' => 'No se pudo agregar el valor extra al pago'
            ];
        }
    }

}

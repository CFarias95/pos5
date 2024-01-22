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
use App\Models\Tenant\AccountMovement;
use App\Models\Tenant\Advance;
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
use App\Models\Tenant\CreditNotesPayment;
use App\Models\Tenant\DocumentFee;
use App\Models\Tenant\Person;
use App\Models\Tenant\Retention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Finance\Http\Controllers\AdvanceController;
use Modules\Finance\Http\Controllers\UnpaidController;
use Modules\Finance\Http\Requests\AdvanceRequest;
use Modules\Finance\Models\GlobalPayment;

class DocumentPaymentController extends Controller
{
    use FinanceTrait, FilePaymentTrait;

    public function records($document_id,$fee_id = 'null')
    {
        Log::info($fee_id);

        $records = DocumentPayment::where('document_id', $document_id)->get();

        if($fee_id != 'undefined' && $fee_id != 'null'){

            $records = DocumentPayment::where('fee_id', $fee_id)->get();
        }

        return new DocumentPaymentCollection($records);
    }

    public function tables()
    {
        return [
            'payment_method_types' => PaymentMethodType::all(),
            'payment_destinations' => $this->getPaymentDestinations(),
            'permissions' => auth()->user()->getPermissionsPayment(),
            'accounts' => AccountMovement::get()->transform(function($row){
                return[
                    'description' => $row->code .' '.$row->description,
                    'id'=> $row->id
                ];

            })
        ];
    }

    public function document($document_id,$fee_id = 'null' )
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

        $full_nale = $document->number_full;

        if($fee_id !== 'null' ){

            $document = DocumentFee::find($fee_id);
            $cuota = ($document)?$document->amount:0;
            $total_paid = DocumentPayment::where('fee_id',$fee_id)->get()->sum('payment');
            $total_difference = round($cuota - $total_paid, 2);
        }

        return [
            'number_full' => $full_nale,
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
        $id = $request->input('id');
        $data = $request->all();

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

            $pagoAnt = DocumentPayment::first(['id' => $id])->payment;
            $reference = $request['reference'];
            $monto = floatval($request['payment']);
            $retention = Retention::find($reference);
            $valor = $retention->total_used;
            $montoUsado = $valor + $monto - $pagoAnt;
            $retention->total_used = $montoUsado;
            $retention->in_use = true;
            $retention->save();
        }else if ($request['payment_method_type_id'] == '16' && $id) {

            $pagoAnt = DocumentPayment::first(['id' => $id])->payment;
            $reference = $request['reference'];
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

        $fee = DocumentFee::where('document_id', $request->document_id)->orderBy('date')->get();
        if($fee->count() > 0 ){
            $valorPagar = $request->payment;
            $fee_id = $request->input('fee_id');

            foreach($fee as $cuotas){

                $pago = DocumentPayment::where('fee_id',$cuotas->id)->get();
                $pagado = $pago->sum('payment');

                if($pagado > 0 ){

                    $valorCuota = $cuotas->amount - $pagado;
                    $cuotaid = $cuotas->id;
                    $sequential = DocumentPayment::latest('id')->first();

                    if(isset($fee_id) && $cuotaid == $fee_id){
                        if( $valorPagar > 0 && $valorPagar >= $valorCuota){

                            $data = DB::connection('tenant')->transaction(function () use ( $sequential, $id, $request, $valorCuota, $cuotaid) {

                                $record = DocumentPayment::firstOrNew(['id' => $id]);
                                $record->fill($request->all());
                                $record->sequential = $sequential->sequential + 1;
                                $record->payment = $valorCuota;
                                $record->fee_id = $cuotaid;
                                $record->save();

                                $this->createGlobalPayment($record, $request->all());
                                $this->saveFiles($record, $request, 'documents');

                                return $record;
                            });

                            $valorPagar = $valorPagar - $valorCuota;

                        }else if ($valorPagar > 0 && $valorPagar < $valorCuota){

                            $data = DB::connection('tenant')->transaction(function () use ($sequential, $id, $request, $valorPagar, $cuotaid) {

                                unset($request->id);
                                //$request->payment = $valorPagar;
                                $record = new DocumentPayment();
                                $record->fill($request->all());
                                $record->payment = $valorPagar;
                                $record->fee_id = $cuotaid;
                                $record->sequential = $sequential->sequential + 1;
                                $record->save();

                                $this->createGlobalPayment($record, $request->all());
                                $this->saveFiles($record, $request, 'documents');

                                return $record;
                            });

                            $valorPagar = 0 ;
                        }
                    }else if(isset($fee_id) == false){
                        if( $valorPagar > 0 && $valorPagar >= $valorCuota){

                            $data = DB::connection('tenant')->transaction(function () use ($sequential, $id, $request, $valorCuota, $cuotaid) {

                                $record = DocumentPayment::firstOrNew(['id' => $id]);
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

                        }else if ($valorPagar > 0 && $valorPagar < $valorCuota){

                            $data = DB::connection('tenant')->transaction(function () use ($sequential, $id, $request, $valorPagar, $cuotaid) {

                                unset($request->id);
                                //$request->payment = $valorPagar;
                                $record = new DocumentPayment();
                                $record->fill($request->all());
                                $record->payment = $valorPagar;
                                $record->fee_id = $cuotaid;
                                $record->sequential = $sequential->sequential + 1;
                                $record->save();

                                $this->createGlobalPayment($record, $request->all());
                                $this->saveFiles($record, $request, 'documents');

                                return $record;
                            });

                            $valorPagar = 0 ;
                        }
                    }
                }
            }

        }else{

            $data = DB::connection('tenant')->transaction(function() use ($id, $request) {
                $sequential = DocumentPayment::latest('id')->first();
                $record = DocumentPayment::firstOrNew(['id' => $id]);
                $record->fill($request->all());
                $record->sequential = $sequential->sequential + 1;
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
        }

        if($id){

            if($request['overPayment'] && $request['overPaymentAdvance']){
                $document = Document::find($request->document_id);
                $requestA = Advance::where('observation','like','%PAGO-'.$data->id)->first();
                if(isset($requestA)){

                    $requestA->id_payment = $request['payment_method_type_id'];
                    $requestA->reference =  $request['reference'];
                    $requestA->valor = $request['overPaymentValue'];
                    $requestA['generate_account'] = 0;

                    $advanceController = new AdvanceController();
                    $advanceController->store($requestA);

                }else{

                    $document = Document::find($request->document_id);
                    $requestA = new AdvanceRequest();
                    $requestA['id'] = null;
                    $requestA['idMethodType'] = '14';
                    $requestA['id_payment'] = $request['payment_method_type_id'];
                    $requestA['reference'] =  $request['reference'];
                    $requestA['idCliente'] = $document->customer_id;
                    $requestA['valor'] = $request['overPaymentValue'];
                    $requestA['observation'] = 'Anticipo generado por sobre pago, PAGO-'.$data->id;
                    $requestA['is_supplier'] = 0;
                    $requestA['in_use'] = 0;
                    $requestA['generate_account'] = 0;
                    $advanceController = new AdvanceController();
                    $advanceController->store($requestA);
                }
            }

        }else{

            if($request['overPayment'] && $request['overPaymentAdvance']){
                $document = Document::find($request->document_id);
                $requestA = new AdvanceRequest();
                $requestA['id'] = null;
                $requestA['idMethodType'] = '14';
                $requestA['id_payment'] = $request['payment_method_type_id'];
                $requestA['reference'] =  $request['reference'];
                $requestA['idCliente'] = $document->customer_id;
                $requestA['valor'] = $request['overPaymentValue'];
                $requestA['observation'] = 'Anticipo generado por sobre pago, PAGO-'.$data->id;
                $requestA['is_supplier'] = 0;
                $requestA['in_use'] = 0;
                $requestA['generate_account'] = 0;
                $advanceController = new AdvanceController();
                $advanceController->store($requestA);
            }
        }


        if((Company::active())->countable > 0 ){
            $this->createAccountingEntry($request, $data);
        }

        $this->verifyPayment($request);

        return [
            'success' => true,
            'message' => ($id) ? 'Pago editado con éxito' : 'Pago registrado con éxito',
            'id' => $data->id,
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
    /* Crear los asientos contables del documento */
    private function createAccountingEntry($requestP, $request){

        $document = Document::find($requestP->document_id);
        $entry = (AccountingEntries::get())->last();

        if($document && ($document->document_type_id == '01' || $document->document_type_id == '03')){

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

                $comment = (($document->document_type_id == '03')?'Cobro '.substr($document->series,0,1):'Cobro Factura F'). $document->establishment->code . substr($document->series,1). str_pad($document->number,'9','0',STR_PAD_LEFT).' '. $document->customer->name ;

                $total_debe = 0;
                $total_haber = 0;

                $cabeceraC = new AccountingEntries();
                $cabeceraC->user_id = $document->user_id;
                $cabeceraC->seat = $seat;
                $cabeceraC->seat_general = $seat_general;
                $cabeceraC->seat_date = date('y-m-d');
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
                $detalle->haber = $request->payment ;

                if($detalle->save() == false){
                    $cabeceraC->delete();
                    return;
                    //abort(500,'No se pudo generar el asiento contable del documento');
                }

                if($request->payment_method_type_id == '99'){
                    $debe = ($requestP['overPayment'] && $requestP['overPaymentAdvance'] == false) ? $request->payment + $requestP['overPaymentValue'] : $request->payment;
                    $reference = $request->reference;
                    $retention = Retention::find($reference);
                    $detRet = $retention->optional;
                    Log::error($detRet);
                    if(is_array($detRet) == false ){
                        $detRet = json_decode($detRet);
                    }
                    $seat = 2;

                    foreach ($detRet as $ret) {

                        if($debe > 0){
                            $valor = (is_array($ret) == true)?floatval($ret['valorRetenido']):floatval($ret->valorRetenido);
                            $debeInterno = 0;
                            $cuentaId = null;
                            if($valor >=  $debe){
                                $debeInterno = $debe;
                                $debe = 0;
                            }
                            if($valor < $debe){
                                $debeInterno = $valor;
                                $debe -=  $valor;
                            }
                            if(is_array($ret) &&  $ret['codigo']== '2' || isset($ret->codigo) && $ret->codigo == '2'){
                                $cuentaId=$ceuntaC->countable_acount;
                            }
                            if(is_array($ret) &&  $ret['codigo']== '1' || isset($ret->codigo) && $ret->codigo == '1'){
                                $cuentaId=$ceuntaC->countable_acount_payment;
                            }
                            if($cuentaId == null){
                                $cabeceraC->delete();
                                throw new Exception("Cuentas contables para Canje Retenciones sin asignar", 1);
                            }

                            $detalle2 = new AccountingEntryItems();
                            $detalle2->accounting_entrie_id = $cabeceraC->id;
                            $detalle2->account_movement_id = $cuentaId;
                            $detalle2->seat_line = $seat;
                            $detalle2->debe = $debeInterno;
                            $detalle2->haber = 0;
                            if($detalle2->save() == false){
                                $cabeceraC->delete();
                                break;
                                //abort(500,'No se pudo generar el asiento contable del documento');
                            }

                            $seat += 1;
                        }
                    }
                }else{

                    $detalle2 = new AccountingEntryItems();
                    $detalle2->accounting_entrie_id = $cabeceraC->id;
                    $detalle2->account_movement_id = ($ceuntaC && $ceuntaC->countable_acount)?$ceuntaC->countable_acount:$configuration->cta_charge;
                    $detalle2->seat_line = 2;
                    $detalle2->debe = ($requestP['overPayment']) ? $request->payment + $requestP['overPaymentValue'] : $request->payment;
                    $detalle2->haber = 0;
                    if($detalle2->save() == false){
                        $cabeceraC->delete();
                        return;
                        //abort(500,'No se pudo generar el asiento contable del documento');
                    }
                }

                if($requestP['overPayment'] && $requestP['overPaymentAdvance'] == false){

                    Log::info('Generando linea de overPayment');
                    $detalle = new AccountingEntryItems();
                    $ceuntaC = PaymentMethodType::find($request->payment_method_type_id);
                    $detalle->accounting_entrie_id = $cabeceraC->id;
                    $detalle->account_movement_id = $requestP['overPaymentAccount'];
                    $detalle->seat_line = 3;
                    $detalle->haber = $requestP['overPaymentValue'];
                    $detalle->debe = 0;
                    $detalle->save();

                    $cabeceraC->total_debe = $request->payment + $requestP['overPaymentValue'];
                    $cabeceraC->total_haber = $request->payment + $requestP['overPaymentValue'];
                    $cabeceraC->save();

                }

                if($requestP['overPayment'] && $requestP['overPaymentAdvance'] == true){

                    Log::info('Generando linea de overPayment');
                    $detalle = new AccountingEntryItems();
                    $ceuntaC = PaymentMethodType::find($request->payment_method_type_id);
                    $detalle->accounting_entrie_id = $cabeceraC->id;
                    $detalle->account_movement_id = $configuration->cta_client_advances;
                    $detalle->seat_line = 3;
                    $detalle->haber = $requestP['overPaymentValue'];
                    $detalle->debe = 0;
                    $detalle->save();

                    $cabeceraC->total_debe = $request->payment + $requestP['overPaymentValue'];
                    $cabeceraC->total_haber = $request->payment + $requestP['overPaymentValue'];
                    $cabeceraC->save();

                }

            }catch(Exception $ex){

                Log::error('Error al intentar generar el asiento contable');
                Log::error($ex->getMessage());
            }

        }else{
            Log::info('tipo de documento no genera asiento contable de momento');
        }

    }

    /* Crear los asientos contables del REVERSO */
    private function createAccountingEntryReverse($requestP, $request){

        $document = Document::find($requestP->document_id);
        $entry = (AccountingEntries::get())->last();

        if($document && ($document->document_type_id == '01' || $document->document_type_id == '03')){

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

                $comment = (($document->document_type_id == '03')?'Reverso cobro '.substr($document->series,0,1):'Reverso cobro Factura F'). $document->establishment->code . substr($document->series,1). str_pad($document->number,'9','0',STR_PAD_LEFT).' '. $document->customer->name ;

                $total_debe = 0;
                $total_haber = 0;

                $cabeceraC = new AccountingEntries();
                $cabeceraC->user_id = $document->user_id;
                $cabeceraC->seat = $seat;
                $cabeceraC->seat_general = $seat_general;
                $cabeceraC->seat_date = date('y-m-d');
                $cabeceraC->types_accounting_entrie_id = 1;
                $cabeceraC->comment = $comment;
                $cabeceraC->serie = null;
                $cabeceraC->number = $seat;
                $cabeceraC->total_debe = $request->payment * -1;
                $cabeceraC->total_haber = $request->payment  * -1;
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
                $detalle->haber = 0;
                $detalle->debe = $request->payment  * -1;

                if($detalle->save() == false){
                    $cabeceraC->delete();
                    return;
                    //abort(500,'No se pudo generar el asiento contable del documento');
                }

                if($request->payment_method_type_id == '99'){
                    $debe = ($requestP['overPayment'] && $requestP['overPaymentAdvance'] == false) ? $request->payment + $requestP['overPaymentValue'] : $request->payment;
                    $reference = $request->reference;
                    $retention = Retention::find($reference);
                    $detRet = $retention->optional;
                    Log::error($detRet);
                    if(is_array($detRet) == false ){
                        $detRet = json_decode($detRet);
                    }
                    $seat = 2;

                    foreach ($detRet as $ret) {

                        if($debe > 0){
                            $valor = (is_array($ret) == true)?floatval($ret['valorRetenido']):floatval($ret->valorRetenido);
                            $debeInterno = 0;
                            $cuentaId = null;
                            if($valor >=  $debe){
                                $debeInterno = $debe;
                                $debe = 0;
                            }
                            if($valor < $debe){
                                $debeInterno = $valor;
                                $debe -=  $valor;
                            }
                            if(is_array($ret) &&  $ret['codigo']== '2' || isset($ret->codigo) && $ret->codigo == '2'){
                                $cuentaId=$ceuntaC->countable_acount;
                            }
                            if(is_array($ret) &&  $ret['codigo']== '1' || isset($ret->codigo) && $ret->codigo == '1'){
                                $cuentaId=$ceuntaC->countable_acount_payment;
                            }
                            if($cuentaId == null){
                                $cabeceraC->delete();
                                throw new Exception("Cuentas contables para Canje Retenciones sin asignar", 1);
                            }

                            $detalle2 = new AccountingEntryItems();
                            $detalle2->accounting_entrie_id = $cabeceraC->id;
                            $detalle2->account_movement_id = $cuentaId;
                            $detalle2->seat_line = $seat;
                            $detalle2->haber = $debeInterno  * -1;
                            $detalle2->debe = 0;
                            if($detalle2->save() == false){
                                $cabeceraC->delete();
                                break;
                                //abort(500,'No se pudo generar el asiento contable del documento');
                            }

                            $seat += 1;
                        }
                    }
                }else{

                    $detalle2 = new AccountingEntryItems();
                    $detalle2->accounting_entrie_id = $cabeceraC->id;
                    $detalle2->account_movement_id = ($ceuntaC && $ceuntaC->countable_acount)?$ceuntaC->countable_acount:$configuration->cta_charge;
                    $detalle2->seat_line = 2;
                    $detalle2->haber = ($requestP['overPayment']) ? $request->payment + $requestP['overPaymentValue'] : $request->payment  * -1;
                    $detalle2->debe = 0;
                    if($detalle2->save() == false){
                        $cabeceraC->delete();
                        return;
                        //abort(500,'No se pudo generar el asiento contable del documento');
                    }
                }

                if($requestP['overPayment'] && $requestP['overPaymentAdvance'] == false){

                    Log::info('Generando linea de overPayment');
                    $detalle = new AccountingEntryItems();
                    $ceuntaC = PaymentMethodType::find($request->payment_method_type_id);
                    $detalle->accounting_entrie_id = $cabeceraC->id;
                    $detalle->account_movement_id = $requestP['overPaymentAccount'];
                    $detalle->seat_line = 3;
                    $detalle->debe = $requestP['overPaymentValue'];
                    $detalle->haber = 0;
                    $detalle->save();

                    $cabeceraC->total_debe = $request->payment + $requestP['overPaymentValue'];
                    $cabeceraC->total_haber = $request->payment + $requestP['overPaymentValue'];
                    $cabeceraC->save();

                }

                if($requestP['overPayment'] && $requestP['overPaymentAdvance'] == true){

                    Log::info('Generando linea de overPayment');
                    $detalle = new AccountingEntryItems();
                    $ceuntaC = PaymentMethodType::find($request->payment_method_type_id);
                    $detalle->accounting_entrie_id = $cabeceraC->id;
                    $detalle->account_movement_id = $configuration->cta_client_advances;
                    $detalle->seat_line = 3;
                    $detalle->debe = $requestP['overPaymentValue'];
                    $detalle->haber = 0;
                    $detalle->save();

                    $cabeceraC->total_debe = $request->payment + $requestP['overPaymentValue'];
                    $cabeceraC->total_haber = $request->payment + $requestP['overPaymentValue'];
                    $cabeceraC->save();

                }

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
            $valor = $retention->used;
            $montoUsado = $valor - $monto;
            $retention->total_used = $montoUsado;
            $retention->in_use = ($montoUsado > 0 )?true:false;
            $retention->save();

        }

        $advance = Advance::where('observation','like','%PAGO-'.$item->id)->delete();

        $sequential = $item->sequential;
        $multiPAy = $item->multipay;

        $item->delete();

        if($multiPAy == 'SI'){
            $item = DocumentPayment::where('sequential',$sequential);
            foreach ($item as $value) {
                $value->delete();
            }

            $asientos = AccountingEntries::where('document_id','like','%CF'.$id.';%')->get();
            foreach($asientos as $ass){
                $ass->delete();
            }

        }else{

            $asientos = AccountingEntries::where('document_id','CF'.$id)->get();
            foreach($asientos as $ass){
                $ass->delete();
            }

            $asientos2 = AccountingEntries::where('document_id','PC'.$id)->get();
            foreach($asientos2 as $ass){
                $ass->delete();
            }

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

    public function generateExpenses(Request $request){

        $id = $request->id;
        $valor = $request->overPaymentValue;
        $cuenta = $request->overPaymentAccount;

        $entry = AccountingEntries::where('document_id','CF'.$id)->first();
        if(isset($entry)){
            $entry->total_debe += $valor;
            $entry->total_haber += $valor;

            $entryItems = AccountingEntryItems::where('accounting_entrie_id',$entry->id)->get();
            foreach($entryItems as $item){
                if($item->debe > 0){
                    $item->debe += $valor;
                    $item->save();
                }
            }

            $detalle = new AccountingEntryItems();
            $detalle->accounting_entrie_id = $entryItems[0]->accounting_entrie_id;
            $detalle->account_movement_id = $cuenta;
            $detalle->seat_line = 3;
            $detalle->haber = $valor;
            $detalle->debe = 0;
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
    public function generateReverse(Request $request){

        Log::info('generateReverse');

        $id = $request->id;
        $motivo = $request->reference;

        $payment = DocumentPayment::find($id);
        $globalPayment = GlobalPayment::where('payment_id',$id)->where('payment_type','like','%DocumentPayment')->first();
        $sequential = DocumentPayment::latest('id')->first();

        if(isset($payment) && $payment->multipay == 'NO'){

            $newPayment = new DocumentPayment();
            $newPayment->document_id = $payment->document_id;
            $newPayment->date_of_payment = date('Y-m-d');
            $newPayment->payment_method_type_id = $payment->payment_method_type_id;
            $newPayment->has_card = $payment->has_card;
            $newPayment->card_brand_id = $payment->card_brand_id;
            $newPayment->reference = $motivo;
            $newPayment->change = $payment->change;
            $newPayment->payment = $payment->payment * -1;
            $newPayment->payment_received = $payment->payment_received;
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

            $this->createAccountingEntryReverse($newPayment,$newPayment);

            return [
                'success'=>true,
                'message' => 'Reverso generado de forma exitosa!'
            ];

        }elseif(isset($payment) && $payment->multipay == 'SI'){

            $multiPays = DocumentPayment::where('sequential',$payment->sequential)->get();
            $paymentsIds = '';
            foreach ($multiPays as $value) {
                $paymentM = DocumentPayment::find($value->id);
                $globalPayment = GlobalPayment::where('payment_id',$id)->where('payment_type','like','%DocumentPayment')->first();
                $sequential = DocumentPayment::latest('id')->first();

                $newPayment = new DocumentPayment();
                $newPayment->document_id = $paymentM->document_id;
                $newPayment->date_of_payment = date('Y-m-d');
                $newPayment->payment_method_type_id = $paymentM->payment_method_type_id;
                $newPayment->has_card = $paymentM->has_card;
                $newPayment->card_brand_id = $paymentM->card_brand_id;
                $newPayment->reference = $motivo;
                $newPayment->change = $paymentM->change;
                $newPayment->payment = $paymentM->payment * -1;
                $newPayment->payment_received = $paymentM->payment_received;
                $newPayment->fee_id = $paymentM->fee_id;
                $newPayment->postdated = $paymentM->postdated;
                $newPayment->sequential = $sequential->sequential + 1;
                $newPayment->multipay = 'SI';
                $newPayment->save();

                $paymentsIds .= 'CF'.$newPayment->id.';';

                $newGlobalPayment = new GlobalPayment();
                $newGlobalPayment->soap_type_id = $globalPayment->soap_type_id;
                $newGlobalPayment->destination_id = $globalPayment->destination_id;
                $newGlobalPayment->destination_type = $globalPayment->destination_type;
                $newGlobalPayment->payment_id = $newPayment->id;
                $newGlobalPayment->payment_type = $globalPayment->payment_type;
                $newGlobalPayment->user_id = $globalPayment->user_id;
                $newGlobalPayment->save();
            }

            $unp = new UnpaidController();
            $unp->generateMultiPayReverse('CF'.$payment->id,$paymentsIds);

            return [
                'success'=>true,
                'message' => 'Reverso generado de forma exitosa!'
            ];

        }else{
            Log::error('No s eencontro un pago con el ID: '.$id);
        }


    }

}

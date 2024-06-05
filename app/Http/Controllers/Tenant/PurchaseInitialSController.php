<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentFee;
use App\Models\Tenant\DocumentItem;

use App\Models\Tenant\Establishment;

use App\Models\Tenant\Item;

use App\Models\Tenant\ItemWarehouse;

use App\Models\Tenant\Person;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchaseItem;
use App\Traits\OfflineTrait;

use Exception;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Modules\Finance\Traits\FinanceTrait;

use Modules\Item\Models\ItemLotsGroup;

use App\Models\Tenant\Inventory;
use App\Models\Tenant\InventoryKardex;

use App\Models\Tenant\PurchaseFee;

use Illuminate\Support\Facades\Log;
use Modules\Item\Models\ItemLot;


set_time_limit(0);

class PurchaseInitialSController extends Controller
{

    use FinanceTrait;
    use StorageDocument;
    use OfflineTrait;

    private $id;
    private $purchase;


    public function create()
    {
        $configuration = Configuration::first();
        $compani = Company::first();
        $data = DB::connection('tenant')->select('SELECT * FROM cuentasporpagarlocales;');
        $itemP = Item::find(1);
        foreach ($data as $item) {

            try {

                $CI = trim($item->CI);
                $numDoc = $item->documento;
                $fechaDoc = date($item->fecha);
                $fecha = date_create_from_format("d/m/Y", $item->fecha)->format("Y-m-d");
                $time = date_create_from_format("d/m/Y", $item->fecha)->format("h:i:s");
                $fechaVenci = date_create_from_format("d/m/Y", $item->vencimiento)->format("Y-m-d");
                $importe =  floatval(str_replace(',', '.', $item->importe));
                $numero = Purchase::where('establishment_id', 1)->where('series', 'CC')->get()->max('number');
                $supplier = Person::where('number', $CI)->where('type','suppliers')->first();

                Log::error('Person ID  '.$CI.' ID INTERNO: '.$supplier->id);
                Log::error('ITEM ID '.$itemP->id);
                $purchaseId = null;
                $purchaseCreated = Purchase::where('date_of_issue',$fecha)->where('document_type_id','376')->where('supplier_id',$supplier->id)->where('user_id',28)->where('sequential_number',$numDoc)->where('total',$importe)->fisrt();
                if($purchaseCreated && $purchaseCreated->count() > 0){
                    $purchaseId = $purchaseCreated->id;
                }else{
                    $purchase = new Purchase();
                    $purchase->user_id = 28;
                    $purchase->external_id = Str::uuid()->toString();
                    $purchase->establishment_id = 1;
                    $purchase->soap_type_id = $compani->soap_type_id;
                    $purchase->state_type_id = '01';
                    $purchase->group_id = '01';
                    $purchase->document_type_id = '376';
                    $purchase->series = 'CC';
                    $purchase->number = $numero + 1;
                    $purchase->date_of_issue = $fecha;
                    $purchase->date_of_due = $fechaVenci;
                    $purchase->time_of_issue = $time;
                    $purchase->supplier_id = $supplier->id;
                    $purchase->supplier = $supplier;
                    $purchase->currency_type_id = $configuration->currency_type_id;
                    $purchase->payment_condition_id = '02';
                    $purchase->exchange_rate_sale = 1;
                    $purchase->total_unaffected = $importe;
                    $purchase->total_taxes = $importe;
                    $purchase->total_value = $importe;
                    $purchase->total = $importe;
                    $purchase->sequential_number = $numDoc;
                    $purchase->document_type_intern = 'SIC'; //ID documento INTERNO

                    $purchase->save();
                    $purchaseId = $purchase->id;
                }

                sleep(10);
                Log::error('ID purchase: '.$purchaseId);
                Log::error('ITEM ID '.$itemP->id);

                $purchaseItem = new PurchaseItem();
                $purchaseItem->item_id = $itemP->id;
                $purchaseItem->item = $itemP;
                $purchaseItem->quantity = 1;
                $purchaseItem->unit_value = $importe;
                $purchaseItem->affectation_igv_type_id = $itemP->purchase_affectation_igv_type_id;
                $purchaseItem->total_base_igv = $importe;
                $purchaseItem->percentage_igv = 0;
                $purchaseItem->total_igv = $importe;
                $purchaseItem->total_taxes = 0;
                $purchaseItem->price_type_id = '01';
                $purchaseItem->unit_price = $importe;
                $purchaseItem->total_value = $importe;
                $purchaseItem->total = $importe;
                $purchaseItem->purchase_id = $purchaseId;
                $purchaseItem->save();

                $purchaseFee = new PurchaseFee();
                $purchaseFee->date = $fechaVenci;
                $purchaseFee->currency_type_id = $configuration->currency_type_id;
                $purchaseFee->amount = $importe;
                $purchaseFee->number = 1; //Monto de la
                $purchaseFee->purchase_id = $purchaseId;
                $purchaseFee->save();

                //echo "Saldo INICIAL creado Para " . $CI . " con fecha: " . $fecha . " valor de: " . $importe . "</br>";
            } catch (Exception $ex) {

                echo "No Se pudo generar el saldo INICIAL para " . $CI . " con fecha : " . $fecha . " valor de " . $importe . "</br>";
                echo $ex->getMessage() ."</br>";

                Log::error("No Se pudo generar el saldo INICIAL PURCHASES para " . $CI . " con fecha : " . $fecha . " valor de " . $importe);
                Log::error($ex->getMessage());
            }
        }
    }

    public function createDocumnets()
    {
        $configuration = Configuration::first();
        //$configuration = $configuration->getCollectionData();

        $compani = Company::first();
        $data = DB::connection('tenant')->select('SELECT * FROM cuentasporcobrarlocales;');
        $establishment = Establishment::find(1);
        $itemP = Item::find(1); //cambiar al ID del ITEM basico
        foreach ($data as $item) {

            try {

                $CI = $item->CI;
                $numDoc = $item->documento;
                $fechaDoc = date($item->fecha);
                $fecha = date_create_from_format("d/m/Y", $item->fecha)->format("Y-m-d");
                $time = date_create_from_format("d/m/Y", $item->fecha)->format("h:i:s");
                $fechaVenci = date_create_from_format("d/m/Y", $item->vencimiento)->format("Y-m-d");
                $importe =  floatval(str_replace(',', '.', $item->importe));
                $numero = Document::where('establishment_id', 1)->where('series', 'B001')->get()->max('number');
                $customer = Person::where('number', $CI)->where('type','customers')->first();

                $document = new Document();
                $document->user_id = 28; //28
                $document->external_id = Str::uuid()->toString();
                $document->clave_SRI = $numDoc;
                $document->establishment_id = 1;
                $document->establishment = $establishment;
                $document->soap_type_id = '01';
                $document->state_type_id = '05';
                $document->ubl_version = '2.1';
                $document->ticket_single_shipment = 0;
                $document->force_send_by_summary = 0;
                $document->group_id = '02';
                $document->document_type_id = '03';
                $document->series = 'B001';
                $document->number = $numero + 1;
                $document->date_of_issue = $fecha;
                $document->time_of_issue = '00:00:00';
                $document->customer_id = $customer->id;
                $document->customer = $customer;
                $document->currency_type_id = 'USD';
                $document->payment_condition_id = '02';
                $document->exchange_rate_sale = 1;
                $document->point_system = 0;
                $document->total_unaffected = $importe;
                $document->total_value = $importe;
                $document->subtotal = $importe;
                $document->total = $importe;
                $document->has_xml = 0;
                $document->has_pdf = 0;
                $document->is_editable = 0;
                $document->reference_data = $numDoc;
                $document->save();

                sleep(5);
                Log::error('ID document: '.$document->id);
                Log::error('ITEM ID '.$itemP->id);


                $documentItem = new DocumentItem();
                $documentItem->document_id = $document->id;
                $documentItem->item_id = $itemP->id;
                $documentItem->item = $itemP;
                $documentItem->quantity = 1;
                $documentItem->unit_value = $importe;
                $documentItem->unit_price = $importe;
                $documentItem->affectation_igv_type_id = '30';
                $documentItem->total_base_igv = $importe;
                $documentItem->percentage_igv = 0;
                $documentItem->total_igv = 0;
                $documentItem->total_value = $importe;
                $documentItem->total_taxes = 0;
                $documentItem->price_type_id = '01';
                $documentItem->total = $importe;
                $documentItem->warehouse_id = 1;
                $documentItem->name_product_pdf = 'SI CUENTAS POR COBRAR/SI CUENTAS POR COBRAR';
                $documentItem->save();

                $documentFee = new DocumentFee();
                $documentFee->document_id = $document->id;
                $documentFee->date = $fechaVenci;
                $documentFee->currency_type_id = 'USD';
                $documentFee->amount = $importe;
                $documentFee->number = intval($item->number);
                $documentFee->save();

                //echo "Saldo INICIAL creado Para " . $CI . " con fecha: " . $fecha . " valor de: " . $importe . "</br>";

            } catch (Exception $ex) {

                echo "No Se pudo generar el saldo INICIAL para " . $CI . " con fecha : " . $fecha . " valor de " . $importe . "</br>";
                Log::error("No Se pudo generar el saldo INICIAL DOCUMENTS para " . $CI . " con fecha : " . $fecha . " valor de " . $importe);
                Log::error($ex->getMessage());
            }
        }
    }

    public function createInventory(){
        $data = DB::connection('tenant')->select('SELECT * FROM inventarioinicial');
        foreach ($data as $value) {
            try{
                $item = Item::where('internal_id',$value->internal_id)->first();

                $iw = ItemWarehouse::firstOrNew(['item_id' => $item->id,
                    'warehouse_id'=> $value->warehouse]);


                $inventory = new Inventory();
                $inventory->type = 1;
                $inventory->description = 'Stock inicial';
                $inventory->item_id = $item->id;
                $inventory->warehouse_id = $value->warehouse;
                $inventory->quantity = floatVal(str_replace(',','.',$value->quantity));
                $inventory->inventory_transaction_id = null;
                $inventory->lot_code = $value->code;
                $inventory->comments = 'Creacion de stock por plantilla inicial';
                $inventory->precio_perso = $value->price;
                $inventory->production_id= null;
                $inventory->save();

                $inventoryKardex = new InventoryKardex();
                $inventoryKardex->date_of_issue = date('Y-m-d');
                $inventoryKardex->item_id = $item->id;
                $inventoryKardex->inventory_kardexable_id = $inventory->id;
                $inventoryKardex->inventory_kardexable_type = 'Modules\Inventory\Models\Inventory';
                $inventoryKardex->warehouse_id = $value->warehouse;
                $inventoryKardex->quantity = floatVal(str_replace(',','.',$value->quantity));
                $inventoryKardex->save();

                if($item->lots_enabled > 0){

                    ItemLotsGroup::create([
                        'code' => $value->code,
                        'quantity' => floatVal(str_replace(',','.',$value->quantity)),
                        'date_of_due' =>$value->date_of_due,
                        'warehouse_id' => $value->warehouse,
                        'item_id' => $item->id
                    ]);
                }
                if($item->series_enabled > 0){
                    ItemLot::create([
                        'date'         => $value->date_of_due,
                        'series'       => $value->code,
                        'item_id'      => $item->id,
                        'warehouse_id' => $value->warehouse,
                        'has_sale'     => false,
                        'state'        => 'Activo',
                        'item_loteable_type' => 'App\Models\Tenant\Item',
                        'item_loteable_id' => $item->id,
                    ]);
                }

                $iw->stock += floatVal(str_replace(',','.',$value->quantity));
                $iw->save();

            }catch(Exception $ex){
                echo "No Se pudo generar el stock INICIAL para " . $value->internal_id . " con code : " . $value->code . " en la bodega " . $value->warehouse . "</br>";
                Log::error("No Se pudo generar el stock INICIAL para " . $value->internal_id . "con code:  " . $value->code . " en la bodega " . $value->warehouse);
                Log::error($ex->getMessage());
            }
        }
    }
}

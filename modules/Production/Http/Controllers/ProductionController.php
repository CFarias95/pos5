<?php

namespace Modules\Production\Http\Controllers;

use App\Imports\ProductionImport;
use App\Models\Tenant\AccountingEntries;
use App\Models\Tenant\AccountingEntryItems;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemSupply;
use App\Models\Tenant\ItemSupplyLot;
use App\Models\Tenant\Person;
use App\Models\Tenant\ProductionSupply;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Inventory\Models\Inventory;
use Modules\Inventory\Models\InventoryTransaction;
use Modules\Inventory\Traits\InventoryTrait;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Production\Exports\BuildProductsExport;
use Modules\Production\Http\Requests\ProductionRequest;
use Modules\Production\Http\Resources\ProductionCollection;
use Modules\Production\Models\Machine;
use Modules\Production\Models\Production;
use Modules\Production\Models\StateTypeProduction;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel;
use App\Models\Tenant\ItemWarehouse;
use Modules\Inventory\Http\Controllers\TransferController;
use Modules\Inventory\Http\Requests\TransferRequest;

class ProductionController extends Controller
{
    use InventoryTrait;
    use FinanceTrait;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('production::production.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create($id = null)
    {
        return view('production::production.form', compact('id'));
    }

    public function storeOld(ProductionRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {

            // validar estado_type_id == 01
            if ($request->records_id !== '01') {
                return [
                    'success' => false,
                    'message' => 'Solo se permite ingresar productos con estado registrado'
                ];
            }

            $production = Production::firstOrNew(['id' => null]);
            $production->fill($request->all());
            $production->state_type_id = $request->records_id;
            $production->user_id = auth()->user()->id;
            $production->soap_type_id = $this->getCompanySoapTypeId();
            $production->save();


            return [
                'success' => true,
                'message' => 'Producto registrado correctamente, listo para elaboración'
            ];
        });

        return $result;
    }

    public function storeOriginal(ProductionRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {

            $item_id = $request->input('item_id');
            $warehouse_id = $request->input('warehouse_id');
            $quantity = $request->input('quantity');
            $informative = ($request->informative) ?: false;


            $inventory_transaction = InventoryTransaction::findOrFail(19); //debe ser Ingreso de producción
            $inventory = new Inventory();

            if ($informative !== true) {
                $inventory->type = null;
                $inventory->description = $inventory_transaction->name;
                $inventory->item_id = $item_id;
                $inventory->warehouse_id = $warehouse_id;
                $inventory->quantity = $quantity;
                $inventory->inventory_transaction_id = $inventory_transaction->id;
                $inventory->save();
            }

            $production = Production::firstOrNew(['id' => null]);
            $production->fill($request->all());
            $production->inventory_id_reference = $inventory->id;
            $production->state_type_id = $request->records_id;
            $production->user_id = auth()->user()->id;
            $production->soap_type_id = $this->getCompanySoapTypeId();
            $production->save();


            if ($informative !== true) {
                $items_supplies = $request->supplies;
                foreach ($items_supplies as $item) {
                    $supplyWarehouseId = (int) ($item['warehouse_id'] ?? $warehouse_id);
                    $supplyWarehouseId = $supplyWarehouseId !== 0 ? $supplyWarehouseId : $warehouse_id;
                    $qty = $item['quantity'] ?? 0;
                    $inventory_transaction_item = InventoryTransaction::findOrFail('101'); //Salida insumos por molino
                    $inventory_it = new Inventory();
                    $inventory_it->type = null;
                    $inventory_it->description = $inventory_transaction_item->name;
                    $inventory_it->item_id = $item['individual_item_id'];
                    $inventory_it->warehouse_id = $supplyWarehouseId;
                    $inventory_it->quantity = (float) ($qty * $quantity);
                    $inventory_it->inventory_transaction_id = $inventory_transaction_item->id;
                    $inventory_it->save();
                }
            }

            return [
                'success' => true,
                'message' => 'Ingreso registrado correctamente'
            ];
        });

        return $result;
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param ProductionRequest $request
     *
     * @return Response
     */

    public function store(ProductionRequest $request)
    {
        try {
            $item_id = $request->input('item_id');
            $warehouse_id = $request->input('warehouse_id');
            $quantity = $request->input('quantity');
            $state_type_id = $request->records_id;
            $informative = $request->input('informative', false);

            $production = new Production();
            $production->fill($request->all());
            $production->inventory_id_reference = $request->input('inventory_id_reference');
            $production->warehouse_id = $warehouse_id;
            $production->state_type_id = $state_type_id;
            $production->user_id = auth()->user()->id;
            $production->soap_type_id = $this->getCompanySoapTypeId();
            $production->save();

            $items_supplies = $request->supplies;
            //Log::info("SUPLIES: " . json_encode($items_supplies));

            try {
                foreach ($items_supplies as $item) {

                    $sitienelote = false;
                    $production_supply = new ProductionSupply();
                    $production_id = $production->id;
                    $qty = $item['quantityD'] ?? 0;
                    $production_supply->production_name = $production->name;
                    $production_supply->production_id = $production_id;
                    $production_supply->item_supply_name = isset($item['individual_item']) ? $item['individual_item']['name'] . ' / ' . $item['individual_item']['description']  : $item['description'];
                    $production_supply->item_supply_id = $item['id'];
                    $production_supply->warehouse_name = $item['warehouse_name'] ?? null;
                    $production_supply->warehouse_id = $item['warehouse_id'] ?? null;
                    $production_supply->quantity = (float) $qty;
                    $production_supply->cost_per_unit = (isset($item['cost_per_unit'])) ? $item['cost_per_unit'] : null;
                    $production_supply->save();

                    $lots_group = isset($item["lots_group"]) ? $item["lots_group"] : [];


                    foreach ($lots_group as $lots) {

                        //if (isset($lots["compromise_quantity"])){

                        $item_lots_groups = new ItemSupplyLot();
                        $item_lots_groups->item_supply_id = $item['id'];
                        $item_lots_groups->item_supply_name = $item['description'];
                        $item_lots_groups->lot_code = $lots["code"];
                        $item_lots_groups->lot_id = $lots["id"];
                        $item_lots_groups->production_name = $production->name;
                        $item_lots_groups->production_id = $production_id;
                        $item_lots_groups->quantity = 0;
                        $item_lots_groups->expiration_date = $lots["date_of_due"];
                        $item_lots_groups->save();
                        //}
                    }
                }
            } catch (Exception $ex2) {
                $production->delete();
                return [
                    'success' => false,
                    'message' => 'Error al registrar el ingreso: ' . $ex2->getMessage()
                ];
            }
            $this->createAccountingEntry($production->id);
            return [
                'success' => true,
                'message' => 'Ingreso registrado correctamente'
            ];
        } catch (\Exception $e) {

            return [
                'success' => false,
                'message' => 'Error al registrar el ingreso: ' . $e->getMessage()
            ];
        }
    }

    /* CREARE ACCOUNTING ENTRIES PRODUCCTION*/
    public function createAccountingEntry($document_id)
    {
        $document = Production::find($document_id);
        //Log::info('documento created: ' . json_encode($document));
        $entry = (AccountingEntries::get())->last();
        //ASIENTO CONTABLE DE ORDENES DE PRODCUCION
        if ($document && $document->state_type_id == '02') {

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

                $comment = 'Orden de Producción Iniciada ' . $document->name;

                $total_debe = 0;
                $total_haber = 0;

                $cabeceraC = new AccountingEntries();
                $cabeceraC->user_id = $document->user_id;
                $cabeceraC->seat = $seat;
                $cabeceraC->seat_general = $seat_general;
                $cabeceraC->seat_date = $document->date_start;
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
                $cabeceraC->currency_type_id = $configuration->currency_type_id;
                $cabeceraC->doctype = 10;
                $cabeceraC->is_client = ($document->customer) ? true : false;
                $cabeceraC->establishment_id = null;
                $cabeceraC->establishment = '';
                $cabeceraC->prefix = 'ASC';
                $cabeceraC->person_id = null;
                $cabeceraC->external_id = Str::uuid()->toString();
                $cabeceraC->document_id = 'OPS' . $document_id;

                $cabeceraC->save();
                $cabeceraC->filename = 'ASC-' . $cabeceraC->id . '-' . date('Ymd');
                $cabeceraC->save();

                //Log::info("Producto a FABRICAR: ".$document->item_id);

                $itemP = Item::find($document->item_id);
                $itemSuppliers = ProductionSupply::where('production_id', $document_id)->get();

                //Log::info("Supplies del producto: ".$itemSuppliers->count());

                $arrayEntrys = [];
                $n = 1;

                $debeGlobal = 0;

                foreach ($itemSuppliers as $key => $value) {

                    //Log::info("Item Supplie".json_encode($value));

                    $itemSu = ItemSupply::find($value['item_supply_id']);
                    $item = Item::find($itemSu->individual_item_id);
                    $debeGlobal += ($value->cost_per_unit * $value->quantity);
                    if ($item->purchase_cta) {

                        if (array_key_exists($item->purchase_cta, $arrayEntrys)) {

                            $arrayEntrys[$item->purchase_cta]['haber'] += ($value->cost_per_unit * $value->quantity);
                        }
                        if (array_key_exists($item->purchase_cta, $arrayEntrys) == false) {

                            $n += 1;
                            $arrayEntrys[$item->purchase_cta] = [
                                'seat_line' => $n,
                                'haber' => ($value->cost_per_unit * $value->quantity),
                                'debe' => 0,
                            ];
                        }
                    }
                    if (!($item->purchase_cta) && $configuration->cta_incomes) {

                        if (array_key_exists($configuration->cta_purchases, $arrayEntrys)) {

                            $arrayEntrys[$configuration->cta_purchases]['haber'] += ($value->cost_per_unit * $value->quantity);
                        }
                        if (array_key_exists($configuration->cta_purchases, $arrayEntrys) == false) {

                            $n += 1;

                            $arrayEntrys[$configuration->cta_purchases] = [
                                'seat_line' => $n,
                                'haber' => ($value->cost_per_unit * $value->quantity),
                                'debe' => 0,
                            ];
                        }
                    }
                }

                //Log::info('arreglo de items cuentas',$arrayEntrys);

                $detalle = new AccountingEntryItems();
                $detalle->accounting_entrie_id = $cabeceraC->id;
                $detalle->account_movement_id = ($itemP->item_process_cta) ? $itemP->item_process_cta : $configuration->cta_item_process;
                $detalle->seat_line = 1;
                $detalle->debe = $debeGlobal;
                $detalle->haber = 0;
                $detalle->save();

                foreach ($arrayEntrys as $key => $value) {
                    if ($value['debe'] > 0 || $value['haber'] > 0) {
                        $detalle = new AccountingEntryItems();
                        $detalle->accounting_entrie_id = $cabeceraC->id;
                        $detalle->account_movement_id = $key;
                        $detalle->seat_line = $value['seat_line'];
                        $detalle->debe = $value['debe'];
                        $detalle->haber = $value['haber'];
                        $detalle->save();
                    }
                }
            } catch (Exception $ex) {

                Log::error('Error al intentar generar el asiento contable');
                Log::error($ex->getMessage());
            }
        } elseif ($document && $document->state_type_id == '03') {

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

                $comment = 'Orden de Producción Finalizada ' . $document->name;

                $total_debe = 0;
                $total_haber = 0;

                $cabeceraC = new AccountingEntries();
                $cabeceraC->user_id = $document->user_id;
                $cabeceraC->seat = $seat;
                $cabeceraC->seat_general = $seat_general;
                $cabeceraC->seat_date = $document->date_end;
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
                $cabeceraC->currency_type_id = $configuration->currency_type_id;
                $cabeceraC->doctype = 10;
                $cabeceraC->is_client = ($document->customer) ? true : false;
                $cabeceraC->establishment_id = null;
                $cabeceraC->establishment = '';
                $cabeceraC->prefix = 'ASC';
                $cabeceraC->person_id = null;
                $cabeceraC->external_id = Str::uuid()->toString();
                $cabeceraC->document_id = 'OPF' . $document_id;

                $cabeceraC->save();
                $cabeceraC->filename = 'ASC-' . $cabeceraC->id . '-' . date('Ymd');
                $cabeceraC->save();

                $itemP = Item::find($document->item_id);
                $itemSuppliers = ProductionSupply::where('production_id', $document_id)->get();

                $arrayEntrys = [];
                $n = 1;

                $debeGlobal = $document->cost_supplies;

                $detalle1 = new AccountingEntryItems();
                $detalle1->accounting_entrie_id = $cabeceraC->id;
                $detalle1->account_movement_id = ($itemP->purchase_cta) ? $itemP->purchase_cta : $configuration->cta_purchases;
                $detalle1->seat_line = 1;
                $detalle1->debe = $debeGlobal;
                $detalle1->haber = 0;
                $detalle1->save();

                $detalle = new AccountingEntryItems();
                $detalle->accounting_entrie_id = $cabeceraC->id;
                $detalle->account_movement_id = ($itemP->item_process_cta) ? $itemP->item_process_cta : $configuration->cta_item_process;
                $detalle->seat_line = 2;
                $detalle->debe = 0;
                $detalle->haber = $debeGlobal;
                $detalle->save();


                if (isset($document->imperfect) && $document->imperfect > 0) {

                    $contoUnitarioProd = ($debeGlobal / $document->quantity) * $document->imperfect;

                    $comment = 'Salida defectuosos Orden de Producción ' . $document->name;

                    $total_debe = 0;
                    $total_haber = 0;

                    $cabeceraC = new AccountingEntries();
                    $cabeceraC->user_id = $document->user_id;
                    $cabeceraC->seat = ($seat + 1);
                    $cabeceraC->seat_general = ($seat_general + 1);
                    $cabeceraC->seat_date = $document->date_end;
                    $cabeceraC->types_accounting_entrie_id = 1;
                    $cabeceraC->comment = $comment;
                    $cabeceraC->serie = null;
                    $cabeceraC->number = ($seat + 1);
                    $cabeceraC->total_debe = $contoUnitarioProd;
                    $cabeceraC->total_haber = $contoUnitarioProd;
                    $cabeceraC->revised1 = 0;
                    $cabeceraC->user_revised1 = 0;
                    $cabeceraC->revised2 = 0;
                    $cabeceraC->user_revised2 = 0;
                    $cabeceraC->currency_type_id = $configuration->currency_type_id;
                    $cabeceraC->doctype = 10;
                    $cabeceraC->is_client = ($document->customer) ? true : false;
                    $cabeceraC->establishment_id = null;
                    $cabeceraC->establishment = '';
                    $cabeceraC->prefix = 'ASC';
                    $cabeceraC->person_id = null;
                    $cabeceraC->external_id = Str::uuid()->toString();
                    $cabeceraC->document_id = 'OPS' . $document_id;

                    $cabeceraC->save();
                    $cabeceraC->filename = 'ASC-' . $cabeceraC->id . '-' . date('Ymd');
                    $cabeceraC->save();

                    $motivoSalida = InventoryTransaction::findOrFail('105');

                    $detalle1 = new AccountingEntryItems();
                    $detalle1->accounting_entrie_id = $cabeceraC->id;
                    $detalle1->account_movement_id = ($itemP->purchase_cta) ? $itemP->purchase_cta : $configuration->cta_purchases;
                    $detalle1->seat_line = 1;
                    $detalle1->haber = $contoUnitarioProd;
                    $detalle1->debe = 0;
                    $detalle1->save();

                    $detalle = new AccountingEntryItems();
                    $detalle->accounting_entrie_id = $cabeceraC->id;
                    $detalle->account_movement_id = ($motivoSalida) ? $motivoSalida->cta_account : null;
                    $detalle->seat_line = 2;
                    $detalle->haber = 0;
                    $detalle->debe = $contoUnitarioProd;
                    $detalle->save();
                }
            } catch (Exception $ex) {

                Log::error('Error al intentar generar el asiento contable');
                Log::error($ex->getMessage());
            }
        } else {
            Log::info('tipo de documento no genera asiento contable de momento');
        }
    }

    /**
     * Show the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        return view('production::show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        return view('production::edit');
    }

    public function update(ProductionRequest $request, $id)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {
            //llama a la producción que fue creada
            $production = Production::findOrFail($id);
            //si la producción que esta en el estado 01 (registrado) y se quiere cambiar a 02 (en elaboración)
            if (!$production) {
                return [
                    'success' => false,
                    'message' => 'No se encontró el registro'
                ];
            }
            $new_state_type_id = $request->records_id;
            $old_state_type_id = $production->state_type_id;
            $quantity = $request->input('quantity');
            $warehouse_id = $request->input('warehouse_id');
            //Log::info('$request->supplies - '.json_encode($request->supplies));
            $production->fill($request->all());
            $production->warehouse_id = $warehouse_id;
            $production->quantity = $quantity;
            $production->state_type_id = $new_state_type_id;
            $informative = ($request->informative) ?: false;
            $production->user_id = auth()->user()->id;
            $production->soap_type_id = $this->getCompanySoapTypeId();
            $items_supplies = $request->supplies;
            //Log::info('tiem_supplies - '.json_encode($items_supplies));
            $costoT = 0;
            //Log::info("SUPLIES: " . json_encode($items_supplies));
            //Log::info('item - ' . json_encode($items_supplies[0]['checked']));
            //Log::info('item - ' . getType($items_supplies[0]['checked']));
            // Error al registrar el ingreso: Undefined variable: item_supplies
            //Log::info('production - '.$production->id);

            if ($old_state_type_id == '01' && $new_state_type_id == '02' && !$informative) {
                //Log::info("Actualiza a elaboracion");
                try {
                    foreach ($items_supplies as $item) {
                        //Log::info('item_supplies - ' . json_encode($item));
                        $sitienelote = false;
                        $production_supply = ProductionSupply::where('production_id', $production->id)->where("item_supply_id", $item['id'])->first();
                        $production_id = $production->id;
                        $qty = $item['quantityD'] ?? 0;
                        $production_supply->production_name = $production->name;
                        $production_supply->production_id = $production_id;
                        $production_supply->item_supply_name = $item['description'];
                        $production_supply->item_supply_id = $item['id'];
                        $production_supply->warehouse_name = $item['warehouse_name'] ?? null;
                        $production_supply->warehouse_id = $item['warehouse_id'] ?? null;
                        $production_supply->quantity = (float) $qty;
                        $production_supply->cost_per_unit = (isset($item['cost_per_unit'])) ? $item['cost_per_unit'] : null;
                        $production_supply->checked = isset($item['checked']) ? $item['checked'] : 0;
                        $production_supply->item_supply_original_id = $item['individual_item_id'];

                        $production_supply->save();
                        $costoT += ($qty * $production_supply->cost_per_unit);
                        $lots_group = $item["lots_group"];
                        foreach ($lots_group as $lots) {
                            if (isset($lots["compromise_quantity"])) {
                                //Log::info('$lots["compromise_quantity"] - ' . $lots["compromise_quantity"]);
                                //Log::info("Se tiene cantidad en un lote selecionado");
                                $sitienelote = true;
                                $item_lots_groups = new ItemSupplyLot();
                                $item_lots_groups->item_supply_id = $item['id'];
                                $item_lots_groups->item_supply_name = $item['description'];
                                $item_lots_groups->lot_code = $lots["code"];
                                $item_lots_groups->lot_id = $lots["id"];
                                $item_lots_groups->production_name = $production->name;
                                $item_lots_groups->production_id = $production_id;
                                $item_lots_groups->quantity = $lots["compromise_quantity"];
                                $item_lots_groups->expiration_date = $lots["date_of_due"];
                                $item_lots_groups->save();
                            }
                        }

                        if (count($lots_group) > 0) {

                            if ($sitienelote == false) {
                                //$production->delete();
                                return [
                                    'success' => false,
                                    'message' => 'Debe seleccionar lote/serie y cantidad de ' . $item['description']
                                ];
                            }
                        }
                    }
                    $production->cost_supplies = $costoT;
                    $production->save();
                } catch (Exception $ex2) {
                    $production->delete();
                    return [
                        'success' => false,
                        'message' => 'Error al registrar el ingreso: ' . $ex2->getMessage()
                    ];
                }
            } elseif ($old_state_type_id == '02' && $new_state_type_id == '03' && !$informative) {

                try {

                    $totalSupply = ProductionSupply::where('production_id', $production->id)->get();
                    foreach ($totalSupply as $supp) {
                        $costoT += ($supp->quantity * $supp->cost_per_unit);
                    }
                    //Log::info('Pasa el foreach de costoT');
                    $production->cost_supplies = $costoT;
                    $production->save();

                    $item = Item::where('id', $production->item_id)->first();
                    $costoA = $item->purchase_mean_cost;
                    $stockA = $item->stock;
                    $totalA = $production->cost_supplies;

                    $stockN = $production->quantity;
                    $totalN = $production->cost_supplies;

                    $stockT = $stockN + $stockA;
                    $costoT = $totalA + $totalN;
                    $costoT = round($costoT / $stockT, 4);

                    //Log::info("ACTUAL " . $costoA . '-' . $stockA . ' NUEVO: ' . $costoT . "-" . $stockT);
                    $item->purchase_mean_cost = $costoT;
                    $item->save();
                } catch (Exception $ex2) {
                    //$production->delete();
                    return [
                        'success' => false,
                        'message' => 'Error al actualizar el ingreso: ' . $ex2->getMessage()
                    ];
                }
            }

            $production->save();

            try {
                if ($old_state_type_id == '01' && $new_state_type_id == '02' && !$informative) {
                    //cuando pasa a elaboración se decuenta el inventario la lista de materiales que se está utilizando en la fabricación del producto.
                    $inventory_transaction_item = InventoryTransaction::findOrFail(101);
                    $this->inventorySupplies($production, $items_supplies, $inventory_transaction_item);
                    $this->createAccountingEntry($production->id);
                }
                if ($old_state_type_id == '02' && $new_state_type_id == '03' && !$informative) {
                    //cuando pasa a terminado se aumenta el inventario del producto terminado
                    $inventory_transaction_item = InventoryTransaction::findOrFail(19);
                    $inventory_transaction_item_imperfect = InventoryTransaction::findOrFail('105');

                    $this->inventoryFinishedProduct($production, $inventory_transaction_item);
                    $this->inventoryImperfectProduct($production, $inventory_transaction_item_imperfect);
                    $this->tranferSamples($request->samples, $request->destination_warehouse_id, $warehouse_id, $production);
                    $this->createAccountingEntry($production->id);
                }
                if ($old_state_type_id == '03' && $new_state_type_id == '04' && !$informative) {
                    //cuando pasa a anulado se aumenta el inventario de los materiales que se utilizó en la fabricación del producto terminado
                    $inventory_transaction_item = InventoryTransaction::findOrFail(104);
                    $this->inventorySupplies($production, $items_supplies, $inventory_transaction_item);
                    $inventory_transaction_item2 = InventoryTransaction::findOrFail(103);
                    $this->inventoryFinishedProduct($production, $inventory_transaction_item2);
                }
            } catch (Exception $ex) {
                Log::error("Error UPDATE PRODUCTION: " . $ex->getMessage());
                $production->state_type_id = '01';
                $production->save();
                return [
                    'success' => false,
                    'message' => $ex->getMessage()
                ];
            }


            return [
                'success' => true,
                'message' => 'Registro actualizado correctamente'
            ];
        });

        return $result;
    }

    public function tranferSamples($samples, $destination_warehouse_id, $warehouse_id, $production)
    {
        Log::info('Entra a transfersamples');
        //Log::info('$samples - '.$samples);
        //Log::info('$destination_warehouse_id - '.$destination_warehouse_id);
        try {
            if (isset($samples) && $samples > 0 && isset($destination_warehouse_id) && $destination_warehouse_id != null) {
                Log::info('Entra al if transfersamples');
                $description = "Traslado de Muestras";
                $client_id = null;
                $created_at = Carbon::now();
                $warehouse_id = $warehouse_id;
                $warehouse_destination_id = $destination_warehouse_id;
                $compromise_quantity = $samples;
                Log::info('samples - '.$samples);
                $transfers = new TransferController();
                $transferRequest = new TransferRequest();

                $items = [];
                //Log::info('Production item before loop: ' . json_encode($production->item));
                //Log::info('$production - ' . $production);

                if (isset($production->item)) {
                    if (is_array($production->item)) {
                        //Log::error('Production item is an array');
                        foreach ($production->item as $item) {
                            $item_data = [
                                $lots = [
                                    [
                                        'id' => $item['id'],
                                        'compromise_quantity' => $samples,
                                        'code' => $production->lot_code,
                                        'checked' => true,
                                        'warehouse_id' => $warehouse_id,
                                        'warehouse_destination_id' => $warehouse_destination_id
                                    ]
                                ],
                                'id' => $item['id'],
                                'lots_enabled' => $item['lots_enabled'],
                                'lots' => $lots,
                                'warehouse_id' => $warehouse_id,
                                'warehouse_destination_id' => $warehouse_destination_id
                            ];
                            $items[] = $item_data;
                        }
                    } elseif (is_object($production->item)) {
                        //Log::info('Production item is an object');
                        $lots = [
                            [
                                'id' => $production->item->id,
                                'compromise_quantity' => $samples,
                                'code' => $production->lot_code,
                                'checked' => true,
                                'warehouse_id' => $warehouse_id,
                                'warehouse_destination_id' => $warehouse_destination_id
                            ]
                        ];
                        $item_data = [
                            'id' => $production->item->id,
                            'lots_enabled' => $production->item->lots_enabled,
                            'lots' => $lots,
                            'warehouse_id' => $warehouse_id,
                            'warehouse_destination_id' => $warehouse_destination_id
                        ];
                        $items[] = $item_data;
                    } else {
                        Log::error('Production item is neither an array nor an object');
                    }
                } else {
                    Log::error('Production item is not set');
                }

                Log::info('fin transfersSamples');


                //$request = new Request();
                $transferRequest['description'] = $description;
                $transferRequest['warehouse_id'] = $warehouse_id;
                $transferRequest['warehouse_destination_id'] = $warehouse_destination_id;
                $transferRequest['items'] = $items;
                $transferRequest['client_id'] = $client_id;
                $transferRequest['created_at'] = $created_at->toDateTimeString();
                Log::info('compromise_quantity - '.$compromise_quantity);
                $transferRequest['quantity'] = $compromise_quantity;
                Log::info('tranfer Request - '.json_encode($transferRequest));
                return $transfers->store($transferRequest);
            }
        } catch (Exception $ex) {
            Log::error("Error Transfer samples: " . $ex->getMessage());
        }

        //Log::info('Sale de transfersamples');

    }

    public function inventoryImperfectProduct($production, $inventory_transaction_item)
    {
        // esta funcion genera salida de inventario por porductos defectuosos
        if (isset($production->imperfect) && $production->imperfect > 0) {
            try {

                $inventory_it = new Inventory();
                $inventory_it->type = null;
                $inventory_it->description = $inventory_transaction_item->name;
                $inventory_it->item_id = $production->item_id;
                $inventory_it->warehouse_id = $production->warehouse_id;
                $inventory_it->quantity = (float) $production->imperfect;
                $inventory_it->inventory_transaction_id = $inventory_transaction_item->id;
                $inventory_it->lot_code = ($production->lot_code) ? $production->lot_code : null;
                $inventory_it->save();
            } catch (Exception $ex) {

                throw $ex;
            }
        }
    }

    public function inventoryFinishedProduct($production, $inventory_transaction_item)
    {
        try {
            //esta función creará el inventario del producto terminado
            //Log::info("production: ".json_encode($production));
            $inventory_it = new Inventory();
            $inventory_it->type = null;
            $inventory_it->description = $inventory_transaction_item->name;
            $inventory_it->item_id = $production->item_id;
            $inventory_it->warehouse_id = $production->warehouse_id;
            $inventory_it->quantity = (float) $production->quantity;
            $inventory_it->inventory_transaction_id = $inventory_transaction_item->id;
            $inventory_it->lot_code = ($production->lot_code) ? $production->lot_code : null;
            $inventory_it->production_id = $production->id;
            $inventory_it->save();

            if (isset($production->lot_code) && $production->lot_code != "") {

                $item_lots_group = new ItemLotsGroup();
                $item_lots_group->code = $production->lot_code;
                $item_lots_group->quantity = $production->quantity;
                $item_lots_group->item_id = $production->item_id;
                $item_lots_group->date_of_due = $production->date_end;
                $item_lots_group->warehouse_id = $production->warehouse_id;
                $item_lots_group->save();
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function inventorySupplies($production, $items_supplies,  $inventory_transaction_item)
    {
        try {
            //esta función creará el inventario de los insumos
            // Salida insumos por molino

            foreach ($items_supplies as $item) {
                //Log::info($item);
                if ($item['unit_type'] != 'Servicio') {
                    if ($item["lots_group"]) {
                        $lots_group = $item["lots_group"];

                        //VALIDAR CANTIDADES EN LOSTES PRIMERO
                        /*foreach ($lots_group as $lots) {
                            if(isset($lots["compromise_quantity"]) && floatval($lots["compromise_quantity"]) > 0){
                                $item_lots_group = ItemLotsGroup::findOrFail($lots["id"]);

                                if ($production->state_type_id == '04') {
                                    //$item_lots_group->quantity += isset($lots["compromise_quantity"]) ? $lots["compromise_quantity"] : 0;
                                } else {
                                    if($item_lots_group->quantity < $lots["compromise_quantity"]){
                                        return[
                                            'success' => false,
                                            'message' => 'Item:  Stok actual: '.$item_lots_group->quantity.' Lote:'.$lots["code"]. ' Stock pedido: '.$lots["compromise_quantity"]
                                        ];
                                    }
                                }
                            }
                        }*/

                        foreach ($lots_group as $lots) {

                            if (isset($lots["compromise_quantity"]) && floatval($lots["compromise_quantity"]) > 0) {
                                $qty = floatval($lots["compromise_quantity"]) ?? 0;
                                //PRIMERO INTENTA REALIZAR EL INGRESO O SALIDA DEL STOCK
                                $item_lots_group = ItemLotsGroup::findOrFail($lots["id"]);
                                if ($production->state_type_id == '04') {
                                    $item_lots_group->quantity += isset($lots["compromise_quantity"]) ? $lots["compromise_quantity"] : 0;
                                } else {
                                    $item_lots_group->quantity -= isset($lots["compromise_quantity"]) ? $lots["compromise_quantity"] : 0;
                                }
                                $item_lots_group->save();
                                //GENERA EL MOVIMIENTO DE INVENTARIO
                                $inventory_it = new Inventory();
                                $inventory_it->type = null;
                                $inventory_it->description = $inventory_transaction_item->name;
                                $inventory_it->item_id = (isset($item['item_id'])) ? $item['item_id'] : $item['individual_item_id'];
                                $inventory_it->warehouse_id = (isset($item['warehouse_id'])) ? $item['warehouse_id'] : $production->warehouse_id;
                                $inventory_it->quantity = (float) ($qty);
                                $inventory_it->inventory_transaction_id = $inventory_transaction_item->id;
                                $inventory_it->lot_code = $lots["code"];
                                $inventory_it->production_id = $production->id;
                                $inventory_it->save();
                            }
                        }
                    } else {

                        $qty = $item['quantity'] ?? 0;
                        $inventory_it = new Inventory();
                        $inventory_it->type = null;
                        $inventory_it->description = $inventory_transaction_item->name;
                        $inventory_it->item_id = (isset($item['item_id'])) ? $item['item_id'] : $item['individual_item_id'];
                        $inventory_it->warehouse_id = (isset($item['warehouse_id'])) ? $item['warehouse_id'] : $production->warehouse_id;
                        $inventory_it->quantity = (float) ($qty);
                        $inventory_it->inventory_transaction_id = $inventory_transaction_item->id;
                        $inventory_it->save();
                    }
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getProductionCount($date)
    {
        $count = Production::whereDate('created_at', $date)->count();

        // Incrementar el contador para la próxima producción
        return response()->json(['count' => $count + 1]);
    }

    public function updateOld(ProductionRequest $request, $id)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {
            //llama a la producción que fue creada
            $production = Production::findOrFail($id);
            //si la producción que esta en el estado 01 (registrado) y se quiere cambiar a 02 (en elaboración)
            if (!$production) {
                return [
                    'success' => false,
                    'message' => 'No se encontró el registro'
                ];
            }
            $new_state_type_id = $request->records_id;
            $old_state_type_id = $production->state_type_id;
            $quantity = $request->input('quantity');
            $warehouse_id = $request->input('warehouse_id');
            $production->fill($request->all());
            $production->warehouse_id = $warehouse_id;
            $production->quantity = $quantity;
            $production->state_type_id = $new_state_type_id;
            $informative = ($request->informative) ?: false;
            $production->user_id = auth()->user()->id;
            $production->soap_type_id = $this->getCompanySoapTypeId();
            $production->save();

            $items_supplies = $request->supplies;


            foreach ($items_supplies as $item) {
                $production_supply = ProductionSupply::where('production_id', $production->id)->where("item_supply_id", $item['id'])->first();
                $production_id = $production->id;
                $qty = $item['quantity'] ?? 0;
                $production_supply->production_name = $production->name;
                $production_supply->production_id = $production_id;
                $production_supply->item_supply_name = $item['description'];
                $production_supply->item_supply_id = $item['id'];
                $production_supply->warehouse_name = $item['warehouse_name'] ?? null;
                $production_supply->warehouse_id = $item['warehouse_id'] ?? null;
                $production_supply->quantity = (float) $qty;
                $production_supply->save();

                $lots_group = $item["lots_group"];
                foreach ($lots_group as $lots) {
                    $item_lots_groups = ItemSupplyLot::where('production_id', $production->id)->where("item_supply_id", $production_supply->item_supply_id)->where("lot_id", $lots["lot_id"])->first();
                    $item_lots_groups->item_supply_id = $production_supply->item_supply_id;
                    $item_lots_groups->item_supply_name = $item['description'];
                    $item_lots_groups->lot_code = $lots["code"];
                    $item_lots_groups->lot_id = $lots["lot_id"];
                    $item_lots_groups->production_name = $production->name;
                    $item_lots_groups->production_id = $production_id;
                    $item_lots_groups->quantity = $lots["compromise_quantity"];
                    $item_lots_groups->expiration_date = $lots["date_of_due"];
                    $item_lots_groups->save();
                }
            }

            if ($old_state_type_id == '01' && $new_state_type_id == '02' && !$informative) {
                //cuando pasa a elaboración se decuenta el inventario la lista de materiales que se está utilizando en la fabricación del producto.
                $inventory_transaction_item = InventoryTransaction::findOrFail(101);
                $this->inventorySupplies($production, $items_supplies, $inventory_transaction_item);
            }
            if ($old_state_type_id == '02' && $new_state_type_id == '03' && !$informative) {
                //cuando pasa a terminado se aumenta el inventario del producto terminado
                $inventory_transaction_item = InventoryTransaction::findOrFail(19);
                $this->inventoryFinishedProduct($production, $inventory_transaction_item);
            }
            if ($old_state_type_id == '03' && $new_state_type_id == '04' && !$informative) {
                //cuando pasa a anulado se aumenta el inventario de los materiales que se utilizó en la fabricación del producto terminado
                $inventory_transaction_item = InventoryTransaction::findOrFail(104);
                $this->inventorySupplies($production, $items_supplies, $inventory_transaction_item);
                $inventory_transaction_item2 = InventoryTransaction::findOrFail(103);
                $this->inventoryFinishedProduct($production, $inventory_transaction_item2);
            }

            return [
                'success' => true,
                'message' => 'Registro actualizado correctamente'
            ];
        });

        return $result;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function tables()
    {
        $machines = Machine::query()->get()->transform(function (Machine $row) {
            return $row->getCollectionData();
        });
        $state_types_prod = StateTypeProduction::get();
        $state_type_descr = StateTypeProduction::find('01');
        $item_warehouses = ItemWarehouse::get();
        //Log::info('items111 - '.self::optionsItemProduction(),);
        return [
            'items' => self::optionsItemProduction(),
            'warehouses' => $this->optionsWarehouse(),
            'machines' => $machines,
            'state_types_prod' => $state_types_prod,
            //'state_types_id' => count($state_types_prod) > 0 ? $state_types_prod->first()->id : null,
            'state_type_descr' => $state_type_descr->description,
            'item_warehouses' => $item_warehouses,
        ];
    }

    public function getLotGroups($warehouse_id, $item_id, $supply_id)
    {
        $lots_groups = [];

        $items = self::optionsItemProduction($item_id);
        //Log::info('items213 - '.json_encode($items));

        foreach ($items as $item) {
            //Log::info('item123 - '.json_encode($item));
            $supplies = $item['supplies'];
            foreach ($supplies as $supply) {
                Log::info('supply - ' . json_encode($supply));
                if ($supply['individual_item_id'] == $supply_id) {
                    //Log::info('supply - '.json_encode($supply));
                    foreach ($supply['lots_group'] as $lots) {
                        if ($lots->warehouse_id == $warehouse_id && $lots->quantity > 0) {
                            //Log::info('lotgroups123 - '.json_encode($lots));
                            array_push($lots_groups, $lots);
                        }
                    }
                }
            }
        }
        //Log::info(' lots - '.json_encode($lots_groups));

        return compact('lots_groups');
    }

    public static function optionsItemProduction($itemId = null)
    {
        $query = Item::ProductEnded();
        if ($itemId !== null) {
            $query->find($itemId);
        }

        //Log::info("ITEM: ".json_encode($query->get()));

        $result = $query->get()
            ->transform(function (Item $row) {
                $data = $row->getCollectionData();
                $supplies = $data["supplies"];
                //Log::info("ITEM SUPPLIES GLOBAL: ".json_encode($supplies));
                $transformed_supplies = [];
                foreach ($supplies as $value) {
                    //Log::info("ITEM SUPPLIES: ".json_encode($value));
                    $lots_group = $value["individual_item"]["lots_group"];

                    foreach ($lots_group as $lot) {
                        $lot["item_supply_id"] = $value["id"];
                    }
                    $descriotion = $value["individual_item"]["description"] ? $value["individual_item"]["name"] . '/' . $value["individual_item"]["description"] : $value["individual_item"]["name"];
                    /*$unit_quantity = $value['quantity'];
                    if ($value['rounded_up'] > 0) {
                        $truncated_number = bcdiv($value['quantity'], 1, 3);
                        $last_digit = substr($truncated_number, -1);

                        $truncated_number = substr($truncated_number, 0, -1); // Remueve el último dígito para redondeo
                        if ($last_digit <= 2) {
                            $rounded_number = $truncated_number . '0'; // Convierte a '0'
                        } elseif ($last_digit >= 3 && $last_digit <= 7) {
                            $rounded_number = $truncated_number . '5'; // Convierte a '5'
                        } elseif ($last_digit >= 8) {
                            $rounded_number = bcadd($truncated_number, '0.01', 2); // Incrementa en '0.01'
                        }
                        $value['quantity'] = $rounded_number;
                    }*/

                    //Log::info('new value - ' .$value['quantity']);
                    //Log::info('old value - ' .$unit_quantity);
                    $transformed_supply = [
                        'id' => $value["id"],
                        'individual_item_id' => $value["individual_item_id"],
                        'description' => $descriotion,
                        'quantity' => $value["quantity"],
                        'unit_type' => $value["individual_item"]["unit_type"]["description"],
                        'quantity_per_unit' => $value['quantity'],
                        'cost_per_unit' => (isset($value["cost_per_unit"]) && $value["cost_per_unit"] > 0) ? $value["cost_per_unit"] : $value["individual_item"]["purchase_mean_cost"],
                        'lots_enabled' => $value["individual_item"]["lots_enabled"],
                        'warehouse' => $value["individual_item"]["warehouse_id"],
                        'modificable' => $value["modificable"],
                        'rounded_up' => $value["rounded_up"],
                        'lots_group' => $lots_group,
                    ];
                    $transformed_supplies[] = $transformed_supply;
                }
                $data["supplies"] = $transformed_supplies;
                return $data;
            });
        //Log::info("ITEM RETURNED: ".json_encode($result));
        return $result;
    }

    public function searchItems(Request $request)
    {
        $search = $request->input('search');

        return [
            'items' => self::optionsItemFullProduction($search, 20),
        ];
    }

    public static function optionsItemFullProduction($search = null, $take = null)
    {
        $query = Item::query()
            ->ProductEnded()
            ->with('item_lots', 'item_lots.item_loteable', 'lots_group');
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhere('internal_id', 'like', "%{$search}%");
        }
        if ($take) {
            $query->take($take);
        }
        return $query->get()->transform(function (Item $row) {
            return $row->getCollectionData();
        });
    }

    public function records(Request $request)
    {
        $records = $this->getRecords($request);
        return new ProductionCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function record($id)
    {
        $production = Production::findOrFail($id);
        $production_supplies = ProductionSupply::where('production_id', $production->id)->with('itemSupply.individual_item')->get();
        Log::info('prod_supp - ' . $production_supplies);
        $warehouse_id = $production->warehouse_id;
        $data = $production->getCollectionData();
        $data['item_id'] = $production->item_id;
        $data['warehouse_id'] = $warehouse_id;
        $data['records_id'] = $production->state_type_id;
        //hago un recorrido de todo los insumos que utilicé para fabricar un producto.
        $transformed_supplies = [];
        //Log::info("production_supplies".json_encode($production_supplies));
        foreach ($production_supplies as $supply) {
            $checked = $supply->checked;
            $item_supply_id = $supply->item_supply_id;
            //por cada insumo que se fabricó voy a obtener los lotes que se utilizó
            //para ello obtengo la producción y el id del insumo que se utilizó en esa producción
            $itemSupplyLots = ItemSupplyLot::select('item_supply_lots.*', 'item_lots_group.*', 'item_lots_group.quantity as compromise_quantity', 'item_supply_lots.quantity as supply_quantity')
                ->where('production_id', $production->id)
                ->where('item_supply_id', $supply->itemSupply->id)
                ->join('item_lots_group', 'item_lots_group.id', '=', 'item_supply_lots.lot_id')
                ->get();
            $transformed_supply_lots = [];

            foreach ($itemSupplyLots as $supplyLots) {
                $transformed_supply_lots[] = [
                    'lot_id' => $supplyLots["lot_id"],
                    'code' => $supplyLots["lot_code"],
                    'quantity' => $supplyLots["compromise_quantity"],
                    'compromise_quantity' => $supplyLots["supply_quantity"],
                    'date_of_due' => $supplyLots["expiration_date"],
                    'item_id' => $supplyLots["item_supply_id"],
                ];
            }

            $transformed_supply = [
                'id' => $item_supply_id,
                'description' => $supply->item_supply_name ?? '',
                'item_id' => $supply->itemSupply->individual_item->id,
                'quantityD' => $supply->quantity,
                'checked' => $checked,
                'quantity' => $supply->quantity,
                'unit_type' => $supply->itemSupply->individual_item->unit_type->description,
                'quantity_per_unit' => $supply->quantity,
                'lots_enabled' => $supply->itemSupply->individual_item->lots_enabled,
                'warehouse_id' => $supply->warehouse_id,
                'warehouse_name' => $supply->warehouse_name,
                'lots_group' => $transformed_supply_lots,
            ];

            $transformed_supplies[] = $transformed_supply;
            //Log::info("transformed_supplies",$transformed_supplies);
        }
        $data["supplies"] = $transformed_supplies;
        return $data;
    }

    public function getRecords(Request $request)
    {
        //Log::info($request);
        $state_type_id = $request->state_type_id;
        $data_of_period = $this->getDatesOfPeriod($request);

        $request = json_decode($request->form, true);
        $order = $request['order'];
        $status = $request['status'];
        //Log::info($request['order']);
        $data = Production::query();

        if (!empty($data_of_period['d_start'])) {
            $data->where(function ($query) use ($data_of_period) {
                $query->where('date_start', '>=', $data_of_period['d_start'])
                    ->orWhere(
                        function ($query) use ($data_of_period) {
                            $query->whereNull('date_start')
                                ->where('created_at', '>=', $data_of_period['d_start']);
                        }
                    );
            });
        }

        if (!empty($data_of_period['d_end'])) {
            $data->where(function ($query) use ($data_of_period) {
                $query->where('date_end', '<=', $data_of_period['d_end'])
                    ->orWhere(
                        function ($query) use ($data_of_period) {
                            $query->whereNull('date_end')
                                ->where('created_at', '<=', $data_of_period['d_end']);
                        }
                    );
            });
        }

        if (isset($order) && $order != '') {
            $data->where('production_order', 'like', '%' . $order . '%');
        }
        if (isset($status) && $status != '') {
            $data->where('state_type_id', $status);
        }


        return $data;
    }

    public function getRecords2(Request $request)
    {
        $state_type_id = $request->state_type_id;

        $data_of_period = $this->getDatesOfPeriod($request);
        $data = Production::query();
        if (!empty($data_of_period['d_start'])) {
            $data->where('date_start', '>=', $data_of_period['d_start']);
        }
        if (!empty($data_of_period['d_end'])) {
            $data->where('date_end', '<=', $data_of_period['d_end']);
        }
        if ($state_type_id) {
            $data->where('state_type_id', 'like', '%' . $state_type_id . '%');
        }
        return $data;
    }

    public function updateStockWarehouses($warehouseId, $itemId)
    {
        //Log::info('warehouse - item : '.$warehouseId.'-'.$itemId);
        $warehouseItem = ItemWarehouse::where('warehouse_id', $warehouseId)
            ->where('item_id', $itemId)
            ->first();

        if (!$warehouseItem) {
            return response()->json(['stock' => 0]);
        }
        //Log::info('warehouseItem - '.$warehouseItem);

        $stock = $warehouseItem->stock;

        return response()->json(['stock' => $stock]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Database\Eloquent\Builder|Builder|Production
     */
    public function getDatesOfPeriod($request)
    {

        if ($request->has('form')) {
            $request = json_decode($request->form, true);
        }
        $period = $request['period'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        $month_start = $request['month_start'];
        $month_end = $request['month_end'];

        $d_start = Carbon::now()->startOfMonth()->format('Y-m-d');
        $d_end = Carbon::now()->endOfMonth()->format('Y-m-d');
        /** @todo: Eliminar periodo, fechas y cambiar por
         * $date_start = $request['date_start'];
         * $date_end = $request['date_end'];
         * \App\CoreFacturalo\Helpers\Functions\FunctionsHelper\FunctionsHelper::setDateInPeriod($request, $date_start, $date_end);
         */
        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_start . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_end . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'date':
                $d_start = $date_start;
                $d_end = $date_start;
                break;
            case 'between_dates':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
        }


        return [
            'd_start' => $d_start,
            'd_end' => $d_end
        ];
    }

    /**
     * @param Request $request
     *
     * @return Response|BinaryFileResponse
     */
    public function excel(Request $request)
    {
        // $records = $this->getData($request);
        $records = $this->getRecords($request)->where('state_type_id', '03')->get()->transform(function (Production $row) {
            return $row->getCollectionData();
        });

        $buildProductsExport = new BuildProductsExport();
        $buildProductsExport->setCollection($records);
        $filename = 'Reporte de produccion - ' . date('YmdHis');
        // return $buildProductsExport->view();
        return $buildProductsExport->download($filename . '.xlsx');
    }

    /**
     * @param Request $request
     *
     * @return Response|BinaryFileResponse
     */
    public function excel2(Request $request)
    {
        // $records = $this->getData($request);
        $records = $this->getRecords($request)->where('state_type_id', '02')->get()->transform(function (Production $row) {
            return $row->getCollectionData();
        });

        $buildProductsExport = new BuildProductsExport();
        $buildProductsExport->setCollection($records)->setInProccess(true);
        $filename = 'Reporte de produccion en proceso- ' . date('YmdHis');
        // return $buildProductsExport->view();
        return $buildProductsExport->download($filename . '.xlsx');
    }


    public function pdf(Request $request)
    {

        $records = $this->getRecords($request)->get()->transform(function (Production $row) {
            return $row->getCollectionData();
        });

        $pdf = PDF::loadView(
            'production::production.partial.export',
            compact(
                'records'
            )
        )->setPaper('a4', 'landscape');

        $filename = 'Reporte de produccion - ' . date('YmdHis');
        return $pdf->stream($filename . '.pdf');
    }

    public function plantillaMezcla($recordId)
    {

        $records = Production::find($recordId);
        $company = Company::first();

        $pdf = PDF::loadView('production::production.plantilla_mezcla', compact("records", "company"));
        return $pdf->stream('Plantilla Mezcla.pdf');
    }

    public function plantillaNoConforme($recordId)
    {

        $order = Production::find($recordId);
        $company = Company::first();

        $pdf = PDF::loadView('production::production.plantilla_no_conforme', compact("records", "company"));
        return $pdf->stream('Plantilla No Conforme.pdf');
    }

    public function pdf_Atributos($recordId)
    {
        $fechas =  Production::find($recordId);
        $company = Company::first();
        $records = Item::find($fechas->item_id);

        $usuario_log = Auth::user();
        $fechaActual = date('d/m/Y');

        //$insumos = ItemSupply::where('item_id', '=', $fechas->item_id)
        //    ->leftJoin('items', 'item_supplies.individual_item_id','=','items.id')->get();
        //Log::info($fechas);

        $pdf = PDF::loadView('production::production.pdf_atributos', compact("records", "company", "usuario_log", "recordId", "fechas"));

        $filename = 'Certifiicado_Calidad_' . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }

    public function etiqueta($recordId)
    {
        $produccion =  Production::find($recordId);
        $company = Company::first();
        $records = Item::find($produccion->item_id)->getCollectionData();
        //$usuario_log = Auth::user();
        $fechaActual = date('d/m/Y');

        //Log::info("rtiquetas".json_encode($produccion->warehouse));
        //Log::info("rtiquetas".json_encode($produccion->warehouse->establishment));
        $recordId = $produccion->item_id;
        $pdf = PDF::loadView('production::production.etiquetas_pdf', compact("records", "company", "recordId", "produccion"));

        $filename = 'Etiquetas_' . $produccion->production_order . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }

    public function etiqueta2($recordId)
    {
        Log::info('id - '.$recordId);
        $produccion = Production::with('production_supplies')->find($recordId);
        Log::info('produccion -'.json_encode($produccion->production_supplies->item_supply));
        //$production_supplies = ProductionSupply::where('production_id', $recordId)->get();
        $production_supplies = ProductionSupply::where('production_id', $produccion->id)->get();

        Log::info('production_supplies - '.json_encode($production_supplies));
        $company = Company::first();
        $fechaActual = date('d/m/Y');
        //$recordId = $produccion->item_id;
        //Log::info('recordId - '.$recordId );
        $pdf = PDF::loadView('production::production.etiquetas2_pdf', compact("production_supplies", "company", "recordId", "produccion"));

        $filename = 'Etiquetas2_' . $produccion->production_order . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }

    public function import(Request $request)
    {
        if ($request->hasFile('file')) {
            try {
                $import = new ProductionImport();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();

                return [
                    'success' => true,
                    'message' => __('app.actions.upload.success'),
                    'data' => $data,
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' => __('app.actions.upload.error'),
        ];
    }
}

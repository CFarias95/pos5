<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Requests\Inputs\Common\PersonInput;
use App\CoreFacturalo\Template;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SearchItemController;
use App\Http\Requests\Tenant\PurchaseImportRequest;
use App\Http\Requests\Tenant\PurchaseRequest;
use App\Http\Resources\Tenant\PurchaseCollection;
use App\Http\Resources\Tenant\PurchaseResource;
use App\Models\Tenant\AccountingEntries;
use App\Models\Tenant\AccountingEntryItems;
use App\Models\Tenant\AccountMovement;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\AttributeType;
use App\Models\Tenant\Catalogs\ChargeDiscountType;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Catalogs\OperationType;
use App\Models\Tenant\Catalogs\PriceType;
use App\Models\Tenant\Catalogs\PurchaseDocumentType;
use App\Models\Tenant\Catalogs\RetentionType;
use App\Models\Tenant\Catalogs\SystemIscType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\CreditNotesPayment;
use App\Models\Tenant\DocumentTypesSustentoSRI;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\GuideFile;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemUnitType;
use App\Models\Tenant\ItemWarehouse;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Person;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchaseItem;
use App\Traits\OfflineTrait;
use DOMDocument;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Finance\Http\Controllers\PaymentFileController;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Inventory\Models\Warehouse;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Purchase\Models\PurchaseOrder;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use stdClass;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use App\Models\Tenant\GeneralPaymentCondition;
use App\Models\Tenant\Imports;
use App\Models\Tenant\PurchaseDocumentTypes2;
use App\Models\Tenant\PurchasePayment;
use App\Models\Tenant\Retention;
use App\Models\Tenant\RetentionTypePurchase;
use App\Models\Tenant\RetentionsDetailEC;
use App\Models\Tenant\RetentionsEC;
use App\Models\Tenant\RetentionTypesPurchase;
use App\Models\Tenant\Series;
use App\Models\Tenant\TypeDocsPurchase;
use App\Models\Tenant\UserDefaultDocumentType;
use App\Models\Tenant\Warehouse as TenantWarehouse;
use Illuminate\Support\Facades\Log;
use Modules\Sale\Models\SaleOpportunity;
use App\Traits\KardexTrait;

class PurchaseController extends Controller
{

    use FinanceTrait;
    use StorageDocument;
    use OfflineTrait;
    use KardexTrait;

    private $id;
    private $purchase;

    public function index()
    {
        return view('tenant.purchases.index');
    }


    public function create($purchase_order_id = null)
    {
        return view('tenant.purchases.form', compact('purchase_order_id'));
    }

    public function columns()
    {
        $columns =  [
            'number' => 'Número',
            'date_of_issue' => 'Fecha de emisión',
            'date_of_due' => 'Fecha de vencimiento',
            'date_of_payment' => 'Fecha de pago',
            'name' => 'Nombre proveedor',
        ];

        $documents = PurchaseDocumentTypes2::get()->transform(function ($row) {
            return [
                'id' => $row->idType,
                'name' => $row->description,
            ];
        });

        return compact('columns', 'documents');
    }

    public function records(Request $request)
    {

        $records = $this->getRecords($request);

        return new PurchaseCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function getRecords($request)
    {

        $records = Purchase::query();

        switch ($request->column) {
            case 'name':

                $records->whereHas('supplier', function ($query) use ($request) {
                    return $query->where($request->column, 'like', "%{$request->value}%");
                })
                    ->whereTypeUser();
                break;

            case 'date_of_payment':

                $records->whereHas('purchase_payments', function ($query) use ($request) {
                    return $query->where($request->column, 'like', "%{$request->value}%");
                })
                    ->whereTypeUser();

                break;

            default:

                $records->where($request->column, 'like', "%{$request->value}%")
                    ->whereTypeUser();

                break;
        }

        if ($request->sequential) {

            $records->where('sequential_number', 'like', "%{$request->sequential}%");
        }

        if ($request->intern) {
            $records->where('document_type_intern', $request->intern);
        }

        if ($request->retention) {
            $retentions = RetentionsEC::where('idRetencion', 'like', '%' . $request->retention . '%')->select('idDocumento')->get();
            $records->whereIn('id', $retentions);
        }

        return $records->latest();
    }

    public function tables()
    {
        $suppliers = $this->table('suppliers');
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $currency_types = CurrencyType::whereActive()->get();
        $document_types_invoice = DocumentType::DocumentsActiveToPurchase()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->whereActive()->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->whereActive()->get();
        $company = Company::active();
        $payment_method_types = PaymentMethodType::getPaymentMethodTypes();
        // $payment_method_types = PaymentMethodType::all();
        $payment_destinations = $this->getPaymentDestinations();
        $customers = $this->getPersons('customers');
        $configuration = Configuration::first();
        $payment_conditions = GeneralPaymentCondition::get();
        $warehouses = Warehouse::get();
        $permissions = auth()->user()->getPermissionsPurchase();
        $global_discount_types = ChargeDiscountType::whereIn('id', ['02', '03'])->whereActive()->get();

        $retention_types_iva = RetentionType::where('type_id', '02')->get();
        $retention_types_income = RetentionType::where('type_id', '01')->get();

        $retention_types_iva = RetentionType::where('type_id', '02')->get();
        $retention_types_income = RetentionType::where('type_id', '01')->get();

        $imports = Imports::where('estado', ['Registrada', 'Liberada'])->get();
        $typeDocs = TypeDocsPurchase::where('active', 1)->get();
        $codSustentos = DocumentTypesSustentoSRI::get();
        $typeDocs2 = PurchaseDocumentTypes2::where('active', 1)->get();


        return compact(
            'suppliers',
            'establishment',
            'currency_types',
            'discount_types',
            'configuration',
            'payment_conditions',
            'charge_types',
            'typeDocs2',
            'imports',
            'typeDocs',
            'codSustentos',
            'document_types_invoice',
            'company',
            'retention_types_iva',
            'retention_types_income',
            'payment_method_types',
            'payment_destinations',
            'customers',
            'warehouses',
            'permissions',
            'global_discount_types'
        );
    }

    public function tables_purchase()
    {
        $suppliers = $this->table('suppliers');
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $currency_types = CurrencyType::whereActive()->get();
        if (!empty(Purchase::latest()->first()->id)) {
            $purchase_id = Purchase::latest()->first()->id;
            $number = Purchase::where('id', $purchase_id)->get();
        } else {
            $number = [];
        }
        $document_types_invoice = PurchaseDocumentType::DocumentsActiveToPurchase()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->whereActive()->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->whereActive()->get();
        $company = Company::active();
        $payment_method_types = PaymentMethodType::getPaymentMethodTypes();
        // $payment_method_types = PaymentMethodType::all();
        $payment_destinations = $this->getPaymentDestinations();
        $customers = $this->getPersons('customers');
        $configuration = Configuration::first();
        $payment_conditions = GeneralPaymentCondition::get();
        $warehouses = Warehouse::get();
        $permissions = auth()->user()->getPermissionsPurchase();
        $global_discount_types = ChargeDiscountType::whereIn('id', ['02', '03'])->whereActive()->get();
        $retention_types_iva = RetentionType::where('type_id', '02')->get();
        $retention_types_income = RetentionType::where('type_id', '01')->get();
        $imports = Imports::where('estado', ['Registrada', 'Liberada'])->get();
        $typeDocs = TypeDocsPurchase::where('active', 1)->get();
        $codSustentos = DocumentTypesSustentoSRI::get();
        $typeDocs2 = PurchaseDocumentTypes2::where('active', 1)->get();

        return compact(
            'suppliers',
            'establishment',
            'currency_types',
            'imports',
            'typeDocs',
            'typeDocs2',
            'number',
            'discount_types',
            'configuration',
            'payment_conditions',
            'charge_types',
            'document_types_invoice',
            'company',
            'codSustentos',
            'retention_types_income',
            'retention_types_iva',
            'payment_method_types',
            'payment_destinations',
            'customers',
            'warehouses',
            'permissions',
            'global_discount_types'
        );
    }

    public function table($table)
    {
        switch ($table) {
            case 'suppliers':

                $suppliers = Person::where('type', 'suppliers')->orderBy('name')->get()->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'description' => $row->number . ' - ' . $row->name,
                        'name' => $row->name,
                        'number' => $row->number,
                        'perception_agent' => (bool)$row->perception_agent,
                        'identity_document_type_id' => $row->identity_document_type_id,
                        'identity_document_type_code' => $row->identity_document_type->code
                    ];
                });
                return $suppliers;
                break;

            case 'items':
                return SearchItemController::getItemToPurchase();
                break;
            default:

                return [];
                break;
        }
    }

    public function getPersons($type)
    {

        $persons = Person::whereType($type)->orderBy('name')->take(20)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->number . ' - ' . $row->name,
                'name' => $row->name,
                'number' => $row->number,
                'identity_document_type_id' => $row->identity_document_type_id,
            ];
        });

        return $persons;
    }

    public function item_tables()
    {
        $items = $this->table('items');
        $items_import = Item::all()->transform(function ($row) {
            $full_description = $row->name . ' / ' . $row->description . ' / ' . $row->model . ' / ' . $row->internal_id;
            return [
                'id' => $row->id,
                'item_code' => $row->item_code,
                'full_description' => $full_description,
                'description' => $row->description,
                'currency_type_id' => $row->currency_type_id,
                'currency_type_symbol' => $row->currency_type->symbol,
                'sale_unit_price' => $row->sale_unit_price,
                'purchase_unit_price' => $row->purchase_unit_price,
                'unit_type_id' => $row->unit_type_id,
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'purchase_has_igv' => (bool)$row->purchase_has_igv,
                'has_perception' => (bool)$row->has_perception,
                'lots_enabled' => (bool)$row->lots_enabled,
                'percentage_perception' => $row->percentage_perception,
                'item_unit_types' => $row->item_unit_types->transform(function ($row) {
                    if (is_array($row)) return $row;
                    if (is_object($row)) {
                        /**@var ItemUnitType $row */
                        return $row->getCollectionData();
                    }
                    return $row;
                }),
                'series_enabled' => (bool)$row->series_enabled,
                'purchase_has_isc' => $row->purchase_has_isc,
                'purchase_system_isc_type_id' => $row->purchase_system_isc_type_id,
                'purchase_percentage_isc' => $row->purchase_percentage_isc,

            ];
        });
        $categories = [];
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $system_isc_types = SystemIscType::whereActive()->get();
        $price_types = PriceType::whereActive()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $attribute_types = AttributeType::whereActive()->orderByDescription()->get();
        $warehouses = Warehouse::all();

        $retention_types_iva = RetentionType::where('type_id', '02')->whereActive()->get();
        $retention_types_income = RetentionType::where('type_id', '01')->whereActive()->get();

        $retention_types_purch = RetentionTypePurchase::get();

        $operation_types = OperationType::whereActive()->get();
        $is_client = $this->getIsClient();
        $configuration = Configuration::first();
        $configuration = $configuration->getCollectionData();
        $imports = Imports::where('estado', ['Registrada', 'Liberada'])->get();
        $currencyTypes = CurrencyType::whereActive()->get();
        return compact(
            'items',
            'categories',
            'affectation_igv_types',
            'system_isc_types',
            'price_types',
            'discount_types',
            'charge_types',
            'attribute_types',
            'currencyTypes',
            'warehouses',
            'imports',
            'operation_types',
            'is_client',
            'configuration',
            'retention_types_iva',
            'retention_types_income',
            'retention_types_purch',
            'items_import'
        );
    }

    public function record($id)
    {

        $record = new PurchaseResource(Purchase::findOrFail($id));

        return $record;
    }

    public function edit($id)
    {
        $resourceId = $id;
        return view('tenant.purchases.form_edit', compact('resourceId'));
    }

    public function store(PurchaseRequest $request)
    {
        $data = self::convert($request);
        $docIntern = PurchaseDocumentTypes2::where('idType', $request->document_type_intern)->get();
        $alteraStock = (bool)($docIntern && $docIntern[0]->stock) ? $docIntern[0]->stock : 0;
        $signo = ($docIntern && $docIntern[0]->sign == 0) ? -1 : 1;

        $validar = Purchase::where('supplier_id', $data['supplier_id'])->where('sequential_number', $data['sequential_number'])->get();
        if ($validar && $validar->count() > 0) {
            return [
                'success' => false,
                'message' => 'La factura ' . $data['sequential_number'] . ' ya se encuentra registrada con ese proveedor',
            ];
        }
        try {
            $purchase = DB::connection('tenant')->transaction(function () use ($data, $signo, $docIntern) {
                $numero = Purchase::where('establishment_id', $data['establishment_id'])->where('series', $data['series'])->count();
                $data['number'] = $numero + 1;
                $doc = Purchase::create($data);

                if (count($data['ret']) > 0) {

                    $serie = UserDefaultDocumentType::where('user_id', auth()->user()->id)->where('document_type_id','20')->first();
                    $tipoSerie = null;
                    $tiposerieText = '';

                    if (isset($serie) && $serie->series_id != '') {
                        $tipoSerie = Series::find($serie->series_id);
                        $tiposerieText = $tipoSerie->number;
                    } else {
                        $tipoSerie = Series::where('document_type_id', '20')->first();
                        $tiposerieText = $tipoSerie->number;
                    }

                    $establecimiento = Establishment::find($doc->establishment_id);
                    $secuelcialRet = RetentionsEC::where('establecimiento', $establecimiento->code)->where('ptoEmision', $tiposerieText)->orderBy('idRetencion','desc')->first();
                    $secuelcialRet = $secuelcialRet->idRetencion;
                    $secuelcialRet = substr($secuelcialRet,7);
                    $secuelcialRet = intVal($secuelcialRet);

                    $ret = new RetentionsEC();
                    $ret->idRetencion = 'R' . $establecimiento->code . substr($tiposerieText, 1, 3) . str_pad($secuelcialRet + 1, 9, 0, STR_PAD_LEFT);
                    $ret->idDocumento = $doc->id;
                    $ret->fechaFizcal = $doc->date_of_issue->format('m/Y');
                    $ret->idProveedor = $doc->supplier_id;
                    $ret->establecimiento = $establecimiento->code;
                    $ret->ptoEmision = $tiposerieText;
                    $ret->secuencial = $doc->sequential_number;
                    $ret->codSustento = $doc->document_type_id;
                    $ret->codDocSustento = $doc->codSustento;
                    $ret->numAuthSustento = $doc->auth_number;
                    $ret->status_id = '01';
                    $ret->save();

                    foreach ($data['ret'] as $retDet) {
                        //Log::info(json_encode($retDet));
                        $detRet = new RetentionsDetailEC();
                        $detRet->idRetencion = $ret->idRetencion;
                        $detRet->codRetencion = $retDet['code'];
                        $detRet->baseRet = $retDet['base'];
                        $detRet->porcentajeRet = $retDet['porcentajeRet'];
                        $detRet->valorRet = $retDet['valor'];
                        $detRet->save();
                    }
                }

                foreach ($data['items'] as $row) {

                    //Log::info('Item a crear: '.json_encode($row));
                    //Log::info('docintern - '.json_encode($docIntern));
                    //COSTO PROMEDIO COMPRA
                    $item = Item::where('id', $row['item_id'])->first();
                    if ($item->unit_type_id != 'ZZ' && $docIntern[0]->cost) {
                        $costoA = $item->purchase_mean_cost;
                        $stockA = $item->stock;
                        $totalA = $costoA * $stockA;

                        $costoN = floatval($row['unit_value']);
                        $stockN = floatval($row['quantity']);
                        $totalN = $costoN * $stockN;

                        $stockT = $stockN + $stockA;
                        $costoT = $totalA + $totalN;
                        $costoT = round($costoT / $stockT, 4);
                        Log::info("ACTUAL " . $costoA . '-' . $totalA . ' NUEVO: ' . $costoN . "-" . $totalN);

                        $item->purchase_mean_cost = $costoT;
                        $item->save();
                    }
                    $p_item = new PurchaseItem();
                    $row['quantity'] = $row['quantity'] * $signo;
                    $p_item->fill($row);
                    $lots = $row['lots'] ?? null;
                    if ($lots != null) {
                        // en compras, se guardan los lotes si existen en el campo item de purchase_items
                        $temp_item = $row['item'];
                        $temp_item['lots'] = $lots;
                        $p_item->item = $temp_item;
                    }
                    //$p_item->purchase_has_igv = $row['purchase_has_igv'];
                    $p_item->purchase_id = $doc->id;
                    $p_item->save();

                    if (isset($row['update_price']) && $row['update_price']) {
                        if (!($row['sale_unit_price'] ?? false)) {
                            throw new Exception('Debe ingresar el nuevo precio de venta del producto, cuando la opción "Actualizar precio de venta" está activado', 500);
                        }
                        Item::where('id', $row['item_id'])
                            ->update(['sale_unit_price' => floatval($row['sale_unit_price'])]);
                    }

                    if (isset($row['update_purchase_price']) && $row['update_purchase_price']) {

                        Log::info("update_purchase_price" . json_encode($row));

                        Item::query()->where('id', $row['item_id'])
                            ->update(['purchase_unit_price' => round(floatval($row['unit_value']), 2), 'purchase_has_igv' => false]);
                        // actualizacion de precios
                        $item = $row['item'];
                        if (isset($item['item_unit_types'])) {
                            $unit_type = $item['item_unit_types'];
                            foreach ($unit_type as $value) {
                                $item_unit_type = ItemUnitType::firstOrNew(['id' => $value['id']]);
                                $item_unit_type->item_id = (int)$row['item_id'];
                                $item_unit_type->description = $value['description'];
                                $item_unit_type->unit_type_id = $value['unit_type_id'];
                                $item_unit_type->quantity_unit = $value['quantity_unit'];
                                $item_unit_type->price1 = $value['price1'];
                                $item_unit_type->price2 = $value['price2'];
                                $item_unit_type->price3 = $value['price3'];
                                $item_unit_type->price_default = $value['price_default'];
                                $item_unit_type->save();
                            }
                        }
                        if (isset($item['item_warehouse_prices'])) {
                            $warehouse_prices = $item['item_warehouse_prices'];
                            foreach ($warehouse_prices as $prices) {
                                Item::setStaticItemWarehousePrice(
                                    (int)$row['item_id'],
                                    (int)$prices['id'],
                                    (int)$prices['warehouse_id'],
                                    $prices['price']
                                );
                            }
                        }
                    }

                    if (isset($row['update_date_of_due'], $row['date_of_due']) && $row['update_date_of_due'] && !empty($row['date_of_due'])) {
                        $item_id = (int)$row['item_id'];
                        $it = Item::find($item_id);
                        if ($it != null) {
                            $it->date_of_due = $row['date_of_due'];
                            $it->push();
                        }
                    }

                    if (array_key_exists('lots', $row)) {

                        foreach ($row['lots'] as $lot) {

                            $p_item->lots()->create([
                                'date' => $lot['date'],
                                'series' => $lot['series'],
                                'item_id' => $row['item_id'],
                                'warehouse_id' => $row['warehouse_id'],
                                'has_sale' => false,
                                'state' => $lot['state']
                            ]);
                        }
                    }

                    if (array_key_exists('item', $row)) {
                        Log::info('Item Lots group: ' . json_encode($row));
                        if (isset($row['item']['lots_enabled']) && ($row['item']['lots_enabled'] == true || $row['item']['lots_enabled'] == 'true')) {

                            // factor de lista de precios
                            $presentation_quantity = (isset($p_item->item->presentation->quantity_unit)) ? $p_item->item->presentation->quantity_unit : 1;

                            $validatLote = ItemLotsGroup::where('item_id', $row['item_id'])
                                ->where('code', $row['lot_code'])
                                ->where('warehouse_id', $row['warehouse_id'])
                                ->first();

                            Log::info('Item Lots group ya existe: ' . json_encode($validatLote));

                            if (isset($validatLote) && $validatLote != '') {
                                $validatLote->quantity = $validatLote->quantity + ($row['quantity'] * $presentation_quantity);
                                $validatLote->save();
                            } else {

                                $validatLote = ItemLotsGroup::where('item_id', $row['item_id'])
                                    ->where('code', $row['lot_code'])
                                    ->first();

                                $item_lots_group = ItemLotsGroup::create([
                                    'code' => $row['lot_code'],
                                    'quantity' => $row['quantity'] * $presentation_quantity,
                                    // 'quantity' => $row['quantity'],
                                    'date_of_due' => ($validatLote) ? $validatLote->date_of_due : $row['date_of_due'],
                                    'warehouse_id' => $row['warehouse_id'],
                                    'item_id' => $row['item_id']
                                ]);

                                $p_item->item_lot_group_id = $item_lots_group->id;
                                $p_item->update();
                            }
                        }
                    }
                }

                foreach ($data['payments'] as $payment) {

                    $record_payment = $doc->purchase_payments()->create($payment);
                    if (isset($payment['payment_destination_id'])) {
                        $this->createGlobalPayment($record_payment, $payment);
                    }

                    if ($payment['payment_method_type_id'] == '99') {

                        $reference = $payment['reference'];
                        $monto = floatval($payment['payment']);
                        $retention = Retention::find($reference);
                        $valor = $retention->total_used;
                        $montoUsado = $valor + $monto;
                        $retention->total_used = $montoUsado;
                        $retention->in_use = true;
                        $retention->save();
                    }
                }

                $this->savePurchaseFee($doc, $data['fee']);
                $this->setFilename($doc);
                $this->createPdf($doc, "a4", $doc->filename);

                if ((Company::active())->countable > 0) {
                    $this->createAccountingEntry($doc->id, $data['ret']);
                    $this->createAccountingEntryPayment($doc->id);
                }

                if ($data['document_type_id'] == '04') {
                    $this->createCreditNotePayment($doc);
                }

                return $doc;
            });

            Log::info('Compra creada: ' . json_encode($purchase));

            return [
                'success' => true,
                'data' => [
                    'id' => $purchase->id,
                    'number_full' => "{$purchase->series}-{$purchase->number}",
                ],
            ];
        } catch (Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    private function savePurchaseFee($purchase, $fee)
    {
        foreach ($fee as  $key => $row) {

            if (key_exists('currency_type_id', $row) == false) {
                $row['currency_type_id'] = $purchase->currency_type_id;
            }
            $row['number'] = $key + 1;
            $purchase->fee()->create($row);
            $purchase->date_of_due = $row['date'];
        }
    }

    //ACTUALIZAR FORMA DE PAGO TIPO NOTA DE CREDITO
    private function updateCreditNotePayment($note)
    {
        try {

            //Log::info("Creando nota de credito como forma de pago ".$note->id);
            $creditPayment = CreditNotesPayment::where('purchase_id', $note->id)->first();
            //Log::info(json_encode($creditPayment));
            $creditPayment->amount = $note->total;
            $creditPayment->user_id = $note->supplier_id;
            $creditPayment->save();
        } catch (Exception $ex) {

            Log::error("Error al actualizar la forma de pago de Nota de Crédito");
            Log::error($ex->getMessage());
            return $ex;
        }
    }

    //CREAMOS LA FORMA DE PAGO DE NOTAS DE CREDITO
    private function createCreditNotePayment($note)
    {
        try {

            //Log::info("Creando nota de credito como forma de pago");
            $creditPayment = new CreditNotesPayment();
            $creditPayment->purchase_id = $note->id;
            $creditPayment->amount = $note->total;
            $creditPayment->user_id = $note->supplier_id;
            $creditPayment->save();
        } catch (Exception $ex) {

            Log::error("Error al generar la forma de pago de Nota de Crédito");
            Log::error($ex->getMessage());
            return $ex;
        }
    }
    /* Crear los asientos contables del documento */
    private function createAccountingEntry($document_id, $ret)
    {
        $document = Purchase::find($document_id);
        $documentoInterno = $document->document_type2;
        $entry = (AccountingEntries::get())->last();
        $ivaArray = [];
        $rentaArray = [];

        if ($ret && count($ret) > 0) {

            foreach ($ret as $rett) {
                Log::info('RETENCIONES' . json_encode($rett));
                if ($rett['tipo'] == 'IVA') {
                    if (array_key_exists($rett['code'], $ivaArray)) {
                        $ivaArray[$rett['code']] += $rett['valor'];
                    } else {
                        $ivaArray[$rett['code']] = $rett['valor'];
                    }
                    //$iva += floatval($rett['valor']);

                }
                if ($rett['tipo'] == 'RENTA')
                    if (array_key_exists($rett['code'], $rentaArray)) {
                        $rentaArray[$rett['code']] += $rett['valor'];
                    } else {
                        $rentaArray[$rett['code']] = $rett['valor'];
                    }
                //$renta += floatval($rett['valor']);
            }
        }

        Log::info('RETENCIONES IVA' . json_encode($ivaArray));
        Log::info('RETENCIONES RENTA' . json_encode($rentaArray));
        /*
        if ($document->document_type_id != '01' && $document->document_type_id != '376' && ) {
            return;
        }
        */

        if ($document && $documentoInterno->accountant > 0) {
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

                $comment = $document->observation . ' | ' . $document->sequential_number . ' | Compra ' . substr($document->series, 0) . str_pad($document->number, '9', '0', STR_PAD_LEFT) . ' ' . $document->supplier->name;

                $total_debe = $document->total;
                $total_haber = $document->total;

                $cabeceraC = new AccountingEntries();
                $cabeceraC->user_id = auth()->user()->id;
                $cabeceraC->seat = $seat;
                $cabeceraC->seat_general = $seat_general;
                $cabeceraC->seat_date = $document->date_of_issue;
                $cabeceraC->types_accounting_entrie_id = 2;
                $cabeceraC->comment = $comment;
                $cabeceraC->serie = 'COMPRA';
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
                $cabeceraC->document_id = 'C' . $document_id;

                $cabeceraC->save();
                $cabeceraC->filename = 'ASC-' . $cabeceraC->id . '-' . date('Ymd');
                $cabeceraC->save();

                $customer = Person::find($cabeceraC->person_id);
                $importP = Imports::find($document->import_id);
                $importCTA = null;

                if ($importP && $importP->count() > 0 && $document->tipo_doc_id == 1) {
                    $importCTA = $importP->cuenta_contable;
                }

                if (isset($importCTA)) {

                    $accountMID = ($customer->account) ? $customer->account : $configuration->cta_suppliers;
                    $accountMIDModel = AccountMovement::find($accountMID);

                    $detalle2 = new AccountingEntryItems();
                    $detalle2->accounting_entrie_id = $cabeceraC->id;
                    $detalle2->account_movement_id = ($customer->account) ? $customer->account : $configuration->cta_suppliers;
                    $detalle2->seat_line = 1;
                    $detalle2->haber = $document->total;
                    $detalle2->debe = 0;
                    $costC = $document->establishment->cost_center[count($document->establishment->cost_center) - 1];
                    $detalle2->seat_cost = ($accountMIDModel && $accountMIDModel->cost_center > 0) ? $costC : null;

                    if ($detalle2->save() == false) {
                        $cabeceraC->delete();
                        return;
                        //abort(500,'No se pudo generar el asiento contable del documento');
                    }

                    $arrayEntrys = [];
                    $n = 1;

                    foreach ($document->items as $key => $value) {

                        $importCTAItem = $value->import;
                        $ctaImportItem = Imports::find($importCTAItem);
                        $itemCTA = "";

                        $warehouseItem = TenantWarehouse::find($value->warehouse_id);
                        $establishmentItem = Establishment::find($warehouseItem->establishment_id);

                        if ($ctaImportItem && $ctaImportItem->count() > 0 && $document->tipo_doc_id == 2) {
                            $itemCTA = $ctaImportItem->cuenta_contable;
                        }

                        $item = Item::find($value->item_id);
                        $impuesto = AffectationIgvType::find($item->purchase_affectation_igv_type_id);
                        //CONTABILIDAD PARA VALORES POSITIVOS
                        if ($documentoInterno->sign > 0) {

                            if ($importCTA) {

                                $accountantItem = AccountMovement::find($importCTA);
                                $valor = $establishmentItem->cost_center[count($establishmentItem->cost_center) - 1];
                                $seatCost = ($accountantItem && $accountantItem->cost_center > 0) ? $valor : 0;

                                if (array_key_exists($importCTA . '-' . $seatCost, $arrayEntrys) == true) {

                                    $arrayEntrys[$importCTA . '-' . $seatCost]['debe'] += floatval($value->total_value);
                                }
                                if (array_key_exists($importCTA . '-' . $seatCost, $arrayEntrys) == false) {
                                    $n += 1;

                                    Log::info('Tiene centro de costo: ' . $accountantItem->cost_center);
                                    Log::info('Centro de costo del establecimiento ' . $valor);

                                    $arrayEntrys[$importCTA . '-' . $seatCost] = [
                                        'account_movement_id' => $importCTA,
                                        'seat_line' => $n,
                                        'debe' => floatval($value->total_value),
                                        'haber' => 0,
                                        'seat_cost' => ($accountantItem && $accountantItem->cost_center > 0) ? $valor : null,
                                    ];
                                }
                            }

                            if ($impuesto->account) {

                                $accountantItem = AccountMovement::find($impuesto->account);
                                $valor = $establishmentItem->cost_center[count($establishmentItem->cost_center) - 1];
                                $seatCost = ($accountantItem && $accountantItem->cost_center > 0) ? $valor : 0;

                                if (array_key_exists($impuesto->account . '-' . $seatCost, $arrayEntrys)) {

                                    $arrayEntrys[$impuesto->account . '-' . $seatCost]['debe'] += floatval($value->total_taxes);
                                }
                                if (!array_key_exists($impuesto->account . '-' . $seatCost, $arrayEntrys)) {

                                    $n += 1;
                                    $arrayEntrys[$impuesto->account . '-' . $seatCost] = [
                                        'account_movement_id' => $impuesto->account,
                                        'seat_line' => $n,
                                        'debe' => floatval($value->total_taxes),
                                        'haber' => 0,
                                        'seat_cost' => ($accountantItem && $accountantItem->cost_center > 0) ? $valor : null,
                                    ];
                                }
                            }

                            if (!($impuesto->account) && $configuration->cta_taxes_purchases) {

                                $accountantItem = AccountMovement::find($configuration->cta_taxes_purchases);
                                $valor = $establishmentItem->cost_center[count($establishmentItem->cost_center) - 1];
                                $seatCost = ($accountantItem && $accountantItem->cost_center > 0) ? $valor : 0;

                                if (array_key_exists($configuration->cta_taxes_purchases . '-' . $seatCost, $arrayEntrys)) {

                                    $arrayEntrys[$configuration->cta_taxes_purchases . '-' . $seatCost]['debe'] += floatval($value->total_taxes);
                                }
                                if (!array_key_exists($configuration->cta_taxes_purchases . '-' . $seatCost, $arrayEntrys)) {

                                    $n += 1;
                                    $arrayEntrys[$configuration->cta_taxes_purchases . '-' . $seatCost] = [
                                        'account_movement_id' => $configuration->cta_taxes_purchases,
                                        'seat_line' => $n,
                                        'debe' => floatval($value->total_taxes),
                                        'haber' => 0,
                                        'seat_cost' => ($accountantItem && $accountantItem->cost_center > 0) ? $valor : null,

                                    ];
                                }
                            }
                        }
                    }

                    foreach ($arrayEntrys as $key => $value) {
                        Log::info('Asientos a crear: ' . json_encode($value));

                        if ($value['debe'] > 0 || $value['haber'] > 0) {

                            $detalle = new AccountingEntryItems();
                            $detalle->accounting_entrie_id = $cabeceraC->id;
                            $detalle->account_movement_id = $value['account_movement_id'];;
                            $detalle->seat_line = $value['seat_line'];
                            $detalle->debe = $value['debe'];
                            $detalle->haber = $value['haber'];
                            $detalle->seat_cost = $value['seat_cost'];
                            if ($detalle->save() == false) {
                                $cabeceraC->delete();
                                break;
                                //abort(500,'No se pudo generar el asiento contable del documento');
                            }
                        }
                    }

                    if (sizeof($ivaArray) > 0) {

                        foreach($ivaArray as $key => $iva){
                            $n += 1;
                            $retInterna = RetentionTypesPurchase::where('code',$key)->first();
                            $ivaCta = (isset($retInterna) && $retInterna->account_id)? $retInterna->account_id : $configuration->cta_iva_tax;
                            $seatCostIVA = AccountMovement::find($ivaCta);
                            $detalle = new AccountingEntryItems();
                            $detalle->accounting_entrie_id = $cabeceraC->id;
                            $detalle->account_movement_id = $ivaCta;
                            $detalle->seat_line = $n;
                            $detalle->debe = ($documentoInterno->sign > 0) ? 0 : floatval($iva);
                            $detalle->haber = ($documentoInterno->sign > 0) ? floatval($iva) : 0;
                            $detalle->seat_cost = ($seatCostIVA && $seatCostIVA->cost_center > 0) ? $document->establishment->cost_center[count($document->establishment->cost_center) - 1] : null;
                            if ($detalle->save() == false) {
                                $cabeceraC->delete();
                                return;
                                //abort(500,'No se pudo generar el asiento contable del documento');
                            }
                        }
                    }

                    if (sizeOf($rentaArray) > 0) {

                        foreach($rentaArray as $key => $renta){
                            $retInterna = RetentionTypesPurchase::where('code',$key)->first();
                            $rentaCta = (isset($retInterna) && $retInterna->account_id)? $retInterna->account_id : $configuration->cta_income_tax;
                            $n += 1;
                            $seatCostRENTA = AccountMovement::find($rentaCta);
                            $detalle = new AccountingEntryItems();
                            $detalle->accounting_entrie_id = $cabeceraC->id;
                            $detalle->account_movement_id = $rentaCta;
                            $detalle->seat_line = $n;
                            $detalle->debe = ($documentoInterno->sign > 0) ? 0 : floatval($renta);
                            $detalle->haber = ($documentoInterno->sign > 0) ? floatval($renta) : 0;
                            $detalle->seat_cost = ($seatCostRENTA && $seatCostRENTA->cost_center > 0) ? $document->establishment->cost_center[count($document->establishment->cost_center) - 1] : null;
                            if ($detalle->save() == false) {
                                $cabeceraC->delete();
                                return;
                                //abort(500,'No se pudo generar el asiento contable del documento');
                            }
                        }
                    }

                } else {

                    $accountMID = ($customer->account) ? $customer->account : $configuration->cta_suppliers;
                    $accountMIDModel = AccountMovement::find($accountMID);
                    $costC = $document->establishment->cost_center[count($document->establishment->cost_center) - 1];

                    $detalle = new AccountingEntryItems();
                    $detalle->accounting_entrie_id = $cabeceraC->id;
                    $detalle->account_movement_id = ($customer->account) ? $customer->account : $configuration->cta_suppliers;
                    $detalle->seat_line = 1;
                    $detalle->haber = ($documentoInterno->sign > 0) ? $document->total : 0;
                    $detalle->debe = ($documentoInterno->sign > 0) ? 0 : $document->total;
                    $detalle->seat_cost = ($accountMIDModel && $accountMIDModel->cost_center > 0) ? $costC : null;

                    if ($detalle->save() == false) {
                        $cabeceraC->delete();
                        return;
                        //abort(500,'No se pudo generar el asiento contable del documento');
                    }

                    $arrayEntrys = [];
                    $n = 1;

                    foreach ($document->items as $key => $value) {

                        $importCTAItem = $value->import;
                        $ctaImportItem = Imports::find($importCTAItem);
                        $itemCTA = "";
                        if ($ctaImportItem && $ctaImportItem->count() > 0 && $document->tipo_doc_id == 2) {
                            $itemCTA = $ctaImportItem->cuenta_contable;
                        }

                        $item = Item::find($value->item_id);
                        $impuesto = AffectationIgvType::find($item->purchase_affectation_igv_type_id);

                        $warehouseItem = TenantWarehouse::find($value->warehouse_id);
                        $establishmentItem = Establishment::find($warehouseItem->establishment_id);
                        $valor = $establishmentItem->cost_center[count($establishmentItem->cost_center) - 1];
                        $accountantItem = AccountMovement::find($item->purchase_cta);
                        $seatCost = ($accountantItem && $accountantItem->cost_center > 0) ? $valor : 0;

                        //CONTABILIDAD PARA VALORES POSITIVOS
                        if ($documentoInterno->sign > 0) {

                            if ($itemCTA == "" && $item->purchase_cta) {

                                $seatCostItem = AccountMovement::find($item->purchase_cta);

                                if (array_key_exists($item->purchase_cta . '-' . $seatCost, $arrayEntrys)) {

                                    $arrayEntrys[$item->purchase_cta . '-' . $seatCost]['debe'] += floatval($value->total_value);
                                }
                                if (!array_key_exists($item->purchase_cta . '-' . $seatCost, $arrayEntrys)) {
                                    $n += 1;

                                    $arrayEntrys[$item->purchase_cta . '-' . $seatCost] = [
                                        'account_movement_id' => $item->purchase_cta,
                                        'seat_line' => $n,
                                        'debe' => floatval($value->total_value),
                                        'haber' => 0,
                                        'seat_cost' => ($seatCost > 0) ? $seatCost : null,
                                    ];
                                }
                            }

                            $accountantItem = AccountMovement::find($item->purchase_cta);
                            $seatCost = ($accountantItem && $accountantItem->cost_center > 0) ? $valor : 0;

                            if ($itemCTA != "") {

                                if (array_key_exists($itemCTA . '-' . $seatCost, $arrayEntrys)) {

                                    $arrayEntrys[$itemCTA . '-' . $seatCost]['debe'] += floatval($value->total_value);
                                }
                                if (!array_key_exists($itemCTA . '-' . $seatCost, $arrayEntrys)) {
                                    $n += 1;

                                    $arrayEntrys[$itemCTA . '-' . $seatCost] = [
                                        'account_movement_id' => $itemCTA,
                                        'seat_line' => $n,
                                        'debe' => floatval($value->total_value),
                                        'haber' => 0,
                                        'seat_cost' => ($seatCost > 0) ? $seatCost : null,
                                    ];
                                }
                            }

                            $accountantItem = AccountMovement::find($configuration->cta_purchases);
                            $seatCost = ($accountantItem && $accountantItem->cost_center > 0) ? $valor : 0;

                            if ($itemCTA == "" && !($item->purchase_cta) && $configuration->cta_purchases) {

                                if (array_key_exists($configuration->cta_purchases . '-' . $seatCost, $arrayEntrys)) {

                                    $arrayEntrys[$configuration->cta_purchases . '-' . $seatCost]['debe'] += floatval($value->total_value);
                                }
                                if (!array_key_exists($configuration->cta_purchases . '-' . $seatCost, $arrayEntrys)) {
                                    $n += 1;

                                    $arrayEntrys[$configuration->cta_purchases . '-' . $seatCost] = [
                                        'account_movement_id' => $configuration->cta_purchases,
                                        'seat_line' => $n,
                                        'debe' => floatval($value->total_value),
                                        'haber' => 0,
                                        'seat_cost' => ($seatCost > 0) ? $seatCost : null,
                                    ];
                                }
                            }

                            if ($impuesto->account) {

                                if (array_key_exists($impuesto->account, $arrayEntrys)) {

                                    $arrayEntrys[$impuesto->account]['debe'] += floatval($value->total_taxes);
                                }
                                if (!array_key_exists($impuesto->account, $arrayEntrys)) {

                                    $n += 1;

                                    $arrayEntrys[$impuesto->account] = [
                                        'account_movement_id' => $impuesto->account,
                                        'seat_line' => $n,
                                        'debe' => floatval($value->total_taxes),
                                        'haber' => 0,
                                        'seat_cost' => null
                                    ];
                                }
                            }

                            if (!($impuesto->account) && $configuration->cta_taxes_purchases) {

                                if (array_key_exists($configuration->cta_taxes_purchases, $arrayEntrys)) {

                                    $arrayEntrys[$configuration->cta_taxes_purchases]['debe'] += floatval($value->total_taxes);
                                }
                                if (!array_key_exists($configuration->cta_taxes_purchases, $arrayEntrys)) {

                                    $n += 1;

                                    $arrayEntrys[$configuration->cta_taxes_purchases] = [
                                        'account_movement_id' => $configuration->cta_taxes_purchases,
                                        'seat_line' => $n,
                                        'debe' => floatval($value->total_taxes),
                                        'haber' => 0,
                                        'seat_cost' => null
                                    ];
                                }
                            }
                        }
                        //CONTABILIDAD PARA VALORES NEGATIVOS
                        if ($documentoInterno->sign < 1) {

                            $accountantItem = AccountMovement::find($itemCTA);
                            $seatCost = ($accountantItem && $accountantItem->cost_center > 0) ? $valor : 0;

                            if ($itemCTA != "" && !$item->purchase_cta) {

                                if (array_key_exists($itemCTA . '-' . $seatCost, $arrayEntrys)) {

                                    $arrayEntrys[$itemCTA . '-' . $seatCost]['haber'] += floatval($value->total_value);
                                }
                                if (!array_key_exists($itemCTA . '-' . $seatCost, $arrayEntrys)) {
                                    $n += 1;

                                    $arrayEntrys[$itemCTA . '-' . $seatCost] = [
                                        'account_movement_id' => $itemCTA,
                                        'seat_line' => $n,
                                        'debe' => 0,
                                        'haber' => floatval($value->total_value),
                                        'seat_cost' => ($seatCost > 0) ? $seatCost : null,
                                    ];
                                }
                            }

                            $accountantItem = AccountMovement::find($item->purchase_cta);
                            $seatCost = ($accountantItem && $accountantItem->cost_center > 0) ? $valor : 0;

                            if ($itemCTA == "" && $item->purchase_cta) {

                                if (array_key_exists($item->purchase_cta . '-' . $seatCost, $arrayEntrys)) {

                                    $arrayEntrys[$item->purchase_cta . '-' . $seatCost]['haber'] += floatval($value->total_value);
                                }
                                if (!array_key_exists($item->purchase_cta . '-' . $seatCost, $arrayEntrys)) {
                                    $n += 1;

                                    $arrayEntrys[$item->purchase_cta . '-' . $seatCost] = [
                                        'account_movement_id' => $item->purchase_cta,
                                        'seat_line' => $n,
                                        'debe' => 0,
                                        'haber' => floatval($value->total_value),
                                        'seat_cost' => ($seatCost > 0) ? $seatCost : null,
                                    ];
                                }
                            }

                            $accountantItem = AccountMovement::find($configuration->cta_purchases);
                            $seatCost = ($accountantItem && $accountantItem->cost_center > 0) ? $valor : 0;

                            if ($itemCTA == "" && !($item->purchase_cta) && $configuration->cta_purchases) {

                                if (array_key_exists($configuration->cta_purchases . '-' . $seatCost, $arrayEntrys)) {

                                    $arrayEntrys[$configuration->cta_purchases . '-' . $seatCost]['haber'] += floatval($value->total_value);
                                }
                                if (!array_key_exists($configuration->cta_purchases . '-' . $seatCost, $arrayEntrys)) {
                                    $n += 1;

                                    $arrayEntrys[$configuration->cta_purchases . '-' . $seatCost] = [
                                        'account_movement_id' => $configuration->cta_purchases,
                                        'seat_line' => $n,
                                        'debe' => 0,
                                        'haber' => floatval($value->total_value),
                                        'seat_cost' => ($seatCost > 0) ? $seatCost : null,
                                    ];
                                }
                            }

                            if ($impuesto->account) {

                                if (array_key_exists($impuesto->account, $arrayEntrys)) {

                                    $arrayEntrys[$impuesto->account]['haber'] += floatval($value->total_taxes);
                                }
                                if (!array_key_exists($impuesto->account, $arrayEntrys)) {

                                    $n += 1;

                                    $arrayEntrys[$impuesto->account] = [
                                        'account_movement_id' => $impuesto->account,
                                        'seat_line' => $n,
                                        'debe' => 0,
                                        'haber' => floatval($value->total_taxes),
                                        'seat_cost' => null,
                                    ];
                                }
                            }

                            if (!($impuesto->account) && $configuration->cta_taxes_purchases) {

                                if (array_key_exists($configuration->cta_taxes_purchases, $arrayEntrys)) {

                                    $arrayEntrys[$configuration->cta_taxes_purchases]['haber'] += floatval($value->total_taxes);
                                }
                                if (!array_key_exists($configuration->cta_taxes_purchases, $arrayEntrys)) {

                                    $n += 1;

                                    $arrayEntrys[$configuration->cta_taxes_purchases] = [
                                        'account_movement_id' => $configuration->cta_taxes_purchases,
                                        'seat_line' => $n,
                                        'debe' => 0,
                                        'haber' => floatval($value->total_taxes),
                                        'seat_cost' => null,
                                    ];
                                }
                            }
                        }
                    }

                    foreach ($arrayEntrys as $key => $value) {
                        if ($value['debe'] > 0 || $value['haber'] > 0) {

                            $detalle = new AccountingEntryItems();
                            $detalle->accounting_entrie_id = $cabeceraC->id;
                            $detalle->account_movement_id = $value['account_movement_id'];
                            $detalle->seat_line = $value['seat_line'];
                            $detalle->debe = $value['debe'];
                            $detalle->haber = $value['haber'];
                            $detalle->seat_cost = $value['seat_cost'];
                            if ($detalle->save() == false) {
                                $cabeceraC->delete();
                                break;
                                //abort(500,'No se pudo generar el asiento contable del documento');
                            }
                        }
                    }

                    if (sizeof($ivaArray) > 0) {

                        $n += 1;
                        foreach($ivaArray as $key => $iva){
                            $retInterna = RetentionTypesPurchase::where('code',$key)->first();
                            $detalle = new AccountingEntryItems();
                            $detalle->accounting_entrie_id = $cabeceraC->id;
                            $detalle->account_movement_id = ($retInterna && $retInterna->count() > 0 && isset($retInterna->account_id))?$retInterna->account_id : $configuration->cta_iva_tax;
                            $detalle->seat_line = $n;
                            $detalle->debe = ($documentoInterno->sign > 0) ? 0 : floatval($iva);
                            $detalle->haber = ($documentoInterno->sign > 0) ? floatval($iva) : 0;
                            if ($detalle->save() == false) {
                                $cabeceraC->delete();
                                return;
                                //abort(500,'No se pudo generar el asiento contable del documento');
                            }

                        }

                    }

                    if (sizeOf($rentaArray) > 0) {
                        $n += 1;
                        foreach($rentaArray as $key => $renta){
                            $retInterna = RetentionTypesPurchase::where('code',$key)->first();
                            $detalle = new AccountingEntryItems();
                            $detalle->accounting_entrie_id = $cabeceraC->id;
                            $detalle->account_movement_id = ($retInterna && $retInterna->count() > 0 && isset($retInterna->account_id))?$retInterna->account_id : $configuration->cta_income_tax;
                            $detalle->seat_line = $n;
                            $detalle->debe = ($documentoInterno->sign > 0) ? 0 : floatval($renta);
                            $detalle->haber = ($documentoInterno->sign > 0) ? floatval($renta) : 0;
                            if ($detalle->save() == false) {
                                $cabeceraC->delete();
                                return;
                                //abort(500,'No se pudo generar el asiento contable del documento');
                            }
                        }
                    }
                }
            } catch (Exception $ex) {

                Log::error('Error al intentar generar el asiento contable: ' . $ex->getMessage());
            }
        } else {
            Log::info('tipo de documento no genera asiento contable de momento');
        }
    }

    /* Crear los asientos contables de los pagos */
    private function createAccountingEntryPayment($document_id)
    {
        $document = Purchase::find($document_id);
        $entry = (AccountingEntries::get())->last();
        $documentoInterno = $document->document_type2;

        if ($document && $document->document_type_id == '01' && $documentoInterno->accountant > 0) {
            foreach ($document->payments as $payment) {
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

                    $comment = 'Compra ' . substr($document->series, 0) . str_pad($document->number, '9', '0', STR_PAD_LEFT) . ' | ' . $document->supplier->name;
                    $pay = PurchasePayment::find($payment->id);
                    $comment.= " | Pago ".$pay->sequential;

                    $total_debe = $payment->payment;
                    $total_haber = $payment->payment;

                    $cabeceraC = new AccountingEntries();
                    $cabeceraC->user_id = auth()->user()->id;
                    $cabeceraC->seat = $seat;
                    $cabeceraC->seat_general = $seat_general;
                    $cabeceraC->seat_date = $document->date_of_issue;
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
                    $detalle = new AccountingEntryItems();
                    $ceuntaC = PaymentMethodType::find($payment->payment_method_type_id);

                    $accountMId = ($customer->account) ? $customer->account : $configuration->cta_suppliers;
                    $accountMIDModel = AccountMovement::find($accountMId);

                    $detalle->accounting_entrie_id = $cabeceraC->id;
                    $detalle->account_movement_id = $accountMId;
                    $detalle->seat_line = 1;
                    $detalle->haber = 0;
                    $detalle->debe = $payment->payment;
                    $detalle->seat_cost = ($accountMIDModel && $accountMIDModel->cost_center > 0) ? array_pop($document->establishment->cost_center) : null;
                    if ($detalle->save() == false) {
                        $cabeceraC->delete();
                        return;
                        //abort(500,'No se pudo generar el asiento contable del documento');
                    }

                    $accountMId2 = ($ceuntaC && $ceuntaC->countable_acount_payment) ? $ceuntaC->countable_acount_payment : $configuration->cta_paymnets;
                    $accountMIDModel2 = AccountMovement::find($accountMId2);

                    $detalle2 = new AccountingEntryItems();
                    $detalle2->accounting_entrie_id = $cabeceraC->id;
                    $detalle2->account_movement_id = $accountMId2;
                    $detalle2->seat_line = 2;
                    $detalle2->haber = $payment->payment;
                    $detalle2->debe = 0;
                    $detalle->seat_cost = ($accountMIDModel2 && $accountMIDModel2->cost_center > 0) ? array_pop($document->establishment->cost_center) : null;
                    if ($detalle2->save() == false) {
                        $cabeceraC->delete();
                        return;
                        //abort(500,'No se pudo generar el asiento contable del documento');
                    }
                } catch (Exception $ex) {
                    Log::error('Error al intentar generar el asiento contable del pago de compra');
                    Log::error($ex->getMessage());
                }
            }
        } else {
            Log::info('tipo de documento no genera asiento contable de momento');
        }
    }

    public static function convert($inputs)
    {
        //Log::info(json_encode($inputs));
        $company = Company::active();
        $values = [
            'user_id' => auth()->id(),
            'external_id' => Str::uuid()->toString(),
            'supplier' => PersonInput::set($inputs['supplier_id']),
            'soap_type_id' => $company->soap_type_id,
            'group_id' => ($inputs->document_type_id === '01') ? '01' : '02',
            'state_type_id' => '01'
        ];

        $inputs->merge($values);

        return $inputs->all();
    }

    private function setFilename($purchase)
    {

        $name = [$purchase->series, $purchase->number, $purchase->id, date('Ymd')];
        $purchase->filename = join('-', $name);
        $purchase->save();
    }

    /*public static function deleteLotsSerie($records)
        {
            foreach ($records as $row) {

                $it = ItemLot::findOrFail($row->id);
                $it->delete();
            }
        }*/

    public function createPdf($purchase = null, $format_pdf = null, $filename = null)
    {

        ini_set("pcre.backtrack_limit", "5000000");
        $template = new Template();
        $pdf = new Mpdf();

        $document = ($purchase != null) ? $purchase : $this->purchase;
        $company = Company::active();
        $filename = ($filename != null) ? $filename : $this->purchase->filename;

        $base_template = Establishment::find($document->establishment_id)->template_pdf;

        Log::info('Purchase: ' . $document);
        //Log::info('FEE ID: '.$id);
        //$conect = DocumentPayment::where('document_id', $docs->id)->where('fee_id', $id)->get();
        //Log::info('createPdf1 DocumentPayment: '.json_encode($conect));

        //$i = $conect[$index];
        $account_entry = AccountingEntries::where('document_id', 'C' . $document->id)->first();
        Log::info('Account Entry - ' . $account_entry);
        //$user_log = auth()->user();

        $html = $template->pdf($base_template, "purchase", $company, $document, $format_pdf, $account_entry);


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

        if ($format_pdf != 'ticket') {
            if (config('tenant.pdf_template_footer')) {
                $html_footer = $template->pdfFooter($base_template, $document);
                $pdf->SetHTMLFooter($html_footer);
            }
        }

        $this->uploadFile($filename, $pdf->output('', 'S'), 'purchase');
    }

    public function uploadFile($filename, $file_content, $file_type)
    {
        $this->uploadStorage($filename, $file_content, $file_type);
    }

    public function toPrint($external_id, $format)
    {
        $purchase = Purchase::where('external_id', $external_id)->first();
        //Log::info('purchase - '.$purchase);

        if (!$purchase) throw new Exception("El código {$external_id} es inválido, no se encontro el pedido relacionado");

        $this->reloadPDF($purchase, $format, $purchase->filename);

        $temp = tempnam(sys_get_temp_dir(), 'purchase');
        file_put_contents($temp, $this->getStorage($purchase->filename, 'purchase'));

        return response()->file($temp, $this->generalPdfResponseFileHeaders($purchase->filename));
    }

    private function reloadPDF($purchase, $format, $filename)
    {
        $this->createPdf($purchase, $format, $filename);
    }

    public function update(PurchaseRequest $request)
    {
        try {
            $docIntern = PurchaseDocumentTypes2::where('idType', $request->document_type_intern)->get();
            $signo = ($docIntern && $docIntern[0]->sign == 0) ? -1 : 1;
            $purchase = DB::connection('tenant')->transaction(function () use ($request, $signo) {

                $doc = Purchase::firstOrNew(['id' => $request['id']]);
                $doc->fill($request->all());
                $doc->supplier = PersonInput::set($request['supplier_id']);
                $doc->group_id = ($request->document_type_id === '01') ? '01' : '02';
                $doc->user_id = auth()->id();
                $doc->save();

                if (count($request['ret']) > 0) {

                    $retenciones = RetentionsEC::where('idDocumento', $doc->id)->first();
                    $IdRetencionHistorico = ($retenciones && $retenciones->count() > 0) ? $retenciones->idRetencion : null;
                    if ($retenciones && $retenciones->count() > 0) {
                        $retenciones->delete();
                    }
                    /*
                    foreach ($retenciones as $ret) {
                        $ret->delete();
                    }
                    */
                    $serie = UserDefaultDocumentType::where('user_id', auth()->user()->id)->where('document_type_id','20')->first();

                    $tipoSerie = null;
                    $tiposerieText = '';
                    if (isset($serie)) {
                        $tipoSerie = Series::find($serie->series_id);
                        $tiposerieText = $tipoSerie->number;
                    } else {
                        $tipoSerie = Series::where('document_type_id', '20')->first();
                        $tiposerieText = $tipoSerie->number;
                    }

                    $establecimiento = Establishment::find($doc->establishment_id);
                    $secuelcialRet = RetentionsEC::where('establecimiento', $establecimiento->code)->where('ptoEmision', $tiposerieText)->orderBy('idRetencion','desc')->first();
                    $secuelcialRet = $secuelcialRet->idRetencion;
                    $secuelcialRet = substr($secuelcialRet,7);
                    $secuelcialRet = intVal($secuelcialRet);

                    $ret = new RetentionsEC();
                    $ret->idRetencion = ($IdRetencionHistorico) ? $IdRetencionHistorico : 'R' . $establecimiento->code . substr($tiposerieText, 1, 3) . str_pad($secuelcialRet + 1, 9, 0, STR_PAD_LEFT);
                    $ret->idDocumento = $doc->id;
                    $ret->fechaFizcal = $doc->date_of_issue->format('m/Y');
                    $ret->idProveedor = $doc->supplier_id;
                    $ret->establecimiento = $establecimiento->code;
                    $ret->ptoEmision = $tiposerieText;
                    $ret->secuencial = $doc->sequential_number;
                    $ret->codSustento = $doc->document_type_id;
                    $ret->codDocSustento = $doc->codSustento;
                    $ret->numAuthSustento = $doc->auth_number;
                    $ret->status_id = '01';
                    $ret->save();

                    foreach ($request['ret'] as $retDet) {
                        //Log::info(json_encode($retDet));
                        $detRet = new RetentionsDetailEC();
                        $detRet->idRetencion = $ret->idRetencion;
                        $detRet->codRetencion = $retDet['code'];
                        $detRet->baseRet = $retDet['base'];
                        $detRet->porcentajeRet = $retDet['porcentajeRet'];
                        $detRet->valorRet = $retDet['valor'];
                        $detRet->save();
                    }
                }

                foreach ($doc->items as $it) {

                    $p_i = PurchaseItem::findOrFail($it->id);
                    $p_i->delete();
                }

                foreach ($request['items'] as $row) {

                    $item = Item::where('id', $row['item_id'])->first();
                    if ($item->unit_type_id != 'ZZ') {
                        $costoA = $item->purchase_mean_cost;
                        $stockA = $item->stock;
                        $totalA = $costoA * $stockA;

                        $costoN = floatval($row['unit_value']);
                        $stockN = floatval($row['quantity']);
                        $totalN = $costoN * $stockN;

                        $stockT = $stockN + $stockA;
                        $costoT = $totalA + $totalN;
                        $costoT = round($costoT / $stockT, 4);
                        Log::info("ACTUAL " . $costoA . '-' . $totalA . ' NUEVO: ' . $costoN . "-" . $totalN);

                        $item->purchase_mean_cost = $costoT;
                        $item->save();
                    }
                    $p_item = new PurchaseItem();
                    $row['quantity'] = $row['quantity'] * $signo;
                    $p_item->fill($row);
                    $p_item->purchase_id = $doc->id;
                    $p_item->has_igv = true;
                    $p_item->save();

                    if (isset($row['update_purchase_price']) && $row['update_purchase_price']) {

                        Log::info("update_purchase_price_update" . json_encode($row));
                        Item::query()->where('id', $row['item_id'])
                            ->update(['purchase_unit_price' => round(floatval($row['unit_value']), 2), 'purchase_has_igv' => false]);
                        // actualizacion de precios
                    }
                    if (array_key_exists('lots', $row)) {

                        foreach ($row['lots'] as $lot) {

                            $p_item->lots()->create([
                                'date' => $lot['date'],
                                'series' => $lot['series'],
                                'item_id' => $row['item_id'],
                                'warehouse_id' => $row['warehouse_id'],
                                'has_sale' => false
                            ]);
                        }
                    }
                    if (array_key_exists('item', $row)) {
                        if (isset($row['item']['lots_enabled']) && $row['item']['lots_enabled'] == true) {
                            $this->processUpdateItemLotsGroup($row, $p_item);
                        }
                    }
                }

                $this->deleteAllPayments($doc->purchase_payments);

                $asientos = AccountingEntries::where('document_id', 'C' . $request['id'])->get();

                foreach ($asientos as $ass) {
                    $ass->delete();
                }

                foreach ($request['payments'] as $payment) {

                    //Log::info("purchase_payments",$payment);
                    $record_payment = $doc->purchase_payments()->create($payment);

                    if (isset($payment['payment_destination_id'])) {
                        $this->createGlobalPayment($record_payment, $payment);
                    }

                    if (isset($payment['payment_filename'])) {
                        $record_payment->payment_file()->create([
                            'filename' => $payment['payment_filename']
                        ]);
                    }

                    if ($payment['payment_method_type_id'] == '99') {

                        $reference = $payment['reference'];
                        $monto = floatval($payment['payment']);
                        $retention = Retention::find($reference);
                        $valor = $retention->total_used;
                        $montoUsado = $valor + $monto;
                        $retention->total_used = $montoUsado;
                        $retention->in_use = true;
                        $retention->save();
                    }


                    //$this->createAccountingEntryPayment($doc->id,$payment['payment']);
                }

                $doc->fee()->delete();
                $this->savePurchaseFee($doc, $request['fee']);

                if (!$doc->filename) {
                    $this->setFilename($doc);
                }

                $this->createPdf($doc, "a4", $doc->filename);

                if ((Company::active())->countable > 0) {
                    $this->createAccountingEntry($doc->id, $request['ret']);
                    $this->createAccountingEntryPayment($doc->id);
                }

                if ($doc->document_type_id == '04') {
                    $this->updateCreditNotePayment($doc);
                }

                return $doc;
            });
            return [
                'success' => true,
                'data' => [
                    'id' => $purchase->id,
                ],
            ];
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            return [
                'success' => false,
                'message' => $ex->getMessage()
            ];
        }
    }

    /**
     *
     * Crear lote
     *
     * @param  string $lot_code
     * @param  float $quantity
     * @param  string $date_of_due
     * @param  int $item_id
     * @return ItemLotsGroup
     */
    private function createItemLotsGroup($lot_code, $quantity, $date_of_due, $item_id, $warehouse_id)
    {
        $validatLote = ItemLotsGroup::where('item_id', $item_id)
            ->where('code', $lot_code)
            ->where('warehouse_id', $warehouse_id)
            ->first();

        if ($validatLote) {
            $validatLote->quantity += $quantity;
            $validatLote->save();
            return  $validatLote;
        } else {
            $validatLote = ItemLotsGroup::where('item_id', $item_id)
                ->where('code', $lot_code)
                ->first();

            return ItemLotsGroup::create([
                'code' => $lot_code,
                'quantity' => $quantity,
                'date_of_due' => ($validatLote) ? $validatLote->date_of_due : $date_of_due,
                'item_id' => $item_id,
                'warehouse_id' => $warehouse_id
            ]);
        }
    }


    /**
     *
     * Proceso para actualizar lotes en la compra
     *
     * @param  array $row
     * @param  PurchaseItem $purchase_item
     * @return void
     */
    private function processUpdateItemLotsGroup($row, PurchaseItem $purchase_item)
    {
        Log::info('processUpdateItemLotsGroup');
        $lot_code = $row['lot_code'] ?? null;
        $date_of_due = $row['date_of_due'] ?? null;

        // factor de lista de precios
        $presentation_quantity = (isset($purchase_item->item->presentation->quantity_unit)) ? $purchase_item->item->presentation->quantity_unit : 1;
        $quantity = $row['quantity'] * $presentation_quantity;

        if ($lot_code && $date_of_due) {
            $item_lots_group = $this->createItemLotsGroup($lot_code, $quantity, $date_of_due, $row['item_id'], $row['warehouse_id']);
            $purchase_item->item_lot_group_id = $item_lots_group->id;
            $purchase_item->update();
        } else {
            $data_item_lot_group = $row['data_item_lot_group'] ?? null;

            if ($data_item_lot_group) {
                $new_date_of_due = $data_item_lot_group['date_of_due'];
                $new_lot_code = $data_item_lot_group['lot_code'];

                $item_lots_group = $this->createItemLotsGroup($new_lot_code, $quantity, $new_date_of_due, $row['item_id'], $row['warehouse_id']);

                $purchase_item->lot_code = $new_lot_code;
                $purchase_item->date_of_due = $new_date_of_due;
                $purchase_item->item_lot_group_id = $item_lots_group->id;
                $purchase_item->update();
            }
        }
    }


    /**
     * @param Request $request
     *
     * @return array
     */
    public function uploadAttached(Request $request)
    {
        $paymentController = new PaymentFileController();
        return $paymentController->uploadAttached($request);
    }

    /**
     * Busca el archivo basado el el id de compra y el nombre del archivo
     *
     * @param Purchase $purchase
     * @param          $filename
     *
     * @return StreamedResponse
     * @throws Exception
     */
    public function downloadGuide(Purchase $purchase, $filename)
    {
        $guideFile = GuideFile::where([
            'purchase_id' => $purchase->id,
            'filename' => $filename
        ])->first();
        if (!empty($guideFile)) return $guideFile->download();

        throw new Exception("El registro no fue encontrado.");
    }

    /**
     * Se utiliza para consultar los datos de compra para guias. Si updateGuide existe
     * se utiliza para guardar los datos de guia.
     *
     * @param Request       $request
     * @param Purchase|null $purchase
     *
     * @return array
     */
    public function processGuides(Request $request, Purchase $purchase = null)
    {

        if ($request->has('updateGuide') && $request->has('guides')) {
            $guides = [];
            foreach ($request->guides as $guide) {
                if (!empty($guide['number'])) {
                    if (isset($guide['live'])) unset($guide['live']);
                    $guides[] = $guide;
                }
            }
            $purchase->setGuidesAttribute($guides);
            $purchase->push();
            $ids = [];
            foreach ($purchase->getGuides() as $guide) {
                /** @var stdClass $guide */
                if (property_exists($guide, 'filename')) {
                    $toSearch = [
                        'purchase_id' => $purchase->id,
                        'filename' => $guide->filename
                    ];
                    // Busca o crea los archivos de guia
                    $guideFile = GuideFile::where($toSearch)->first();
                    if ($guideFile == null) $guideFile = new GuideFile($toSearch);
                    $guideFile->push();
                    $ids[] = $guideFile->id;
                    $guideFile->saveFiles($guide->temp_path);
                }
            }
            // Borra las guias que no existan para la compra correspondiente
            GuideFile::wherenotin('id', $ids)->where('purchase_id', $purchase->id)->get()->transform(function ($item) {
                $item->delete();
            });
        }
        return $purchase->getCollectionData();
    }

    public function anular($id)
    {
        $obj = Purchase::find($id);
        $validated = self::verifyHasSaleItems($obj->items);
        if (!$validated['success']) {
            return [
                'success' => false,
                'message' => $validated['message']
            ];
        }

        DB::connection('tenant')->transaction(function () use ($obj) {


            foreach ($obj->items as $it) {
                $it->lots()->delete();
            }


            $obj->state_type_id = 11;
            $obj->save();

            foreach ($obj->items as $item) {
                $item_warehouse_id = $item->warehouse_id ?? $obj->establishment->getCurrentWarehouseId();

                $item->purchase->inventory_kardex()->create([
                    'date_of_issue' => date('Y-m-d'),
                    'item_id' => $item->item_id,
                    'warehouse_id' => $item_warehouse_id,
                    'quantity' => -$item->quantity,
                ]);

                $wr = ItemWarehouse::where([['item_id', $item->item_id], ['warehouse_id', $item_warehouse_id]])->first();
                $wr->stock = $wr->stock - $item->quantity;
                $wr->save();

                self::voidedItemLotsGroup($item);
            }
        });

        return [
            'success' => true,
            'message' => 'Compra anulada con éxito'
        ];
    }


    /**
     *
     * Anular lote ingresado por compra
     *
     * @param  PurchaseItem $purchase_item
     * @return void
     */
    public static function voidedItemLotsGroup($purchase_item)
    {
        $lots_enabled = $purchase_item->item->lots_enabled ?? false;

        if ($lots_enabled && $purchase_item->lot_code && $purchase_item->item_lot_group_id) {
            $lot_group = self::findItemLotsGroup($purchase_item);
            $lot_group->quantity = $lot_group->quantity - $purchase_item->quantity;
            $lot_group->update();
        }
    }


    public static function verifyHasSaleItems($items)
    {
        $validated = true;
        $message = '';
        foreach ($items as $element) {

            $lot_has_sale = collect($element->lots)->firstWhere('has_sale', 1);
            if ($lot_has_sale) {
                $validated = false;
                $message = 'No se puede anular esta compra, series en productos no disponibles';
                break;
            }
            $lot_enabled = false;
            if (is_array($element->item)) {
                if (in_array('lots_enabled', $element->item)) {
                    $lot_enabled = true;
                }
            } elseif (is_object($element->item)) {
                if (property_exists($element->item, 'lots_enabled')) {
                    $lot_enabled = true;
                }
            }
            if ($lot_enabled) {

                if ($element->item->lots_enabled && $element->lot_code) {
                    /*
                        $lot_group = ItemLotsGroup::where('code', $element->lot_code)->first();
                        */

                    $lot_group = self::findItemLotsGroup($element);

                    if (!$lot_group) {
                        $message = "Lote {$element->lot_code} no encontrado.";
                        $validated = false;
                        break;
                    }


                    /*
                    if ((int)$lot_group->quantity != (int)$element->quantity) {
                        $message = "Los productos del lote {$element->lot_code} han sido vendidos!";
                        $validated = false;
                        break;
                    }
                    */
                }
            }
        }

        return [
            'success' => $validated,
            'message' => $message
        ];
    }


    /**
     *
     * buscar lote por id o codigo
     *
     * @param  PurchaseItem $purchase_item
     * @return ItemLotsGroup
     */
    public static function findItemLotsGroup($purchase_item)
    {
        if (!is_null($purchase_item->item_lot_group_id)) {
            $lot_group = ItemLotsGroup::find($purchase_item->item_lot_group_id);
        } else {
            $lot_group = ItemLotsGroup::where('code', $purchase_item->lot_code)->first();
        }

        return $lot_group;
    }


    public function searchItemById($id)
    {


        $items = SearchItemController::getItemToPurchase(null, $id);
        $a = null;
        // Solo para que no entre en esta seccion
        if ($a !== null) {
            $items = SearchItemController::getNotServiceItemToPurchase(null, $id)->transform(function ($row) {
                /** @var Item $row */
                $full_description = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
                return [
                    'id' => $row->id,
                    'item_code' => $row->item_code,
                    'full_description' => $full_description,
                    'description' => $row->description,
                    'currency_type_id' => $row->currency_type_id,
                    'currency_type_symbol' => $row->currency_type->symbol,
                    'sale_unit_price' => $row->sale_unit_price,
                    'purchase_unit_price' => $row->purchase_unit_price,
                    'unit_type_id' => $row->unit_type_id,
                    'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                    'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                    'purchase_has_igv' => (bool)$row->purchase_has_igv,
                    'has_perception' => (bool)$row->has_perception,
                    'lots_enabled' => (bool)$row->lots_enabled,
                    'percentage_perception' => $row->percentage_perception,
                    'item_unit_types' => collect($row->item_unit_types)->transform(function ($row) {
                        return [
                            'id' => $row->id,
                            'description' => "{$row->description}",
                            'item_id' => $row->item_id,
                            'unit_type_id' => $row->unit_type_id,
                            'quantity_unit' => $row->quantity_unit,
                            'price1' => $row->price1,
                            'price2' => $row->price2,
                            'price3' => $row->price3,
                            'price_default' => $row->price_default,
                        ];
                    }),
                    'series_enabled' => (bool)$row->series_enabled,
                ];
            });
        }
        return compact('items');
    }

    public function searchItems(Request $request)
    {
        $items = SearchItemController::getItemToPurchase($request);
        // Solo para evitar que entre en esta seccion
        $a = null;
        if ($a != null) {
            $items = SearchItemController::getItemToPurchase($request)->transform(function ($row) {
                /** @var Item $row */
                $full_description = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
                $temp = array_merge($row->getCollectionData(), $row->getDataToItemModal());
                $data = [
                    'id' => $row->id,
                    'item_code' => $row->item_code,
                    'full_description' => $full_description,
                    'description' => $row->description,
                    'currency_type_id' => $row->currency_type_id,
                    'currency_type_symbol' => $row->currency_type->symbol,
                    'sale_unit_price' => $row->sale_unit_price,
                    'purchase_unit_price' => $row->purchase_unit_price,
                    'unit_type_id' => $row->unit_type_id,
                    'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                    'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                    'purchase_has_igv' => (bool)$row->purchase_has_igv,
                    'has_perception' => (bool)$row->has_perception,
                    'lots_enabled' => (bool)$row->lots_enabled,
                    'percentage_perception' => $row->percentage_perception,
                    'item_unit_types' => $row->item_unit_types->transform(function ($row) {
                        if (is_array($row)) return $row;
                        if (is_object($row)) {
                            /**@var ItemUnitType $row */
                            return $row->getCollectionData();
                        }
                        return $row;
                        return [
                            'id' => $row->id,
                            'description' => "{$row->description}",
                            'item_id' => $row->item_id,
                            'unit_type_id' => $row->unit_type_id,
                            'quantity_unit' => $row->quantity_unit,
                            'price1' => $row->price1,
                            'price2' => $row->price2,
                            'price3' => $row->price3,
                            'price_default' => $row->price_default,
                        ];
                    }),
                    'series_enabled' => (bool)$row->series_enabled,
                ];
                foreach ($temp as $k => $v) {
                    if (!isset($data[$k])) {
                        $data[$k] = $v;
                    }
                }
                return $data;
            });
        }
        return compact('items');
    }

    public function delete($id)
    {

        try {

            DB::connection('tenant')->transaction(function () use ($id) {

                $row = Purchase::findOrFail($id);
                $this->deleteAllPayments($row->purchase_payments);
                $row->delete();

                $asientos = AccountingEntries::where('document_id', 'C' . $id)->get();
                foreach ($asientos as $ass) {
                    $ass->delete();
                }
            });

            return [
                'success' => true,
                'message' => 'Compra eliminada con éxito'
            ];
        } catch (Exception $e) {

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function xml2array($xmlObject, $out = [])
    {
        foreach ((array)$xmlObject as $index => $node) {
            $out[$index] = (is_object($node)) ? $this->xml2array($node) : $node;
        }
        return $out;
    }

    public function XMLtoArray($xml)
    {
        $previous_value = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->loadXml($xml);
        libxml_use_internal_errors($previous_value);
        if (libxml_get_errors()) {
            return [];
        }
        return $this->DOMtoArray($dom);
    }

    public function DOMtoArray($root)
    {
        $result = [];

        if ($root->hasAttributes()) {
            $attrs = $root->attributes;
            foreach ($attrs as $attr) {
                $result['@attributes'][$attr->name] = $attr->value;
            }
        }

        if ($root->hasChildNodes()) {
            $children = $root->childNodes;
            if ($children->length == 1) {
                $child = $children->item(0);
                if (in_array($child->nodeType, [XML_TEXT_NODE, XML_CDATA_SECTION_NODE])) {
                    $result['_value'] = $child->nodeValue;
                    return count($result) == 1
                        ? $result['_value']
                        : $result;
                }
            }
            $groups = [];
            foreach ($children as $child) {
                if (!isset($result[$child->nodeName])) {
                    $result[$child->nodeName] = $this->DOMtoArray($child);
                } else {
                    if (!isset($groups[$child->nodeName])) {
                        $result[$child->nodeName] = [$result[$child->nodeName]];
                        $groups[$child->nodeName] = 1;
                    }
                    $result[$child->nodeName][] = $this->DOMtoArray($child);
                }
            }
        }
        return $result;
    }

    /*public function itemResource($id)
        {
            $establishment_id = auth()->user()->establishment_id;
            $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
            $row = Item::find($id);
            return [
                'id' => $row->id,
                'description' => $row->description,
                'lots' => $row->item_lots->where('has_sale', false)->where('warehouse_id', $warehouse->id)->transform(function($row) {
                    return [
                        'id' => $row->id,
                        'series' => $row->series,
                        'date' => $row->date,
                        'item_id' => $row->item_id,
                        'warehouse_id' => $row->warehouse_id,
                        'has_sale' => (bool)$row->has_sale,
                        'lot_code' => ($row->item_loteable_type) ? (isset($row->item_loteable->lot_code) ? $row->item_loteable->lot_code:null):null
                    ];
                })->values(),
                'series_enabled' => (bool) $row->series_enabled,
            ];
        }*/

    public function import(PurchaseImportRequest $request)
    {
        try {
            $model = $request->all();
            $supplier = Person::whereType('suppliers')->where('number', $model['supplier_ruc'])->first();

            if (!$supplier) {
                return [
                    'success' => false,
                    'data' => 'Proveedor no encontrado: ' . $model['supplier_ruc'],
                    'message' => 'Proveedor no encontrado: ' . $model['supplier_ruc'],
                ];
            }

            $model['supplier_id'] = $supplier->id;
            $formaPagoDefecto = PaymentMethodType::find($supplier->default_payment);
            $company = Company::active();

            $validar = Purchase::where('supplier_id', $supplier->id)->where('sequential_number', $model['sequential_number'])->get();
            if ($validar && $validar->count() > 0) {
                return [
                    'success' => false,
                    'message' => 'La factura ' . $model['sequential_number'] . ' de proveedor ' . $model['supplier_ruc'] . ' ya se encuentra registrada ',
                ];
            }

            $values = [
                'user_id' => auth()->id(),
                'external_id' => Str::uuid()->toString(),
                'supplier' => PersonInput::set($model['supplier_id']),
                'soap_type_id' => $company['soap_type_id'],
                'group_id' => ($model['document_type_id'] === '01') ? '01' : '02',
                'state_type_id' => '01'
            ];

            $numero = Purchase::where('establishment_id', $model['establishment_id'])->where('series', $model['series'])->count();
            $data = array_merge($model, $values);
            $data['number'] = $numero + 1;
            $indice = 0;

            foreach ($data['payments'] as $payment) {
                if ($formaPagoDefecto) {

                    $date = date_create($data['date_of_issue']);
                    $numberDays = ($formaPagoDefecto && $formaPagoDefecto->number_days >= 0) ? $formaPagoDefecto->number_days : 0;

                    $fecha = date_add($date, date_interval_create_from_date_string($numberDays . " days"));
                    $data['payments'][$indice]['payment_method_type_id'] = $supplier->default_payment;
                    $data['payment_method_type_id'] = $supplier->default_payment;
                    $data['payments'][$indice]['date_of_payment'] = date_format($fecha, "Y-m-d");
                    $data['payment_condition_id'] = ($formaPagoDefecto->is_cash) ? '01' : '02';
                }
                $indice += 1;
            }

            foreach ($data['items'] as $item) {

                $data['total_igv'] += $item['total_igv'];
            }

            $purchase = DB::connection('tenant')->transaction(function () use ($data) {
                Log::info('Data Compra XML');
                Log::info($data);

                try {
                    $doc = new Purchase();
                    $doc->fill($data);
                    $doc->save();

                    foreach ($data['items'] as $row) {
                        log::info("Purchase item to create : " . json_encode($row));
                        $row['has_igv'] = true;
                        if (isset($row['total_base_igv']) == false) {
                            $row['total_base_igv'] = 0;
                        }
                        if (isset($row['total_igv']) == false) {
                            $row['total_igv'] = 0;
                        }
                        $doc->items()->create($row);
                    }

                    foreach ($data['payments'] as $row) {
                        $doc->purchase_payments()->create($row);
                    }

                    return $doc;
                } catch (Exception $ex) {
                    $doc->delete();
                    Log::error('Error al tratar de crear Compra desde iimportacion: '.$ex->getMessage());
                    return false;
                }
            });

            if ($purchase) {

                if ((Company::active())->countable > 0) {
                    $this->createAccountingEntry($purchase->id, null);
                    $this->createAccountingEntryPayment($purchase->id);
                }
                return [
                    'success' => true,
                    'message' => 'Xml cargado correctamente.',
                    'data' => [
                        'id' => $purchase->id,
                    ],
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Xml No cargado correctamente.',
                ];
            }
        } catch (Exception $e) {
            Log::error("Error al generar Purchase Import: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function destroy_purchase_item($id)
    {

        DB::connection('tenant')->transaction(function () use ($id) {

            $item = PurchaseItem::findOrFail($id);
            $item->delete();
        });

        return [
            'success' => true,
            'message' => 'Item eliminado'
        ];
    }

    public function download($external_id, $format = 'a4')
    {
        $purchase = SaleOpportunity::where('external_id', $external_id)->first();

        if (!$purchase) throw new Exception("El código {$external_id} es inválido, no se encontro el archivo relacionado");

        return $this->downloadStorage($purchase->filename, 'purchase');
    }


    public function searchPurchaseOrder(Request $request)
    {
        // $input = (string)$request->input;
        $purchases = Purchase::select('purchase_order_id')->wherenotnull('purchase_order_id')
            ->get()
            ->pluck('purchase_order_id');
        $purchaseOrder = PurchaseOrder::whereNotIn('id', $purchases)
            // ->where('prefix','like','%'.$input.'%')
            ->get()
            ->transform(function (PurchaseOrder $row) {
                $data = [
                    'id' => $row->id,
                    'description' => $row->getNumberFullAttribute(),
                ];
                return $data;
            });
        return $purchaseOrder;
    }

    public function validateSecuencial($supplierId, $secuencial)
    {
        $existente = Purchase::where('supplier_id', $supplierId)->where('sequential_number', $secuencial)->get();

        return compact('existente');
    }
}

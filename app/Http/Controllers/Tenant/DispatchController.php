<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Facturalo;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\DispatchRequest;
use App\Http\Resources\Tenant\DispatchCollection;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\Country;
use App\Models\Tenant\Catalogs\Department;
use App\Models\Tenant\Catalogs\District;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use App\Models\Tenant\Catalogs\Province;
use App\Models\Tenant\Catalogs\TransferReasonType;
use App\Models\Tenant\Catalogs\TransportModeType;
use App\Models\Tenant\Catalogs\UnitType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\DispatchItem;
use App\Models\Tenant\Document;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Person;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\Series;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Document\Traits\SearchTrait;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Inventory\Models\Warehouse as ModuleWarehouse;
use Modules\Order\Http\Resources\DispatchResource;
use Modules\Order\Mail\DispatchEmail;
use Modules\Order\Models\Dispatcher;
use Modules\Order\Models\Driver;
use Modules\Order\Models\OrderNote;
use App\Models\Tenant\PaymentCondition;
use App\Models\Tenant\Catalogs\RelatedDocumentType;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Models\InventoryTransfer;
use Swift_Mailer;
use Swift_SmtpTransport;

/**
 * Class DispatchController
 *
 * @package App\Http\Controllers\Tenant
 * @mixin Controller
 */
class DispatchController extends Controller
{
    use FinanceTrait;
    use SearchTrait;
    use StorageDocument;

    public function __construct()
    {
        $this->middleware('input.request:dispatch,web', ['only' => ['store']]);
    }

    public function index()
    {
        $configuration = Configuration::getPublicConfig();
        return view('tenant.dispatches.index', compact('configuration'));
    }

    public function columns()
    {
        return [
            'number' => 'Número'
        ];
    }

    public function records(Request $request)
    {
        $records = $this->getRecords($request);

        return new DispatchCollection($records->paginate(config('tenant.items_per_page')));
    }


    public function getRecords($request)
    {

        $d_end = $request->d_end;
        $d_start = $request->d_start;
        $number = $request->number;
        $series = $request->series;
        $customer_id = $request->customer_id;


        if ($d_start && $d_end) {
            $records = Dispatch::where('series', 'like', '%' . $series . '%')->whereBetween('date_of_issue', [$d_start, $d_end]);
        } else {
            $records = Dispatch::where('series', 'like', '%' . $series . '%');
        }

        if ($number) {
            $records = $records->where('number', $number);
        }

        if ($customer_id) {
            $records = $records->where('customer_id', $customer_id);
        }

        return $records->latest();
    }


    public function data_table()
    {
        $customers = Person::whereType('customers')->orderBy('name')->take(20)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->number . ' - ' . $row->name,
                'name' => $row->name,
                'number' => $row->number,
                'identity_document_type_id' => $row->identity_document_type_id,
            ];
        });

        $series = Series::where('document_type_id', '09')->get();

        return compact('customers', 'series');
    }


    public function create($document_id = null, $type = null, $dispatch_id = null)
    {

        if ($type == 'q') {
            $document = Quotation::find($document_id);
        } elseif ($type == 'on') {
            $document = OrderNote::find($document_id);
        } elseif ($type == 't') {
            $document = InventoryTransfer::find($document_id);
        } elseif ($type == 'i') {
            $type = 'i';
            $document = Document::find($document_id);
        } elseif (isset($document_id) && isset($type) == false) {
            $type = 'i';
            $document = Document::find($document_id);
        } else {
            $type = null;
            $document = null;
        }

        if (!$document && !$dispatch_id) {
            return view('tenant.dispatches.create');
        }

        $configuration = Configuration::query()->first();
        $items = [];
        $dispatch = Dispatch::find($dispatch_id);
        if (isset($document)) {
            Log::info('Log dispatch - '.json_encode($document));

            if ($type != 't') {
                foreach ($document->items as $item) {
                    $name_product_pdf = ($configuration->show_pdf_name) ? strip_tags($item->name_product_pdf) : null;
                    $items[] = [
                        'item_id' => $item->item_id,
                        'item' => $item,
                        'quantity' => $item->quantity,
                        'description' => $item->item->description,
                        'name' => ($item->item->name != null) ? $item->item->name : ' ',
                        'name_product_pdf' => $name_product_pdf
                    ];
                }
            } else {
                $origin = [];
                $delivery = [];
                $document->establishment_id = $document->warehouse_id;
                $document->establishment = $document->warehouse->establishment;
                //$document->date_of_issue = $document->created_at;
                $document->customer_id = $document->client_id;//warehouse_destination->establishment->customer_associate_id;
                $document->transfer_reason_type_id = '04';
                $document->transfer_reason_description = $document->description;
                $document->customer =  $document->warehouse_destination->establishment->associated;
                $document->reference_transfer_id = $document_id;
                $origin['location_id'] = [$document->warehouse->establishment->department_id, $document->warehouse->establishment->province_id, $document->warehouse->establishment->district_id];
                $origin['address'] = $document->warehouse->establishment->address;
                $origin['country_id'] = $document->warehouse->establishment->country_id;
                $document->origin = $origin;

                $delivery['country_id'] = $document->warehouse_destination->establishment->country_id;
                $delivery['location_id'] = [$document->warehouse_destination->establishment->department_id, $document->warehouse_destination->establishment->province_id, $document->warehouse_destination->establishment->district_id];
                $delivery['address'] = $document->warehouse_destination->establishment->address;
                $document->delivery = $delivery;

                foreach ($document->inventories as $item) {
                    $name_product_pdf = ($configuration->show_pdf_name) ? strip_tags($item->item->name_product_pdf) : null;
                    $items[] = [
                        'item_id' => $item->item_id,
                        'item' => $item->item,
                        'quantity' => $item->quantity,
                        'description' => $item->item->description,
                        'name' => $item->item->name,
                        'name_product_pdf' => $name_product_pdf
                    ];
                }
            }
        } elseif (isset($dispatch)) {
            foreach ($dispatch->items as $item) {
                $name_product_pdf = ($configuration->show_pdf_name) ? strip_tags($item->name_product_pdf) : null;
                $items[] = [
                    'item_id' => $item->item_id,
                    'item' => $item,
                    'quantity' => $item->quantity,
                    'description' => $item->item->description,
                    'name' => $item->item->name,
                    'name_product_pdf' => $name_product_pdf
                ];
            }
        }

        Log::info(json_encode($items));
        return view('tenant.dispatches.form', compact('document', 'items', 'type', 'dispatch'));
    }

    public function generate($sale_note_id)
    {
        $sale_note = SaleNote::findOrFail($sale_note_id);
        $type = null;
        $document = $sale_note;
        $dispatch = null;
        $configuration = Configuration::query()->first();
        $items = [];
        foreach ($document->items as $item) {
            $name_product_pdf = ($configuration->show_pdf_name) ? strip_tags($item->name_product_pdf) : null;
            $items[] = [
                'item_id' => $item->item_id,
                'item' => $item,
                'quantity' => $item->quantity,
                'description' => $item->item->description,
                'name_product_pdf' => $name_product_pdf
            ];
        }
        //dd($sale_note_id);
        return view('tenant.dispatches.form', compact('document', 'type', 'dispatch', 'items'));
    }

    public function sendDispatchToSunat(Dispatch $document)
    {

        $data = [
            'sent'        => false,
            'code'        => null,
            'description' => "El elemento ya fue enviado",
        ];
        if (!$document->wasSend()) {
            $facturalo = $document->getFacturalo();

            $facturalo
                ->setActions(['send_xml_signed' => true])
                ->loadXmlSigned()
                ->senderXmlSignedBill();
            $data = $facturalo->getResponse();
        }

        return json_encode($data);
    }

    public function store(DispatchRequest $request)
    {
        try {
            $configuration = Configuration::first();

            if ($request->series[0] == 'T') {
                /** @var Facturalo $fact */
                $fact = DB::connection('tenant')->transaction(function () use ($request, $configuration) {
                    $facturalo = new Facturalo();
                    $facturalo->save($request->all());
                    $facturalo->createXmlUnsigned();
                    $facturalo->signXmlUnsigned();
                    $facturalo->createPdf();
                    if ($configuration->isAutoSendDispatchsToSunat()) {
                        $facturalo->senderXmlSignedBill();
                    }
                    return $facturalo;
                });

                $document = $fact->getDocument();
                // $response = $fact->getResponse();
            } else {
                /** @var Facturalo $fact */
                $fact = DB::connection('tenant')->transaction(function () use ($request) {
                    $facturalo = new Facturalo();
                    $facturalo->save($request->all());
                    $facturalo->createPdf();

                    return $facturalo;
                });

                $document = $fact->getDocument();
                // $response = $fact->getResponse();
            }

            if (!empty($document->reference_document_id) && $configuration->getUpdateDocumentOnDispaches()) {
                $reference = Document::find($document->reference_document_id);
                if (!empty($reference)) {
                    $reference->updatePdfs();
                }
            }

            return [
                'success' => true,
                'message' => ($request->id) ? ("Se actualizo la guía de remisión {$document->series}-{$document->number}") : ("Se creo la guía de remisión {$document->series}-{$document->number}"),
                'data'    => [
                    'id' => $document->id,
                ],
            ];

        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return [
                'success' => false,
                'message' => $ex->getMessage(),
            ];
        }
    }

    /**
     * Tables
     *
     * @param  Request $request
     *
     * @return array
     */
    public function tables(Request $request)
    {
        $itemsFromSummary = null;
        if ($request->itemIds) {
            $itemsFromSummary = Item::query()
                ->with('lots_group')
                ->whereIn('id', $request->itemIds)
                ->where('item_type_id', '01')
                ->orderBy('description')
                ->get()
                ->transform(function ($row) {
                    $full_description = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;

                    return [
                        'id'                               => $row->id,
                        'full_description'                 => $full_description,
                        'description'                      => $row->description,
                        'model'                            => $row->model,
                        'internal_id'                      => $row->internal_id,
                        'currency_type_id'                 => $row->currency_type_id,
                        'currency_type_symbol'             => $row->currency_type->symbol,
                        'sale_unit_price'                  => $row->sale_unit_price,
                        'purchase_unit_price'              => $row->purchase_unit_price,
                        'unit_type_id'                     => $row->unit_type_id,
                        'sale_affectation_igv_type_id'     => $row->sale_affectation_igv_type_id,
                        'attributes'                       => $row->attributes ? $row->attributes : [],
                        'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                        'has_igv'                          => $row->has_igv,
                        'lots_group' => $row->lots_group->each(function ($lot) {
                            return [
                                'id'  => $lot->id,
                                'code' => $lot->code,
                                'quantity' => $lot->quantity,
                                'date_of_due' => $lot->date_of_due,
                                'checked'  => false
                            ];
                        }),
                        'lots' => [],
                        'lots_enabled' => (bool) $row->lots_enabled,
                    ];
                });
        }
        $items = Item::query()
            ->with('lots_group')
            ->where('item_type_id', '01')
            ->orderBy('description')
            ->take(20)
            ->get()
            ->transform(function ($row) {
                $full_description = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;

                return [
                    'id'                               => $row->id,
                    'full_description'                 => $full_description,
                    'description'                      => $row->description,
                    'model'                            => $row->model,
                    'internal_id'                      => $row->internal_id,
                    'currency_type_id'                 => $row->currency_type_id,
                    'currency_type_symbol'             => $row->currency_type->symbol,
                    'sale_unit_price'                  => $row->sale_unit_price,
                    'purchase_unit_price'              => $row->purchase_unit_price,
                    'unit_type_id'                     => $row->unit_type_id,
                    'sale_affectation_igv_type_id'     => $row->sale_affectation_igv_type_id,
                    'attributes'                       => $row->attributes ? $row->attributes : [],
                    'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                    'has_igv'                          => $row->has_igv,
                    'lots_group' => $row->lots_group->each(function ($lot) {
                        return [
                            'id'  => $lot->id,
                            'code' => $lot->code,
                            'quantity' => $lot->quantity,
                            'date_of_due' => $lot->date_of_due,
                            'checked'  => false
                        ];
                    }),
                    'lots' => [],
                    'lots_enabled' => (bool) $row->lots_enabled,
                    'warehouses' => $row->getDataWarehouses(),
                ];
            });

        $identities = ['6', '4', '1'];

        // $dni_filter = config('tenant.document_type_03_filter');
        // if($dni_filter){
        //     array_push($identities, '1');
        // }

        $customers = Person::with('addresses')
            ->whereIn('identity_document_type_id', $identities)
            ->whereType('customers')
            ->orderBy('name')
            ->whereIsEnabled()
            ->get()
            ->transform(function ($row) {
                return [
                    'id'                          => $row->id,
                    'description'                 => $row->number . ' - ' . $row->name,
                    'name'                        => $row->name,
                    'trade_name'                  => $row->trade_name,
                    'country_id'                  => $row->country_id,
                    'address'                     => $row->address,
                    'addresses'                   => $row->addresses,
                    'email'                       => $row->email,
                    'telephone'                   => $row->telephone,
                    'number'                      => $row->number,
                    'district_id'                 => $row->district_id,
                    'department_id'               => $row->department_id,
                    'province_id'                 => $row->province_id,
                    'identity_document_type_id'   => $row->identity_document_type_id,
                    'identity_document_type_code' => $row->identity_document_type->code
                ];
            });

        $locations = [];
        $departments = Department::whereActive()->get();
        /** @var Department $department */
        /** @var Province $province */
        /** @var District $district */
        foreach ($departments as $department) {
            $children_provinces = [];
            foreach ($department->provinces as $province) {
                $children_districts = [];
                foreach ($province->districts as $district) {
                    $children_districts[] = [
                        'value' => $district->id,
                        'label' => $district->id . " - " . $district->description
                    ];
                }
                $children_provinces[] = [
                    'value'    => $province->id,
                    'label'    => $province->description,
                    'children' => $children_districts
                ];
            }
            $locations[] = [
                'value'    => $department->id,
                'label'    => $department->description,
                'children' => $children_provinces
            ];
        }

        $identityDocumentTypes = IdentityDocumentType::whereActive()->get();
        $transferReasonTypes = TransferReasonType::whereActive()->get();
        $transportModeTypes = TransportModeType::whereActive()->get();
        $unitTypes = UnitType::whereActive()->get()->toArray();
        $countries = Country::whereActive()->get()->toArray();
        $establishments = Establishment::all();
        $series = Series::all()->toArray();
        $company = Company::select('number')->first();
        $drivers = Driver::all();
        $dispachers = Dispatcher::all();
        $related_document_types = RelatedDocumentType::get();

        // ya se tiene un locations con lo siguiente combinado
        // $departments = Department::whereActive()->get();
        // $provinces = Province::whereActive()->get();
        // $districts = District::whereActive()->get();

        return compact(
            'establishments',
            'customers',
            'series',
            'transportModeTypes',
            'transferReasonTypes',
            'unitTypes',
            'countries',
            // 'departments',
            // 'provinces',
            // 'districts',
            'identityDocumentTypes',
            'items',
            'locations',
            'company',
            'drivers',
            'dispachers',
            'related_document_types',
            'itemsFromSummary'
        );
    }

    public function downloadExternal($type, $external_id)
    {
        $retention = Dispatch::where('external_id', $external_id)->first();

        if (!$retention) {
            throw new Exception("El código {$external_id} es inválido, no se encontro documento relacionado");
        }

        switch ($type) {
            case 'pdf':
                $folder = 'pdf';
                break;
            case 'xml':
                $folder = 'signed';
                break;
            case 'cdr':
                $folder = 'cdr';
                break;
            default:
                throw new Exception('Tipo de archivo a descargar es inválido');
        }

        return $this->downloadStorage($retention->filename, $folder);
    }

    public function record($id)
    {
        $record = new DispatchResource(Dispatch::findOrFail($id));

        return $record;
    }

    public function email(Request $request)
    {
        $record = Dispatch::find($request->input('id'));
        $customer_email = $request->input('customer_email');
        $email = $customer_email;
        $mailable = new DispatchEmail($record);
        $id =  $request->input('id');
        $model = __FILE__ . ";;" . __LINE__;
        //$sendIt = EmailController::SendMail($email, $mailable, $id, 4);
        Configuration::setConfigSmtpMail();
        $backup = Mail::getSwiftMailer();
        $transport =  new Swift_SmtpTransport(Config::get('mail.host'), Config::get('mail.port'), Config::get('mail.encryption'));
        $transport->setUsername(Config::get('mail.username'));
        $transport->setPassword(Config::get('mail.password'));
        $mailer = new Swift_Mailer($transport);
        Mail::setSwiftMailer($mailer);
        Mail::to($email)->send($mailable);

        return [
            'success' => true
        ];
    }

    public function generateDocumentTables($id)
    {
        $dispatch = Dispatch::findOrFail($id);
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $establishment_id = $establishment->id;
        $warehouse = ModuleWarehouse::where('establishment_id', $establishment_id)->first();
        $relation_external_document = $dispatch->getRelationExternalDocument();
        $set_unit_price_dispatch_related_record = Configuration::getUnitPriceDispatchRelatedRecord();

        $itemsId = $dispatch->items->pluck('item_id')->all();

        $items = Item::whereIn('id', $itemsId)->get()->transform(function ($row) use ($warehouse, $dispatch, $relation_external_document, $set_unit_price_dispatch_related_record) {

            $detail = $this->getFullDescription($row, $warehouse);

            $sale_unit_price = $this->getDispatchSaleUnitPrice($row, $dispatch, $relation_external_document, $set_unit_price_dispatch_related_record);

            return [
                'id'                               => $row->id,
                'full_description'                 => $detail['full_description'],
                'model'                            => $row->model,
                'brand'                            => $detail['brand'],
                'category'                         => $detail['category'],
                'stock'                            => $detail['stock'],
                'internal_id'                      => $row->internal_id,
                'description'                      => $row->description,
                'currency_type_id'                 => $row->currency_type_id,
                'currency_type_symbol'             => $row->currency_type->symbol,
                'sale_unit_price'                  => number_format($sale_unit_price, 4, '.', ''),
                // 'sale_unit_price'                  => number_format($row->sale_unit_price, 4, '.', ''),
                'purchase_unit_price'              => $row->purchase_unit_price,
                'unit_type_id'                     => $row->unit_type_id,
                'sale_affectation_igv_type_id'     => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'calculate_quantity'               => (bool) $row->calculate_quantity,
                'has_igv'                          => (bool) $row->has_igv,
                'has_plastic_bag_taxes'            => (bool) $row->has_plastic_bag_taxes,
                'amount_plastic_bag_taxes'         => $row->amount_plastic_bag_taxes,
                'item_unit_types'                  => collect($row->item_unit_types)->transform(function ($row) {
                    return [
                        'id'            => $row->id,
                        'description'   => "{$row->description}",
                        'item_id'       => $row->item_id,
                        'unit_type_id'  => $row->unit_type_id,
                        'quantity_unit' => $row->quantity_unit,
                        'price1'        => $row->price1,
                        'price2'        => $row->price2,
                        'price3'        => $row->price3,
                        'price_default' => $row->price_default,
                    ];
                }),
                'warehouses' => collect($row->warehouses)->transform(function ($row) use ($warehouse) {
                    return [
                        'warehouse_description' => $row->warehouse->description,
                        'stock'                 => $row->stock,
                        'warehouse_id'          => $row->warehouse_id,
                        'checked'               => ($row->warehouse_id == $warehouse->id) ? true : false,
                    ];
                }),
                'attributes' => $row->attributes ? $row->attributes : [],
                'lots_group' => collect($row->lots_group)->transform(function ($row) {
                    return [
                        'id'          => $row->id,
                        'code'        => $row->code,
                        'quantity'    => $row->quantity,
                        'date_of_due' => $row->date_of_due,
                        'checked'     => false
                    ];
                }),
                'lots'           => [],
                'lots_enabled'   => (bool) $row->lots_enabled,
                'series_enabled' => (bool) $row->series_enabled,
            ];
        });

        $series = Series::where('establishment_id', $establishment->id)->get();
        $document_types_invoice = DocumentType::whereIn('id', ['01', '03'])->get();
        // $document_types_invoice = DocumentType::whereIn('id', ['01', '03', '80'])->get();
        $payment_method_types = PaymentMethodType::all();
        $payment_destinations = $this->getPaymentDestinations();
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $payment_conditions = PaymentCondition::get();

        return response()->json([
            'dispatch'               => $dispatch,
            'document_types_invoice' => $document_types_invoice,
            'establishments'         => $establishment,
            'payment_destinations'   => $payment_destinations,
            'series'                 => $series,
            'success'                => true,
            'payment_method_types'   => $payment_method_types,
            'items'                  => $items,
            'affectation_igv_types' => $affectation_igv_types,
            'payment_conditions' => $payment_conditions,
        ], 200);
    }


    /**
     * Obtener precio unitario desde registro relacionado a la guia - convertir guia a cpe
     *
     * @param  Item $item
     * @param  Dispatch $dispatch
     * @param  mixed $relation_external_document
     * @param  bool $set_unit_price_dispatch_related_record
     * @return float
     */
    public function getDispatchSaleUnitPrice($item, $dispatch, $relation_external_document, $set_unit_price_dispatch_related_record)
    {

        if ($dispatch->isGeneratedFromExternalDocument($relation_external_document) && $set_unit_price_dispatch_related_record) {
            $exist_item = $relation_external_document->items->where('item_id', $item->id)->first();

            if ($exist_item) return $exist_item->unit_price;
        }

        return $item->sale_unit_price;
    }


    public function setDocumentId($id)
    {
        request()->validate(['document_id' => 'required|exists:tenant.documents,id']);
        DB::connection('tenant')->beginTransaction();
        try {
            Dispatch::where('id', $id)
                ->update([
                    'reference_document_id' => request('document_id')
                ]);

            $dispatch = Dispatch::findOrFail($id);
            $facturalo = new Facturalo();
            $facturalo->createPdf($dispatch, 'dispatch', 'a4');

            DB::connection('tenant')->commit();
            return response()->json([
                'success' => true,
                'message' => 'Información actualiza'
            ], 200);
        } catch (\Throwable $th) {
            DB::connection('tenant')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al asociar la guía con el comprobante. Detalles: ' . $th->getMessage()
            ], 500);
        }
    }

    public function dispatchesByClient($clientId)
    {
        $records = Dispatch::without([
            'user', 'soap_type', 'state_type', 'document_type', 'unit_type', 'transport_mode_type',
            'transfer_reason_type', 'items', 'reference_document'
        ])
            ->select('series', 'number', 'id', 'date_of_issue', 'soap_shipping_response')
            ->where('customer_id', $clientId)
            ->whereNull('reference_document_id')
            ->whereStateTypeAccepted()
            ->orderBy('series')
            ->orderBy('number', 'desc')
            ->take(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $records,
        ], 200);
    }

    public function getItemsFromDispatches(Request $request)
    {
        $request->validate([
            'dispatches_id' => 'required|array',
        ]);

        $items = DispatchItem::whereIn('dispatch_id', $request->dispatches_id)
            ->select('item_id', 'quantity')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items,
        ], 200);
    }

    /**
     * Devuelve un conjuto de tipo de documento 9 y 31 para Guías
     *
     * @return DocumentType[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    public function getDocumentTypeToDispatches()
    {
        $doc_type = ['09', '31'];
        $document_types_guide = DocumentType::whereIn('id', $doc_type)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'active' => (bool) $row->active,
                'short' => $row->short,
                'description' => ucfirst(mb_strtolower(str_replace('REMITENTE ELECTRÓNICA', 'REMITENTE', $row->description))),
            ];
        });

        return $document_types_guide;
    }
}

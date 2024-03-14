<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Requests\Inputs\Common\EstablishmentInput;
use App\CoreFacturalo\Requests\Inputs\Common\PersonInput;
use App\Models\Tenant\TypesAccountingEntries;
use App\Http\Requests\Tenant\AccountEntriesRequest;
use App\Http\Resources\Tenant\AccountingEntriesCollection;
use App\Models\Tenant\Item;
use App\Models\Tenant\AccountingEntries;
use App\Models\Tenant\AccountMovement;
use App\Models\Tenant\Person;
use App\Models\Tenant\Warehouse;
use App\Traits\OfflineTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Finance\Traits\FinanceTrait;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use App\Models\Tenant\Establishment;
use App\CoreFacturalo\Template;
use App\Http\Resources\Tenant\AccountingEntriesResource;
use App\Models\Tenant\Catalogs\CurrencyType;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class AccountingEntriesController extends Controller
{
    use FinanceTrait;
    use OfflineTrait;
    use StorageDocument;

    protected $quotation;
    protected $account_entry;
    protected $company;


    public function index()
    {
        $company = Company::select('soap_type_id')->first();
        $soap_company = $company->soap_type_id;
        $generate_order_note_from_quotation = Configuration::getRecordIndividualColumn('generate_order_note_from_quotation');

        return view('tenant.accounting-entries.index', compact('soap_company', 'generate_order_note_from_quotation'));
    }

    public function create()
    {
        return view('tenant.accounting-entries.form');
    }

    public function edit($id)
    {
        $resourceId = $id;
        return view('tenant.accounting-entries.form_edit', compact('resourceId'));
    }

    public function columns()
    {
        return [
            'user_id' => 'Cliente',
            //'date_of_issue' => 'Fecha de emisión',
            //'delivery_date' => 'Fecha de entrega',
            //'user_name' => 'Registrado por',
            //'seller_name' => 'Vendedor',
            //'referential_information' => 'Inf.Referencial',
            //'number' => 'Número',
        ];
    }


    public function records(Request $request)
    {
        $records = $this->getRecords($request);
        return new AccountingEntriesCollection($records->paginate(config('tenant.items_per_page')));
    }

    private function getRecords($request)
    {
        $accountid = $request->input('account_movement_id');
        $typeid = $request->input('types_accounting_entrie_id');
        $date_start = $request->input('date_start');
        $date_end = $request->input('date_end');
        $cost = $request->input('cost');
        $search = $request->input('search');

        $query = AccountingEntries::query();

        if (!is_null($search)) {
            $query->where('comment', 'like', "%{$search}%")->orWhere('filename', 'like', "%{$search}%");
        }

        if (!is_null($typeid) && $typeid>0) {
            $query->where('types_accounting_entrie_id', '=', $typeid);
        }

        if (!is_null($accountid) && $accountid>0) {
            $query->whereHas('items', function ($q) use ($accountid) {
                $q->where('account_movement_id','=',$accountid);
            });
        };

        if (!is_null($cost) ) {
            $query->whereHas('items', function ($q) use ($cost) {
                $q->where('cost', 'like', "%{$cost}%");
            });

        }
            $records = $query->latest();
            if ($date_start && $date_end) {
                $records = $records->whereBetween('seat_date', [$date_start, $date_end]);
            }
        return $records;
    }

    public function searchCustomers(Request $request)
    {

        $customers = Person::whereType('customers')
            ->orderBy('name')
            ->whereIsEnabled();
        if ($request->has('customer_id')) {
            $customers->where('id', $request->customer_id)->whereType('customers');
        } else {
            $customers->where('number', 'like', "%{$request->input}%")
                ->orWhere('name', 'like', "%{$request->input}%")->whereType('customers');
        }
        $customers = $customers->get()->transform(function ($row) {
            /** @var Person $row */
            return $row->getCollectionData();
            /* Se ha movido al modelo */
            return [
                'id' => $row->id,
                'description' => $row->number . ' - ' . $row->name,
                'name' => $row->name,
                'number' => $row->number,
                'identity_document_type_id' => $row->identity_document_type_id,
                'identity_document_type_code' => $row->identity_document_type->code,
                'addresses' => $row->addresses,
                'address' => $row->address,
            ];
        });
        return compact('customers');
    }

    public function searchSuppliers(Request $request)
    {
        $suppliers = Person::whereType('suppliers')
            ->orderBy('name')
            ->whereIsEnabled();
        if ($request->has('customer_id')) {
            $suppliers->where('id', $request->customer_id)->whereType('suppliers');
        } else {
            $suppliers->where('number', 'like', "%{$request->input}%")
                ->orWhere('name', 'like', "%{$request->input}%")->whereType('suppliers');
        }
        $suppliers = $suppliers->get()->transform(function ($row) {
            /** @var Person $row */
            return $row->getCollectionData();
            /* Se ha movido al modelo */
            return [
                'id' => $row->id,
                'description' => $row->number . ' - ' . $row->name,
                'name' => $row->name,
                'number' => $row->number,
                'identity_document_type_id' => $row->identity_document_type_id,
                'identity_document_type_code' => $row->identity_document_type->code,
                'addresses' => $row->addresses,
                'address' => $row->address,
            ];
        });
        return compact('suppliers');
    }


    public function tables()
    {
        $idauth = auth()->user()->id;
        $lista = AccountingEntries::where('user_id', '=', $idauth)->latest('id')->first();
        if (empty($lista)) {
            $seat = 1;
            $seat_general = 1;
        } else {
            $genSeat = AccountingEntries::select('seat_general')->latest('id')->first();
            $seat = $lista->seat + 1;
            $seat_general = $genSeat->seat_general + 1;
        }

        $user = [
            'id' => auth()->user()->id,
            'seat' => $seat,
            'seat_general' => $seat_general,
        ];
        $customers = $this->table('customers');
        $suppliers = $this->table('suppliers');
        $types_seat = TypesAccountingEntries::select('id', 'name')->get();
        $establishments = Establishment::where('id', auth()->user()->establishment_id)->get();
        $currency_types = CurrencyType::whereActive()->get();
        $company = Company::active();
        $configuration = Configuration::select('destination_sale')->first();
        return compact('configuration', 'user', 'types_seat', 'customers', 'suppliers', 'establishments', 'currency_types', 'company');
    }


    public function item_tables()
    {
        $account_movement = AccountMovement::with('account_group')->get();
        $account_movement2 = AccountMovement::select('id', 'description')->get();
        $types_seat = TypesAccountingEntries::select('id', 'name')->get();
        return compact(
            'account_movement',
            'account_movement2',
            'types_seat'
        );
    }

    public function record($id)
    {
        $record = new AccountingEntriesResource(AccountingEntries::findOrFail($id));

        return $record;
    }



    public function store(AccountEntriesRequest $request)
    {

        DB::connection('tenant')->transaction(function () use ($request) {

            $data = $this->mergeData($request);

            $this->account_entry = AccountingEntries::create($data);

            foreach ($data['items'] as $row) {
                $this->account_entry->items()->create($row);
            }

            $this->setFilename();
            $this->createPdf($this->account_entry, "a4", $this->account_entry->filename);

        });

        return [
            'success' => true,
            'data' => [
                'id' => $this->account_entry->id,
                'number_full' => $this->account_entry->number_full,
            ],
        ];
    }


    private function setFilename()
    {

        $name = [$this->account_entry->prefix, $this->account_entry->id, date('Ymd')];
        $this->account_entry->filename = join('-', $name);
        $this->account_entry->save();

    }


    public function toPrint($external_id, $format)
    {
        $account = AccountingEntries::where('external_id', $external_id)->first();

        if (!$account) throw new Exception("El código {$external_id} es inválido, no se encontro el asiento contable relacionado");

        $this->reloadPDF($account, $format, $account->filename);
        $temp = tempnam(sys_get_temp_dir(), 'account_entry');

        file_put_contents($temp, $this->getStorage($account->filename, 'account_entry'));


        return response()->file($temp, $this->generalPdfResponseFileHeaders($account->filename));
    }

    public function update(AccountEntriesRequest $request)
    {

        DB::connection('tenant')->transaction(function () use ($request) {
            $this->account_entry = AccountingEntries::firstOrNew(['id' => $request->id]);
            $this->account_entry->fill($request->all());
            $this->account_entry->person = PersonInput::set($request['person_id'], isset($request['customer_address_id']) ? $request['customer_address_id'] : null);
            $this->account_entry->save();
            $this->account_entry->items()->delete();
            foreach ($request['items'] as $row) {
                $this->account_entry->items()->create($row);
            }
        });

        return [
            'success' => true,
            'data' => [
                'id' => $this->account_entry->id,
            ],
        ];
    }

    public function mergeData($inputs)
    {
        $this->company = Company::active();
        $idauth = auth()->user()->id;
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
        $values = [
            'external_id' => Str::uuid()->toString(),
            'person' => PersonInput::set($inputs['person_id'], isset($inputs['customer_address_id']) ? $inputs['customer_address_id'] : null),
            'establishment' => EstablishmentInput::set($inputs['establishment_id']),
            'seat' => $seat,
            'seat_general' => $seat_general
        ];

        $inputs->merge($values);

        return $inputs->all();
    }



    public function table($table)
    {
        switch ($table) {
            case 'customers':

                $customers = Person::whereType('customers')->whereIsEnabled()->orderBy('name')->take(20)->get()->transform(function ($row) {
                    /** @var Person $row */
                    return $row->getCollectionData();
                    /** Se ha movido al modelo */
                    return [
                        'id' => $row->id,
                        'description' => $row->number . ' - ' . $row->name,
                        'name' => $row->name,
                        'number' => $row->number,
                        'identity_document_type_id' => $row->identity_document_type_id,
                        'identity_document_type_code' => $row->identity_document_type->code,
                        'addresses' => $row->addresses,
                        'address' => $row->address
                    ];
                });
                return $customers;

                break;

            case 'suppliers':

                $suppliers = Person::whereType('suppliers')->orderBy('name')->get()->transform(function ($row) {
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

                $warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();

                $items = Item::orderBy('description')->whereIsActive()
                    // ->with(['warehouses' => function($query) use($warehouse){
                    //     return $query->where('warehouse_id', $warehouse->id);
                    // }])
                    ->take(20)->get();

                $this->ReturnItem($items);

                return $items;

                break;
            default:
                return [];

                break;
        }
    }


    /**
     * Realiza la busqueda de producto en cotizacion.
     * @param Request $request
     * @return array
     */
    /*public function searchItems(Request $request)
    {
        $items = SearchItemController::getItemsToQuotation($request);
        return compact('items');

    }*/


    /*  public function searchItemById($id)
    {

        $items = SearchItemController::getItemsToQuotation(null, $id);
        return compact('items');

    }
*/

    public function searchCustomerById($id)
    {
        return $this->searchClientById($id);
    }



    /*public function changed($id)
    {
        $record = Quotation::find($id);
        $record->changed = true;
        $record->save();

        return [
            'success' => true
        ];
    }*/

    /*  public function updateStateType($state_type_id, $id)
    {
        $record = Quotation::find($id);
        $record->state_type_id = $state_type_id;
        $record->save();

        return [
            'success' => true,
            'message' => 'Estado actualizado correctamente'
        ];
    }*/


    public function destroy($id)
    {

        try {

            $person_type = AccountingEntries::find( $id);
            $person_type_type = 'Asiento Contable';
            $person_type->items()->delete();
            $person_type->delete();

            return [
                'success' => true,
                'message' => $person_type_type . ' eliminado con éxito'
            ];
        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false, 'message' => "El {$person_type_type} esta siendo usado por otros registros, no puede eliminar"] : ['success' => false, 'message' => "Error inesperado, no se pudo eliminar el {$person_type_type}"];
        }
    }
    private function reloadPDF($quotation, $format, $filename)
    {
        $this->createPdf($quotation, $format, $filename);
    }

    public function createPdf($quotation = null, $format_pdf = null, $filename = null)
    {
        ini_set("pcre.backtrack_limit", "5000000");
        $template = new Template();
        $pdf = new Mpdf();

        $document = ($quotation != null) ? $quotation : $this->account_entry;
        $company = ($this->company != null) ? $this->company : Company::active();
        $filename = ($filename != null) ? $filename : $this->account_entry->filename;

        $configuration = Configuration::first();

        $base_template = Establishment::find(1)->template_pdf;
        //$base_template = Establishment::find($document->establishment_id)->template_pdf;

        $html = $template->pdf($base_template, "account_entry", $company, $document, $format_pdf);

        if ($format_pdf === 'ticket' or $format_pdf === 'ticket_80') {

            $width = 78;
            if (config('tenant.enabled_template_ticket_80')) $width = 76;

            $company_name = (strlen($company->name) / 20) * 10;
            $company_address = (strlen($document->establishment->address) / 30) * 10;
            $company_number = $document->establishment->telephone != '' ? '10' : '0';
            $customer_name = strlen($document->person->name) > '25' ? '10' : '0';
            $customer_address = (strlen($document->person->address) / 200) * 10;
            $p_order = $document->purchase_order != '' ? '10' : '0';

            $total_exportation = $document->total_exportation != '' ? '10' : '0';
            $total_free = $document->total_free != '' ? '10' : '0';
            $total_unaffected = $document->total_unaffected != '' ? '10' : '0';
            $total_exonerated = $document->total_exonerated != '' ? '10' : '0';
            $total_taxed = $document->total_taxed != '' ? '10' : '0';
            $quantity_rows = count($document->items);
            $payments = $document->payments()->count() * 5;
            $discount_global = 0;
            $terms_condition = $document->terms_condition ? 15 : 0;
            $contact = $document->contact ? 15 : 0;

            $document_description = ($document->description) ? count(explode("\n", $document->description)) * 3 : 0;


            foreach ($document->items as $it) {
                if ($it->discounts) {
                    $discount_global = $discount_global + 1;
                }
            }
            $legends = $document->legends != '' ? '10' : '0';

            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    $width,
                    120 +
                        ($quantity_rows * 8) +
                        ($discount_global * 3) +
                        $company_name +
                        $company_address +
                        $company_number +
                        $customer_name +
                        $customer_address +
                        $p_order +
                        $legends +
                        $total_exportation +
                        $total_free +
                        $total_unaffected +
                        $payments +
                        $total_exonerated +
                        $terms_condition +
                        $contact +
                        $document_description +
                        $total_taxed
                ],
                'margin_top' => 0,
                'margin_right' => 5,
                'margin_bottom' => 0,
                'margin_left' => 5
            ]);
        } else if ($format_pdf === 'a5') {

            $company_name = (strlen($company->name) / 20) * 10;
            $company_address = (strlen($document->establishment->address) / 30) * 10;
            $company_number = $document->establishment->telephone != '' ? '10' : '0';
            $customer_name = strlen($document->customer->name) > '25' ? '10' : '0';
            $customer_address = (strlen($document->customer->address) / 200) * 10;
            $p_order = $document->purchase_order != '' ? '10' : '0';

            $total_exportation = $document->total_exportation != '' ? '10' : '0';
            $total_free = $document->total_free != '' ? '10' : '0';
            $total_unaffected = $document->total_unaffected != '' ? '10' : '0';
            $total_exonerated = $document->total_exonerated != '' ? '10' : '0';
            $total_taxed = $document->total_taxed != '' ? '10' : '0';
            $quantity_rows = count($document->items);
            $discount_global = 0;
            foreach ($document->items as $it) {
                if ($it->discounts) {
                    $discount_global = $discount_global + 1;
                }
            }
            $legends = $document->legends != '' ? '10' : '0';


            $alto=0;

          /*  $alto = ($quantity_rows * 8) +
                ($discount_global * 3) +
                $company_name +
                $company_address +
                $company_number +
                $customer_name +
                $customer_address +
                $p_order +
                $legends +
                $total_exportation +
                $total_free +
                $total_unaffected +
                $total_exonerated +
                $total_taxed;*/
            $diferencia = 148 - (float)$alto;

            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    210,
                    $diferencia + $alto
                ],
                'margin_top' => 0,
                'margin_right' => 5,
                'margin_bottom' => 0,
                'margin_left' => 5
            ]);
        } else {


            $pdf_font_regular = config('tenant.pdf_name_regular');
            $pdf_font_bold = config('tenant.pdf_name_bold');

            if ($pdf_font_regular != false) {
                $defaultConfig = (new ConfigVariables())->getDefaults();
                $fontDirs = $defaultConfig['fontDir'];

                $defaultFontConfig = (new FontVariables())->getDefaults();
                $fontData = $defaultFontConfig['fontdata'];

                $default = [
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
                ];

                if ($base_template == 'citec') {
                    $default = [
                        'mode' => 'utf-8',
                        'margin_top' => 0,
                        'margin_right' => 0,
                        'margin_bottom' => 0,
                        'margin_left' => 0,
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
                    ];
                }

                $pdf = new Mpdf($default);
                $pdf->setAutoTopMargin = false;
            }
        }

        $path_css = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
            DIRECTORY_SEPARATOR . 'pdf' .
            DIRECTORY_SEPARATOR . $base_template .
            DIRECTORY_SEPARATOR . 'style.css');

        $stylesheet = file_get_contents($path_css);

        $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);

        if ($format_pdf != 'ticket') {
            if (config('tenant.pdf_template_footer')) {

                $html_footer = $template->pdfFooter($base_template, $this->account_entry);
                $html_footer_term_condition = ($document->terms_condition) ? $template->pdfFooterTermCondition($base_template, $document) : "";

                $html_footer_legend = "";
                if ($configuration->legend_footer) {
                    $html_footer_legend = $template->pdfFooterLegend($base_template, $this->account_entry);
                }

                $pdf->setAutoBottomMargin = 'stretch';
                $pdf->setAutoTopMargin = 'stretch';


                $pdf->SetHTMLFooter($html_footer_term_condition . $html_footer . $html_footer_legend);
            }
        }
        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        $this->uploadFile($filename, $pdf->output('', 'S'), 'account_entry');
    }

    public function uploadFile($filename, $file_content, $file_type)
    {
        $this->uploadStorage($filename, $file_content, $file_type);
    }

    public function download($externalId,$type){

        $account = AccountingEntries::where('external_id', $externalId)->first();

        if (!$account) throw new Exception("El código {$externalId} es inválido, no se encontro el asiento contable relacionado");

        $this->reloadPDF($account, $type, $account->filename);
        $temp = tempnam(sys_get_temp_dir(), 'account_entry');

        file_put_contents($temp, $this->getStorage($account->filename, 'account_entry'));


        return response()->download($temp, $account->filename.'.pdf');

    }
}

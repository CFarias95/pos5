<?php
namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\RetentionRequest;
use App\Http\Resources\Tenant\RetentionCollection;
use App\Http\Resources\Tenant\RetentionResource;
use App\Models\Tenant\Catalogs\Code;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Series;
use App\Models\Tenant\Retention;
use App\Models\Tenant\Supplier;
use Exception;
use Illuminate\Http\Request;
use App\Models\Tenant\Catalogs\RetentionType;
use App\Models\Tenant\Person;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\DocumentType;
use Illuminate\Support\Facades\DB;
use App\CoreFacturalo\Facturalo;
use App\CoreFacturalo\WS\Services\AuthSri;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use DOMDocument;
use Illuminate\Support\Facades\Log;

class RetentionController extends Controller
{
    use StorageDocument;
    private $config;
    private $company;

    public function __construct() {
        $this->middleware('input.request:retention,web', ['only' => ['store']]);
        $this->config = Configuration::first();
        $this->company = Company::first();
    }

    public function index()
    {
        return view('tenant.retentions.index');
    }

    public function columns()
    {
        return [
            'number' => 'Número'
        ];
    }

    public function records(Request $request)
    {
        $records = Retention::where($request->column, 'like', "%{$request->value}%")
                            // ->orderBy('series')
                            // ->orderBy('number', 'desc');
                            ->latest();

        return new RetentionCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function create()
    {
        return view('tenant.retentions.form');
    }

    public function tables()
    {
        $establishments = Establishment::where('id', auth()->user()->establishment_id)->get();// Establishment::all();
        $retention_types = RetentionType::get();
        $suppliers = $this->table('suppliers');
        $series = Series::all();

        return compact('establishments', 'retention_types', 'suppliers', 'series');
    }

    public function document_tables()
    {
        $retention_types = RetentionType::get();
        $currency_types = CurrencyType::whereActive()->get();
        $document_types = DocumentType::whereIn('id', ['01', '03'])->get();

        return compact('document_types', 'currency_types', 'retention_types');
    }

    public function table($table)
    {
        if ($table === 'suppliers') {

            $suppliers = Person::whereType('suppliers')->where('identity_document_type_id', '6')->orderBy('name')->get()->transform(function($row) {
                return [
                    'id' => $row->id,
                    'description' => $row->number.' - '.$row->name,
                    'name' => $row->name,
                    'number' => $row->number,
                    'identity_document_type_id' => $row->identity_document_type_id,
                    'identity_document_type_code' => $row->identity_document_type->code
                ];
            });
            return $suppliers;
        }

        return [];
    }

    public function record($id)
    {
        $record = new RetentionResource(Retention::findOrFail($id));

        return $record;
    }


    public function store(RetentionRequest $request)
    {

        $fact = DB::connection('tenant')->transaction(function () use($request) {
            $facturalo = new Facturalo();
            $facturalo->save($request->all());
            $facturalo->createXmlUnsigned();
            $facturalo->signXmlUnsigned();
            $facturalo->createPdf();
            $facturalo->senderXmlSignedBill();

            return $facturalo;
        });

        $document = $fact->getDocument();
        $response = $fact->getResponse();

        return [
            'success' => true,
            'message' => "Se generó la retención {$document->series}-{$document->number}",
            'data' => [
                'id' => $document->id,
                'response' =>$response

            ],
        ];
    }

    public function downloadExternal($type, $external_id)
    {
        $retention = Retention::where('external_id', $external_id)->first();
        if(!$retention) {
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

    public function import(Request $request){

        try{

            $data = $request['data'];
            $dataArray = explode("\n",$data);

            if(count($dataArray) > 1){
                $message = 'Retenciones procesadas \n';
                foreach($dataArray as $ret){

                    $ret = explode("\t",$ret);

                    if($ret['1'] != "SERIE_COMPROBANTE"){

                        $message .= '\n'.$ret['10'];
                        $claveAcceso = $ret['10'];
                        $rucProveedor = $ret['2'];

                        $url = 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';

                        if($this->company->soap_type_id == '01'){

                            $url = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';
                        }

                        $get = new AuthSri();
                        $documento = $get->send($url,$claveAcceso);

                        $comporbante = $documento['RespuestaAutorizacionComprobante']['numeroComprobantes'];
                        if($comporbante > 0){

                            $retencion = $documento['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['comprobante'];
                            $xml = new DOMDocument();
                            $xml->loadXML($retencion);


                        }else{

                            Log::error("No se encontro la retencion : ".$ret['10']);

                        }
                    }
                }
                return [
                    'success' => true,
                    'message' => $message
                ];


            }else{
                return [
                    'success' => false,
                    'message' => "EL archivo TXT no tiene retenciones para procesar"
                ];
            }

        }catch(Exception $e){
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}

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
use Illuminate\Support\Str;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date;

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

            $totalDocumentos = 0;
            $totalProcesados = 0;
            $totalError = 0;

            if(count($dataArray) > 1){

                $message = 'Retenciones procesadas';

                foreach($dataArray as $ret){

                    $totalDocumentos += 1;

                    try{

                        $ret = explode("\t",$ret);

                        if($ret['1'] != "SERIE_COMPROBANTE"){

                            if(substr($ret['10'],8,2) != '07' ){
                                return;
                            }
                            //$message .= '\n'.$ret['10'];
                            $claveAcceso = $ret['10'];
                            $rucProveedor = $ret['2'];
                            $supplier = Person::where('number',$rucProveedor)->get();
                            if($supplier->count() < 1){

                                $retAc = Retention::where('observations','like','%'.$claveAcceso.'%')->delete();
                                return [
                                    'success' => false,
                                    'message' => "No se encontro el proveedor : ".$rucProveedor
                                ];

                            }
                            $url = 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';

                            if($this->company->soap_type_id == '01'){

                                $url = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';
                            }

                            $get = new AuthSri();
                            $documento = $get->send($url,$claveAcceso);

                            $comporbante = $documento['RespuestaAutorizacionComprobante']['numeroComprobantes'];

                            if($comporbante > 0){

                                $retencion = $documento['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['comprobante'];
                                $xmlRet =  simplexml_load_string($retencion);

                                Log::info($xmlRet->infoTributaria->claveAcceso);
                                foreach($xmlRet->docsSustento->docSustento->retenciones->retencion as $retC){

                                    $existe = Retention::where('ubl_version',$claveAcceso. "-".$retC->codigo."-".$retC->codigoRetencion)->get();

                                    if($existe->count() > 0 ){

                                        if($existe[0]->in_use == false){

                                            $existe[0]->delete();

                                            $retIN = new Retention();
                                            $retIN->user_id = auth()->user()->id;
                                            $retIN->external_id = Str::uuid()->toString();
                                            $retIN->establishment_id = auth()->user()->establishment_id;
                                            $retIN->soap_type_id = $this->company->soap_type_id;
                                            $retIN->state_type_id = '05';
                                            $retIN->series = 'RT';
                                            $retIN->number = (Retention::get())->count() + 1;
                                            $retIN->date_of_issue = date("Y-m-d");
                                            $retIN->time_of_issue = date("h:i:s");
                                            $retIN->supplier_id = $supplier[0]->id;
                                            $retIN->supplier = $supplier[0];
                                            $retIN->retention_type_id = '01';
                                            $retIN->observations = "Retencion: ".$claveAcceso. " Tipo: ".(($retC->codigo == '1')?'RENTA':'IVA');
                                            $retIN->ubl_version = $claveAcceso. "-".$retC->codigo."-".$retC->codigoRetencion;
                                            $retIN->currency_type_id = $this->config->currency_type_id;
                                            $retIN->total_retention = $retC->valorRetenido;
                                            $retIN->total = $xmlRet->docsSustento->docSustento->importeTotal;
                                            $retIN->document_type_id = $xmlRet->docsSustento->docSustento->codDocSustento;
                                            $retIN->save();

                                        }
                                    }else{
                                        $retIN = new Retention();
                                        $retIN->user_id = auth()->user()->id;
                                        $retIN->external_id = Str::uuid()->toString();
                                        $retIN->establishment_id = auth()->user()->establishment_id;
                                        $retIN->soap_type_id = $this->company->soap_type_id;
                                        $retIN->state_type_id = '05';
                                        $retIN->series = 'RT';
                                        $retIN->number = (Retention::get())->count() + 1;
                                        $retIN->date_of_issue = date("Y-m-d");
                                        $retIN->time_of_issue = date("h:i:s");
                                        $retIN->supplier_id = $supplier[0]->id;
                                        $retIN->retention_type_id = '01';
                                        $retIN->observations = "Retencion: ".$claveAcceso. " Tipo: ".(($retC->codigo == '1')?'RENTA':'IVA');
                                        $retIN->ubl_version = $claveAcceso. "-".$retC->codigo."-".$retC->codigoRetencion;
                                        $retIN->currency_type_id = $this->config->currency_type_id;
                                        $retIN->total_retention = $retC->valorRetenido;
                                        $retIN->total = $xmlRet->docsSustento->docSustento->importeTotal;
                                        $retIN->document_type_id = $xmlRet->docsSustento->docSustento->codDocSustento;
                                        $retIN->save();
                                    }
                                }

                                $totalProcesados += 1;

                            }else{

                                Log::error("No se encontro la retencion : ".$ret['10']);
                                $totalError += 1;
                            }
                        }
                    }catch(Exception $ex){
                        Log::error("Error procesando retencion desde importacion: ".$ex->getMessage());
                        $totalError += 1;
                    }
                }

                return [
                    'success' => true,
                    'message' => $message,
                    'documents'=>$totalDocumentos,
                    'fail' =>$totalError,
                    'procesed' =>$totalProcesados,
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

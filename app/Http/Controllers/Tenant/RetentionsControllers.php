<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Helpers\Functions\GeneralPdfHelper;
use App\CoreFacturalo\Helpers\QrCode\QrCodeGenerate;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Helpers\Xml\XmlFormat;
use App\CoreFacturalo\SRI\FirmarSri;
use App\CoreFacturalo\Template;
use App\CoreFacturalo\WS\Services\AuthSri;
use App\CoreFacturalo\WS\Services\BillSender;
use App\CoreFacturalo\WS\Services\SunatEndpoints;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\Tenant\DocumentEmail;
use App\Models\Tenant\Catalogs\RetentionType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchasePayment;
use App\Models\Tenant\RetentionsDetailEC;
use App\Models\Tenant\RetentionsEC;
use App\Models\Tenant\SriFormasPagos;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Modules\Item\Models\Category;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Swift_Mailer;
use Swift_SmtpTransport;

class RetentionsControllers extends Controller
{
    const CREADA = '01';
    const GENERADA = '02';
    const RECIBIDA = '03';
    const DEVUELTA = '04';
    const AUTORIZADA = '05';
    const NOAUTORIZADA = '06';
    const RECHAZADA = '07';
    const ENPROCESO = '08';
    const ERROR = '09';

    protected $configuration;
    protected $company;
    protected $isDemo;
    protected $ambienteLocal;
    protected $isOse;
    protected $isSRI;
    protected $purchase;
    protected $xmlUnsigned;
    protected $xmlSigned;
    protected $type;
    protected $clave_acceso;
    protected $pathCertificate;
    protected $urlSri;
    protected $soapUsername;
    protected $soapPassword;
    protected $endpoint;
    protected $actions;
    protected $document;
    protected $doc_type;
    protected $email;

    public function __construct()
    {
        $this->configuration = Configuration::first();
        $this->company = Company::active();
        $this->isDemo = ($this->company->soap_type_id === '01') ? true : false;
        $this->ambienteLocal = ($this->company->soap_type_id === '01') ? 1 : 2;
        $this->isOse = ($this->company->soap_send_id === '02') ? true : false;
        $this->isSRI = ($this->company->soap_send_id === '03') ? true : false;
        $this->type = 'retentionEC';

    }

    private function loadXmlSigned($file)
    {

        $this->xmlSigned = Storage::disk('tenant')->get('signed/'.$file);

    }

    private function prepareDocument($id){

        $purchaseL = Purchase::findOrFail($id);
        $retencionL = RetentionsEC::where('idDocumento', $id)->where('status_id',['01'])->get();
        if($retencionL->count() > 0 ){
            $establecimiento = Establishment::findOrFail($purchaseL->establishment_id);
            $retencioneDetallesL = RetentionsDetailEC::where('idRetencion',$retencionL[0]->idRetencion)->get();
            $formasPago = PurchasePayment::where('purchase_id',$id)->get();

            $clave = "" . date('dmY', strtotime($retencionL[0]->created_at)) . "07" . $this->company->number."".substr($this->company->soap_type_id,1,1)."".substr($retencionL[0]->idRetencion,1)."" . str_pad('12345678', '8', '0', STR_PAD_LEFT) . "" . 1 . "";
            $digito_verificador_clave = $this->validar_clave($clave);
            $this->clave_acceso = $clave . "" . $digito_verificador_clave . "";
            $retencion = null;

            if($purchaseL && $purchaseL->count() > 0 ){

                $retencion = [
                    'ambiente'=>$this->ambienteLocal,
                    'emision' => 1,
                    'razonSocial' => $this->company->name,
                    'nombreComercial' => $this->company->trade_name,
                    'ruc' => $this->company->number,
                    'claveAcceso' => $this->clave_acceso,
                    'codDoc' => '07',
                    'establecimiento' => $retencionL[0]->establecimiento,
                    'ptoEmision'=> substr($retencionL[0]->ptoEmision,1),
                    'secuencial'=>substr($retencionL[0]->idRetencion,7),
                    'dirMatriz' => $establecimiento->address,
                    'fechaEmision' => $retencionL[0]->created_at->format('d/m/Y'),
                    'disEstablecimiento' => $establecimiento->address,
                    'contribuyenteEspecial' => $this->company->contribuyente_especial_num,
                    'obligadoContabilidad' => ($this->company->obligado_contabilidad > 0 ) ? 'SI':'NO',
                    'tipoIdentificacionSujetoRetenido' => str_pad($purchaseL->supplier->identity_document_type_id, '2', '0', STR_PAD_LEFT),
                    'parteRel' => 'NO',
                    'razonSocialSujetoRetenido' => $purchaseL->supplier->name,
                    'identificacionSujetoRetenido' => $purchaseL->supplier->number,
                    'periodoFiscal'=> $retencionL[0]->created_at->format('m/Y'),
                    'codSustento' => $purchaseL->codSustento,
                    'codDocSustento' => $purchaseL->document_type_id,
                    'numDocSustento' => $purchaseL->sequential_number,
                    'fechaEmisionDocSustento' => $retencionL[0]->created_at->format('d/m/Y'),
                    'numAutDocSustento' => $purchaseL->auth_number,
                    'pagoLocExt' => '01',
                    'totalSinImpuestos' => $purchaseL->total_value,
                    'importeTotal' => $purchaseL->total,

                    'baseImponible0' => ($purchaseL->total_unaffected) ? $purchaseL->total_unaffected :0,
                    'baseImponible12' => ($purchaseL->total_taxed)? $purchaseL->total_taxed:0,
                    'valorIva12' => ($purchaseL->total_igv) ? $purchaseL->total_igv:0,

                    'retenciones' => $retencioneDetallesL->transform(function($row, $key) {
                        $retentionDescrip = RetentionType::where('code',$row->codRetencion)->get();
                        return [
                            'codigo' => intval($retentionDescrip[0]->type_id),
                            'codigoRetencion' => ($retentionDescrip[0]->code2)? $retentionDescrip[0]->code2 : $row->codRetencion,
                            'baseImponible' => $row->baseRet,
                            'porcentajeRetener' => $row->porcentajeRet,
                            'valorRetenido' => $row->valorRet,
                        ];
                    }),
                    'fpagos' => ($formasPago->count() > 0 )? $formasPago->transform(function($row, $key) {
                        $pagoSRI = PaymentMethodType::find($row->payment_method_type_id);
                        return [
                            'formaPago' => $pagoSRI->pago_sri,
                            'total' => $row->payment,
                        ];
                    }): [],



                ];
            }
            $qr = $this->getQr($this->clave_acceso);

            $retencionL[0]->update([

                'claveAcceso'=>$this->clave_acceso,
                'filename'=>$this->clave_acceso,
                'external_id'=>$this->clave_acceso,
                'status_id' => Self::GENERADA,
                'barCode' => $qr,

            ]);

            return $retencion;
        }else{
            return false;
        }

    }

    public function validateDocumentSRI($id, $clave){

        $url = null;
        if($this->isDemo){

            $url = "https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl";

        }else{

            $url = "https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl";
        }

        $request = new AuthSri();
        $authSRI = $request->send($url,$clave);
        $retencion = RetentionsEC::find($id);

        try{

            if($authSRI != ''){

                $mensajeAuth = null;
                $responseAuth = null;
                $estateId = null;
                $fechaAuth = null;

                $estado = $authSRI['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['estado'];

                if($estado == 'RECHAZADA'){

                    $estateId =  self::RECHAZADA;
                    $responseAuth = json_encode($authSRI['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['mensajes']);
                    $mensajeAuth = 'DOCUMENTO RECHAZADO POR EL SRI';

                }elseif($estado == 'AUTORIZADO'){

                    $fechaAutorizado = $authSRI['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['fechaAutorizacion'];
                    $fechaAutorizado = str_replace('T',' ',$fechaAutorizado);
                    $fechaAuth = substr($fechaAutorizado,0,19);

                    $estateId = self::AUTORIZADA;
                    $mensajeAuth = 'DOCUMENTO AUTORIZADO POR EL SRI';
                    $documento = $authSRI['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['comprobante'];
                    $nombre= 'autorizado/'.$retencion->claveAcceso.'.xml';

                    Storage::disk('tenant')->put($nombre, $documento);

                    $tipodoc = 'retention';
                    $this->doc_type = '03';
                    $this->actions['format_pdf'] = 'blank';

                    $this->createPdf($retencion, $tipodoc, 'a4');

                    $this->sendEmail2($id);

                }elseif($estado == 'NO AUTORIZADO'){

                    $valor = array_filter($authSRI['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['mensajes'], function($B,$k){

                        return array_filter($B,function ($key, $value) use($k){
                            return preg_replace("/[\r\n|\n|\r]+/", '', $key);

                        },ARRAY_FILTER_USE_BOTH);

                    },ARRAY_FILTER_USE_BOTH);

                    $responseAuth = json_encode($authSRI['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['mensajes']);
                    $mensajeAuth = 'DOCUMENTO NO AUTORIZADO POR EL SRI';
                    $estateId = self::NOAUTORIZADA;
                    $fechaAuth = null;

                }elseif($estado == 'EN PROCESO'){

                    $responseAuth = json_encode($authSRI['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['mensajes']);
                    $mensajeAuth = 'DOCUEMNTO EN PROCESO DE VALIDACIÓN';
                    $estateId = self::ENPROCESO;
                    $fechaAuth = null;

                }
                else{

                    if($authSRI['RespuestaAutorizacionComprobante']['numeroComprobantes'] > 0){

                        $mensajeAuth = 'SIN ESTADO POR EL SRI';
                        $responseAuth = $authSRI['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['mensajes']['mensaje']['mensaje'];
                        $estateId = self::ENPROCESO;
                        $fechaAuth = null;

                    }else{

                        $mensajeAuth = 'NO SE ENCONTRO EL DOCUMENTO EN EL SISTEMA DEL SRI';
                        $responseAuth = NULL;
                        $estateId = self::ENPROCESO;
                        $fechaAuth = null;

                    }

                }

                $retencion = RetentionsEC::find($id);
                $retencion->update([

                    'status_id' => $estateId,
                    'response_verification' => $mensajeAuth,
                    'DateTimeAutorization' => $fechaAuth,
                    'response_message_verification' => $responseAuth,
                    'verificated' => 1,
                ]);

            }else{

                $retencion->update([
                    'status_id' => self::DEVUELTA,
                    'response_verification' => 'NO SE PUDO VERIFICAR EL DOCUMENTO',
                    'verificated' => 0,
                ]);
            }
        }
        catch(Exception $ex){
            Log::error('Error al porcesar una retencion: '.$ex->getMessage());
        }


    }

    private function getQr($clave)
    {

        $qrCode = new QrCodeGenerate();
        //JOINSOFTWARE
        $barcode = $qrCode->generarCodigoBarras($clave);
        return $barcode;
    }

    public function toPrint($id, $format)
    {
        $retencion = RetentionsEC::find($id);
        $external_id = $retencion->external_id;

        if (!$retencion) throw new Exception("El código {$external_id} es inválido, no se encontro el pedido relacionado");

        //$this->reloadPDF($purchase, $format, $purchase->filename);

        $content = Storage::disk('tenant')->get('pdf/'.$retencion->claveAcceso.'.pdf');
        $temp = tempnam(sys_get_temp_dir(), 'retention');

        file_put_contents($temp, $content);
        //file_put_contents($temp, $this->getStorage($retencion->filename, 'purchase'));
        //Log::info('ruta del archivo : ', $ruta);
        return response()->file($temp, $this->generalPdfResponseFileHeaders($retencion->filename));
        //return response()->file('pdf/'.$retencion->claveAcceso.'.pdf', $this->generalPdfResponseFileHeaders($retencion->filename.'.pdf'));
    }

    private function createPdf($document = null, $type = null, $format = null, $output = 'pdf') {

        ini_set("pcre.backtrack_limit", "5000000");
        $template = new Template();
        $pdf = new Mpdf();

        $format_pdf = $this->actions['format_pdf'] ?? null;

        $documentoEnviar = ($document != null) ? $document : $this->document;
        $format_pdf = ($format != null) ? $format : $format_pdf;
        $this->type = ($type != null) ? $type : $this->type;

        $base_pdf_template = ($type != null && $type == 'retention') ? 'default': Establishment::find($this->document->establishment_id)->template_pdf;

        if (($format_pdf === 'ticket') OR
            ($format_pdf === 'ticket_58') OR
            ($format_pdf === 'ticket_50'))
        {
            $base_pdf_template = Establishment::find($this->document->establishment_id)->template_ticket_pdf;
        }

        $pdf_margin_top = 15;
        $pdf_margin_right = 15;
        $pdf_margin_bottom = 15;
        $pdf_margin_left = 15;

        $purchase = Purchase::find($documentoEnviar->idDocumento);
        $detalles = RetentionsDetailEC::where('idRetencion',$documentoEnviar->idRetencion)->get();
        $establecimiento = Establishment::find($purchase->establishment_id);

        $detalles->transform(function($row){

            $code = RetentionType::where('code',$row->codRetencion)->get();
            $row['code'] = ( $code->count() > 0 && $code[0]->type_id == '01') ? 'RENTA':'IVA';
            return $row;

        });

        $documentoEnviar->purchase = $purchase;
        $documentoEnviar->detalles = $detalles;
        $documentoEnviar->establecimiento = $establecimiento;

        $this->email = $purchase->supplier->email;

        $html = $template->pdf($base_pdf_template, $this->type, $this->company, $documentoEnviar, $format_pdf);

        if (($format_pdf === 'ticket') OR
            ($format_pdf === 'ticket_58') OR
            ($format_pdf === 'ticket_50'))
        {
            $base_pdf_template = Establishment::find($this->document->establishment_id)->template_ticket_pdf;

            $width = ($format_pdf === 'ticket_58') ? 56 : 78 ;
            if(config('tenant.enabled_template_ticket_80')) $width = 76;
            if(config('tenant.enabled_template_ticket_70')) $width = 70;
            if($format_pdf === 'ticket_50') $width = 45;

            $company_name      = (strlen($this->company->name) / 20) * 10;
            $company_address   = (strlen($this->document->establishment->address) / 30) * 10;
            $company_number    = $this->document->establishment->telephone != '' ? '10' : '0';
            $customer_name     = strlen($this->document->customer->name) > '25' ? '10' : '0';
            $customer_address  = (strlen($this->document->customer->address) / 200) * 10;
            $customer_department_id  = ($this->document->customer->department_id == 16) ? 20:0;
            $p_order           = $this->document->purchase_order != '' ? '10' : '0';

            $total_prepayment = $this->document->total_prepayment != '' ? '10' : '0';
            $total_discount = $this->document->total_discount != '' ? '10' : '0';
            $was_deducted_prepayment = $this->document->was_deducted_prepayment ? '10' : '0';

            $total_exportation = $this->document->total_exportation != '' ? '10' : '0';
            $total_free        = $this->document->total_free != '' ? '10' : '0';
            $total_unaffected  = $this->document->total_unaffected != '' ? '10' : '0';
            $total_exonerated  = $this->document->total_exonerated != '' ? '10' : '0';
            $total_taxed       = $this->document->total_taxed != '' ? '10' : '0';
            $perception       = $this->document->perception != '' ? '10' : '0';
            $detraction       = $this->document->detraction != '' ? '50' : '0';
            $detraction       += ($this->document->detraction && $this->document->invoice->operation_type_id == '1004') ? 45 : 0;

            $total_plastic_bag_taxes       = $this->document->total_plastic_bag_taxes != '' ? '10' : '0';
            $quantity_rows     = count($this->document->items) + $was_deducted_prepayment;
            $document_payments     = count($this->document->payments ?? []);
            $document_transport     = ($this->document->transport) ? 30 : 0;
            $document_retention     = ($this->document->retention) ? 10 : 0;

            $extra_by_item_additional_information = 0;
            $extra_by_item_description = 0;
            $discount_global = 0;
            foreach ($this->document->items as $it) {
                if(strlen($it->item->description)>100){
                    $extra_by_item_description +=24;
                }
                if ($it->discounts) {
                    $discount_global = $discount_global + 1;
                }
                if($it->additional_information){
                    $extra_by_item_additional_information += count($it->additional_information) * 5;
                }
            }
            $legends = $this->document->legends != '' ? '10' : '0';

            $quotation_id = ($this->document->quotation_id) ? 15:0;

            //ajustes para footer amazonia

            if($this->configuration->legend_footer AND $format_pdf === 'ticket') {
                $height_legend = 15;
            } elseif($this->configuration->legend_footer AND $format_pdf === 'ticket_58') {
                $height_legend = 30;
            } elseif($this->configuration->legend_footer AND $format_pdf === 'ticket_50') {
                $height_legend = 50;
            } else {
                $height_legend = 10;
            }

            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    $width,
                    180 +
                    (($quantity_rows * 8) + $extra_by_item_description) +
                    ($document_payments * 8) +
                    ($discount_global * 8) +
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
                    $perception +
                    $total_taxed+
                    $total_prepayment +
                    $total_discount +
                    $was_deducted_prepayment +
                    $customer_department_id+
                    $detraction+
                    $total_plastic_bag_taxes+
                    $quotation_id+
                    $extra_by_item_additional_information+
                    $height_legend+
                    $document_transport+
                    $document_retention
                ],
                'margin_top' => 0,
                'margin_right' => 1,
                'margin_bottom' => 0,
                'margin_left' => 1
            ]);
        }else if($format_pdf === 'a5'){

            $company_name      = (strlen($this->company->name) / 20) * 10;
            $company_address   = (strlen($this->document->establishment->address) / 30) * 10;
            $company_number    = $this->document->establishment->telephone != '' ? '10' : '0';
            $customer_name     = strlen($this->document->customer->name) > '25' ? '10' : '0';
            $customer_address  = (strlen($this->document->customer->address) / 200) * 10;
            $p_order           = $this->document->purchase_order != '' ? '10' : '0';

            $total_exportation = $this->document->total_exportation != '' ? '10' : '0';
            $total_free        = $this->document->total_free != '' ? '10' : '0';
            $total_unaffected  = $this->document->total_unaffected != '' ? '10' : '0';
            $total_exonerated  = $this->document->total_exonerated != '' ? '10' : '0';
            $total_taxed       = $this->document->total_taxed != '' ? '10' : '0';
            $total_plastic_bag_taxes       = $this->document->total_plastic_bag_taxes != '' ? '10' : '0';
            $quantity_rows     = count($this->document->items);

            $extra_by_item_description = 0;
            $discount_global = 0;
            foreach ($this->document->items as $it) {
                if(strlen($it->item->description)>100){
                    $extra_by_item_description +=24;
                }
                if ($it->discounts) {
                    $discount_global = $discount_global + 1;
                }
            }
            $legends = $this->document->legends != '' ? '10' : '0';


            $height = ($quantity_rows * 8) +
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
                    $total_taxed;
            $diferencia = 148 - (float)$height;

            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    210,
                    $diferencia + $height
                    ],
                'margin_top' => 2,
                'margin_right' => 5,
                'margin_bottom' => 0,
                'margin_left' => 5
            ]);


        } else {

            if ($base_pdf_template === 'brand') {
                $pdf_margin_top = 93.7;
                $pdf_margin_bottom = 74;
            }
            if ($base_pdf_template === 'blank' && in_array($documentoEnviar->document_type_id, ['09'])) {
                $pdf_margin_top = 110;
                $pdf_margin_bottom = 125;
            }

            $pdf_font_regular = config('tenant.pdf_name_regular');
            $pdf_font_bold = config('tenant.pdf_name_bold');

            if ($pdf_font_regular != false) {
                $defaultConfig = (new ConfigVariables())->getDefaults();
                $fontDirs = $defaultConfig['fontDir'];

                $defaultFontConfig = (new FontVariables())->getDefaults();
                $fontData = $defaultFontConfig['fontdata'];

                $pdf = new Mpdf([
                    'fontDir' => array_merge($fontDirs, [
                        app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.
                                                 DIRECTORY_SEPARATOR.'pdf'.
                                                 DIRECTORY_SEPARATOR.$base_pdf_template.
                                                 DIRECTORY_SEPARATOR.'font')
                    ]),
                    'fontdata' => $fontData + [
                        'custom_bold' => [
                            'R' => $pdf_font_bold.'.ttf',
                        ],
                        'custom_regular' => [
                            'R' => $pdf_font_regular.'.ttf',
                        ],
                    ],
                    'margin_top' => $pdf_margin_top,
                    'margin_right' => $pdf_margin_right,
                    'margin_bottom' => $pdf_margin_bottom,
                    'margin_left' => $pdf_margin_left,
                ]);

            } else {
                $pdf = new Mpdf([
                    'margin_top' => $pdf_margin_top,
                    'margin_right' => $pdf_margin_right,
                    'margin_bottom' => $pdf_margin_bottom,
                    'margin_left' => $pdf_margin_left
                ]);
            }
        }

        $path_css = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.
                                             DIRECTORY_SEPARATOR.'pdf'.
                                             DIRECTORY_SEPARATOR.$base_pdf_template.
                                             DIRECTORY_SEPARATOR.'style.css');

        $stylesheet = file_get_contents($path_css);

        if(config('tenant.pdf_template_footer')) {
            $html_footer = '';
            if (($format_pdf != 'ticket') AND ($format_pdf != 'ticket_58') AND ($format_pdf != 'ticket_50')) {
                $html_footer = $template->pdfFooter($base_pdf_template, in_array($documentoEnviar->document_type_id, ['09']) ? null : $documentoEnviar);
                $html_footer_legend = "";
            }
            // dd($this->configuration->legend_footer && in_array($this->document->document_type_id, ['01', '03']));
            // se quiere visuzalizar ahora la legenda amazona en todos los formatos
            $html_footer_legend = '';
            if($this->configuration->legend_footer && in_array($documentoEnviar->document_type_id, ['01', '03'])){
                $html_footer_legend = $template->pdfFooterLegend($base_pdf_template, $document);
            }

            $pdf->SetHTMLFooter($html_footer.$html_footer_legend);

        }

        // para impresion automatica se requiere el resultado en html ya que es lo que se envia a las funciones de impresión
        if($output == 'html') {
            $path_html = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.
                                             DIRECTORY_SEPARATOR.'pdf'.
                                             DIRECTORY_SEPARATOR.'ticket_html.css');
            $ticket_html = file_get_contents($path_html);
            $pdf->WriteHTML($ticket_html, HTMLParserMode::HEADER_CSS);
            $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);
            return "<style>".$ticket_html.$stylesheet."</style>".$html;
        }
        else {
            $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
            $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);
        }

        //$this->uploadFile($pdf->output('', 'S'), 'pdf');
        Storage::disk('tenant')->put('pdf/'.$documentoEnviar->claveAcceso.'.pdf',$pdf->output('', 'S'));

        //return $this;
    }

    public function sendXmlSigned($id ,$file)
    {
        $this->setSoapCredentials();

        $sender = new BillSender();
        $this->loadXmlSigned($file);
        $res =  $sender->send($this->urlSri, $this->xmlSigned);
        $Retencion = RetentionsEC::find($id);

        if($res) {

            $responseSRI = $res;
            $estado = NULL;
            $mensajeEstado = NULL;
            $mensajesRespuesta = NULL;
            $IdEstado = NULL;

            try{

                $estado = ($responseSRI['RespuestaRecepcionComprobante']['estado']) ? $responseSRI['RespuestaRecepcionComprobante']['estado'] :'ERROR';

                if($estado == 'DEVUELTA'){

                    $IdEstado = (self::DEVUELTA);
                    $mensajeEstado = 'DOCUMENTO DEVUELTO POR EL SRI';
                    $mensajesRespuesta = json_encode($responseSRI['RespuestaRecepcionComprobante']['comprobantes']['comprobante']['mensajes']);

                }elseif($estado == 'RECIBIDA'){

                    $IdEstado = (self::RECIBIDA);
                    $mensajeEstado = 'DOCUMENTO RECIBIDO POR EL SRI';
                    $mensajesRespuesta = null;

                }else{

                    $IdEstado = (self::GENERADA);
                    $mensajeEstado = 'ERROR AL PROCESAR LA RESPUESTA DEL SRI';
                    $mensajesRespuesta = json_encode($res);
                }

               $Retencion->update([
                    'status_id' => $IdEstado,
                    'response_envio' => $mensajeEstado,
                    'response_message_envio' => $mensajesRespuesta,
               ]);


            }catch(Exception $ex){

                $IdEstado = self::ERROR;
                $mensajeEstado = 'NO SE PUDO PORCESAR LAS RESPUESTA DEL SRI';
                $mensajesRespuesta = null;

                if($res['RespuestaRecepcionComprobante']['comprobantes']['comprobante']['claveAcceso'] == 'N/A'){

                    $mensajeEstado = $res['RespuestaRecepcionComprobante']['comprobantes']['comprobante']['mensajes']['mensaje']['mensaje'];
                    $mensajesRespuesta = json_encode($res);
                }


                $Retencion->update([

                    'status_id' => $IdEstado,
                    'response_envio' => $mensajeEstado,
                    'response_message_envio' => $mensajesRespuesta,

               ]);
            }

        } else {

            $IdEstado = self::GENERADA;
            $mensajeEstado = 'SIN RESPUESTA DEL SRI';
            $mensajesRespuesta = json_encode($res);

            $Retencion->update([
                'status_id' => $IdEstado,
                'response_envio' => $mensajeEstado,
                'response_message_envio' => $mensajesRespuesta,
           ]);
        }
    }

    public function createXML($id){

        $documento = $this->prepareDocument($id);
        if($documento){
            $template = new Template();
            $this->xmlUnsigned = XmlFormat::format($template->xml($this->type, $this->company, $documento,null));
            $nombre = "unsigned/" . $this->clave_acceso . ".xml";
            Storage::disk('tenant')->put($nombre, $this->xmlUnsigned);
            $this->firmarXML();
            $nombre2 = "signed/" . $this->clave_acceso . ".xml";
            Storage::disk('tenant')->put($nombre2, $this->xmlSigned);

            return $this->clave_acceso;

        }else{
            return 'No se pueden generar retenciones del documento: ' . $id;
        }

    }

    private function firmarXML(){

        $this->setPathCertificate();
        $firma = new FirmarSri();
        $this->xmlSigned = $firma->Firma_SRI($this->clave_acceso, $this->pathCertificate,$this->company->certificate_pass,$this->xmlUnsigned);
    }

    private function setPathCertificate()
    {
        if($this->isOse) {
            $this->pathCertificate = storage_path('app'.DIRECTORY_SEPARATOR.'certificates'.DIRECTORY_SEPARATOR.$this->company->certificate);
        }else if($this->isSRI){

            $this->pathCertificate = storage_path('app'.DIRECTORY_SEPARATOR.'certificates'.DIRECTORY_SEPARATOR.$this->company->certificate);


        }else {
            if($this->isDemo) {
                $this->pathCertificate = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.
                    'WS'.DIRECTORY_SEPARATOR.
                    'Signed'.DIRECTORY_SEPARATOR.
                    'Resources'.DIRECTORY_SEPARATOR.
                    'certificate.pem');
            } else {
                $this->pathCertificate = storage_path('app'.DIRECTORY_SEPARATOR.
                    'certificates'.DIRECTORY_SEPARATOR.$this->company->certificate);
            }
        }
    }

    private function validar_clave($clave) {

        if ($clave == "") {
            $verificado = false;
            return $verificado;
        }

        $x = 2;
        $sumatoria = 0;
        for ($i = strlen($clave) - 1; $i >= 0; $i--) {
            if ($x > 7) {
                $x = 2;
            }
            $sumatoria = $sumatoria + ($clave[$i] * $x);
            $x++;
        }
        $digito = $sumatoria % 11;
        $digito = 11 - $digito;

        switch ($digito) {
            case 10:
                $digito = "1";
                break;
            case 11:
                $digito = "0";
                break;
        }
        return $digito;
    }

    private function setSoapCredentials()
    {

        if($this->isOse) {

            $this->soapUsername = $this->company->soap_username;
            $this->soapPassword = $this->company->soap_password;
            $this->endpoint = $this->company->soap_url;
            //            dd($this->soapPassword);

        }else{

            if($this->isSRI){
                if($this->isDemo){
                    $this->urlSri = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';
                }else{
                    $this->urlSri = 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';
                }
            }else{
                if($this->isDemo) {
                    $this->soapUsername = $this->company->number.'MODDATOS';
                    $this->soapPassword = 'moddatos';
                } else {
                    $this->soapUsername = $this->company->soap_username;
                    $this->soapPassword = $this->company->soap_password;
                }
            }



        }
        if($this->isOse) {
            /*NO HACE NADA SI ES OSE */
        }else {
            switch ($this->type) {
                case 'perception':
                case 'retention':
                    $this->endpoint = ($this->isDemo)?SunatEndpoints::RETENCION_BETA:SunatEndpoints::RETENCION_PRODUCCION;
                    break;
                case 'dispatch':
                    $this->endpoint = ($this->isDemo)?SunatEndpoints::GUIA_BETA:SunatEndpoints::GUIA_PRODUCCION;
                    break;
                default:
                    // $this->endpoint = ($this->isDemo)?SunatEndpoints::FE_BETA:SunatEndpoints::FE_PRODUCCION;
                    $this->endpoint = ($this->isDemo)?SunatEndpoints::FE_BETA : ($this->configuration->sunat_alternate_server ? SunatEndpoints::FE_PRODUCCION_ALTERNATE : SunatEndpoints::FE_PRODUCCION);
                    break;
            }
        }

    }

    public function sendEmail( Request $request )
    {

        $company = $this->company;
        $document = RetentionsEC::find($request->id);
        $purchase = Purchase::find($document->idDocumento);
        $detalles = RetentionsDetailEC::where('idRetencion',$document->idRetencion)->get();
        $establecimiento = Establishment::find($purchase->establishment_id);

        $document->establishment = $establecimiento;
        $document->purchase = $purchase;
        $document->total_retention = $detalles->sum('valorRet');

        $email = $request->email;
        $mailable =new DocumentEmail($company, $document);
        $id =  $request->id;

        Configuration::setConfigSmtpMail();

        // Backup your default mailer
        $backup = Mail::getSwiftMailer();
        $transport =  new Swift_SmtpTransport(Config::get('mail.host'), Config::get('mail.port'), Config::get('mail.encryption'));
        $transport->setUsername(Config::get('mail.username'));
        $transport->setPassword(Config::get('mail.password'));
        $mailer = new Swift_Mailer($transport);
        Mail::setSwiftMailer($mailer);
        Mail::to($email)->send($mailable);

        //$model = __FILE__.";;".__LINE__;
        //$sendIt = EmailController::SendMail($email, $mailable, $id, $model);

        return [
            'success' => true
        ];
    }

    private function sendEmail2($id)
    {

        $company = $this->company;
        $document = RetentionsEC::find($id);
        $email = $this->email;
        $mailable =new DocumentEmail($company, $document);
        $id =  $id;
        $model = __FILE__.";;".__LINE__;
        $sendIt = EmailController::SendMail($email, $mailable, $id, $model);

    }

}

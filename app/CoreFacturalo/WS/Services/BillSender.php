<?php

namespace App\CoreFacturalo\WS\Services;

use App\CoreFacturalo\WS\Response\BillResult;
use Illuminate\Support\Facades\Log;
use SoapClient;
use SoapHeader;
use nusoap_client;

/**
 * Class BillSender.
 */
class BillSender extends BaseSunat
{
    /**
     * @param string $filename
     * @param string $content agregar o quitar programas
     *
     * @return mixed
     */
    public function send($filename, $content)
    {
        $response = null;
        $servicio = "https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl"; //url del servicio
        $client = new nusoap_client("$filename", 'wsdl');
        $client->soap_defencoding = 'utf-8';
        $client->decode_utf8 = false;
        $params = array();
        Log::alert('URL a ebviar: '.$filename);
        try {

            $params['xml'] = base64_encode($content);
            $response = $client->call("validarComprobante", $params, "http://ec.gob.sri.ws.recepcion");
            //Log::info('__getLastResponseHeaders: '.json_encode($client->__getLastResponseHeaders()));
            Log::info('gettype: '.gettype($response));
            Log::info('response: '.json_encode($response));
            //Log::info('response: '.$response['RespuestaRecepcionComprobante']['estado']);

        } catch (\SoapFault $e) {
            //$result->setError($this->getErrorFromFault($e));
            Log::info('exception try to consult SRI: '.json_encode($e));
            return false;
        }

        return $response;
    }
}

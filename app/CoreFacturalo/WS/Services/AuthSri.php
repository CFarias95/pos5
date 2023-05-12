<?php

namespace App\CoreFacturalo\WS\Services;

use App\CoreFacturalo\WS\Response\BillResult;
use Illuminate\Support\Facades\Log;
use SoapHeader;
use nusoap_client;

/**
 * Class AuthSri.
 */
class AuthSri extends BaseSunat
{
    /**
     * @param string $url
     * @param string $clave de acceso a consultar
     *
     * @return mixed
     */
    public function send($url, $clave)
    {
        $response = null;
        $client = new nusoap_client("$url", 'wsdl');
        $client->soap_defencoding = 'utf-8';
        $client->decode_utf8 = false;

        $params = array();

        try {

            Log::info('CLAVE SRI: '.$clave);
            Log::info('API: '.$url);

            $params['claveAccesoComprobante'] = $clave;
            $response = $client->call("autorizacionComprobante", $params, "http://ec.gob.sri.ws.autorizacion");
            $err = $client->getError();

            Log::info('gettype: '.gettype($response));
            Log::info('response: '.$response['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['estado']);

        } catch (\SoapFault $e) {

            Log::info('ERROR: '.$e);
            return false;
        }

        return $response;
    }
}

<?php
namespace App\CoreFacturalo\SRI;

use App\CoreFacturalo\SRI\Firma;
use Illuminate\Support\Facades\Log;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of firmar
 *
 * @author UESR
 */
class FirmarSri {

    public function Firma_SRI($clave, $ruta_firma, $contrasena, $content){

        $config = array(
            'file' => $ruta_firma,
            'pass' => $contrasena
        );

        $firma = new Firma($config, $clave);
        $resp = $firma->verificarCertPKey();

        if ($resp["error"] === true)
            return $resp;

        $doc = $this->getDocXml($content);
        $resp = $firma->firmar($content , '$docFirmados');

        return $resp;

    }

    private function getDocXml($content)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($content);

        return $doc;
    }

}

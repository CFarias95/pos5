<?php

namespace App\CoreFacturalo\Helpers\QrCode;

use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;
// HERE WAS JOINSOFTWARE
use BarcodeBakery\Barcode\BCGcode128;
use BarcodeBakery\Common\BCGColor;
use BarcodeBakery\Common\BCGDrawing;
use BarcodeBakery\Common\BCGFontFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QrCodeGenerate
{
    public function displayPNGBase64($value, $w = 150, $level = 'L', $background = [255, 255, 255], $color = [0, 0, 0], $filename = null, $quality = 0)
    {
        $qrCode = new QrCode($value, $level);
        $output = new Output\Png();
        $base64 = base64_encode($output->output($qrCode, $w, $background, $color, $quality));
        return ($base64);
    }

    //JOINSOFTWARE
    public function generarCodigoBarras($claveAcceso) {
        $font = new BCGFontFile(public_path('fonts/arial.ttf'), 18);
        $colorBlack = new BCGColor(0, 0, 0);
        $colorWhite = new BCGColor(234, 234, 234);

        // Barcode Part
        $code = new BCGcode128();
        $code->setScale(4);
        $code->setThickness(40);
        $code->setForegroundColor($colorBlack);
        $code->setBackgroundColor($colorWhite);
        $code->setFont($font);
        $code->setStart(null);
        $code->setTilde(true);
        $code->parse($claveAcceso);
        $code->clearLabels();
        // Drawing Part
        $drawing = new BCGDrawing($code, $colorWhite);
        $drawing->finish(BCGDrawing::IMG_FORMAT_PNG, $claveAcceso.'.png');
        $this->redim($claveAcceso.'.png', $claveAcceso.'_mod.png', 350, 200);
        $img = file_get_contents($claveAcceso.'_mod.png');
        unlink($claveAcceso.'.png');
        unlink($claveAcceso.'_mod.png');
        return (base64_encode($img));
    }

    //JOINSOFTWARE
    public function redim($ruta1, $ruta2, $ancho, $alto) {
        # se obtene la dimension y tipo de imagen 
        $datos = getimagesize($ruta1);

        $ancho_orig = $datos[0]; # Anchura de la imagen original 
        $alto_orig = $datos[1];    # Altura de la imagen original 
        $tipo = $datos[2];

        if ($tipo == 1) { # GIF 
            if (function_exists("imagecreatefromgif"))
                $img = imagecreatefromgif($ruta1);
            else
                return false;
        }
        else if ($tipo == 2) { # JPG 
            if (function_exists("imagecreatefromjpeg"))
                $img = imagecreatefromjpeg($ruta1);
            else
                return false;
        }
        else if ($tipo == 3) { # PNG 
            if (function_exists("imagecreatefrompng"))
                $img = imagecreatefrompng($ruta1);
            else
                return false;
        }

        # Se calculan las nuevas dimensiones de la imagen 
        if ($ancho_orig > $alto_orig) {
            $ancho_dest = $ancho;
            $alto_dest = ($ancho_dest / $ancho_orig) * $alto_orig;
        } else {
            $alto_dest = $alto;
            $ancho_dest = ($alto_dest / $alto_orig) * $ancho_orig;
        }

        // imagecreatetruecolor, solo estan en G.D. 2.0.1 con PHP 4.0.6+ 
        $img2 = @imagecreatetruecolor($ancho_dest, $alto_dest) or $img2 = imagecreate($ancho_dest, $alto_dest);

        // Redimensionar 
        // imagecopyresampled, solo estan en G.D. 2.0.1 con PHP 4.0.6+ 
        @imagecopyresampled($img2, $img, 0, 0, 0, 0, $ancho_dest, $alto_dest, $ancho_orig, $alto_orig) or imagecopyresized($img2, $img, 0, 0, 0, 0, $ancho_dest, $alto_dest, $ancho_orig, $alto_orig);

        // Crear fichero nuevo, según extensión. 
        if ($tipo == 1) // GIF 
            if (function_exists("imagegif"))
                imagegif($img2, $ruta2);
            else
                return false;

        if ($tipo == 2) // JPG 
            if (function_exists("imagejpeg"))
                imagejpeg($img2, $ruta2);
            else
                return false;

        if ($tipo == 3)  // PNG 
            if (function_exists("imagepng"))
                imagepng($img2, $ruta2);
            else
                return false;

        return $img2;
    }
}
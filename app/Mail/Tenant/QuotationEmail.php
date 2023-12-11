<?php

namespace App\Mail\Tenant;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class QuotationEmail extends Mailable
{
    use Queueable, SerializesModels;
    use StorageDocument;

    public $company;
    public $quotation;

    public function __construct($company, $quotation)
    {
        $this->company = $company;
        $this->quotation = $quotation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $pdfNombre = "";
        if($this->quotation->send_upload_pdf == true){
            $pdfNombre = str_replace('.pdf','',$this->quotation->upload_filename);
        }else{
            $pdfNombre = $this->quotation->filename;
        }

        if($this->quotation->send_extra_pdf == true){

            $pdf2 = $this->getStorage($this->quotation->internal_request->upload_filename, 'internal_request_attached');
            $pdf = $this->getStorage($pdfNombre, 'quotation');

            return $this->subject('Envio de Cotización')
                        ->from(config('mail.username'), 'Cotización')
                        ->view('tenant.templates.email.quotation')
                        ->attachData($pdf2, $this->quotation->internal_request->upload_filename.'.pdf')
                        ->attachData($pdf, $this->quotation->filename.'.pdf');
        }else{
            $pdf = $this->getStorage($pdfNombre, 'quotation');
            return $this->subject('Envio de Cotización')
                        ->from(config('mail.username'), 'Cotización')
                        ->view('tenant.templates.email.quotation')
                        ->attachData($pdf, $this->quotation->filename.'.pdf');
        }

    }
}

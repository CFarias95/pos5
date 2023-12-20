<?php

namespace App\Mail\Tenant;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Models\Tenant\InternalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

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
        $IR = InternalRequest::find($this->quotation->internal_request);

        if($this->quotation->send_upload_pdf == true){
            $pdfNombre = str_replace('.pdf','',$this->quotation->upload_filename);
        }else{
            $pdfNombre = $this->quotation->filename;
        }

        if($this->quotation->send_extra_pdf == true){

            $pdf2 = $this->getStorage(str_replace('.pdf','',$IR->upload_filename),'pdf' ,'internal_request_attached');
            $pdf = $this->getStorage($pdfNombre, 'quotation');

            return $this->subject('Envio de Cotización')
                        ->from(config('mail.username'), 'Cotización')
                        ->view('tenant.templates.email.quotation')
                        ->attachData($pdf2, $IR->upload_filename)
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

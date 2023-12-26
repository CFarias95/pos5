<?php

namespace App\Mail\Tenant;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Models\Tenant\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\File;

class InventoryEmail extends Mailable
{
    use Queueable;
    use SerializesModels;
    use StorageDocument;

    public $document;
    public $type;
    public $company;

    public function __construct($document, $type, $company)
    {
        $this->document = $document;
        $this->type = $type;
        $this->company = $company;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $tipo='Ingreso';

        if($this->type=='output'){
            $tipo='Salida';
        }
        if($this->type=='fix'){
            $tipo='Ajuste';
        }
        $pdf = $this->getStorage('INV-'.$this->document->id,'pdf', 'inventory');

        $template_document_mail = config('tenant.template_document_mail');

        if($template_document_mail === 'default') {
            $template_document_mail_view = 'tenant.templates.email.inventory';
            $subject = "Envio de Comprobante $tipo  Mercadería";
        }

        $email = $this->subject($subject)
                    ->from(config('mail.username'), "Comprobante $tipo Mercadería")
                    ->view($template_document_mail_view)
                    ->attachData($pdf,'INV-'.$this->document->id.'.pdf');


        return $email;
    }

}

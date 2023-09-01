<?php

namespace App\Mail\Tenant;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InternalRequestEmail extends Mailable
{
    use Queueable, SerializesModels;
    use StorageDocument;

    public $estado;
    public $id;

    public function __construct($estado,$id)
    {
        $this->estado = $estado;
        $this->id = $id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        //$pdf = $this->getStorage($this->quotation->filename, 'quotation');
        return $this->subject('Pedido Interno')
        ->from(config('mail.username'), 'Pedido Interno')
        ->view('tenant.templates.email.internal_request');
        //->attachData($pdf, $this->quotation->filename.'.pdf');
    }
}

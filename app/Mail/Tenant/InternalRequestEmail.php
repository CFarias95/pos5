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
    public $name;
    public $body;
    public $upload_file;

    public function __construct($estado,$id, $name, $body, $uploadFile)
    {
        $this->upload_file = $uploadFile;
        $this->estado = $estado;
        $this->id = $id;
        $this->name = $name;
        $this->body = $body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->upload_file){

            $pdf = $this->getStorage(str_replace('.pdf','',$this->upload_file),'pdf', 'internal_request_attached');
            return $this->subject('Pedido Interno')
                        ->from(config('mail.username'), 'Pedido Interno')
                        ->attachData($pdf,'IR-'.$this->id.'.pdf')
                        ->view('tenant.templates.email.internal');
        }else{

            return $this->subject('Pedido Interno')
                        ->from(config('mail.username'), 'Pedido Interno')
                        ->view('tenant.templates.email.internal');
        }
    }
}

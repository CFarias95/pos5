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

    public function __construct($estado,$id, $name, $body)
    {
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
        return $this->subject('Pedido Interno')
                    ->from(config('mail.username'), 'Pedido Interno')
                    ->view('tenant.templates.email.internal');

    }
}

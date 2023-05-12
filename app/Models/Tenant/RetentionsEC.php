<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Model;

class RetentionsEC extends ModelTenant
{
    protected $table = 'retenciones_join';
    protected $fillable = [

        'id',
        'idRetencion',
        'idDocumento',
        'fechaFizcal',
        'idProveedor',
        'establecimiento',
        'ptoEmision',
        'secuencial',
        'codSustento',
        'codDocSustento',
        'numAuthSustento',
        'claveAcceso',
        'status_id',
        'response_envio',
        'response_message_envio',
        'response_verification',
        'DateTimeAutorization',
        'response_message_verification',
        'verificated',
        'external_id',
        'filename',
        'barCode',

    ];
}

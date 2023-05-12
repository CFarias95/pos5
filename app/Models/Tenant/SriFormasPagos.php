<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class SriFormasPagos extends ModelTenant
{

    protected $table = 'sri_formas_pago';
    protected $fillable = [
        'id',
        'code',
        'description',

    ];

}

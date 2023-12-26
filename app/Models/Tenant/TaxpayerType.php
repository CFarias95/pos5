<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;

class TaxpayerType extends ModelTenant
{
    //
    protected $fillable = [
        'id',
        'description',
    ];
}

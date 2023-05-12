<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Model;

class Advance extends ModelTenant
{
    protected $table = 'advances';
    protected $fillable = [
        'id',
        'idMethodType',
        'id_payment',
        'reference',
        'idCliente',
        'valor',
        'observation',
        'is_supplier',
    ];
}

<?php

namespace Modules\Account\Models;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Model;

class BankReconciliation extends ModelTenant
{
    protected $fillable = [
        'id',
        'initial_value',
        'total_debe',
        'total_haber',
        'diference_value',
        'status',
        'user_id',
        'account_id',
        'month',  // Mes de la verificacion
        'init_value',
    ];

    protected $casts = [
        'status' => 'bool',
        'init_value' => 'double',
    ];
}

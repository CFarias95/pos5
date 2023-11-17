<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Model;

class CreditNotesPayment extends ModelTenant
{
    protected $fillable = [
        'id',
        'document_id',
        'purchase_id',
        'amount',
        'user_id',
        'in_use',
        'used',
    ];
}

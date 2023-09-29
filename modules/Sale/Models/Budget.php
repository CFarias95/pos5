<?php

namespace Modules\Sale\Models;

use App\Models\Tenant\User;
use App\Models\Tenant\ModelTenant;

class Budget extends ModelTenant
{
    protected $with = ['user'];
    protected $fillable = [
        'id',
        'user_id',
        'user',
        'amount',
        'date_from',
        'date_until',

    ];
    protected $casts = [
        'date_from' => 'date',
        'date_until' => 'date',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

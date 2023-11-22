<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Model;

class Incoterm extends ModelTenant
{
    protected $fillable = [
        'id',
        'code',
    ];
}

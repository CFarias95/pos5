<?php

namespace Modules\Account\Models;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Model;

class CostCenter extends ModelTenant
{
    protected $fillable = [
        'id',
        'name',
        'level_1',
        'level_2',
    ];
}

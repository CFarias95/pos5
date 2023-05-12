<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Model;

class RetencionesStateTypes extends ModelTenant
{
    protected $table = 'retenciones_state_types';
    protected $fillable = [
        'idEstado',
        'name',
    ];
}

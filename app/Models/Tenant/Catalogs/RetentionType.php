<?php

namespace App\Models\Tenant\Catalogs;

use Hyn\Tenancy\Traits\UsesTenantConnection;

class RetentionType extends ModelCatalog
{
    use UsesTenantConnection;

    protected $table = "cat_retention_types";
    public $incrementing = false;
    public $fillable = [
        'active',
        'percentage',
        'description',
        'code',
        'type_id',
        'code2',
    ];
}

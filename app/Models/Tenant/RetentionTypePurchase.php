<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;

class RetentionTypePurchase extends ModelTenant
{
    public $incrementing = false;
    public $timestamps = false;
    protected $table = 'cat_add_retention_types';


    public static function getDataApiApp()
    {
        $states = self::get();

        return $states->push([
            'id' => 'all',
            'description' => 'Todos',
        ]);
    }
}

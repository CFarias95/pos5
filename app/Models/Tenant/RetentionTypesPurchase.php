<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;

class RetentionTypesPurchase extends ModelTenant
{
    public $incrementing = false;
    public $timestamps = false;
    protected $table = 'cat_retention_types';
    protected $fillable = [
        'id',
        'active',
        'percentage',
        'description',
        'code',
        'type_id',
        'code2',
        'account_id'
    ];
    protected $casts = [
        'active' => 'bool',
    ];

    public static function getDataApiApp()
    {
        $states = self::get();

        return $states->push([
            'id' => 'all',
            'description' => 'Todos',
        ]);
    }
}

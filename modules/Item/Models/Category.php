<?php

namespace Modules\Item\Models;

use App\Models\Tenant\Item;
use App\Models\Tenant\ModelTenant;

class Category extends ModelTenant
{

    protected $fillable = [
        'id',
        'name',
        'parent_id',
        'parent_2_id',
        'parent_3_id',
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function scopeFilterForTables($query)
    {
        return $query->select('id', 'name')->orderBy('name');
    }

    public function getRowResourceApi()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'selected' => false,
        ];
    }


    /**
     *
     * Data para filtros - select
     *
     * @return array
     */
    public static function getDataForFilters()
    {
        return self::select(['id', 'name'])->orderBy('name')->get();
    }

}

<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Item\Models\ItemLotsGroup;

class ItemSupplyLot extends ModelTenant
{

    protected $fillable = [
        'item_supply_id',
        'item_supply_name',
        'lot_id',
        'lot_code',
        'production_id',
        'production_name',
        'quantity',
        'expiration_date',
        'unit_price',
    ];

    public function itemSupply()
    {
        return $this->belongsTo(ItemSupply::class);
    }
}


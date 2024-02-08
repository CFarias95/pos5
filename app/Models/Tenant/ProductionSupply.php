<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Modules\Production\Models\Production;

class ProductionSupply extends ModelTenant
{

    protected $table = 'production_supplies';
    protected $fillable = ['production_name','production_id', 'item_supply_id',  'item_supply_name', 'quantity', 'cost_per_unit', 'checked', 'item_supply_original_id'];
    protected $casts = ['checked' => 'bool'];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function itemSupply()
    {
        return $this->belongsTo(ItemSupply::class);
    }
}

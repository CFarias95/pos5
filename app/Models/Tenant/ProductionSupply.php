<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Modules\Production\Models\Production;

class ProductionSupply extends ModelTenant
{   

    protected $table = 'production_supplies';
    protected $fillable = ['production_name','production_id', 'item_supply_id',  'item_supply_name', 'quantity'];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function itemSupply()
    {
        return $this->belongsTo(ItemSupply::class);
    }
}

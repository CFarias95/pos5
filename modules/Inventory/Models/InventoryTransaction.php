<?php

namespace Modules\Inventory\Models;

use App\Models\Tenant\AccountMovement;
use App\Models\Tenant\Item;
use App\Models\Tenant\ModelTenant;

class InventoryTransaction extends ModelTenant
{

    public $incrementing = false;
    public $timestamps = false;

    protected $with = ['accounting'];
    protected $fillable = [
        'name',
        'type',
        'id',
        'cta_account',
    ];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function accounting(){

        return $this->belongsTo(AccountMovement::class, 'cta_account','id');
    }

}

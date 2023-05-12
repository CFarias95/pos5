<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Catalogs\UnitType;

/**
 * App\Models\Tenant\ItemUnitType
 *
 * @property-read \App\Models\Tenant\Item $item
 * @property-read UnitType $unit_type
 * @method static \Illuminate\Database\Eloquent\Builder|ItemUnitType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ItemUnitType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ItemUnitType query()
 * @mixin \Eloquent
 */
class ItemRate extends ModelTenant
{
    protected $with = ['unit_type','rate'];
    public $timestamps = false;
    protected $table = 'item_rate';
    protected $fillable = [
        'item_id',
        'unit_type_id',
        'rate_id',
        'price1',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit_type() {
        return $this->belongsTo(UnitType::class, 'unit_type_id');
    }
    public function rate() {
        return $this->belongsTo(Rate::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item() {
        return $this->belongsTo(Item::class);
    }


    /**
     * Retorna un standar de nomenclatura para el modelo
     *
     * @param int $decimal_units
     *
     * @return array
     */
    public function getCollectionData($decimal_units = 2){

        return [
            'id'            => $this->id,
            'item_id'       => $this->item_id,
            'unit_type_id'  => $this->unit_type_id,
            'rate_id'  => $this->rate_id,
            'price1'        => number_format($this->price1, $decimal_units, '.', ''),

        ];
    }

}

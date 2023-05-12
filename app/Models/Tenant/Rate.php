<?php

namespace App\Models\Tenant;

/**
 * App\Models\Tenant\PersonType
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tenant\Person[] $person
 * @property-read int|null $person_count
 * @method static \Illuminate\Database\Eloquent\Builder|PersonType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PersonType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PersonType query()
 * @mixin \Eloquent
 */
class Rate extends ModelTenant
{
    protected $fillable = [
        'id',
        'rate_name',
        'rate_start',
        'rate_end',
        'rate_offer'
    ];

    public function item_rate()
    {
        return $this->hasMany(ItemRate::class);
        //return $this->belongsToMany(ItemRate::class);

    }
    public function items()
    {
        return $this->belongsToMany(Item::class,'item_rate','item_id','rate_id')->withPivot('price1');
    }

}

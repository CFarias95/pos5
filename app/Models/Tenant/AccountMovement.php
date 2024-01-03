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
class AccountMovement extends ModelTenant
{
    protected $fillable = [
        'code',
        'description',
        'cost_center',
        'account_group_id'
    ];

    protected $casts = [
        'account_group_id'=> 'int',
        'cost_center' => 'bool',
    ];

    public function account_group()
    {
        return $this->belongsTo(AccountGroup::class);
    }
    public function account_entries()
    {
        return $this->hasMany(AccountingEntries::class);
    }

    public function scopeInUse($query,$id)
    {
        $datos= AccountingEntryItems::where('account_movement_id','=',$id)->count()>0?true:false;
        return $datos;
    }
}

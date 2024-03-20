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
class AccountingEntryItems extends ModelTenant

{
    protected $table='accounting_entry_items';
    public $timestamps = false;
    protected $with = ['account_movement'];
    protected $fillable = [
        'id',
        'accounting_entrie_id',
        'account_movement_id',
        'seat_line',
        'debe',
        'haber',
        'seat_cost',
        'user_id_audited',
        'user_id_reconciliation',
        'audited',
        'reconciliation',
        'reconciliation_date',
        'audit_date',
        'comment',
    ];

    protected $casts = [
        'audited' => 'bool',
        'reconciliation' => 'bool',
    ];

    public function account()
    {
        return $this->belongsTo(AccountingEntries::class,'accounting_entrie_id','id');
    }

    public function account_movement()
    {
        return $this->belongsTo(AccountMovement::class);
    }







}

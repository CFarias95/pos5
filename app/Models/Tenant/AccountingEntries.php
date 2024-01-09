<?php

namespace App\Models\Tenant;
use App\Models\Tenant\Catalogs\CurrencyType;
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
class AccountingEntries extends ModelTenant

{
    protected $table='accounting_entries';
    protected $with = ['user', 'person', 'type_account', 'items'];
    protected $fillable = [
        'id',
        'user_id',
        'seat',
        'seat_general',
        'seat_date',
        'types_accounting_entrie_id',
        'comment',
        'serie',
        'number',
        'total_debe',
        'total_haber',
        'revised1',
        'user_revised1',
        'revised2',
        'user_revised2',
        'currency_type_id',
        'doctype',
        'is_client',
        'external_id',
        'establishment_id',
        'establishment',
        'prefix',
        'filename',
        'person_id',
        'person',
        'document_id',
        'created_at',
        'updated_at',

    ];
    public function getEstablishmentAttribute($value)
    {
        return (is_null($value))?null:(object) json_decode($value);
    }

    public function setEstablishmentAttribute($value)
    {
        $this->attributes['establishment'] = (is_null($value))?null:json_encode($value);
    }
    public function getIdentifierAttribute()
    {
        return $this->prefix.'-'.$this->id;
    }

    public function getUrlPrintPdf($format = "a4")
    {
        return url("accounting-entries/print/{$this->external_id}/{$format}");
    }
    public function items()
    {
        return $this->hasMany(AccountingEntryItems::class,'accounting_entrie_id','id')->orderBy('haber','asc');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function type_account()
    {
        return $this->belongsTo(TypesAccountingEntries::class,'types_accounting_entrie_id','id');
    }

    public function getNumberFullAttribute()
    {
        return $this->prefix.'-'.$this->id;
    }

    public function getPersonAttribute($value)
    {
        return (is_null($value))?null:(object) json_decode($value);
    }

    public function setPersonAttribute($value)
    {
        $this->attributes['person'] = (is_null($value))?null:json_encode($value);
    }
    public function currency_type()
    {
        return $this->belongsTo(CurrencyType::class, 'currency_type_id');
    }

}

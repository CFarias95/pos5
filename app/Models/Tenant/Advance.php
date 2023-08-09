<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Model;

class Advance extends ModelTenant
{
    protected $table = 'advances';
    protected $with = ['person','methosType'];
    protected $fillable = [
        'id',
        'idMethodType',
        'id_payment',
        'reference',
        'idCliente',
        'valor',
        'observation',
        'is_supplier',
    ];

    public function person(){

        return $this->belongsTo(Person::class,'idCliente');
    }

    public function methosType(){

        return $this->belongsTo(PaymentMethodType::class,'idMethodType');
    }
}

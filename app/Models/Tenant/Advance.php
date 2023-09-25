<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Model;

class Advance extends ModelTenant
{
    protected $table = 'advances';
    protected $with = ['person','methosType','payment'];
    protected $fillable = [
        'id',
        'idMethodType',
        'id_payment',
        'reference',
        'idCliente',
        'valor',
        'observation',
        'is_supplier',
        'in_use',
    ];
    protected $casts = [
        'in_use'=>'boolean',
    ];

    public function person(){

        return $this->belongsTo(Person::class,'idCliente');
    }

    public function methosType(){

        return $this->belongsTo(PaymentMethodType::class,'idMethodType');
    }
    public function payment(){

        return $this->belongsTo(PaymentMethodType::class,'id_payment');
    }
}

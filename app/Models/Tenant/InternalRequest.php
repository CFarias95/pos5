<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class InternalRequest extends ModelTenant
{
    protected $with = ['user','manage'];
    protected $fillable = [
        'id',
        'user_id',
        'user_manage',
        'title',
        'description',
        'status',
        'phase',
        'confirmed',
    ];

    protected $casts = [
        'confirmed' => 'bool',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function manage()
    {
        return $this->belongsTo(User::class,'user_manage');
    }
}

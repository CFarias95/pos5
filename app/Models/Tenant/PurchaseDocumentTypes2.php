<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class PurchaseDocumentTypes2 extends ModelTenant
{
    //protected $primaryKey = 'idType';
    protected $table = 'cat_purchase_document_types2';
    protected $fillable = [
        'idType',
        'active',
        'short',
        'description',
        'DocumentTypeID',
        'accountant',
        'stock',
        'sign',
        'cost',
    ];
    protected $casts = [
        'sign' => 'bool',
        'stock' => 'bool',
        'cost' => 'bool',
        'accountant' => 'bool',
    ];

    public function getCollectionData()
    {
        $data = [
            'idType' => $this->idType,
            'short' => $this->short,
            'DocumentTypeID' => $this->DocumentTypeID,
            'active' => $this->active,
            'description' => $this->description,
            'accountant' => $this->accountant,
            'stock' => $this->stock,
            'sign' => $this->sign,
            'cost' => $this->cost,
        ];

        return $data;
    }
}

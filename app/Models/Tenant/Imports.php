<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Imports extends ModelTenant
{
    protected $table = 'import';
    protected $fillable = [
        'id',
        'numeroImportacion',
        'tipoTransporte',
        'fechaEmbarque',
        'fechaLlegada',
        'estado',
        'cuenta_contable',
    ];

    public function getCollectionData()
    {
        $data = [
            'id' => $this->id,
            'numeroImportacion' => $this->numeroImportacion,
            'tipoTransporte'=> $this->tipoTransporte,
            'fechaEmbarque'=> $this->fechaEmbarque,
            'fechaLlegada'=>$this->fechaLlegada,
            'estado' => $this->estado,
            'cuenta_contable' => $this->cuenta_contable,
        ];
        return $data;
    }
}

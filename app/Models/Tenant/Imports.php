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
        'cta_isd',
        'cta_comunications',
        'isd',
        'comunications',
        'incoterm'
    ];
    protected $casts = [
        'comunications' => 'double',
        'isd' => 'double',
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
            'cta_isd' => $this->cta_isd,
            'cta_comunications' => $this->cta_comunications,
            'isd' => $this->isd,
            'comunications' => $this->comunications,
            'incoterm' => $this->incoterm,
        ];
        return $data;
    }
}

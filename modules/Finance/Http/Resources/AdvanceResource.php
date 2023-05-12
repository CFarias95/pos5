<?php

namespace Modules\Finance\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdvanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'idMethodType' => $this->idMethodType,
            'id_payment' => $this->id_payment,
            'reference' =>$this->reference,
            'idCliente' => $this->idCliente,
            'is_supplier' => (bool) $this->is_supplier,
            'valor' => $this->valor,
            'observation' => ($this->observation) ? $this->observation : '',

        ];
    }
}

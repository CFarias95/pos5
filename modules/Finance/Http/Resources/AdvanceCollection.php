<?php

namespace Modules\Finance\Http\Resources;

use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Person;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AdvanceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {

            $method = PaymentMethodType::find($row->idMethodType);
            $cliente = Person::find($row->idCliente);

            return [
                'id' => $row->id,
                'method' => ($method && $method->count() > 0 ) ? $method->description:'Sin metodo de pago',
                'cliente' => ($cliente && $cliente->count() > 0 ) ? $cliente->name: 'Sin cliente',
                'valor' => $row->valor,
                'is_supplier' => (bool) $row->is_supplier,
                'observation' => ($row->observation) ? $row->observation : '',
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
            ];
        });
    }

}

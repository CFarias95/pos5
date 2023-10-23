<?php

namespace Modules\Finance\Http\Resources;

use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Person;
use App\Models\Tenant\PurchasePayment;
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

            $usadoD = DocumentPayment::whereIn('payment_method_type_id',[14,15])->where('reference',$row->id)->sum('payment');
            $usadoP = PurchasePayment::whereIn('payment_method_type_id',[14,15])->where('reference',$row->id)->sum('payment');

            return [
                'id' => $row->id,
                'method' => ($method && $method->count() > 0 ) ? $method->description:'Sin metodo de pago',
                'cliente' => ($cliente && $cliente->count() > 0 ) ? $cliente->name: 'Sin cliente',
                'valor' => $row->valor,
                'is_supplier' => (bool) $row->is_supplier,
                'in_use' => $row->in_use,
                'used' => $usadoD+$usadoP,
                'free' => $row->valor-$usadoD-$usadoP,
                'observation' => ($row->observation) ? $row->observation : '',
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
            ];
        });
    }

}

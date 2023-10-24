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

            $purchases = PurchasePayment::select('purchase_payments.id','purchase_payments.payment','purchases.series','purchases.number')->whereIn('purchase_payments.payment_method_type_id',[14,15])->where('reference',$row->id)->join('purchases', function($join){
                $join->on('purchases.id','purchase_payments.purchase_id');
            });
            $documents = DocumentPayment::select('document_payments.id','document_payments.payment','documents.series','documents.number')->whereIn('document_payments.payment_method_type_id',[14,15])->where('reference',$row->id)->join('documents', function($join){
                $join->on('documents.id','document_payments.document_id');
            });

            $docs = $purchases ->union($documents)->get();

            return [
                'id' => $row->id,
                'method' => ($method && $method->count() > 0 ) ? $method->description:'Sin metodo de pago',
                'cliente' => ($cliente && $cliente->count() > 0 ) ? $cliente->name: 'Sin cliente',
                'valor' => $row->valor,
                'is_supplier' => (bool) $row->is_supplier,
                'in_use' => $row->in_use,
                'used' => $usadoD+$usadoP,
                'free' => round($row->valor-$usadoD-$usadoP,2),
                'observation' => ($row->observation) ? $row->observation : '',
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
                'documents' => $docs,
            ];
        });
    }

}

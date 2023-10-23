<?php

namespace Modules\Account\Http\Resources;

use App\Models\Tenant\AccountingEntries;
use App\Models\Tenant\Advance;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\PurchasePayment;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AuditCollection extends ResourceCollection
{
    public function toArray($request) {


        return $this->collection->transform(function(AccountingEntries $row, $key) {

            $pago = $row->document_id;

            if(str_contains($pago,"CF")){
                $pagoC = DocumentPayment::find(preg_replace("/[a-zA-Z]/", "", $pago));
            }
            if(str_contains($pago,"PC")){
                $pagoC = PurchasePayment::find(preg_replace("/[a-zA-Z]/", "", $pago));
            }

            if($pagoC && ($pagoC->payment_method_type_id == '14' || $pagoC->payment_method_type_id == '15')){
                $reference = $pagoC->reference;
                $advance = Advance::find($reference);
                $referenceP = $advance->reference;
            }

            foreach ($row->items as $value) {
                if($value->haber > 0){
                    $ctaHaber = $value->account_movement->account_group_id.$value->account_movement->code." - ".$value->account_movement->description;
                }
                if($value->debe > 0){
                    $ctaDebe = $value->account_movement->account_group_id.$value->account_movement->code." - ".$value->account_movement->description;
                }
            }

            $data['comment'] = $row->comment;
            $data['reference'] = (isset($referenceP))?$referenceP:(is_null($pagoC)?'':$pagoC->reference);
            $data['date'] = $row->seat_date;
            $data['value'] = $row->total_debe;
            $data['id'] = $row->id;

            $data['ctaDebe'] = $ctaDebe;
            $data['ctaHaber'] = $ctaHaber;
            $data['audited'] = $row->revised2;
            $data['reconciliated'] = $row->revised1;
            //$data['row'] = $row;
            return $data;
        });
    }
}

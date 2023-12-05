<?php

namespace Modules\Account\Http\Resources;

use App\Models\Tenant\AccountingEntries;
use App\Models\Tenant\AccountMovement;
use App\Models\Tenant\Advance;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\PurchasePayment;
use App\Models\Tenant\Retention;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AuditCollection extends ResourceCollection
{
    public function toArray($request) {

        return $this->collection->transform(function(AccountingEntries $row, $key) {

            $account_movement = AccountMovement::find($row->account_movement_id);
            $account_entry = AccountingEntries::find($row->accounting_entrie_id);

            $pago = $account_entry->document_id;
            $pagoC = null;

            if(str_contains($pago,"CF")){
                $pagoC = DocumentPayment::find(preg_replace("/[a-zA-Z]/", "", $pago));
            }
            if(str_contains($pago,"PC")){
                $pagoC = PurchasePayment::find(preg_replace("/[a-zA-Z]/", "", $pago));
            }

            if($pagoC && ($pagoC->payment_method_type_id == '14' || $pagoC->payment_method_type_id == '15')){
                $reference = $pagoC->reference;
                $advance = Advance::find($reference);
                $referenceP = 'A,'.$advance->reference;
            }

            if($pagoC && $pagoC->payment_method_type_id == '99'){
                $reference = $pagoC->reference;
                $retention = Retention::find($reference);
                $referenceP = ($retention)?'R,'.$retention->ubl_version:null;
            }

            if($row->haber > 0){
                $ctaHaber = $account_movement->account_group_id.$account_movement->code." - ".$account_movement->description;
            }
            if($row->debe > 0){
                $ctaDebe = $account_movement->account_group_id.$account_movement->code." - ".$account_movement->description;
            }


            $data['comment'] = $account_entry->comment;
            $data['reference'] = (isset($referenceP))?$referenceP:(is_null($pagoC)?'':$pagoC->reference);
            $data['date'] = $account_entry->seat_date;
            $data['value'] = round($row->debe + $row->haber,2);
            $data['id'] = $row->id;

            $data['ctaDebe'] = (isset($ctaDebe))?$ctaDebe:'';
            $data['ctaHaber'] = (isset($ctaHaber))?$ctaHaber:'';
            $data['reconciliated'] = $row->reconciliation;
            $data['audited'] = $row->audited;
            //$data['row'] = $row;
            return $data;
        });
    }
}

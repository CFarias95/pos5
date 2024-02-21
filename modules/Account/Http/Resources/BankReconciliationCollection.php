<?php

namespace Modules\Account\Http\Resources;

use App\Models\Tenant\AccountingEntries;
use App\Models\Tenant\AccountMovement;
use App\Models\Tenant\Advance;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\PurchasePayment;
use App\Models\Tenant\Retention;
use App\Models\Tenant\User;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Account\Models\BankReconciliation;

class BankReconciliationCollection extends ResourceCollection
{
    public function toArray($request) {

        return $this->collection->transform(function(BankReconciliation $row, $key) {

            $account_movement = AccountMovement::find($row->account_id);
            $user = User::find($row->user_id);
            $status = 'Creada';

            if($row->status == true){
                $status = 'Cerrada';
            }
            $data = null;
            $data['id'] = $row->id;
            $data['initial_value'] = $row->initial_value;
            $data['total_haber'] = $row->total_haber;
            $data['total_debe'] = $row->total_debe;
            $data['diference_value'] = $row->diference_value;
            $data['status'] = $status;
            $data['user_id'] = $user->name;
            $data['account_id'] = $account_movement->description;
            $data['month'] = substr($row->month,0,-3);
            return $data;
        });
    }
}

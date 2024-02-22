<?php

namespace Modules\Purchase\Http\Resources;

use App\Models\Tenant\AccountMovement;
use App\Models\Tenant\RetentionTypePurchase;
use App\Models\Tenant\RetentionTypesPurchase;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PurchaseRetentionsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function(RetentionTypesPurchase $row, $key) {
            $tipo = RetentionTypePurchase::find($row->type_id);
            $account = AccountMovement::find($row->account_id);
            return [
                'id' => $row->id,
                'active' => $row->active ? 'SI' : 'NO',
                'description' => $row->description,
                'code' => $row->code,
                'percentage' => $row->percentage,
                'type' => $tipo->description,
                'account' => $account ? $account->code.'  - '.$account->description : '',
            ];
        });
    }
}

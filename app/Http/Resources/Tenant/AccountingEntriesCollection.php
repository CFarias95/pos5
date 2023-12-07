<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Account\Models\CostCenter;

class AccountingEntriesCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            return [
                'id' => $row->id,
                'user_id' => $row->user_id,
                'type' => $row->type_account->name,
                'user' => $row->user->name,
                'seat' => $row->seat,
                'seat_general' => $row->seat_general,
                'seat_date' => $row->seat_date,//->format('Y-m-d'),
                'types_accounting_entrie_id' => $row->types_accounting_entrie_id,
                'comment' => $row->comment,
                'serie' => $row->serie,
                'filename' => $row->filename,
                'external_id' => $row->external_id,
                'number' => $row->number,
                'total_debe' => $row->total_debe,
                'total_haber' => $row->total_haber,
                'revised1' => $row->revised1,
                'user_revised1' => $row->user_revised1,
                'revised2' => $row->revised2,
                'user_revised2' => $row->user_revised2,
                'currency_code' => $row->currency_code,
                'doctype' => $row->doctype,
                'is_client' => $row->is_client,
                'person_id' => $row->person_id,
                'created_at' => $row->created_at->format('d/m/Y H:i:s'),
                'updated_at' => $row->updated_at->format('d/m/Y H:i:s'),
                'detalles'=>$row->items->transform(function($row){
                    $data = $row;
                    if($data['seat_cost']){
                        $seat = CostCenter::find($data['seat_cost']);
                        $data['seat_cost'] = $seat->name;
                    }
                    return $row;

                }),
            ];
        });
    }
}

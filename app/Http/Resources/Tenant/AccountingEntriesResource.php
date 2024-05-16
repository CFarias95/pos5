<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountingEntriesResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
            return [
                'id' => $this->id,
                'user_id' => $this->user_id,
                'type' => $this->type_account->name,
                'user' => $this->user->name,
                'seat' => $this->seat,
                'seat_general' => $this->seat_general,
                'seat_date' => $this->seat_date,//->format('Y-m-d'),
                'types_accounting_entrie_id' => $this->types_accounting_entrie_id,
                'comment' => $this->comment,
                'serie' => $this->serie,
                'filename' => $this->filename,
                'number' => $this->number,
                'external_id' => $this->external_id,
                'identifier' => $this->identifier,
                'total_debe' => $this->total_debe,
                'total_haber' => $this->total_haber,
                'revised1' => $this->revised1,
                'user_revised1' => $this->user_revised1,
                'revised2' => $this->revised2,
                'user_revised2' => $this->user_revised2,
                'currency_code' => $this->currency_code,
                'doctype' => $this->doctype,
                'is_client' => $this->is_client,
                'person_id' => $this->person_id,
                'created_at' => $this->created_at->format('d/m/Y H:i:s'),
                'updated_at' => $this->updated_at->format('d/m/Y H:i:s'),
                'detalles'=> $this->items,
            ];


    }
}

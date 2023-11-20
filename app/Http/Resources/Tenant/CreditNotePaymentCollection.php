<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Document;
use App\Models\Tenant\Person;
use App\Models\Tenant\Purchase;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CreditNotePaymentCollection extends ResourceCollection
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

            $tipo = "Nota de Crédito Venta";
            $document = null;

            if($row->purchase_id){
                $tipo = "Nota de crédito Compra";
                $document = Purchase::find($row->purchase_id);
            }elseif(isset($row->document_id)){
                $document = Document::find($row->document_id);
            }

            $person = Person::find($row->user_id);

            return [
                'id' => $row->id,
                'type' => $tipo,
                'date_of_issue' => $document->date_of_issue->format('y-m-d'),
                'person' => $person->name,
                'person_number' =>$person->number,
                'document' => $document->series.' - '.$document->number,
                'total' =>$row->amount,
                'in_use'=>$row->in_use,
                'used' => $row->used,
            ];
        });
    }
}

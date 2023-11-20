<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Person;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RetentionCollection extends ResourceCollection
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

            $has_cdr = false;

            if (in_array($row->state_type_id, ['05', '07', '09'])) {
                $has_cdr = false;
            }
            $person = Person::find($row->supplier_id);

            return [
                'id' => $row->id,
                'date_of_issue' => $row->date_of_issue->format('Y-m-d'),
                'number' => $row->number_full,
                'secuencial' => $row->ubl_version,
                'clave_acceso' => $row->observations,
                'supplier_name' => $person->name,
                'supplier_number' => $person->identity_document_type->description.' '.$person->number,
                'state_type_id' => $row->state_type_id,
                'state_type_description' => $row->state_type->description,
                'total_retention' => $row->total_retention,
                'total' => $row->total,
                'has_xml' => $row->has_xml,
                'has_pdf' => $row->has_pdf,
                'has_cdr' => $has_cdr,
                'in_use' =>$row->in_use,
                'total_used' =>($row->total_used)?$row->total_used:0,
                'download_external_xml' => $row->download_external_xml,
                'download_external_pdf' => $row->download_external_pdf,
                'download_external_cdr' => $row->download_external_cdr,
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
            ];
        });
    }
}

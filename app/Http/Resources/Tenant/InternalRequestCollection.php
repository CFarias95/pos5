<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\EmailSendLog;
use Illuminate\Http\Resources\Json\ResourceCollection;

class InternalRequestCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request) {
        return $this->collection->transform(function(\App\Models\Tenant\InternalRequest $row, $key) {

            return [
                'id' => $row->id,
                'numeroImportacion' => $row->numeroImportacion,
                'tipoTransporte' => $row->tipoTransporte,
                'fechaEmbarque' => (!is_null($row->fechaEmbarque)) ? $row->fechaEmbarque:null,
                'fechaLlegada' => (!is_null($row->fechaLlegada)) ? $row->fechaLlegada:null,
                'estado'=>$row->estado,
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),

            ];
        });
    }

}

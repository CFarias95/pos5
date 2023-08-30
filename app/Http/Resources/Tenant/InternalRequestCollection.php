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
                'title' => $row->title,
                'text' => $row->description,
                'phase' => ($row->phase)?$row->phase:'',
                'status' => $row->status,
                'aproved'=>$row->confirmed,
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),

            ];
        });
    }

}

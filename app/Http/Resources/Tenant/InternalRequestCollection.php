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

            $autID= auth()->user()->id;
            $authType = auth()->user()->type;

            return [
                'id' => $row->id,
                'title' => $row->title,
                'text' => $row->description,
                'phase' => ($row->phase)?$row->phase:'Solicitud Creada',
                'status' => $row->status,
                'aproved'=>$row->confirmed,
                'user' => $row->user->name,
                'manage' => $row->manage->name,
                'is_manager'=>( $authType == 'admin' || $autID == $row->user_manage)?true:false,
                'is_user' => ( $authType == 'admin' || $autID == $row->user_id)?true:false,
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),

            ];
        });
    }

}

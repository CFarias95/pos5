<?php

namespace Modules\Sale\Http\Resources;

use App\Models\Tenant\SriFormasPagos;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PaymentMethodTypeCollection extends ResourceCollection
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

            /** @var \App\Models\Tenant\PaymentMethodType  $row */
            $show_actions = true;
            /*
            if(in_array($row->id, ['01', '05', '08', '09', '11', '12', '13'])){
                $show_actions = false;
            }
            */
            $sriDescription = SriFormasPagos::where('code',$row->pago_sri)->get();
            $desciptionSRI = '';
            $return = $row->toArray();
            $return['show_actions'] = $show_actions;
            if($sriDescription->count() > 0 ){
                foreach($sriDescription as $desc){
                    $desciptionSRI = $desc->description;
                }
            }
            $return['sri_desciption'] = $desciptionSRI;
            return $return;

            return [
                'id' => $row->id,
                'description' => $row->description,
                'show_actions' => $show_actions,
                'sri_desciption' => $row->pago_sri
            ];
        });
    }
}

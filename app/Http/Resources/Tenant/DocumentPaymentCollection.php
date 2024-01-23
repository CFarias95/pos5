<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Advance;
use App\Models\Tenant\Retention;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DocumentPaymentCollection extends ResourceCollection
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
            $referencia = '';
            if($row->payment_method_type_id == '99'){
                $retention = Retention::find($row->reference);
                $referencia = 'R'.$retention->ubl_version.'/'.$retention->series.$retention->number;
            }

            if($row->payment_method_type_id == '14' || $row->payment_method_type_id == '15'){
                $advance = Advance::find($row->reference);
                $referencia = 'AT'.$advance->id;
            }

            return [
                'id' => $row->id,
                'date_of_payment' => $row->date_of_payment->format('d/m/Y'),
                'payment_method_type_description' => $row->payment_method_type->description,
                'destination_description' => ($row->global_payment) ? $row->global_payment->destination_description:null,
                'reference' => ($referencia != '')?$referencia:$row->reference,
                'filename' => ($row->payment_file) ? $row->payment_file->filename:null,
                'payment' => $row->payment,
                'payment_received' => $row->payment_received,
                'payment_received_description' => $row->getPaymentReceivedDescription(),
                'postdated' => ($row->postdated)?$row->postdated->format('d/m/Y'):'Sin Postfechar',
                'multi_pay' => ($row->multipay)?$row->multipay:'NO',
            ];
        });
    }
}

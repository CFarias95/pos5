<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Catalogs\RetentionType;
use App\Models\Tenant\RetencionesStateTypes;
use App\Models\Tenant\RetentionsDetailEC;
use App\Models\Tenant\RetentionsEC;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

/**
 * Class PurchaseCollection
 *
 * @package App\Http\Resources\Tenant
 */
class PurchaseCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Collection
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {

            /** @var \App\Models\Tenant\Purchase  $row */
            return $row->getCollectionData();
            /** Pasado al modelo */
            $total = $row->total;
            if($row->total_perception)
            {
                $total += round($row->total_perception, 2);
            }

            $retencionesID = RetentionsEC::where('idDocumento',$this->id)->get();
            $idRetentionsState = '';
            $nameRetentionsState = '';
            $fileRetentions = '';
            $idRetentions = '';
            $retencoinesArray = [];

            if($retencionesID){
                //Log::info(json_encode($retencionesID));
                $retencoinesArray =  RetentionsDetailEC::where('idRetencion',$retencionesID->idRetencion)->get();
                $idRetentionsState = ( $retencionesID->count() > 0 )? $retencionesID[0]->status_id:'';
                $estados = RetencionesStateTypes::where('idEstado',$retencionesID[0]->status_id)->get();
                $nameRetentionsState = $estados;
                $fileRetentions = $retencionesID[0]->claveAcceso;
                $idRetentions = $retencionesID[0]->id;

            }

            return [
                'id' => $row->id,
                'document_type_description' => $row->document_type->description,
                'group_id' => $row->group_id,
                'soap_type_id' => $row->soap_type_id,
                'date_of_issue' => $row->date_of_issue->format('Y-m-d'),
                'date_of_due' => ($row->date_of_due) ? $row->date_of_due->format('Y-m-d'):'-',
                'number' => $row->number_full,
                'supplier_name' => $row->supplier->name,
                'supplier_number' => $row->supplier->number,
                'currency_type_id' => $row->currency_type_id,
                'total_exportation' => $row->total_exportation,
                'total_free' => number_format($row->total_free, 2, ".",""),
                'total_unaffected' => number_format($row->total_unaffected, 2, ".",""),
                'total_exonerated' => number_format($row->total_exonerated, 2, ".",""),
                'total_taxed' => number_format($row->total_taxed, 2, ".",""),
                'total_igv' => number_format($row->total_igv, 2, ".",""),
                'total_perception' => number_format($row->total_perception, 2, ".",""),
                'total' => number_format($total, 2, ".",""),
                'state_type_id' => $row->state_type_id,
                'state_type_description' => $row->state_type->description,
                'state_type_payment_description' => $row->total_canceled ? 'Pagado':'Pendiente de pago',
                // 'payment_method_type_description' => isset($row->purchase_payments['payment_method_type']['description'])?$row->purchase_payments['payment_method_type']['description']:'-',
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
                'payments' => $row->purchase_payments->transform(function($row, $key) {
                    return [
                        'id' => $row->id,
                        'payment_method_type_description' => $row->payment_method_type->description,
                        'reference' => $row->reference,
                        'payment' => $row->payment,
                        'payment_method_type_id' => $row->payment_method_type_id,
                    ];
                }),
                'items' => $row->items->transform(function($row, $key) {
                    return [
                        'key' => $key + 1,
                        'id' => $row->id,
                        'description' => $row->item->description,
                        'quantity' => round($row->quantity,2)
                    ];
                }),
                'retenciones' => $retencoinesArray->each(function($row, $key) {
                    $catType = RetentionType::where('code',$row->code)->get();
                    $tipo = 'RENTA';
                    if($catType->type_id == '02'){
                        $tipo = 'IVA';
                    }
                    return [
                        'key' => $key + 1,
                        'type' => $tipo,
                        'description' => $catType->description,
                        'value' => round($row->valorRet,2)
                    ];
                }),
                'retenciones_state_id' => $idRetentionsState,
                'retenciones_id' => $idRetentions,
                'retenciones_state_name' => $nameRetentionsState,
                'retenciones_unique_name' => $fileRetentions,
                'suplier_email' => $row->supplier->email,
                'print_a4' => url('')."/purchases/print/{$row->external_id}/a4",
            ];
        });
    }

}

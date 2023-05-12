<?php

namespace Modules\Report\Http\Resources;

use App\Models\Tenant\Purchase;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use Modules\Purchase\Models\PurchaseOrder;
use Modules\Purchase\Models\PurchaseOrderItem;

/**
 * Class PurchaseCollection
 *
 * @package App\Http\Resources\Tenant
 */
class PurchaseOrderCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Collection
     */
    public function toArray($request)
    {
        $ordernC = $request->ordernC;

        return $this->collection->transform(function($row, $key){

            $purchaseOrder = Purchase::findOrFail($row->purchase_id);
            $orderItem = PurchaseOrderItem::where('purchase_order_id',$purchaseOrder->purchase_order_id)->where('item_id',$row->item_id)->get();

            $data['itemId'] = $row->item_id;
            $data['itemDescription'] = $row->item->description;
            $data['unitValue'] = round($row->unit_value,2);
            $data['purchaseQuantity'] = floatval($row->quantity);
            $data['purchaseOrderQuantity'] = $orderItem[0]->quantity;
            $data['dif'] = round($orderItem[0]->quantity - floatval($row->quantity),2);
            //$data['purchaseOrder'] = $purchaseOrder;

            return $data;

        });
    }

}

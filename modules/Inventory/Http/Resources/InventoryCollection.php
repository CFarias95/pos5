<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Log;

class InventoryCollection extends ResourceCollection
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
            //Log::info('row'.$row);
            return [
                'id' => $row->id,
                'item_internal_id' => $row->item->internal_id,
                'item_description' => $row->item->description,
                'item_fulldescription' => ($row->item->internal_id) ? "{$row->item->internal_id} / {$row->item->name} / {$row->item->description}" :$row->item->description,
                'warehouse_description' => $row->warehouse->description,
                'purchase_mean_cost' => $row->item->purchase_mean_cost,
                'stock' => $row->stock,
                'lots_enabled' => $row->item->lots_enabled,
                'category_id' => $row->item->category_id,
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
            ];
        });
    }
}

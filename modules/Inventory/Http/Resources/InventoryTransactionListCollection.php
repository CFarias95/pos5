<?php
namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class InventoryTransactionListCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {


            return [
                'id' => $row->id,
                'name' => $row->name,
                'visible' => $row->visible,
            ];
        });
    }
}

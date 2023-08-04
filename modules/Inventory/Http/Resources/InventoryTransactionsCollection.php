<?php
namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class InventoryTransactionsCollection extends ResourceCollection
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
                'type' => $row->type,
                'ctaCountant' => ($row->accounting)?$row->accounting->code.'-'.$row->accounting->description:'',
            ];
        });
    }
}

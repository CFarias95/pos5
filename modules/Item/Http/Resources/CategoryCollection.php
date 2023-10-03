<?php

namespace Modules\Item\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Item\Models\Category;

class CategoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function (Category $row, $key) {

            if($row->parent_id){
                $nombre1 = Category::findOrFail($row->parent_id)->name;
            }

            if($row->parent_2_id){
                $nombre2 = Category::findOrFail($row->parent_2_id)->name;
            }

            if($row->parent_3_id){
                $nombre3 = Category::findOrFail($row->parent_3_id)->name;
            }

            return [
                'id' => $row->id,
                'name' =>(isset($nombre1)?$nombre1.'-':'').(isset($nombre2)?$nombre2.'-':'').(isset($nombre3)?$nombre3.'-':'').$row->name,
                'created_at' => ($row->created_at) ? $row->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => ($row->updated_at) ? $row->updated_at->format('Y-m-d H:i:s') : null,
            ];
        });
    }
}

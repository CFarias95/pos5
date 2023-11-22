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

            if ($row->parent_id) {
                $nombre1 = Category::findOrFail($row->parent_id)->name;
            }

            if ($row->parent_2_id) {
                $nombre2 = Category::findOrFail($row->parent_2_id)->name;
            }

            if ($row->parent_3_id) {
                $nombre3 = Category::findOrFail($row->parent_3_id)->name;
            }

            $records1 = Category::where('parent_id', $row->id)->where('parent_2_id', null)->get();

            return [
                'id' => $row->id,
                'name' => $row->name,
                'created_at' => ($row->created_at) ? $row->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => ($row->updated_at) ? $row->updated_at->format('Y-m-d H:i:s') : null,
                'children' => $records1->transform(function ($data1) use ($row) {

                    $records2 = Category::where('parent_id', $row->id)->where('parent_2_id', $data1->id)->where('parent_3_id', null)->get();
                    return [
                        'id' => $data1->id,
                        'name' => $data1->name,
                        'created_at' => ($data1->created_at) ? $data1->created_at->format('Y-m-d H:i:s') : null,
                        'updated_at' => ($data1->updated_at) ? $data1->updated_at->format('Y-m-d H:i:s') : null,
                        'children' => $records2->transform(function ($data2) use ($row, $data1) {

                            $records3 = Category::where('parent_id', $row->id)->where('parent_2_id', $data1->id)->where('parent_3_id', $data2->id)->get();
                            return [
                                'id' => $data2->id,
                                'name' => $data2->name,
                                'created_at' => ($data2->created_at) ? $data2->created_at->format('Y-m-d H:i:s') : null,
                                'updated_at' => ($data2->updated_at) ? $data2->updated_at->format('Y-m-d H:i:s') : null,
                                'children' => $records3->transform(function ($data3) {
                                    return [
                                        'id' => $data3->id,
                                        'name' => $data3->name,
                                        'created_at' => ($data3->created_at) ? $data3->created_at->format('Y-m-d H:i:s') : null,
                                        'updated_at' => ($data3->updated_at) ? $data3->updated_at->format('Y-m-d H:i:s') : null,
                                    ];
                                })
                            ];
                        })
                    ];
                }),
            ];
        });
    }
}

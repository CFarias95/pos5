<?php

namespace Modules\Dashboard\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DashboardVentasVendedorSPCollection extends ResourceCollection
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
            return [ $row ];
        });
    }
}
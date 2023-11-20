<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Models\{
    Warehouse,
    ItemWarehouse,
};
use Modules\Item\Models\{
    Brand,
    Category,
};
use App\Models\Tenant\{
    Establishment,
    CatItemSize,
    Company,
};
use App\Models\Tenant\Catalogs\{
    AttributeType,
    CatColorsItem
};
use App\Exports\GeneralFormatExport;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Log;

class InventoryReviewController extends Controller
{

    public function index()
    {
        return view('inventory::inventory-review.index');
    }


    public function filters()
    {
        $warehouses = Warehouse::getDataForFilters();
        $categories = Category::getDataForFilters();
        $item_sizes = CatItemSize::get();
        $item_colors = CatColorsItem::get();
        $brands = Brand::get();
        $attributes = AttributeType::get();

    return compact('warehouses', 'categories', 'item_sizes', 'item_colors', 'brands', 'attributes');
    }


    /**
     *
     * @param  Request $request
     * @return array
     */
    public function records(Request $request)
    {
        //Log::info($request);
        $filter_by_variants = $request->has('filter_by_variants') && $request->filter_by_variants === 'true';

        $records = ItemWarehouse::with([
                        'item' => function($q){
                            $q->whereFilterWithOutRelations();
                        }
                    ])
                    ->whereHas('item', function ($query) use ($request, $filter_by_variants) {

                        $query->whereIsNotService()->whereNotIsSet()->whereIsActive();

                        $category_id = $request->has('category_id') && $request->category_id;
                        if($category_id) $query->where('category_id', $request->category_id);

                        $brand_id = $request->has('brand_id') && $request->brand_id;
                        if($brand_id) $query->where('brand_id', $request->brand_id);
                        //Log::info(json_encode($query));

                        $attribute_id = $request->has('attribute_id') && $request->attribute_id;
                        if($attribute_id) $query->where('attributes','like', '%'.$request->attribute_id.'%');

                        $attribute_descr = $request->has('attribute_descr') && $request->attribute_descr;
                        if($attribute_id && $attribute_descr) $query->where('attributes','like','%'.$request->attribute_id.'%'.$request->attribute_descr.'%');
                        if($attribute_descr) $query->where('attributes','like','%'.$request->attribute_descr.'%');

                        // para variantes
                        if($filter_by_variants)
                        {
                            $query->whereHas('item_movement_rel_extra', function($query_rel_extra) use ($request) {

                                //$item_color_id = $request->item_color_id ?? false;
                                if(isset($request->item_color_id) && $request->item_color_id != ''){
                                    $query_rel_extra->where('item_color_id', $request->item_color_id);
                                }

                                //$item_size_id = $request->item_size_id ?? false;
                                if(isset($request->item_size_id) && $request->item_size_id != ''){
                                    $query_rel_extra->where('item_size_id', $request->item_size_id);
                                }

                                return $query_rel_extra;
                            });
                        }
                        // para variantes

                        return $query;
                    })
                    ->where('warehouse_id', $request->warehouse_id)
                    ->orderBy('item_id')
                    ->get()
                    ->transform(function($row, $index) use($filter_by_variants, $request){
                        Log::info($row);
                        return [
                            'index' => $index + 1,
                            'id' => $row->id,
                            'item_id' => $row->item_id,
                            'item_description' => $row->item->description,
                            'item_barcode' => $row->item->barcode,
                            'stock' => $row->stock,
                            'input_stock' => 0,
                            'difference' => null,
                            'stock_by_variants' => $filter_by_variants ? $row->item->getStockByVariantsInventoryReview($request->establishment_id) : null,
                            'attribute_types' => $row->item->attributes
                        ];
                    });
        Log::info($records);

        if($filter_by_variants)
        {
            $records = $this->transformDataForVariants($records, $request);
        }

        Log::info($records);

        return [
            'data' => $records
        ];
    }


    /**
     *
     * Transformar datos de los items encontrados para las variantes
     *
     * @param  array $records
     * @param  Request $request
     * @return array
     */
    private function transformDataForVariants($records, $request)
    {
        $data = [];
        $index = 0;

        $records->each(function($row) use(&$data, $index, $request){

            $colors = $row['stock_by_variants']['colors'] ?? null;
            $sizes = $row['stock_by_variants']['CatItemSize'] ?? null;

            if($colors)
            {
                if($this->isSetDataVariant($request) || ($request->item_color_id && !$request->item_size_id))
                {
                    $color = $colors['detailed']->filter(function ($value, $key) use($request){
                        return $value->rel_id == $request->item_color_id;
                    });

                    //$this->setDataToVariant($colors['detailed'], $row, $data, $index, 'Color');
                    $this->setDataToVariant($color, $row, $data, $index, 'Color');
                }
            }

            if($sizes)
            {
                if($this->isSetDataVariant($request) || (!$request->item_color_id && $request->item_size_id))
                {
                    $size = $sizes['detailed']->filter(function ($value, $key) use($request){
                        return $value->rel_id == $request->item_size_id;
                    });
                    //$this->setDataToVariant($sizes['detailed'], $row, $data, $index, 'Talla');
                    $this->setDataToVariant($size, $row, $data, $index, 'Talla');
                }
            }

        });

        return $data;
    }


    /**
     *
     * @param  Request $request
     * @return bool
     */
    private function isSetDataVariant($request)
    {
        return ($request->item_color_id && $request->item_size_id) || (!$request->item_color_id && !$request->item_size_id);
    }


    /**
     *
     * Asignar datos de las variante
     *
     * @param  array $data_detailed
     * @param  array $row
     * @param  array $data
     * @param  int $index
     * @return void
     */
    private function setDataToVariant($data_detailed, $row, &$data, &$index, $type)
    {
        if($data_detailed->count() > 0)
        {
            foreach ($data_detailed as $value)
            {
                $data [] = [
                    'index' => $index + 1,
                    'id' => $row['id'],
                    'item_id' => $row['item_id'],
                    'item_description' => $row['item_description']." - {$type}: ".$value->name,
                    'item_barcode' => $row['item_barcode'],
                    'stock' => (float) $value->total,
                    'input_stock' => 0,
                    'difference' => null,
                    'stock_by_variants' => null,
                ];
                $index++;
            }
        }
    }


    /**
     *
     * Exportar formato pdf/excel
     *
     * @param  Request $request
     * @return mixed
     */
    public function export(Request $request)
    {
        //Log::info($request);
        $this->initConfigurations();
        $format = $request->format;
        $records = $request->records;
        //Log::info(json_encode($records));
        $company = Company::getDataForReportHeader();
        $data = compact('records', 'company');
        $view = 'inventory::inventory-review.exports.general_format';
        $filename = 'Revision_stock_'.Carbon::now().".{$format}";

        if($format === 'pdf')
        {
            $pdf = PDF::loadView($view, $data);
            $export = $pdf->download($filename);
        }
        else
        {
            $general_format_export= new GeneralFormatExport();
            $general_format_export->data($data)->view_name($view);
            $export = $general_format_export->download($filename);
        }

        return $export;
    }


    /**
     *
     * @return void
     */
    private function initConfigurations()
    {
        ini_set('memory_limit', '4026M');
        ini_set("pcre.backtrack_limit", "5000000");
    }

}

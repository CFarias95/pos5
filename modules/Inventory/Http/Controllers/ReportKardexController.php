<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Barryvdh\DomPDF\Facade as PDF;
use Modules\Inventory\Exports\KardexExport;
use Illuminate\Http\Request;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Company;
use App\Models\Tenant\Kardex;
use App\Models\Tenant\Item;
use Carbon\Carbon;
use Modules\Inventory\Models\InventoryKardex;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Http\Resources\ReportKardexCollection;
use Modules\Inventory\Http\Resources\ReportKardexLotsCollection;

use Modules\Inventory\Models\ItemWarehouse;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Item\Models\ItemLot;

use Modules\Inventory\Http\Resources\ReportKardexLotsGroupCollection;
use Modules\Inventory\Http\Resources\ReportKardexItemLotCollection;
use Modules\Inventory\Models\Devolution;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\Warehouse as TenantWarehouse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Http\Resources\ReportKardexCollection2;

class ReportKardexController extends Controller
{
    protected $models = [
        "App\Models\Tenant\Document",
        "App\Models\Tenant\Purchase",
        "App\Models\Tenant\PurchaseSettlement",
        "App\Models\Tenant\SaleNote",
        "Modules\Inventory\Models\Inventory",
        "Modules\Order\Models\OrderNote",
        Devolution::class,
        Dispatch::class
    ];

    public function index()
    {
        return view('inventory::reports.kardex.index');
    }


    public function filter()
    {
        $warehouses = [];
        $user = User::query()->find(auth()->id());
        if ($user->type === 'admin') {
            $warehouses[] = [
                'id' => 'all',
                'name' => 'Todos'
            ];
            $records = Warehouse::query()
                ->get();
        } else {
            $records = Warehouse::query()
                ->where('establishment_id', $user->establishment_id)
                ->get();
        }

        foreach ($records as $record) {
            $warehouses[] = [
                'id' => $record->id,
                'name' => $record->description,
            ];
        }

        return [
            'warehouses' => $warehouses
        ];
    }

    public function filterByWarehouse($warehouse_id)
    {
        $query = Item::query()->whereNotIsSet()
            ->with('warehouses')
            ->where([['item_type_id', '01'], ['unit_type_id', '!=', 'ZZ']]);

        if ($warehouse_id !== 'all') {
            $query->whereHas('warehouses', function ($query) use ($warehouse_id) {
                return $query->where('warehouse_id', $warehouse_id);
            });
        }

        $items = $query->latest()
            ->get()
            ->transform(function ($row) {
                $full_description = $this->getFullDescription($row);
                $nombre = $row->name?$row->name:'-';
                $descripcion = $row->description?$row->description:'-';
                $model = $row->model?$row->model:'-';
                $referencia = ($row->internal_id)?$row->internal_id:'-';
                $fac = ($row->factory_code)?$row->factory_code:'-';
                return [

                    'id' => $row->id,
                    'full_description' => $nombre.'/'.$descripcion.'/'.$model.'/'.$referencia.'/'.$fac,
                    'internal_id' => $row->internal_id,
                    'description' => $row->name,
                    'warehouses' => $row->warehouses
                ];
            });

        return [
            'items' => $items
        ];
    }

    public function records(Request $request)
    {

        $records = $this->getDatBySp($request->all());
        //Log::info($records);
        //return new ReportKardexCollection($records->paginate(config('tenant.items_per_page')));
        $collection = collect($records);
        $per_page = (config('tenant.items_per_page'));
        $page = request()->query('page') ?? 1;
        $paginatedItems = $collection->slice(($page - 1) * $per_page, $per_page)->all();
        $paginatedCollection = new LengthAwarePaginator($paginatedItems, count($collection), $per_page, $page);
        return new ReportKardexCollection2($paginatedCollection);

    }

    public function records_lots()
    {
        $records = ItemWarehouse::with(['item'])->whereHas('item', function ($q) {
            $q->where([['item_type_id', '01'], ['unit_type_id', '!=', 'ZZ'], ['lot_code', '!=', null]]);
            $q->whereNotIsSet();
        });

        return new ReportKardexLotsCollection($records->paginate(config('tenant.items_per_page')));

    }


    /**
     * @param $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|InventoryKardex
     */
    public function getRecords($request)
    {
        $warehouse_id = $request['warehouse_id'];
        $item_id = $request['item_id'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        /*Log::info('itemid'.$item_id);
        Log::info('warehouse'.$warehouse_id);
        Log::info('start'.$date_start);
        Log::info('end'.$date_end);*/


        //$records = $this->data($item_id, $warehouse_id, $date_start, $date_end);
        $records = $this->getDatBySp($item_id, $warehouse_id);
        return $records;

    }

    public function getDatBySp($request){

        $warehouse = $request['warehouse_id'];
        $item = $request['item_id'];

        if($warehouse == 'all'){
            $warehouse = 0;
        }
       $data = DB::connection('tenant')->select('CALL SP_ReportKardex(?,?)',[$item,$warehouse]);
       return $data;

    }


    /**
     * @param $item_id
     * @param $date_start
     * @param $date_end
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|InventoryKardex
     */
    private function data($item_id, $warehouse_id, $date_start, $date_end)
    {
        //$warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();
        //$inventory_kardexable_id = null;
        $data = InventoryKardex::with(['inventory_kardexable']);
        /*$data['info_kardex'] = DB::connection('tenant')->select("SELECT * FROM inventory_kardex ik
            LEFT JOIN inventories i ON ik.inventory_kardexable_id = i.id
            WHERE ik.item_id = :item_id", ['item_id' => $item_id]);*/
        //Log::info($data);
        if($warehouse_id !== 'all') {
            $data->where('warehouse_id', $warehouse_id);
        }
        if ($date_start) {
            $data->where('date_of_issue', '>=', $date_start);
        }
        if ($date_end) {
            $data->where('date_of_issue', '<=', $date_end);
        }
        if ($item_id) {
            $data->where('item_id', $item_id);
        }
        //Log::info(json_encode($data));
        //$data->where('inventory_kardexable_id', $inventory_kardexable_id);


        // if($date_start && $date_end){

        //     $data = InventoryKardex::with(['inventory_kardexable'])
        //                 ->where([['item_id', $item_id],['warehouse_id', $warehouse->id]])
        //                 ->whereBetween('date_of_issue', [$date_start, $date_end])
        //                 ->orderBy('id');

        // }else{

        //     $data = InventoryKardex::with(['inventory_kardexable'])
        //                 ->where([['item_id', $item_id],['warehouse_id', $warehouse->id]])
        //                 ->orderBy('id');
        // }

        $data
            ->orderBy('item_id')
            ->orderBy('id');

        return $data;

    }


    public function getFullDescription($row)
    {
        $desc = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
        $category = ($row->category) ? " - {$row->category->name}" : "";
        $brand = ($row->brand) ? " - {$row->brand->name}" : "";

        $desc = "{$desc} {$category} {$brand}";

        return $desc;
    }


    private function getData($request)
    {
        $company = Company::query()->first();
        $establishment = Establishment::query()->find(auth()->user()->establishment_id);
        $date_start = $request->input('date_start');
        $date_end = $request->input('date_end');
        $item_id = $request->input('item_id');
        $item = Item::query()->findOrFail($request->input('item_id'));

        $warehouse = Warehouse::query()
            ->where('establishment_id', $establishment->id)
            ->first();

        $query = InventoryKardex::query()
            ->with(['inventory_kardexable'])
            ->where('warehouse_id', $warehouse->id);

        if ($date_start && $date_end) {
            $query->whereBetween('date_of_issue', [$date_start, $date_end])
                ->orderBy('item_id')->orderBy('id');
        }

        if ($item_id) {
            $query->where('item_id', $item_id);
        }

        $records = $query->orderBy('item_id')
            ->orderBy('id')
            ->get();

        return [
            'company' => $company,
            'establishment' => $establishment,
            'warehouse' => $warehouse,
            'item_id' => $item_id,
            'item' => $item,
            'models' => $this->models,
            'date_start' => $date_start,
            'date_end' => $date_end,
            'records' => $records,
            'balance' => 0,
            'cost' => 0,
        ];
    }

    /**
     * PDF
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function pdf(Request $request)
    {
        $records = $this->getDatBySp($request);
        $company = Company::active();
        $item = Item::find($request['item_id']);
        $warehouse = Warehouse::find($request['warehouse_id']);
        $establishment = $warehouse->establishment;

        $pdf = PDF::loadView('inventory::reports.kardex.report_pdf', compact('company','item','establishment','records'))->setPaper('a4', 'landscape');
        $filename = 'Reporte_Kardex' . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Excel
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function excel(Request $request)
    {
        $data = $this->getDatBySp($request);
        $company = Company::active();
        $item = Item::find($request['item_id']);
        $warehouse = Warehouse::find($request['warehouse_id']);
        $kardexExport = new KardexExport();
        $kardexExport
            ->records($data)
            ->company($company)
            ->establishment($warehouse->establishment)
            ->item($item);

        return $kardexExport->download('ReporteKardexGeneral' . Carbon::now() . '.xlsx');
    }

    public function getRecords2($request)
    {

        $item_id = $request['item_id'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        $warehouse_id = $request['warehouse_id'];

        if($warehouse_id == 'all'){
            $warehouse_id = null;
        }

        $records = $this->data2($warehouse_id,$item_id, $date_start, $date_end);

        return $records;
    }

    private function data2($warehouse_id,$item_id, $date_start, $date_end)
    {
        $data = ItemLotsGroup::query();

        if(isset($warehouse_id) && $warehouse_id != ''){

            $data->where('warehouse_id', $warehouse_id);
        }

        if($item_id){

            $data->where('item_id', $item_id);
        }

        if($date_start && $date_end){

            $data->whereBetween('date_of_due', [$date_start, $date_end])
                        ->orderBy('item_id')->orderBy('id');

        }else{

            $data->orderBy('item_id')->orderBy('id');
        }

        return $data;
    }

    public function records_lots_kardex(Request $request)
    {
        $records = $this->getRecords2($request->all());

        return new ReportKardexLotsGroupCollection($records->paginate(config('tenant.items_per_page')));


    }


    public function getRecords3($request)
    {

        $item_id = $request['item_id'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        $warehouse_id = $request['warehouse_id'];

        if($warehouse_id == 'all'){
            $warehouse_id = null;
        }

        $records = $this->data3($warehouse_id,$item_id, $date_start, $date_end);

        return $records;

    }


    private function data3($warehouse,$item_id, $date_start, $date_end)
    {

        $data = ItemLot::query();
        if ($date_start && $date_end) {

            $data->whereBetween('date', [$date_start, $date_end])
                ->orderBy('item_id')->orderBy('id');

        } else {
            $data->orderBy('item_id')->orderBy('id');
        }

        if ($item_id) {
            $data->where('item_id', $item_id);
        }

        if ($warehouse) {
            $data->where('warehouse_id', $warehouse);
        }

        return $data;
    }

    public function records_series_kardex(Request $request)
    {

        $records = $this->getRecords3($request->all());

        return new ReportKardexItemLotCollection($records->paginate(config('tenant.items_per_page')));

        /*$records = [];

        if($item)
        {
            $records  =  ItemLot::where('item_id', $item)->get();

        }
        else{
            $records  = ItemLot::all();
        }

       // $records  =  ItemLot::all();
        return new ReportKardexItemLotCollection($records);*/

    }




    // public function search(Request $request) {
    //     //return $request->item_selected;
    //     $balance = 0;
    //     $d = $request->d;
    //     $a = $request->a;
    //     $item_selected = $request->item_selected;

    //     $items = Item::query()->whereNotIsSet()
    //         ->where([['item_type_id', '01'], ['unit_type_id', '!=','ZZ']])
    //         ->latest()
    //         ->get();

    //     $warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();

    //     if($d && $a){

    //         $reports = InventoryKardex::with(['inventory_kardexable'])
    //                     ->where([['item_id', $request->item_selected],['warehouse_id', $warehouse->id]])
    //                     ->whereBetween('date_of_issue', [$d, $a])
    //                     ->orderBy('id')
    //                     ->paginate(config('tenant.items_per_page'));

    //     }else{

    //         $reports = InventoryKardex::with(['inventory_kardexable'])
    //                     ->where([['item_id', $request->item_selected],['warehouse_id', $warehouse->id]])
    //                     ->orderBy('id')
    //                     ->paginate(config('tenant.items_per_page'));

    //     }

    //     //return json_encode($reports);

    //     $models = $this->models;

    //     return view('inventory::reports.kardex.index', compact('items', 'reports', 'balance','models', 'a', 'd','item_selected'));
    // }

}

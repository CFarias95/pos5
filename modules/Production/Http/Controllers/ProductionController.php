<?php

namespace Modules\Production\Http\Controllers;


use App\Models\Tenant\Item;
use App\Models\Tenant\ItemSupplyLot;
use App\Models\Tenant\ProductionSupply;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Inventory\Models\Inventory;
use Modules\Inventory\Models\InventoryTransaction;
use Modules\Inventory\Traits\InventoryTrait;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Production\Exports\BuildProductsExport;
use Modules\Production\Http\Requests\ProductionRequest;
use Modules\Production\Http\Resources\ProductionCollection;
use Modules\Production\Models\Machine;
use Modules\Production\Models\Production;
use Modules\Production\Models\StateTypeProduction;

class ProductionController extends Controller
{
    use InventoryTrait;
    use FinanceTrait;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('production::production.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create($id = null)
    {
        return view('production::production.form', compact('id'));
    }

    public function storeOld(ProductionRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {

            // validar estado_type_id == 01
            if ($request->records_id !== '01') {
                return [
                    'success' => false,
                    'message' => 'Solo se permite ingresar productos con estado registrado'
                ];
            }

            $production = Production::firstOrNew(['id' => null]);
            $production->fill($request->all());
            $production->state_type_id = $request->records_id;
            $production->user_id = auth()->user()->id;
            $production->soap_type_id = $this->getCompanySoapTypeId();
            $production->save();


            return [
                'success' => true,
                'message' => 'Producto registrado correctamente, listo para elaboración'
            ];
        });

        return $result;
    }

    public function storeOriginal(ProductionRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {

            $item_id = $request->input('item_id');
            $warehouse_id = $request->input('warehouse_id');
            $quantity = $request->input('quantity');
            $informative = ($request->informative) ?: false;


            $inventory_transaction = InventoryTransaction::findOrFail(19); //debe ser Ingreso de producción
            $inventory = new Inventory();

            if ($informative !== true) {
                $inventory->type = null;
                $inventory->description = $inventory_transaction->name;
                $inventory->item_id = $item_id;
                $inventory->warehouse_id = $warehouse_id;
                $inventory->quantity = $quantity;
                $inventory->inventory_transaction_id = $inventory_transaction->id;
                $inventory->save();
            }

            $production = Production::firstOrNew(['id' => null]);
            $production->fill($request->all());
            $production->inventory_id_reference = $inventory->id;
            $production->state_type_id = $request->records_id;
            $production->user_id = auth()->user()->id;
            $production->soap_type_id = $this->getCompanySoapTypeId();
            $production->save();


            if ($informative !== true) {
                $items_supplies = $request->supplies;
                foreach ($items_supplies as $item) {
                    $supplyWarehouseId = (int) ($item['warehouse_id'] ?? $warehouse_id);
                    $supplyWarehouseId = $supplyWarehouseId !== 0 ? $supplyWarehouseId : $warehouse_id;
                    $qty = $item['quantity'] ?? 0;
                    $inventory_transaction_item = InventoryTransaction::findOrFail('101'); //Salida insumos por molino
                    $inventory_it = new Inventory();
                    $inventory_it->type = null;
                    $inventory_it->description = $inventory_transaction_item->name;
                    $inventory_it->item_id = $item['individual_item_id'];
                    $inventory_it->warehouse_id = $supplyWarehouseId;
                    $inventory_it->quantity = (float) ($qty * $quantity);
                    $inventory_it->inventory_transaction_id = $inventory_transaction_item->id;
                    $inventory_it->save();
                }
            }

            return [
                'success' => true,
                'message' => 'Ingreso registrado correctamente'
            ];
        });

        return $result;
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param ProductionRequest $request
     *
     * @return Response
     */
    public function store(ProductionRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {

            $item_id = $request->input('item_id');
            $warehouse_id = $request->input('warehouse_id');
            $quantity = $request->input('quantity');
            $state_type_id = $request->records_id;
            $informative = ($request->informative) ?: false;
            $inventory_transaction = null;

            switch ($state_type_id) {
                case '01': // Registrado
                    $inventory_transaction = null;
                    break;
                case '02': // En elaboración
                    $inventory_transaction = InventoryTransaction::findOrFail(101); // Salida insumos por molino
                    break;
                case '03': // Finalizado
                    $inventory_transaction = InventoryTransaction::findOrFail(19); // Ingreso de producción
                    break;
                case '04': // Anulado
                    $inventory_transaction = null;
                    break;
            }
            $inventory = new Inventory();
            $production = new Production();

            if ($inventory_transaction && !$informative && $state_type_id == '03') {
                $inventory->type = null;
                $inventory->description = $inventory_transaction->name;
                $inventory->item_id = $item_id;
                $inventory->warehouse_id = $warehouse_id;
                $inventory->quantity = $quantity;
                $inventory->inventory_transaction_id = $inventory_transaction->id;
                $inventory->save();
            }

            $production->fill($request->all());
            $production->inventory_id_reference = $inventory->id ?? null;
            $production->warehouse_id = $warehouse_id;
            $production->state_type_id = $state_type_id;
            $production->user_id = auth()->user()->id;
            $production->soap_type_id = $this->getCompanySoapTypeId();
            $production->save();

            if (!$informative && $state_type_id != '01' && $state_type_id != '04' && $inventory_transaction) {
                $items_supplies = $request->supplies;
                foreach ($items_supplies as $item) {
                    $supplyWarehouseId = (int) ($item['warehouse_id'] ?? $warehouse_id);
                    $supplyWarehouseId = $supplyWarehouseId !== 0 ? $supplyWarehouseId : $warehouse_id;
                    $qty = $item['quantity'] ?? 0;
                    $inventory_transaction_item = InventoryTransaction::findOrFail(101); // Salida insumos por molino
                    $inventory_it = new Inventory();
                    $inventory_it->type = null;
                    $inventory_it->description = $inventory_transaction_item->name;
                    $inventory_it->item_id = $item['individual_item_id'];
                    $inventory_it->warehouse_id = $supplyWarehouseId;
                    $inventory_it->quantity = (float) ($qty * $quantity);
                    $inventory_it->inventory_transaction_id = $inventory_transaction_item->id;
                    $inventory_it->save();
                }
            }

            return [
                'success' => true,
                'message' => 'Ingreso registrado correctamente'
            ];
        });

        return $result;
    }


    /**
     * Show the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        return view('production::show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        return view('production::edit');
    }


    public function update(ProductionRequest $request, $id)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {

            $production = Production::findOrFail($id);

            $old_state_type_id = $production->state_type_id;
            $new_state_type_id = $request->records_id;
            $item_id = $request->input('item_id');
            $warehouse_id = $request->input('warehouse_id');
            $quantity = $request->input('quantity');
            $informative = ($request->informative) ?: false;
            $inventory_transaction = null;

            switch ($new_state_type_id) {
                case '01': // Registrado
                    $inventory_transaction = null;
                    break;
                case '02': // En elaboración
                    $inventory_transaction = InventoryTransaction::findOrFail(101); // Salida insumos por molino
                    break;
                case '03': // Finalizado
                    $inventory_transaction = InventoryTransaction::findOrFail(19); // Ingreso de producción
                    break;
                case '04': // Anulado
                    $inventory_transaction = null;
                    break;
            }

            $inventory = new Inventory();

            if ($inventory_transaction && !$informative && $new_state_type_id == '03') {
                $inventory->type = null;
                $inventory->description = $inventory_transaction->name;
                $inventory->item_id = $item_id;
                $inventory->warehouse_id = $warehouse_id;
                $inventory->quantity = $quantity;
                $inventory->inventory_transaction_id = $inventory_transaction->id;
                $inventory->save();
            }

            $production->fill($request->all());
            if (!($old_state_type_id == '03' && $new_state_type_id == '04')) {
                $production->inventory_id_reference = $inventory->id ?? null;
            }
            $production->warehouse_id = $warehouse_id;
            $production->state_type_id = $new_state_type_id;
            $production->user_id = auth()->user()->id;
            $production->soap_type_id = $this->getCompanySoapTypeId();
            $production->save();

            // Verificar si se está intentando anular después de finalizado
            if ($old_state_type_id == '03' && $new_state_type_id == '04' && !$informative) {
                $inventory_id_reference = $production->inventory_id_reference;
                if ($inventory_id_reference) {
                    $inventory_transaction = InventoryTransaction::findOrFail(103); // Salida por insumo anulado
                    $inventory = new Inventory();
                    $inventory->type = null;
                    $inventory->description = $inventory_transaction->name;
                    $inventory->item_id = $production->item_id;
                    $inventory->warehouse_id = $production->warehouse_id;
                    $inventory->quantity = ($production->quantity);
                    $inventory->inventory_transaction_id = $inventory_transaction->id;
                    $inventory->save();


                    $items_supplies = $request->supplies;
                    foreach ($items_supplies as $item) {
                        $supplyWarehouseId = (int) ($item['warehouse_id'] ?? $warehouse_id);
                        $supplyWarehouseId = $supplyWarehouseId !== 0 ? $supplyWarehouseId : $warehouse_id;
                        $qty = $item['quantity'] ?? 0;
                        $inventory_transaction_item = InventoryTransaction::findOrFail(104); //Entrada por insumo anulado
                        $inventory_it = new Inventory();
                        $inventory_it->type = null;
                        $inventory_it->description = $inventory_transaction_item->name;
                        $inventory_it->item_id = $item['individual_item_id'];
                        $inventory_it->warehouse_id = $supplyWarehouseId;
                        $inventory_it->quantity = (float) ($qty * $quantity);
                        $inventory_it->inventory_transaction_id = $inventory_transaction_item->id;
                        $inventory_it->save();
                    }
                }

            }


            if (!$informative && $new_state_type_id != '01' && $new_state_type_id != '04' && $inventory_transaction && $new_state_type_id != '03') {
                $items_supplies = $request->supplies;
                foreach ($items_supplies as $item) {
                    $supplyWarehouseId = (int) ($item['warehouse_id'] ?? $warehouse_id);
                    $supplyWarehouseId = $supplyWarehouseId !== 0 ? $supplyWarehouseId : $warehouse_id;
                    $qty = $item['quantity'] ?? 0;
                    $inventory_transaction_item = InventoryTransaction::findOrFail(101); // Salida insumos por molino
                    $inventory_it = new Inventory();
                    $inventory_it->type = null;
                    $inventory_it->description = $inventory_transaction_item->name;
                    $inventory_it->item_id = $item['individual_item_id'];
                    $inventory_it->warehouse_id = $supplyWarehouseId;
                    $inventory_it->quantity = (float) ($qty * $quantity);
                    $inventory_it->inventory_transaction_id = $inventory_transaction_item->id;
                    $inventory_it->save();
                }
            }


            return [
                'success' => true,
                'message' => 'Actualización realizada correctamente'
            ];
        });

        return $result;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function updateOld(ProductionRequest $request, $id)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {
            $production = Production::findOrFail($id);
            $current_status_production = $production->state_type_id;

            switch ($current_status_production) {
                case '01':
                    if ($request->records_id)
                        break;

                default:
                    # code...
                    break;
            }
            $production->fill($request->all());
            $production->state_type_id = $request->records_id;

            $inventory = Inventory::findOrFail($production->inventory_id_reference);

            $inventory_transaction = InventoryTransaction::findOrFail(19); //debe ser Ingreso de producción

            if (!$request->informative) {
                $inventory->type = null;
                $inventory->description = $inventory_transaction->name;
                $inventory->item_id = $request->input('item_id');
                $inventory->warehouse_id = $request->input('warehouse_id');
                $inventory->quantity = $request->input('quantity');
                $inventory->inventory_transaction_id = $inventory_transaction->id;
                $inventory->save();
            }

            if (!$request->informative) {
                $items_supplies = $request->supplies;

                foreach ($items_supplies as $item) {
                    $supplyWarehouseId = (int) ($item['warehouse_id'] ?? $request->input('warehouse_id'));
                    $supplyWarehouseId = $supplyWarehouseId !== 0 ? $supplyWarehouseId : $request->input('warehouse_id');
                    $qty = $item['quantity'] ?? 0;

                    $inventory_transaction_item = InventoryTransaction::findOrFail('101'); //Salida insumos por molino
                    $inventory_it = Inventory::where('item_id', $item['individual_item_id'])
                        ->where('warehouse_id', $supplyWarehouseId)
                        ->where('inventory_transaction_id', $inventory_transaction_item->id)
                        ->firstOrFail();

                    $inventory_it->quantity = (float) ($qty * $request->input('quantity'));
                    $inventory_it->save();
                }
            }

            $production->save();

            return [
                'success' => true,
                'message' => 'Actualización realizada correctamente'
            ];
        });

        return $result;
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function tables()
    {
        $machines = Machine::query()->get()->transform(function (Machine $row) {
            return $row->getCollectionData();
        });
        $state_types_prod = StateTypeProduction::get();
        $state_type_descr = StateTypeProduction::find('01');
        return [
            'items' => self::optionsItemProduction(),
            'warehouses' => $this->optionsWarehouse(),
            'machines' => $machines,
            'state_types_prod' => $state_types_prod,
            //'state_types_id' => count($state_types_prod) > 0 ? $state_types_prod->first()->id : null,
            'state_type_descr' => $state_type_descr->description,
        ];
    }

    public static function optionsItemProduction($itemId = null)
    {
        $query = Item::ProductEnded();
        if ($itemId !== null) {
            $query->find($itemId);
        }
        $result = $query->get()
            ->transform(function (Item $row) {
                $data = $row->getCollectionData();
                $supplies = $data["supplies"];

                foreach ($supplies as $value) {
                    $lots_group = $value["individual_item"]["lots_group"];

                    foreach ($lots_group as $lot) {
                        $lot["item_supply_id"] = $value["id"];
                    }
                    $transformed_supply = [
                        'id' => $value["id"],
                        'individual_item_id' => $value["individual_item_id"],
                        'description' => $value["individual_item"]["description"] ?? '',
                        'quantity' => $value["quantity"],
                        'unit_type' => $value["individual_item"]["unit_type"]["description"],
                        'quantity_per_unit' => $value["quantity"],
                        'lots_enabled' => $value["individual_item"]["lots_enabled"],
                        'warehouse' => $value["individual_item"]["warehouse_id"],
                        'lots_group' => $lots_group,
                    ];
                    $transformed_supplies[] = $transformed_supply;
                }
                $data["supplies"] = $transformed_supplies;
                return $data;
            });
        return $result;
    }

    public function searchItems(Request $request)
    {
        $search = $request->input('search');

        return [
            'items' => self::optionsItemFullProduction($search, 20),
        ];
    }

    public static function optionsItemFullProduction($search = null, $take = null)
    {
        $query = Item::query()
            ->ProductEnded()
            ->with('item_lots', 'item_lots.item_loteable', 'lots_group');
        if ($search) {
            $query->where('description', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhere('internal_id', 'like', "%{$search}%");
        }
        if ($take) {
            $query->take($take);
        }
        return $query->get()->transform(function (Item $row) {
            return $row->getCollectionData();
        });
    }

    public function records(Request $request)
    {
        $records = $this->getRecords($request);
        return new ProductionCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function record($id)
    {
        $production = Production::findOrFail($id);
        $production_supplies = ProductionSupply::where('production_id', $production->id)->with('itemSupply.individual_item')->get();
        $warehouse_id = $production->warehouse_id;
        $data = $production->getCollectionData();
        $data['item_id'] = $production->item_id;
        $data['warehouse_id'] = $warehouse_id;
        $data['records_id'] = $production->state_type_id;
        //hago un recorrido de todo los insumos que utilicé para fabricar un producto.
        foreach ($production_supplies as $supply) {
            $item_supply_id = $supply->item_supply_id;
            //por cada insumo que se fabricó voy a obtener los lotes que se utilizó
            //para ello obtengo la producción y el id del insumo que se utilizó en esa producción
            $itemSupplyLots = ItemSupplyLot::select('item_supply_lots.*', 'item_lots_group.*', 'item_lots_group.quantity as compromise_quantity', 'item_supply_lots.quantity as supply_quantity')
                ->where('production_id', $production->id)
                ->where('item_supply_id', $supply->itemSupply->id)
                ->join('item_lots_group', 'item_lots_group.id', '=', 'item_supply_lots.lot_id')
                ->get();
            $transformed_supply_lots = [];
            foreach ($itemSupplyLots as $supplyLots) {
                $transformed_supply_lots[] = [
                    'lot_id' => $supplyLots["lot_id"],
                    'code' => $supplyLots["lot_code"],
                    'quantity' => $supplyLots["compromise_quantity"],
                    'compromise_quantity' => $supplyLots["supply_quantity"],
                    'date_of_due' => $supplyLots["expiration_date"],
                    'item_id' => $supplyLots["item_supply_id"],
                ];
            }

            $transformed_supply = [
                'id' => $item_supply_id,
                'description' => $supply->item_supply_name ?? '',
                'item_id' => $supply->itemSupply->individual_item->id,
                'quantity' => $supply->quantity,
                'unit_type' => $supply->itemSupply->individual_item->unit_type->description,
                'quantity_per_unit' => $supply->quantity,
                'lots_enabled' => $supply->itemSupply->individual_item->lots_enabled,
                'warehouse_id' => $supply->warehouse_id,
                'warehouse_name' => $supply->warehouse_name,
                'lots_group' => $transformed_supply_lots,
            ];
            $transformed_supplies[] = $transformed_supply;
        }
        $data["supplies"] = $transformed_supplies;
        return $data;
    }

    public function getRecords(Request $request)
    {
        $state_type_id = $request->state_type_id;
        $data_of_period = $this->getDatesOfPeriod($request);

        $data = Production::query();

        if (!empty($data_of_period['d_start'])) {
            $data->where(function ($query) use ($data_of_period) {
                $query->where('date_start', '>=', $data_of_period['d_start'])
                    ->orWhere(
                        function ($query) use ($data_of_period) {
                            $query->whereNull('date_start')
                                ->where('created_at', '>=', $data_of_period['d_start']);
                        }
                    );
            });
        }

        if (!empty($data_of_period['d_end'])) {
            $data->where(function ($query) use ($data_of_period) {
                $query->where('date_end', '<=', $data_of_period['d_end'])
                    ->orWhere(
                        function ($query) use ($data_of_period) {
                            $query->whereNull('date_end')
                                ->where('created_at', '<=', $data_of_period['d_end']);
                        }
                    );
            });
        }



        return $data;
    }

    public function getRecords2(Request $request)
    {
        $state_type_id = $request->state_type_id;

        $data_of_period = $this->getDatesOfPeriod($request);
        $data = Production::query();
        if (!empty($data_of_period['d_start'])) {
            $data->where('date_start', '>=', $data_of_period['d_start']);
        }
        if (!empty($data_of_period['d_end'])) {
            $data->where('date_end', '<=', $data_of_period['d_end']);
        }
        if ($state_type_id) {
            $data->where('state_type_id', 'like', '%' . $state_type_id . '%');
        }
        return $data;
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Database\Eloquent\Builder|Builder|Production
     */
    public function getDatesOfPeriod($request)
    {

        if ($request->has('form')) {
            $request = json_decode($request->form, true);
        }
        $period = $request['period'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        $month_start = $request['month_start'];
        $month_end = $request['month_end'];

        $d_start = Carbon::now()->startOfMonth()->format('Y-m-d');
        $d_end = Carbon::now()->endOfMonth()->format('Y-m-d');
        /** @todo: Eliminar periodo, fechas y cambiar por
         * $date_start = $request['date_start'];
         * $date_end = $request['date_end'];
         * \App\CoreFacturalo\Helpers\Functions\FunctionsHelper\FunctionsHelper::setDateInPeriod($request, $date_start, $date_end);
         */
        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_start . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_end . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'date':
                $d_start = $date_start;
                $d_end = $date_start;
                break;
            case 'between_dates':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
        }


        return [
            'd_start' => $d_start,
            'd_end' => $d_end
        ];
    }

    /**
     * @param Request $request
     *
     * @return Response|BinaryFileResponse
     */
    public function excel(Request $request)
    {
        // $records = $this->getData($request);
        $records = $this->getRecords($request)->where('informative', 0)->get()->transform(function (Production $row) {
            return $row->getCollectionData();
        });

        $buildProductsExport = new BuildProductsExport();
        $buildProductsExport->setCollection($records);
        $filename = 'Reporte de produccion - ' . date('YmdHis');
        // return $buildProductsExport->view();
        return $buildProductsExport->download($filename . '.xlsx');
    }

    /**
     * @param Request $request
     *
     * @return Response|BinaryFileResponse
     */
    public function excel2(Request $request)
    {
        // $records = $this->getData($request);
        $records = $this->getRecords($request)->where('informative', 1)->get()->transform(function (Production $row) {
            return $row->getCollectionData();
        });

        $buildProductsExport = new BuildProductsExport();
        $buildProductsExport->setCollection($records)->setInProccess(true);
        $filename = 'Reporte de produccion en proceso- ' . date('YmdHis');
        // return $buildProductsExport->view();
        return $buildProductsExport->download($filename . '.xlsx');
    }


    public function pdf(Request $request)
    {
        // $records = $this->getData($request);
        $records = $this->getRecords($request)->get()->transform(function (Production $row) {
            return $row->getCollectionData();
        });

        /** @var \Barryvdh\DomPDF\PDF $pdf */
        $pdf = PDF::loadView(
            'production::production.partial.export',
            compact(
                'records'
            )
        )
            ->setPaper('a4', 'landscape');


        $filename = 'Reporte de produccion - ' . date('YmdHis');
        return $pdf->stream($filename . '.pdf');
    }
}

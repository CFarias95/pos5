<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\Inventory;
use Modules\Inventory\Models\InventoryTransaction;
use Modules\Inventory\Traits\InventoryTrait;
use Modules\Inventory\Models\ItemWarehouse;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Http\Requests\Api\InventoryRequest;
use Modules\Item\Models\ItemLot;
use Modules\Item\Models\ItemLotsGroup;
use App\Models\Tenant\Item;

class InventoryController extends Controller
{
    use InventoryTrait;


    public function store_transaction(InventoryRequest $request)
    {

        $result = DB::connection('tenant')->transaction(function () use ($request) {

            $item = Item::select('id')->where('internal_id', $request->item_code)->first();

            if(!$item){
                return [
                    'success' => false,
                    'message' => 'No se encontró un producto con el codigo ingresado'
                ];
            }

            $item_id = $item->id;

            $type = $request->input('type');
            $warehouse_id = $request->input('warehouse_id');
            $inventory_transaction_id = $request->input('inventory_transaction_id');
            $quantity = $request->input('quantity');

            $item_warehouse = ItemWarehouse::firstOrNew(['item_id' => $item_id,
                                                         'warehouse_id' => $warehouse_id]);

            $inventory_transaction = InventoryTransaction::findOrFail($inventory_transaction_id);

            if($type == 'output' && ($quantity > $item_warehouse->stock)) {
                return  [
                    'success' => false,
                    'message' => 'La cantidad no puede ser mayor a la que se tiene en el almacén.'
                ];
            }

            $inventory = new Inventory();
            $inventory->type = null;
            $inventory->description = $inventory_transaction->name;
            $inventory->item_id = $item_id;
            $inventory->warehouse_id = $warehouse_id;
            $inventory->quantity = $quantity;
            $inventory->inventory_transaction_id = $inventory_transaction_id;
            $inventory->save();

            return  [
                'success' => true,
                'message' => ($type == 'input') ? 'Ingreso registrado correctamente' : 'Salida registrada correctamente'
            ];
        });

        return $result;
    }




}

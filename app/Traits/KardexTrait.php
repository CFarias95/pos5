<?php

namespace App\Traits;
use App\Models\Tenant\Item;
use App\Models\Tenant\Kardex;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Models\ItemWarehouse;
use Modules\Inventory\Models\InventoryConfiguration;
use Modules\Item\Models\ItemLotsGroup;

trait KardexTrait
{

    public function saveKardex($type, $item_id, $id, $quantity, $relation) {

        $kardex = Kardex::create([
            'type' => $type,
            'date_of_issue' => date('Y-m-d'),
            'item_id' => $item_id,
            'document_id' => ($relation == 'document') ? $id : null,
            'purchase_id' => ($relation == 'purchase') ? $id : null,
            'purchase_settlement_id' => ($relation == 'purchase_settlement') ? $id : null,
            'sale_note_id' => ($relation == 'sale_note') ? $id : null,
            'quantity' => $quantity,
        ]);

        return $kardex;

    }

    public function updateStock($item_id, $quantity, $is_sale){

        $item = Item::find($item_id);
        /* dd($item); */
        $item->stock = ($is_sale) ? $item->stock - $quantity : $item->stock + $quantity;
        $item->save();

    }

    public function restoreStockInWarehpuse($item_id, $warehouse_id, $quantity)
    {
        Log::info('restoreStockInWarehpuse');
        $item_warehouse = ItemWarehouse::firstOrNew(['item_id' => $item_id, 'warehouse_id' => $warehouse_id]);
        $item_warehouse->stock = $item_warehouse->stock + $quantity;
        $item_warehouse->save();
    }

    public function restoreStockInWarehouseLotGroup($item_id, $warehouse_id, $quantity, $lot_code)
    {
        Log::info('restoreStockInWarehouseLotGroup');
        $item_warehouse = ItemLotsGroup::firstOrNew(['item_id' => $item_id, 'warehouse_id' => $warehouse_id,'code' => $lot_code]);
        $item_warehouse->quantity = $item_warehouse->quantity + $quantity;
        $item_warehouse->save();

        $item_warehouse = ItemWarehouse::firstOrNew(['item_id' => $item_id, 'warehouse_id' => $warehouse_id]);
        $item_warehouse->stock = $item_warehouse->stock + $quantity;
        $item_warehouse->save();

    }

}

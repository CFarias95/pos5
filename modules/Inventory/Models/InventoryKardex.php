<?php

namespace Modules\Inventory\Models;

use App\Models\Tenant\Dispatch;
use App\Models\Tenant\Document;
use App\Models\Tenant\Imports;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemWarehousePrice;
use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchaseSettlement;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\Warehouse;
use Illuminate\Support\Facades\Log;
use Modules\Order\Models\OrderNote;

/**
 * Modules\Inventory\Models\InventoryKardex
 *
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $inventory_kardexable
 * @property-read Item $item
 * @property-read \Modules\Inventory\Models\Warehouse $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryKardex newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryKardex newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryKardex query()
 * @mixin ModelTenant
 */
class InventoryKardex extends ModelTenant
{
    protected $table = 'inventory_kardex';

    protected $fillable = [
        'date_of_issue',
        'item_id',
        'inventory_kardexable_id',
        'inventory_kardexable_type',
        'warehouse_id',
        'quantity',
    ];

    public function inventory_kardexable()
    {
        return $this->morphTo();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * @return ItemWarehousePrice|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|mixed|object|null
     */
    public function getItemWarehousePriceModel()
    {
        return ItemWarehousePrice::where(
            [
                'warehouse_id' => $this->warehouse_id,
                'item_id' => $this->item_id,
            ]
        )->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed|Warehouse|Warehouse[]|null
     */
    public function getWarehouseModel()
    {
        return Warehouse::find($this->warehouse_id);
    }

    /**
     * Obtener notas de venta asociadas a documento
     *
     * @return string
     */
    public function getSaleNoteAsoc($inventory_kardexable)
    {
        $sale_note_asoc = "-";

        if(isset($inventory_kardexable->sale_note_id))
        {
            $sale_note_asoc = optional($inventory_kardexable)->sale_note->number_full;
        }

        if(isset($inventory_kardexable->sale_notes_relateds))
        {
            $data = [];

            foreach ($inventory_kardexable->sale_notes_relateds as $sale_note)
            {
                if(isset($sale_note->items)){

                    $exist_sale_note = collect($sale_note->items)->where('item_id', $this->item_id)->first();

                    if($exist_sale_note) $data [] = $sale_note->number_full;
                }
            }

            // $sale_note_asoc = collect($inventory_kardexable->sale_notes_relateds)->implode('number_full', ', ');
            $sale_note_asoc = count($data) > 0 ? implode(', ', $data) : '-';

        }

        return $sale_note_asoc;
    }

    /**
     * @param $balance
     * @return array
     */
    public function getKardexReportCollection(&$balance)
    {
        $models = [
            Document::class,
            Purchase::class,
            SaleNote::class,
            Inventory::class,
            OrderNote::class,
            Devolution::class,
            Dispatch::class,
            PurchaseSettlement::class,
        ];
        $item = $this->item;
        $warehouseprice = $this->getItemWarehousePriceModel();
        $warehouse = $this->getWarehouseModel();
        $price = '-';
        $warehouseName = '';
        if (!empty($warehouseprice)) {
            $price = $warehouseprice->getPrice();
        }
        if (!empty($warehouse)) {
            $warehouseName = $warehouse->description;
        }
        $data = [
            'id' => $this->id,
            'item_name' => $item->description,
            'date_time' => $this->created_at->format('Y-m-d H:i:s'),
            'date_of_issue' => '-',
            'number' => '-',
            'sale_note_asoc' => '-',
            'order_note_asoc' => '-',
            'doc_asoc' => '-',
            // 'inventory_kardexable_id' => $this->inventory_kardexable_id,
            'inventory_kardexable_type' => $this->inventory_kardexable_type,
            // 'item' => $item->getCollectionData(),
            'item_warehouse_price' => $price,
            'warehouse' => $warehouseName,
            'cost' => 'N/A',

        ];
        $inventory_kardexable = $this->inventory_kardexable;
        $qty = $this->quantity;
        $input_set = ($qty > 0) ? $qty : "-";
        $output_set = ($qty < 0) ? $qty : "-";
        $data['input'] = $input_set;
        $data['output'] = $output_set;
        switch ($this->inventory_kardexable_type) {

            case $models[0]: //venta

                $lot_code = '';
                //$cpe_input = ($qty > 0) ? (isset($inventory_kardexable->sale_note_id) || isset($inventory_kardexable->order_note_id) || isset($inventory_kardexable->sale_notes_relateds) ? "-" : $qty) : "-";

                //$cpe_output = ($qty < 0) ? (isset($inventory_kardexable->sale_note_id) || isset($inventory_kardexable->order_note_id) || isset($inventory_kardexable->sale_notes_relateds) ? "-" : $qty) : "-";

                $cpe_input = ($qty > 0) ? (isset($inventory_kardexable->sale_note_id) || isset($inventory_kardexable->sale_notes_relateds) ? "-" : $qty) : "-";

                $cpe_output = ($qty < 0) ? (isset($inventory_kardexable->sale_note_id) || isset($inventory_kardexable->sale_notes_relateds) ? "-" : $qty) : "-";

                $cpe_discounted_stock = false;
                $cpe_doc_asoc = isset($inventory_kardexable->note) ? $inventory_kardexable->note->affected_document->getNumberFullAttribute() : '-';

                if (isset($inventory_kardexable->dispatch)) {
                    if ($inventory_kardexable->dispatch->transfer_reason_type->discount_stock) {
                        $cpe_output = '-';
                        $cpe_discounted_stock = true;
                    }
                    $cpe_doc_asoc = ($cpe_doc_asoc == '-') ? $inventory_kardexable->dispatch->number_full : $cpe_doc_asoc . ' | ' . $inventory_kardexable->dispatch->number_full;
                }

                $doc_balance = (isset($inventory_kardexable->sale_note_id) || $cpe_discounted_stock || isset($inventory_kardexable->sale_notes_relateds)) ? $balance += 0 : $balance += $qty;

                $data['input'] = $cpe_input;
                $data['output'] = $cpe_output;
                $data['balance'] = $doc_balance;
                $data['number'] = optional($inventory_kardexable)->series . '-' . optional($inventory_kardexable)->number;
                $data['type_transaction'] = ($qty < 0) ? "Venta" : "Anulación Venta";
                $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                // $data['sale_note_asoc'] = isset($inventory_kardexable->sale_note_id) ? optional($inventory_kardexable)->sale_note->number_full : "-";
                $data['sale_note_asoc'] = $this->getSaleNoteAsoc($inventory_kardexable);
                $data['doc_asoc'] = $cpe_doc_asoc;
                $data['order_note_asoc'] = isset($inventory_kardexable->order_note_id) ? optional($inventory_kardexable)->order_note->number_full : "-";
                $cost = 'N/A';

                if(isset(optional($inventory_kardexable)->items) == true){
                    foreach (optional($inventory_kardexable)->items as $key => $value) {
                        if($value->item_id == $item->id){
                            $cost=(isset($value->item->purchase_mean_cost))?$value->item->purchase_mean_cost:'N/A';
                            $lot_code = $value->lot_code;
                        }
                    }
                }else{
                    $cost='N/A';
                }

                foreach ($inventory_kardexable->items as $key => $value) {
                    Log::info('$inventory_kardexable->items'.json_encode($value));
                    if($value->item_id == $item->id){
                        if($value->item->IdLoteSelected){
                            foreach($value->item->IdLoteSelected as $lot){
                                $lot_code .= $lot->code . ', ';
                            }
                        }
                        $cost=$value->unit_value;
                    }
                }
                $data['cost'] = $cost;//$item->purchase_mean_cost;
                $data['lot_code'] = $lot_code;
                break;

            case $models[1]: //COMPRA
                $imp = Purchase::where('series',optional($inventory_kardexable)->series)->where('number',optional($inventory_kardexable)->number)->first();
                $lot_code = '';

                //Log::info('importacion asociada: '.json_encode($imp->import));
                $numeroImp = '';
                if($imp->import){
                    //Log::info('importacion asociada: '.json_encode($imp->import->numeroImportacion));
                    $numeroImp = ' / '.$imp->import->numeroImportacion;
                }

                $data['balance'] = $balance += $qty;
                $data['number'] = optional($inventory_kardexable)->series . '-' . optional($inventory_kardexable)->number.$numeroImp;
                $data['type_transaction'] = ($qty < 0) ? "Anulación Compra" : "Compra";
                $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                $cost = 'N/A';

                foreach ($inventory_kardexable->items as $key => $value) {
                    if($value->item_id == $item->id){
                        $cost=$value->unit_value;
                        $lot_code = $value->lot_code;
                    }
                }

                $data['cost'] = $cost;
                $data['lot_code'] = $inventory_kardexable->lot_code;
                break;

            case $models[2]: // Nota de venta

                if(isset($inventory_kardexable->order_note_id))
                {
                    $nv_balance = $balance += 0;
                    $data['output'] = '-';
                    $data['order_note_asoc'] = optional($inventory_kardexable)->order_note->number_full;
                }
                else
                {
                    $nv_balance = $balance += $qty;
                }

                $data['balance'] = $nv_balance;
                // $data['balance'] = $balance += $qty;
                $data['number'] = optional($inventory_kardexable)->number_full;
                $data['type_transaction'] = ($qty < 0) ? "Nota de venta" : "Anulación Nota de venta";
                // $data['type_transaction'] = "Nota de venta";
                $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                $data['lot_code'] = $inventory_kardexable->lot_code;
                break;
            case $models[3]: // MOVIMIENTOS DE INVENTARIO

                $transaction = '';
                $input = '';
                $output = '';
                $tranfer = '';
                $movimiento = '-';

                if($inventory_kardexable && isset($inventory_kardexable->warehouse_destination_id)){
                    $origenW = Warehouse::find($inventory_kardexable->warehouse_id);
                    $destinoW = Warehouse::find($inventory_kardexable->warehouse_destination_id);
                    $movimiento = $origenW->establishment->description. '/'.$destinoW->establishment->description;
                }
                if ($inventory_kardexable && !$inventory_kardexable->type) {
                    $transaction = InventoryTransaction::findOrFail($inventory_kardexable->inventory_transaction_id);
                }
                if ( $inventory_kardexable && $inventory_kardexable->type != null) {
                    $input = ($inventory_kardexable->type == 1) ? $qty : "-";
                } else {
                    $input = ($transaction && $transaction->type == 'input') ? $qty : "-";
                }
                if ($inventory_kardexable && $inventory_kardexable->type != null) {
                    $output = ($inventory_kardexable->type == 2 || $inventory_kardexable->type == 3) ? $qty : "-";
                } else {
                    $output = ($transaction && $transaction->type == 'output') ? $qty : "-";
                }
                $user = auth()->user();
                $data['balance'] = $balance += $qty;
                $data['type_transaction'] = isset($inventory_kardexable->description) ? $inventory_kardexable->description: null;
                $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                if ( $inventory_kardexable && $inventory_kardexable->warehouse_destination_id === $user->establishment_id) {
                    $data['input'] = $output;
                    $data['output'] = $input;
                } else {
                    $data['input'] = $input;
                    $data['output'] = $output;
                }

                $data['doc_asoc'] = $movimiento;
                $data['number'] = isset($inventory_kardexable->id) ? 'INV - '.$inventory_kardexable->id : 'N/A';
                $data['cost'] = isset($inventory_kardexable->precio_perso)?$inventory_kardexable->precio_perso:'N/A';
                $data['lot_code'] = isset($inventory_kardexable->lot_code) ? $inventory_kardexable->lot_code : 'N/A';
                break;

            case $models[4]:
                $data['balance'] = $balance += $qty;
                $data['number'] = optional($inventory_kardexable)->prefix . '-' . optional($inventory_kardexable)->id;
                $data['type_transaction'] = ($qty < 0) ? "Pedido" : "Anulación Pedido";
                $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                $data['lot_code'] = $inventory_kardexable->lot_code;
                break;
            case $models[5]: // Devolution
                $data['balance'] = $balance += $qty;
                $data['number'] = optional($inventory_kardexable)->number_full;
                $data['type_transaction'] = "Devolución";
                $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                $data['lot_code'] = $inventory_kardexable->lot_code;
                break;
            case $models[6]: // Dispatch
                $data['input'] = ($qty > 0) ? (isset($inventory_kardexable->reference_sale_note_id) || isset($inventory_kardexable->reference_order_note_id) || isset($inventory_kardexable->reference_document_id) ? "-" : $qty) : "-";
                $data['output'] = ($qty < 0) ? (isset($inventory_kardexable->reference_sale_note_id) || isset($inventory_kardexable->reference_order_note_id) || isset($inventory_kardexable->reference_document_id) ? "-" : $qty) : "-";
                $data['balance'] = (isset($inventory_kardexable->reference_sale_note_id) || isset($inventory_kardexable->reference_order_note_id) || isset($inventory_kardexable->reference_document_id)) ? $balance += 0 : $balance += $qty;
                $data['number'] = optional($inventory_kardexable)->number_full;
                $data['type_transaction'] = isset($inventory_kardexable->transfer_reason_type->description) ? $inventory_kardexable->transfer_reason_type->description : '';
                $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                $data['sale_note_asoc'] = isset($inventory_kardexable->reference_sale_note_id) ? optional($inventory_kardexable)->sale_note->number_full : "-";
                $data['order_note_asoc'] = isset($inventory_kardexable->reference_order_note_id) ? optional($inventory_kardexable)->order_note->number_full : "-";
                $data['doc_asoc'] = isset($inventory_kardexable->reference_document_id) ? $inventory_kardexable->reference_document->getNumberFullAttribute() : '-';
                $data['lot_code'] = $inventory_kardexable->lot_code;
                break;
            case $models[7]: // liquidacion de compra

                $data['balance'] = $balance += $qty;
                $data['number'] = optional($inventory_kardexable)->series . '-' . optional($inventory_kardexable)->number;
                $data['type_transaction'] = ($qty < 0) ? "Anulación Liquidacion Compra" : "Liquidacion Compra";
                $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                $data['lot_code'] = $inventory_kardexable->lot_code;
                break;
        }
        $decimalRound = 6; // Cantidad de decimales a aproximar
        $data['balance'] =$data['balance'] ? round( $data['balance'] ,$decimalRound):0;
        return $data;
    }
}

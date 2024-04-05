<?php

    namespace Modules\Inventory\Http\Controllers;

    use App\Http\Controllers\Controller;
    use App\Http\Controllers\SearchItemController;
use App\Models\Tenant\Item;
use Barryvdh\DomPDF\Facade as PDF;
    use Carbon\Carbon;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Exports\InventoryTransferExport;
    use Modules\Inventory\Http\Resources\TransferCollection;
    use Modules\Inventory\Http\Resources\TransferResource;
    use Modules\Inventory\Models\Inventory;
    use Modules\Inventory\Traits\InventoryTrait;
    use Modules\Inventory\Models\ItemWarehouse;
    use Modules\Inventory\Models\Warehouse;
    use Modules\Inventory\Models\InventoryTransfer;
    use Modules\Inventory\Http\Requests\InventoryRequest;
    use Modules\Inventory\Http\Requests\TransferRequest;
    use App\Models\Tenant\Person;
use App\Models\Tenant\Warehouse as TenantWarehouse;
use Exception;
use Modules\Item\Models\ItemLot;
use Modules\Item\Models\ItemLotsGroup;

    class TransferController extends Controller
    {
        use InventoryTrait;

        public function index()
        {
            return view('inventory::transfers.index');
        }

        public function create()
        {
            // $establishment_id = auth()->user()->establishment_id;
            //$current_warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
            return view('inventory::transfers.form');

        }

        public function columns()
        {
            $columns =  [
                'created_at' => 'Fecha de emisión',
            ];

            $clients = Person::get()->transform(function($row){
                return [
                    'id'=> $row->id,
                    'description' => $row->name
                ];
            });

            $warehouses = Warehouse::get()->transform(function($row){
                return[
                    'id' => $row->id,
                    'description' => $row->description
                ];
            });

            return compact('columns', 'clients', 'warehouses');
        }

        public function records(Request $request)
        {
            $records = InventoryTransfer::with(['warehouse', 'warehouse_destination', 'inventory', 'client']);

            if ($request->column) {
                $records->where('created_at', 'like', "%{$request->value}%")->latest();
            }

            if ($request->client_id) {
                $records->where('client_id', $request->client_id)->latest();
            }

            if ($request->warehouse) {
                $records->where('warehouse_id', $request->warehouse)->orWhere('warehouse_destination_id', $request->warehouse)->latest();
            }

            if ($request->warehouse_id) {
                $records->where('warehouse_id', $request->warehouse_id)->latest();
            }

            if ($request->warehouse_destination_id) {
                $records->where('warehouse_destination_id', $request->warehouse_destination_id)->latest();
            }

            //$person = Person::where('id', $records->client_id)->get();
            //Log::info('records'.json_encode($person));

            //return json_encode( $records );
            /*$records = Inventory::with(['item', 'warehouse', 'warehouse_destination'])
                                ->where('type', 2)
                                ->whereHas('warehouse_destination')
                                ->whereHas('item', function($query) use($request) {
                                    $query->where('description', 'like', '%' . $request->value . '%');

                                })
                                ->latest();*/

            //Log::info('records'.json_encode($records));

            return new TransferCollection($records->paginate(config('tenant.items_per_page')));
        }


        public function tables()
        {
            return [
                //'items' => $this->optionsItemWareHouse(),
                'warehouses' => $this->optionsWarehouse(),
                'customers' => $this->optionsCustomers(),
            ];
        }

        public function record($id)
        {
            $record = new TransferResource(Inventory::findOrFail($id));

            return $record;
        }

        public function updateEstado($id, $estado_id)
        {
            //Log::info('id'.$id);
            //Log::info('estado'.$estado_id);
            $traslado = InventoryTransfer::find($id);

            //Log::info('trasladoantes'.$traslado);
            $traslado->estado_id = $estado_id;
            //Log::info('trasladodespues'.$traslado);
            $traslado->save();

            return [
                'success' => true,
                'message' => 'Se actualizo el estado de la transferencia'
            ];
        }

        /* public function store(Request $request)
         {

             $result = DB::connection('tenant')->transaction(function () use ($request) {

                 $id = $request->input('id');
                 $item_id = $request->input('item_id');
                 $warehouse_id = $request->input('warehouse_id');
                 $warehouse_destination_id = $request->input('warehouse_destination_id');
                 $stock = $request->input('stock');
                 $quantity = $request->input('quantity');
                 $detail = $request->input('detail');

                 if($warehouse_id === $warehouse_destination_id) {
                     return  [
                         'success' => false,
                         'message' => 'El almacén destino no puede ser igual al de origen'
                     ];
                 }
                 if($stock < $quantity) {
                     return  [
                         'success' => false,
                         'message' => 'La cantidad a trasladar no puede ser mayor al que se tiene en el almacén.'
                     ];
                 }

                 $re_it_warehouse = ItemWarehouse::where([['item_id',$item_id],['warehouse_id', $warehouse_destination_id]])->first();

                 if(!$re_it_warehouse) {
                     return  [
                         'success' => false,
                         'message' => 'El producto no se encuentra registrado en el almacén destino.'
                     ];
                 }


                 $inventory = Inventory::findOrFail($id);

                 //proccess stock
                 $origin_inv_kardex = $inventory->inventory_kardex->first();
                 $origin_item_warehouse = ItemWarehouse::where([['item_id',$origin_inv_kardex->item_id],['warehouse_id', $origin_inv_kardex->warehouse_id]])->first();
                 $origin_item_warehouse->stock += $inventory->quantity;
                 $origin_item_warehouse->stock -= $quantity;
                 $origin_item_warehouse->update();


                 $destination_inv_kardex = $inventory->inventory_kardex->last();
                 $destination_item_warehouse = ItemWarehouse::where([['item_id',$destination_inv_kardex->item_id],['warehouse_id', $destination_inv_kardex->warehouse_id]])->first();
                 $destination_item_warehouse->stock -= $inventory->quantity;
                 $destination_item_warehouse->update();


                 $new_item_warehouse = ItemWarehouse::where([['item_id',$item_id],['warehouse_id', $warehouse_destination_id]])->first();
                 $new_item_warehouse->stock += $quantity;
                 $new_item_warehouse->update();

                 //proccess stock

                 //proccess kardex
                 $origin_inv_kardex->quantity = -$quantity;
                 $origin_inv_kardex->update();

                 $destination_inv_kardex->quantity = $quantity;
                 $destination_inv_kardex->warehouse_id = $warehouse_destination_id;
                 $destination_inv_kardex->update();
                 //proccess kardex

                 $inventory->warehouse_destination_id = $warehouse_destination_id;
                 $inventory->quantity = $quantity;
                 $inventory->detail = $detail;


                 $inventory->update();

                 return  [
                     'success' => true,
                     'message' => 'Traslado actualizado con éxito'
                 ];
             });

             return $result;
         }*/


        public function destroy($id)
        {

            DB::connection('tenant')->transaction(function () use ($id) {

                $record = Inventory::findOrFail($id);

                $origin_inv_kardex = $record->inventory_kardex->first();
                $destination_inv_kardex = $record->inventory_kardex->last();

                $destination_item_warehouse = ItemWarehouse::where([['item_id', $destination_inv_kardex->item_id], ['warehouse_id', $destination_inv_kardex->warehouse_id]])->first();
                $destination_item_warehouse->stock -= $record->quantity;
                $destination_item_warehouse->update();

                $origin_item_warehouse = ItemWarehouse::where([['item_id', $origin_inv_kardex->item_id], ['warehouse_id', $origin_inv_kardex->warehouse_id]])->first();
                $origin_item_warehouse->stock += $record->quantity;
                $origin_item_warehouse->update();

                $record->inventory_kardex()->delete();
                $record->delete();

            });


            return [
                'success' => true,
                'message' => 'Traslado eliminado con éxito'
            ];


        }

        public function stock($item_id, $warehouse_id)
        {

            $row = ItemWarehouse::where([['item_id', $item_id], ['warehouse_id', $warehouse_id]])->first();
            return [
                'stock' => ($row) ? $row->stock : 0
            ];

        }

        public function store(TransferRequest $request)
        {
            try{
                $result = DB::connection('tenant')->transaction(function () use ($request) {
                    $created_at = Carbon::parse($request->created_at);
                    //Log::info('date'.$created_at);
                    $row = InventoryTransfer::create([
                        'description' => $request->description,
                        'warehouse_id' => $request->warehouse_id,
                        'warehouse_destination_id' => $request->warehouse_destination_id,
                        'quantity' => count($request->items),
                        'client_id' => $request->client_id,
                    ]);
                    $row->created_at = $created_at;
                    $row->save();

                    foreach ($request->items as $it) {
                        $item = Item::find($it['id']);

                        if($it['lots_enabled'] == true || $it['lots_enabled'] == 1){
                            // si tiene Lotes se crea el kardex por lotes
                            foreach ($it['lots'] as $key => $value) {
                                //Log::info('Entro foreach - '.json_encode($value));
                                # code...
                                if ($value['checked'] == true && $value['compromise_quantity'] && $value['compromise_quantity'] > 0) {
                                    //Log::info('entro al if de value checked - '.json_encode($value));
                                    $inventory = new Inventory();
                                    $inventory->type = 2;
                                    $inventory->description = 'Traslado Lotes';
                                    $inventory->item_id = $it['id'];
                                    $inventory->warehouse_id = $value['warehouse_id'];
                                    $inventory->warehouse_destination_id = $request->warehouse_destination_id;
                                    $inventory->quantity = $value['compromise_quantity'];
                                    $inventory->inventories_transfer_id = $row->id;
                                    $inventory->lot_code = $value['code'];
                                    //Log::info('Inventory antes de guardar');
                                    $inventory->precio_perso = $item->purchase_mean_cost;
                                    $inventory->save();
                                    //Log::info('inventory guardado');

                                    //lotes origen
                                    $lotOrigin = ItemLotsGroup::where('item_id',$it['id'])
                                    ->where('code',$value['code'])
                                    ->where('warehouse_id', $value['warehouse_id'])
                                    ->first();
                                    Log::info('$lotOrigin - '.json_encode($lotOrigin));
                                    //$cantOrigin=$lotOrigin->quantity;

                                    //comprobar existencia producto
                                    $lotDest = ItemLotsGroup::where('item_id',$it['id'])
                                    ->where('code',$value['code'])
                                    ->where('warehouse_id', $request->warehouse_destination_id)
                                    ->first();
                                    Log::info('$lotDest - '.json_encode($lotDest));

                                    if(isset($lotDest)){

                                        //$cantDest=$lotOrigin->quantity;
                                        //Log::info('LotDestif - ');
                                        $lotDest->quantity += floatVal($value['compromise_quantity']);
                                        $lotDest->save();

                                    }else{
                                        ItemLotsGroup::create([
                                            'code' => $value['code'],
                                            'quantity' => floatVal($value['compromise_quantity']),
                                            'date_of_due' => $lotOrigin->date_of_due,
                                            'warehouse_id' => $request->warehouse_destination_id,
                                            'item_id' => $it['id']
                                        ]);

                                    }

                                    $lotOrigin->quantity -= floatVal($value['compromise_quantity']);
                                    $lotOrigin->save();

                                }
                            }
                        }elseif(isset($it['series_enabled']) && $it['series_enabled'] == true){
                            $quantity = null;
                            if(isset($it['quantity'])){
                                $quantity = $it['quantity'];
                            }elseif(isset($it['compromise_quantity'])){
                                $quantity = $it['compromise_quantity'];
                            }
                            //si tienes series
                            $inventory = new Inventory();
                            $inventory->type = 2;
                            $inventory->description = 'Traslado Serie';
                            $inventory->item_id = $it['id'];
                            $inventory->warehouse_id = $request->warehouse_id;
                            $inventory->warehouse_destination_id = $request->warehouse_destination_id;
                            $inventory->quantity = $quantity;
                            $inventory->inventories_transfer_id = $row->id;
                            $inventory->precio_perso = $item->purchase_mean_cost;
                            $inventory->save();

                            foreach ($it['lots'] as $lot) {

                                if (isset($lot['checked']) && $lot['checked'] == true) {
                                    $item_lot = ItemLot::findOrFail($lot['id']);
                                    $item_lot->warehouse_id = $inventory->warehouse_destination_id;
                                    $item_lot->update();

                                    $inventory->lot_code = $item_lot->series;
                                    $inventory->save();
                                }
                            }
                        }else{
                            if(isset($it['quantity']) && $it['quantity'] > 0){
                                $inventory = new Inventory();
                                $inventory->type = 2;
                                $inventory->description = 'Traslado';
                                $inventory->item_id = $it['id'];
                                $inventory->warehouse_id = $request->warehouse_id;
                                $inventory->warehouse_destination_id = $request->warehouse_destination_id;
                                $inventory->quantity = $it['quantity'];
                                $inventory->inventories_transfer_id = $row->id;
                                $inventory->precio_perso = $item->purchase_mean_cost;
                                $inventory->save();
                            }

                        }
                    }

                    return [
                        'success' => true,
                        'message' => 'Traslado creado con éxito'
                    ];
                });

                return $result;

            } catch (Exception $e) {
                //DB::rollback();
                Log::info('Error al generar traslado ');
                Log::error($e->getMessage());
            }

        }

        public function reverse($id){
            try{

                $transfer = InventoryTransfer::find($id);

                $reverseTransfer = new InventoryTransfer();
                $reverseTransfer->fill($transfer->toArray());
                $reverseTransfer->id =  null;
                $reverseTransfer->description = 'Reverso Traslado  de: '.$transfer->description;
                $reverseTransfer->warehouse_id = $transfer->warehouse_destination_id;
                $reverseTransfer->warehouse_destination_id= $transfer->warehouse_id;
                $reverseTransfer->save();

                foreach($transfer->inventories as $it){

                    $reverseInventory = new Inventory();
                    $reverseInventory->fill($it->toArray());
                    $reverseInventory->id = null;
                    $reverseInventory->warehouse_id = $it->warehouse_destination_id;
                    $reverseInventory->warehouse_destination_id= $it->warehouse_id;
                    $reverseInventory->description = 'Reverso '. $it->description;
                    $reverseInventory->save();

                }

                return[
                    'success'=> true,
                    'message'=>'Reverso generado satisfactoriamente'
                ];

            }catch(Exception $ex){
                Log::error('Error al generar reverso de Traslado');
                Log::error($ex);
                return[
                    'success'=> false,
                    'message'=>'Error al generar reversión del traslado ' .$ex->getMessage()
                ];
            }
        }

        public function searchItems(Request  $request)
        {
            $items = SearchItemController::getItemToTrasferWithSearch($request);
            return compact('items');

        }
        public function items($warehouse_id)
        {
            return ['items'=>SearchItemController::getItemToTrasferWithoutSearch($warehouse_id)];

            return [
                'items' => $this->optionsItemWareHousexId($warehouse_id),
            ];
        }



        /**
         * No se implementa
         *
         * @param \Modules\Inventory\Models\InventoryTransfer $inventoryTransfer
         *
         * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
         */
        public function excel(InventoryTransfer $inventoryTransfer)
        {

            return null;
            $export = new InventoryTransferExport();
            $export->setInventory($inventoryTransfer);
            return $export->download('Reporte_Traslado_' . $inventoryTransfer->id . '_' . date('YmdHis') . '.xlsx');
        }

        public function getInventoryTransferData(InventoryTransfer $inventoryTransfer)
        {
            return null;
            // return $this->excel(($inventoryTransfer));
            $data = $inventoryTransfer->getPdfData();
            Log::info(json_encode($data));
            $pdf = PDF::loadView('inventory::transfers.export.pdf', compact('data'));
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Reporte_Traslado_' . $inventoryTransfer->id . '_' . date('YmdHis');
            return $pdf->download($filename . '.pdf');

        }


        /**
         * Genera un pdf para nota de traslado
         *
         * @param \Modules\Inventory\Models\InventoryTransfer $inventoryTransfer
         *
         * @return \Illuminate\Http\Response
         */
        public function getPdf(InventoryTransfer $inventoryTransfer): \Illuminate\Http\Response
        {
            $data = $inventoryTransfer->getPdfData();
            Log::info('data pdf - '.json_encode($data));
            $transfer_id = $inventoryTransfer->id;
            $lote = DB::connection('tenant')->select("SELECT i.lot_code FROM items i
            RIGHT JOIN inventories inv ON i.id = inv.item_id
            RIGHT JOIN inventories_transfer it ON inv.inventories_transfer_id = it.id
            WHERE it.id = :transfer_id", ['transfer_id' => $transfer_id]);
            Log::info($lote);
            // return View('inventory::transfers.export.pdf', compact('data'));
            $pdf = PDF::loadView('inventory::transfers.export.pdf', compact('data', 'lote'));
            $pdf->setPaper('A4', 'portrait');
            // $pdf->setPaper('A4', 'landscape');
            $filename = 'Reporte_Traslado_' . $inventoryTransfer->id . '_' . date('YmdHis');

            return $pdf->download($filename . '.pdf');

        }


    }

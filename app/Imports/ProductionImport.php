<?php

    namespace App\Imports;

use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemSupplyLot;
use App\Models\Tenant\Person;
    use App\Models\Tenant\PersonType;
use App\Models\Tenant\ProductionSupply;
use Exception;
    use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
    use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\Production\Http\Controllers\ProductionController;
use Modules\Production\Models\Machine;
use Modules\Production\Models\Production;

    class ProductionImport implements ToCollection
    {
        use Importable;

        protected $data;

        public function collection(Collection $rows)
        {
            $total = count($rows);
            $registered = 0;
            $noRegistered = '';
            unset($rows[0]);

            $config = Company::first();

            foreach ($rows as $row) {
                Log::info(json_encode($row));
                if($row[0] == null){
                    return;
                }
                try{
                    
                    $item = Item::where('internal_id',$row[0])->orWhere('id',$row[0])->first();
                    $maquina = Machine::where('name',$row[3])->orWhere('model',$row[3])->first();
                    $bodega = Establishment::where('code',$row[2])->first();

                    $data['user_id'] = auth()->user()->id;
                    $data['soap_type_id']= $config->soap_type_id;
                    $data['item_id']= $item->id;
                    $data['quantity']=$row[1];
                    $data['warehouse_id']=$bodega->id;
                    $data['machine_id']=$maquina->id;
                    $data['production_order']=$row[4];
                    $data['name']=$row[5];
                    $data['comment']=$row[6];
                    $data['lot_code']=$row[7];
                    $data['state_type_id']= '01';


                    $production = Production::where('production_order', $row[4])
                        ->where('name', $row[5])
                        ->first();

                    if (!$production) {
                        $production = production::create($data);
                        $registered += 1;

                        $itemSupplo = ProductionController::optionsItemProduction($item->id);

                        foreach($itemSupplo->supplies as $supplie){

                            $production_supply = new ProductionSupply();
                            $production_id = $production->id;
                            $qty = $supplie['quantityD'] ?? 0;
                            $production_supply->production_name = $production->name;
                            $production_supply->production_id = $production_id;
                            $production_supply->item_supply_name = $supplie['description'];
                            $production_supply->item_supply_id = $supplie['id'];
                            $production_supply->warehouse_name = $supplie['warehouse_name'] ?? null;
                            $production_supply->warehouse_id = $supplie['warehouse_id'] ?? null;
                            $production_supply->quantity = (float) $qty;
                            $production_supply->cost_per_unit = (isset($supplie['cost_per_unit']))?$supplie['cost_per_unit']:null ;
                            $production_supply->save();

                            $lots_group = $item["lots_group"];
                            foreach ($lots_group as $lots) {

                                $item_lots_groups = new ItemSupplyLot();
                                $item_lots_groups->item_supply_id = $supplie['id'];
                                $item_lots_groups->item_supply_name = $supplie['description'];
                                $item_lots_groups->lot_code = $lots["code"];
                                $item_lots_groups->lot_id = $lots["id"];
                                $item_lots_groups->production_name = $production->name;
                                $item_lots_groups->production_id = $production_id;
                                $item_lots_groups->quantity = 0;
                                $item_lots_groups->expiration_date = $lots["date_of_due"];
                                $item_lots_groups->save();
                            }
                        }
                    }
                }catch(Exception $ex){
                    Log::error("No se pudo procesar la orden de produccion: ".$row[4]." - ".$row[5]);
                    Log::error($ex->getMessage());
                    $noRegistered .= $row[4]." - ".$row[5].", ";
                }
            }
            $this->data = compact('total', 'registered','noRegistered');
        }

        public function getData()
        {
            return $this->data;
        }
    }

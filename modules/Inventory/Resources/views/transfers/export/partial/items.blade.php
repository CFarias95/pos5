<div class="">
    <div class=" ">
        <table class="full-width">
            <?php

            use App\Models\Tenant\Company;
            use App\Models\Tenant\Configuration;
            use App\Models\Tenant\User;
            use Modules\Inventory\Models\Inventory;
            use Modules\Inventory\Models\InventoryTransfer;
            use Modules\Inventory\Models\Warehouse;
            use Illuminate\Support\Carbon;
            use Illuminate\Database\Eloquent\Collection;

            $motivo = !empty($data['motivo']) ? $data['motivo'] : null;
            /** @var Carbon $created_at */
            /** @var Warehouse $warehouse_to */
            /** @var Warehouse $warehouse_from */
            /** @var User $user */
            /** @var Collection|Inventory[] $inventories */

            $created_at = !empty($data['created_at']) ? $data['created_at'] : Carbon::now();
            $quantity = !empty($data['quantity']) ? $data['quantity'] : 0;

            $warehouse_from = !empty($data['warehouse_from']) ? $data['warehouse_from'] : new Warehouse();
            $warehouse_to = !empty($data['warehouse_to']) ? $data['warehouse_to'] : new Warehouse();
            $user = !empty($data['user']) ? $data['user'] : new User();
            $configuration = !empty($data['configuration']) ? $data['configuration'] : new Configuration();
            $company = !empty($data['company']) ? $data['company'] : new Company();

            $pdf = $pdf ?? false;

            ?>


            <thead>
                <tr>
                    <th class="five-width text-center">ITEM</th>
                    <!--<th class="ten-width text-left">CODIGO INTERNO</th>-->
                    <th class="fourteen-width text-left">DESCRIPCIÓN PRODUCTO</th>
                    <th class="ten-width">UNIDAD</th>
                    <th class="eight-width">CANT</th>
                    <th class="twelve-width">LOTE/SERIE</th>

                    <!--        <th width="10%">SERIE</th>-->
                </tr>
            </thead>
            <tbody>
                @foreach ($inventories as $index => $inventory)
                    <?php
                    /** @var \Modules\Inventory\Models\Inventory $inventory */
                    /*$item = $inventory->item;
                    $itemCollection = $item->getCollectionData($configuration);
                    $itemCollection['description'] = substr($itemCollection['name'] . ' / ' . $itemCollection['description'].' / '.$itemCollection['model'], 0, 49);
                    $itemCollection['internal_id'] = substr($itemCollection['internal_id'], 0, 10);
                    $itemCollection['unit_type_text'] = substr($itemCollection['unit_type_text'], 0, 10);
                    $qty = $inventory->quantity;

                    /*
                @todo BUSCAR DONDE SE GUARDA LA SERIE en modules/Inventory/Http/Controllers/TransferController.php 237
                */

                    ?>
                    <tr>
                        <td class="celda text-center">{{ $index + 1 }}</td>
                        <!--<td class="celda text-left"></td>-->
                        <td class="celda text-left">{{ ($inventory->item->name)?$inventory->item->name.' / ':'' }}{{ $inventory->item->description }}</td>
                        <td class="celda">{{ $inventory->item->unit_type->description }}</td>
                        <td class="celda">{{ $inventory->quantity }}</td>
                        <td class="celda">{{ $inventory->lot_code }}</td>
                        <!--            <td>SERIE</td>-->
                    </tr>
                @endforeach
            </tbody>
        </table>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        {{-- <table style="border: none" class="full-width">
            <tr>
                <td style="border-bottom:solid;"></td>
                <br>
                <td><strong>Entregado por:</strong></td>
            </tr>
            <tr>
                <td style="border-bottom:solid;"></td>
                <br>
                <td><strong>Recibido por:</strong></td>
            </tr>
        </table> --}}
        <table class="full-width mt-4 mb-5" style="border: none">
            <tr>
                <td width="50%" class="text-center">________________________________________</td>
                <td width="50%" class="text-center">________________________________________</td>
            </tr>
            <tr>
                <td width="50%" class="text-center"><b>Entregado por:</b></td>
                <td width="50%" class="text-center"><b>Recibido por:</b></td>
            </tr>
        </table>
    </div>
</div>

@php
    $date_end = $produccion->date_end;
    $date_end_object = DateTime::createFromFormat('Y-m-d', $date_end);
    $validity = $producido->validity;
    $date_end_object->modify("+$validity days");
    $formatted_date_end = $date_end_object->format('Y-m-d');
    $totalKg = 0;
    foreach ($production_items as $supplies) {
        foreach ($produccion->production_supplies as $supply) {
            if ($supplies->id == $supply->item_supply_original_id) {
                $totalKg += $supply->quantity;
            }
        }
    }
    $totalKgEM = 0;
    foreach ($empaque_items as $empaques) {
        foreach ($produccion->production_supplies as $supply) {
            if ($empaques->id == $supply->item_supply_original_id) {
                $totalKgEM += $supply->quantity;
            }
        }
    }
    $logo = "storage/uploads/logos/{$company->logo}";
@endphp
<center>
    <img src="{{ $logo }}" style="width: 25%;" />
</center>
<br>
<br>
<table class="full-width">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>N° de ficha</th>
            <th>Cantidad a Producir</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $produccion->date_end }}</td>
            <td>{{ $produccion->production_order }}</td>
            <td>{{ $produccion->quantity }}</td>
        </tr>
    </tbody>
</table>
<br>
<br>

<table class="full-width">
    <thead>
        <tr>
            <th class="section-title" colspan="4">{{ $formatted_date_end }} || Materia Prima Solicitada a Bodega</th>
        </tr>
        <tr>
            <th>Detalle</th>
            <th>KG</th>
            <th>Lote</th>
            <th>Control</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($production_items as $supplies)
            <tr>
                <td>{{ $supplies->name }}</td>
                @foreach ($produccion->production_supplies as $supply)
                    @if ($supplies->id == $supply->item_supply_original_id)
                        <td>{{ number_format($supply->quantity, 6, ',', '.') }}</td>
                    @endif
                @endforeach
                @foreach ($produccion->production_inventories as $inventory)
                    @if ($inventory->item_id == $supply->item_supply_original_id)
                        <td>{{ $inventory->lot_code }}</td>
                    @endif
                @endforeach
                @foreach ($produccion->production_supplies as $supply)
                    @if ($supplies->id == $supply->item_supply_original_id)
                        <td>{{ $supply->checked ? 'Sí' : 'No' }}</td>
                    @endif
                @endforeach
            </tr>
        @endforeach
        <tr>
            <th>Total KG</th>
            <td>{{ $totalKg }}</td>
            <td style="border-bottom: none; border-right: none;"></td>
            <td style="border-bottom: none; border-right: none; border-left: none;"></td>
        </tr>

    </tbody>
</table>
<br>
<br>

<table class="full-width">
    <thead>
        <tr>
            <th class="section-title" colspan="4">Empaques</th>
        </tr>
        <tr>
            <th>Detalle</th>
            <th>KG</th>
            <th>Lote</th>
            <th>Control</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($empaque_items as $empaques)
            <tr>
                <td>{{ $empaques->name }}</td>
                @foreach ($produccion->production_supplies as $supply)
                    @if ($empaques->id == $supply->item_supply_original_id)
                        <td>{{ number_format($supply->quantity, 6, ',', '.') }}</td>
                    @endif
                @endforeach
                @foreach ($produccion->production_inventories as $inventory)
                    @if ($inventory->item_id == $supply->item_supply_original_id)
                        <td>{{ $inventory->lot_code }}</td>
                    @endif
                @endforeach
                @foreach ($produccion->production_supplies as $supply)
                    @if ($empaques->id == $supply->item_supply_original_id)
                        <td>{{ $supply->checked ? 'Sí' : 'No' }}</td>
                    @endif
                @endforeach
            </tr>
        @endforeach
        <tr>
            <th>Total KG</th>
            <td>{{ $totalKgEM }}</td>
            <td style="border-bottom: none; border-right: none;"></td>
            <td style="border-bottom: none; border-right: none; border-left: none;"></td>
        </tr>
    </tbody>
</table>
<br>
<br>

<table class="full-width">
    <tbody>
        <tr>
            <th colspan="2">Comentario</th>
            <td colspan="2">{{ $produccion->comment ? $produccion->comment : 'N/A' }}</td>
        </tr>
    </tbody>
</table>
<br>
<br>

<table class="full-width">
    <tbody>
        <tr>
            <th>Colaborador de Producción</th>
            <td>{{ $produccion->production_collaborator ? $produccion->production_collaborator : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Colaborador de Mezcla</th>
            <td>{{ $produccion->mix_collaborator ? $produccion->mix_collaborator : 'N/A' }}</td>
        </tr>
    </tbody>
</table>
<br>
<br>
<table class="full-width">
    <thead>
        <tr>
            <th class="section-title" colspan="4">Presentación Producto Fabricado: {{ $produccion->item->name }}
            </th>
        </tr>
        <tr>
            <th colspan="2">Lote: </th>
            <td colspan="2">{{ $produccion->lot_code }}</td>
        </tr>
        <tr>
            <th colspan="2">Cantidad Producida</th>
            <td colspan="2">{{ $produccion->quantity }}</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th colspan="2">Merma</th>
            <td colspan="2">{{ $produccion->imperfect }}</td>
        </tr>
    </tbody>
</table>


<style>
    body {
        font-family: 'Arial', sans-serif;
    }

    .full-width {
        width: 100%;
        border-collapse: collapse;
    }

    .full-width th,
    .full-width td {
        border: 1px solid black;
        text-align: left;
        padding: 8px;
    }

    .full-width th {
        background-color: #f2f2f2;
    }

    .section-title {
        background-color: #f2f2f2;
        text-align: center;
        font-weight: bold;
    }
</style>

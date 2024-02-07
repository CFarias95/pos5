@php
//Log::info('Item producir - '.$produccion->item->validity);
@endphp
{{ json_encode($produccion->production_inventories) }}

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

<table class="full-width">
    <thead>
        <tr>
            <th class="section-title" colspan="4">  ||  Materia Prima Solicitada a Bodega</th>
        </tr>
        <tr>
            <th>Detalle</th>
            <th>KG</th>
            <th>Lote</th>
            <th>Control</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($produccion->production_supplies as $supplies)
            <tr>
                <td>{{ $supplies->item_supply_name }}</td>
                <td>{{ number_format($supplies->quantity, 6, ',', '.') }}</td>
                <td>{{ $supplies->item_supply->item->lot_code }}</td>
                <td>{{ $supplies->checked ? 'Sí' : 'No' }}</td>
            </tr>
        @endforeach
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
    .full-width th, .full-width td {
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
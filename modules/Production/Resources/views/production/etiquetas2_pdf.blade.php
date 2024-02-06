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

<table class="full-width">
    <thead>
        <tr>
            <th class="section-title" colspan="4">Materia Prima Solicitada a Bodega</th>
        </tr>
        <tr>
            <th>Detalle</th>
            <th>KG</th>
            <th>Lote</th>
            <th>Control</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($production_supplies as $supplies)
            <tr>
                <td>{{ $supplies->item_supply_name }}</td>
                <td>{{ number_format($supplies->quantity, 2, ',', '.') }}</td>
                <td>{{ optional($supplies->itemSupply)->item ? optional($supplies->itemSupply->item)->lot_code : 'N/A' }}</td>
                <td>{{ $supplies->checked ? 'Sí' : 'No' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

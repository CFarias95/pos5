<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
          content="application/pdf; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible"
          content="ie=edge">
    <title>Inventario</title>
    <style>
        html {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-spacing: 0;
            border: 1px solid black;
        }

        .celda {
            text-align: center;
            padding: 5px;
            font-size: 10px;
            border: 0.1px solid black;
        }

        th {
            font-size: 10px;
            padding: 5px;
            text-align: center;
            border-color: #0088cc;
            border: 0.1px solid black;
        }

        .title {
            font-weight: bold;
            padding: 5px;
            font-size: 20px !important;
            text-decoration: underline;
        }

        p > strong {
            margin-left: 5px;
            font-size: 13px;
        }

        thead {
            font-weight: bold;
            background: #0088cc;
            color: white;
            text-align: center;
        }
    </style>
</head>
<body>
<div>
    <p align="center"
       class="title"><strong>Reporte {{ $tipo }} Inventario</strong></p>
    <p align="center"
       class="title"><strong>INV - {{ str_pad($records->id,9,'0',STR_PAD_LEFT) }}</strong></p>
</div>
<div style="margin-top:20px; margin-bottom:20px;">
    <table>
        <tr>
            <td>
                <p><strong>Empresa: </strong>{{$company->name}}</p>
            </td>
            <td>
                <p><strong>Fecha y Hora: </strong>{{$records->created_at->format('y-m-d h:i:s')}}</p>
            </td>
            <td>
                <p><strong>Ruc: </strong>{{$company->number}}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p><strong>Producto: </strong>{{$records->item->name}}</p>
                @if($records->lot_code)
                <p><strong>Lote: </strong>{{$records->lot_code}}</p>
                <p><strong>F. Vencimiento: </strong>
                @foreach($records->item->lots_group as $lot)
                    @if($lot->code == $records->lot_code)
                    {{ $lot->date_of_due}}
                    @endif
                @endforeach
                </p>
                @endif
            </td>
            <td>
                <p><strong>Cantidad: </strong>{{$records->quantity}}</p>
            </td>
            <td>
                <p><strong>Destino: </strong>{{$records->warehouse->description}}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p><strong>Motivo: </strong>{{$records->description}}</p>
            </td>
            <td>
                @if($tipo == 'Ajuste')
                <p><strong>Fecha: </strong>{{$records->created_at->format('y-m-d h:i:s')}}</p>
                @else
                <p><strong>Fecha: </strong>{{$records->date_of_issue}}</p>
                @endif
            </td>
            <td>
                @if($tipo == 'Ajuste')
                <p><strong>Ajuste: </strong>{{$records->quantity}}</p>
                <p><strong>Stock en el sistema: </strong>{{$records->system_stock}}</p>
                <p><strong>Stock real: </strong>{{$records->real_stock}}</p>
                @else
                <p><strong>Comentario: </strong>{{$records->comments}}</p>
                @endif

            </td>
        </tr>
    </table>
</div>

</body>
</html>

@php
Log::info('records - '.json_encode($records));
@endphp
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Listado de Inventarios</title>
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
                text-align: left;
                padding: 5px;
                border: 0.1px solid black;
            }
            
            th {
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
            
            p>strong {
                margin-left: 5px;
                font-size: 13px;
            }
            
            thead {
                font-weight: bold;
                background: #0088cc;
                color: white;
                text-align: center;
            }
            p {
                text-align:right;
            }
            hr {
                display: block;
                margin-top: 0.5em;
                margin-bottom: 0.5em;
                margin-left: auto;
                margin-right: auto;
                border-style: inset;
                border-width: 1px;
            }
            .container {
                display: flex;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div id="column_1">
                <p><strong>Empresa: </strong>{{$company->name}}</p>
                <p><strong>Usuario: </strong>{{$usuario_log->name}}</p>
                <p><strong>Fecha: </strong>{{date('d-m-Y')}}</p>
            </div>
        </div>
        <hr>
        <div>
            <h2 align="center" class="title"><strong>Listado Inventarios</strong></h2>
        </div>
        <div style="margin-top:20px; margin-bottom:20px;">
            
        </div>
        @if(!empty($records))
            <div class="">
                <div class=" ">
                    <table class="">
                        <thead>
                            <tr>
                                <th class="">ID</th>
                                <th class="">ID Interno</th>
                                <th class="">Descripción</th>
                                <th class="">Descripción Completa</th>
                                <th class="">Descripción Almacen</th>
                                <th class="">Precio Promedio</th>
                                <th class="">Stock</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $row)
                                <tr>
                                    <td class="celda">{{ $row['id'] }}</td>
                                    <td class="celda">{{ $row['item_internal_id'] }}</td>
                                    <td class="celda">{{ $row['item_description'] }}</td>
                                    <td class="celda">{{ $row['item_fulldescription'] }}</td>
                                    <td class="celda">{{ $row['warehouse_description'] }}</td>
                                    <td class="celda">{{ $row['purchase_mean_cost'] }}</td>
                                    <td class="celda">{{ $row['stock'] }}</td>
                                    
                                    
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="callout callout-info">
                <p>No se encontraron registros.</p>
            </div>
        @endif
    </body>
</html>
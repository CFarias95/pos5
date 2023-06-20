@php
$image = "storage/uploads/logos/{$records->image_small}";
@endphp
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Etiquetas</title>
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
                text-align:center;
            }
            img {
                text-align:center;
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
                <img src = "data:{{mime_content_type(public_path("{$image}"))}};base64, {{base64_encode(file_get_contents(public_path("{$image}")))}}" alt="{{$records->name}}" class="" style="margin-left: 50px; padding-bottom: 0px; max-width: 150px" >
            </div>
        </div>
        <div>
            <h2 align="center" class="title"><strong>Etiquetas</strong></h2>
        </div>
        <div style="margin-top:20px; margin-bottom:20px;">
        <div>
            <label><strong>Producto:</strong> {{$records->name}}</label>
            <br>
            <label><strong>Lote:</strong> {{$records->lot_code}}</label>
            <br>
            <label><strong>LoteF. Elaboracion:</strong> {{$fechas[0]->date_start}}</label>
            <br>
            <label><strong>LoteF. Vencimiento:</strong> {{$fechas[0]->date_end}}</label>
            <br>
        </div>
        <div>
            <label><strong>Ingregientes/Insumos: </strong></label>
            @foreach($insumos as $insumo)
                <label>{{$insumo->name}};</label>
            @endforeach
        </div>
        </div>
        @if(!empty($records))
            <div class="">
                <div class=" ">
                    <table class="">
                        <thead>
                            <tr>
                                <th class="text-center">Categoria</th>
                                <th class="text-center">Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records->attributes as $row)
                                <tr>
                                    <td class="celda">{{$row->description}}</td>
                                    <td class="celda">{{$row->value}}</td> 
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <div>
                <p alignment="center">________________________</p>
                <p alignment="center">{{ $usuario_log->name }}</p>
                <br>
                <p alignment="center">{{ $company->name }}</p>                
            </div>
        @else
            <div class="callout callout-info">
                <p>No se encontraron registros.</p>
            </div>
        @endif
    </body>
</html>
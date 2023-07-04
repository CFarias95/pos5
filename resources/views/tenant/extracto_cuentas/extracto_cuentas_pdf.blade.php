@php
$logo = "storage/uploads/logos/{$company->logo}";
@endphp
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Reporte de Extracto Cuentas</title>
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
            img {
                text-align:left;
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
            <div id="column_2">
                <img src = "data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$company->name}}" class="company_logo" style="margin-left: 50px; padding-bottom: 0px; max-width: 150px" >
            </div>
            <div id="column_1">
                <p><strong>Empresa: </strong>{{$company->name}}</p>
                <p><strong>Usuario: </strong>{{$usuario_log->name}}</p>
                <p><strong>Fecha: </strong>{{date('d-m-Y')}}</p>
            </div>
        </div>
        <hr>
        <div>
            <h2 align="center" class="title"><strong>Reporte de Extracto Cuentas</strong></h2>
        </div>
        <div style="margin-top:20px; margin-bottom:20px;">
            
        </div>
        @if(!empty($records))
            <div class="">
                <div class=" ">
                    <table class="">
                        <thead>
                            <tr>
                                <th class="">Asiento</th>
                                <th class="">Linea</th>
                                <th class="">Cuenta</th>
                                <th class="">Descripción cuenta</th>
                                <th class="">Comentario</th>
                                <th class="">Fecha</th>
                                <th class="">Serie</th>
                                <th class="">Número</th>
                                <th class="">Debe</th>
                                <th class="">Haber</th>
                                <th class="">Saldo</th>
                                <th class="">C_C</th>
                                <th class="">Id Persona</th>
                                <th class="">Nombre Persona</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $row)
                                <tr>
                                    <td class="celda">{{ $row->Asiento }}</td>
                                    <td class="celda">{{ $row->Linea }}</td>
                                    <td class="celda">{{ $row->Cuenta }}</td>
                                    <td class="celda">{{ $row->Descripcion_cuenta }}</td>
                                    <td class="celda">{{ $row->Comentario }}</td>
                                    <td class="celda">{{ $row->Fecha }}</td>
                                    <td class="celda">{{ $row->Serie }}</td>
                                    <td class="celda">{{ $row->Numero }}</td>
                                    <td class="celda">{{ $row->Debe }}</td>
                                    <td class="celda">{{ $row->Haber }}</td>
                                    <td class="celda">{{ $row->Saldo }}</td>
                                    <td class="celda">{{ $row->C_C }}</td>
                                    <td class="celda">{{ $row->Id_persona }}</td>
                                    <td class="celda">{{ $row->Nombre_persona }}</td>
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
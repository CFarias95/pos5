@php
$logo = "storage/uploads/logos/{$company->logo}";
//Log::info('data - '.json_encode($data1));
@endphp
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Conciliación bancaria</title>
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
                width: 50%;
                height: auto;
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
                <img src="{{ $logo }}" class="img-resize"/>
            </div>
            <div id="column_1">
                <p><strong>Empresa: </strong>{{$company->name}}</p>
                <p><strong>Usuario: </strong>{{$usuario_log->name}}</p>
                <p><strong>Fecha: </strong>{{date('d-m-Y')}}</p>
            </div>
        </div>
        <hr>
        <div>
            <h2 align="center" class="title"><strong>Conciliación bancaria</strong></h2>
        </div>
        <div style="margin-top:20px; margin-bottom:20px;">
            
        </div>
        @if(!empty($records))
            <div class="">
                <div class=" ">
                    <table class="">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Saldo Inicial</th>
                                <th class="text-center">Total Debe</th>
                                <th class="text-center">Total Haber</th>
                                <th class="text-center">Diferencia</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Creado por</th>
                                <th class="text-center">Cta Movimiento</th>
                                <th class="text-center">Fecha Conciliación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $row)
                                <tr>
                                    <td class="celda">{{$row->id}}</td>
                                    <td class="celda">{{$row->initial_value}}</td>
                                    <td class="celda">{{$row->total_debe}}</td>  
                                    <td class="celda">{{$row->total_haber}}</td>
                                    <td class="celda">{{$row->diference_value}}</td> 
                                    <td class="celda">{{$row->status == 0 ? 'Creada' : 'Cerrada'}}</td> 
                                    <td class="celda">{{$user->name}}</td> 
                                    <td class="celda">{{$account->description}}</td> 
                                    <td class="celda">{{ $row->created_at->format('d-m-Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <br>
            <br>
            <br>
            @if(!empty($entries))
                <div class="">
                    <div class=" ">
                        <div>
                            <h2 align="center" class="title"><strong>Conciliados</strong></h2>
                        </div>
                        <div style="margin-top:20px; margin-bottom:20px;">
                        <table class="">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Asiento</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Commentario</th>
                                    <th class="text-center">Debe</th>
                                    <th class="text-center">Haber</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($entries as $entry)
                                    <tr>
                                        <td class="celda">{{$entry->account->id}}</td> 
                                        <td class="celda">{{$entry->account->filename}}</td> 
                                        <td class="celda">{{$entry->account->seat_date}}</td> 
                                        <td class="celda">{{$entry->account->comment}}</td> 
                                        <td class="celda">{{$entry->debe}}</td> 
                                        <td class="celda">{{$entry->haber}}</td> 
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="callout callout-info">
                    <p>No se encontraron registros asociados.</p>
                </div>
            @endif
            <br>
            <br>
            <br>
            @if(!empty($data1))
                <div class="">
                    <div class=" ">
                        <div>
                            <h2 align="center" class="title"><strong>Sin Conciliar</strong></h2>
                        </div>
                        <div style="margin-top:20px; margin-bottom:20px;">
                        <table class="">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Asiento</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Commentario</th>
                                    <th class="text-center">Debe</th>
                                    <th class="text-center">Haber</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data1 as $entry1)
                                    <tr>
                                        <td class="celda">{{$entry1->account->id}}</td> 
                                        <td class="celda">{{$entry1->account->filename}}</td> 
                                        <td class="celda">{{$entry1->account->seat_date}}</td> 
                                        <td class="celda">{{$entry1->account->comment}}</td> 
                                        <td class="celda">{{$entry1->debe}}</td> 
                                        <td class="celda">{{$entry1->haber}}</td> 
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="callout callout-info">
                    <p>No se encontraron registros asociados.</p>
                </div>
            @endif
        @else
            <div class="callout callout-info">
                <p>No se encontraron registros.</p>
            </div>
        @endif
    </body>

</html>
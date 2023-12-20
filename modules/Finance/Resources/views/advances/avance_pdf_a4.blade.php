@php
    Log::info('data'.json_encode($collection));
@endphp
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Anticipo</title>
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
        </style>
    </head>
    <body>
        <div>
            <p align="center" class="title"><strong>Anticipo</strong></p>
        </div>
        <div style="margin-top:20px; margin-bottom:20px;">
            <table>
                <tr>
                    <td>
                        <p><strong>Empresa: </strong>{{$company->name}}</p>
                    </td>
                    <td>
                        <p><strong>Fecha: </strong>{{date('Y-m-d')}}</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p><strong>Ruc: </strong>{{$company->number}}</p>
                    </td>
                    {{-- <td> 
                        <p><strong>Establecimiento: </strong>{{$establishment->address}} - {{$establishment->department->description}} - {{$establishment->district->description}}</p>
                    </td>--}}
                </tr>
            </table>
        </div>
        @if(!empty($collection))
            <div class="">
                <div class=" "> 
                    <table class="">
                        <thead>
                            <tr>
                                <th class="">#</th>
                                <th class="">Método</th>
                                <th class="">Cliente</th>
                                <th class="">Valor</th>
                                <th class="">Valor usado</th>
                                <th class="">Valor libre</th>
                                <th class="">Notas</th>
                               
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($collection as $data)
                                <tr>
                                <td class="celda">1</td> 
                                <td class="celda">{{$data['method']}}</td> 
                                <td class="celda">{{$data['cliente']}}</td>
                                <td class="celda">{{$data['valor']}}</td>
                                <td class="celda">{{$data['used']}}</td>  
                                <td class="celda">{{$data['free']}}</td>
                                <td class="celda">{{$data['observation']}}</td>  
                                       
                            </tr>
                            @endforeach
                        </tbody>                      
                        <tfoot>
                            <tr>
                            </tr> 
                        </tfoot>
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

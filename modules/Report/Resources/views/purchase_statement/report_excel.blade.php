<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Compras</title>
    </head>
    <body>
        <div>
            <h3 align="center" class="title"><strong>Extracto Compras</strong></h3>
        </div>
        <br>
        <div style="margin-top:20px; margin-bottom:15px;">
            <table>
                <tr>
                    <td>
                        <p><b>Empresa: </b></p>
                    </td>
                    <td align="center">
                        <p><strong>{{$company->name}}</strong></p>
                    </td>
                    <td>
                        <p><strong>Fecha: </strong></p>
                    </td>
                    <td align="center">
                        <p><strong>{{date('Y-m-d')}}</strong></p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p><strong>Ruc: </strong></p>
                    </td>
                    <td align="center"> <p>{{ $company->number }} </p></td>
                    <td>
                        <p><strong>Establecimiento: </strong></p>
                    </td>
                    <td align="center">{{$establishment->address}} - {{$establishment->department->description}} - {{$establishment->district->description}}</td>
                </tr>
            </table>
        </div>
        <br>
        @if(!empty($records))
        <div class="">
            <div class=" ">
                @php
                    $keys = $records[0];
                @endphp
                <table class="">
                    <thead>
                        <tr>
                            @foreach($keys as $key => $value)
                            <th><strong> {{ $key }} </strong></th>
                            @endforeach

                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $key => $value)
                        <tr>
                            @foreach($value as $v)
                            <td>{{ $v }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div>
            <p>No se encontraron registros.</p>
        </div>
        @endif
    </body>
</html>

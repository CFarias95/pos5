<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Compras Base e Impuestos</title>
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
            <div id="column_1">
                <p><strong>Empresa: </strong>{{$company->name}}</p>
                <p><strong>Usuario: </strong>{{$usuario_log->name}}</p>
                <p><strong>Fecha: </strong>{{date('d-m-Y')}}</p>
            </div>
        </div>
        <hr>
        <div>
            <h2 align="center" class="title"><strong>Compras Bases e Impuestos</strong></h2>
        </div>
        <div style="margin-top:20px; margin-bottom:20px;">
            
        </div>
        @if(!empty($records))
            <div class="">
                <div class=" ">
                    <table class="">
                        <thead>
                            <tr>
                                <th class="">Tipo Doc. Interno</th>
                                <th class="">Tipo Documento</th>
                                <th class="">Código Tipo Doc. SRI</th>
                                <th class="">Tipo Doc. SRI</th>
                                <th class="">Serie Interna</th>
                                <th class="">Número interno</th>
                                <th class="">Secuencial</th>
                                <th class="">Número Autorización</th>
                                <th class="">Nombre Proveedor</th>
                                <th class="">CI/RUC</th>
                                <th class="">Tipo</th>
                                <th class="">Fecha Documento</th>
                                <th class="">Base IVA 12%</th>
                                <th class="">Base IVA 0%</th>
                                <th class="">Total IVA</th>
                                <th class="">Base Imponible</th>
                                <th class="">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $row)
                                <tr>
                                    <td class="celda">{{ $row->Tipodocinterno }}</td>
                                    <td class="celda">{{ $row->Tipodocumento }}</td>
                                    <td class="celda">{{ $row->Codtipodocsri }}</td>
                                    <td class="celda">{{ $row->Tipodocsri }}</td>
                                    <td class="celda">{{ $row->Serieinterna }}</td>
                                    <td class="celda">{{ $row->Numerointerno }}</td>
                                    <td class="celda">{{ $row->secuencial }}</td>
                                    <td class="celda">{{ $row->Numautorizacion }}</td>
                                    <td class="celda">{{ $row->Nombreproveedor }}</td>
                                    <td class="celda">{{ $row->CIRUC }}</td>
                                    <td class="celda">{{ $row->Tipo }}</td>
                                    <td class="celda">{{ $row->fechadoducmento }}</td>
                                    <td class="celda">{{ $row->Baseiva12 }}</td>
                                    <td class="celda">{{ $row->Baseiva0 }}</td>
                                    <td class="celda">{{ $row->Totaliva }}</td>
                                    <td class="celda">{{ $row->Baseimponible }}</td>
                                    <td class="celda">{{ $row->total }}</td>
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
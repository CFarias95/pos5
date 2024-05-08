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
            .table1 {
                width: 100%;
                border-spacing: 0;
                border: 0px;
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
        <div>
            <h2 align="center" class="title"><strong>Conciliación bancaria</strong></h2>
        </div>
        @if(!empty($bankReconciliation))
                <div class="">
                    <table class="">
                        <thead>
                            <tr>
                                <th class="text-center">Fecha</th>
                                <th class="text-center">Transaccion</th>
                                <th class="text-center">Se</th>
                                <th class="text-center">Documento</th>
                                <th class="text-center">No. Cheque</th>
                                <th class="text-center">Fecha Cobro</th>
                                <th class="text-center">Valor</th>
                                <th class="text-center">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="celda">{{$bankReconciliation->created_at->format('m-Y')}}</td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda"><strong>SALDO CONTABLE</strong></td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">$</td>
                                <td class="celda">{{$SaldoContable}}</td>
                            </tr>
                            <tr>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda"><strong>CHEQUE GIRADO Y NO COBRADO</strong></td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">$</td>
                                <td class="celda">$</td>
                            </tr>
                            @foreach($chequesGNC as $entry1)
                            <tr>
                                <td class="celda">-</td>
                                <td class="celda">{{$entry1['entry']}}</td>
                                <td class="celda">-</td>
                                <td class="celda">{{$entry1['comment']}}</td>
                                <td class="celda"></td>
                                <td class="celda">{{$entry1['date']}}</td>
                                <td class="celda">-</td>
                                @if($entry1['debe'] > 0)
                                <td class="celda ">{{$entry1['debe']}}</td>
                                @else
                                <td class="celda">{{$entry1['haber']}}</td>
                                @endif

                            </tr>
                            @endforeach
                            <tr>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda" style="text-align: right" ><strong>Suman:</strong></td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">$</td>
                                <td class="celda">{{ $chequesGNCTotales}}</td>
                            </tr>
                            <tr>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda"><strong>CHEQUES ANTICIPADOS</strong></td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">$</td>
                                <td class="celda">$</td>
                            </tr>
                            @foreach($chequesANT as $entry1)
                            <tr>
                                <td class="celda">-</td>
                                <td class="celda">{{$entry1['entry']}}</td>
                                <td class="celda">-</td>
                                <td class="celda">{{$entry1['comment']}}</td>
                                <td class="celda"></td>
                                <td class="celda">{{$entry1['date']}}</td>
                                <td class="celda">-</td>
                                @if($entry1['debe'] > 0)
                                <td class="celda">{{$entry1['debe']}}</td>
                                @else
                                <td class="celda">{{$entry1['haber']}}</td>
                                @endif

                            </tr>
                            @endforeach
                            <tr>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda" style="text-align: right" ><strong>Suman:</strong></td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">$</td>
                                <td class="celda">{{$chequesANTTotales}}</td>
                            </tr>
                            <tr>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda"><strong>DEPOSITOS NO EFECTIVIZADOS</strong></td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">$</td>
                                <td class="celda">$</td>
                            </tr>
                            @foreach($depositosNE as $entry1)
                            <tr>
                                <td class="celda">-</td>
                                <td class="celda">{{$entry1['entry']}}</td>
                                <td class="celda">-</td>
                                <td class="celda">{{$entry1['comment']}}</td>
                                <td class="celda"></td>
                                <td class="celda">{{$entry1['date']}}</td>
                                <td class="celda">-</td>
                                @if($entry1['debe'] > 0)
                                <td class="celda">{{$entry1['debe']}}</td>
                                @else
                                <td class="celda">{{$entry1['haber']}}</td>
                                @endif

                            </tr>
                            @endforeach
                            <tr>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda" style="text-align: right" ><strong>Suman:</strong></td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">$</td>
                                <td class="celda">{{$depositosNETotales}}</td>
                            </tr>
                            <tr>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda"><strong>RESUMEN</strong></td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">$</td>
                                <td class="celda">$</td>
                            </tr>
                            <tr>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">  Saldo contable:</td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">$</td>
                                <td class="celda">{{$SaldoContable}}</td>
                            </tr>
                            <tr>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">  Saldo bancario:</td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">{{$bankReconciliation->initial_value}}</td>
                            </tr>
                            <tr>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">  Saldo conciliado:</td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">{{$SaldoContable +$chequesGNCTotales + $chequesANTTotales - $depositosNETotales }}</td>
                            </tr>
                            <tr>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">  Diferencia:</td>
                                <td class="celda">-</td>
                                <td class="celda">-</td>
                                <td class="celda">$</td>
                                <td class="celda">{{$bankReconciliation->initial_value - ($SaldoContable +$chequesGNCTotales + $chequesANTTotales - $depositosNETotales)}}</td>
                            </tr>
                        </tbody>
                    </table>
                    <br>
                    <br>
                    <table class="table1">
                        <tr>
                            <td colspan="3"style="text-align: center; height:50px;">
                                ORLANDO ESPINOZA
                            </td>
                            <td colspan="3" style="text-align:center; text-justify:  height:50px;">
                                {{$user->name}}

                            </td>
                        </tr>
                        <tr>
                            <td colspan="3"style="text-align: center;">
                                CONTADOR GENERAL
                            </td>
                            <td colspan="3" style="text-align:center;">
                                RESPONSABLE
                            </td>
                        </tr>
                    </table>
                </div>

            {{-- <br>
            <br>
            <br> --}}
            {{-- @if(!empty($entries))
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
            @endif --}}
        @else
            <div class="callout callout-info">
                <p>No se encontraron registros.</p>
            </div>
        @endif
    </body>

</html>

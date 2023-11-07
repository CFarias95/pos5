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
            <h3 align="center" class="title"><strong>Reporte Compras VS Orden de compra</strong></h3>
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
                    <td align="center">{{ strval($company->number) }}</td>
                    <td>
                        <p><strong>Establecimiento: </strong></p>
                    </td>
                    <td align="center">{{$establishment->address}} - {{$establishment->department->description}} - {{$establishment->district->description}}</td>
                </tr>

                @inject('reportService', 'Modules\Report\Services\ReportService')
                <tr>
                    @if($filters['order'])
                    <td>
                        <p><strong>Orden número: </strong></p>
                    </td>
                    <td align="center">
                        OC-{{$filters['order']}}
                    </td>
                    @endif
                </tr>

            </table>
        </div>
        <br>
        @if(!empty($records))
            <div class="">
                <div class=" ">
                    @php
                        $acum_total_taxed=0;
                        $acum_total_igv=0;
                        $acum_total=0;

                        $acum_total_taxed_usd=0;
                        $acum_total_igv_usd=0;
                        $acum_total_usd=0;
                        $apply_conversion_to_pen = $filters['apply_conversion_to_pen'] == 'true';

                    @endphp
                    <table class="">
                        <thead>
                            <tr>
                                <th>Item ID</th>
                                <th>Item Descripcion</th>
                                <th>Precio Compra</th>
                                <th>Comprado</th>
                                <th>Solicitado</th>
                                <th>Diferencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $key => $value)
                            <tr>
                                <td class="celda">{{$value['itemId']}}</td>
                                <td class="celda">{{$value['itemDescription']}}</td>
                                <td class="celda">{{$value['unitValue']}}</td>
                                <td class="celda">{{$value['purchaseQuantity']}}</td>
                                <td class="celda">{{$value['purchaseOrderQuantity']}}</td>
                                <td class="celda">{{$value['dif']}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div>
                <p>No se encontraron registros para este reporte.</p>
            </div>
        @endif
    </body>
</html>

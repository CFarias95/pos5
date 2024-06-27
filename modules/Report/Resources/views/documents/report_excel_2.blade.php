<?php
    use App\Models\Tenant\Document;
    use App\CoreFacturalo\Helpers\Template\TemplateHelper;
    use App\Models\Tenant\SaleNote;

    $enabled_sales_agents = App\Models\Tenant\Configuration::getRecordIndividualColumn('enabled_sales_agents');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
          content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible"
          content="ie=edge">
    <title>Document</title>
</head>
<body>
<div>
    <h3 align="center"
        class="title"><strong>Reporte Documentos</strong></h3>
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
            <td align="center">{{$company->number}}</td>
            <td>
                <p><strong>Establecimiento: </strong></p>
            </td>
            <td align="center">{{$establishment->address}} - {{$establishment->department->description}}
                                                           - {{$establishment->district->description}}</td>
        </tr>
        @inject('reportService', 'Modules\Report\Services\ReportService')
        <tr>
            @if($filters['seller_id'])
                <td>
                    <p><strong>Usuario: </strong></p>
                </td>
                <td align="center">
                    {{$reportService->getUserName($filters['seller_id'])}}
                </td>
            @endif
            @if($filters['person_id'])
                <td>
                    <p><strong>Cliente: </strong></p>
                </td>
                <td align="center">
                    {{$reportService->getPersonName($filters['person_id'])}}
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
                $acum_total_charges=0;
                $acum_total_taxed=0;
                $acum_total_igv=0;
                $acum_total=0;

                $serie_affec = '';
                $acum_total_exonerado=0;
                $acum_total_inafecto=0;

                $acum_total_free=0;

                $acum_total_taxed_usd = 0;
                $acum_total_igv_usd = 0;
                $acum_total_usd = 0;
            @endphp
            <table class="">
                <thead>
                    <tr>
                        @foreach($records[0] as $key => $value)
                        <th>{{$key}}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                    @foreach($records as $key => $value)
                        <td class="celda">{{$value}}</td>
                    @endforeach
                    </tr>
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

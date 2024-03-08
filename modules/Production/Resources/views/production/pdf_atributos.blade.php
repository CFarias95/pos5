@php
$logo = "";

if(isset($company->logo)){
    $logo = "storage/uploads/logos/{$company->logo}";
}
$atributos = $records->attributes;
$bpm = null;
$psn = null;
$em = null;
$array1 = [];
$array2 = [];
foreach ($atributos as $key => $value) {
    if($value->attribute_type_id == 'BPM')
    {
        $bpm = $value->value;
    }
    if($value->attribute_type_id == 'PSN')
    {
        $psn = $value->value;
    }
    if($value->attribute_type_id == 'EM')
    {
        $em = $value->value;
    }
    if(starts_with($value->attribute_type_id, 'ET'))
    {
        $array1[] = $value;
    }
    if(starts_with($value->attribute_type_id, 'CM'))
    {

        $value->resultdo = str_before($value->value, ' || ');
        $value->metodo = str_after($value->value, ' || ');
        $array2[] = $value;
    }
}
$date =  new DateTime($fechas->date_end.' '.$fechas->time_end);
$fechaCaducidad = date_add($date, date_interval_create_from_date_string($records['validity']." days"));

$quantity = 0;
if($production->custom_quantity > 0)
{
    $quantity = $production->custom_quantity;
}else{
    $quantity = $production->quantity;
}

$peso_neto = $quantity - $production->samples - $production->imperfect;

@endphp
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Certificado de Calidad</title>
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
                @if(isset($logo) && $logo != '' )
                <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="padding-top: 10px; max-width: 150px" >
                @endif
            </div>
        </div>
        <div>
            <h2 align="center" class="title"><strong>Certificado de Calidad</strong></h2>
        </div>
        <div style="margin-top:20px; margin-bottom:20px;">
        <div>
            <label><strong>Producto:</strong> {{$records->name}} - {{$records->description}}</label>
            <br>
            <label><strong>Lote:</strong> {{$fechas->lot_code}}</label>
            <br>
            <label><strong>F. Elaboracion:</strong> {{$fechas->date_start}}</label>
            <br>
            <label><strong>F. Vencimiento:</strong> {{date_format($fechaCaducidad, "Y-m-d")}}</label>
            <br>
            @if (isset($bpm))
                <label><strong>BPM:</strong>{{$bpm}}</label>
            @endif
            <br>
        </div>
        <div>
            <label><strong>Ingregientes/Insumos: </strong></label>
            @foreach($records['supplies'] as $ingre)
                <label>{{$ingre['individual_item']['second_name']}};</label>
            @endforeach
        </div>
        </div>
        @if(!empty($records))
            <div class="">
                <div class=" ">
                    <label><strong>Características Físico-Químicas:</strong></label>
                    <br>
                    <table class="">
                        <thead>
                            <tr>
                                <th class="text-center">Categoria</th>
                                <th class="text-center">Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($array1 as $row)
                                <tr>
                                    <td class="celda">{{$row->description}}</td>
                                    <td class="celda">{{$row->value}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <br>
                    <br>
                    <label><strong>Características Microbiológicas:</strong></label>
                    <br>
                    <table class="">
                        <thead>
                            <tr>
                                <th class="text-center">Ensayo</th>
                                <th class="text-center">Resultado</th>
                                <th class="text-center">Método</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($array2 as $row)
                                <tr>
                                    <td class="celda">{{$row->description}}</td>
                                    <td class="celda">{{$row->resultdo}}</td>
                                    <td class="celda">{{$row->metodo}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <br>
            <label><strong>Empaque:</strong>{{$em}}</label>
            <br>
            <label><strong>Peso Neto:</strong>{{$peso_neto}}</label>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <div>
                <p alignment="center">________________________</p>
                <p alignment="center">Angélica Toaquiza</p>
                <p alignment="center">DEPARTAMENTO CALIDAD</p>
                <p alignment="center">{{$company->name}}</p>
            </div>
        @else
            <div class="callout callout-info">
                <p>No se encontraron registros.</p>
            </div>
        @endif
    </body>
</html>

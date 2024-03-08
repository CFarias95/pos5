@php
$logo = "";

if(isset($company->logo)){

    $logo = "storage/uploads/logos/$company->logo";
}

$ingredientes = $records['second_name'] ?? 'N/A';

/*foreach ($records['supplies'] as $ingre) {
    $ingredientes .= $ingre['individual_item']['second_name']. ",";
}*/

$atributos = $records['supplies'][0]['item']['attributes'];
$bpm = null;
$psn = null;
$em = null;
$codigoBPM = null;
$nfu = null;
$array1 = [];
$array2 = [];
foreach ($atributos as $key => $value) {

    if($value->attribute_type_id == 'BPM')
    {
        $bpm = $value->value;
    }
    if($value->attribute_type_id == 'CBPM')
    {
        $codigoBPM = $value->value;
    }
    if($value->attribute_type_id == 'PSN')
    {
        $psn = $value->value;
    }
    if($value->attribute_type_id == 'EM')
    {
        $em = $value->value;
    }
    if($value->attribute_type_id == 'NFU')
    {
        $nfu = $value->value;
    }
    if(starts_with($value->attribute_type_id, 'ET'))
    {
        $array1[] = $value;
    }
    if(starts_with($value->attribute_type_id, 'CM'))
    {
        $array2[] = $value;
    }
}
$date =  new DateTime($produccion->date_end.' '.$produccion->time_end);
$fechaCaducudad = date_add($date, date_interval_create_from_date_string($records['validity']." days"));

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
            @page {
                margin: 0.3cm 0.3cm;
            }
            html {
                font-family: monospace;
                font-size: 10px;
            }
            .title {
                font-weight: bold;
                padding: 5px;
                font-size: 14px !important;
                text-decoration: underline;
            }

            p>strong {
                margin-left: 5px;
                font-size: 11px;
            }
            p {
                text-align:center;
                font-size: 10px;
            }
            img {

                align-items: center
            }
            .card {
                border: 1px solid #000;
                padding: 10px;
                width: 450px;
                margin: 0 auto; 
            }
            .card img {
                display: block;
                margin: 0 auto;
            }
            .container {
                display: flex;
                justify-content: center;
                align-items: center;
            }
        </style>
    </head>
    <body>
        <table class="table card" style="border: 1px solid black;" width="100%">
            <thead style="align-content: center; text-align: center;">
                <tr>
                    <th>
                    @if(isset($logo) && $logo != '' )
                        <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="padding-top: 10px; max-width: 100px" >
                    @endif
                    </th>
                    <th style="text-align: left;"><p><strong>{{$company->name}}</strong></p></th>
                </tr>
                <tr>
                    <th colspan="2"><p><strong>{{$records['name']}}</strong> {{--  <strong>000{{$records['id']}}</strong> --}}</p></th>
                </tr>

                <tr>
                    <th colspan="2"><strong>{{$records['description']}}</strong></th>
                </tr>

                <tr>
                    <th colspan="2"><strong>{{$nfu}}</strong></th>
                </tr>

                <tr>
                    <th colspan="2">Ingredientes:{{$ingredientes}}</th>
                </tr>
                <tr>
                    <th colspan="2"></th>
                </tr>
                <tr>
                    <th colspan="2">
                    Modo de conservación:{{$bpm}} <br/>
                    Peso: {{$produccion->muestra1}} <br/>
                    Lote: {{$produccion->lot_code}} <br/>
                    Fecha de Producción: {{$produccion->date_start}} <br/>
                    Fecha de Caducidad: {{date_format($fechaCaducudad, "Y-m-d")}} <br/>
                    Código único BPM: {{ $codigoBPM }} <br/><br/>
                    </th>
                </tr>
                <tr>
                    <th style="text-align: left;">
                        <h5>
                        {{-- {{$produccion->warehouse->description }}<br/> --}}
                        {{$produccion->warehouse->establishment->address }}<br/>
                        {{-- {{$produccion->warehouse->establishment->district->description }}, --}}
                        {{$produccion->warehouse->establishment->province->description }},
                        {{$produccion->warehouse->establishment->department->description }},
                        {{$produccion->warehouse->establishment->country->description }}<br/>
                        Telf. {{$produccion->warehouse->establishment->telephone }}<br/>
                        </h5>
                    </th>
                    <th style="text-align: left;">
                        <h5>
                        INDUSTRIA ECUATORIANA<br/>
                        ELABORADO POR {{$company->name}}<br/>
                        </h5>
                    </th>
                </tr>

            </thead>
        </table>
        <br>
        <table class="table card" style="border: 1px solid black;" width="100%">
            <thead style="align-content: center; text-align: center;">
                <tr>
                    <th>
                    @if(isset($logo) && $logo != '' )
                        <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="padding-top: 10px; max-width: 100px" >
                    @endif
                    </th>
                    <th style="text-align: left;"><p><strong>{{$company->name}}</strong></p></th>
                </tr>
                <tr>
                    <th colspan="2"><p><strong>{{$records['name']}}</strong> {{--  <strong>000{{$records['id']}}</strong> --}}</p></th>
                </tr>

                <tr>
                    <th colspan="2"><strong>{{$records['description']}}</strong></th>
                </tr>

                <tr>
                    <th colspan="2"><strong>{{$nfu}}</strong></th>
                </tr>

                <tr>
                    <th colspan="2">Ingredientes:{{$ingredientes}}</th>
                </tr>
                <tr>
                    <th colspan="2"></th>
                </tr>
                <tr>
                    <th colspan="2">
                    Modo de conservación:{{$bpm}} <br/>
                    Peso: {{$produccion->muestra2}} <br/>
                    Lote: {{$produccion->lot_code}} <br/>
                    Fecha de Producción: {{$produccion->date_start}} <br/>
                    Fecha de Caducidad: {{date_format($fechaCaducudad, "Y-m-d")}} <br/>
                    Código único BPM: {{ $codigoBPM }} <br/><br/>
                    </th>
                </tr>
                <tr>
                    <th style="text-align: left;">
                        <h5>
                        {{-- {{$produccion->warehouse->description }}<br/> --}}
                        {{$produccion->warehouse->establishment->address }}<br/>
                        {{-- {{$produccion->warehouse->establishment->district->description }}, --}}
                        {{$produccion->warehouse->establishment->province->description }},
                        {{$produccion->warehouse->establishment->department->description }},
                        {{$produccion->warehouse->establishment->country->description }}<br/>
                        Telf. {{$produccion->warehouse->establishment->telephone }}<br/>
                        </h5>
                    </th>
                    <th style="text-align: left;">
                        <h5>
                        INDUSTRIA ECUATORIANA<br/>
                        ELABORADO POR {{$company->name}}<br/>
                        </h5>
                    </th>
                </tr>

            </thead>
        </table>
        <br>
        <table class="table card" style="border: 1px solid black;" width="100%">
            <thead style="align-content: center; text-align: center;">
                <tr>
                    <th>
                    @if(isset($logo) && $logo != '' )
                        <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="padding-top: 10px; max-width: 100px" >
                    @endif
                    </th>
                    <th style="text-align: left;"><p><strong>{{$company->name}}</strong></p></th>
                </tr>
                <tr>
                    <th colspan="2"><p><strong>{{$records['name']}}</strong> {{--  <strong>000{{$records['id']}}</strong> --}}</p></th>
                </tr>

                <tr>
                    <th colspan="2"><strong>{{$records['description']}}</strong></th>
                </tr>

                <tr>
                    <th colspan="2"><strong>{{$nfu}}</strong></th>
                </tr>

                <tr>
                    <th colspan="2">Ingredientes:{{$ingredientes}}</th>
                </tr>
                <tr>
                    <th colspan="2"></th>
                </tr>
                <tr>
                    <th colspan="2">
                    Modo de conservación:{{$bpm}} <br/>
                    Peso: {{$produccion->muestra3}} <br/>
                    Lote: {{$produccion->lot_code}} <br/>
                    Fecha de Producción: {{$produccion->date_start}} <br/>
                    Fecha de Caducidad: {{date_format($fechaCaducudad, "Y-m-d")}} <br/>
                    Código único BPM: {{ $codigoBPM }} <br/><br/>
                    </th>
                </tr>
                <tr>
                    <th style="text-align: left;">
                        <h5>
                        {{-- {{$produccion->warehouse->description }}<br/> --}}
                        {{$produccion->warehouse->establishment->address }}<br/>
                        {{-- {{$produccion->warehouse->establishment->district->description }}, --}}
                        {{$produccion->warehouse->establishment->province->description }},
                        {{$produccion->warehouse->establishment->department->description }},
                        {{$produccion->warehouse->establishment->country->description }}<br/>
                        Telf. {{$produccion->warehouse->establishment->telephone }}<br/>
                        </h5>
                    </th>
                    <th style="text-align: left;">
                        <h5>
                        INDUSTRIA ECUATORIANA<br/>
                        ELABORADO POR {{$company->name}}<br/>
                        </h5>
                    </th>
                </tr>

            </thead>
        </table>
        <br>
        <table class="table card" style="border: 1px solid black;" width="100%">
            <thead style="align-content: center; text-align: center;">
                <tr>
                    <th>
                    @if(isset($logo) && $logo != '' )
                        <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="padding-top: 10px; max-width: 100px" >
                    @endif
                    </th>
                    <th style="text-align: left;"><p><strong>{{$company->name}}</strong></p></th>
                </tr>
                <tr>
                    <th colspan="2"><p><strong>{{$records['name']}}</strong> {{--  <strong>000{{$records['id']}}</strong> --}}</p></th>
                </tr>

                <tr>
                    <th colspan="2"><strong>{{$records['description']}}</strong></th>
                </tr>

                <tr>
                    <th colspan="2"><strong>{{$nfu}}</strong></th>
                </tr>

                <tr>
                    <th colspan="2">Ingredientes:{{$ingredientes}}</th>
                </tr>
                <tr>
                    <th colspan="2"></th>
                </tr>
                <tr>
                    <th colspan="2">
                    Modo de conservación:{{$bpm}} <br/>
                    Peso: {{$produccion->muestra4}} <br/>
                    Lote: {{$produccion->lot_code}} <br/>
                    Fecha de Producción: {{$produccion->date_start}} <br/>
                    Fecha de Caducidad: {{date_format($fechaCaducudad, "Y-m-d")}} <br/>
                    Código único BPM: {{ $codigoBPM }} <br/><br/>
                    </th>
                </tr>
                <tr>
                    <th style="text-align: left;">
                        <h5>
                        {{-- {{$produccion->warehouse->description }}<br/> --}}
                        {{$produccion->warehouse->establishment->address }}<br/>
                        {{-- {{$produccion->warehouse->establishment->district->description }}, --}}
                        {{$produccion->warehouse->establishment->province->description }},
                        {{$produccion->warehouse->establishment->department->description }},
                        {{$produccion->warehouse->establishment->country->description }}<br/>
                        Telf. {{$produccion->warehouse->establishment->telephone }}<br/>
                        </h5>
                    </th>
                    <th style="text-align: left;">
                        <h5>
                        INDUSTRIA ECUATORIANA<br/>
                        ELABORADO POR {{$company->name}}<br/>
                        </h5>
                    </th>
                </tr>

            </thead>
        </table>
        <br>
        <table class="table card" style="border: 1px solid black;" width="100%">
            <thead style="align-content: center; text-align: center;">
                <tr>
                    <th>
                    @if(isset($logo) && $logo != '' )
                        <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="padding-top: 10px; max-width: 100px" >
                    @endif
                    </th>
                    <th style="text-align: left;"><p><strong>{{$company->name}}</strong></p></th>
                </tr>
                <tr>
                    <th colspan="2"><p><strong>{{$records['name']}}</strong> {{--  <strong>000{{$records['id']}}</strong> --}}</p></th>
                </tr>

                <tr>
                    <th colspan="2"><strong>{{$records['description']}}</strong></th>
                </tr>

                <tr>
                    <th colspan="2"><strong>{{$nfu}}</strong></th>
                </tr>

                <tr>
                    <th colspan="2">Ingredientes:{{$ingredientes}}</th>
                </tr>
                <tr>
                    <th colspan="2"></th>
                </tr>
                <tr>
                    <th colspan="2">
                    Modo de conservación:{{$bpm}} <br/>
                    Peso: {{$produccion->muestra5}} <br/>
                    Lote: {{$produccion->lot_code}} <br/>
                    Fecha de Producción: {{$produccion->date_start}} <br/>
                    Fecha de Caducidad: {{date_format($fechaCaducudad, "Y-m-d")}} <br/>
                    Código único BPM: {{ $codigoBPM }} <br/><br/>
                    </th>
                </tr>
                <tr>
                    <th style="text-align: left;">
                        <h5>
                        {{-- {{$produccion->warehouse->description }}<br/> --}}
                        {{$produccion->warehouse->establishment->address }}<br/>
                        {{-- {{$produccion->warehouse->establishment->district->description }}, --}}
                        {{$produccion->warehouse->establishment->province->description }},
                        {{$produccion->warehouse->establishment->department->description }},
                        {{$produccion->warehouse->establishment->country->description }}<br/>
                        Telf. {{$produccion->warehouse->establishment->telephone }}<br/>
                        </h5>
                    </th>
                    <th style="text-align: left;">
                        <h5>
                        INDUSTRIA ECUATORIANA<br/>
                        ELABORADO POR {{$company->name}}<br/>
                        </h5>
                    </th>
                </tr>

            </thead>
        </table>
        <br>
        <table class="table card" style="border: 1px solid black;" width="100%">
            <thead style="align-content: center; text-align: center;">
                <tr>
                    <th>
                    @if(isset($logo) && $logo != '' )
                        <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="padding-top: 10px; max-width: 100px" >
                    @endif
                    </th>
                    <th style="text-align: left;"><p><strong>{{$company->name}}</strong></p></th>
                </tr>
                <tr>
                    <th colspan="2"><p><strong>{{$records['name']}}</strong> {{--  <strong>000{{$records['id']}}</strong> --}}</p></th>
                </tr>

                <tr>
                    <th colspan="2"><strong>{{$records['description']}}</strong></th>
                </tr>

                <tr>
                    <th colspan="2"><strong>{{$nfu}}</strong></th>
                </tr>

                <tr>
                    <th colspan="2">Ingredientes:{{$ingredientes}}</th>
                </tr>
                <tr>
                    <th colspan="2"></th>
                </tr>
                <tr>
                    <th colspan="2">
                    Modo de conservación:{{$bpm}} <br/>
                    Peso: {{$produccion->samples}} <br/>
                    Lote: {{$produccion->lot_code}} <br/>
                    Fecha de Producción: {{$produccion->date_start}} <br/>
                    Fecha de Caducidad: {{date_format($fechaCaducudad, "Y-m-d")}} <br/>
                    Código único BPM: {{ $codigoBPM }} <br/><br/>
                    </th>
                </tr>
                <tr>
                    <th style="text-align: left;">
                        <h5>
                        {{-- {{$produccion->warehouse->description }}<br/> --}}
                        {{$produccion->warehouse->establishment->address }}<br/>
                        {{-- {{$produccion->warehouse->establishment->district->description }}, --}}
                        {{$produccion->warehouse->establishment->province->description }},
                        {{$produccion->warehouse->establishment->department->description }},
                        {{$produccion->warehouse->establishment->country->description }}<br/>
                        Telf. {{$produccion->warehouse->establishment->telephone }}<br/>
                        </h5>
                    </th>
                    <th style="text-align: left;">
                        <h5>
                        INDUSTRIA ECUATORIANA<br/>
                        ELABORADO POR {{$company->name}}<br/>
                        </h5>
                    </th>
                </tr>

            </thead>
        </table>
        <br>
    </body>
</html>

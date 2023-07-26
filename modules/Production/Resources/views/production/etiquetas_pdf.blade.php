@php
$logo = "";

if(isset($company->logo)){

    $logo = "storage/uploads/logos/$company->logo";
}

$ingredientes = "";

foreach ($records['supplies'] as $ingre) {
    $ingredientes .= $ingre['individual_item']['second_name']. ",";
}

$atributos = $records['supplies'][0]['item']['attributes'];
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
        $array2[] = $value;
    }
}

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
            html {
                font-family: monospace;
                font-size: 12px;
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
            p {
                text-align:center;
            }
            img {

                align-items: center
            }
            .card {
                border: 1px solid #000;
                padding: 10px;
                width: 450px;
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
        <table class="table" style="border: 1px solid black;">
            <thead style="align-content: center; text-align: center;">
                <tr>
                    <th>
                    @if(isset($logo) && $logo != '' )
                        <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="padding-top: 10px; max-width: 150px" >
                    @endif
                    </th>
                    <th style="text-align: left;"><p><strong>{{$company->name}}</strong></p></th>
                </tr>
                <tr>
                    <th colspan="2"><p><strong>{{$records['name']}}</strong>  <strong>000{{$records['id']}}</strong></p></th>
                </tr>

                <tr>
                    <th colspan="2"><strong>{{$records['description']}}</strong></th>
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
                    Peso: {{$psn}} <br/>
                    Lote: {{$produccion->lot_code}} <br/>
                    Fecha de Producción: {{$produccion->date_start}} <br/>
                    Fecha de Caducidad: {{$produccion->date_of_due}} <br/><br/>
                    </th>
                </tr>
                <tr>
                    <th colspan="2"> PRODUCTO IMPORTADO, DISTRIBUIDO, Y COMERCIALIZADO POR {{$company->name}}</th>
                <tr>

            </thead>
        </table>

        <br>
        <br>
        <table class="table" style="border: 1px solid black;">
            <thead style="align-content: center; text-align: center;">
                <tr>
                    <th>

                    @if(isset($logo) && $logo != '' )
                        <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="padding-top: 10px; max-width: 150px" >
                    @endif

                    </th>
                    <th style="text-align: left"><p><strong>{{$company->name}}</strong></p></th>
                </tr>
                <tr>
                    <th colspan="2"><p><strong>{{$records['name']}}</strong>  <strong>000{{$records['id']}}</strong></p></th>
                </tr>

                <tr>
                    <th colspan="2"><strong>{{$records['description']}}</strong></th>
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
                    Peso: {{$psn}} <br/>
                    Lote: {{$produccion->lot_code}} <br/>
                    Fecha de Producción: {{$produccion->date_start}} <br/>
                    Fecha de Caducidad: {{$produccion->date_of_due}} <br/><br/>
                    </th>
                </tr>
                <tr>
                    <th colspan="2"> PRODUCTO IMPORTADO, DISTRIBUIDO, Y COMERCIALIZADO POR {{$company->name}}</th>
                <tr>

            </thead>
        </table>
    </body>
</html>

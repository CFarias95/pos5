@php
$logo = "storage/uploads/logos/{$company->logo}";
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
                font-family: sans-serif;
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
        <div class="container">
            <div class="card">
                <div class="card-body">
                    <br>
                    <img src = "data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$records->name}}" class="" style="margin-center: 300px; padding-bottom: 0px; max-width: 150px" >
                    <br>
                    <p><strong>{{$records->name}}</strong>  <strong>000{{$records->id}}</strong></p>
                    <p><strong>Peso: {{$psn}}</strong></p>
                    <p><strong>Lote: {{$records->lot_code}}</strong></p>
                    <p><strong>Fecha de Producción: {{$fechas[0]->date_start}}</strong></p>
                    <p><strong>Fecha de Caducidad: {{$records->date_of_due}}</strong></p>
                    <label><strong>PRODUCTO IMPORTADO, DISTRIBUIDO, Y COMERCIALIZADO POR {{$company->name}}</strong></label>
                </div>
            </div>
        </div>
        
        <br>
        <br>
        <div class="container">
            <div class="card">
                <div class="card-body">
                    <br>
                    <img src = "data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$records->name}}" class="" style="margin-center: 300px; padding-bottom: 0px; max-width: 150px" >
                    <br>
                    <p><strong>{{$records->name}}</strong>  <strong>000{{$records->id}}</strong></p>
                    <p><strong>Peso: {{$psn}}</strong></p>
                    <p><strong>Lote: {{$records->lot_code}}</strong></p>
                    <p><strong>Fecha de Producción: {{$fechas[0]->date_start}}</strong></p>
                    <p><strong>Fecha de Caducidad: {{$records->date_of_due}}</strong></p>
                    <label><strong>PRODUCTO IMPORTADO, DISTRIBUIDO, Y COMERCIALIZADO POR {{$company->name}}</strong></label>
                </div>
            </div>
        </div>
        
    </body>
</html>
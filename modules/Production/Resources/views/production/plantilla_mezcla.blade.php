@php
$logo = "";

if(isset($company->logo)){
    $logo = "storage/uploads/logos/{$company->logo}";
}

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
                align-content: center;
            }
            table, th, td {
                border: 1px solid black;
                text-align: center;
            }

        </style>
    </head>
    <body style="align-content: center;">

        <table width="100%">
            <tr>
                <th rowspan="3">
                    @if(isset($logo) && $logo != '' )
                    <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="max-width: 100px" >
                    @endif
                </th>
                <th>
                    SISTEMA DE GESTIÓN DE SEGURIDAD ALIMENTARIA
                </th>
                <th colspan="2">
                    CCA-BPM-005
                </th>
            </tr>
            <tr>
                <td rowspan="2">
                   CHECK LIST ARRANQUE Y TÉRMINO DE PROCESO MEZCLADO
                </td>
                <td colspan="2">
                    Actualización: MARZO 2023
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    REV:04
                </td>
            </tr>
            <tr>
                <td colspan="4" style="text-align:right;">
                    No Orden: {{ $records->production_order}}
                </td>
            </tr>
            <tr>
                <td  style="text-align:left;">
                    FECHA:
                </td>
                <td  style="text-align:left;">
                    {{ $records->date_end.'-'.$records->time_end}}
                </td>
            </tr>
            <tr>
                <td  style="text-align:left;">
                    PRODUCTO:
                </td>
                <td  style="text-align:left;">
                    {{ $records->item->name}}
                </td>
            </tr>
            <tr>
                <td  style="text-align:left;">
                    SUBLOTE #:
                </td>
                <td  style="text-align:left;">
                    {{ $records->lot_code}}
                </td>
            </tr>
            <tr>
                <td  colspan="4">
                    CHECK LIST PARA EL ARRANQUE Y TÉRMINO DEL PROCESO DE MEZCLADO
                </td>
            </tr>
        </table>
        <table width="100%">
            <tr>
                <th rowspan="2">
                    ÁREA DE TRABAJO
                </th>
                <th colspan="2">
                    Arranque
                </th>
                <th colspan="2">
                    Término
                </th>
                <th rowspan="2">
                    Observaciones
                </th>
            </tr>
            <tr>
                <th>
                    Cumple
                </th>
                <th>
                    No cumple
                </th>
                <th>
                    Cumple
                </th>
                <th>
                    No cumple
                </th>
            </tr>
            <tr >
                <td style="text-align: left">Adana Sanitaria limpia y con dotación completa</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="text-align: left">Pisos y Paredes limpios y secos</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="text-align: left">Balanza area de pesado verificada?</td>
                <td></td>
                <td></td>
                <td>N/A</td>
                <td>N/A</td>
                <td style="text-align: left">Patron=           Resultado=</td>
            </tr>
            <tr>
                <td style="text-align: left">Balanza area de envasado verificada?</td>
                <td></td>
                <td></td>
                <td>N/A</td>
                <td>N/A</td>
                <td style="text-align: left">Patron=           Resultado=</td>
            </tr>
            <tr>
                <td style="text-align: left">Balanza grama verificada?</td>
                <td></td>
                <td></td>
                <td>N/A</td>
                <td>N/A</td>
                <td style="text-align: left">Patron=           Resultado=</td>
            </tr>
            <tr>
                <td style="text-align: left">Brixometro verificado?</td>
                <td></td>
                <td></td>
                <td>N/A</td>
                <td>N/A</td>
                <td style="text-align: left">Patron=           Resultado=</td>
            </tr>
            <tr>
                <td style="text-align: left">pHmetro verificado?</td>
                <td></td>
                <td></td>
                <td>N/A</td>
                <td>N/A</td>
                <td style="text-align: left">Patron=           Resultado=</td>
            </tr>
            <tr>
                <th rowspan="2">
                    LIMPIEZA DE EQUIPOS
                </th>
                <th colspan="2">
                    Arranque
                </th>
                <th colspan="2">
                    Término
                </th>
                <th rowspan="2">
                    Observaciones
                </th>
            </tr>
            <tr>
                <th>
                    Cumple
                </th>
                <th>
                    No cumple
                </th>
                <th>
                    Cumple
                </th>
                <th>
                    No cumple
                </th>
            </tr>
            <tr>
                <td colspan="6">
                    PARTE INTERNA
                </td>
            </tr>
            <tr>
                <td style="text-align: left">Cuchillas del mezclador limpias y secas</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="text-align: left">Paredes internas del mezclador limpias y secas</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="text-align: left">Cuchillas y plato del cutter limpias y secas</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="text-align: left">Malla y superficies del tamizador limpias y secas</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="6">
                    PARTE EXTERNA
                </td>
            </tr>
            <tr>
                <td style="text-align: left">Parte acrilica y metálica del cutter limpias y secas</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="text-align: left">La tapa del mezclador limpia y seca</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="text-align: left">Cortinas plasticas limpias y secas</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <th rowspan="2">
                    OPERACIÓN Y MOVIMIENTOS
                </th>
                <th colspan="2">
                    Arranque
                </th>
                <th colspan="2">
                    Término
                </th>
                <th rowspan="2">
                    Observaciones
                </th>
            </tr>
            <tr>
                <th>
                    Cumple
                </th>
                <th>
                    No cumple
                </th>
                <th>
                    Cumple
                </th>
                <th>
                    No cumple
                </th>
            </tr>
            <tr>
                <td style="text-align: left">Verificar luz de encendido de equipos</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="text-align: left">Verificar funcionamiento de sensores de seguridad de equipos</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <th rowspan="2">
                    INVENTARIO DE UTENSILIOS DE PLANTA
                </th>
                <th colspan="2">
                    Arranque
                </th>
                <th colspan="2">
                    Término
                </th>
                <th rowspan="2">
                    Observaciones/Cantidad de Utensilios utilizados
                </th>
            </tr>
            <tr>
                <th>
                    Cumple
                </th>
                <th>
                    No cumple
                </th>
                <th>
                    Cumple
                </th>
                <th>
                    No cumple
                </th>
            </tr>
            <tr>
                <td style="text-align: left">Cucharetas plásticas y metálicas limpias, secas y en buen estado</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="text-align: left">Estilete y cuchillas limpias, secas y en buen estado</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="text-align: left">Fundas limpias y en buen estado</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="text-align: left">Amarras contabilizadas para cada saco</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="text-align: left">Brochas para la limpieza limpias, secas y en buen estado</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="6" style=" height:100px; text-align:top;" >
                    OBSEERVACIONES:
                </td>
            </tr>
            <tr>
                <td colspan="3"style="text-align: end; height:50px;">
                    Firma operador
                </td>
                <td colspan="3" style="text-align:justify; text-justify:  height:50px;">
                    Firma encargado de producción
                </td>
            </tr>
        </table>


    </body>
</html>

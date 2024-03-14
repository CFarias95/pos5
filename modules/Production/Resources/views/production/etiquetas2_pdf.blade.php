@php
    $date_end = $produccion->date_end;
    $date_end_object = DateTime::createFromFormat('Y-m-d', $date_end);
    $validity = $producido->validity;
    $date_end_object->modify("+$validity days");
    $formatted_date_end = $date_end_object->format('Y-m-d');
    $totalKg = 0;
    foreach($production_items as $supplies)
    {
        $totalKg += $supplies->quantity;
    }
    $totalKgEM = 0;
    foreach($empaque_items as $supplies)
    {
        $totalKgEM += $supplies->quantity;
    }
    $logo = "storage/uploads/logos/{$company->logo}";
    //Log::info('production_items - '.json_encode($production_items));
    //Log::info('empaque_items - '.json_encode($empaque_items));
   

    $empacado =  $totalKg - $produccion->imperfect - $produccion->samples;

    $producido_global = floatval($produccion->color);
    $merma_global = floatval($produccion->olor);

    $porcentaje_merma = 0;
    $porcentaje_merma_global = 0;

    $multiplicacion1 = $produccion->imperfect * 100;
    $porcentaje_merma = round($multiplicacion1 / $totalKg, 2);

    if($producido_global > 0)
    {
        $multiplicacion = $merma_global * 100;
        $porcentaje_merma_global = round($multiplicacion / $producido_global, 2);
    }else{
        $producido_global = 0;
        //$merma_global = 0;
    }
    

    $rango = null;
    $color = null;
    $olor = null;
    $sabor = null;
    $soluble = null;

    foreach ($producido->attributes as $attribute) {
        if($attribute->attribute_type_id == 'OPC')
        {
            $color = $attribute->value;
        }
        if($attribute->attribute_type_id == 'OPOL')
        {
            $olor = $attribute->value;
        }
        if($attribute->attribute_type_id == 'OPS')
        {
            $sabor = $attribute->value;
        }
        if($attribute->attribute_type_id == 'OPSL')
        {
            $soluble = $attribute->value;
        }
        if($attribute->attribute_type_id == 'ET07')
        {
            $rango = $attribute->value;
        }
    }
@endphp

<table>
    <tr>
        <td rowspan="3">
            <div class="img-container">
                <img src="{{ $logo }}" class="img-resize"/>
            </div>       
        </td>
        <td rowspan="2"><strong><center>SISTEMA DE GESTIÓN DE SEGURIDAD ALIMENTARIA</center></strong></td>
        <td><center>CCA-BPM-001</center></td>
    </tr>
    <tr>
        <td><center>Actualización: Mayo 2022</center></td>
        
    </tr>
    <tr>
        <td><center>ORDEN DE PRODUCCIÓN</center></td>
        <td><center>REV. 06</center></td>
    </tr>
</table>

<table style="width: 100%; border-collapse: collapse; border: 1px solid #000000;">
    <tr>
        <td style="width: 48%; vertical-align: top;">
            <table border="1" style="width: 100%; border-collapse: collapse;">
                <tbody>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 1px;">Fecha</th>
                        <td style="border: 1px solid #ddd; padding: 1px;">{{ $produccion->date_end }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 1px;">N° Orden Conversión</th>
                        <td style="border: 1px solid #ddd; padding: 1px;">{{ $produccion->id }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 1px;">N° Solicitud Producción</th>
                        <td style="border: 1px solid #ddd; padding: 1px;">{{ $produccion->name }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 1px;">Cantidad a Producir</th>
                        <td style="border: 1px solid #ddd; padding: 1px;">{{ $totalKg }}</td>
                    </tr>
                </tbody>
            </table>
        </td>

        <td style="width: 48%; vertical-align: top;">
            <table border="1" style="width: 100%; border-collapse: collapse;">
                <tbody>
                    <tr>
                        <th colspan="2" style="border: 1px solid #ddd; padding: 1px;"><center>DEP. PRODUCCIÓN</center></th>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 1px;">Nombre Producto</th>
                        <td style="border: 1px solid #ddd; padding: 1px;">{{ $producido->name }}-{{$producido->description}}</td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th colspan="4">
                <center>Materia Prima Solicitada a Bodega</center>
            </th>
        </tr>
        <tr>
            <th>Detalle</th>
            <th>KG</th>
            <th>Lote</th>
            <th>Control</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($production_items as $supplies)
            <tr>
                <td>{{ $supplies->item->name }}</td>
                <td>{{ $supplies->quantity }}</td>
                <td>{{ $supplies->lot_code }}</td>
                @foreach ($produccion->production_supplies as $supply)
                    @if ($supplies->item->id == $supply->item_supply_original_id)
                        <td>{{ $supply->checked ? 'Sí' : 'No' }}</td>
                    @endif
                @endforeach
            </tr>
        @endforeach
        <tr>
            <th>Total Solicitado</th>
            <td>{{ $totalKg }}</td>
            <td style="border-bottom: none; border-right: none;"></td>
            <td style="border-bottom: none; border-right: none; border-left: none;"></td>
        </tr>
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th class="section-title" colspan="4">
                <center>Material de Empaque Solicitado a Bodega</center>
            </th>
        </tr>
        <tr>
            <th>Detalle</th>
            <th>Cantidad</th>
            <th>Lote</th>
            <th>Control</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($empaque_items as $empaques)
            <tr>
                <td>{{ $empaques->item->name }}</td>
                <td>{{ $empaques->quantity }}</td>
                <td>{{ $empaques->lot_code }}</td>
                @foreach ($produccion->production_supplies as $supply)
                    @if ($supplies->item->id == $supply->item_supply_original_id)
                        <td>{{ $supply->checked ? 'Sí' : 'No' }}</td>
                    @endif
                @endforeach
            </tr>
        @endforeach
        @foreach($servicios as $servicio)
            @if($servicio > 0)
                <tr>
                    <td>{{ $servicio->item_supply_name }}</td>
                    <td>{{ $servicio->quantity }}</td>
                    <td>N/A</td>
                    <td>{{ $servicio->checked ? 'Sí' : 'No' }}</td>
                </tr>
            @endif
        @endforeach
        <tr>
            <th>Total Solicitado</th>
            <td>{{ $totalKgEM }}</td>
            <td style="border-bottom: none; border-right: none;"></td>
            <td style="border-bottom: none; border-right: none; border-left: none;"></td>
        </tr>
    </tbody>
</table>

<br>
<br>
<br>

<div style="width: 100%; text-align: center; margin-bottom: 3px;">
    <div style="margin: 0 auto; width: 90%;">
        <div style="display: inline-block; width: 45%; text-align: center; margin-right: 15px; font-size: 8px;">
            <div style="border-bottom: 1px solid #000; margin-bottom: 1px;"></div>
            <strong>Colaborador de Producción</strong>
            <div>{{ $produccion->production_collaborator ? $produccion->production_collaborator : 'N/A' }}</div>
        </div>

        <div style="display: inline-block; width: 45%; text-align: center; font-size: 8px;">
            <div style="border-bottom: 1px solid #000; margin-bottom: 1px;"></div>
            <strong>Colaborador de Mezcla</strong>
            <div>{{ $produccion->mix_collaborator ? $produccion->mix_collaborator : 'N/A' }}</div>
        </div>
    </div>
</div>

<table style="width: 100%; margin-bottom: 3px;">
    <thead>
        <tr>
            <th colspan="2"><center>Comentario</center></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">
                {{ $produccion->comment ? $produccion->comment : 'N/A' }}
            </td>
        </tr>
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th class="section-title" colspan="4">
                <center>Control de Produccion</center>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2"><strong>Fecha</strong></td>
            <td colspan="2">{{ $produccion->created_at->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>N° Personas</strong></td>
            <td colspan="2">{{ $produccion->num_personas }}</td>
        </tr>
        <tr>
                <td colspan="4"><strong><center>Producción</center></strong></td>
        </tr>
        <tr>
            <td><strong>Hora Inicio:</strong></td>
            <td>{{ $produccion->time_start }}</td>
            <td><strong>Hora Fin:</strong></td>
            <td>{{ $produccion->time_end }}</td>
        </tr>
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <td colspan="2"><strong><center>Producto</center></strong></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Producto:</strong> {{ $producido->name }}-{{$producido->description}}</td>
            <td><strong>Lote asignado:</strong> {{ $produccion->lot_code }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Presentacion solicitada:</strong> {{ $produccion->presentacion }}</td>
        </tr>
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th colspan="2"></th>
            <th>KG</th>
            <th>%</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th colspan="2">Cantidad Producida:</th>
            <td>{{ $totalKg }}</td>
            <td>100%</td>
        </tr>
        <tr>
            <th colspan="2">Merma</th>
            <td>{{ number_format($produccion->imperfect, 4) }}</td>
            <td>{{ $porcentaje_merma }}%</td>
        </tr>
    </tbody>
</table>

<table style="width: 100%; border-collapse: collapse; border: 1px solid #000000;">
    <tr>
        <td style="width: 48%; vertical-align: top;">
            <table border="1" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th colspan="2"><center>Producto Terminado</center></th>
                        <th colspan="2"><center>Control de Peso Producto Terminado</center></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Cantidad Empacada (KG):</th>
                        <td>{{ $empacado }}</td>
                        <th>Muestra 1:</th>
                        <td>{{ $produccion->muestra1 }}</td>
                    </tr>
                    <tr>
                        <th>Muestra Testigo (KG):</th>
                        <td>{{ number_format($produccion->samples, 4) }}</td>
                        <th>Muestra 2:</th>
                        <td>{{ $produccion->muestra2 }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <th>Muestra 3:</th>
                        <td>{{ $produccion->muestra3 }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <th>Muestra 4:</th>
                        <td>{{ $produccion->muestra4 }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <th>Muestra 5:</th>
                        <td>{{ $produccion->muestra5 }}</td>
                    </tr>
                </tbody>
            </table>
        </td>
        <td style="width: 48%; vertical-align: top;">
            <table border="1" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th colspan="3"><center>Cantidad Producida Global</center></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th></th>
                        <th>KG</th>
                        <th>%</th>
                    </tr>
                    <tr>
                        <th>Cantidad Producida Global</th>
                        <td>{{ $producido_global }}</td>
                        <td>100%</td>
                    </tr>
                    <tr>
                        <th>Merma Total (KG)</th>
                        <td>{{ $merma_global }}</td>
                        <td>{{ $porcentaje_merma_global }}%</td>
                    </tr>
                    <tr>
                        <th>Muestra Testigo (KG)</th>
                        <td>{{ $produccion->sabor }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Sobrante Total (KG)</th>
                        <td>{{ $produccion->solubilidad }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>

<table style="width: 100%; border-collapse: collapse; border: 1px solid #000000;">
    <tr>
        <td style="width: 48%; vertical-align: top;">
            <table border="1" style="width: 100%; border-collapse: collapse;">
                <tbody>
                    <tr>
                        <th colspan="2" style="border: 1px solid #ddd; padding: 1px;"><center>Control de Calidad</center></th>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 1px;">RANGO PH</th>
                        <td style="border: 1px solid #ddd; padding: 1px;">{{ $rango }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 1px;">PH</th>
                        <td style="border: 1px solid #ddd; padding: 1px;">{{ $produccion->ph }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 1px;">COLOR</th>
                        <td style="border: 1px solid #ddd; padding: 1px;">{{ $color }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 1px;">OLOR</th>
                        <td style="border: 1px solid #ddd; padding: 1px;">{{ $olor }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 1px;">SABOR</th>
                        <td style="border: 1px solid #ddd; padding: 1px;">{{ $sabor }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 1px;">SOLUBILIDAD</th>
                        <td style="border: 1px solid #ddd; padding: 1px;">{{ $soluble }}</td>
                    </tr>
                </tbody>
            </table>
        </td>

        <td style="width: 48%; vertical-align: top;">
            <table border="1" style="width: 100%; border-collapse: collapse;">
                <tbody>
                    <tr>
                        <th colspan="2" style="border: 1px solid #ddd; padding: 1px;"><center>Certificado de Calidad</center></th>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 1px;">Revisión de Información:</th>
                        <td style="border: 1px solid #ddd; padding: 1px;">{{ $produccion->revision == 1 ? 'Si' : 'No' }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 1px;">Enviado a:</th>
                        <td style="border: 1px solid #ddd; padding: 1px;">{{ $produccion->enviado }}</td>
                    </tr>
                    <tr>
                        <th colspan="2" style="border: 1px solid #ddd; padding: 1px;"><center>Información de Etiqueta</center></th>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 1px;">Verificación de Nombre y Lote:</th>
                        <td style="border: 1px solid #ddd; padding: 1px;">{{ $produccion->verificacion_nombre == 1 ? 'Si' : 'No' }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 1px;">Verificación de Fecha Elab.:</th>
                        <td style="border: 1px solid #ddd; padding: 1px;">{{ $produccion->verificacion_date_issue == 1 ? 'Si' : 'No' }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 1px;">Verificación de Fecha Ven.:</th>
                        <td style="border: 1px solid #ddd; padding: 1px;">{{ $produccion->verificacion_date_end == 1 ? 'Si' : 'No' }}</td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>

</table>

<table style="width: 100%; margin-bottom: 3px;">
    <thead>
        <tr>
            <th colspan="2"><center>Observaciones</center></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">
                {{ $produccion->observaciones2 ? $produccion->observaciones2 : 'N/A' }}
            </td>
        </tr>
    </tbody>
</table>

<br>
<br>
<br>

<div style="width: 100%; text-align: center; margin-bottom: 1px;">
    <div style="margin: 0 auto; width: 90%;">
        <div style="display: inline-block; width: 30%; text-align: center; margin-right: 15px; font-size: 8px;">
            <div style="border-bottom: 1px solid #000; margin-bottom: 1px;"></div>
            <strong>F. Producción</strong>
        </div>

        <div style="display: inline-block; width: 30%; text-align: center; margin-right: 15px; font-size: 8px;">
            <div style="border-bottom: 1px solid #000; margin-bottom: 1px;"></div>
            <strong>F. Calidad</strong>
        </div>

        <div style="display: inline-block; width: 30%; text-align: center; font-size: 8px;">
            <div style="border-bottom: 1px solid #000; margin-bottom: 1px;"></div>
            <strong>F. Bodega</strong>
        </div>
    </div>
</div>

<style>
    @page {
        margin: 10px 1cm;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        font-size: 8px; 
        margin-bottom: 3px;   
    }

    th, td {
        border: 1px solid #dddddd;
        text-align: left;
    }

    .img-container {
        border: 1px solid #ffffff;
        text-align: center;
    }

    .img-resize {
        width: 15%;
        height: auto;
    }
    .section-title {
        text-align: center;
    }
</style>
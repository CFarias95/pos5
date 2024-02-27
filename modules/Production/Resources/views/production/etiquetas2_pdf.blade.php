@php
    $date_end = $produccion->date_end;
    $date_end_object = DateTime::createFromFormat('Y-m-d', $date_end);
    $validity = $producido->validity;
    $date_end_object->modify("+$validity days");
    $formatted_date_end = $date_end_object->format('Y-m-d');
    $totalKg = 0;
    foreach ($inventarios as $inventario) {
        foreach($production_items as $supplies)
        {
            if($inventario->item_id == $supplies->id)
            {
                $totalKg += $inventario->quantity;
            }
        }
    }
    $totalKgEM = 0;
    foreach ($inventarios as $inventario) {
        foreach($empaque_items as $empaques)
        {
            if($inventario->item_id == $empaques->id)
            {
                $totalKgEM += $inventario->quantity;
            }
        }
    }
    $logo = "storage/uploads/logos/{$company->logo}";
    //Log::info('');
    $porcentaje_merma = 0;
    $multiplicacion = $produccion->imperfect * 100;
    $porcentaje_merma = $multiplicacion / $totalKg;
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
<br>
<table style="width: 100%; border-collapse: collapse; border: 1px solid #000000;">
    <tr>
        <td style="width: 48%; vertical-align: top;">
            <table border="1" style="width: 100%; border-collapse: collapse;">
                <tbody>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 2px;">Fecha</th>
                        <td style="border: 1px solid #ddd; padding: 2px;">{{ $produccion->date_end }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 2px;">N° Orden Conversión</th>
                        <td style="border: 1px solid #ddd; padding: 2px;">{{ $produccion->id }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 2px;">N° Solicitud Producción</th>
                        <td style="border: 1px solid #ddd; padding: 2px;">{{ $produccion->production_order }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 2px;">Cantidad a Producir</th>
                        <td style="border: 1px solid #ddd; padding: 2px;">{{ $totalKg }}</td>
                    </tr>
                </tbody>
            </table>
        </td>

        <td style="width: 48%; vertical-align: top;">
            <table border="1" style="width: 100%; border-collapse: collapse;">
                <tbody>
                    <tr>
                        <th colspan="2" style="border: 1px solid #ddd; padding: 2px;"><center>DEP. PRODUCCIÓN</center></th>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 2px;">Nombre Producto</th>
                        <td style="border: 1px solid #ddd; padding: 2px;">{{ $producido->name }}</td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>
<br>
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
                <td>{{ $supplies->name }}</td>
                @foreach($inventarios as $inventario)
                    @if($inventario->item_id == $supplies->id)
                        <td>{{ $inventario->quantity }}</td>
                    @endif
                @endforeach
                @foreach($inventarios as $inventario)
                    @if($inventario->item_id == $supplies->id)
                        <td>{{ $inventario->lot_code }}</td>
                    @endif
                @endforeach
                @foreach ($produccion->production_supplies as $supply)
                    @if ($supplies->id == $supply->item_supply_original_id)
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
<br>
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
                <td>{{ $empaques->name }}</td>
                @foreach($inventarios as $inventario)
                    @if($inventario->item_id == $empaques->id)
                        <td>{{ $inventario->quantity }}</td>
                    @endif
                @endforeach
                @foreach($inventarios as $inventario)
                    @if($inventario->item_id == $empaques->id)
                        <td>{{ $inventario->lot_code }}</td>
                    @endif
                @endforeach
                @foreach ($produccion->production_supplies as $supply)
                    @if ($supplies->id == $supply->item_supply_original_id)
                        <td>{{ $supply->checked ? 'Sí' : 'No' }}</td>
                    @endif
                @endforeach
            </tr>
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
<br>

<div style="width: 100%; text-align: center; margin-bottom: 5px;">
    <div style="margin: 0 auto; width: 90%;">
        <div style="display: inline-block; width: 45%; text-align: center; margin-right: 15px; font-size: 6px;">
            <div style="border-bottom: 1px solid #000; margin-bottom: 1px;"></div>
            <strong>Colaborador de Producción</strong>
            <div>{{ $produccion->production_collaborator ? $produccion->production_collaborator : 'N/A' }}</div>
        </div>

        <div style="display: inline-block; width: 45%; text-align: center; font-size: 6px;">
            <div style="border-bottom: 1px solid #000; margin-bottom: 1px;"></div>
            <strong>Colaborador de Mezcla</strong>
            <div>{{ $produccion->mix_collaborator ? $produccion->mix_collaborator : 'N/A' }}</div>
        </div>
    </div>
</div>

<table style="width: 100%; margin-bottom: 10px;">
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
<br>
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
<br>
<table style="margin: 0 auto;">
    <thead>
        <tr>
            <td colspan="2"><strong><center>Producto</center></strong></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Producto:</strong> {{ $producido->name }}</td>
            <td><strong>Lote asignado:</strong> {{ $produccion->lot_code }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Presentacion solicitada:</strong> {{ $produccion->presentacion }}</td>
        </tr>
    </tbody>
</table>
<br>
<table border="1" style="width:100%">
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
            <td>{{ $produccion->imperfect }}</td>
            <td>{{ $porcentaje_merma }}%</td>
        </tr>
    </tbody>
</table>
<br>
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
                        <td>{{ $totalKg + $totalKgEM }}</td>
                        <th>Muestra 1:</th>
                        <td>{{ $produccion->muestra1 }}</td>
                    </tr>
                    <tr>
                        <th>Muestra Testigo (KG):</th>
                        <td>{{ $produccion->samples }}</td>
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
                        <td>{{ $totalKg }}</td>
                        <td>100%</td>
                    </tr>
                    <tr>
                        <th>Merma Total (KG)</th>
                        <td>{{ $produccion->imperfect }}</td>
                        <td>{{ $porcentaje_merma }}</td>
                    </tr>
                    <tr>
                        <th>Muestra Testigo (KG)</th>
                        <td>{{ $produccion->samples }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Sobrante Total (KG)</th>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>
<br>
<table style="width: 100%; border-collapse: collapse; border: 1px solid #000000;">
    <tr>
        <td style="width: 48%; vertical-align: top;">
            <table border="1" style="width: 100%; border-collapse: collapse;">
                <tbody>
                    <tr>
                        <th colspan="2" style="border: 1px solid #ddd; padding: 2px;"><center>Control de Calidad</center></th>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 2px;">RANGO PH</th>
                        <td style="border: 1px solid #ddd; padding: 2px;"></td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 2px;">PH</th>
                        <td style="border: 1px solid #ddd; padding: 2px;">{{ $produccion->ph }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 2px;">COLOR</th>
                        <td style="border: 1px solid #ddd; padding: 2px;">{{ $produccion->color }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 2px;">OLOR</th>
                        <td style="border: 1px solid #ddd; padding: 2px;">{{ $produccion->olor }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 2px;">SABOR</th>
                        <td style="border: 1px solid #ddd; padding: 2px;">{{ $produccion->sabor }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 2px;">SOLUBILIDAD</th>
                        <td style="border: 1px solid #ddd; padding: 2px;">{{ $produccion->solubilidad }}</td>
                    </tr>
                </tbody>
            </table>
        </td>

        <td style="width: 48%; vertical-align: top;">
            <table border="1" style="width: 100%; border-collapse: collapse;">
                <tbody>
                    <tr>
                        <th colspan="2" style="border: 1px solid #ddd; padding: 2px;"><center>Certificado de Calidad</center></th>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 2px;">Revisión de Información:</th>
                        <td style="border: 1px solid #ddd; padding: 2px;">{{ $produccion->revision == 1 ? 'Si' : 'No' }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 2px;">Enviado a:</th>
                        <td style="border: 1px solid #ddd; padding: 2px;">{{ $produccion->enviado }}</td>
                    </tr>
                    <tr>
                        <th colspan="2" style="border: 1px solid #ddd; padding: 2px;"><center>Información de Etiqueta</center></th>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 2px;">Verificación de Nombre y Lote:</th>
                        <td style="border: 1px solid #ddd; padding: 2px;">{{ $produccion->verificacion_nombre == 1 ? 'Si' : 'No' }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 2px;">Verificación de Fecha Elab.:</th>
                        <td style="border: 1px solid #ddd; padding: 2px;">{{ $produccion->verificacion_date_issue == 1 ? 'Si' : 'No' }}</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 2px;">Verificación de Fecha Ven.:</th>
                        <td style="border: 1px solid #ddd; padding: 2px;">{{ $produccion->verificacion_date_end == 1 ? 'Si' : 'No' }}</td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>

</table>

<br>
<br>
<br>
<br>

<div style="width: 100%; text-align: center; margin-bottom: 1px;">
    <div style="margin: 0 auto; width: 90%;">
        <div style="display: inline-block; width: 30%; text-align: center; margin-right: 15px; font-size: 6px;">
            <div style="border-bottom: 1px solid #000; margin-bottom: 1px;"></div>
            <strong>F. Producción</strong>
        </div>

        <div style="display: inline-block; width: 30%; text-align: center; margin-right: 15px; font-size: 6px;">
            <div style="border-bottom: 1px solid #000; margin-bottom: 1px;"></div>
            <strong>F. Calidad</strong>
        </div>

        <div style="display: inline-block; width: 30%; text-align: center; font-size: 6px;">
            <div style="border-bottom: 1px solid #000; margin-bottom: 1px;"></div>
            <strong>F. Bodega</strong>
        </div>
    </div>
</div>

<table style="width: 100%; margin-bottom: 10px;">
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

<style>
    table {
        border-collapse: collapse;
        width: 100%;
        font-size: 6px;    
    }

    th, td {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 1px;
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
@php
    $invoice = $document->invoice;
    $establishment = $document->establishment;
    $customer = $document->customer;

    $document_xml_service = new Modules\Document\Services\DocumentXmlService;
    
    $note = $document->note;

    $series = ($note->affected_document) ? $note->affected_document->series : $note->data_affected_document->series;
    $document_type_id = ($note->affected_document) ? $note->affected_document->document_type_id : $note->data_affected_document->document_type_id;
    $number = ($note->affected_document) ? $note->affected_document->number : $note->data_affected_document->number;
    $DocumentDate = ($note->affected_document) ? $note->affected_document->date_of_issue->format('d/m/Y') : $note->data_affected_document->date_of_issue->format('d/m/Y');
    $estable = ($note->affected_document) ? $note->affected_document->establishment_id : $note->data_affected_document->establishment_id;
    $DocAfectado = str_pad($estable , '3', '0', STR_PAD_LEFT).'-'.substr($series,1,3).'-'.str_pad($number , '9', '0', STR_PAD_LEFT);

    $total_IVA12 = 0;
    //JOINSOFTWARE
    $total_IVA0 = 0;
    $total_BASE12 = 0;
    //JOINSOFTWARE
    $total_BASE0 = 0;
    $total_IVA8= 0;
    $total_BASE8 = 0;
    $total_IVA14 = 0;
    $total_BASE14 = 0;

    foreach($document->items as $row){
        if($row->affectation_igv_type_id == 10){
            $total_IVA12 = $total_IVA12 + $row->total_igv;
            $total_BASE12 = $total_BASE12 + $row->total_base_igv;
        }
        if($row->affectation_igv_type_id == 11){
            $total_IVA8 = $total_IVA8 + $row->total_igv;
            $total_BASE8 = $total_BASE8 + $row->total_base_igv;
        }
        if($row->affectation_igv_type_id == 12){
            $total_IVA14 = $total_IVA14 + $row->total_igv;
            $total_BASE14 = $total_BASE14 + $row->total_base_igv;
        }
        if($row->affectation_igv_type_id == 30){
            $total_IVA0 = 0;
            $total_BASE0 = $total_BASE0 + $row->total_base_igv;
        }

    }

@endphp
{!!  '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'  !!}
<notaCredito id="comprobante" version="1.1.0">
    <infoTributaria>
        <ambiente>{{ substr($company->soap_type_id,1,1) }}</ambiente>
        <tipoEmision>1</tipoEmision>
        <razonSocial>{{ $company->trade_name }}</razonSocial>
        <nombreComercial>{{ $company->trade_name }}</nombreComercial>
        <ruc>{{ $company->number }}</ruc>
        <claveAcceso>{{ $clave_acceso }}</claveAcceso>
        <codDoc>04</codDoc>
        <estab>{{ $establishment->code }}</estab>
        <ptoEmi>{{ str_pad(substr($document->series,2,2), '3', '0', STR_PAD_LEFT) }}</ptoEmi>
        <secuencial>{{ str_pad($document->number , '9', '0', STR_PAD_LEFT) }}</secuencial>
        <dirMatriz>{{ $establishment->address }}</dirMatriz>
    </infoTributaria>
    <infoNotaCredito>
        <fechaEmision>{{ $document->date_of_issue->format('d/m/Y') }}</fechaEmision>
        <dirEstablecimiento>{{ $establishment->address }}</dirEstablecimiento>
        @if($customer->identity_document_type_id == 1)
        <tipoIdentificacionComprador>05</tipoIdentificacionComprador>
        @endif
        @if($customer->identity_document_type_id == 6)
        <tipoIdentificacionComprador>04</tipoIdentificacionComprador>
        @endif
        @if($customer->identity_document_type_id == 7)
        <tipoIdentificacionComprador>06</tipoIdentificacionComprador>
        @endif
        @if($customer->identity_document_type_id == 0)
        <tipoIdentificacionComprador>07</tipoIdentificacionComprador>
        @endif
        <razonSocialComprador>{{ $customer->name }}</razonSocialComprador>
        <identificacionComprador>{{ $customer->number }}</identificacionComprador>
        <obligadoContabilidad>SI</obligadoContabilidad>
        <codDocModificado>01</codDocModificado>
        <numDocModificado>{{ $DocAfectado }}</numDocModificado>
        <fechaEmisionDocSustento>{{ $DocumentDate }}</fechaEmisionDocSustento>
        <totalSinImpuestos>{{ $document->total_value }}</totalSinImpuestos>
        <valorModificacion>{{ $document->total }}</valorModificacion>
        <moneda>DOLAR</moneda>
        <totalConImpuestos>
            @if($total_BASE0 > 0)
            <totalImpuesto>
                <codigo>2</codigo>
                <codigoPorcentaje>0</codigoPorcentaje>
                <baseImponible>{{  $total_BASE0 }}</baseImponible>
                <valor>{{ $total_IVA0 }}</valor>
            </totalImpuesto>
            @endif
            @if($total_IVA12 > 0)
            <totalImpuesto>
                <codigo>2</codigo>
                <codigoPorcentaje>2</codigoPorcentaje>
                <baseImponible>{{  $total_BASE12 }}</baseImponible>
                <valor>{{ $total_IVA12 }}</valor>
            </totalImpuesto>
            @endif
            @if($total_IVA8 > 0)
            <totalImpuesto>
                <codigo>2</codigo>
                <codigoPorcentaje>2</codigoPorcentaje>
                <baseImponible>{{  $total_BASE8 }}</baseImponible>
                <valor>{{ $total_IVA8 }}</valor>
            </totalImpuesto>
            @endif
            @if($total_IVA14 > 0)
            <totalImpuesto>
                <codigo>2</codigo>
                <codigoPorcentaje>3</codigoPorcentaje>
                <baseImponible>{{  $total_BASE14 }}</baseImponible>
                <valor>{{ $total_IVA14 }}</valor>
            </totalImpuesto>
            @endif
        </totalConImpuestos>
        <motivo>{{ $note->note_description }}</motivo>
    </infoNotaCredito>
    <detalles>
    @foreach($document->items as $row)
        <detalle>
            <codigoInterno>{{ $row->item_id }}</codigoInterno>
            <descripcion>{{ $row->item->description }}</descripcion>
            <cantidad>{{ $row->quantity }}00</cantidad>
            <precioUnitario>{{ $row->unit_value }}</precioUnitario>
            <descuento>{{ $row->total_discount }}</descuento>
            <precioTotalSinImpuesto>{{ $row->total_value }}</precioTotalSinImpuesto>
            <impuestos>
            @if($row->total_base_igv > 0 && $row->affectation_igv_type_id == 10)
                <impuesto>
                    <codigo>2</codigo>
                    <codigoPorcentaje>2</codigoPorcentaje>
                    <tarifa>{{ 12 }}</tarifa>
                    <baseImponible>{{ $row->total_base_igv }}</baseImponible>
                    <valor>{{ $row->total_igv }}</valor>
                </impuesto>
            @endif
            @if($row->total_base_igv > 0 && $row->affectation_igv_type_id == 11)
                <impuesto>
                    <codigo>2</codigo>
                    <codigoPorcentaje>2</codigoPorcentaje>
                    <tarifa>{{ 8 }}</tarifa>
                    <baseImponible>{{ $row->total_base_igv }}</baseImponible>
                    <valor>{{ $row->total_igv }}</valor>
                </impuesto>
            @endif
            @if($row->total_base_igv > 0 && $row->affectation_igv_type_id == 12)
                <impuesto>
                    <codigo>2</codigo>
                    <codigoPorcentaje>3</codigoPorcentaje>
                    <tarifa>{{ 14 }}</tarifa>
                    <baseImponible>{{ $row->total_base_igv }}</baseImponible>
                    <valor>{{ $row->total_igv }}</valor>
                </impuesto>
            @endif
            @if($row->total_base_igv > 0 && $row->affectation_igv_type_id == 30)
                <impuesto>
                    <codigo>2</codigo>
                    <codigoPorcentaje>0</codigoPorcentaje>
                    <tarifa>{{ 0 }}</tarifa>
                    <baseImponible>{{ $row->total_base_igv }}</baseImponible>
                    <valor>{{ 0 }}</valor>
                </impuesto>
            @endif
        </impuestos>
        </detalle>
    @endforeach
    </detalles>
</notaCredito>
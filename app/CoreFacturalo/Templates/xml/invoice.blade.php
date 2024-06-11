@php
    $invoice = $document->invoice;
    $establishment = $document->establishment;
    $customer = $document->customer;
    $payments = $document->payments;
    $document_xml_service = new Modules\Document\Services\DocumentXmlService;

    // Cargos globales que no afectan la base imponible del IGV/IVAP
    $tot_charges = $document_xml_service->getGlobalChargesNoBase($document);
    $fecha = new DateTime();
    //descuento global - item que no afectan la base imponible
    $total_discount_no_base = $document_xml_service->getGlobalDiscountsNoBase($document) + $document_xml_service->getItemsDiscountsNoBase($document);
    // $total_IVA12 = 0;
    // $total_BASE12 = 0;
    // $total_IVA8= 0;
    // $total_BASE8 = 0;
    // $total_IVA14 = 0;
    // $total_BASE14 = 0;
    // $total_IVA0 = 0;
    // foreach($document->items as $row){
    //     if($row->affectation_igv_type_id == 10){
    //         $total_IVA12 = $total_IVA12 + $row->total_igv;
    //         $total_BASE12 = $total_BASE12 + $row->total_base_igv;
    //     }
    //     if($row->affectation_igv_type_id == 11){
    //         $total_IVA8 = $total_IVA8 + $row->total_igv;
    //         $total_BASE8 = $total_BASE8 + $row->total_base_igv;
    //     }
    //     if($row->affectation_igv_type_id == 12){
    //         $total_IVA14 = $total_IVA14 + $row->total_igv;
    //         $total_BASE14 = $total_BASE14 + $row->total_base_igv;
    //     }
    //     if($row->affectation_igv_type_id == 30){
    //         $total_IVA0 = $total_IVA0 + $row->total_base_igv;
    //     }
    // }
    $totales = [];
    $subtotal = 0;

    foreach($document->items as $item){

        $subtotal += $item->total_value;

        $existe = false;
        foreach ($totales as $key => $value) {
            if($value['tarifa'] == intVal($item->affectation_igv_type->percentage)){
                $existe = true;
                $totales[$key]['iva'] += floatVal($item->total_taxes);
                $totales[$key]['subtotal'] += floatVal($item->total_value);

            }
        }
        if( $existe ==  false){
            array_push($totales,[
                'tarifa'=> intVal($item->affectation_igv_type->percentage),
                'iva' => $item->total_taxes,
                'subtotal' => $item->total_value,
                'code' => $item->affectation_igv_type->code,
            ]);
        }
    }
    Log::info("DOCUMENTO A ENVIAR: ".json_encode($document));
    $series = '';
    foreach($document->items as $row){
        if($row->item->lots && count($row->item->lots) > 0){
            foreach($row->item->lots as $lot){
                if(isset($lot->has_sale) && $lot->has_sale){
                    $series = $lot->series;
                }
            }
        }
    }
@endphp
{!!  '<'.'?xml version="1.0" encoding="UTF-8" standalone="no"?'.'>'  !!}
<factura id="comprobante" version="1.1.0">
    <infoTributaria>
        <ambiente>{{ substr($company->soap_type_id,1,1) }}</ambiente>
        <tipoEmision>1</tipoEmision>
        <razonSocial>{{ $company->trade_name }}</razonSocial>
        <nombreComercial>{{ $company->trade_name }}</nombreComercial>
        <ruc>{{ $company->number }}</ruc>
        <claveAcceso>{{ $clave_acceso }}</claveAcceso>
        <codDoc>01</codDoc>
        <estab>{{ $establishment->code }}</estab>
        <ptoEmi>{{ substr($document->series,1,3) }}</ptoEmi>
        <secuencial>{{ str_pad($document->number , '9', '0', STR_PAD_LEFT) }}</secuencial>
        <dirMatriz>{{ $establishment->address }}</dirMatriz>
    </infoTributaria>
    <infoFactura>
        <fechaEmision>{{ $document->date_of_issue->format('d/m/Y') }}</fechaEmision>
        <dirEstablecimiento>{{ $establishment->address }}</dirEstablecimiento>
        <obligadoContabilidad>SI</obligadoContabilidad>
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
        <direccionComprador>{{ $customer->address }}</direccionComprador>
        <totalSinImpuestos>{{ $document->total_taxed + $document->total_unaffected }}</totalSinImpuestos>
        <totalDescuento>{{ $document->total_discount }}</totalDescuento>
        <totalConImpuestos>
            {{-- @if($total_IVA12 > 0)
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
            @if($total_IVA0 > 0)
            <totalImpuesto>
                <codigo>2</codigo>
                <codigoPorcentaje>0</codigoPorcentaje>
                <baseImponible>{{  $total_IVA0 }}</baseImponible>
                <valor>0</valor>
            </totalImpuesto>
            @endif --}}
            @foreach($totales as $impuesto)
            <totalImpuesto>
                <codigo>2</codigo>
                <codigoPorcentaje>{{$impuesto['code']}}</codigoPorcentaje>
                <baseImponible>{{$impuesto['subtotal']}}</baseImponible>
                <valor>{{$impuesto['iva']}}</valor>
            </totalImpuesto>
            @endforeach
        </totalConImpuestos>
        <propina>0.00</propina>
        <importeTotal>{{ $document->total }}</importeTotal>
        <moneda>DOLAR</moneda>
        <pagos>
        @if(count($document->payments) > 0)
            @if($document->payment_condition_id === '01')
            @foreach($payments as $pago)
            <pago>
                <formaPago>{{ $pago->payment_method_type->pago_sri }}</formaPago>
                <total>{{ $pago->payment }}</total>
                <plazo>0</plazo>
                <unidadTiempo>Dias</unidadTiempo>
            </pago>
            @endforeach
            @elseif($document->payment_condition_id === '02')
            @foreach($document->fee as $pago)
            <pago>
                <formaPago>01</formaPago>
                <total>{{ $pago->amount }}</total>
                <plazo>{{ date_diff($document->date_of_issue, $pago->date)->format('%a') - 1 }}</plazo>
                <unidadTiempo>Dias</unidadTiempo>
            </pago>
            @endforeach
            @elseif($document->payment_condition_id === '03')
            @foreach($document->fee as $pago)
            <pago>
                <formaPago>01</formaPago>
                <total>{{ $pago->amount }}</total>
                <plazo>{{ date_diff($document->date_of_issue, $pago->date)->format('%a') - 1 }}</plazo>
                <unidadTiempo>Dias</unidadTiempo>
            </pago>
            @endforeach
            @endif
        @else
            <pago>
                <formaPago>01</formaPago>
                <total>{{ $document->total }}</total>
                <plazo>0</plazo>
                <unidadTiempo>Dias</unidadTiempo>
            </pago>
        @endif
        </pagos>
    </infoFactura>
    <detalles>
    @foreach($document->items as $row)
    @inject('itemLotGroup', 'App\Services\ItemLotsGroupService')
        <detalle>
            <codigoPrincipal>{{ $row->item_id }}</codigoPrincipal>
            <descripcion>{{trim($row->item->name.'/'.$row->item->description.'/'.$row->item->model.'/'.$row->m_item->factory_code.'/ Lote: '.$itemLotGroup->getLote($row->item->IdLoteSelected).'/ Serie: '.$series)}}</descripcion>
            <cantidad>{{ $row->quantity }}00</cantidad>
            <precioUnitario>{{ $row->unit_value }}</precioUnitario>
            <descuento>{{ $row->total_discount }}</descuento>
            <precioTotalSinImpuesto>{{ $row->total_value }}</precioTotalSinImpuesto>
            <impuestos>
                <impuesto>
                    <codigo>2</codigo>
                    <codigoPorcentaje>{{$row->affectation_igv_type->code}}</codigoPorcentaje>
                    <tarifa>{{ intVal($row->affectation_igv_type->percentage) }}</tarifa>
                    <baseImponible>{{ $row->total_base_igv }}</baseImponible>
                    <valor>{{ $row->total_igv }}</valor>
                </impuesto>
            </impuestos>
        </detalle>
    @endforeach
    </detalles>
    @if($document->additional_information[0] != null)
    <infoAdicional>
        <campoAdicional nombre="Informacion Adicional">{{ $document->additional_information[0] }}</campoAdicional>
        <campoAdicional nombre="Vendedor">{{ $document->seller->name }}</campoAdicional>
        <campoAdicional nombre="Orden de compra">{{ $document->purchase_order }}</campoAdicional>
    </infoAdicional>
    @endif
</factura>

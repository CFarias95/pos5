@php
    //$establishment = $document->establishment;
@endphp
{!! '<?xml version="1.0" encoding="utf-8" standalone="no"?>' !!}
<comprobanteRetencion id="comprobante" version="2.0.0">
    <infoTributaria>
        <ambiente>{{ $document['ambiente'] }}</ambiente>
        <tipoEmision>{{ $document['emision'] }}</tipoEmision>
        <razonSocial>{{ $document['razonSocial'] }}</razonSocial>
        <nombreComercial>{{ $document['nombreComercial'] }}</nombreComercial>
        <ruc>{{ $document['ruc'] }}</ruc>
        <claveAcceso>{{ $document['claveAcceso'] }}</claveAcceso>
        <codDoc>{{ $document['codDoc'] }}</codDoc>
        <estab>{{ $document['establecimiento'] }}</estab>
        <ptoEmi>{{ $document['ptoEmision'] }}</ptoEmi>
        <secuencial>{{ $document['secuencial'] }}</secuencial>
        <dirMatriz>{{ $document['dirMatriz'] }}</dirMatriz>
    </infoTributaria>
    <infoCompRetencion>
        <fechaEmision>{{ $document['fechaEmision'] }}</fechaEmision>
        <dirEstablecimiento>{{ $document['disEstablecimiento'] }}</dirEstablecimiento>
        @if($document['contribuyenteEspecial'])
        <contribuyenteEspecial>{{ $document['contribuyenteEspecial'] }}</contribuyenteEspecial>
        @endif
        <obligadoContabilidad>{{ $document['obligadoContabilidad'] }}</obligadoContabilidad>
        <tipoIdentificacionSujetoRetenido>{{ $document['tipoIdentificacionSujetoRetenido'] }}</tipoIdentificacionSujetoRetenido>
        <parteRel>{{ $document['parteRel'] }}</parteRel>
        <razonSocialSujetoRetenido>{{ $document['razonSocialSujetoRetenido'] }}</razonSocialSujetoRetenido>
        <identificacionSujetoRetenido>{{ $document['identificacionSujetoRetenido'] }}</identificacionSujetoRetenido>
        <periodoFiscal>{{ $document['periodoFiscal'] }}</periodoFiscal>
    </infoCompRetencion>
    <docsSustento>
        <docSustento>
            <codSustento>{{ $document['codSustento'] }}</codSustento>
            <codDocSustento>{{ $document['codDocSustento'] }}</codDocSustento>
            <numDocSustento>{{ $document['numDocSustento'] }}</numDocSustento>
            <fechaEmisionDocSustento>{{ $document['fechaEmisionDocSustento'] }}</fechaEmisionDocSustento>
            <numAutDocSustento>{{ $document['numAutDocSustento'] }}</numAutDocSustento>
            <pagoLocExt>{{ $document['pagoLocExt'] }}</pagoLocExt>
            <totalSinImpuestos>{{ $document['totalSinImpuestos'] }}</totalSinImpuestos>
            <importeTotal>{{ $document['importeTotal'] }}</importeTotal>
            <impuestosDocSustento>
                <impuestoDocSustento>
                    <codImpuestoDocSustento>2</codImpuestoDocSustento>
                    <codigoPorcentaje>0</codigoPorcentaje>
                    <baseImponible>{{ $document['baseImponible0'] }}</baseImponible>
                    <tarifa>0</tarifa>
                    <valorImpuesto>0</valorImpuesto>
                </impuestoDocSustento>
                <impuestoDocSustento>
                    <codImpuestoDocSustento>2</codImpuestoDocSustento>
                    <codigoPorcentaje>2</codigoPorcentaje>
                    <baseImponible>{{ $document['baseImponible12'] }}</baseImponible>
                    <tarifa>12</tarifa>
                    <valorImpuesto>{{ $document['valorIva12'] }}</valorImpuesto>
                </impuestoDocSustento>
            </impuestosDocSustento>
            <retenciones>
                @foreach($document['retenciones'] as $ret)
                <retencion>
                    <codigo>{{ $ret['codigo']}}</codigo>
                    <codigoRetencion>{{ $ret['codigoRetencion']}}</codigoRetencion>
                    <baseImponible>{{ $ret['baseImponible']}}</baseImponible>
                    <porcentajeRetener>{{ $ret['porcentajeRetener']}}</porcentajeRetener>
                    <valorRetenido>{{ $ret['valorRetenido']}}</valorRetenido>
                </retencion>
                @endforeach
            </retenciones>
            @if(count($document['fpagos']) > 0)
            <pagos>
                @foreach($document['fpagos'] as $pago)
                <pago>
                    <formaPago>{{ $pago['formaPago'] }}</formaPago>
                    <total> {{ $pago['total'] }}</total>
                </pago>
                @endforeach
            </pagos>
            @else
            <pagos>
                <pago>
                    <formaPago>01</formaPago>
                    <total>{{ $document['importeTotal'] }}</total>
                </pago>
            </pagos>
            @endif
        </docSustento>
    </docsSustento>

</comprobanteRetencion>

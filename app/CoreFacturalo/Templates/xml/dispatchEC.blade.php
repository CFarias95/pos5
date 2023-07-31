{!! '<?xml version="1.0" encoding="utf-8" standalone="no"?>' !!}
<guiaRemision id="comprobante" version="1.1.0">
    <infoTributaria>
        <ambiente>{{$document['ambiente']}}</ambiente>
        <tipoEmision>{{$document['tipoEmision']}}</tipoEmision>
        <razonSocial>{{$document['razonSocial']}}</razonSocial>
        <nombreComercial>{{$document['nombreComercial']}}</nombreComercial>
        <ruc>{{$document['ruc']}}</ruc>
        <claveAcceso>{{$document['claveAcceso']}}</claveAcceso>
        <codDoc>{{$document['codDoc']}}</codDoc>
        <estab>{{$document['establecimiento']}}</estab>
        <ptoEmi>{{$document['ptoEmision']}}</ptoEmi>
        <secuencial>{{$document['secuencial']}}</secuencial>
        <dirMatriz>{{$document['dirMatriz']}}</dirMatriz>
    </infoTributaria>
    <infoGuiaRemision>
        <dirEstablecimiento>{{$document['dirEstablecimiento']}}</dirEstablecimiento>
        <dirPartida>{{$document['dirPartida']}}</dirPartida>
        <razonSocialTransportista>{{$document['razonSocialTransportista']}}</razonSocialTransportista>
        <tipoIdentificacionTransportista>{{$document['tipoIdentificacionTransportista']}}</tipoIdentificacionTransportista>
        <rucTransportista>{{$document['rucTransportista']}}</rucTransportista>
        @if(isset($document['rise']) && $document['rise'] > 0)
        <rise>{{$document['rise']}}</rise>
        @endif
        <obligadoContabilidad>{{$document['obligadoContabilidad']}}</obligadoContabilidad>
        @if(isset($document['contribuyenteEspecial']) && $document['contribuyenteEspecial'] != '')
        <contribuyenteEspecial>{{$document['contribuyenteEspecial']}}</contribuyenteEspecial>
        @endif
        <fechaIniTransporte>{{$document['fechaIniTransporte']}}</fechaIniTransporte>
        <fechaFinTransporte>{{$document['fechaFinTransporte']}}</fechaFinTransporte>
        <placa>{{$document['placa']}}</placa>
    </infoGuiaRemision>
    <destinatarios>
        <destinatario>
            <identificacionDestinatario>{{$document['identificacionDestinatario']}}</identificacionDestinatario>
            <razonSocialDestinatario>{{$document['razonSocialDestinatario']}}</razonSocialDestinatario>
            <dirDestinatario>{{$document['dirDestinatario']}}</dirDestinatario>
            <motivoTraslado>{{$document['motivoTraslado']}}</motivoTraslado>
            @if(isset($document['docAduaneroUnico']))
            <docAduaneroUnico>{{$document['docAduaneroUnico']}}</docAduaneroUnico>
            @endif
            @if(isset($document['codDocSustento']))
            <codDocSustento>{{$document['codDocSustento']}}</codDocSustento>
            @endif
            @if(isset($document['numDocSustento']))
            <numDocSustento>{{$document['numDocSustento']}}</numDocSustento>
            @endif
            @if(isset($document['numAutDocSustento']))
            <numAutDocSustento>{{$document['numAutDocSustento']}}</numAutDocSustento>
            @endif
            @if(isset($document['fechaEmisionDocSustento']))
            <fechaEmisionDocSustento>{{$document['fechaEmisionDocSustento']}}</fechaEmisionDocSustento>
            @endif
            @if(isset($document['detalles']))
            <detalles>
                @foreach ($document['detalles'] as $item)
                <detalle>
                    <codigoInterno>{{$item['codigoInterno']}}</codigoInterno>
                    @if(isset($item['codigoAdicional']))
                    <codigoAdicional>{{$item['codigoAdicional']}}</codigoAdicional>
                    @endif
                    <descripcion>{{$item['descripcion']}}</descripcion>
                    <cantidad>{{$item['cantidad']}}</cantidad>
                </detalle>
                @endforeach
            </detalles>
            @endif
        </destinatario>
    </destinatarios>
    @if(isset($document['adicionales']))
    <infoAdicional>
        @foreach(explode(';',$document['adicionales']) as $info)
        <campoAdicional nombre="{{trim(substr($info,0,strpos($info,':')))}}">{{trim(substr($info,strpos($info,':')))}}</campoAdicional>
        @endforeach
    </infoAdicional>
    @endif
</guiaRemision>

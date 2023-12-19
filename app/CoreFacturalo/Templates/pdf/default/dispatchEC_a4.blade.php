@php
    //$document_base = ($document->note) ? $document->note : null;

    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_number = $document->establishment->code.'-'.substr($document->series,1,3).'-'.str_pad($document->number, 9, '0', STR_PAD_LEFT);
    $str2 = explode("|", $document->correo);
    if ($document->adicionales != null) {
        //$fixStr = rtrim($document->adicionales, ";");
        $str3 = explode(";", $document->adicionales);
        if (count($str3) > 0) {
            $infoMails = explode("=", $str3[0]);
            $arrayMails = [];
            foreach ($str3 as $key => $value) {
                $sub1 = explode("=", $value);
                $addInfo[trim($sub1[0])] = trim($sub1[1]);
            }
        }
    }
    $logo = "storage/uploads/logos/{$company->logo}";

@endphp


<html>
<head>
</head>
<body>
    <table class="full-width">
        <tbody>
            <tr>
                <td width="50%">
                    @if($company->logo)
                        <div class="company_logo_box">
                            <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="margin-left: 50px; padding-bottom: 40px; max-width: 150px;">
                        </div>
                    @endif
                    @if($company->emailLogo)

                        <div class="company_logo_box">
                            <img src="{{ $company->emailLogo }}" alt="{{$document->nombreComercial}}" class="company_logo" style="margin-left: 50px; padding-bottom: 40px; max-width: 150px" >
                        </div>

                    @endif
                    <table>
                        <tbody>
                            <tr>
                                <td style="text-transform: uppercase; background: #eaeaea; padding-left: 15px; padding-right: 15px; padding-bottom: 60px; padding-top: 15px;">
                                    <strong>Emisor: </strong>{{ $company->name }}<br></br>
                                    <strong>RUC: </strong>{{ $company->number }}<br></br>
                                    <strong>Matriz: </strong> <h7 style="text-transform: uppercase;">{{ ($document->establishment->address !== '')? $document->establishment->address : 'sin dirección' }}</h7><br></br>
                                    <strong>Establecimiento: </strong> <h7 style="text-transform: uppercase;">{{ ($document->establishment !== '')? $document->establishment->address : 'sin dirección' }}</h7><br></br>
                                    @if($company->obligadoContabilidad === 'SI')
                                    <strong>Obligado a llevar contabilidad: </strong>SI<br></br>
                                    @else
                                    <strong>Obligado a llevar contabilidad: </strong>NO<br></br>
                                    @endif
                                    @if($company->contribuyente_especial)
                                    <strong>Contribuyente especial: </strong>{{ $company->contribuyente_especial_num }}<br></br>
                                    @endif
                                    @if($company->agente_retencion)
                                    <strong>Agente de Retención Resolución No.: </strong>{{ $company->agente_retencion_num }}<br></br>
                                    @endif
                                    @if($company->rimpe_emp || $company->rimpe_np)
                                    <strong>CONTRIBUYENTE RÉGIMEN RIMPE</strong><br></br>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td width="50%">
                    <table>
                        <tbody>
                            <tr>
                                <td style="background: #eaeaea; height: 30px;"></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 15px 10px 15px; text-align: center;">
                                    <pre style="tab-size: 16; font-size: 14px; text-align: center;"><strong style="align-content: center">GUÍA DE REMISIÓN</strong><br>No.{{$document_number}}</pre>
                                </td>
                            </tr>
                            <tr>
                                <td style="background: #eaeaea; padding-top: 20px; padding-left: 15px; padding-right: 15px;">
                                    <strong>Número de Autorización:</strong>
                                    <br></br>
                                    <h6 style="font-size: 13px;">{{$document->clave_SRI}}</h6>

                                    <strong>Fecha y hora de Autorización:</strong>
                                    <br></br>
                                    <h6 style="font-size: 13px;">{{$document->dateTimeAutorization}}</h6>

                                    @if($document->soap_type_id === '01')
                                    <strong>Ambiente: </strong>PRUEBAS
                                    <br></br>
                                    @endif
                                    @if($document->soap_type_id === '03')
                                    <strong>Ambiente: </strong>INTERNO
                                    <br></br>
                                    @endif
                                    @if($document->soap_type_id === '02')
                                    <strong>Ambiente: </strong>PRODUCCION
                                    <br></br>
                                    @endif
                                    <strong>Emisión: </strong>NORMAL
                                    <br></br>
                                    <strong>Clave de Acceso:</strong>
                                    <br></br>
                                    <div class="text-left">&nbsp;&nbsp;<img class="qr_code" src="data:image/png;base64, {{ $document->barCode }}" /></div>
                                    <h6 style="font-size: 13px;">{{ $document->clave_SRI }}</h6>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <br></br>
    <div class="container text-left" style=" background: #eaeaea; padding-top: 5px; padding-bottom: 5px; padding-left: 15px; padding-right: 15px;">
        <table class="full-width">
            <tbody>
                <tr>
                    <td style="text-transform: uppercase;" >
                        <strong>Identificación (Transportista): </strong>
                    </td>
                    <td style="text-transform: uppercase;">
                        {{ $document->dispatcher->number }}
                    </td>
                </tr>
                <tr>
                    <td style="text-transform: uppercase;" >
                        <strong>Razón Social/ Nombres y Apellidos: </strong>
                    </td>
                    <td style="text-transform: uppercase;" >
                        {{ $document->dispatcher->name }}
                    </td>
                </tr>
                <tr>
                    <td style="text-transform: uppercase;">
                        <strong>Placa: </strong>
                    </td>
                    <td style="text-transform: uppercase;">
                        {{ $document->license_plate }}
                    </td>
                </tr>
                <tr>
                    <td style="text-transform: uppercase;" >
                        <strong>Punto de partida: </strong>
                    </td>
                    <td style="text-transform: uppercase;" >
                        {{$document->origin->address}}
                    </td>
                </tr>
                <tr>
                    <td style="text-transform: uppercase;">
                        <strong>Fecha inicio transporte: </strong> {{$document->date_of_issue->format('Y-m-d')}}
                    </td>
                    <td style="text-transform: uppercase;">
                        <strong>Fecha fin transporte: </strong> {{$document->date_of_shipping->format('Y-m-d')}}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <br></br>
    <div>
        <div class="container text-left" style=" background: #eaeaea; padding-top: 5px; padding-bottom: 5px; padding-left: 15px; padding-right: 15px;">
            <table class="full-width">
                <tbody>
                    <tr>
                        <td style="text-transform: uppercase;" >
                            <strong>Comprobante de venta: </strong>
                        </td>
                        <td style="text-transform: uppercase;">
                            @if($document->reference_document)
                            Factura
                            @endif
                        </td>
                        <td style="text-transform: uppercase;">
                            @if($document->reference_document)
                            {{substr($document->reference_document->clave_SRI, 24, 15)}}
                            @endif
                        </td>
                        <td style="text-transform: uppercase;">
                            <strong>Fecha emision: </strong>
                        </td>
                        <td style="text-transform: uppercase;">
                            @if($document->reference_document)
                            {{$document->reference_document->date_of_issue}}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="text-transform: uppercase;" colspan="2" >
                            <strong>Número autorización </strong>
                        </td>
                        <td style="text-transform: uppercase;" colspan="3" >
                            @if($document->reference_document)
                            {{$document->reference_document->clave_SRI}}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="text-transform: uppercase;" colspan="2">
                            <strong>Motivo traslado: </strong>
                        </td>
                        <td style="text-transform: uppercase;" colspan="3">
                            {{ $document->transfer_reason_type->description }}
                        </td>
                    </tr>
                    <tr>
                        <td style="text-transform: uppercase;" colspan="2">
                            <strong>Destino(punto de llegada): </strong>
                        </td>
                        <td style="text-transform: uppercase;" colspan="3" >
                            {{$document->delivery->address}}
                        </td>
                    </tr>
                    <tr>
                        <td style="text-transform: uppercase;" colspan="2">
                            <strong>Identificación(Destinatario): </strong>
                        </td>
                        <td style="text-transform: uppercase;" colspan="3">
                            {{$document->customer->number}}
                        </td>
                    </tr>
                    <tr>
                        <td style="text-transform: uppercase;" colspan="2">
                            <strong>Razón Social/Nombres y Apellidos: </strong>
                        </td>
                        <td style="text-transform: uppercase;" colspan="3">
                            {{$document->customer->name}}
                        </td>
                    </tr>
                    <tr>
                        <td style="text-transform: uppercase;" colspan="2">
                            <strong>Documento aduanero: </strong>
                        </td>
                        <td style="text-transform: uppercase;" colspan="3">
                        </td>
                    </tr>
                    <tr>
                        <td style="text-transform: uppercase;" colspan="2">
                            <strong>Código establecimiento destino: </strong>
                        </td>
                        <td style="text-transform: uppercase;" colspan="3">
                        </td>
                    </tr>
                    <tr>
                        <td style="text-transform: uppercase;" colspan="2">
                            <strong>Ruta: </strong>
                        </td>
                        <td style="text-transform: uppercase;" colspan="3">
                        </td>
                    </tr>
                    <tr>
                        <td style="text-transform: uppercase;" colspan="5">
                            <table class="full-width">
                                <thead>
                                    <tr style="background: #eaeaea;">
                                        <th class="text-left py-2 pl-4">Cantidad</th>
                                        <th class="text-left py-2 pl-4">Descripción</th>
                                        <th class="text-left py-2 pl-4">Código principal</th>
                                        <th class="text-left py-2 pl-4">Código auxiliar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($document->items as $detalle)
                                    <tr style="background: #f7f7f5;">
                                        <td class="text-left align-top pl-4">
                                            {{$detalle->quantity}}
                                        </td>
                                        <td class="text-left align-top pl-4">
                                            {{$detalle->item->description}}
                                        </td>
                                        <td class="text-left align-top pl-4">
                                            {{$detalle->item_id}}
                                        </td>
                                        <td class="text-left align-top pl-4">
                                            {{($detalle->item->item_code)?$detalle->item->item_code:''}}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <br></br>
    <table class="full-width">
        <tbody>
            <tr>
                <td width="60%" style="position: relative;">
                    <div style="position: absolute; width: 50%; padding-top: 7px; padding-bottom: 7px">
                        <table class="full-width">
                            <thead class="">
                                <tr style="background: #eaeaea;">
                                    <th class="py-2" style="text-align: start; padding-left: 15px; padding-right: 15px;">Información Adicional</th>
                                </tr>
                            </thead>
                            <tbody>
                            @if($document->reference_transfer_id)
                            <tr style="background: #f7f7f5;">
                                <td width="40%" style="text-align: start; padding-left: 15px; padding-right: 15px;">Traslado No {{$document->reference_transfer_id}}</td>
                            </tr>
                            @endif
                            @if($document->adicionales != null)
                                @if(count($arrayMails) > 0)
                                    @foreach($arrayMails as $mails)
                                    <tr style="background: #f7f7f5;">
                                        <td width="40%" style="text-align: start; padding-left: 15px; padding-right: 15px;">{!!$infoMails[0]!!}</td>
                                        <td style="text-align: start; padding-left: 15px; padding-right: 15px;">{!!$mails!!}</td>
                                    </tr>
                                    @endforeach
                                @endif
                                @if(count($str3) > 0)
                                    @foreach($addInfo as $key => $value)
                                    <tr style="background: #f7f7f5;">
                                        <td style="text-align: start; padding-left: 15px; padding-right: 15px;">{!!$key!!}</td>
                                        <td style="text-align: start; padding-left: 15px; padding-right: 15px;">{!!$value!!}</td>
                                    </tr>
                                    @endforeach
                                @endif
                            @endif
                            </tbody>
                        </table>
                        @if(isset($company->terms) && $document->tipoComprobante == 1)

                        <table class="full-width">
                            <thead class="">
                                <tr style="background: #eaeaea;">
                                    <th class="py-2" style="text-align: start; padding-left: 15px; padding-right: 15px;">Términos y condiciones del servicio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="background: #f7f7f5;">
                                    <td style="text-align: start; padding-left: 15px; padding-right: 15px;">
                                        {!! $company->terms !!}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        @endif

                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>

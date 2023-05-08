@php

    $document_number = substr($document->idRetencion,1);
    $str2 = explode("|", $document->purchase->supplier->email);
    $payments = $document->purchase_payments;
    $establishment = $document->establecimiento;
    $purchase = $document->purchase;
    $supplier = $document->purchase->supplier;
    $logo = "storage/uploads/logos/{$company->logo}";

@endphp


<html>
<head>
</head>
<body>
    <!-- Separeted code2 -->
    <!-- Code from JOINSOFTWARE -->

    <table class="full-width">
        <tbody>
            <tr>
                <td width="50%">
                    @if($company->logo)

                        <div class="company_logo_box">
                            <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="max-width: 150px;">
                        </div>

                    @endif
                    <table>
                        <tbody>
                            <tr>
                                <td style="text-transform: uppercase; background: #eaeaea; padding-left: 15px; padding-right: 15px; padding-bottom: 60px; padding-top: 15px;">
                                    <strong>Emisor: </strong>{{ $company->name }}<br></br>
                                    <strong>RUC: </strong>{{ $company->number }}<br></br>
                                    <strong>Matriz: </strong> <h7 style="text-transform: uppercase;">{{ ($establishment->address !== '')? $establishment->address : 'sin dirección' }}</h7><br></br>
                                    @if($company->obligado_contabilidad)
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
                                    <pre style="tab-size: 16; font-size: 14px"><strong>COMPROBANTE DE RETENCION </strong>No.{{$document_number}}</pre>
                                </td>
                            </tr>
                            <tr>
                                <td style="background: #eaeaea; padding-top: 20px; padding-left: 15px; padding-right: 15px;">
                                    <strong>Número de Autorización:</strong>
                                    <br></br>
                                    <h6 style="font-size: 13px;">{{$document->claveAcceso}}</h6>
                                    <br></br>
                                    <br></br>
                                    <strong>Fecha y hora de Autorización:</strong>
                                    <br></br>
                                    {{$document->DateTimeAutorization}}
                                    <br></br>
                                    <br></br>
                                    @if($purchase->soap_type_id === '1')
                                    <strong>Ambiente: </strong>PRUEBAS
                                    <br></br>
                                    @endif
                                    @if($purchase->soap_type_id === '3')
                                    <strong>Ambiente: </strong>INTERNO
                                    <br></br>
                                    @endif
                                    @if($purchase->soap_type_id === '2')
                                    <strong>Ambiente: </strong>PRODUCCION
                                    <br></br>
                                    @endif
                                    <strong>Emisión: </strong>NORMAL
                                    <br></br>
                                    <strong>Clave de Acceso:</strong>
                                    <br></br>
                                    <div class="text-left">&nbsp;&nbsp;<img class="qr_code" src="data:image/png;base64, {{ $document->barCode }}" /></div>
                                    <h6 style="font-size: 13px;">{{ $document->claveAcceso }}</h6>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <div style=" background: #eaeaea; padding-bottom: 20px; padding-left: 15px; padding-right: 15px;">
        <table class="full-width">
            <tbody>
                <tr>
                    <td style="text-transform: uppercase;" width="50%">
                        <div>
                            <strong>Razón Social: </strong>{{ $supplier->name }}<br></br>
                            <strong>Dirección: </strong> {{ $supplier->address }}<br></br>
                            <strong>Fecha Emisión: </strong> {{$purchase->date_of_issue}}
                        </div>
                    </td>
                    <td style="text-transform: uppercase;" width="50%">
                        <div>
                            <br></br>
                            <strong>{{ $supplier->identity_document_type->description }}: </strong> {{ $supplier->number }}<br></br><br></br>
                            <strong>Teléfono: </strong> {{ $supplier->telephone }}<br></br>
                            <strong>Correo: </strong> {{ $supplier->email }}<br></br>

                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div>
        <table class="full-width mt-10 mb-10">
            <thead>
            <tr style="background: #eaeaea;">
                <th class="text-left py-2 pl-4" width="10%">COMPROBANTE</th>
                <th class="text-center py-2" width="8%">NÚMERO</th>
                <th class="text-center py-2" width="8%">FECHA EMISIÓN</th>
                <th class="text-center py-2" width="8%">EJERCICIO FISCAL</th>
                <th class="text-center py-2" width="8%">BASE IMPONIBLE</th>
                <th class="text-center py-2" width="8%">IMPUESTO</th>
                <th class="text-center py-2" width="12%">PORCENTAJE</th>
                <th class="text-center py-2 pr-4" width="8%">VALOR RETENIDO</th>
            </tr>
            </thead>
            <tbody>
            @foreach($document->detalles as $row)
                <tr style="background: #f7f7f5;">
                    <td class="text-left align-top pl-4">
                        {!!$purchase->document_type->description!!}
                    </td>
                    <td class="text-center align-top">
                        {!!$document->secuencial!!}
                    </td>
                    <td class="text-center align-top">
                        {!!$purchase->date_of_issue->format('d/m/Y')!!}
                    </td>
                    <td class="text-center align-top">
                        {!!$document->fechaFizcal!!}
                    </td>
                    <td class="text-center align-top">
                        @if(((double)$row['baseRet'] != $row['baseRet']))
                            {{ $row['baseRet'] }}
                        @else
                            {{ number_format($row['baseRet'], 2) }}
                        @endif
                    </td>
                    <td class="text-center align-top">
                        {!!$row['code']!!}
                    </td>
                    <td class="text-center align-top">{{ number_format($row['porcentajeRet'], 2) }}</td>
                    <td class="text-right align-top pr-4">{{ number_format($row['valorRet'], 2) }}</td>
                </tr>
                <tr style="background: #f7f7f5;">
                    <td colspan="6"></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

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
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>

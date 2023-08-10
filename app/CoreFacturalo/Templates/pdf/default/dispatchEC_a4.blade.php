@php
    $establishment = $document->establishment;
    $customer = $document->customer;

    $document_number = $document->series.'-'.$document->establishment->code.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $document_type_dispatcher = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->dispatcher->identity_document_type_id);

@endphp
<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
<table class="full-width">
    <tr>
        @if($company->logo)
            <td width="10%">
                <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" alt="{{ $company->name }}"  class="company_logo" style="max-width: 300px">
            </td>
        @else
            <td width="10%">
                {{--<img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px">--}}
            </td>
        @endif
        <td width="50%" class="pl-3">
            <div class="text-left">
                <h3 class="">{{ $company->name }}</h3>
                <h4>{{ 'RUC '.$company->number }}</h4>
                <h5 style="text-transform: uppercase;">
                    {{ ($establishment->address !== '-')? $establishment->address : '' }}
                    {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                    {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                    {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                </h5>
                <h5>{{ ($establishment->email !== '-')? $establishment->email : '' }}</h5>
                <h5>{{ ($establishment->telephone !== '-')? $establishment->telephone : '' }}</h5>
            </div>
        </td>
        <td width="40%" class="border-box p-4 text-center">
            <h4 class="text-center">{{ $document->document_type->description }}</h4>
            <h3 class="text-center">{{ $document_number }}</h3>
        </td>
    </tr>
</table>
<table class="full-width border-box mt-10 mb-10">
    <thead>
    <tr>
        <th class="border-bottom text-left">DESTINATARIO</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>Razón Social: {{ $customer->name }}</td>
    </tr>
    <tr>
        <td>RUC: {{ $customer->number }}
        </td>
    </tr>
    <tr>
        <td>Dirección: {{ $customer->address }}
            {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
            {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
            {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
        </td>
    </tr>
    @if ($customer->telephone)
    <tr>
        <td>Teléfono:{{ $customer->telephone }}</td>
    </tr>
    @endif
    <tr>
        <td>Vendedor: {{ $document->user->name }}</td>
    </tr>
    </tbody>
</table>
<table class="full-width border-box mt-10 mb-10">
    <thead>
    <tr>
        <th class="border-bottom text-left" colspan="2">ENVIO</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>Fecha Emisión: {{ $document->date_of_issue->format('Y-m-d') }}</td>
        <td>Fecha Inicio de Traslado: {{ $document->date_of_shipping->format('Y-m-d') }}</td>
    </tr>
    <tr>
        <td>Motivo Traslado: {{ $document->transfer_reason_type->description }}</td>
        <td>Modalidad de Transporte: {{ $document->transport_mode_type->description }}</td>
    </tr>

    @if($document->transfer_reason_description)
    <tr>
        <td colspan="2">Descripción de motivo de traslado: {{ $document->transfer_reason_description }}</td>
    </tr>
    @endif

    @if($document->related)
    <tr>
        <td>Número de documento (DAM): {{ $document->related->number }}</td>
        <td>Tipo documento relacionado: {{ $document->getRelatedDocumentTypeDescription() }}</td>
    </tr>
    @endif

    <tr>
        <td>Peso Bruto Total({{ $document->unit_type_id }}): {{ $document->total_weight }}</td>
        @if($document->packages_number)
        <td>Número de Bultos: {{ $document->packages_number }}</td>
        @endif
    </tr>
    <tr>
        <td>P.Partida: {{ $document->origin->location_id }} - {{ $document->origin->address }}</td>
        <td>P.Llegada: {{ $document->delivery->location_id }} - {{ $document->delivery->address }}</td>
    </tr>
    @if($document->order_form_external)
    <tr>
        <td>Orden de pedido: {{ $document->order_form_external }}</td>
        <td></td>
    </tr>
    @endif
    </tbody>
</table>
<table class="full-width border-box mt-10 mb-10">
    <thead>
    <tr>
        <th class="border-bottom text-left" colspan="2">TRANSPORTE</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>Nombre y/o razón social: {{ $document->dispatcher->name }}</td>
        <td>{{ $document_type_dispatcher->description }}: {{ $document->dispatcher->number }}</td>
    </tr>
    <tbody>
    <tr>
        @if($document->license_plate)
        <td>Número de placa del vehículo: {{ $document->license_plate }}</td>
        @endif
        @if($document->driver->number)
        <td>Conductor: {{ $document->driver->number }}</td>
        @endif
    </tr>
    <tr>
        @if($document->secondary_license_plates)
            @if($document->secondary_license_plates->semitrailer)
                <td>Número de placa semirremolque: {{ $document->secondary_license_plates->semitrailer }}</td>
            @endif
        @endif
        @if($document->driver->license)
            <td>Licencia del conductor: {{ $document->driver->license }}</td>
        @endif
    </tr>
</table>
<table class="full-width border-box mt-10 mb-10">
    <thead class="">
    <tr>
        <th class="border-top-bottom text-center">Item</th>
        <th class="border-top-bottom text-center">Código</th>
        <th class="border-top-bottom text-left">Descripción</th>
        <th class="border-top-bottom text-left">Modelo</th>
        <th class="border-top-bottom text-center">Unidad</th>
        <th class="border-top-bottom text-right">Cantidad</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document->items as $row)
        <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td class="text-center">{{ $row->item->internal_id }}</td>
            <td class="text-left">
                @if($row->name_product_pdf)
                    {!!$row->name_product_pdf!!}
                @else
                    {!!$row->item->description!!}
                @endif

                @if (!empty($row->item->presentation)) {!!$row->item->presentation->description!!} @endif

                @if($row->attributes)
                    @foreach($row->attributes as $attr)
                        <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                    @endforeach
                @endif
                @if($row->discounts)
                    @foreach($row->discounts as $dtos)
                        <br/><span style="font-size: 9px">{{ $dtos->factor * 100 }}% {{$dtos->description }}</span>
                    @endforeach
                @endif
                @if($row->relation_item->is_set == 1)
                    <br>
                    @inject('itemSet', 'App\Services\ItemSetService')
                    @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                        {{$item}}<br>
                    @endforeach
                @endif

                @if($document->has_prepayment)
                    <br>
                    *** Pago Anticipado ***
                @endif
            </td>
            <td class="text-left">{{ $row->item->model ?? '' }}</td>
            <td class="text-center">{{ $row->item->unit_type_id }}</td>
            <td class="text-right">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

@if($document->observations)
<table class="full-width border-box mt-10 mb-10">
    <tr>
        <td class="text-bold border-bottom font-bold">OBSERVACIONES</td>
    </tr>
    <tr>
        <td>{{ $document->observations }}</td>
    </tr>
</table>
@endif

@if ($document->reference_document)
<table class="full-width border-box">
    @if($document->reference_document)
    <tr>
        <td class="text-bold border-bottom font-bold">{{$document->reference_document->document_type->description}}</td>
    </tr>
    <tr>
        <td>{{ ($document->reference_document) ? $document->reference_document->number_full : "" }}</td>
    </tr>
    @endif
</table>
@endif
@if ($document->data_affected_document)
    @php
        $document_data_affected_document = $document->data_affected_document;

    $number = (property_exists($document_data_affected_document,'number'))?$document_data_affected_document->number:null;
    $series = (property_exists($document_data_affected_document,'series'))?$document_data_affected_document->series:null;
    $document_type_id = (property_exists($document_data_affected_document,'document_type_id'))?$document_data_affected_document->document_type_id:null;

    @endphp
    @if($number !== null && $series !== null && $document_type_id !== null)

        @php
            $documentType  = App\Models\Tenant\Catalogs\DocumentType::find($document_type_id);
            $textDocumentType = $documentType->getDescription();
        @endphp
        <table class="full-width border-box">
            <tr>
                <td class="text-bold border-bottom font-bold">{{$textDocumentType}}</td>
            </tr>
            <tr>
                <td>{{$series }}-{{$number}}</td>
            </tr>
        </table>
    @endif
@endif
@if ($document->reference_order_form_id)
<table class="full-width border-box">
    @if($document->order_form)
    <tr>
        <td class="text-bold border-bottom font-bold">ORDEN DE PEDIDO</td>
    </tr>
    <tr>
        <td>{{ ($document->order_form) ? $document->order_form->number_full : "" }}</td>
    </tr>
    @endif
</table>

@elseif ($document->order_form_external)
<table class="full-width border-box">
    <tr>
        <td class="text-bold border-bottom font-bold">ORDEN DE PEDIDO</td>
    </tr>
    <tr>
        <td>{{ $document->order_form_external }}</td>
    </tr>
</table>

@endif


@if ($document->reference_sale_note_id)
<table class="full-width border-box">
    @if($document->sale_note)
    <tr>
        <td class="text-bold border-bottom font-bold">NOTA DE VENTA</td>
    </tr>
    <tr>
        <td>{{ ($document->sale_note) ? $document->sale_note->number_full : "" }}</td>
    </tr>
    @endif
</table>
@endif

@if ($document->terms_condition)
    <br>
    <table class="full-width">
        <tr>
            <td>
                <h6 style="font-size: 12px; font-weight: bold;">Términos y condiciones del servicio</h6>
                {!! $document->terms_condition !!}
            </td>
        </tr>
    </table>
@endif

</body>
</html>


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
                            <img src="{{ $company->emailLogo }}" alt="{{$document->trade_name}}" class="company_logo" style="margin-left: 50px; padding-bottom: 40px; max-width: 150px" >
                        </div>

                    @endif
                    <table>
                        <tbody>
                            <tr>
                                <td style="text-transform: uppercase; background: #eaeaea; padding-left: 15px; padding-right: 15px; padding-bottom: 60px; padding-top: 15px;">
                                    <strong>Emisor: </strong>{{ $company->name }}<br></br>
                                    <strong>RUC: </strong>{{ $company->number }}<br></br>
                                    <strong>Matriz: </strong> <h7 style="text-transform: uppercase;">{{ ($document->establishment->address !== '')? $$document->establishment->address : 'sin dirección' }}</h7><br></br>
                                    <strong>Establecimiento: </strong> <h7 style="text-transform: uppercase;">{{ ($document->establishment->address !== '')? $document->establishment->address : 'sin dirección' }}</h7><br></br>
                                    @if($company->obligado_contabilidad > 0)
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
                                    <pre style="tab-size: 16; font-size: 14px;"><strong style="align-content: center">GUÍA DE REMISIÓN</strong>         No.{{$document_number}}</pre>
                                </td>
                            </tr>
                            <tr>
                                <td style="background: #eaeaea; padding-top: 20px; padding-left: 15px; padding-right: 15px;">
                                    <strong>Número de Autorización:</strong>
                                    <br></br>
                                    <h6 style="font-size: 13px;">{{$document->clave_SRI}}</h6>

                                    <strong>Fecha y hora de Autorización:</strong>
                                    <br></br>
                                    <h6 style="font-size: 13px;">{{$document->dateTimeAutorization}} </h6>

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
                                    <div class="text-left">&nbsp;&nbsp;<img class="qr_code" src="data:image/png;base64, {{ $document->qr }}" /></div>
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
                        <strong>Fecha inicio transporte: </strong> {{$document->date_of_issue}}
                    </td>
                    <td style="text-transform: uppercase;">
                        <strong>Fecha fin transporte: </strong> {{$document->date_of_shipping}}
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
                            FACTURA ELECTRÓNICA
                            @endif
                        </td>
                        <td style="text-transform: uppercase;">
                            @if($document->reference_document)
                            {{sub_str($document->reference_document->clave_SRI,22,15)}}
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
                    @endif
                    <tr>
                        <td style="text-transform: uppercase;" colspan="2">
                            <strong>Motivo traslado: </strong>
                        </td>
                        <td style="text-transform: uppercase;" colspan="3">
                            {{ $document->transfer_reason_description }}
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
                            {{$document->container_number}}
                        </td>
                    </tr>
                    <tr>
                        <td style="text-transform: uppercase;" colspan="2">
                            <strong>Ruta: </strong>
                        </td>
                        <td style="text-transform: uppercase;" colspan="3">
                            {{$document->optional}}
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
                                            {{$detalle->item->internal_id}}
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

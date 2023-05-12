{{-- @php
    $establishment = $document->establishment;
    $customer = $document->customer;

    $document_base = $document->note;
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $document_type_description_array = [
        '01' => 'FACTURA',
        '03' => 'BOLETA DE VENTA',
        '07' => 'NOTA DE CREDITO',
        '08' => 'NOTA DE DEBITO',
    ];
    $identity_document_type_description_array = [
        '-' => 'S/D',
        '0' => 'S/D',
        '1' => 'DNI',
        '6' => 'RUC',
    ];

    $affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

    //$affected_document_number = $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT);
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');

    $logo = "storage/uploads/logos/{$company->logo}";
    if($establishment->logo) {
        $logo = "{$establishment->logo}";
    }

@endphp
<html>
<head>
</head>
<body>
<table class="full-width">
    <tr>
        @if($company->logo)
            <td width="20%">
                <div class="company_logo_box">
                    <img src="data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$company->name}}" alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                </div>
            </td>
        @else
            <td width="20%">
                <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px">
            </td>
        @endif
        <td width="50%" class="pl-3">
            <div class="text-left">
                <h4 class="">{{ $company->name }}</h4>
                <h5>{{ 'RUC '.$company->number }}</h5>
                <h6 style="text-transform: uppercase;">
                    {{ ($establishment->address !== '-')? $establishment->address : '' }}
                    {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                    {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                </h6>
                <h6>{{ ($establishment->email !== '-')? $establishment->email : '' }}</h6>
                <h6>{{ ($establishment->telephone !== '-')? $establishment->telephone : '' }}</h6>
            </div>
        </td>
        <td width="30%" class="border-box py-4 px-2 text-center">
            <h5 class="text-center">{{ $document->document_type->description }}</h5>
            <h3 class="text-center">{{ $document_number }}</h3>
        </td>
    </tr>
</table>

<table class="full-width mt-5">
    <tr>
        <td width="120px">FEHA AUTORIZADO</td>
        <td width="8px">:</td>
        <td>{{$document->date_authorization}} {{ $document->time_authorization}}</td>
    </tr>
    <tr>
        <td width="120px">FECHA DE EMISIÓN</td>
        <td width="5px">:</td>
        <td>{{ $document->date_of_issue->format('Y-m-d') }}</td>
    </tr>
    <tr>
        <td>CLIENTE</td>
        <td>:</td>
        <td>{{ $customer->name }}</td>
    </tr>
    <tr>
        <td>{{ $customer->identity_document_type->description }}</td>
        <td>:</td>
        <td>{{ $customer->number }}</td>
    </tr>
    @if ($customer->address !== '')
    <tr>
        <td class="align-top">DIRECCIÓN</td>
        <td>:</td>
        <td>
            {{ $customer->address }}
            <!--{{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}-->
            {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
            {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
        </td>
    </tr>
    @endif
</table>

@if ($document->guides)
<table class="full-width mt-3">
@foreach($document->guides as $guide)
    <tr>
        <td>{{ $guide->document_type_id }}</td>
        <td>{{ $guide->number }}</td>
    </tr>
@endforeach
</table>
@endif

<table class="full-width mt-3">
    @if ($document->purchase_order)
    <tr>
        <td>ORDEN DE COMPRA</td>
        <td>:</td>
        <td>{{ $document->purchase_order }}</td>
    </tr>
    @endif
    <tr>
        <td width="120px">DOC. AFECTADO</td>
        <td width="5px">:</td>
        <td>{{ $affected_document_number }}</td>
    </tr>
    <tr>
        <td>TIPO DE NOTA</td>
        <td>:</td>
        <td>{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>
    </tr>
    <tr>
        <td>DESCRIPCIÓN</td>
        <td>:</td>
        <td>{{ $document_base->note_description }}</td>
    </tr>
</table>
<table class="full-width mt-10 mb-10">
    <thead class="">
    <tr class="bg-grey">
        <th class="border-top-bottom text-left py-2" width="">COD.</th>
        <th class="border-top-bottom text-center py-2" width="8%">CANT.</th>
        <th class="border-top-bottom text-center py-2" width="8%">UNIDAD</th>
        <th class="border-top-bottom text-left py-2">DESCRIPCIÓN</th>
        <th class="border-top-bottom text-right py-2" width="12%">P.UNIT</th>
        <th class="border-top-bottom text-right py-2" width="8%">DTO.</th>
        <th class="border-top-bottom text-right py-2" width="12%">TOTAL</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document->items as $row)
        <tr>
            <td class="text-left align-top">{{ $row->relation_item->internal_id }}</td>
            <td class="text-center">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-center">{{ $row->item->unit_type_id }}</td>
            <td class="text-left">
                {!! $row->item->description !!}
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
            </td>
            <td class="text-right">{{ number_format($row->unit_price, 2) }}</td>
            <td class="text-right align-top">
                @if($row->discounts)
                    @php
                        $total_discount_line = 0;
                        foreach ($row->discounts as $disto) {
                            $total_discount_line = $total_discount_line + $disto->amount;
                        }
                    @endphp
                    {{ number_format($total_discount_line, 2) }}
                @else
                0
                @endif
            </td>
            <td class="text-right">{{ number_format($row->total, 2) }}</td>
        </tr>
        <tr>
            <td colspan="7" class="border-bottom"></td>
        </tr>
    @endforeach
        @if($document->total_exportation > 0)
            <tr>
                <td colspan="6" class="text-right font-bold">OP. EXPORTACIÓN: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_exportation, 2) }}</td>
            </tr>
        @endif
        @if($document->total_free > 0)
            <tr>
                <td colspan="6" class="text-right font-bold">OP. GRATUITAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_free, 2) }}</td>
            </tr>
        @endif
        @if($document->total_unaffected > 0)
            <tr>
                <td colspan="6" class="text-right font-bold">OP. INAFECTAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
            </tr>
        @endif
        @if($document->total_exonerated > 0)
            <tr>
                <td colspan="6" class="text-right font-bold">OP. EXONERADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_exonerated, 2) }}</td>
            </tr>
        @endif
        @if($document->total_taxed > 0)
            <tr>
                <td colspan="6" class="text-right font-bold">OP. GRAVADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
        @endif
        @if($document->total_discount > 0)
            <tr>
                <td colspan="6" class="text-right font-bold">{{(($document->total_prepayment > 0) ? 'ANTICIPO':'DESCUENTO TOTAL')}}: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td colspan="6" class="text-right font-bold">IGV: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_igv, 2) }}</td>
        </tr>
        <tr>
            <td colspan="6" class="text-right font-bold">TOTAL A PAGAR: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
        </tr>
    </tbody>
    <tfoot style="border-top: 1px solid #333;">
    <tr>
        <td colspan="5" class="font-lg"  style="padding-top: 2rem;text-transform: uppercase;">Son: <span class="font-bold">{{ $document->number_to_letter }} {{ $document->currency_type->description }}</span></td>
    </tr>
    @if(isset($document->optional->observations))
        <tr>
            <td colspan="3"><b>Obsevaciones</b></td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td colspan="3">{{ $document->optional->observations }}</td>
            <td colspan="2"></td>
        </tr>
    @endif
    </tfoot>
</table>

<table class="full-width">
    <tr>
        <td width="65%">
            <div class="text-left"><img class="qr_code" src="data:image/png;base64, {{ $document->qr }}" /></div>
            <p>Código Hash: {{ $document->hash }}</p>
        </td>
    </tr>
</table>
</body>
</html> --}}

{{-- JOINSOFTWARE --}}
@php
    $establishment = $document->establishment;
    $customer = $document->customer;
    $invoice = $document->invoice;
    $document_base = ($document->note) ? $document->note : null;

    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_base = $document->note;
    //JOINSOFTWARE
    $establishment2 = $document_base->affected_document->establishment->code;
    //JOINSOFTWARE
    $document_number = $document->establishment->code.substr($document->series, 1).''.str_pad($document->number, 9, '0', STR_PAD_LEFT);
    $document_type_description_array = [
        '01' => 'FACTURA',
        '03' => 'BOLETA DE VENTA',
        '07' => 'NOTA DE CREDITO',
        '08' => 'NOTA DE DEBITO',
    ];
    $identity_document_type_description_array = [
        '-' => 'S/D',
        '0' => 'S/D',
        '1' => 'DNI',
        '6' => 'RUC',
    ];
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();

    if($document_base) {
        //JOINSOFTWARE
        $affected_document_number = ($document_base->affected_document) ? $establishment2.substr($document_base->affected_document->series, 1).''.str_pad($document_base->affected_document->number, 9, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

    } else {

        $affected_document_number = null;
    }

    $payments = $document->payments;

    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = ($document->total - $total_payment) - $document->payments->sum('change');

    $logo = "storage/uploads/logos/{$company->logo}";
    if($establishment->logo) {
        $logo = "{$establishment->logo}";
    }

    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();

    $total12=0;
    $totalIVA12=0;

    $total8=0;
    $totalIVA8=0;

    $total0=0.00;
    $totalIVA0=0.00;

    $total14=0;
    $totalIVA14=0;

    foreach($document->items as $item){

        if($item->affectation_igv_type_id === '10'){
            //JOINSOFTWARE
            $total12=$total12 + $item->total_value;
            $totalIVA12= $totalIVA12 + $item->total_taxes;
        }
        if($item->affectation_igv_type_id === '11'){
            //JOINSOFTWARE
            $total8=$total8 + $item->total_value;
            $totalIVA8= $totalIVA8 + $item->total_taxes;
        }
        if($item->affectation_igv_type_id === '12'){
            //JOINSOFTWARE
            $total14=$total14 + $item->total_value;
            $totalIVA14= $totalIVA14 + $item->total_taxes;
        }
        if($item->affectation_igv_type_id === '30'){
            //JOINSOFTWARE
            $total0=$total0 + $item->total_value;
            $totalIVA0= $totalIVA0 + $item->total_taxes;
        }
    }
@endphp


<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link rel="stylesheet" href="{{ $path_style }}" />--}}
</head>
<body>

    <!-- Separeted code1 -->


    <!--
    @if($document->state_type->id == '11')
        <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
            <img src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")))}}" alt="anulado" class="" style="opacity: 0.6;">
        </div>
    @endif
    <table class="full-width">
        <tr>
            @if($company->logo)
                <td width="20%">
                    <div class="company_logo_box">
                        <img src="data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$company->name}}" class="company_logo" style="max-width: 150px;">
                    </div>
                </td>
            @else
                <td width="20%">
                    {{--<img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px">--}}
                </td>
            @endif
            <td width="50%" class="pl-3">
                <div class="text-left">
                    <h4 class="">{{ $company->name }}</h4>
                    <h5>{{ 'RUC '.$company->number }}</h5>
                    <h6 style="text-transform: uppercase;">
                        {{ ($establishment->address !== '-')? $establishment->address : '' }}
                        {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                        {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                        {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                    </h6>

                    @isset($establishment->trade_address)
                        <h6>{{ ($establishment->trade_address !== '-')? 'D. Comercial: '.$establishment->trade_address : '' }}</h6>
                    @endisset

                    <h6>{{ ($establishment->telephone !== '-')? 'Central telefónica: '.$establishment->telephone : '' }}</h6>

                    <h6>{{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}</h6>

                    @isset($establishment->web_address)
                        <h6>{{ ($establishment->web_address !== '-')? 'Web: '.$establishment->web_address : '' }}</h6>
                    @endisset

                    @isset($establishment->aditional_information)
                        <h6>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</h6>
                    @endisset
                </div>
            </td>
            <td width="30%" class="border-box py-4 px-2 text-center">
                <h5 class="text-center">{{ $document->document_type->description }}</h5>
                <h3 class="text-center">{{ $document_number }}</h3>
            </td>
        </tr>
    </table>

    <table class="full-width mt-5">
        <tr>
            <td width="120px">CLAVE ACCESO:</td>
            <td>{{$document->clave_SRI}}</td>
        </tr>
        <tr>
            <td width="120px">FEHA AUTORIZADO:</td>
            <td>{{$document->date_authorization}} {{ $document->time_authorization}}</td>
        </tr>
        <tr>
            <td width="120px">FECHA DE EMISIÓN:</td>
            <td>{{$document->date_of_issue->format('Y-m-d')}}</td>

            @if ($document->detraction)

                <td width="120px">N. CTA DETRACCIONES:</td>
                <td>{{ $document->detraction->bank_account}}</td>
                
            @endif
        </tr>
        @if($invoice)
            <tr>
                <td>FECHA DE VENCIMIENTO:</td>
                <td>{{$invoice->date_of_due->format('Y-m-d')}}</td>
            </tr>
        @endif

        @if ($document->detraction)
            <td width="140px">B/S SUJETO A DETRACCIÓN:</td>
            @inject('detractionType', 'App\Services\DetractionTypeService')
            <td width="220px">{{$document->detraction->detraction_type_id}} - {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id ) }}</td>

        @endif
        <tr>
            <td style="vertical-align: top;">CLIENTE:</td>
            <td style="vertical-align: top;">
                {{ $customer->name }}
                @if ($customer->internal_code ?? false)
                <br>
                <small>{{ $customer->internal_code ?? '' }}</small>
                @endif
            </td>

            @if ($document->detraction)
                <td width="120px">MÉTODO DE PAGO:</td>
                <td width="220px">{{ $detractionType->getPaymentMethodTypeDescription($document->detraction->payment_method_id ) }}</td>
            @endif

        </tr>
        <tr>
            <td>{{ $customer->identity_document_type->description }}:</td>
            <td>{{$customer->number}}</td>

            @if ($document->detraction)

                <td width="120px">P. DETRACCIÓN:</td>
                <td>{{ $document->detraction->percentage}}%</td>
            @endif
        </tr>
        @if ($customer->address !== '')
        <tr>
            <td class="align-top">DIRECCIÓN:</td>
            <td style="text-transform: uppercase;">
                {{ $customer->address }}
                {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
                {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
                {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
            </td>

            @if ($document->detraction)
                <td width="120px">MONTO DETRACCIÓN</td>
                <td width="8px">:</td>
                <td>S/ {{ $document->detraction->amount}}</td>
            @endif
        </tr>
        @endif

        @if ($document->reference_data)
            <tr>
                <td width="120px">D. REFERENCIA</td>
                <td width="8px">:</td>
                <td>{{ $document->reference_data}}</td>
            </tr>
        @endif

        @if ($document->detraction)
            @if($document->detraction->pay_constancy)
            <tr>
                <td colspan="3">
                </td>
                <td width="120px">CONSTANCIA DE PAGO</td>
                <td width="8px">:</td>
                <td>{{ $document->detraction->pay_constancy}}</td>
            </tr>
            @endif
        @endif

        @if($document->detraction && $invoice->operation_type_id == '1004')
        <tr>
            <td colspan="4"><strong>DETALLE - SERVICIOS DE TRANSPORTE DE CARGA</strong></td>
        </tr>
        <tr>
            <td class="align-top">Ubigeo origen</td>
            <td>:</td>
            <td>{{ $document->detraction->origin_location_id[2] }}</td>

            <td width="120px">Dirección origen</td>
            <td width="8px">:</td>
            <td>{{ $document->detraction->origin_address }}</td>
        </tr>
        <tr>
            <td class="align-top">Ubigeo destino</td>
            <td>:</td>
            <td>{{ $document->detraction->delivery_location_id[2] }}</td>

            <td width="120px">Dirección destino</td>
            <td width="8px">:</td>
            <td>{{ $document->detraction->delivery_address }}</td>
        </tr>
        <tr>
            <td class="align-top" width="170px">Valor referencial servicio de transporte</td>
            <td>:</td>
            <td>{{ $document->detraction->reference_value_service }}</td>

            <td width="170px">Valor referencia carga efectiva</td>
            <td width="8px">:</td>
            <td>{{ $document->detraction->reference_value_effective_load }}</td>
        </tr>
        <tr>
            <td class="align-top">Valor referencial carga útil</td>
            <td>:</td>
            <td>{{ $document->detraction->reference_value_payload }}</td>

            <td width="120px">Detalle del viaje</td>
            <td width="8px">:</td>
            <td>{{ $document->detraction->trip_detail }}</td>
        </tr>
        @endif

    </table>


    @if ($document->retention)
        <table class="full-width mt-3">
            <tr>
                <td colspan="3">
                    <strong>Información de la retención</strong>
                </td>
            </tr>
            <tr>
                <td width="120px">Base imponible</td>
                <td width="8px">:</td>
                <td>{{ $document->currency_type->symbol}} {{ $document->retention->base }}</td>

                <td width="80px">Porcentaje</td>
                <td width="8px">:</td>
                <td>{{ $document->retention->percentage * 100 }}%</td>
            </tr>
            <tr>
                <td width="120px">Monto</td>
                <td width="8px">:</td>
                <td>{{ $document->currency_type->symbol}} {{ $document->retention->amount }}</td>
            </tr>
        </table>
    @endif


    @if ($document->guides)
    <br/>
    <table>
        @foreach($document->guides as $guide)
            <tr>
                @if(isset($guide->document_type_description))
                <td>{{ $guide->document_type_description }}</td>
                @else
                <td>{{ $guide->document_type_id }}</td>
                @endif
                <td>:</td>
                <td>{{ $guide->number }}</td>
            </tr>
        @endforeach
    </table>
    @endif


    @if ($document->transport)
    <br>
    <strong>Transporte de pasajeros</strong>
    @php
        $transport = $document->transport;
        $origin_district_id = (array)$transport->origin_district_id;
        $destinatation_district_id = (array)$transport->destinatation_district_id;
        $origin_district = Modules\Order\Services\AddressFullService::getDescription($origin_district_id[2]);
        $destinatation_district = Modules\Order\Services\AddressFullService::getDescription($destinatation_district_id[2]);
    @endphp

    <table class="full-width mt-3">
        <tr>
            <td width="120px">{{ $transport->identity_document_type->description }}</td>
            <td width="8px">:</td>
            <td>{{ $transport->number_identity_document }}</td>
            <td width="120px">NOMBRE</td>
            <td width="8px">:</td>
            <td>{{ $transport->passenger_fullname }}</td>
        </tr>
        <tr>
            <td width="120px">N° ASIENTO</td>
            <td width="8px">:</td>
            <td>{{ $transport->seat_number }}</td>
            <td width="120px">M. PASAJERO</td>
            <td width="8px">:</td>
            <td>{{ $transport->passenger_manifest }}</td>
        </tr>
        <tr>
            <td width="120px">F. INICIO</td>
            <td width="8px">:</td>
            <td>{{ $transport->start_date }}</td>
            <td width="120px">H. INICIO</td>
            <td width="8px">:</td>
            <td>{{ $transport->start_time }}</td>
        </tr>
        <tr>
            <td width="120px">U. ORIGEN</td>
            <td width="8px">:</td>
            <td>{{ $origin_district }}</td>
            <td width="120px">D. ORIGEN</td>
            <td width="8px">:</td>
            <td>{{ $transport->origin_address }}</td>
        </tr>
        <tr>
            <td width="120px">U. DESTINO</td>
            <td width="8px">:</td>
            <td>{{ $destinatation_district }}</td>
            <td width="120px">D. DESTINO</td>
            <td width="8px">:</td>
            <td>{{ $transport->destinatation_address }}</td>
        </tr>
    </table>
    @endif


    @if ($document->reference_guides)
        @if (count($document->reference_guides) > 0)
        <br/>
        <strong>Guias de remisión</strong>
        <table>
            @foreach($document->reference_guides as $guide)
                <tr>
                    <td>{{ $guide->series }}</td>
                    <td>-</td>
                    <td>{{ $guide->number }}</td>
                </tr>
            @endforeach
        </table>
        @endif
    @endif



    <table class="full-width mt-3">
        @if ($document->prepayments)
            @foreach($document->prepayments as $p)
            <tr>
                <td width="120px">ANTICIPO</td>
                <td width="8px">:</td>
                <td>{{$p->number}}</td>
            </tr>
            @endforeach
        @endif
        @if ($document->purchase_order)
            <tr>
                <td width="120px">ORDEN DE COMPRA</td>
                <td width="8px">:</td>
                <td>{{ $document->purchase_order }}</td>
            </tr>
        @endif
        @if ($document->quotation_id)
            <tr>
                <td width="120px">COTIZACIÓN</td>
                <td width="8px">:</td>
                <td>{{ $document->quotation->identifier }}</td>

                @isset($document->quotation->delivery_date)
                        <td width="120px">F. ENTREGA</td>
                        <td width="8px">:</td>
                        <td>{{ $document->date_of_issue->addDays($document->quotation->delivery_date)->format('d-m-Y') }}</td>
                @endisset
            </tr>

        @endif
        @isset($document->quotation->sale_opportunity)
            <tr>
                <td width="120px">O. VENTA</td>
                <td width="8px">:</td>
                <td>{{ $document->quotation->sale_opportunity->number_full}}</td>
            </tr>
        @endisset
        @if(!is_null($document_base))
        <tr>
            <td width="120px">DOC. AFECTADO</td>
            <td width="8px">:</td>
            <td>{{ $affected_document_number }}</td>
        </tr>
        <tr>
            <td>TIPO DE NOTA</td>
            <td>:</td>
            <td>{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>
        </tr>
        <tr>
            <td>DESCRIPCIÓN</td>
            <td>:</td>
            <td>{{ $document_base->note_description }}</td>
        </tr>
        @endif
    </table>

    {{--<table class="full-width mt-3">--}}
        {{--<tr>--}}
            {{--<td width="25%">Documento Afectado:</td>--}}
            {{--<td width="20%">{{ $document_base->affected_document->series }}-{{ $document_base->affected_document->number }}</td>--}}
            {{--<td width="15%">Tipo de nota:</td>--}}
            {{--<td width="40%">{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>--}}
        {{--</tr>--}}
        {{--<tr>--}}
            {{--<td class="align-top">Descripción:</td>--}}
            {{--<td class="text-left" colspan="3">{{ $document_base->note_description }}</td>--}}
        {{--</tr>--}}
    {{--</table>--}}

    <table class="full-width mt-10 mb-10">
        <thead class="">
        <tr class="bg-grey">
            <th class="border-top-bottom text-center py-2" width="8%">CANT.</th>
            <th class="border-top-bottom text-center py-2" width="8%">UNIDAD</th>
            <th class="border-top-bottom text-left py-2">DESCRIPCIÓN</th>
            <th class="border-top-bottom text-left py-2">MODELO</th>
            <th class="border-top-bottom text-center py-2" width="8%">LOTE</th>
            <th class="border-top-bottom text-center py-2" width="8%">SERIE</th>
            <th class="border-top-bottom text-right py-2" width="12%">P.UNIT</th>
            <th class="border-top-bottom text-right py-2" width="8%">DTO.</th>
            <th class="border-top-bottom text-right py-2" width="12%">TOTAL</th>
        </tr>
        </thead>
        <tbody>
        @foreach($document->items as $row)
            <tr>
                <td class="text-center align-top">
                    @if(((int)$row->quantity != $row->quantity))
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif
                </td>
                <td class="text-center align-top">{{ $row->item->unit_type_id }}</td>
                <td class="text-left align-top">
                    @if($row->name_product_pdf)
                        {!!$row->name_product_pdf!!}
                    @else
                        {!!$row->item->description!!}
                    @endif

                    @if($row->total_isc > 0)
                        <br/><span style="font-size: 9px">ISC : {{ $row->total_isc }} ({{ $row->percentage_isc }}%)</span>
                    @endif

                    @if (!empty($row->item->presentation)) {!!$row->item->presentation->description!!} @endif

                    @if($row->total_plastic_bag_taxes > 0)
                        <br/><span style="font-size: 9px">ICBPER : {{ $row->total_plastic_bag_taxes }}</span>
                    @endif

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

                    @if($row->charges)
                        @foreach($row->charges as $charge)
                            <br/><span style="font-size: 9px">{{ $document->currency_type->symbol}} {{ $charge->amount}} ({{ $charge->factor * 100 }}%) {{$charge->description }}</span>
                        @endforeach
                    @endif

                    @if($row->item->is_set == 1)
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
                <td class="text-left align-top">{{ $row->item->model ?? '' }}</td>
                <td class="text-center align-top">
                    @inject('itemLotGroup', 'App\Services\ItemLotsGroupService')
                    {{ $itemLotGroup->getLote($row->item->IdLoteSelected) }}

                </td>
                <td class="text-center align-top">

                    @isset($row->item->lots)
                        @foreach($row->item->lots as $lot)
                            @if( isset($lot->has_sale) && $lot->has_sale)
                                <span style="font-size: 9px">{{ $lot->series }}</span><br>
                            @endif
                        @endforeach
                    @endisset

                </td>

                @if ($configuration_decimal_quantity->change_decimal_quantity_unit_price_pdf)
                    <td class="text-right align-top">{{ $row->generalApplyNumberFormat($row->unit_price, $configuration_decimal_quantity->decimal_quantity_unit_price_pdf) }}</td>
                @else
                    <td class="text-right align-top">{{ number_format($row->unit_price, 2) }}</td>
                @endif
                
                <td class="text-right align-top">
                    @if($row->discounts)
                        @php
                            $total_discount_line = 0;
                            foreach ($row->discounts as $disto) {
                                $total_discount_line = $total_discount_line + $disto->amount;
                            }
                        @endphp
                        {{ number_format($total_discount_line, 2) }}
                    @else
                    0
                    @endif
                </td>
                <td class="text-right align-top">{{ number_format($row->total, 2) }}</td>
            </tr>
            <tr>
                <td colspan="9" class="border-bottom"></td>
            </tr>
        @endforeach



        @if ($document->prepayments)
            @foreach($document->prepayments as $p)
            <tr>
                <td class="text-center align-top">1</td>
                <td class="text-center align-top">NIU</td>
                <td class="text-left align-top">
                    ANTICIPO: {{($p->document_type_id == '02')? 'FACTURA':'BOLETA'}} NRO. {{$p->number}}
                </td>
                <td class="text-center align-top"></td>
                <td class="text-center align-top"></td>
                <td class="text-center align-top"></td>
                <td class="text-right align-top">-{{ number_format($p->total, 2) }}</td>
                <td class="text-right align-top">0</td>
                <td class="text-right align-top">-{{ number_format($p->total, 2) }}</td>
            </tr>
            <tr>
                <td colspan="9" class="border-bottom"></td>
            </tr>
            @endforeach
        @endif

            @if($document->total_exportation > 0)
                <tr>
                    <td colspan="8" class="text-right font-bold">OP. EXPORTACIÓN:</td>
                    <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format($document->total_exportation, 2) }}</td>
                </tr>
            @endif
            @if($document->total_free > 0)
                <tr>
                    <td colspan="8" class="text-right font-bold">OP. GRATUITAS:</td>
                    <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format($document->total_free, 2) }}</td>
                </tr>
            @endif
            @if($document->total_unaffected > 0)
                <tr>
                    <td colspan="8" class="text-right font-bold">OP. INAFECTAS:</td>
                    <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format($document->total_unaffected, 2) }}</td>
                </tr>
            @endif
            @if($document->total_exonerated > 0)
                <tr>
                    <td colspan="8" class="text-right font-bold">OP. EXONERADAS:</td>
                    <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format($document->total_exonerated, 2) }}</td>
                </tr>
            @endif

            @if ($document->document_type_id === '07')
                @if($document->total_taxed >= 0)
                <tr>
                    <td colspan="8" class="text-right font-bold">OP. GRAVADAS:</td>
                    <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format($document->total_taxed, 2) }}</td>
                </tr>
                @endif
            @elseif($document->total_taxed > 0)
                <tr>
                    <td colspan="8" class="text-right font-bold">BASE IMPONIBLE:</td>
                    <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format($document->total_taxed, 2) }}</td>
                </tr>
            @endif

            @if($document->total_plastic_bag_taxes > 0)
                <tr>
                    <td colspan="8" class="text-right font-bold">ICBPER:</td>
                    <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="8" class="text-right font-bold">IVA:</td>
                <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format($document->total_igv, 2) }}</td>
            </tr>

            @if($document->total_isc > 0)
            <tr>
                <td colspan="8" class="text-right font-bold">ISC:</td>
                <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format($document->total_isc, 2) }}</td>
            </tr>
            @endif

            @if($document->total_discount > 0 && $document->subtotal > 0)
            <tr>
                <td colspan="8" class="text-right font-bold">SUBTOTAL:</td>
                <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format($document->subtotal, 2) }}</td>
            </tr>
            @endif

            @if($document->total_discount > 0)
                <tr>
                    <td colspan="8" class="text-right font-bold">{{(($document->total_prepayment > 0) ? 'ANTICIPO':'DESCUENTO TOTAL')}}:</td>
                    <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format($document->total_discount, 2) }}</td>
                </tr>
            @endif

            @if($document->total_charge > 0)
                @if($document->charges)
                    @php
                        $total_factor = 0;
                        foreach($document->charges as $charge) {
                            $total_factor = ($total_factor + $charge->factor) * 100;
                        }
                    @endphp
                    <tr>
                        <td colspan="8" class="text-right font-bold">CARGOS ({{$total_factor}}%):</td>
                        <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format($document->total_charge, 2) }}</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="8" class="text-right font-bold">CARGOS:</td>
                        <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format($document->total_charge, 2) }}</td>
                    </tr>
                @endif
            @endif

            @if($document->perception)
                <tr>
                    <td colspan="8" class="text-right font-bold"> IMPORTE TOTAL:</td>
                    <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format($document->total, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="8" class="text-right font-bold">PERCEPCIÓN:</td>
                    <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format($document->perception->amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="8" class="text-right font-bold">TOTAL:</td>
                    <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format(($document->total + $document->perception->amount), 2) }}</td>
                </tr>
            @else
                <tr>
                    <td colspan="8" class="text-right font-bold">TOTAL:</td>
                    <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format($document->total, 2) }}</td>
                </tr>
            @endif

            @if(($document->retention || $document->detraction) && $document->total_pending_payment > 0)
                <tr>
                    <td colspan="8" class="text-right font-bold">M. PENDIENTE:</td>
                    <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format($document->total_pending_payment, 2) }}</td>
                </tr>
            @endif

            @if($balance < 0)

                <tr>
                    <td colspan="8" class="text-right font-bold">VUELTO:</td>
                    <td class="text-right font-bold">{{ $document->currency_type->symbol }}{{ number_format(abs($balance),2, ".", "") }}</td>
                </tr>

            @endif



        </tbody>
    </table>
    <table class="full-width">
        <tr>
            <td width="65%" style="text-align: top; vertical-align: top;">
                @foreach(array_reverse( (array) $document->legends) as $row)
                    @if ($row->code == "1000")
                        <p style="text-transform: uppercase;">Son: <span class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></p>
                        @if (count((array) $document->legends)>1)
                            <p><span class="font-bold">Leyendas</span></p>
                        @endif
                    @else
                        <p> {{$row->code}}: {{ $row->value }} </p>
                    @endif

                @endforeach
                <br/>
                @if ($document->detraction)
                <p>
                    <span class="font-bold">
                    Operación sujeta al Sistema de Pago de Obligaciones Tributarias
                    </span>
                </p>
                <br/>
                @endif
                @if ($customer->department_id == 16)
                    <br/><br/><br/>
                    <div>
                        <center>
                            Representación impresa del Comprobante de Pago Electrónico.
                            <br/>Esta puede ser consultada en:
                            <br/><b>{!! url('/buscar') !!}</b>
                            <br/> "Bienes transferidos en la Amazonía
                            <br/>para ser consumidos en la misma".
                        </center>
                    </div>
                    <br/>
                @endif
                @foreach($document->additional_information as $information)
                    @if ($information)
                        @if ($loop->first)
                            <strong>Información adicional</strong>
                        @endif
                        <p>@if(\App\CoreFacturalo\Helpers\Template\TemplateHelper::canShowNewLineOnObservation())
                                {!! \App\CoreFacturalo\Helpers\Template\TemplateHelper::SetHtmlTag($information) !!}
                            @else
                                {{$information}}
                            @endif</p>
                    @endif
                @endforeach
                <br>
                @if(in_array($document->document_type->id,['01','03']))
                    @foreach($accounts as $account)
                        <p>
                        <span class="font-bold">{{$account->bank->description}}</span> {{$account->currency_type->description}}
                        <span class="font-bold">N°:</span> {{$account->number}}
                        @if($account->cci)
                        <span class="font-bold">CCI:</span> {{$account->cci}}
                        @endif
                        </p>
                    @endforeach
                @endif
            </td>
            <td width="35%" class="text-right">
                <img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -10px;" />
                <p style="font-size: 9px">Código Hash: {{ $document->hash }}</p>
            </td>
        </tr>
    </table>
    @php
        $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);
    @endphp
    {{-- Condicion de pago  Crédito / Contado --}}
    <table class="full-width">
        <tr>
            <td>
                <strong>CONDICIÓN DE PAGO: {{ $paymentCondition }} </strong>
            </td>
        </tr>
    </table>

    @if($document->payment_method_type_id)
        <table class="full-width">
            <tr>
                <td>
                    <strong>MÉTODO DE PAGO: </strong>{{ $document->payment_method_type->description }}
                </td>
            </tr>
        </table>
    @endif

    @if ($document->payment_condition_id === '01')
        @if($payments->count())
            <table class="full-width">
                <tr>
                    <td><strong>PAGOS:</strong></td>
                </tr>
                    @php $payment = 0; @endphp
                    @foreach($payments as $row)
                        <tr>
                            <td>&#8226; {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td>
                        </tr>
                    @endforeach
                </tr>
            </table>
        @endif
    @else
        <table class="full-width">
                @foreach($document->fee as $key => $quote)
                    <tr>
                        <td>&#8226; {{ (empty($quote->getStringPaymentMethodType()) ? 'Cuota #'.( $key + 1) : $quote->getStringPaymentMethodType()) }} / Fecha: {{ $quote->date->format('d-m-Y') }} / Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}</td>
                    </tr>
                @endforeach
            </tr>
        </table>
    @endif

    <br>
    <table class="full-width">
        <tr>
            <td>
                <strong>Vendedor:</strong>
            </td>
        </tr>
        <tr>
            @if ($document->seller)
                <td>{{ $document->seller->name }}</td>
            @else
                <td>{{ $document->user->name }}</td>
            @endif
        </tr>
    </table>

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
    -->

    <!-- Separeted code2 -->
    <!-- Code from JOINSOFTWARE -->

    <table class="full-width">
        <tbody>
            <tr>
                <td width="50%">
                @if($company->logo)
                    
                        <div class="company_logo_box">
                            <img src="data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$company->name}}" class="company_logo" style="margin-left: 50px; padding-bottom: 40px; max-width: 150px" >
                        </div>
                   
                @else
                {{--<img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px">--}}
                        <!--
                        {{--<img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px" width="100px" height="100px" style="margin-left: 50px">--}}
                        <img alt="Bootstrap Image Preview" src="https://www.pngitem.com/pimgs/m/47-479827_join-now-png-transparent-images-join-logo-png.png" width="100px" height="100px" style="margin-left: 50px"/>
                        -->
                @endif
                        
                    <!--<img alt="Bootstrap Image Preview" src="https://www.pngitem.com/pimgs/m/47-479827_join-now-png-transparent-images-join-logo-png.png" width="100px" height="100px" style="margin-left: 50px"/>-->
                    <table>
                        <tbody>
                            <tr>
                                <td style="text-transform: uppercase; background: #eaeaea; padding-left: 15px; padding-right: 15px; padding-bottom: 60px; padding-top: 15px;">
                                    <strong>Emisor: </strong>{{ $company->name }}<br></br>
                                    <strong>RUC: </strong>{{ $company->number }}<br></br>
                                    <strong>Matriz: </strong> <h7 style="text-transform: uppercase;">{{ ($establishment->address !== '-')? $establishment->address : ', ' }}<!-- JOINSOFTWARE {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}-->{{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}{{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}</h7><br></br>
                                    <strong>Correo: </strong>{{ ($establishment->email !== '-')? ''.$establishment->email : '' }}<br></br>
                                    <strong>Teléfono: </strong>{{ ($establishment->telephone !== '-')? ''.$establishment->telephone : '' }}<br></br>
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
                                    <pre style="tab-size: 16; font-size: 14px"><strong>NOTA DE CRÉDITO     </strong>     No.{{$document_number}}</pre>
                                </td>
                            </tr>
                            <tr>
                                <td style="background: #eaeaea; padding-top: 20px; padding-left: 15px; padding-right: 15px;">
                                    <strong>Número de Autorización:</strong>
                                    <br></br>
                                    <h6 style="font-size: 13px;">{{$document->clave_SRI}}</h6>
                                    <br></br>
                                    <br></br>
                                    <strong>Fecha y hora de Autorización:</strong>
                                    <br></br>
                                    {{$document->date_authorization}} {{ $document->time_authorization}}
                                    <br></br>
                                    <br></br>
                                    @if($company->soap_type_id === '01')
                                    <strong>Ambiente: </strong>PRUEBAS
                                    <br></br>
                                    @endif
                                    @if($company->soap_type_id === '03')
                                    <strong>Ambiente: </strong>INTERNO
                                    <br></br>
                                    @endif
                                    @if($company->soap_type_id === '02')
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
    <div style=" background: #eaeaea; padding-bottom: 20px; padding-left: 15px; padding-right: 15px;">
        <table class="full-width">
            <tbody>
                <tr>
                    <td style="text-transform: uppercase;">
                        <div>
                            <strong>Razón Social: </strong>{{ $customer->name }}<br></br>
                            <strong>Dirección: </strong> {{ $customer->address }}<br></br>
                            <strong>Fecha Emisión: </strong> {{$document->date_of_issue->format('Y-m-d')}}
                        </div>
                    </td>
                    <td style="text-transform: uppercase;">
                        <div>
                            <br></br>
                            <strong>RUC/CI: </strong> {{ $customer->number }}<br></br><br></br>
                            <strong>Teléfono: </strong> {{ $customer->telephone }}<br></br>
                            <strong>Correo: </strong> {{ $customer->email }}
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <hr style="width: 97%; border-top: 1px solid #eaeaea;">
        <table class="full-width">
            <tbody>
                @if(!is_null($document_base))
                <tr>
                    <td style="text-transform: uppercase;">
                        <strong>Comprobante que se modifica: </strong>                        
                    </td>
                    <td>
                        <pre>FACTURA&nbsp;&nbsp;    {{ $affected_document_number }}</pre>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>FECHA EMISIÓN (comprobante a modificar): </strong>
                    </td>
                    <td>
                        {{ $document_base->affected_document->date_of_issue->format('m-d-Y') }}
                    </td>
                </tr>
                <!-- JOINSOFTWARE
                <tr>
                    <td>
                        <strong>TIPO DE NOTA: </strong>
                    </td>
                    <td>
                        {{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}
                    </td>
                </tr>
                -->
                <tr>
                    <td style="text-transform: uppercase;">
                        <strong>Razón de Modificación: </strong>
                    </td>
                    <td>
                        {{ $document_base->note_description }}
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    <div>
        <table class="full-width mt-10 mb-10">
            <thead>
            <tr style="background: #eaeaea;">
                <th class="text-center py-2 pl-4" width="10%">CANT.</th>
                <th class="text-center py-2" width="8%">UNIDAD</th>
                <th class="text-left py-2">DESCRIPCIÓN</th>
                <th class="text-left py-2">MODELO/REF</th>
                <th class="text-center py-2" width="8%">LOTE</th>
                <th class="text-center py-2" width="8%">SERIE</th>
                <th class="text-right py-2" width="12%">P.UNIT</th>
                <th class="text-right py-2" width="8%">DTO.</th>
                <th class="text-right py-2 pr-4" width="12%">TOTAL</th>
            </tr>
            </thead>
            <tbody>
            @foreach($document->items as $row)
                <tr style="background: #f7f7f5;">
                    <td class="text-left align-top pl-4">
                        @if(((int)$row->quantity != $row->quantity))
                            {{ $row->quantity }}
                        @else
                            {{ number_format($row->quantity, 0) }}
                        @endif
                    </td>
                    <td class="text-center align-top">{{ $row->item->unit_type_id }}</td>
                    <td class="text-left align-top">
                        @if($row->name_product_pdf)
                            {!!$row->name_product_pdf!!}
                        @else
                            {!!$row->item->description!!}
                        @endif

                        @if($row->total_isc > 0)
                            <br/><span style="font-size: 9px">ISC : {{ $row->total_isc }} ({{ $row->percentage_isc }}%)</span>
                        @endif

                        @if (!empty($row->item->presentation)) {!!$row->item->presentation->description!!} @endif

                        @if($row->total_plastic_bag_taxes > 0)
                            <br/><span style="font-size: 9px">ICBPER : {{ $row->total_plastic_bag_taxes }}</span>
                        @endif

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

                        @if($row->charges)
                            @foreach($row->charges as $charge)
                                <br/><span style="font-size: 9px">{{ $document->currency_type->symbol}} {{ $charge->amount}} ({{ $charge->factor * 100 }}%) {{$charge->description }}</span>
                            @endforeach
                        @endif

                        @if($row->item->is_set == 1)
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
                    <td class="text-left align-top">{{ $row->item->model ?? '' }}</td>
                    <td class="text-center align-top">
                        @inject('itemLotGroup', 'App\Services\ItemLotsGroupService')
                        {{ $itemLotGroup->getLote($row->item->IdLoteSelected) }}

                    </td>
                    <td class="text-center align-top">

                        @isset($row->item->lots)
                            @foreach($row->item->lots as $lot)
                                @if( isset($lot->has_sale) && $lot->has_sale)
                                    <span style="font-size: 9px">{{ $lot->series }}</span><br>
                                @endif
                            @endforeach
                        @endisset

                    </td>
                    <!-- JOINSOFTWARE -->
                    <td class="text-right align-top">{{ number_format($row->unit_value, 2) }}</td>
                    <!-- JOINSOFTWARE
                    @if ($configuration_decimal_quantity->change_decimal_quantity_unit_price_pdf)
                        <td class="text-right align-top">{{ $row->generalApplyNumberFormat($row->unit_price, $configuration_decimal_quantity->decimal_quantity_unit_price_pdf) }}</td>
                    @else
                        <td class="text-right align-top">{{ number_format($row->unit_price, 2) }}</td>
                    @endif
                    -->
                    <td class="text-right align-top">
                        @if($row->discounts)
                            @php
                                $total_discount_line = 0;
                                foreach ($row->discounts as $disto) {
                                    $total_discount_line = $total_discount_line + $disto->amount;
                                }
                            @endphp
                            {{ number_format($total_discount_line, 2) }}
                        @else
                        0
                        @endif
                    </td>
                    <td class="text-right align-top pr-4">{{ number_format($row->total_value, 2) }}</td>
                </tr>
                <tr style="background: #f7f7f5;">
                    <td colspan="9"></td>
                </tr>
            @endforeach



            @if ($document->prepayments)
                @foreach($document->prepayments as $p)
                <tr style="background: #f7f7f5;">
                    <td class="text-center align-top">1</td>
                    <td class="text-center align-top">NIU</td>
                    <td class="text-left align-top">
                        ANTICIPO: {{($p->document_type_id == '02')? 'FACTURA':'BOLETA'}} NRO. {{$p->number}}
                    </td>
                    <td class="text-center align-top"></td>
                    <td class="text-center align-top"></td>
                    <td class="text-center align-top"></td>
                    <td class="text-right align-top">-{{ number_format($p->total, 2) }}</td>
                    <td class="text-right align-top">0</td>
                    <td class="text-right align-top">-{{ number_format($p->total, 2) }}</td>
                </tr>
                <tr style="background: #f7f7f5;">
                    <td colspan="9"></td>
                </tr>
                @endforeach
            @endif

                <!-- Reordenate this
                @if($document->total_exportation > 0)
                    <tr>
                        <td colspan="8" class="text-right font-bold">OP. EXPORTACIÓN: {{ $document->currency_type->symbol }}</td>
                        <td class="text-right font-bold pr-4">{{ number_format($document->total_exportation, 2) }}</td>
                    </tr>
                @endif
                @if($document->total_free > 0)
                    <tr>
                        <td colspan="8" class="text-right font-bold">OP. GRATUITAS: {{ $document->currency_type->symbol }}</td>
                        <td class="text-right font-bold pr-4">{{ number_format($document->total_free, 2) }}</td>
                    </tr>
                @endif
                @if($document->total_unaffected > 0)
                    <tr>
                        <td colspan="8" class="text-right font-bold">OP. INAFECTAS: {{ $document->currency_type->symbol }}</td>
                        <td class="text-right font-bold pr-4">{{ number_format($document->total_unaffected, 2) }}</td>
                    </tr>
                @endif
                @if($document->total_exonerated > 0)
                    <tr>
                        <td colspan="8" class="text-right font-bold">OP. EXONERADAS: {{ $document->currency_type->symbol }}</td>
                        <td class="text-right font-bold pr-4">{{ number_format($document->total_exonerated, 2) }}</td>
                    </tr>
                @endif
                -->

                <!-- Reordenate this
                @if($document->total_plastic_bag_taxes > 0)
                    <tr>
                        <td colspan="8" class="text-right font-bold">ICBPER: {{ $document->currency_type->symbol }}</td>
                        <td class="text-right font-bold pr-4">{{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
                    </tr>
                @endif
                -->

                <!-- Reordenate this
                @if($document->total_isc > 0)
                <tr>
                    <td colspan="8" class="text-right font-bold">ISC: {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold pr-4">{{ number_format($document->total_isc, 2) }}</td>
                </tr>
                @endif

                @if($document->total_discount > 0 && $document->subtotal > 0)
                <tr>
                    <td colspan="8" class="text-right font-bold">SUBTOTAL: {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold pr-4">{{ number_format($document->subtotal, 2) }}</td>
                </tr>
                @endif

                @if($document->total_discount > 0)
                    <tr>
                        <td colspan="8" class="text-right font-bold">{{(($document->total_prepayment > 0) ? 'ANTICIPO':'DESCUENTO TOTAL')}}: {{ $document->currency_type->symbol }}</td>
                        <td class="text-right font-bold pr-4">{{ number_format($document->total_discount, 2) }}</td>
                    </tr>
                @endif

                @if($document->total_charge > 0)
                    @if($document->charges)
                        @php
                            $total_factor = 0;
                            foreach($document->charges as $charge) {
                                $total_factor = ($total_factor + $charge->factor) * 100;
                            }
                        @endphp
                        <tr>
                            <td colspan="8" class="text-right font-bold">CARGOS ({{$total_factor}}%): {{ $document->currency_type->symbol }}</td>
                            <td class="text-right font-bold pr-4">{{ number_format($document->total_charge, 2) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="8" class="text-right font-bold">CARGOS: {{ $document->currency_type->symbol }}</td>
                            <td class="text-right font-bold pr-4">{{ number_format($document->total_charge, 2) }}</td>
                        </tr>
                    @endif
                @endif
                -->

                <!-- Reordenate this
                @if(($document->retention || $document->detraction) && $document->total_pending_payment > 0)
                    <tr>
                        <td colspan="8" class="text-right font-bold">M. PENDIENTE: {{ $document->currency_type->symbol }}</td>
                        <td class="text-right font-bold pr-4">{{ number_format($document->total_pending_payment, 2) }}</td>
                    </tr>
                @endif

                @if($balance < 0)

                    <tr>
                        <td colspan="8" class="text-right font-bold">VUELTO: {{ $document->currency_type->symbol }}</td>
                        <td class="text-right font-bold pr-4">{{ number_format(abs($balance),2, ".", "") }}</td>
                    </tr>

                @endif
                -->
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
                                <tr style="background: #f7f7f5;">
                                    @if($document->additional_information[0])
                                    <td style="text-align: start; padding-left: 15px; padding-right: 15px;">{{ $document->additional_information[0]}}</td>
                                    @endif
                                </tr>
                            </tbody>
                        </table>
                        <!--
                        <table class="full-width">
                            
                            <thead class="">
                                <tr style="background: #eaeaea;">
                                    <th class="py-2" style="text-align: start; padding-left: 15px; padding-right: 15px;">Formas de pago</th>
                                </tr>
                            </thead>
                            
                            <tbody>
                                <tr style="background: #f7f7f5;">
                                    <td style="text-align: start; padding-left: 15px; padding-right: 15px;">Otros con Utilización del Sistema Financiero</td>
                                    <td style="text-align: start; padding-left: 15px; padding-right: 15px;">{{ $document->currency_type->symbol }}{{ number_format($document->total, 2) }}</td>
                                    <td style="text-align: start; padding-left: 15px; padding-right: 15px;">0 días</td>
                                </tr>
                            </tbody>
                        </table>
                        -->
                    </div>
                </td>
                <td width="40%">
                    <table class="full-width" style="border-spacing: 0px 5px; border-collapse: separate;">
                        <tr>
                            <td style="padding-left: 15px; padding-right: 15px; background: #f7f7f5;">Subtotal 0%:</td>
                            <td class="text-right" style="padding-left: 15px; padding-right: 15px; background: #eaeaea;">{{ $document->currency_type->symbol }}{{ number_format($total0, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 15px; padding-right: 15px; background: #f7f7f5;">Subtotal 12%:</td>
                            <td class="text-right" style="padding-left: 15px; padding-right: 15px; background: #eaeaea;">{{ $document->currency_type->symbol }}{{ number_format($total12, 2) }}</td>
                        </tr>
                        <!-- JOINSOFTWARE
                        <tr>
                            <td style="padding-left: 15px; padding-right: 15px; background: #f7f7f5;">Subtotal 14%:</td>
                            <td class="text-right" style="padding-left: 15px; padding-right: 15px; background: #eaeaea;">{{ $document->currency_type->symbol }}{{ number_format($total14, 2) }}</td>
                        </tr>
                        -->
                        @if ($document->document_type_id === '07')
                            @if($document->total_taxed >= 0)
                            <tr>
                                <td style="padding-left: 15px; padding-right: 15px; background: #f7f7f5;">Subtotal Sin Impuestos:</td>
                                <td class="text-right" style="padding-left: 15px; padding-right: 15px; background: #eaeaea;">{{ $document->currency_type->symbol }}{{ number_format($total0+$total12+$total14+$total8, 2) }}</td>
                            </tr>
                            @endif
                        @endif
                        <tr>
                            <td style="padding-left: 15px; padding-right: 15px; background: #f7f7f5;">Descuentos:</td>
                            <td class="text-right" style="padding-left: 15px; padding-right: 15px; background: #eaeaea;">{{ $document->currency_type->symbol }}{{ number_format($document->total_discount, 2) }}</td>
                        </tr>
                        <!-- JOINSOFTWARE -->
                        <tr>
                            <td style="padding-left: 15px; padding-right: 15px; background: #f7f7f5;">IVA 0%:</td>
                            <td class="text-right" style="padding-left: 15px; padding-right: 15px; background: #eaeaea;">{{ $document->currency_type->symbol }}0.00</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 15px; padding-right: 15px; background: #f7f7f5;">IVA 12%:</td>
                            <td class="text-right" style="padding-left: 15px; padding-right: 15px; background: #eaeaea;">{{ $document->currency_type->symbol }}{{ number_format($totalIVA12, 2) }}</td>
                        </tr>
                        <!-- JOINSOFTWARE
                        <tr>
                            <td style="padding-left: 15px; padding-right: 15px; background: #f7f7f5;">IVA 14%:</td>
                            <td class="text-right" style="padding-left: 15px; padding-right: 15px; background: #eaeaea;">{{ $document->currency_type->symbol }}{{ number_format($totalIVA14, 2) }}</td>
                        </tr>
                        -->
                        <tr>
                            <td style="padding-left: 15px; padding-right: 15px; background: #f7f7f5;">Servicio %:</td>
                            <td class="text-right" style="padding-left: 15px; padding-right: 15px; background: #eaeaea;">{{ $document->currency_type->symbol }}0.00</td>
                        </tr>
                        @if($document->perception)
                        <tr>
                            <td style="padding-left: 15px; padding-right: 15px; background: #f7f7f5;">IMPORTE TOTAL:</td>
                            <td class="text-right" style="padding-left: 15px; padding-right: 15px; background: #eaeaea;">{{ $document->currency_type->symbol }}{{ number_format($document->total, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 15px; padding-right: 15px; background: #f7f7f5;">PERCEPCIÓN:</td>
                            <td class="text-right" style="padding-left: 15px; padding-right: 15px; background: #eaeaea;">{{ $document->currency_type->symbol }}{{ number_format($document->perception->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 15px; padding-right: 15px; background: #f7f7f5;">TOTAL:</td>
                            <td class="text-right" style="padding-left: 15px; padding-right: 15px; background: #eaeaea;">{{ $document->currency_type->symbol }}{{ number_format(($document->total + $document->perception->amount), 2) }}</td>
                        </tr>
                        @else
                        <tr>
                            <td style="padding-left: 15px; padding-right: 15px; background: #f7f7f5;">Valor Total:</td>
                            <td class="text-right" style="padding-left: 15px; padding-right: 15px; background: #eaeaea;">{{ $document->currency_type->symbol }}{{ number_format($document->total, 2) }}</td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <!--
    <div style="background: #eaeaea; padding: 5px 15px 20px 15px" >
        <h5 style="text-align: center; margin-top: 0px; margin-bottom: 0px"><strong>Factura de Join Software S.A.</strong></h5>
    </div>
    -->
</body>
</html>
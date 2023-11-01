@php
    $establishment = $document->establishment;
    $payments = $document->payments;
    $left =  ($document->series) ? $document->series : $document->prefix;
    $tittle = $left.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();
    $total_payment = $document->payments->sum('payment');
    $balance = ($document->total - $total_payment) - $document->payments->sum('change');
    //dd($tittle_unpaid);
    $data = $payments;
    $valores = null;
    for($i = 0 ; $i <= $index ; $i++)
    {
        $valores += $data[$i]->payment;
    }
    $referencia = '';
    if($data[$index]->payment_method_type_id == '99'){
        $referencia = 'R'.$data[$index]->retentions->ubl_version;
    }
    if($data[$index]->payment_method_type_id == '15' || $data[$index]->payment_method_type_id == '14'){
        $referencia = 'AT'.$data[$index]->advances->id;
    }

    $num_comprobante = str_pad(($index + 1), 8, '0', STR_PAD_LEFT);
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
                    <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="max-width: 150px;">
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
            <h5 class="text-center">CUENTAS POR COBRAR</h5>
            <h3 class="text-center">{{ $num_comprobante }}</h3>
        </td>
    </tr>
</table>
<table class="full-width mt-5">
    <tr>
        <td width="15%">Cliente:</td>
        <td width="45%">{{ $document->customer->name }}</td>
        <td width="25%">Fecha de emisión:</td>
        {{-- <td width="15%">{{ $document->date_of_issue->format('Y-m-d') }}</td> --}}
        @if($payments->count())

                @php
                    $payment = 0;
                @endphp
                <td width="15%">{{ $data[$index]->date_of_payment->format('Y-m-d') }}</td>
                @foreach($payments as $row)

                    @php
                        $payment += (float) $row->payment;
                    @endphp
                @endforeach
        @endif
    </tr>
    <tr>
        <td width="30%">Documento por cobrar:</td>
        <td width="45%"> {{ $tittle }}</td>
    </tr>
</table>


<table class="full-width">
    <thead class="">
    <tr class="bg-grey">
        <th class="border-top-bottom text-center py-2" width="8%">CANT.</th>
        <th class="border-top-bottom text-center py-2" width="8%">UNIDAD</th>
        <th class="border-top-bottom text-left py-2">DESCRIPCIÓN</th>
        <th class="border-top-bottom text-right py-2" width="12%">TOTAL</th>
        <th class="border-top-bottom text-right py-2" width="12%">PENDIENTE PAGAR</th>
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
            <td class="text-right align-top">{{ number_format($row->total, 2) }}</td>
            <td class="text-right align-top">{{ $document->currency_type->symbol }} {{ number_format( $row->total - $valores, 2) }}</td>
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
    </tbody>
</table>


@if($document->payment_method_type_id && $payments->count() == 0)
    <table class="full-width">
        <tr>
            <td>
                <strong>PAGO: </strong>{{ $document->payment_method_type->description }}
            </td>
        </tr>
    </table>
@endif

@if($payments->count())

<table class="full-width">
    <tr>
        <td>
            <strong>
                PAGOS:
            </strong>
        </td>
    </tr>
    <tr>
        <td>-{{ $data[$index]->date_of_payment->format('d/m/Y') }} - {{ $data[$index]->payment_method_type->description }} - {{ $referencia != '' ? $referencia:$data[$index]->reference }} - {{ $document->currency_type->symbol }}{{$data[$index]->payment}}</td>
    </tr>
    @php
        $payment = 0;
    @endphp
    @foreach($payments as $row)
        @php
            $payment += (float) $row->payment;
        @endphp
    @endforeach
    </tr>
</table>
@endif


</body>
</html>

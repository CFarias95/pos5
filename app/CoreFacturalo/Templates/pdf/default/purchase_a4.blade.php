@php
    $establishment = $document->establishment;
    $supplier = $document->supplier;
    $payments = $document->payments;
    $tittle = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
@endphp
<html>
<head>
    {{--<title>{{ $tittle }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
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
            <h5 class="text-center">{{ $document->document_type->description}}</h5>
            <h3 class="text-center">{{ $tittle }}</h3>
        </td>
    </tr>
</table>
<table class="full-width mt-5">
    <tr>
        <td width="15%">Proveedor:</td>
        <td width="45%">{{ $supplier->name }}</td>
        <td width="25%">Fecha de emisión:</td>
        <td width="15%">{{ $document->date_of_issue->format('Y-m-d') }}</td>
    </tr>
    <tr>
        <td>{{ $supplier->identity_document_type->description }}:</td>
        <td>{{ $supplier->number }}</td>
        @if($document->date_of_due)
            <td width="25%">Fecha de vencimiento:</td>
            <td width="15%">{{ $document->date_of_due->format('Y-m-d') }}</td>
        @endif
    </tr>
    @if ($supplier->address !== '')
    <tr>
        <td class="align-top">Dirección:</td>
        <td colspan="3">
            {{ $supplier->address }}
            {{ ($supplier->district_id !== '-')? ', '.$supplier->district->description : '' }}
            {{ ($supplier->province_id !== '-')? ', '.$supplier->province->description : '' }}
            {{ ($supplier->department_id !== '-')? '- '.$supplier->department->description : '' }}
        </td>
    </tr>
    @endif
    @if ($supplier->telephone)
    <tr>
        <td class="align-top">Teléfono:</td>
        <td colspan="3">
            {{ $supplier->telephone }}
        </td>
    </tr>
    @endif
    <tr>
        <td class="align-top">Usuario:</td>
        <td colspan="3">
            {{ $document->user->name }}
        </td>
    </tr>
    @if($document->purchase_order)
    <tr>
        <td class="align-top">O. Compra:</td>
        <td  colspan="3">{{ $document->purchase_order->number_full }}</td>
    </tr>
    @endif
    @if ($document->observation)
    <tr>
        <td class="align-top">Observación: </td>
        <td colspan="3">{{ $document->observation }}</td>
    </tr>
    @endif
    <tr>
        <td width="15%">Sequencial:</td>
        <td width="45%">{{ $document->sequential_number }}</td>
        <td width="25%">Código Interno</td>
        <td width="15%">{{ $document->document_type_intern }}</td>
    </tr>
</table>


<table class="full-width mt-10 mb-10">
    <thead class="">
    <tr class="bg-grey">
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
            <td class="text-center align-top">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-center align-top">{{ $row->item->unit_type_id }}</td>
            <td class="text-left">
                {!!$row->item->description!!} @if (!empty($row->item->presentation)) {!!$row->item->presentation->description!!} @endif

                @if($row->total_isc > 0)
                    <br/><span style="font-size: 9px">ISC : {{ $row->total_isc }} ({{ $row->percentage_isc }}%)</span>
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
            </td>
            <td class="text-right align-top">{{ number_format($row->unit_value, 2) }}</td>
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
            <td colspan="6" class="border-bottom"></td>
        </tr>
    @endforeach
        @if($document->total_exportation > 0)
            <tr>
                <td colspan="5" class="text-right font-bold">OP. EXPORTACIÓN: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_exportation, 2) }}</td>
            </tr>
        @endif
        @if($document->total_free > 0)
            <tr>
                <td colspan="5" class="text-right font-bold">OP. GRATUITAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_free, 2) }}</td>
            </tr>
        @endif
        @if($document->total_unaffected > 0)
            <tr>
                <td colspan="5" class="text-right font-bold">SUBTOTAL 0%: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
            </tr>
        @endif
        @if($document->total_exonerated > 0)
            <tr>
                <td colspan="5" class="text-right font-bold">OP. EXONERADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_exonerated, 2) }}</td>
            </tr>
        @endif
        @if($document->total_taxed > 0)
            <tr>
                <td colspan="5" class="text-right font-bold">SUBTOTAL 12%: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
        @endif
        @if($document->total_discount > 0)
            <tr>
                <td colspan="5" class="text-right font-bold">{{(($document->total_prepayment > 0) ? 'ANTICIPO':'DESCUENTO TOTAL')}}: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td colspan="5" class="text-right font-bold">IVA: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_igv, 2) }}</td>
        </tr>

        @if($document->total_isc > 0)
        <tr>
            <td colspan="5" class="text-right font-bold">ISC: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_isc, 2) }}</td>
        </tr>
        @endif

        <tr>
            <td colspan="5" class="text-right font-bold">TOTAL A PAGAR: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
        </tr>
    </tbody>
</table>

@if($document->payment_condition_id && ($payments->count() || $document->fee->count()))
<table class="full-width">
    <tr>
        <td>
            <strong>CONDICIÓN DE PAGO: {{ $document->payment_condition->name }} </strong>
        </td>
    </tr>
</table>
@endif

@if($payments->count())
    <table class="full-width">
        <tr>
            <td>
                <strong>PAGOS:</strong>
            </td>
        </tr>
            @php
                $payment = 0;
            @endphp
            @foreach($payments as $row)
                <tr>
                    <td>&#8226; {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td>
                </tr>
            @endforeach
        </tr>

    </table>
@endif

@if($document->fee->count())

<table class="full-width">
        @foreach($document->fee as $key => $quote)
            <tr>
                <td>&#8226; {{ (empty($quote->getStringPaymentMethodType()) ? 'Cuota #'.( $key + 1) : $quote->getStringPaymentMethodType()) }} / Fecha: {{ $quote->date->format('d-m-Y') }} / Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}</td>
            </tr>
        @endforeach
    </tr>
</table>

@endif

<table class="full-width">
    <tr>
        <td width="65%">
            @if($document->observation)
                <strong>Información adicional</strong>
                <p>{{$document->observation}}</p>
            @endif
        </td>
    </tr>
</table>

<table class="full-width mt-4">
    <tr class="mt-4">
        <td width="50%" class="font-bold">
            <h4 >
                <u>
                    Detalle del Asiento:
                </u>
            </h4>
        </td>
        <br>
        <td width="50%" class="font-bold">ASIENTO NRO - {{$account_entry['filename']}}</td>
    </tr>
</table>
<table width="100%">
    <thead>
        <tr >
            <th width="60%" class="border-box text-center p-2">
                Cuenta
            </th>
            <th width="20%" class="border-box text-center p-1">
                Debe
            </th>
            <th width="20%" class="border-box text-center p-1">
                Haber
            </th>
        </tr>
    </thead>
    <tbody class="font-sm">
        @foreach($account_entry['items'] as $value)
        <tr >
            @if($value->debe>0)
            <td class="border-box text-center p-1 font-sm">{{$value->account_movement->code}} {{$value->account_movement->description}} </td>
            @else
            <td class="border-box text-center p-1 pl-5 font-sm">{{$value->account_movement->code}} {{$value->account_movement->description}} </td>
            @endif

            <td class="border-box text-center p-1">${{number_format($value->debe, 2, '.', ',')}} </td>
            <td class="border-box text-center p-1">${{number_format($value->haber, 2, '.', ',')}} </td>

        </tr>
        @endforeach
        <tr class="font-sm">
            <td class="text-right p-1 font-sm font-bold">Totales: </td>
            <td class="text-right p-1 font-bold">${{number_format($account_entry['total_debe'], 2, '.', ',')}}</td>
            <td class="text-right p-1 font-bold">${{number_format($account_entry['total_haber'], 2, '.', ',')}}</td>
        </tr>
    </tbody>
</table>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<table class="full-width mt-6">
    <tbody class="font-sm">
        <tr class="font-sm">
            <td class="border-top text-left p-1 font-sm" width="15%">
                <b>
                    Responsable: 
                </b>
                {{ $document->user->name }}
                <br>
                <b>
                    Cédula: 
                </b>
                {{ $document->user->number }}
            </td>
            <td class="p-1"  width="5%"></td>
            <td class="border-top text-left p-1 font-sm" width="15%">
                <b>
                    Revisado por: 
                </b>
                <br>
                <br>
                <b>
                    Cédula: 
                </b>
                
            </td>
            <td class="p-1"  width="5%"></td>
            <td class="border-top text-left p-1 font-sm" width="20%">
                <b>
                    Auditor Interno: 
                </b>
                <br>
                <br>
                <b>
                    Cédula: 
                </b>
                
            </td>
            <td class="p-1"  width="5%"></td>
            <td class="border-top text-left p-1 font-sm" width="15%">
                <b>
                    Aprobado por: 
                </b>
                <br>
                <br>
                <b>
                    Cédula: 
                </b>
                
            </td>
            <td class="p-1"  width="5%"></td>
            <td class="border-top text-left p-1 font-sm" width="20%">
                <b>
                    Recibí conforme: 
                </b>
                <br>
                <br>
                <b>
                    Cédula: 
                </b>
                
            </td>
         </tr>
    </tbody>
</table>

</body>
</html>

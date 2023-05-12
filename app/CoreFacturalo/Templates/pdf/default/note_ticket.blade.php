@php
    $establishment = $document->establishment;
    $customer = $document->customer;

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
    $currency_type_description_array = [
        'PEN' => 'S/D',
        '0' => 'S/D',
        '1' => 'DNI',
        '6' => 'RUC',
    ];
    //JOINSOFTWARE
    $affected_document_number = ($document_base->affected_document) ? $establishment2.substr($document_base->affected_document->series, 1).''.str_pad($document_base->affected_document->number, 9, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    // $optional = $document->optional;
    $accounts = \App\Models\Tenant\BankAccount::all();
@endphp
<html>
<head>
    {{--<title>{{ $document_number }}111111111</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>

@if($company->logo)
{{-- JOINSOFTWARE -> pt-5 --}}
    <div class="text-center company_logo_box">
        <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo_ticket contain">
    </div>
{{-- JOINSOFTWARE @else
    <div class="text-center company_logo_box pt-5">
        <img src="{{ asset('logo/logo.jpg') }}" class="company_logo_ticket contain">
    </div>
--}}
@endif
<table class="full-width mx-2">
    <tr>
        <td class="text-center"><h3>{{ $company->name }}</h3></td>
    </tr>
    <tr>
        <td class="text-center"><h4>{{ 'RUC '.$company->number }}</h4></td>
    </tr>
    <tr>
        <td class="text-center" style="text-transform: uppercase;">
            {{ ($establishment->address !== '-')? $establishment->address : '' }}
            <!--{{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}-->
            {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
            {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
        </td>
    </tr>
    <tr>
        <td class="text-center">{{ ($establishment->email !== '-')? $establishment->email : '' }}</td>
    </tr>
    <tr>
        <td class="text-center pb-3">{{ ($establishment->telephone !== '-')? $establishment->telephone : '' }}</td>
    </tr>
    <tr>
        <td class="text-center pt-3 border-top"><h4>{{ $document->document_type->description }}</h4></td>
    </tr>
    <tr>
        <td class="text-center pb-3 border-bottom"><h3>{{ $document_number }}</h3></td>
    </tr>
</table>
<table class="full-width mx-2">
    <tr>
        <td width="45%" class="pt-3"><p class="desc">Fecha de emisión:</p></td>
        <td width="" class="pt-3"><p class="desc">{{ $document->date_of_issue->format('Y-m-d') }}</p></td>
    </tr>
    @isset($document->date_of_due)
    <tr>
        <td><p class="desc">Fecha de vencimiento:</p></td>
        <td><p class="desc">{{ $document->date_of_due->format('Y-m-d') }}</p></td>
    </tr>
    @endisset

    <tr>
        <td><p class="desc">Cliente:</p></td>
        <td><p class="desc">{{ $customer->name }}</p></td>
    </tr>
    <tr>
        <td><p class="desc">{{ $customer->identity_document_type->description }}:</p></td>
        <td><p class="desc">{{ $customer->number }}</p></td>
    </tr>
    @if ($customer->address !== '')
        <tr>
            <td class="align-top"><p class="desc">Dirección:</p></td>
            <td>
                <p class="desc">
                    {{ $customer->address }}
                    {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
                    {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
                    {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
                </p>
            </td>
        </tr>
    @endif
    @if ($document->purchase_order)
        <tr>
            <td><p class="desc">Orden de Compra:</p></td>
            <td><p class="desc">{{ $document->purchase_order }}</p></td>
        </tr>
    @endif
    @if ($document->guides)
        @foreach($document->guides as $guide)
            <tr>
                <td><p class="desc">{{ $guide->document_type_id }}</p></td>
                <td><p class="desc">{{ $guide->number }}</p></td>
            </tr>
        @endforeach
    @endif
    <!-- JOINSOFTWARE -->
    <tr>
        <td class="desc">Comprobante que se modifica:</td>
        <td class="desc"><pre>FACTURA&nbsp;&nbsp;    {{ $affected_document_number }}</pre></td>
    </tr>
    <!-- JOINSOFTWARE -->
    <tr>
        <td class="desc">Fecha Emisión (comprobante a modificar):</td>
        <td class="desc">{{ $document_base->affected_document->date_of_issue->format('m-d-Y') }}</td>
    </tr>
    <!-- JOINSOFTWARE -->
    <tr>
        <td class="align-top desc">Razón de Modificación:</td>
        <td class="text-left desc">{{ $document_base->note_description }}</td>
    </tr>
    <!-- JOINSOFTWARE
    <tr>
        <td class="desc">Documento Afectado:</td>
        <td class="desc">{{ $affected_document_number }}</td>
    </tr>
    <tr>
        <td class="desc">Tipo de nota:</td>
        <td class="desc">{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>
    </tr>
    <tr>
        <td class="align-top desc">Descripción:</td>
        <td class="text-left desc">{{ $document_base->note_description }}</td>
    </tr>
    -->
    {{-- @if ($optional->salesman)
        <tr>
            <td class="desc">Vendedor:</td>
            <td class="desc">{{ $optional->salesman  }}</td>
        </tr>
    @endif
    @if ($optional->box_number)
        <tr>
            <td class="desc">N° Caja:</td>
            <td class="desc">{{ $optional->box_number  }}</td>
        </tr>
    @endif
    @if ($optional->method_payment)
        <tr>
            <td class="desc">Cond. de pago:</td>
            <td class="desc">{{ $optional->method_payment  }}</td>
        </tr>
    @endif --}}
</table>
<table class="full-width mt-10 mb-10 mx-2">
    <thead class="">
    <tr>
        <th class="border-top-bottom desc-9 text-left">CANT.</th>
        <th class="border-top-bottom desc-9 text-left">UNIDAD</th>
        <th class="border-top-bottom desc-9 text-left">DESCRIPCIÓN</th>
        <th class="border-top-bottom desc-9 text-left">P.UNIT</th>
        <th class="border-top-bottom desc-9 text-left">TOTAL</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document->items as $row)
        <tr>
            <td class="text-center desc-9 align-top">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-center desc-9 align-top">{{ $row->item->unit_type_id }}</td>
            <td class="text-left desc-9 align-top">
                {!! $row->item->description !!}
                @if($row->attributes)
                    @foreach($row->attributes as $attr)
                        <br/>{!! $attr->description !!} : {{ $attr->value }}
                    @endforeach
                @endif
                @if($row->discounts)
                    @foreach($row->discounts as $dtos)
                        <br/><small>{{ $dtos->factor * 100 }}% {{$dtos->description }}</small>
                    @endforeach
                @endif
            </td>
            <td class="text-right desc-9 align-top">{{ number_format($row->unit_price, 2) }}</td>
            <td class="text-right desc-9 align-top">{{ number_format($row->total, 2) }}</td>
        </tr>
        <tr>
            <td colspan="5" class="border-bottom"></td>
        </tr>
    @endforeach
        @if($document->total_exportation > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">OP. EXPORTACIÓN: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_exportation, 2) }}</td>
            </tr>
        @endif
        @if($document->total_free > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">OP. GRATUITAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_free, 2) }}</td>
            </tr>
        @endif
        <!-- JOINSOFTWARE -->
        @if($document->total_unaffected > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">SUBTOTAL 0%: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_unaffected, 2) }}</td>
            </tr>
        @endif
        @if($document->total_exonerated > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">OP. EXONERADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_exonerated, 2) }}</td>
            </tr>
        @endif
        <!-- JOINSOFTWARE -->
        @if($document->total_taxed > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">SUBTOTAL 12%: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
        @endif
        @if($document->total_discount > 0)
            <tr>
                <td colspan="5" class="text-right font-bold">{{(($document->total_prepayment > 0) ? 'ANTICIPO':'DESCUENTO TOTAL')}}: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
            </tr>
        @endif
        <!-- JOINSOFTWARE -->
        <tr>
            <td colspan="4" class="text-right font-bold desc">IVA: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold desc">{{ number_format($document->total_igv, 2) }}</td>
        </tr>
        <tr>
            <td colspan="4" class="text-right font-bold desc">TOTAL A PAGAR: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold desc">{{ number_format($document->total, 2) }}</td>
        </tr>
    </tbody>
</table>
<table class="full-width mx-2">
    <tr>
        @foreach($document->legends as $row)
            <td class="desc pt-3" style="text-transform: uppercase;">Son: <span class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></td>
        @endforeach
    </tr>
    @foreach($accounts as $account)
        <tr>
            <td class="desc" >
                <span class="font-bold">{{$account->bank->description}}</span> {{$account->currency_type->description}}
                <span class="font-bold">N°:</span> {{$account->number}}
                @if($account->cci)
                    <span class="font-bold">CCI:</span> {{$account->cci}}
                @endif
            </td>
        </tr>
    @endforeach
    @if(isset($document->optional->observations))
        <tr>
            <td class="desc">Obsevaciones</td>
        </tr>
        <tr>
            <td class="desc">{{ $document->optional->observations }}</td>
        </tr>
    @endif
    <!-- JOINSOFTWARE -->
    <tr>
        <td class="text-center desc" style="text-transform: uppercase;">
            @if($company->rimpe_emp || $company->rimpe_np)
            CONTRIBUYENTE RÉGIMEN RIMPE
            @endif
        </td>
    </tr>
    <!-- JOINSOFTWARE -->
    <tr>
        <td class="text-center desc" style="text-transform: uppercase;">
            @if($company->obligado_contabilidad)
            Obligado a llevar contabilidad: SI
            @else
            Obligado a llevar contabilidad: NO
            @endif
        </td>
    </tr>
    <!-- JOINSOFTWARE
    <tr>
        <td class="text-center pt-5"><img class="qr_code" src="data:image/png;base64, {{ $document->qr }}" /></td>
    </tr>
    <tr>
        <td class="text-center desc">Código Hash: {{ $document->hash }}</td>
    </tr>
    -->
    <tr>
        <td class="text-center desc pt-5">Para consultar el comprobante ingresar a {!! url('/buscar') !!}</td>
    </tr>
</table>
</body>
</html>

@php

    //$left =  ($document->series) ? $document->series : $document->prefix;
    //$tittle = $left.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();
    //$total_payment = $document->payments->sum('payment');
    //$balance = ($document->total - $total_payment) - $document->payments->sum('change');
    //dd($tittle_unpaid);
    //$data = $payments;
    //$valores = null;
    /*
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

    $num_comprobante = str_pad(($document->payments[$index]->id), 8, '0', STR_PAD_LEFT);
    //Log::info('datos'.json_encode($account_entry))
    //Log::info('index'.$index);
    Log::info('document - '.$document->payments[$index]->id)
    */
    Log::info($document);
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
            <h3 class="text-center">{{ $document[0]['sequential'] }}</h3>
        </td>
    </tr>
</table>
<br>
<br>
<table class="full-width">
    <thead class="">
    <tr class="bg-grey">
        <th class="border-top-bottom text-left p-1">#</th>
        <th class="border-top-bottom text-left p-2">SERIE</th>
        <th class="border-top-bottom text-left p-2">NÚMERO</th>
        <th class="border-top-bottom text-left p-2">CUOTA</th>
        <th class="border-top-bottom text-left p-3">CLIENTE</th>
        <th class="border-top-bottom text-left p-2">CI</th>
        <th class="border-top-bottom text-left p-3">COMENTARIO</th>
        <th class="border-top-bottom text-left p-2">PAGADO</th>
        <th class="border-top-bottom text-left p-2">PENDIENTE</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document as $key => $row)
        <tr>
            <td class="text-left ">{{ $key }}</td>
            <td class="text-left ">{{ $row['document_serie'] }}</td>
            <td class="text-left ">{{ $row['document_number']}}</td>
            <td class="text-left ">{{ $row['document_fee']}}</td>
            <td class="text-left ">{{ $row['client_name'] }}</td>
            <td class="text-left ">{{ $row['client_number'] }}</td>
            <td class="text-left ">{{ $row['comment'] }}</td>
            <td class="text-left ">{{ $row['payment'] }}</td>
            <td class="text-left ">{{ $row['to_pay'] }}</td>
        </tr>
    @endforeach
    </tbody>
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
            <th width="60%" class="border-box text-left p-2">
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
            <td class="border-box text-left p-1 font-sm">{{$value->account_movement->code}} {{$value->account_movement->description}} </td>
            @else
            <td class="border-box text-left p-1 pl-5 font-sm">{{$value->account_movement->code}} {{$value->account_movement->description}} </td>
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
            <td class="border-top text-left p-1 font-sm" width="30%">
                <b>
                    Elaborado por:
                </b>
                {{$user_log->name}}
                <br>
                <b>
                    Cédula por:
                </b>
                {{$user_log->number}}
            </td>
            <td class="p-1"  width="8%"></td>
            <td class="border-top text-left p-1 font-sm" width="30%">
                <b>
                    Aprobado por:
                </b>
                <br>
                <b>
                    Cédula por:
                </b>

            </td>
            <td class="p-1"  width="8%"></td>
            <td class="border-top text-left p-1 font-sm" width="30%">
                <b>
                    Revisado por:
                </b>
                <br>
                <b>
                    Cédula por:
                </b>

            </td>
         </tr>
    </tbody>
</table>


</body>
</html>

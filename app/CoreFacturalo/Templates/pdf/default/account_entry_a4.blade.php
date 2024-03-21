@php
    $establishment = $document->establishment;
    $customer = $document->person;

    $tittle = $document->prefix.'-'.str_pad($document->id, 8, '0', STR_PAD_LEFT);

    $logo = "storage/uploads/logos/{$company->logo}";
    if($establishment->logo) {
        $logo = "{$establishment->logo}";
    }

@endphp
<html>
<head>
    {{--<title>{{ $tittle }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}


</head>

<body>
<table class="full-width m-0 pt-0">
    <tr>
        @if($company->logo)
            <td width="25%" class="border-bottom align-middle">
                <div class="company_logo_sm">
                    <img src="data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$company->name}}" class="company_logo" style="max-width: 70px;">
                </div>
            </td>
        @else
            <td width="25%" class="border-bottom">
                {{--<img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px">--}}
            </td>
        @endif
        <td  class="border-bottom">

        </td >
        <td width="35%" class="pl-0 m-0 border-bottom text-right align-middle">
            <div class="text-right">
                <h5 class="font-bold">{{ $company->name }}</h5>
                <p class="font-bold text-sm">{{ 'RUC '.$company->number }}</p>
                <p class="text-sm">Usuario: {{  $document->user->name }} </p>
                <p class="text-sm"> Fecha de impresión : {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
            </div>
        </td>

    </tr>
    <tr >
        <td   colspan="3" class="text-center pt-4">
                <h4 class="font-bold">Asiento Contable</h4>
                <h6 >{{ $tittle }}</h6>
        </td >

    </tr>
</table>

<table class="full-width mt-4">
    <tr class="mt-4">
        <td width="20%" class="font-bold">
            Fecha:
        </td>
        <td width="85%">{{ \Carbon\Carbon::createFromFormat('Y-m-d', $document->seat_date)->format('d/m/Y') }}</td>
    </tr>
    <tr class="mt-4">
        <td width="20%" class="font-bold">
            Código:
        </td>
        <td width="85%">{{ $document->filename}} -- {{$document->type_account->name}} </td>
    </tr>

    <tr class="mt-4">
        <td width="20%" class="font-bold">
            Comentario:
        </td>
        <td width="85%">{{ $document->comment}}</td>
    </tr>
    <tr class="mt-4">
        <td width="20%" class="font-bold">
            Gasto No Deducible:
        </td>
        <td width="85%">No</td>
    </tr>
</table>

<table class="full-width mt-4">
    <tr class="mt-4">
        <td width="100%" class="font-bold">
            <h4 >
                <u>
                    Detalle del Asiento:
                </u>
            </h4>
        </td>
    </tr>
</table>

<table class="full-width mt-3">
    <thead>
        <tr >
            <th width="40%" class="border-box text-left p-1">
                Cuenta
            </th>
            <th width="15%" class="border-box text-right p-1">
                Debe
            </th>
            <th width="15%" class="border-box text-right p-1">
                Haber
            </th>
            <th class="border-box text-left p-1">
                Centro de Costo
            </th>
            <th class="border-box text-left p-1">
                Comentario
            </th>
        </tr>
    </thead>
    <tbody class="font-sm">
        @foreach($document->items as $value)
        <tr >
            @if($value->debe>0)
            <td class="border-box text-left p-1 font-sm">{{$value->account_movement->code}} {{$value->account_movement->description}} </td>
            @else
            <td class="border-box text-left p-1 pl-5 font-sm">{{$value->account_movement->code}} {{$value->account_movement->description}} </td>
            @endif

            <td class="border-box text-right p-1">${{number_format($value->debe, 2, '.', ',')}} </td>
            <td class="border-box text-right p-1">${{number_format($value->haber, 2, '.', ',')}} </td>
            <td class="border-box text-left p-1">{{$value->seat_cost}} </td>
            <td class="border-box text-left p-1">{{$value->comment}} </td>
        </tr>
        @endforeach
        <tr class="font-sm">
            <td class="text-right p-1 font-sm font-bold">Totales: </td>
            <td class="text-right p-1 font-bold">${{number_format($document->total_debe, 2, '.', ',')}}</td>
            <td class="text-right p-1 font-bold">${{number_format($document->total_haber, 2, '.', ',')}}</td>
            <td class="text-left p-1 font-bold"> </td>
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
                {{$document->user->name}}
                <br>
                <b>
                    Cédula por:
                </b>
                {{$document->user->number}}
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

@php
    $establishment = $document->establishment;
    $customer = $document->customer;
    $invoice = $document->invoice;
    $document_base = ($document->note) ? $document->note : null;

    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_number = $establishment->code.''.substr($document->series,1,3).''.str_pad($document->number, 9, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();

    if($document_base) {

        $affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

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
                                    <strong>Matriz: </strong> <h7 style="text-transform: uppercase;">{{ ($establishment->address !== '-')? $establishment->address : ', ' }}{{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}{{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}</h7><br></br>
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
                                    <pre style="tab-size: 16; font-size: 14px"><strong>FACTURA         </strong>         No.{{$document_number}}</pre>
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
                        <table class="full-width">
                            <thead class="">
                                <tr style="background: #eaeaea;">
                                    <th class="py-2" style="text-align: start; padding-left: 15px; padding-right: 15px;">Formas de pago</th>
                                </tr>
                            </thead>
                            <tbody>
                            @if($document->payment_condition_id === '01' )
                            @foreach($document->payments as $pago)
                                <tr style="background: #f7f7f5;">
                                    <td style="text-align: start; padding-left: 15px; padding-right: 15px;">{{ $pago->sridesc }}</td>
                                    <td style="text-align: start; padding-left: 15px; padding-right: 15px;">{{ $document->currency_type->symbol }}{{ number_format($pago->payment, 2) }}</td>
                                    <td style="text-align: start; padding-left: 15px; padding-right: 15px;">0 días</td>
                                </tr>

                            @endforeach
                            @endif
                            @if($document->payment_condition_id === '02')
                            @foreach($document->fee as $pago)
                                <tr style="background: #f7f7f5;">
                                    <td style="text-align: start; padding-left: 15px; padding-right: 15px;">{{ $pago->sridesc }}</td>
                                    <td style="text-align: start; padding-left: 15px; padding-right: 15px;">{{ $document->currency_type->symbol }}{{ number_format($pago->amount, 2) }}</td>
                                    <td style="text-align: start; padding-left: 15px; padding-right: 15px;">{{ date_diff($document->date_of_issue, $pago->date)->format('%a') }} días</td>
                                </tr>
                            @endforeach
                            @endif
                            </tbody>
                        </table>
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
                                <td style="padding-left: 15px; padding-right: 15px; background: #f7f7f5;">OP. GRAVADAS:</td>
                                <td class="text-right" style="padding-left: 15px; padding-right: 15px; background: #eaeaea;">{{ $document->currency_type->symbol }}{{ number_format($document->total_taxed, 2) }}</td>
                            </tr>
                            @endif
                        @elseif($document->total_taxed > 0)
                            <tr>
                                <td style="padding-left: 15px; padding-right: 15px; background: #f7f7f5;">Subtotal Sin Impuestos:</td>
                                <td class="text-right" style="padding-left: 15px; padding-right: 15px; background: #eaeaea;">{{ $document->currency_type->symbol }}{{ number_format($total0+$total12+$total14+$total8, 2) }}</td>
                            </tr>
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

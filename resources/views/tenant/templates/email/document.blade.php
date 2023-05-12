@php
    $establishment = $document->establishment;
    $logo = "storage/uploads/logos/{$company->logo}";
    if($establishment->logo) {
        $logo = "{$establishment->logo}";
    }
    $document_number = '';
    if($document->idRetencion){
        $document_number = $document->idRetencion;
    }else{
        $document_number = $establishment->code.''.substr($document->series,1,3).''.str_pad($document->number, 9, '0', STR_PAD_LEFT);
    }

    $url =  asset("storage/uploads/logos/".$company->logo);
    @endphp

<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
        <title>Envio de Comprobante de Pago Electrónico</title>
        <style>
            body {
                color: #000;
            }
            ul {
                list-style: none;
            }
            .main {
                display: grid;
                background: #f5f7f9;
                padding: 40px 0px;
                text-align: center;
            }
            .fdiv {
                background: white;
                width: 50%;
                margin: 0 auto;
                border-bottom: 1px solid #e4e8ea;
            }
            .fdiv > h1 {
                font-size: 55px;
				margin: 0;
			}
            #value {
				font-size: 15px;
				margin-bottom: 0;
			}
			#value1 {
				margin: 12px 0;
				font-size: 15px;
			}
			#value2 {
				margin-bottom: 15px;
				font-size: 15px;
			}
			.fdiv > h2 {
                font-size: 30px;
				margin-top: 0;
			}
            #title1 {
				color: gray;
				margin-bottom: 0;
                padding-left: 90px;
			}
            #title {
                color: gray;
            }
            .solid {
                width: 80%;
                border-top: 1px solid #e4e8ea;
            }
            .sdiv {
                background: #e4e8ea;
                width: 50%;
                margin: 0 auto;
            }
            .tdiv {
                background: #dce4e4;
                width: 50%;
                height: 40px;
                margin: 0 auto;
            }
            .btn1 {
				margin-bottom: 25px;
                background: #348eda;
                border: 0;
                color: white;
                padding: 10px 25px;
                border-radius: 20px;
			}
            .btn2 {
                width: 40%;
                height: 40px;
                margin: 0 auto;
                background: white;
                border: 0;
            }
        </style>
    </head>
    <body>

        <!-- JOINSOFTWARE Code2 -->
        <div class="main">
            <div class="fdiv">
                <img alt="logo" src="{{ asset('logo/logo2.png') }}" width="70%" height="100%">
            </div>
            <div class="fdiv">
                @if($document->customer)
                <h3 id="title">{{ $document->customer->name }}</h3>
                @elseif($document->purchase)
                <h3 id="title">{{ $document->purchase->supplier->name }}</h3>
                @else
                <h3 id="title">{{ $document->supplier->name }}</h3>
                @endif
                <h3 id="title">Has recibido un Documento Electrónico de</h3>
                @if($company->logo)
                <!--<img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="max-width: 150px;">
                -->
                <img alt="{{$company->name}}" src="{{ $url }}" width="50px" height="50px">

                @else
                <img alt="logo" src="{{ asset('logo/logo.jpg') }}" width="50px" height="50px">
                @endif
                <hr class="solid">
                @if($document->idRetencion)
                <h3>Retencion {{ $document_number }}</h3>
                @else
                <h3>{{ $document->document_type->description }} {{ $document_number }}</h3>
                @endif

            </div>
            <div class="sdiv">
                @if($document->idRetencion)
                <h6 id="value1">Fecha Emisión: {{$document->created_at->format('m-d-Y')}}</h6>
                @else
                <h6 id="value1">Fecha Emisión: {{$document->date_of_issue->format('m-d-Y')}}</h6>
                @endif
            </div>
            <div class="fdiv">
                <h6 id="value">Por el valor de:</h6>
                @if($document->idRetencion)
                <h2>{{ $document->purchase->currency_type->symbol }}{{ $document->total_retention }}</h2>
                @else
                <h2>{{ $document->currency_type->symbol }}{{ $document->total }}</h2>
                @endif
            </div>
            <p class="btn2">Adjunto encontrará los archivos pdf y xml de su documento electronico</p>
            <div class="tdiv"></div>
        </div>
    </body>
</html>

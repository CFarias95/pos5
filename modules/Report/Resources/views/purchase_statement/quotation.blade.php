@extends('tenant.layouts.app')

@section('content')

    <tenant-report-purchases-quotation
        @if(isset($apply_conversion_to_pen))
            :apply-conversion-to-pen="{{ json_encode($apply_conversion_to_pen) }}"
        @endif
    ></tenant-report-purchases-quotation>

@endsection

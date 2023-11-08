@extends('tenant.layouts.app')

@section('content')

    <tenant-report-retention-statement-index
        @if(isset($apply_conversion_to_pen))
            :apply-conversion-to-pen="{{ json_encode($apply_conversion_to_pen) }}"
        @endif
    ></tenant-report-retention-statement-index>

@endsection

@extends('tenant.layouts.app')

@section('content')

    <tenant-reporte-purchases-payments-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-reporte-purchases-payments-index>

@endsection

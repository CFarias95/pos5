@extends('tenant.layouts.app')

@section('content')

    <tenant-stock_fecha-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    >   
    </tenant-stock_fecha-index>

@endsection
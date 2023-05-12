@extends('tenant.layouts.app')

@section('content')

    <tenant-stock-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    >   
    </tenant-stock-index>

@endsection
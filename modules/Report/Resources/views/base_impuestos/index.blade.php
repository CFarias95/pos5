@extends('tenant.layouts.app')

@section('content')

    <tenant-base_impuestos-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    >   
    </tenant-base_impuestos-index>

@endsection
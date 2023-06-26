@extends('tenant.layouts.app')

@section('content')

    <tenant-extracto_cuentas-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    >   
    </tenant-extracto_cuentas-index>

@endsection
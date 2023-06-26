@extends('tenant.layouts.app')

@section('content')

    <tenant-balance_comprobacion-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    >   
    </tenant-balance_comprobacion-index>

@endsection
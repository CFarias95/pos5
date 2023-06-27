@extends('tenant.layouts.app')

@section('content')

    <tenant-balance_resultados-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    >   
    </tenant-balance_resultados-index>

@endsection
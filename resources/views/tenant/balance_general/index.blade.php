@extends('tenant.layouts.app')

@section('content')

    <tenant-balance_general-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    >   
    </tenant-balance_general-index>

@endsection
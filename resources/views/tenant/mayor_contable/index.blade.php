@extends('tenant.layouts.app')

@section('content')

    <tenant-mayor_contable-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    >   
    </tenant-mayor_contable-index>

@endsection
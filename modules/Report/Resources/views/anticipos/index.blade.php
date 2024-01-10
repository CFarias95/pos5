@extends('tenant.layouts.app')

@section('content')

    <tenant-anticipos_reporte-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    >   
    </tenant-anticipos_reporte-index>

@endsection
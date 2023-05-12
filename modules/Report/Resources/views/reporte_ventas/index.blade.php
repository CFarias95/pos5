@extends('tenant.layouts.app')

@section('content')

    <tenant-reporte-ventas-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-reporte-ventas-index>

@endsection
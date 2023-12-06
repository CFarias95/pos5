@extends('tenant.layouts.app')

@section('content')

    <tenant-reporte-ventas-detalle-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-reporte-ventas-detalle-index>

@endsection

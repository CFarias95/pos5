@extends('tenant.layouts.app')

@section('content')

    <tenant-recetas_kits-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    >   
    </tenant-recetas_kits-index>

@endsection
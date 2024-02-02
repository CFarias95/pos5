@extends('tenant.layouts.app')

@section('content')

    <tenant-cobros_defectuosos-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    >   
    </tenant-cobros_defectuosos-index>

@endsection
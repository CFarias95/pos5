@extends('tenant.layouts.app')

@section('content')

    <tenant-plan_cuentas-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-plan_cuentas-index>

@endsection
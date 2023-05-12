@extends('tenant.layouts.app')

@section('content')

    <tenant-finance-advances-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-finance-advances-index>

@endsection

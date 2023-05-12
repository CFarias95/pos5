@extends('tenant.layouts.app')

@section('content')

    <tenant-persons-index

        :type="{{ json_encode($type) }}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
        :company="{{\App\Models\Tenant\company::first()->toJson()}}"
        :type-user="{{json_encode(Auth::user()->type)}}"

    ></tenant-persons-index>


@endsection

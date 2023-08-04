@extends('tenant.layouts.app')

@section('content')

    <tenant-inventory-transactions :type-user="{{ json_encode(auth()->user()->type) }}"></tenant-inventory-transactions>

@endsection

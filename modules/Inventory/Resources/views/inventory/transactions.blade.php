@extends('tenant.layouts.app')

@section('content')

    <inventory-transactions :type-user="{{ json_encode(auth()->user()->type) }}"></inventory-transactions>

@endsection
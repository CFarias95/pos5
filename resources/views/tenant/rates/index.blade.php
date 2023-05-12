@extends('tenant.layouts.app')

@section('content')

    <tenant-ratelists-index
        :type-user="{{json_encode(Auth::user()->type)}}"
    ></tenant-ratelists-index>


@endsection

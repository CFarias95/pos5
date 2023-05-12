@extends('tenant.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 pt-2 pt-md-0">
            <tenant-production-form
                @if(!empty($id))
                :id="{{$id}}"
                @endif
            ></tenant-production-form>
        </div>
        <!--
        <div class="col-lg-9 col-md-12 pt-2 pt-md-0">
            <tenant-production-form></tenant-production-form>
        </div>
        <div class="col-lg-3 col-md-12">
            <tenant-production-state></tenant-production-state>
        </div>
        -->
    </div>

@endsection

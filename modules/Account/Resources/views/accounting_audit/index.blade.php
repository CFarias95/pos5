@extends('tenant.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <tenant-accounting-audit
                :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
            ></tenant-accounting-audit>
        </div>
    </div>
@endsection

@extends('tenant.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <tenant-accounting-reconciliation
                :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
            ></tenant-accounting-reconciliation>
        </div>
    </div>
@endsection

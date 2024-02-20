@extends('tenant.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <tenant-bank-reconciliation
                :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
            ></tenant-bank-reconciliation>
        </div>
    </div>
@endsection

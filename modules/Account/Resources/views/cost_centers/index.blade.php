@extends('tenant.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <tenant-cost-centers
                :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
            ></tenant-cost-centers>
        </div>
    </div>
@endsection

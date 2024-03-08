@extends('tenant.layouts.app')

@section('content')

    <tenant-documents-note-edit
        :user="{{ json_encode(auth()->user()) }}"
        :document_affected="{{ json_encode($document_affected) }}"
        :note ="{{ json_encode($note) }}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-documents-note-edit>

@endsection

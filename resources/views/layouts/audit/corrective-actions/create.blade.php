@extends('layouts.audit.layout.app', ['select2' => true])

@section('title2')
<title>Create Corrective Action - JASIRI LIMS</title>
@endsection

@section('content2')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="mdi mdi-plus-circle"></i> Create Corrective Action</h4>
                <a href="{{ route('audit.capa.index') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            @livewire('audit-module.corrective-action-form', ['ncId' => $ncId ?? null])
        </div>
    </div>
</div>
@endsection

@section('script2')
@endsection


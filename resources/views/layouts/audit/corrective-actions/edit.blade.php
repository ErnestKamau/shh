@extends('layouts.audit.layout.app')

@section('title2')
<title>Edit CAPA {{ $capa->capa_number }} - JASIRI LIMS</title>
@endsection

@section('content2')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="mdi mdi-pencil"></i> Edit: {{ $capa->capa_number }}</h4>
                <a href="{{ route('audit.capa.show', $capa->id) }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            @livewire('audit-module.corrective-action-form', ['capaId' => $capa->id])
        </div>
    </div>
</div>
@endsection

@section('script2')
@endsection


@extends('layouts.audit.layout.app')

@section('title2')
<title>Audit Management - Audit Summary Reports</title>
@endsection

@section('content2')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="mdi mdi-file-document-check"></i> Audit Summary Reports</h4>
                <a href="{{ route('audit.create') }}" class="btn btn-primary">
                    <i class="mdi mdi-plus"></i> Create New Audit Report
                </a>
            </div>
        </div>
        <div class="card-body">
            @livewire('audit.audit-summary-reports-table')
        </div>
    </div>
</div>
@endsection

@section('script2')
@endsection


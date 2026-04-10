@extends('layouts.audit.layout.app')

@section('title2')
<title>Edit Audit Report - {{ $audit->report_number }}</title>
@endsection

@section('content2')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="mdi mdi-file-document-edit"></i> Edit Audit Summary Report</h4>
                <a href="{{ route('audit.show', $audit->id) }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left"></i> Back to Report
                </a>
            </div>
        </div>
        <div class="card-body">
            @livewire('audit.audit-summary-report-form', ['auditId' => $audit->id])
        </div>
    </div>
</div>
@endsection

@section('script2')
@endsection


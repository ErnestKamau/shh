@extends('layouts.audit.layout.app')

@section('title2')
<title>Audit Report - {{ $audit->report_number }}</title>
@endsection

@section('content2')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="mdi mdi-file-document-check"></i> Audit Summary Report: {{ $audit->report_number }}</h4>
                <div>
                    <a href="{{ route('audit.edit', $audit->id) }}" class="btn btn-warning">
                        <i class="mdi mdi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('audit.pdf', $audit->id) }}" class="btn btn-info" target="_blank">
                        <i class="mdi mdi-file-pdf"></i> Generate PDF
                    </a>
                    <a href="{{ route('audit.index') }}" class="btn btn-secondary">
                        <i class="mdi mdi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @livewire('audit.audit-report-show', ['auditId' => $audit->id])
        </div>
    </div>
</div>
@endsection

@section('script2')
@endsection


@extends('layouts.audit.layout.app')

@section('title2')
<title>Compliance Statuses Configuration - JASIRI LIMS</title>
<style type="text/css">
    .config-index-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .config-index-header {
        background: #f8f9fa;
        border: none;
        border-left: 6px solid #007bff;
        padding: 18px 24px;
    }

    .config-index-header h4 {
        font-size: 1.35rem;
        font-weight: 600;
        color: #222;
        letter-spacing: 0.5px;
        margin: 0;
    }
</style>
@endsection

@section('content2')
<div class="container-fluid">
    <div class="card config-index-card">
        <div class="card-header config-index-header">
            <h4>
                <i class="mdi mdi-check-circle-multiple"></i>
                Compliance Statuses Configuration
            </h4>
            <small class="text-muted">Manage compliance statuses for audit checklist items</small>
        </div>
        <div class="card-body">
            @livewire('audit-module.config-manager', ['type' => 'compliance_statuses'])
        </div>
    </div>
</div>
@endsection





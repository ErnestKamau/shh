@extends('layouts.audit.layout.app')

@section('title2')
<title>Audit Approval Configuration - JASIRI LIMS</title>
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
        border-left: 6px solid #28a745;
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
    <div class="row mb-4">
        <div class="col-12">
            <div class="card config-index-card">
                <div class="config-index-header">
                    <h4>
                        <i class="mdi mdi-account-check"></i> Audit Workflow Approval Configuration
                    </h4>
                    <p class="mb-0 text-muted mt-2">Configure approvers and verifiers for each workflow step</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @livewire('audit-module.audit-approval-config')
        </div>
    </div>
</div>
@endsection

@section('script2')
<script>
    // Any additional JavaScript for approval configuration
</script>
@endsection


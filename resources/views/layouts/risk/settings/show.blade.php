@extends('layouts.risk.layout.app')

@section('title2')
<title>{{ ucfirst(str_replace('_', ' ', $optionType)) }} Configuration - JASIRI LIMS</title>
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
        border-left: 6px solid #dc3545;
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
<main>
    <?php
    $typeLabels = [
        'evaluation_result' => 'Evaluation Results',
        'acceptance_threshold_rpn' => 'Acceptance Threshold RPN',
        'risk_level' => 'Risk Levels',
        'implementation_status' => 'Implementation Statuses',
        'treatment_priority' => 'Treatment Priorities',
        'review_type' => 'Review Types',
        'review_decision' => 'Review Decisions',
        'closure_type' => 'Closure Types',
    ];
    $typeLabel = $typeLabels[$optionType] ?? ucfirst(str_replace('_', ' ', $optionType));
    $items = array(
        array('link' => route('risk.dashboard'), 'name' => 'Risk Management Dashboard', 'icon' => null),
        array('link' => route('risk.settings.index'), 'name' => 'Settings', 'icon' => null),
        array('link' => '#', 'name' => $typeLabel . ' Configuration', 'icon' => null)
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="card config-index-card mb-4">
        <div class="card-header config-index-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="mdi mdi-cog"></i> {{ $typeLabel }} Configuration
            </h4>
            <a href="{{ route('risk.settings.index') }}" class="btn btn-secondary btn-sm">
                <i class="mdi mdi-arrow-left"></i> Back to Settings
            </a>
        </div>
        <div class="card-body p-0">
            @livewire('risk-module.configuration-options-manager', ['optionType' => $optionType])
        </div>
    </div>
</main>
@endsection


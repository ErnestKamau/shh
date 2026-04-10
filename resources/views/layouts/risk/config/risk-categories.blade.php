@extends('layouts.risk.layout.app')

@section('title2')
<title>Risk Categories Configuration - JASIRI LIMS</title>
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
    $items = array(
        array('link' => route('risk.dashboard'), 'name' => 'Risk Management Dashboard', 'icon' => null),
        array('link' => '#', 'name' => 'Risk Categories Configuration', 'icon' => null)
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="card config-index-card mb-4">
        <div class="card-header config-index-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="mdi mdi-folder-multiple"></i> Risk Categories Configuration</h4>

        </div>
        <div class="card-body p-0">
            @livewire('risk-module.config-manager', ['type' => 'risk_categories'])
        </div>
    </div>
</main>
@endsection

@section('script2')
<script>
    // Listen for Livewire notifications
    document.addEventListener('livewire:init', () => {
        Livewire.on('notify', (event) => {
            showToast(event[0].message, event[0].type || 'success');
        });
    });
</script>
@endsection


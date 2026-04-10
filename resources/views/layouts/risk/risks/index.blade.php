@extends('layouts.risk.layout.app')

@section('title2')
<title>Risks - {{ $status }} - JASIRI LIMS</title>
<style type="text/css">
    .risk-index-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .risk-index-header {
        background: #f8f9fa;
        border: none;
        border-left: 6px solid #dc3545;
        padding: 18px 24px;
    }

    .risk-index-header h4 {
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
        array(
            'link' => route('risk.dashboard'),
            'name' => 'Risk Management Dashboard',
            'icon' => null
        ),
        array(
            'link' => route('risk.risks.index'),
            'name' => 'Risk Management',
            'icon' => null
        ),
        array(
            'link' => '#',
            'name' => $status,
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="card risk-index-card mb-4">
        <div class="card-header risk-index-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="mdi mdi-alert-octagon"></i> Risk Management - {{ $status }}</h4>
                <a href="{{ route('risk.risks.create') }}" class="btn btn-primary" style="border-radius: 8px; font-weight: 500;">
                    <i class="mdi mdi-plus"></i> New Risk
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @livewire('risk-module.risks-table', ['status' => $status])
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

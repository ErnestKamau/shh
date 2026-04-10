@extends('layouts.audit.layout.app')

@section('title2')
<title>Non-Conformances - {{ $status }} - JASIRI LIMS</title>
<style type="text/css">
    .nc-index-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .nc-index-header {
        background: #f8f9fa;
        border: none;
        border-left: 6px solid #dc3545;
        padding: 18px 24px;
    }

    .nc-index-header h4 {
        font-size: 1.35rem;
        font-weight: 600;
        color: #222;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .btn-modern {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.5rem 1.25rem;
        transition: all 0.3s ease;
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
</style>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => route('audit.dashboard'),
            'name' => 'Audit & CAPA Dashboard',
            'icon' => null
        ),
        array(
            'link' => route('audit.nc.index'),
            'name' => 'Non-Conformances',
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

    <!-- Header Card -->
    <div class="card nc-index-card mb-4">
        <div class="card-header nc-index-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">
                        <i class="mdi mdi-alert-octagon"></i> Non-Conformances
                        <span class="text-muted" style="font-size: 1rem; font-weight: 400;">- {{ $status }}</span>
                    </h4>
                </div>
                <div>
                    <a href="{{ route('audit.nc.create') }}" class="btn btn-modern btn-danger">
                        <i class="mdi mdi-plus"></i> New NC
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @livewire('audit-module.non-conformances-table', ['status' => $status])
        </div>
    </div>
</main>
@endsection

@section('script2')
@endsection

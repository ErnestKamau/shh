@extends('layouts.audit.layout.app')

@section('title2')
<title>Audits - {{ $status }} - JASIRI LIMS</title>
<style type="text/css">
    .audit-index-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .audit-index-header {
        background: #f8f9fa;
        border: none;
        border-left: 6px solid #007bff;
        padding: 18px 24px;
    }

    .audit-index-header h4 {
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
            'link' => route('audit.dashboard'),
            'name' => 'Audit & CAPA Dashboard',
            'icon' => null
        ),
        array(
            'link' => route('audit.audits.index'),
            'name' => 'Audit Management',
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

    <div class="card audit-index-card mb-4">
        <div class="card-header audit-index-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="mdi mdi-file-document-multiple"></i> Audit Management - {{ $status }}</h4>
                <a href="{{ route('audit.audits.create') }}" class="btn btn-primary" style="border-radius: 8px; font-weight: 500;">
                    <i class="mdi mdi-plus"></i> New Audit
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @livewire('audit-module.audits-table', ['status' => $status])
        </div>
    </div>
</main>
@endsection

@section('script2')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize popovers for status badge on hover
    function initializeStatusPopovers() {
        $('.status-hover-badge').each(function() {
            var $badge = $(this);
            var contentId = $badge.data('content-id');
            
            // Dispose existing popover if any (Bootstrap 4 uses 'dispose' not 'destroy')
            if ($badge.data('bs.popover')) {
                $badge.popover('dispose');
            }
            
            $badge.popover({
                html: true,
                placement: 'right',
                trigger: 'hover',
                container: 'body',
                title: function() {
                    return '<i class="mdi mdi-information"></i> Audit Details';
                },
                content: function() {
                    return $('#' + contentId).html();
                },
                template: '<div class="popover audit-details-popover-wrapper" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
            });
        });
    }
    
    // Initialize on page load
    initializeStatusPopovers();
    
    // Re-initialize after Livewire updates
    document.addEventListener('livewire:load', function() {
        initializeStatusPopovers();
    });
    
    document.addEventListener('livewire:update', function() {
        setTimeout(function() {
            initializeStatusPopovers();
        }, 100);
    });
});
</script>

<style>
.status-hover-badge:hover {
    opacity: 0.8;
    transform: scale(1.05);
    transition: all 0.2s;
}

.audit-details-popover-wrapper {
    max-width: 800px !important;
}

.audit-details-popover-wrapper .popover-body {
    max-height: 600px;
    overflow-y: auto;
    padding: 15px;
}

.audit-details-popover-wrapper .popover-header {
    background: linear-gradient(135deg, #1a73e8 0%, #4285f4 100%);
    color: white;
    border-bottom: none;
    font-weight: 600;
}

.audit-details-popover-wrapper .popover-header i {
    margin-right: 5px;
}
</style>
@endsection


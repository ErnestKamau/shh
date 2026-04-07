@extends('layouts.documents.layout.app')

@section('title2')
    <title>Documents - Imara LIMS</title>
    <style>
        .advanced-filters-section {
            transition: all 0.3s ease;
        }
        
        .form-control-lg, .form-select-lg {
            border-radius: 8px;
        }
        
        .input-group-text {
            border-radius: 8px 0 0 8px;
        }
        
        .input-group .form-control {
            border-radius: 0 8px 8px 0;
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
        }
    </style>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => '/home',
            'name' => 'Home',
            'icon' => null
        ),
        array(
            'link' => '/documents/dashboard',
            'name' => 'Documents',
            'icon' => null
        ),
    );
    
    if (request()->has('document_type_id')) {
        $documentType = \App\Models\DocumentType::find(request()->get('document_type_id'));
        if ($documentType) {
            $items[] = array(
                'link' => '/documents/types',
                'name' => 'Document Types',
                'icon' => null
            );
            $items[] = array(
                'link' => null,
                'name' => $documentType->name . ' Documents',
                'icon' => null
            );
        } else {
            $items[] = array(
                'link' => null,
                'name' => 'All Documents',
                'icon' => null
            );
        }
    } elseif (request()->has('status')) {
        $status = request()->get('status');
        $items[] = array(
            'link' => null,
            'name' => ucfirst($status) . ' Documents',
            'icon' => null
        );
    } elseif (request()->has('expiry_status')) {
        $expiryStatus = request()->get('expiry_status');
        $items[] = array(
            'link' => null,
            'name' => ucfirst(str_replace('_', ' ', $expiryStatus)) . ' Documents',
            'icon' => null
        );
    } else {
        $items[] = array(
            'link' => null,
            'name' => 'All Documents',
            'icon' => null
        );
    }
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <!-- View Toggle -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">
                <i class="mdi mdi-book-open-page-variant"></i> Documents
                @if(request()->has('document_type_id'))
                    @php
                        $documentType = \App\Models\DocumentType::find(request()->get('document_type_id'));
                    @endphp
                    @if($documentType)
                        <span class="text-muted">- {{ $documentType->name }}</span>
                    @endif
                @endif
                @if(request()->has('status'))
                    <span class="text-muted">- {{ ucfirst(request()->get('status')) }}</span>
                @endif
                @if(request()->has('expiry_status'))
                    <span class="text-muted">- {{ ucfirst(str_replace('_', ' ', request()->get('expiry_status'))) }}</span>
                @endif
            </h2>
        </div>
        <div class="d-flex" style="gap: 1rem;">
            <a href="{{ route('documents.dashboard') }}" class="btn btn-outline-primary">
                <i class="mdi mdi-view-dashboard"></i> Dashboard
            </a>
            <a href="{{ route('documents.index') }}" class="btn btn-primary">
                <i class="mdi mdi-view-list"></i> Table View
            </a>
        </div>
    </div>

    @livewire('documents.documents-table')
</main>
@endsection

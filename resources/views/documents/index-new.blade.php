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
        array(
            'link' => null,
            'name' => 'All Documents',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <!-- View Toggle -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">
                <i class="mdi mdi-book-open-page-variant"></i> Documents
                <span class="text-muted">- {{ $userDepartment->name }}</span>
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

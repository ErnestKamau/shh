@extends('layouts.documents.layout.app')

@section('title2')
    <title>{{ $documentType->name }} - Imara LIMS</title>
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
            'link' => '/documents/types',
            'name' => 'Document Types',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => $documentType->name,
            'icon' => null
        ),
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="mdi mdi-folder-text fs-2"></i>
                            </div>
                            <div>
                                <h3 class="mb-1 text-dark fw-bold d-flex align-items-center gap-2">
                                    {{ $documentType->name }}
                                    @if($documentType->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill fs-6 fw-normal px-3 py-1">Active</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill fs-6 fw-normal px-3 py-1">Inactive</span>
                                    @endif
                                </h3>
                                <div class="text-muted d-flex align-items-center gap-3 fs-6">
                                    <span><i class="mdi mdi-barcode ms-1"></i> Code: <strong>{{ $documentType->code }}</strong></span>
                                    @if($documentType->description)
                                    <span>|</span>
                                    <span class="text-truncate" style="max-width: 400px;" title="{{ $documentType->description }}">
                                        {{ $documentType->description }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('documents.types.index') }}" class="btn btn-light rounded-pill px-3">
                                <i class="mdi mdi-arrow-left"></i> Back to Types
                            </a>
                            <a href="{{ route('documents.types.edit', $documentType->id) }}" class="btn btn-warning text-dark rounded-pill px-3">
                                <i class="mdi mdi-pencil"></i> Edit Type
                            </a>
                            @if(($documentType->documents_count ?? 0) == 0)
                                <form action="{{ route('documents.types.destroy', $documentType->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this document type?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger rounded-pill px-3">
                                        <i class="mdi mdi-delete"></i> Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Livewire File Manager specifically scoped to this Document Type -->
    <div class="row">
        <div class="col-12">
            @livewire('documents.documents-table', ['currentNode' => 'type_' . $documentType->id, 'isTypeView' => true])
        </div>
    </div>
</main>
@endsection

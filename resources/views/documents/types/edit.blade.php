@extends('layouts.documents.layout.app')

@section('title2')
    <title>Edit Document Type - Imara LIMS</title>
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
            'name' => 'Edit',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <h2 class="p-1">
        <i class="mdi mdi-book-open-page-variant text-deep-orange"></i> Edit Document Type: {{ $documentType->name }}
    </h2><br>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('documents.types.update', $documentType->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Document Type Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $documentType->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Document Type Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                           id="code" name="code" value="{{ old('code', $documentType->code) }}" required>
                                    <small class="form-text text-muted">A unique code to identify this document type</small>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $documentType->description) }}</textarea>
                            <small class="form-text text-muted">Provide a brief description of what this document type is used for</small>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-end">
                            <a href="{{ route('documents.types.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i> Update Document Type
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
</main>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Ensure code is uppercase
        $('#code').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });
    });
</script>
@endsection

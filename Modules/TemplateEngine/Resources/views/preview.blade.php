@extends('layouts.lab.layout.app')

@section('content2')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{ route('templates.builder', $template->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Builder
            </a>
        </div>
    </div>

    <!-- This would reuse the rendering logic, potentially a Livewire component or Blade component -->
    <div class="card">
        <div class="card-header">
            <h4>{{ $template->name }}</h4>
            <p class="text-muted mb-0">{{ $template->description }}</p>
        </div>
        <div class="card-body">
            @livewire('template-engine::render-form', ['template' => $template])
        </div>
    </div>
</div>
@endsection

@extends('layouts.lab.layout.app', ['dataTable'=>false,'select2'=>false])

@section('title2')
  <title>Edit Supporting Document Template</title>
@endsection

@section('content2')
  <main>
    @include('layouts.lab.partials.lab-panel-theme-styles')
    <div class="container-fluid py-3">
      <div class="row mb-3">
        <div class="col-12">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><a href="{{ route('lab-home') }}">Lab Management</a></li>
              <li class="breadcrumb-item"><a href="#">Configurations</a></li>
              <li class="breadcrumb-item"><a href="{{ route('supporting-documents.templates.index') }}">Supporting Documents</a></li>
              <li class="breadcrumb-item active" aria-current="page">Template #{{ $templateId }}</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
    @livewire('supporting-documents.template-editor', ['templateId' => $templateId])
  </main>
@endsection


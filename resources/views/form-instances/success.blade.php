@extends('layouts.lab.layout.app')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="card">
        <div class="card-body text-center py-5">
          <div class="mb-4">
            <i class="mdi mdi-check-circle display-1 text-success"></i>
          </div>
          
          <h2 class="text-success mb-3">Form Submitted Successfully!</h2>
          
          <p class="text-muted mb-4">
            Thank you for submitting the <strong>{{ $instance->submissionForm->name }}</strong> form.
            Your submission has been received and will be reviewed.
          </p>
          
          <div class="alert alert-info">
            <strong>Submission ID:</strong> #{{ $instance->id }}<br>
            <strong>Submitted:</strong> {{ $instance->submitted_at->format('M j, Y g:i A') }}<br>
            <strong>Status:</strong> 
            <span class="badge bg-info">{{ ucwords(str_replace('_', ' ', $instance->status)) }}</span>
          </div>
          
          <div class="d-grid gap-2 d-md-block">
            <a href="{{ route('form-instances.show', $instance) }}" class="btn btn-primary">
              <i class="mdi mdi-eye"></i> View Submission
            </a>
            <a href="{{ route('forms.show', $instance->submissionForm->slug) }}" class="btn btn-outline-secondary">
              <i class="mdi mdi-plus"></i> Submit Another
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
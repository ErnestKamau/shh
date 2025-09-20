@extends('layouts.app')

@section('title', 'Simple Form Display - ' . $instance->submissionForm->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">
                    <i class="mdi mdi-file-document"></i> Simple Form Display
                </h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('submission-forms.index') }}">Forms</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('submission-forms.show', $instance->submissionForm) }}">{{ $instance->submissionForm->name }}</a></li>
                        <li class="breadcrumb-item active">Simple Display</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{-- Include the simple form display --}}
                    @include('submission-forms.partials.simple-form-display', ['instance' => $instance, 'formData' => $formData])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.audit.layout.app')

@section('title2')
<title>Edit Email Template - Audit Configuration</title>
@endsection

@section('content2')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="mdi mdi-email-edit"></i> Edit Email Template</h2>
        <a href="{{ route('audit.config.email-templates.index') }}" class="btn btn-outline-secondary">
            <i class="mdi mdi-arrow-left"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('audit.config.email-templates.update', $template->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="template_code">Template Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('template_code') is-invalid @enderror" 
                                   id="template_code" name="template_code" 
                                   value="{{ old('template_code', $template->template_code) }}" required>
                            <small class="form-text text-muted">Unique identifier (e.g., UPCOMING_AUDIT, OVERDUE_CAPA)</small>
                            @error('template_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Template Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" 
                                   value="{{ old('name', $template->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notification_type_id">Notification Type</label>
                    <select class="form-control @error('notification_type_id') is-invalid @enderror" 
                            id="notification_type_id" name="notification_type_id">
                        <option value="">Select Notification Type</option>
                        @foreach($notificationTypes as $type)
                            <option value="{{ $type->id }}" 
                                    {{ old('notification_type_id', $template->notification_type_id) == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('notification_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="subject">Email Subject <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                           id="subject" name="subject" 
                           value="{{ old('subject', $template->subject) }}" required>
                    <small class="form-text text-muted">
                        <strong>Placeholders:</strong> Use <code>@{{variable_name}}</code> format (e.g., <code>@{{audit_number}}</code>, <code>@{{lead_auditor}}</code>, <code>@{{scheduled_date}}</code>). 
                        These will be replaced with actual values when the email is sent.
                    </small>
                    @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="body">Email Body <span class="text-danger">*</span></label>
                    <textarea class="form-control editor @error('body') is-invalid @enderror" 
                              id="body" name="body" rows="15" required>{{ old('body', $template->body) }}</textarea>
                    <small class="form-text text-muted">
                        <strong>Placeholders:</strong> Use <code>@{{variable_name}}</code> format in your email body. 
                        <strong>Available variables:</strong> <code>@{{audit_number}}</code>, <code>@{{audit_title}}</code>, <code>@{{lead_auditor}}</code>, 
                        <code>@{{scheduled_date}}</code>, <code>@{{capa_number}}</code>, <code>@{{nc_number}}</code>, <code>@{{action_owner}}</code>, 
                        <code>@{{due_date}}</code>, <code>@{{days_overdue}}</code>, and more. <strong>HTML formatting is supported.</strong>
                    </small>
                    @error('body')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Active
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary" onclick="if(typeof tinyMCE !== 'undefined') { tinyMCE.triggerSave(); }">
                        <i class="mdi mdi-content-save"></i> Update Template
                    </button>
                    <a href="{{ route('audit.config.email-templates.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script2')
<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: 'textarea.editor',
        height: 400,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | formatselect | ' +
            'bold italic backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
    });
</script>
@endsection


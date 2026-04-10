@extends('layouts.audit.layout.app')

@section('title2')
<title>Preview Email Template - Audit Configuration</title>
@endsection

@section('content2')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="mdi mdi-eye"></i> Preview Email Template</h2>
        <div>
            <a href="{{ route('audit.config.email-templates.test', $template->id) }}" class="btn btn-warning">
                <i class="mdi mdi-email-send"></i> Send Test Email
            </a>
            <a href="{{ route('audit.config.email-templates.index') }}" class="btn btn-outline-secondary">
                <i class="mdi mdi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Template Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Template Code:</th>
                            <td><code>{{ $template->template_code }}</code></td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td>{{ $template->name }}</td>
                        </tr>
                        <tr>
                            <th>Notification Type:</th>
                            <td>{{ $template->notificationType->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($template->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5>Sample Variables Used</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        @foreach($sampleVariables as $key => $value)
                        <tr>
                            <th>{{ $key }}:</th>
                            <td>{{ $value }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Rendered Email Preview</h5>
                </div>
                <div class="card-body">
                    <div class="border p-3" style="background: #f9f9f9;">
                        <strong>Subject:</strong>
                        <div class="mb-3 p-2 bg-white border">{{ $rendered['subject'] }}</div>
                        
                        <strong>Body:</strong>
                        <div class="p-3 bg-white border" style="min-height: 400px;">
                            {!! nl2br(e($rendered['body'])) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script2')
@endsection









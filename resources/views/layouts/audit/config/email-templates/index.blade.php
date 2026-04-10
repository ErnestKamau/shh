@extends('layouts.audit.layout.app')

@section('title2')
<title>Email Templates - Audit Configuration - JASIRI LIMS</title>
<style type="text/css">
    .email-template-index-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .email-template-index-header {
        background: #f8f9fa;
        border: none;
        border-left: 6px solid #6c757d;
        padding: 18px 24px;
    }

    .email-template-index-header h4 {
        font-size: 1.35rem;
        font-weight: 600;
        color: #222;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .btn-modern {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.5rem 1.25rem;
        transition: all 0.3s ease;
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .table-modern {
        border-radius: 8px;
        overflow: hidden;
    }

    .table-modern thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 1rem;
    }

    .table-modern tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-top: 1px solid #f1f3f5;
    }

    .table-modern tbody tr:nth-child(even) {
        background: #f8f9fa;
    }

    .table-modern tbody tr:nth-child(odd) {
        background: #ffffff;
    }

    .table-modern tbody tr:hover {
        background: #e8f0fe !important;
    }
</style>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => route('audit.dashboard'),
            'name' => 'Audit & CAPA Dashboard',
            'icon' => null
        ),
        array(
            'link' => '#',
            'name' => 'Email Templates',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <!-- Header Card -->
    <div class="card email-template-index-card mb-4">
        <div class="card-header email-template-index-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">
                        <i class="mdi mdi-email-multiple"></i> Email Templates
                    </h4>
                </div>
                <div>
                    <a href="{{ route('audit.config.email-templates.create') }}" class="btn btn-modern btn-primary">
                        <i class="mdi mdi-plus"></i> Create Template
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive table-modern">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Template Code</th>
                            <th>Name</th>
                            <th>Notification Type</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                        <tr>
                            <td><code style="background: #f8f9fa; padding: 4px 8px; border-radius: 4px; font-size: 0.875rem;">{{ $template->template_code }}</code></td>
                            <td style="font-weight: 500;">{{ $template->name }}</td>
                            <td>{{ $template->notificationType->name ?? 'N/A' }}</td>
                            <td style="color: #6c757d;">{{ Str::limit($template->subject, 50) }}</td>
                            <td>
                                @if($template->is_active)
                                    <span class="badge badge-success" style="padding: 4px 12px; border-radius: 12px;">Active</span>
                                @else
                                    <span class="badge badge-secondary" style="padding: 4px 12px; border-radius: 12px;">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('audit.config.email-templates.preview', $template->id) }}" 
                                       class="btn btn-outline-info" title="Preview" style="border-radius: 4px;">
                                        <i class="mdi mdi-eye"></i>
                                    </a>
                                    <a href="{{ route('audit.config.email-templates.test', $template->id) }}" 
                                       class="btn btn-outline-warning" title="Send Test Email" style="border-radius: 4px;">
                                        <i class="mdi mdi-email-send"></i>
                                    </a>
                                    <a href="{{ route('audit.config.email-templates.edit', $template->id) }}" 
                                       class="btn btn-outline-primary" title="Edit" style="border-radius: 4px;">
                                        <i class="mdi mdi-pencil"></i>
                                    </a>
                                    <form action="{{ route('audit.config.email-templates.destroy', $template->id) }}" 
                                          method="POST" class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this template?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete" style="border-radius: 4px;">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="mdi mdi-email-outline" style="font-size: 48px; color: #dee2e6;"></i>
                                <p class="text-muted mt-3">No email templates found. <a href="{{ route('audit.config.email-templates.create') }}" style="color: #007bff;">Create one</a></p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script2')
@endsection

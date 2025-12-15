@extends('layouts.lab.layout.app', ['dataTable'=>true])

@section('content2')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="fas fa-file-alt text-primary"></i>
                                Form Templates
                            </h2>
                            <p class="text-muted mb-0">Manage form templates and builders</p>
                        </div>
                        <a href="{{ route('templates.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Template
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-body">
            @if($templates->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead style="background-color: rgba(0, 0, 0, .03);">
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                                <tr>
                                    <td><strong>{{ $template->name }}</strong></td>
                                    <td><span class="badge badge-info">{{ $template->category }}</span></td>
                                    <td>
                                        <span class="badge badge-{{ $template->status === 'published' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($template->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $template->creator->name ?? 'N/A' }}</td>
                                    <td>{{ $template->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('templates.builder', $template->id) }}" class="btn btn-sm btn-outline-info" title="Builder">
                                                <i class="fas fa-tools"></i>
                                            </a>
                                            <a href="{{ route('templates.preview', $template->id) }}" class="btn btn-sm btn-outline-secondary" title="Preview">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $templates->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-alt text-muted" style="font-size: 3rem;"></i>
                    <h5 class="text-muted mt-3">No templates found</h5>
                    <p class="text-muted">Create a new template to get started.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

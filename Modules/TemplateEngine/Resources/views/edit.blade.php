@extends('layouts.lab.layout.app', ['select2'=>true])

@section('title2')
<title>Edit Form Template - Template Engine</title>
@endsection

@section('content2')
<div class="container-fluid">
    <?php
    $breadcrumbItems = [
        [
            'link' => route('dashboard-lab'),
            'name' => 'Dashboard',
            'icon' => null
        ],
        [
            'link' => route('templates.index'),
            'name' => 'Form Templates',
            'icon' => null
        ],
        [
            'link' => '#',
            'name' => 'Edit Template',
            'icon' => null
        ]
    ];
    ?>
    <x-bread-crumb :items="$breadcrumbItems"></x-bread-crumb>

    <!-- Page Title Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="fas fa-edit text-primary"></i>
                                Edit Form Template
                            </h2>
                            <p class="text-muted mb-0">Update template metadata</p>
                        </div>
                        <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Templates
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <form action="{{ route('templates.update', $template->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group mb-3">
                            <label for="name" class="font-weight-bold">Template Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" required value="{{ old('name', $template->name) }}" placeholder="e.g. Employee Evaluation Form">
                        </div>

                        <div class="form-group mb-3">
                            <label for="type" class="font-weight-bold">Template Type <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-control">
                                <option value="form" {{ old('type', $template->type) === 'form' ? 'selected' : '' }}>Form (Data Entry)</option>
                                <option value="report" {{ old('type', $template->type) === 'report' ? 'selected' : '' }}>Report (Read Only / Print)</option>
                            </select>
                            <small class="text-muted">Form allows data submission. Report is for generating documents.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="category" class="font-weight-bold">Category</label>
                            <input type="text" name="category" id="category" class="form-control" value="{{ old('category', $template->category) }}" placeholder="e.g. HR, Operations">
                        </div>

                        <div class="form-group mb-3">
                            <label for="process_id" class="font-weight-bold">Linked Process (Optional)</label>
                            <select name="process_id" id="process_id" class="form-control select2">
                                <option value="">-- None --</option>
                                @foreach(getTemplateProcesses() as $key => $label)
                                    <option value="{{ $key }}" {{ (old('process_id', $template->process_type) == $key) ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select a process to link this template to</small>
                        </div>

                        <div class="form-group mb-4">
                            <label for="description" class="font-weight-bold">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Brief description of the template...">{{ old('description', $template->description) }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('templates.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

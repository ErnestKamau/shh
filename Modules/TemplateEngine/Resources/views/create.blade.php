@extends('layouts.lab.layout.app', ['select2'=>true])

@section('content2')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h3 class="card-title text-primary"><i class="fas fa-plus-circle"></i> Create New Template</h3>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('templates.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="name" class="font-weight-bold">Template Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" required placeholder="e.g. Employee Evaluation Form">
                        </div>

                        <div class="form-group mb-3">
                            <label for="category" class="font-weight-bold">Category</label>
                            <input type="text" name="category" id="category" class="form-control" placeholder="e.g. HR, Operations">
                        </div>

                        <div class="form-group mb-3">
                            <label for="process_id" class="font-weight-bold">Linked Process (Optional)</label>
                            <select name="process_id" id="process_id" class="form-control select2">
                                <option value="">-- None --</option>
                                @foreach($processes as $process)
                                    <option value="{{ $process->id }}">{{ $process->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-4">
                            <label for="description" class="font-weight-bold">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Brief description of the template..."></textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('templates.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Create & Open Builder</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

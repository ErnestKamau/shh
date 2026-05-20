@extends('layouts.lab.layout.app')

@section('title2')
  <title>Create Method Sequence | Lab Management</title>
  @include('layouts.lab.partials.lab-panel-theme-styles')
  @include('layouts.lab.partials.worksheet-engine-hub-styles')
  @include('layouts.lab.partials.worksheet-engine-module-pages')
@endsection

@section('content2')
  <main>
    <?php
      $items = [
        ['link' => route('lab-home'), 'name' => 'Lab', 'icon' => null],
        ['link' => route('formulars.index'), 'name' => 'Worksheet Engine', 'icon' => null],
        ['link' => route('stage-headers.index'), 'name' => 'Method sequences', 'icon' => null],
        ['link' => null, 'name' => 'Create configuration', 'icon' => null],
      ];
    ?>
    <div class="px-4 lab-panel-theme module-accent-purple">
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="container-fluid px-0">
      <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
          <h5>
            <i class="mdi mdi-plus"></i>
            Create method sequence
          </h5>
          <a href="{{ route('stage-headers.index') }}" class="btn btn-outline-secondary btn-action-sm">
            <i class="mdi mdi-arrow-left"></i> Back to list
          </a>
        </div>
        <div class="workflow-board-panel-body">
          <form method="POST" action="{{ route('stage-headers.store') }}">
            @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="control-label">Test Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="e.g., Salmonella 7-Day Test" required />
                </div>
                <div class="form-group">
                  <label class="control-label">Method <span class="text-danger">*</span></label>
                  <select class="form-control" name="method_id" required>
                    <option value="">Select Method...</option>
                    @foreach($methods as $method)
                      <option value="{{ $method->id }}" {{ old('method_id') == $method->id ? 'selected' : '' }}>
                        {{ $method->name }} ({{ $method->code }})
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group">
                  <label class="control-label">Analyte <span class="text-danger">*</span></label>
                  <select class="form-control" name="analyte_id" required>
                    <option value="">Select Analyte...</option>
                    @foreach($analytes as $analyte)
                      <option value="{{ $analyte->id }}" {{ old('analyte_id') == $analyte->id ? 'selected' : '' }}>
                        {{ $analyte->name }} ({{ $analyte->code }})
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="control-label">
                    <input type="checkbox" name="is_multi_stage" value="1" {{ old('is_multi_stage', 1) ? 'checked' : '' }} /> Multi-Stage Test
                  </label>
                </div>
              </div>
            </div>
            <div class="form-group mb-0">
              <button type="submit" class="btn btn-primary btn-action-sm"><i class="mdi mdi-content-save"></i> Create configuration</button>
              <a href="{{ route('stage-headers.index') }}" class="btn btn-outline-secondary btn-action-sm">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
    </div>
  </main>
@endsection

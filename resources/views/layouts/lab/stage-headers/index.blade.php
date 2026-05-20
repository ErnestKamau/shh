@extends('layouts.lab.layout.app', ['dataTable'=>true,'select2'=>true])

@section('title2')
  <title>Method Sequences | Lab Management</title>
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
        ['link' => null, 'name' => 'Method sequences', 'icon' => null],
      ];
    ?>
    <div class="px-4 lab-panel-theme module-accent-purple">
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="container-fluid px-0">
      <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
          <h5>
            <i class="mdi mdi-chart-timeline"></i>
            Method sequences
          </h5>
          <button type="button" class="btn btn-primary btn-action-sm" data-toggle="modal" data-target="#add-stage-header">
            <i class="mdi mdi-plus"></i> Add method sequence
          </button>
        </div>
        <div class="workflow-board-panel-body p-0">
          <div class="table-responsive">
            <table id="stage-headers-table" class="table workflow-table table-hover table-bordered mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Test Name</th>
                  <th>Method</th>
                  <th>Analyte</th>
                  <th>Stages</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($stageHeaders as $header)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $header->name }}</td>
                    <td>{{ $header->method->name ?? 'N/A' }}</td>
                    <td>{{ $header->analyte->name ?? 'N/A' }}</td>
                    <td>{{ $header->testStages->count() }}</td>
                    <td nowrap>
                      <a href="{{ route('stage-headers.show', $header->id) }}" class="btn btn-default text-primary btn-sm">
                        <i class="mdi mdi-eye" data-toggle="tooltip" title="View Stages"></i>
                      </a>
                      <a href="{{ route('stage-headers.edit', $header->id) }}" class="btn btn-default text-info btn-sm">
                        <i class="mdi mdi-pencil-outline" data-toggle="tooltip" title="Edit"></i>
                      </a>
                      <form action="{{ route('stage-headers.destroy', $header->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-default text-danger btn-sm" onclick="return confirm('Are you sure?')">
                          <i class="mdi mdi-delete-outline" data-toggle="tooltip" title="Delete"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    </div>
  </main>

  <!-- Add Stage Header Modal -->
  <div id="add-stage-header" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
      <form class="modal-content" method="POST" action="{{ route('stage-headers.store') }}">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Test Configuration</h4>
        </div>
        <div class="modal-body row">
          <div class="col-sm-8 offset-sm-2">
            <div class="form-group">
              <label class="control-label">Test Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="name" placeholder="e.g., Salmonella 7-Day Test" required />
            </div>
            <div class="form-group">
              <label class="control-label">Method <span class="text-danger">*</span></label>
              <select class="form-control no-select2 stage-header-modal-select" name="method_id" required>
                <option value="">Select Method...</option>
                @foreach(getMethods() as $method)
                  <option value="{{ $method->id }}">{{ $method->name }} ({{ $method->code }})</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label class="control-label">Analyte <span class="text-danger">*</span></label>
              <select class="form-control no-select2 stage-header-modal-select" name="analyte_id" required>
                <option value="">Select Analyte...</option>
                @foreach(\App\Analyte::all() as $analyte)
                  <option value="{{ $analyte->id }}">{{ $analyte->name }} ({{ $analyte->code }})</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label class="control-label">
                <input type="checkbox" name="is_multi_stage" value="1" checked /> Multi-Stage Test
              </label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@section('script2')
  <script>
    $(function () {
      $('#stage-headers-table').DataTable();

      var $addModal = $('#add-stage-header');

      function initStageHeaderModalSelects() {
        if (!$.fn.select2) {
          return;
        }

        $addModal.find('.stage-header-modal-select').each(function () {
          var $el = $(this);

          if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
          }

          $el.select2({
            width: '100%',
            placeholder: $el.find('option:first').text() || 'Select...',
            dropdownParent: $addModal,
          });
        });
      }

      function destroyStageHeaderModalSelects() {
        $addModal.find('.stage-header-modal-select').each(function () {
          if ($(this).hasClass('select2-hidden-accessible')) {
            $(this).select2('destroy');
          }
        });
      }

      $addModal.on('shown.bs.modal', initStageHeaderModalSelects);
      $addModal.on('hidden.bs.modal', destroyStageHeaderModalSelects);
    });
  </script>
@endsection

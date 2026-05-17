@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>{{ $stageHeader->name }} - Method Sequences</title>
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
        ['link' => null, 'name' => $stageHeader->name, 'icon' => null],
      ];
    ?>
    <div class="px-4 lab-panel-theme module-accent-purple">
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="container-fluid px-0">
      <div class="workflow-board-panel mb-3">
        <div class="workflow-board-panel-header">
          <h5>
            <i class="mdi mdi-information-outline"></i>
            Method sequence details
          </h5>
        </div>
        <div class="workflow-board-panel-body">
          <div class="row">
            <div class="col-md-3">
              <strong>Method:</strong> {{ $stageHeader->method->name ?? 'N/A' }}
            </div>
            <div class="col-md-3">
              <strong>Analyte:</strong> {{ $stageHeader->analyte->name ?? 'N/A' }}
            </div>
            <div class="col-md-3">
              <strong>Sample Type:</strong> {{ $stageHeader->sampleType->name ?? 'All' }}
            </div>
            <div class="col-md-3">
              <strong>Total Days:</strong> {{ $stageHeader->total_days }}
            </div>
          </div>
        </div>
      </div>

      <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
          <h5>
            <i class="mdi mdi-chart-timeline"></i>
            {{ $stageHeader->name }} — stages
          </h5>
          <button type="button" class="btn btn-primary btn-action-sm" data-toggle="modal" data-target="#add-stage-modal">
            <i class="mdi mdi-plus"></i> Add stage
          </button>
        </div>
        <div class="workflow-board-panel-body p-0">
          @if($stageHeader->testStages->count() > 0)
            <div class="table-responsive">
              <table class="table workflow-table table-hover table-bordered mb-0" style="width:110%" id="stages-table">
                <thead>
                  <tr>
                    <th width="30"><i class="mdi mdi-drag-vertical"></i></th>
                    <th>Actions</th>
                    <th>Order</th>
                    <th>Stage Name</th>
                    <th>Duration</th>
                    <th>Media Required</th>
                    <th>Diluents Required</th>
                    <th>Equipment Required</th>
                    <th>Controls Required</th>
                    <th>Is Result Stage</th>
                    <th>End if Pass</th>
                    <th>End if Fail</th>
                    <th>Is End Stage</th>
                  </tr>
                </thead>
                <tbody id="sortable-stages">
                  @foreach($stageHeader->testStages as $stage)
                    <tr data-stage-id="{{ $stage->id }}">
                      <td class="drag-handle" style="cursor: move;"><i class="mdi mdi-drag-vertical"></i></td>
                      <td style="width: 8% !important">
                        <div class="d-flex align-items-center" style="gap: 0.25rem;">
                          <button type="button" class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#edit-stage-modal" 
                                  data-stage-id="{{ $stage->id }}"
                                  data-order="{{ $stage->order }}"
                                  data-stage-name="{{ $stage->stage_name }}"
                                  data-duration='@json($stage->duration_hours)'
                                  data-safe-duration='@json($stage->safe_duration)'
                                  data-media='@json($stage->media_required)'
                                  data-diluents='@json($stage->diluents_required)'
                                  data-equipment='@json($stage->equipment_required)'
                                  data-controls='@json($stage->controls_required)'
                                  data-instructions="{{ $stage->instructions }}"
                                  data-is-result="{{ $stage->is_result_stage ? 1 : 0 }}"
                                  data-end-pass="{{ $stage->end_if_pass ? 1 : 0 }}"
                                  data-end-fail="{{ $stage->end_if_fail ? 1 : 0 }}"
                                  data-end-stage="{{ $stage->is_end_stage ? 1 : 0 }}">
                            <i class="mdi mdi-pencil"></i>
                          </button>
                          <button type="button" class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#delete-stage-modal"
                                  data-stage-id="{{ $stage->id }}"
                                  data-stage-name="{{ $stage->stage_name }}"
                                  data-duration="{{ $stage->duration_hours }}">
                            <i class="mdi mdi-delete"></i>
                          </button>
                        </div>
                      </td>
                      <td>{{ $stage->order }}</td>
                      <td>{{ $stage->stage_name }}</td>
                      <td>
                        @if($stage->safe_duration)
                          {{ $stage->safe_duration }} - {{ $stage->duration_hours }} hrs
                        @else
                          {{ $stage->duration_hours }} hrs
                        @endif
                      </td>
                      <td>
                        @if($stage->media_required)
                          @php
                            $mediaRequired = is_string($stage->media_required) ? json_decode($stage->media_required, true) : $stage->media_required;
                            if (is_array($mediaRequired)) {
                              $mediaIds = [];
                              foreach ($mediaRequired as $mediaItem) {
                                // Support old format (array of IDs) and new format (array of objects)
                                $mediaIds[] = is_array($mediaItem) ? $mediaItem['id'] : $mediaItem;
                              }
                              $mediaNames = \App\LabSubCategory::whereIn('id', $mediaIds)->pluck('name')->toArray();
                              echo implode(', ', $mediaNames);
                            } else {
                              echo is_array($stage->media_required) ? json_encode($stage->media_required) : $stage->media_required;
                            }
                          @endphp
                        @else
                          None
                        @endif
                      </td>
                      <td>
                        @if($stage->diluents_required)
                          @php
                            $diluentsRequired = is_string($stage->diluents_required) ? json_decode($stage->diluents_required, true) : $stage->diluents_required;
                            if (is_array($diluentsRequired)) {
                              $diluentIds = [];
                              foreach ($diluentsRequired as $diluentItem) {
                                $diluentIds[] = is_array($diluentItem) ? $diluentItem['id'] : $diluentItem;
                              }
                              $diluentNames = \App\LabSubCategory::whereIn('id', $diluentIds)->pluck('name')->toArray();
                              echo implode(', ', $diluentNames);
                            } else {
                              echo is_array($stage->diluents_required) ? json_encode($stage->diluents_required) : $stage->diluents_required;
                            }
                          @endphp
                        @else
                          None
                        @endif
                      </td>
                      <td>
                        @if($stage->equipment_required)
                          @php
                            $equipmentIds = is_string($stage->equipment_required) ? json_decode($stage->equipment_required, true) : $stage->equipment_required;
                            if (is_array($equipmentIds)) {
                              $equipmentNames = [];
                              foreach($equipmentIds as $equipId) {
                                // Equipment is still an array of raw IDs, but let's be safe
                                $id = is_array($equipId) ? $equipId['id'] : $equipId;
                                $equipment = getEquipmentById($id);
                                if ($equipment) {
                                  $equipmentNames[] = $equipment->name;
                                }
                              }
                              echo implode(', ', $equipmentNames);
                            } else {
                              echo is_array($stage->equipment_required) ? json_encode($stage->equipment_required) : $stage->equipment_required;
                            }
                          @endphp
                        @else
                          None
                        @endif
                      </td>
                      <td>
                        @if($stage->controls_required)
                          @php
                            $controlsRequired = is_string($stage->controls_required) ? json_decode($stage->controls_required, true) : $stage->controls_required;
                            if (is_array($controlsRequired)) {
                              $controlIds = [];
                              foreach ($controlsRequired as $controlItem) {
                                // Support old format (array of IDs) and new format (array of objects)
                                $controlIds[] = is_array($controlItem) ? $controlItem['id'] : $controlItem;
                              }
                              $controlNames = \App\LabSubCategory::whereIn('id', $controlIds)->pluck('name')->toArray();
                              echo implode(', ', $controlNames);
                            } else {
                              echo is_array($stage->controls_required) ? json_encode($stage->controls_required) : $stage->controls_required;
                            }
                          @endphp
                        @else
                          None
                        @endif
                      </td>
                      <td>
                        @if($stage->is_result_stage)
                          <span class="badge" style="background-color: #d4edda; color: #155724; border-radius: 4px; padding: 5px 10px;">Yes</span>
                        @else
                          <span class="badge badge-secondary" style="border-radius: 4px; padding: 5px 10px;">No</span>
                        @endif
                      </td>
                      <td>
                        @if($stage->end_if_pass)
                          <span class="badge" style="background-color: #d4edda; color: #155724; border-radius: 4px; padding: 5px 10px;">Yes</span>
                        @else
                          <span class="badge badge-secondary" style="border-radius: 4px; padding: 5px 10px;">No</span>
                        @endif
                      </td>
                      <td>
                        @if($stage->end_if_fail)
                          <span class="badge" style="background-color: #d4edda; color: #155724; border-radius: 4px; padding: 5px 10px;">Yes</span>
                        @else
                          <span class="badge badge-secondary" style="border-radius: 4px; padding: 5px 10px;">No</span>
                        @endif
                      </td>
                      <td>
                        @if($stage->is_end_stage)
                          <span class="badge" style="background-color: #d4edda; color: #155724; border-radius: 4px; padding: 5px 10px;">Yes</span>
                        @else
                          <span class="badge badge-secondary" style="border-radius: 4px; padding: 5px 10px;">No</span>
                        @endif
                      </td>
                     
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="alert alert-info">
              <i class="mdi mdi-information"></i> No stages configured yet. Add stages to complete this test configuration.
            </div>
          @endif
        </div>
      </div>
    </div>
    </div>
  </main>


@endsection

@section('script2')
  <!-- Add Stage Modal -->
  <div id="add-stage-modal" class="modal fade lab-panel-theme module-accent-purple" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-xl">
      <form class="modal-content" method="POST" action="{{ route('stage-headers.add-stage', $stageHeader->id) }}" id="add-stage-form" novalidate>
        @csrf
        <div class="modal-body p-0">
          
          <!-- Stepper Navigation -->
          <div class="d-flex bg-white border-bottom p-3 justify-content-center" style="gap: 2rem;">
            <div class="step-nav active" id="nav-step-1" style="font-weight: bold; color: #007bff; border-bottom: 3px solid #007bff; padding-bottom: 0.5rem; cursor: pointer;">
              1. Stage Info
            </div>
            <div class="step-nav text-muted" id="nav-step-2" style="font-weight: bold; padding-bottom: 0.5rem; cursor: pointer;">
              2. Solutions used (Media & Controls)
            </div>
          </div>

          <div class="p-4" style="background: #f8f9fa;">
            <!-- STEP 1: Stage Info -->
            <div id="step-1-content">
              <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                  <div class="row">
                    <div class="col-sm-2">
                      <div class="form-group">
                        <label class="font-weight-bold">Order No <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="order" min="1" max="{{ $stageHeader->total_days }}" value="{{ $stageHeader->testStages->count() + 1 }}" readonly />
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label class="font-weight-bold">Stage Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="stage_name" placeholder="e.g., Primary Enrichment" required />
                      </div>
                    </div>
                    <div class="col-sm-2">
                      <div class="form-group">
                        <label class="font-weight-bold">Duration (Hrs) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="duration_hours" min="1" value="24" required />
                      </div>
                    </div>
                    <div class="col-sm-2">
                      <div class="form-group">
                        <label class="font-weight-bold">Safe Duration</label>
                        <input type="number" class="form-control" name="safe_duration" min="1" placeholder="Optional" />
                      </div>
                    </div>
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label class="font-weight-bold">Equipment Required</label>
                        <select class="form-control no-select2 select2-multiple stage-modal-equipment" name="equipment_required[]" multiple="multiple" style="width: 100%;">
                          @foreach($equipments as $equip)
                            <option value="{{ $equip->id }}">{{ $equip->name }} ({{ $equip->equipment_number }})</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                    <div class="col-sm-12">
                      <div class="form-group mb-0">
                        <label class="font-weight-bold">Instructions</label>
                        <textarea class="form-control" name="instructions" rows="2" placeholder="Special instructions for this stage..."></textarea>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="card shadow-sm border-0">
                <div class="card-body">
                  <h6 class="font-weight-bold text-muted mb-3">Stage Behaviors & Triggers</h6>
                  <div class="row">
                    <div class="col-sm-3 col-6 mb-3">
                      <div class="custom-control custom-switch custom-switch-lg">
                        <input type="checkbox" class="custom-control-input" id="is_result_stage" name="is_result_stage" value="1">
                        <label class="custom-control-label font-weight-bold" for="is_result_stage">Is Result Stage</label>
                      </div>
                      <small class="form-text text-muted">Requires result recording.</small>
                    </div>
                    <div class="col-sm-3 col-6 mb-3">
                      <div class="custom-control custom-switch custom-switch-lg">
                        <input type="checkbox" class="custom-control-input" id="end_if_pass" name="end_if_pass" value="1">
                        <label class="custom-control-label text-success font-weight-bold" for="end_if_pass">End if Pass</label>
                      </div>
                      <small class="form-text text-muted">Sequence stops if pass.</small>
                    </div>
                    <div class="col-sm-3 col-6 mb-3">
                      <div class="custom-control custom-switch custom-switch-lg">
                        <input type="checkbox" class="custom-control-input" id="end_if_fail" name="end_if_fail" value="1">
                        <label class="custom-control-label text-danger font-weight-bold" for="end_if_fail">End if Fail</label>
                      </div>
                      <small class="form-text text-muted">Sequence stops if fail.</small>
                    </div>
                    <div class="col-sm-3 col-6 mb-3">
                      <div class="custom-control custom-switch custom-switch-lg">
                        <input type="checkbox" class="custom-control-input" id="is_end_stage" name="is_end_stage" value="1">
                        <label class="custom-control-label text-warning font-weight-bold" for="is_end_stage">Is End Stage</label>
                      </div>
                      <small class="form-text text-muted">Marks final step always.</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- STEP 2: Solutions Used -->
            <div id="step-2-content" style="display: none;">
              
              <!-- Sleek Tabs Navigation -->
              <ul class="nav nav-pills nav-fill mb-4 shadow-sm" id="solutions-tabs" role="tablist" style="border-radius: 10px; background: #fff; padding: 5px;">
                <li class="nav-item">
                  <a class="nav-link active font-weight-bold" id="controls-tab" data-toggle="pill" href="#controls-panel" role="tab" style="border-radius: 8px;">
                     <i class="mdi mdi-microscope mr-1"></i> Control Organisms
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link font-weight-bold" id="media-tab" data-toggle="pill" href="#media-panel" role="tab" style="border-radius: 8px;">
                     <i class="mdi mdi-flask mr-1"></i> Uninoculated Media
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link font-weight-bold" id="diluent-tab" data-toggle="pill" href="#diluent-panel" role="tab" style="border-radius: 8px;">
                     <i class="mdi mdi-water mr-1"></i> Uninoculated Diluent
                  </a>
                </li>
              </ul>

              <!-- Tabs Content -->
              <div class="tab-content border-0 p-0" id="solutions-tabs-content">
                
                <!-- Controls Panel -->
                <div class="tab-pane fade show active" id="controls-panel" role="tabpanel">
                  <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center border-0 pt-4 pb-2">
                      <h6 class="mb-0 text-muted">Select organisms required for this stage</h6>
                      <button type="button" class="btn btn-sm btn-primary rounded-pill shadow-sm px-3" id="add-control-row">
                        <i class="mdi mdi-plus"></i> Add Control
                      </button>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table table-hover mb-0" id="controls-table">
                          <thead class="bg-light text-muted">
                            <tr>
                              <th width="40%" class="border-top-0">Control</th>
                              <th width="30%" class="border-top-0">Result Nature</th>
                              <th width="15%" class="text-center border-top-0">Mandatory</th>
                              <th width="10%" class="text-center border-top-0">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <!-- Rows added by JS -->
                          </tbody>
                        </table>
                      </div>
                      <div class="text-center p-4 text-muted empty-controls-msg" style="display:none; background: #fafafa;">
                         <i class="mdi mdi-flask-empty-outline" style="font-size: 24px; color: #ccc;"></i><br>
                        No controls added yet. Click "Add Control" above.
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Media Panel -->
                <div class="tab-pane fade" id="media-panel" role="tabpanel">
                  <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center border-0 pt-4 pb-2">
                      <h6 class="mb-0 text-muted">Select media required for this stage</h6>
                      <button type="button" class="btn btn-sm btn-primary rounded-pill shadow-sm px-3" id="add-media-row">
                        <i class="mdi mdi-plus"></i> Add Media
                      </button>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table table-hover mb-0" id="media-table">
                          <thead class="bg-light text-muted">
                            <tr>
                              <th width="40%" class="border-top-0">Uninoculated Media</th>
                              <th width="30%" class="border-top-0">Result Nature</th>
                              <th width="15%" class="text-center border-top-0">Mandatory</th>
                              <th width="10%" class="text-center border-top-0">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <!-- Rows added by JS -->
                          </tbody>
                        </table>
                      </div>
                      <div class="text-center p-4 text-muted empty-media-msg" style="display:none; background: #fafafa;">
                         <i class="mdi mdi-flask-empty-outline" style="font-size: 24px; color: #ccc;"></i><br>
                        No media added yet. Click "Add Media" above.
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Diluent Panel -->
                <div class="tab-pane fade" id="diluent-panel" role="tabpanel">
                  <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center border-0 pt-4 pb-2">
                      <h6 class="mb-0 text-muted">Select diluents required for this stage</h6>
                      <button type="button" class="btn btn-sm btn-primary rounded-pill shadow-sm px-3" id="add-diluent-row">
                        <i class="mdi mdi-plus"></i> Add Diluent
                      </button>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table table-hover mb-0" id="diluent-table">
                          <thead class="bg-light text-muted">
                            <tr>
                              <th width="25%" class="border-top-0">Uninoculated Diluent</th>
                              <th width="20%" class="border-top-0">Result Nature</th>
                              <th width="15%" class="border-top-0">Volume</th>
                              <th width="15%" class="border-top-0">Concentration</th>
                              <th width="15%" class="text-center border-top-0">Mandatory</th>
                              <th width="10%" class="text-center border-top-0">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <!-- Rows added by JS -->
                          </tbody>
                        </table>
                      </div>
                      <div class="text-center p-4 text-muted empty-diluent-msg" style="display:none; background: #fafafa;">
                        <i class="mdi mdi-water-outline" style="font-size: 24px; color: #ccc;"></i><br>
                        No diluents added yet. Click "Add Diluent" above.
                      </div>
                    </div>
                  </div>
                </div>

              </div>

            </div>
          </div>
        </div>
        
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary mr-auto" data-dismiss="modal">Close</button>
          
          <button type="button" class="btn btn-secondary" id="btn-prev-step" style="display:none;">
             <i class="mdi mdi-arrow-left"></i> Previous
          </button>
          
          <button type="button" class="btn btn-primary" id="btn-next-step">
            Next Section <i class="mdi mdi-arrow-right"></i>
          </button>
          
          <button type="submit" class="btn btn-success" id="btn-save-stage" style="display:none;">
            <i class="mdi mdi-content-save"></i> Save Complete Stage
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Stage Modal -->
  <div id="delete-stage-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
              <div class="modal-body p-5 text-center">
                  <div class="mb-4">
                      <i class="mdi mdi-alert-outline" style="font-size: 60px; color: #ff6b6b;"></i>
                  </div>
                  <h4 class="font-weight-bold mb-3" style="color: #4a4a4a;">Delete this Stage?</h4>
                  <p class="text-muted mb-4" style="font-size: 15px;">
                      You are about to permanently delete the stage sequence below. This action cannot be undone.
                  </p>
                  
                  <div class="text-left p-3 mb-4 rounded" style="background-color: #fff8f8; border: 1px solid #ffcccc; color: #d9534f;">
                      <p class="mb-1"><strong>Stage Name:</strong> <span id="delete-stage-name-display" class="font-weight-bold"></span></p>
                      <p class="mb-0"><strong>Duration:</strong> <span id="delete-stage-duration-display" class="font-weight-bold"></span> hours</p>
                  </div>
                  
                  <div class="d-flex justify-content-center mt-4" style="gap: 15px;">
                      <button type="button" class="btn btn-light px-4 py-2 font-weight-bold shadow-sm" data-dismiss="modal" style="border-radius: 8px; color: #6c757d;">
                          Cancel
                      </button>
                      <form id="delete-stage-form" method="POST" action="/stage-headers/remove-stage" style="display:inline-block; margin: 0;">
                          @csrf
                          <input type="hidden" name="stage_id" id="delete-stage-id-input">
                          <button type="submit" class="btn px-4 py-2 font-weight-bold shadow-sm" style="border-radius: 8px; background-color: #ff6b6b; color: white; border: none;">
                              <i class="mdi mdi-delete-outline mr-1"></i> Yes, Delete
                          </button>
                      </form>
                  </div>
              </div>
          </div>
      </div>
  </div>

  <!-- Edit Stage Modal -->
  <div id="edit-stage-modal" class="modal fade lab-panel-theme module-accent-purple" role="dialog">
    <div class="modal-dialog modal-xl">
      <form class="modal-content" method="POST" id="edit-stage-form" novalidate>
        @csrf
        @method('PUT')
        <div class="modal-body p-0">
          
          <!-- Stepper Navigation -->
          <div class="d-flex bg-white border-bottom p-3 justify-content-center" style="gap: 2rem;">
            <div class="step-nav active" id="edit-nav-step-1" style="font-weight: bold; color: #007bff; border-bottom: 3px solid #007bff; padding-bottom: 0.5rem; cursor: pointer;">
              1. Edit Stage Info
            </div>
            <div class="step-nav text-muted" id="edit-nav-step-2" style="font-weight: bold; padding-bottom: 0.5rem; cursor: pointer;">
              2. Solutions used (Media & Controls)
            </div>
          </div>

          <div class="p-4" style="background-color: #f8f9fa;">
            
            <!-- STEP 1: Edit Stage Info -->
            <div id="edit-step-1-content">
              <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                  <div class="row">
                    <div class="col-sm-2">
                      <div class="form-group">
                        <label class="font-weight-bold">Order No <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="order" id="edit-order" min="1" max="{{ $stageHeader->total_days }}" required />
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label class="font-weight-bold">Stage Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="stage_name" id="edit-stage-name" required />
                      </div>
                    </div>
                    <div class="col-sm-2">
                      <div class="form-group">
                        <label class="font-weight-bold">Duration (Hrs) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="duration_hours" id="edit-duration" min="1" required />
                      </div>
                    </div>
                    <div class="col-sm-2">
                      <div class="form-group">
                        <label class="font-weight-bold">Safe Duration</label>
                        <input type="number" class="form-control" name="safe_duration" id="edit-safe-duration" min="1" placeholder="Optional" />
                      </div>
                    </div>
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label class="font-weight-bold">Equipment Required</label>
                        <select class="form-control no-select2 select2-multiple-edit stage-modal-equipment" name="equipment_required[]" id="edit-equipment" multiple="multiple" style="width: 100%;">
                          @foreach($equipments as $equip)
                            <option value="{{ $equip->id }}">{{ $equip->name }} ({{ $equip->equipment_number }})</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                    <div class="col-sm-12">
                      <div class="form-group mb-0">
                        <label class="font-weight-bold">Instructions</label>
                        <textarea class="form-control" name="instructions" id="edit-instructions" rows="2" placeholder="Specific instructions for this stage..."></textarea>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="card shadow-sm border-0">
                <div class="card-body">
                  <h6 class="font-weight-bold text-muted mb-3">Stage Behaviors & Triggers</h6>
                  <div class="row">
                    <div class="col-sm-3 col-6 mb-3">
                      <div class="custom-control custom-switch custom-switch-lg">
                        <input type="checkbox" class="custom-control-input" id="edit-is-result" name="is_result_stage" value="1">
                        <label class="custom-control-label font-weight-bold" for="edit-is-result">Is Result Stage</label>
                      </div>
                      <small class="form-text text-muted">Requires result recording.</small>
                    </div>
                    <div class="col-sm-3 col-6 mb-3">
                      <div class="custom-control custom-switch custom-switch-lg">
                        <input type="checkbox" class="custom-control-input" id="edit-end-pass" name="end_if_pass" value="1">
                        <label class="custom-control-label text-success font-weight-bold" for="edit-end-pass">End if Pass</label>
                      </div>
                      <small class="form-text text-muted">Sequence stops if pass.</small>
                    </div>
                    <div class="col-sm-3 col-6 mb-3">
                      <div class="custom-control custom-switch custom-switch-lg">
                        <input type="checkbox" class="custom-control-input" id="edit-end-fail" name="end_if_fail" value="1">
                        <label class="custom-control-label text-danger font-weight-bold" for="edit-end-fail">End if Fail</label>
                      </div>
                      <small class="form-text text-muted">Sequence stops if fail.</small>
                    </div>
                    <div class="col-sm-3 col-6 mb-3">
                      <div class="custom-control custom-switch custom-switch-lg">
                        <input type="checkbox" class="custom-control-input" id="edit-end-stage" name="is_end_stage" value="1">
                        <label class="custom-control-label text-warning font-weight-bold" for="edit-end-stage">Is End Stage</label>
                      </div>
                      <small class="form-text text-muted">Marks final step always.</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- STEP 2: Edit Solutions Used -->
            <div id="edit-step-2-content" style="display: none;">
              <!-- Sleek Tabs Navigation -->
              <ul class="nav nav-pills nav-fill mb-4 shadow-sm" id="edit-solutions-tabs" role="tablist" style="border-radius: 10px; background: #fff; padding: 5px;">
                <li class="nav-item">
                  <a class="nav-link active font-weight-bold" id="edit-controls-tab-nav" data-toggle="pill" href="#edit-controls-panel" role="tab" style="border-radius: 8px;">
                    <i class="mdi mdi-microscope mr-1"></i> Control Organisms
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link font-weight-bold" id="edit-media-tab-nav" data-toggle="pill" href="#edit-media-panel" role="tab" style="border-radius: 8px;">
                    <i class="mdi mdi-flask mr-1"></i> Uninoculated Media
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link font-weight-bold" id="edit-diluent-tab-nav" data-toggle="pill" href="#edit-diluent-panel" role="tab" style="border-radius: 8px;">
                    <i class="mdi mdi-water mr-1"></i> Uninoculated Diluent
                  </a>
                </li>
              </ul>

              <!-- Tabs Content -->
              <div class="tab-content border-0 p-0" id="edit-solutions-tabs-content">
                
                <!-- Controls Panel -->
                <div class="tab-pane fade show active" id="edit-controls-panel" role="tabpanel">
                  <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center border-0 pt-4 pb-2">
                      <h6 class="mb-0 text-muted">Select organisms required for this stage</h6>
                      <button type="button" class="btn btn-sm btn-primary rounded-pill shadow-sm px-3" id="edit-add-control-row">
                        <i class="mdi mdi-plus"></i> Add Control
                      </button>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table table-hover mb-0" id="edit-controls-table">
                          <thead class="bg-light text-muted">
                            <tr>
                              <th width="40%" class="border-top-0">Control</th>
                              <th width="30%" class="border-top-0">Result Nature</th>
                              <th width="15%" class="text-center border-top-0">Mandatory</th>
                              <th width="10%" class="text-center border-top-0">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <!-- Rows added by JS -->
                          </tbody>
                        </table>
                      </div>
                      <div class="text-center p-4 text-muted edit-empty-controls-msg" style="display:none; background: #fafafa;">
                         <i class="mdi mdi-flask-empty-outline" style="font-size: 24px; color: #ccc;"></i><br>
                        No controls added yet. Click "Add Control" above.
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Media Panel -->
                <div class="tab-pane fade" id="edit-media-panel" role="tabpanel">
                  <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center border-0 pt-4 pb-2">
                      <h6 class="mb-0 text-muted">Select media required for this stage</h6>
                      <button type="button" class="btn btn-sm btn-primary rounded-pill shadow-sm px-3" id="edit-add-media-row">
                        <i class="mdi mdi-plus"></i> Add Media
                      </button>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table table-hover mb-0" id="edit-media-table">
                          <thead class="bg-light text-muted">
                            <tr>
                              <th width="40%" class="border-top-0">Uninoculated Media</th>
                              <th width="30%" class="border-top-0">Result Nature</th>
                              <th width="15%" class="text-center border-top-0">Mandatory</th>
                              <th width="10%" class="text-center border-top-0">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <!-- Rows added by JS -->
                          </tbody>
                        </table>
                      </div>
                      <div class="text-center p-4 text-muted edit-empty-media-msg" style="display:none; background: #fafafa;">
                         <i class="mdi mdi-flask-empty-outline" style="font-size: 24px; color: #ccc;"></i><br>
                        No media added yet. Click "Add Media" above.
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Diluent Panel -->
                <div class="tab-pane fade" id="edit-diluent-panel" role="tabpanel">
                  <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center border-0 pt-4 pb-2">
                      <h6 class="mb-0 text-muted">Select diluents required for this stage</h6>
                      <button type="button" class="btn btn-sm btn-primary rounded-pill shadow-sm px-3" id="edit-add-diluent-row">
                        <i class="mdi mdi-plus"></i> Add Diluent
                      </button>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table table-hover mb-0" id="edit-diluent-table">
                          <thead class="bg-light text-muted">
                            <tr>
                              <th width="25%" class="border-top-0">Uninoculated Diluent</th>
                              <th width="20%" class="border-top-0">Result Nature</th>
                              <th width="15%" class="border-top-0">Volume</th>
                              <th width="15%" class="border-top-0">Concentration</th>
                              <th width="15%" class="text-center border-top-0">Mandatory</th>
                              <th width="10%" class="text-center border-top-0">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <!-- Rows added by JS -->
                          </tbody>
                        </table>
                      </div>
                      <div class="text-center p-4 text-muted edit-empty-diluent-msg" style="display:none; background: #fafafa;">
                        <i class="mdi mdi-water-outline" style="font-size: 24px; color: #ccc;"></i><br>
                        No diluents added yet. Click "Add Diluent" above.
                      </div>
                    </div>
                  </div>
                </div>

              </div>

            </div>
          </div>
        </div>
        
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary mr-auto" data-dismiss="modal">Close</button>
          
          <button type="button" class="btn btn-secondary" id="edit-btn-prev-step" style="display:none;">
             <i class="mdi mdi-arrow-left"></i> Previous
          </button>
          
          <button type="button" class="btn btn-primary" id="edit-btn-next-step">
            Next Section <i class="mdi mdi-arrow-right"></i>
          </button>
          
          <button type="submit" class="btn btn-success" id="edit-btn-save-stage" style="display:none;">
            <i class="mdi mdi-content-save"></i> Save Complete Stage
          </button>
        </div>
      </form>
    </div>
  </div>
<!-- Include SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
  $(function(){
    function initModalEquipmentSelect($modal, $select) {
      if (!$.fn.select2 || !$select.length) {
        return;
      }

      $select.each(function () {
        var $el = $(this);

        if ($el.hasClass('select2-hidden-accessible')) {
          $el.select2('destroy');
        }

        $el.select2({
          placeholder: 'Select options...',
          allowClear: true,
          width: '100%',
          dropdownParent: $modal,
        });
      });
    }

    function destroyModalEquipmentSelect($select) {
      $select.each(function () {
        if ($(this).hasClass('select2-hidden-accessible')) {
          $(this).select2('destroy');
        }
      });
    }

    var $addStageModal = $('#add-stage-modal');
    var $editStageModal = $('#edit-stage-modal');

    $addStageModal.on('shown.bs.modal', function () {
      initModalEquipmentSelect($addStageModal, $addStageModal.find('select.stage-modal-equipment'));
    });

    $addStageModal.on('hidden.bs.modal', function () {
      destroyModalEquipmentSelect($addStageModal.find('select.stage-modal-equipment'));
    });

    var debugSubmit = (new URLSearchParams(window.location.search)).has('debugDiluent') || (new URLSearchParams(window.location.search)).has('debugForm');

    // Destroy DataTables if it was initialized on our stages table
    if ($.fn.DataTable.isDataTable('#stages-table')) {
      $('#stages-table').DataTable().destroy();
    }

    // Initialize SortableJS for drag and drop
    var sortableList = document.getElementById('sortable-stages');
    if (sortableList) {
      var sortable = new Sortable(sortableList, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: function(evt) {
          // Get all stage IDs in the new order
          var stageIds = [];
          $('#sortable-stages tr').each(function(index) {
            stageIds.push({
              id: $(this).data('stage-id'),
              order: index + 1
            });
          });

          // Send AJAX request to update order
          $.ajax({
            url: '{{ route('stage-headers.reorder-stages', $stageHeader->id) }}',
            method: 'POST',
            data: {
              _token: '{{ csrf_token() }}',
              stages: stageIds
            },
            success: function(response) {
              // Update the order column in the table
              $('#sortable-stages tr').each(function(index) {
                $(this).find('td:eq(1)').text(index + 1);
              });
              
              // Show success message (using console for less intrusive feedback)
              console.log('Stage order updated successfully!');
            },
            error: function(xhr) {
              console.error('Error updating stage order:', xhr);
              var errorMessage = 'Error updating stage order. Please try again.';
              
              if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
              } else if (xhr.responseText) {
                try {
                  var response = JSON.parse(xhr.responseText);
                  errorMessage = response.message || errorMessage;
                } catch (e) {
                  console.error('Response text:', xhr.responseText);
                }
              }
              
              alert(errorMessage);
              // Reload page to reset order
              location.reload();
            }
          });
        }
      });
    }

    // Common options for both modals
    var mediaOptionsHtml = `{!! $mediaItems->map(fn($m) => "<option value='{$m->id}'>" . addslashes(e($m->name)) . "</option>")->implode("") !!}`;
    var controlsOptionsHtml = `{!! $controlItems->map(fn($c) => "<option value='{$c->id}'>" . addslashes(e($c->name)) . "</option>")->implode("") !!}`;

    console.log('--- GLOBAL OPTION TEMPLATES ---');
    console.log('Media Options Sample:', mediaOptionsHtml.substring(0, 100));
    console.log('Control Options Sample:', controlsOptionsHtml.substring(0, 100));

    // Handle edit modal population
    $('#edit-stage-modal').on('show.bs.modal', function (e) {
      // (Original edit logic remains unchanged here for brevity, but a similar stepper could be added later if needed)
      var $button = $(e.relatedTarget);
      
      var stageId = $button.data('stage-id');
      var order = $button.data('order');
      var stageName = $button.data('stage-name');
      var duration = $button.data('duration');
      var safeDuration = $button.data('safe-duration');
      var media = $button.data('media');
      var diluents = $button.data('diluents');
      var equipment = $button.data('equipment');
      var controls = $button.data('controls');
      var instructions = $button.data('instructions');
      var isResult = $button.data('is-result');
      var endPass = $button.data('end-pass');
      var endFail = $button.data('end-fail');
      var endStage = $button.data('end-stage');

      console.log('--- EDIT MODAL DEBUG START ---');
      console.log('Stage ID:', stageId);
      console.log('Media Data:', media);
      console.log('Controls Data:', controls);
      console.log('Diluents Data:', diluents);
      console.log('Equipment Data:', equipment);

      // Set form action URL
      var updateUrl = @json(route('stage-headers.update-stage', ['stageHeader' => $stageHeader->id, 'testStage' => '__STAGE__']));
      $('#edit-stage-form').attr('action', updateUrl.replace('__STAGE__', stageId));

      // Populate text fields
      $('#edit-order').val(order);
      $('#edit-stage-name').val(stageName);
      var durationValue = duration;
      if (durationValue !== null && typeof durationValue === 'object') {
        durationValue = Array.isArray(durationValue) ? durationValue[0] : Object.values(durationValue)[0];
      }
      $('#edit-duration').val(durationValue ?? '');
      $('#edit-safe-duration').val(safeDuration);
      $('#edit-instructions').val(instructions);

      // Populate checkboxes
      $('#edit-is-result').prop('checked', isResult == 1);
      $('#edit-end-pass').prop('checked', endPass == 1);
      $('#edit-end-fail').prop('checked', endFail == 1);
      $('#edit-end-stage').prop('checked', endStage == 1);

      // Parse and populate equipment BEFORE initializing Select2
      var equipmentIds = [];
      if (equipment) {
        try {
          var equipmentArray = (typeof equipment === 'string') ? JSON.parse(equipment) : equipment;
          
          // Handle double-encoded JSON for equipment too
          if (typeof equipmentArray === 'string') {
            equipmentArray = JSON.parse(equipmentArray);
          }
          
          console.log('Parsed Equipment:', equipmentArray);
          if (Array.isArray(equipmentArray)) {
            equipmentIds = equipmentArray.map(function(e) { 
              return (typeof e === 'object' && e !== null) ? e.id : e; 
            });
          }
        } catch (e) {
          console.error('Error parsing equipment:', e);
        }
      }
      
      // Re-initialize Select2 for edit modal with pre-selected values
      destroyModalEquipmentSelect($editStageModal.find('select.stage-modal-equipment'));
      $('#edit-equipment').val(equipmentIds);
      initModalEquipmentSelect($editStageModal, $('#edit-equipment'));

      // Reset tabs and tables
      $('#edit-controls-table tbody').empty();
      $('#edit-media-table tbody').empty();
      $('#edit-diluent-table tbody').empty();

      // Ensure we start on step 1
      $('#edit-step-2-content').hide();
      $('#edit-step-1-content').show();
      $('#edit-nav-step-2').removeClass('active').css({'color': '', 'border-bottom': ''}).addClass('text-muted');
      $('#edit-nav-step-1').addClass('active').removeClass('text-muted').css({'color': '#007bff', 'border-bottom': '3px solid #007bff'});
      $('#edit-btn-prev-step').hide();
      $('#edit-btn-save-stage').hide();
      $('#edit-btn-next-step').show();

      // Load specific tables based on JSON data
      function loadTableData(data, tableSelector, optionsHtml, prefix) {
         console.log('--- Processing Table: ' + prefix + ' ---');
         console.log('Data to Load:', data);
         
         if (data && data !== 'null' && data !== '[]' && data !== '{}') {
           try {
              // Handle both string and already parsed objects
              var itemsData = (typeof data === 'string') ? JSON.parse(data) : data;
              
              // Handle double-encoded JSON (when data attribute contains JSON string that was encoded again)
              if (typeof itemsData === 'string') {
                console.log('   Data is double-encoded, parsing again...');
                itemsData = JSON.parse(itemsData);
              }
              
              console.log('   Parsed/Object Data for ' + prefix + ':', itemsData);
              console.log('   Type check: itemsData=' + (itemsData ? 'truthy' : 'falsy') + ', typeof=' + typeof itemsData + ', isArray=' + Array.isArray(itemsData));
              
              if (itemsData && typeof itemsData === 'object') {
                 // Convert to array if it's an object (handles PHP associative arrays/non-sequential keys)
                 var items = Array.isArray(itemsData) ? itemsData : Object.values(itemsData);
                 console.log('   Items Found:', items.length);
                 
                 items.forEach(function(item, index) {
                   console.log('   Processing item #' + index + ':', item, 'Type:', typeof item);
                   if (item !== null && item !== '') {
                     if (typeof item === 'object' && item.id) {
                        console.log('      -> Creating row with object data: ID=' + item.id + ', Nature=' + item.result_nature + ', Mandatory=' + item.is_mandatory);
                        let extraData = {};
                        if (prefix === 'diluent') {
                            extraData.volume = item.volume || '';
                            extraData.concentration = item.concentration || '';
                        }
                        let $newRow = createEditRow(optionsHtml, prefix, item.id, item.result_nature, item.is_mandatory, extraData);
                        $(tableSelector + ' tbody').append($newRow);
                     } else if (typeof item !== 'object') {
                        // Fallback for old flat arrays (just IDs)
                        console.log('      -> Creating row with simple ID: ' + item);
                        let $newRow = createEditRow(optionsHtml, prefix, item, 'none', false);
                        $(tableSelector + ' tbody').append($newRow);
                     }
                     console.log('      -> Row appended to ' + tableSelector);
                   }
                 });
              }
           } catch(e) { 
              console.error('   Error processing data for ' + prefix + ':', e); 
           }
         } else {
            console.log('   Data for ' + prefix + ' is empty or null');
         }
      }

      loadTableData(media, '#edit-media-table', mediaOptionsHtml, 'media');
      loadTableData(controls, '#edit-controls-table', controlsOptionsHtml, 'control');
      loadTableData(diluents, '#edit-diluent-table', mediaOptionsHtml, 'diluent');

      initEditModalTableSelects();
      updateEditTableEmptyMessages();
      console.log('--- EDIT MODAL DEBUG END ---');
    });

    function updateEditTableEmptyMessages() {
      $('#edit-controls-table tbody tr').length === 0 ? $('.edit-empty-controls-msg').show() : $('.edit-empty-controls-msg').hide();
      $('#edit-media-table tbody tr').length === 0 ? $('.edit-empty-media-msg').show() : $('.edit-empty-media-msg').hide();
      $('#edit-diluent-table tbody tr').length === 0 ? $('.edit-empty-diluent-msg').show() : $('.edit-empty-diluent-msg').hide();
    }

    // --- STEPPER LOGIC FOR EDIT STAGE MODAL ---
    $('#edit-btn-next-step').click(function() {
      var valid = true;
      $('#edit-step-1-content input[required]').each(function() {
        if ($(this).val() === '') valid = false;
      });
      if(!valid) {
        alert("Please fill all required fields in Step 1.");
        return;
      }

      $('#edit-step-1-content').hide();
      $('#edit-step-2-content').show();
      $('#edit-nav-step-1').removeClass('active').css({'color': '', 'border-bottom': ''}).addClass('text-muted');
      $('#edit-nav-step-2').addClass('active').removeClass('text-muted').css({'color': '#007bff', 'border-bottom': '3px solid #007bff'});
      
      $('#edit-btn-next-step').hide();
      $('#edit-btn-prev-step').show();
      $('#edit-btn-save-stage').show();
      updateEditTableEmptyMessages();
    });

    $('#edit-btn-prev-step').click(function() {
      $('#edit-step-2-content').hide();
      $('#edit-step-1-content').show();
      $('#edit-nav-step-2').removeClass('active').css({'color': '', 'border-bottom': ''}).addClass('text-muted');
      $('#edit-nav-step-1').addClass('active').removeClass('text-muted').css({'color': '#007bff', 'border-bottom': '3px solid #007bff'});
      
      $('#edit-btn-prev-step').hide();
      $('#edit-btn-save-stage').hide();
      $('#edit-btn-next-step').show();
    });

    $('#edit-nav-step-2').click(function() {
      if(!$('#edit-step-1-content').is(':visible')) return;
      $('#edit-btn-next-step').click();
    });
    $('#edit-nav-step-1').click(function() {
      if(!$('#edit-step-2-content').is(':visible')) return;
      $('#edit-btn-prev-step').click();
    });

    var mediaOptionsHtmlTpl = `@foreach($mediaItems as $media)<option value="{{ $media->id }}">{{ $media->name }}</option>@endforeach`;
    var controlsOptionsHtmlTpl = `@foreach($controlItems as $control)<option value="{{ $control->id }}">{{ $control->name }}</option>@endforeach`;

    // Add row handlers for Edit form (delegated — buttons live inside Bootstrap tabs)
    $(document).on('click', '#edit-add-control-row', function(e) {
      e.preventDefault();
      appendEditTableRow('#edit-controls-table', controlsOptionsHtml, 'control');
    });
    $(document).on('click', '#edit-add-media-row', function(e) {
      e.preventDefault();
      appendEditTableRow('#edit-media-table', mediaOptionsHtml, 'media');
    });
    $(document).on('click', '#edit-add-diluent-row', function(e) {
      e.preventDefault();
      appendEditTableRow('#edit-diluent-table', mediaOptionsHtml, 'diluent');
    });

    // Handle delete modal population
    $('#delete-stage-modal').on('show.bs.modal', function (e) {
      var button = $(e.relatedTarget);
      var stageId = button.data('stage-id');
      var stageName = button.data('stage-name');
      var duration = button.data('duration');

      // Set hidden field value for POST
      $('#delete-stage-id-input').val(stageId);

      // Populate text details
      $('#delete-stage-name-display').text(stageName);
      $('#delete-stage-duration-display').text(duration);
    });

    // --- STEPPER LOGIC FOR ADD STAGE MODAL ---
    $('#btn-next-step').click(function() {
      // Basic validation of Step 1 required fields
      var valid = true;
      $('#step-1-content input[required]').each(function() {
        if ($(this).val() === '') valid = false;
      });
      if(!valid) {
        alert("Please fill all required fields in Step 1.");
        return;
      }

      $('#step-1-content').hide();
      $('#step-2-content').show();
      $('#nav-step-1').removeClass('active').css({'color': '', 'border-bottom': ''}).addClass('text-muted');
      $('#nav-step-2').addClass('active').removeClass('text-muted').css({'color': '#007bff', 'border-bottom': '3px solid #007bff'});
      
      $('#btn-next-step').hide();
      $('#btn-prev-step').show();
      $('#btn-save-stage').show();
      updateTableEmptyMessages();
    });

    $('#btn-prev-step').click(function() {
      $('#step-2-content').hide();
      $('#step-1-content').show();
      $('#nav-step-2').removeClass('active').css({'color': '', 'border-bottom': ''}).addClass('text-muted');
      $('#nav-step-1').addClass('active').removeClass('text-muted').css({'color': '#007bff', 'border-bottom': '3px solid #007bff'});
      
      $('#btn-prev-step').hide();
      $('#btn-save-stage').hide();
      $('#btn-next-step').show();
    });

    // Optional: Allow clicking nav headers if valid
    $('#nav-step-2').click(function() {
      if(!$('#step-1-content').is(':visible')) return;
      $('#btn-next-step').click();
    });
    $('#nav-step-1').click(function() {
      if(!$('#step-2-content').is(':visible')) return;
      $('#btn-prev-step').click();
    });

    // --- DYNAMIC TABLES LOGIC FOR STEP 2 ---
    // Note: controlsOptionsHtml and mediaOptionsHtml are already defined at the top of this script

    function initEditModalTableSelects() {
      $('#edit-stage-modal .item-id-select.select2-single').each(function() {
        var $el = $(this);
        if ($el.hasClass('select2-hidden-accessible')) {
          $el.select2('destroy');
        }
        $el.select2({
          placeholder: 'Select option...',
          allowClear: true,
          dropdownParent: $('#edit-stage-modal'),
        });
      });
    }

    function initAddModalRowSelect($select) {
      $select.select2({
        placeholder: 'Select option...',
        allowClear: true,
        dropdownParent: $('#add-stage-modal'),
      });
    }

    function createEditRow(optionsHtml, namePrefix, selectedId, selectedNature, isMandatory, extraData = {}) {
      var uniqueId = namePrefix + '_mand_' + Date.now() + Math.floor(Math.random() * 1000);
      var isChecked = (isMandatory === true || isMandatory == 1 || isMandatory === 'true') ? 'checked' : '';
      var hiddenVal = (isMandatory === true || isMandatory == 1 || isMandatory === 'true') ? '1' : '0';
      var volumeField = '';
      var concentrationField = '';

      if (namePrefix === 'diluent') {
        volumeField = '<td><input type="text" class="form-control" name="edit_' + namePrefix + '_volume[]" value="' + (extraData.volume || '') + '" placeholder="e.g. 9ml"></td>';
        concentrationField = '<td><input type="text" class="form-control" name="edit_' + namePrefix + '_concentration[]" value="' + (extraData.concentration || '') + '" placeholder="e.g. 10^-1"></td>';
      }

      var rowHtml = '<tr>'
        + '<td><select class="form-control item-id-select select2-single" name="edit_' + namePrefix + '_id[]">'
        + '<option value="">Select Option...</option>' + optionsHtml + '</select></td>'
        + '<td><select class="form-control item-nature-select" name="edit_' + namePrefix + '_result_nature[]">'
        + '<option value="quantitative">Quantitative</option>'
        + '<option value="qualitative">Qualitative</option>'
        + '<option value="none">No Result</option></select></td>'
        + volumeField
        + '<td class="text-center align-middle"><div class="custom-control custom-checkbox">'
        + '<input type="checkbox" class="custom-control-input" value="1" id="' + uniqueId + '" name="edit_' + namePrefix + '_mandatory_temp[]" ' + isChecked + '>'
        + '<label class="custom-control-label" for="' + uniqueId + '"></label>'
        + '<input type="hidden" name="edit_' + namePrefix + '_is_mandatory[]" value="' + hiddenVal + '" class="hidden-mand-val">'
        + '</div></td>'
        + '<td class="text-center align-middle"><button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" title="Remove row"><i class="mdi mdi-delete"></i></button></td>'
        + '</tr>';


      var $row = $(rowHtml);
      if (selectedId) {
        $row.find('.item-id-select').val(String(selectedId));
      }
      if (selectedNature) {
        $row.find('.item-nature-select').val(selectedNature);
      }
      return $row;
    }

    function appendEditTableRow(tableSelector, optionsHtml, namePrefix) {
      var $row = createEditRow(optionsHtml, namePrefix);
      $(tableSelector + ' tbody').append($row);
      initEditModalTableSelects();
      updateEditTableEmptyMessages();
    }

    function appendAddTableRow(tableSelector, optionsHtml, namePrefix) {
      var $row = $(createRow('', optionsHtml, namePrefix));
      $(tableSelector + ' tbody').append($row);
      initAddModalRowSelect($row.find('.select2-single'));
      updateTableEmptyMessages();
    }
    
    function updateTableEmptyMessages() {
      $('#controls-table tbody tr').length === 0 ? $('.empty-controls-msg').show() : $('.empty-controls-msg').hide();
      $('#media-table tbody tr').length === 0 ? $('.empty-media-msg').show() : $('.empty-media-msg').hide();
      $('#diluent-table tbody tr').length === 0 ? $('.empty-diluent-msg').show() : $('.empty-diluent-msg').hide();
    }

    function createRow(type, optionsHtml, namePrefix, data = {}) {
      let resultOptions = `
        <option value="quantitative" ${data.result_nature === 'quantitative' ? 'selected' : ''}>Quantitative</option>
        <option value="qualitative" ${data.result_nature === 'qualitative' ? 'selected' : ''}>Qualitative</option>
        <option value="none" ${data.result_nature === 'none' ? 'selected' : ''}>No Result</option>
      `;

      let volumeField = '';
      let concentrationField = '';
      
      if (namePrefix === 'diluent') {
          volumeField = `<td><input type="text" class="form-control" name="${namePrefix}_volume[]" value="${data.volume || ''}" placeholder="e.g. 9ml"></td>`;
          concentrationField = `<td><input type="text" class="form-control" name="${namePrefix}_concentration[]" value="${data.concentration || ''}" placeholder="e.g. 10^-1"></td>`;
      }

      let rowHtml = `
        <tr>
          <td>
            <select class="form-control select2-single" name="${namePrefix}_id[]">
              <option value="">Select Option...</option>
              ${optionsHtml}
            </select>
          </td>
          <td>
             <select class="form-control" name="${namePrefix}_result_nature[]">
              ${resultOptions}
             </select>
          </td>
          ${volumeField}
          ${concentrationField}
          <td class="text-center align-middle">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" value="1" id="${namePrefix}_mand_${Date.now()}_${Math.floor(Math.random() * 1000)}" name="${namePrefix}_mandatory_temp[]" ${data.is_mandatory !== false ? 'checked' : ''}>
              <label class="custom-control-label" for="${namePrefix}_mand_${Date.now()}_${Math.floor(Math.random() * 1000)}"></label>
              <input type="hidden" name="${namePrefix}_is_mandatory[]" value="${data.is_mandatory !== false ? '1' : '0'}" class="hidden-mand-val">
            </div>
          </td>
          <td class="text-center align-middle">
            <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" title="Remove row"><i class="mdi mdi-delete"></i></button>
          </td>
        </tr>
      `;
      return rowHtml;
    }

    // Add row handlers for Add form (delegated)
    $(document).on('click', '#add-control-row', function(e) {
      e.preventDefault();
      appendAddTableRow('#controls-table', controlsOptionsHtml, 'control');
    });
    $(document).on('click', '#add-media-row', function(e) {
      e.preventDefault();
      appendAddTableRow('#media-table', mediaOptionsHtml, 'media');
    });
    $(document).on('click', '#add-diluent-row', function(e) {
      e.preventDefault();
      appendAddTableRow('#diluent-table', mediaOptionsHtml, 'diluent');
    });

    // Remove row generic handler
    $(document).on('click', '.remove-row-btn', function() {
      $(this).closest('tr').remove();
      updateTableEmptyMessages();
      updateEditTableEmptyMessages();
    });

    // Update hidden field when checkbox changes
    $(document).on('change', 'input[type="checkbox"][name$="_mandatory_temp[]"]', function() {
      $(this).siblings('.hidden-mand-val').val(this.checked ? '1' : '0');
    });

    // --- FORM SUBMISSION PRE-PROCESSING ---
    // Instead of arrays of individual columns, server expects JSON objects structured like: [{"id": 1, "result_nature": "qualitative", "is_mandatory": true}]
    function preProcessFormSubmission(formId, isEdit) {
      $('#' + formId).off('submit.worksheetEngine').on('submit.worksheetEngine', function(e) {
        try {
        var pre = isEdit ? 'edit_' : '';
        var formPrefix = isEdit ? 'edit-' : '';

        // Validate step 1 required fields (novalidate on form — tabs hide native validation)
        var step1Valid = true;
        $('#' + formPrefix + 'step-1-content input[required], #' + formPrefix + 'step-1-content select[required]').each(function() {
          if ($(this).val() === '' || $(this).val() === null) {
            step1Valid = false;
          }
        });
        if (!step1Valid) {
          alert('Please fill all required fields in Step 1.');
          e.preventDefault();
          return false;
        }

        var processTable = function(prefix, tableSuffix) {
          var dataArray = [];
          // tableSuffix allows us to handle plural table names (e.g., 'controls-table' vs 'control' prefix)
          var actualTableSuffix = tableSuffix || prefix;
          var tableSelector = '#' + (isEdit ? 'edit-' : '') + actualTableSuffix + '-table tbody tr';
          console.log('Processing table with selector:', tableSelector, '| Input prefix:', pre + prefix);
          
          $(tableSelector).each(function() {
            var idVal = $(this).find('select[name="' + pre + prefix + '_id[]"]').val();
            console.log('  Found row with ID:', idVal);
            if(idVal) {
               var itemData = {
                 "id": idVal,
                 "result_nature": $(this).find('select[name="' + pre + prefix + '_result_nature[]"]').val(),
                 "is_mandatory": $(this).find('.hidden-mand-val').val() == '1' ? true : false
               };
               
               if (prefix === 'diluent') {
                   itemData.volume = $(this).find('input[name="' + pre + prefix + '_volume[]"]').val();
                   itemData.concentration = $(this).find('input[name="' + pre + prefix + '_concentration[]"]').val();
               }
               
               dataArray.push(itemData);
            }
          });
          console.log('  Total items for ' + prefix + ':', dataArray.length, dataArray);
          return dataArray;
        };

        var controlsData = processTable('control', 'controls');
        var mediaData = processTable('media');
        var diluentsData = processTable('diluent');

        console.log('=== FINAL DATA BEFORE SUBMISSION ===');
        console.log('Controls:', controlsData);
        console.log('Media:', mediaData);
        console.log('Diluents:', diluentsData);

        // Remove existing temp names to not clutter POST
        $(this).find('[name^="' + pre + 'control_"], [name^="' + pre + 'media_"], [name^="' + pre + 'diluent_"]').not('[name="controls_required[]"]').not('[name="media_required[]"]').not('[name="diluents_required[]"]').remove();

        var buildHiddenInputs = function(form, formNestedName, objArray) {
          console.log('Building hidden inputs for:', formNestedName, '| Count:', objArray.length);
          objArray.forEach(function(obj, index) {
              $('<input>').attr({type: 'hidden', name: formNestedName + '[' + index + '][id]', value: obj.id}).appendTo(form);
              $('<input>').attr({type: 'hidden', name: formNestedName + '[' + index + '][result_nature]', value: obj.result_nature}).appendTo(form);
              $('<input>').attr({type: 'hidden', name: formNestedName + '[' + index + '][is_mandatory]', value: obj.is_mandatory ? 1 : 0}).appendTo(form);
              
              if (obj.volume !== undefined) {
                  $('<input>').attr({type: 'hidden', name: formNestedName + '[' + index + '][volume]', value: obj.volume}).appendTo(form);
              }
              if (obj.concentration !== undefined) {
                  $('<input>').attr({type: 'hidden', name: formNestedName + '[' + index + '][concentration]', value: obj.concentration}).appendTo(form);
              }
              
              console.log('  Added hidden inputs for index', index, ':', obj);
          });
        };

        buildHiddenInputs(this, 'controls_required', controlsData);
        buildHiddenInputs(this, 'media_required', mediaData);
        buildHiddenInputs(this, 'diluents_required', diluentsData);
        
        console.log('=== FORM SUBMISSION COMPLETE ===');
        
        if (debugSubmit) {
          console.log('DEBUG MODE: Holding form submission. Serialized payload below:');
          console.log($(this).serializeArray());
          e.preventDefault();
          return false;
        }

        // Submit allows to continue naturally
        } catch (err) {
          console.error('Stage form submission error:', err);
          alert('Could not save stage: ' + err.message);
          e.preventDefault();
          return false;
        }
      });
    }

    preProcessFormSubmission('add-stage-form', false);
    preProcessFormSubmission('edit-stage-form', true);

  });
</script>
@endsection
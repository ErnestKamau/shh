@extends('layouts.lab.layout.app',['select2' => true])

@section('title2')
  <title>Edit Submission Form - {{ $submissionForm->name }}</title>
@endsection

@section('content2')
  <main>
    <?php
      $items = array(
        array(
          'link' => route('lab-home'),
          'name' => 'Lab Management',
          'icon' => null
        ),
        array(
          'link' => route('submission-forms.index'),
          'name' => 'Submission Forms',
          'icon' => null
        ),
        array(
          'link' => route('submission-forms.show', $submissionForm),
          'name' => $submissionForm->name,
          'icon' => null
        ),
        array(
          'link' => '#',
          'name' => 'Edit',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="d-flex justify-content-between align-items-center p-4">
      <h2>
        <i class="mdi mdi-pencil"></i> Edit Submission Form
      </h2>
      <div>
        <a href="{{ route('submission-forms.show', $submissionForm) }}" class="btn btn-outline-info">
          <i class="mdi mdi-eye"></i> View Form
        </a>
        <a href="{{ route('submission-forms.index') }}" class="btn btn-outline-secondary">
          <i class="mdi mdi-arrow-left"></i> Back to Forms
        </a>
      </div>
    </div>

    <div class="bg-light p-4">
      <div class="row">
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Form Details</h5>
            </div>
            <div class="card-body">
              <form method="POST" action="{{ route('submission-forms.update', $submissionForm) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                  <label for="name" class="required">Form Name</label>
                  <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $submissionForm->name) }}" required maxlength="255" placeholder="Enter a descriptive name for your form">
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-12 mt-3 mb-1 px-0">
                  <h6 class="text-primary font-weight-bold small text-uppercase">
                    <i class="mdi mdi-certificate mr-1"></i> Form Quality Control Metadata
                  </h6>
                  <p class="text-muted small mb-3">To get started, please provide the standard identification details for this form. These include the Document Control Number, Revision Number, and the official Issue Date which explain and identify this form.</p>
                </div>

                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="document_code" class="required">Document Control Number</label>
                      <input type="text" class="form-control @error('document_code') is-invalid @enderror" id="document_code" name="document_code" value="{{ old('document_code', $submissionForm->document_code) }}" required maxlength="50" placeholder="e.g. FM/QA/047">
                      @error('document_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="version" class="required">Revision Number</label>
                      <input type="text" class="form-control @error('version') is-invalid @enderror" id="version" name="version" value="{{ old('version', $submissionForm->version) }}" required maxlength="50" placeholder="e.g. 01">
                      @error('version')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="issue_date" class="required">Issue Date</label>
                      <input type="date" class="form-control @error('issue_date') is-invalid @enderror" id="issue_date" name="issue_date" value="{{ old('issue_date', $submissionForm->issue_date ? $submissionForm->issue_date->format('Y-m-d') : '') }}" required>
                      @error('issue_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label for="description">Description</label>
                  <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" maxlength="1000" placeholder="Provide a brief description of what this form is used for">{{ old('description', $submissionForm->description) }}</textarea>
                  @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">Optional. This helps users understand the purpose of the form.</small>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="naming_convention_prefix" class="required">Form Number Prefix</label>
                      <input type="text" class="form-control @error('naming_convention_prefix') is-invalid @enderror" id="naming_convention_prefix" name="naming_convention_prefix" value="{{ old('naming_convention_prefix', $submissionForm->naming_convention_prefix) }}" required maxlength="50" placeholder="SF">
                      @error('naming_convention_prefix')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                      <small class="form-text text-muted">Used to generate unique form numbers (e.g., SF for Submission Form).</small>
                      @if($submissionForm->instances()->exists())
                        <div class="alert alert-warning small mt-2">
                          <i class="mdi mdi-alert"></i>
                          <strong>Warning:</strong> This form has existing instances. Changing the prefix may affect future form numbering.
                        </div>
                      @endif
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="naming_convention_format" class="required">Form Number Format</label>
                      <select class="form-control @error('naming_convention_format') is-invalid @enderror" id="naming_convention_format" name="naming_convention_format" required>
                        <option value="{prefix}/{year}/{sequence}" {{ old('naming_convention_format', $submissionForm->naming_convention_format) == '{prefix}/{year}/{sequence}' ? 'selected' : '' }}>SF/2025/001</option>
                        <option value="{prefix}-{year}-{sequence}" {{ old('naming_convention_format', $submissionForm->naming_convention_format) == '{prefix}-{year}-{sequence}' ? 'selected' : '' }}>SF-2025-001</option>
                        <option value="{prefix}{year}{sequence}" {{ old('naming_convention_format', $submissionForm->naming_convention_format) == '{prefix}{year}{sequence}' ? 'selected' : '' }}>SF2025001</option>
                        <option value="{prefix}/{sequence}" {{ old('naming_convention_format', $submissionForm->naming_convention_format) == '{prefix}/{sequence}' ? 'selected' : '' }}>SF/001</option>
                      </select>
                      @error('naming_convention_format')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                      <small class="form-text text-muted">Format for generating unique form instance numbers.</small>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label for="print_template_name">Print Template</label>
                  <select class="form-control @error('print_template_name') is-invalid @enderror" id="print_template_name" name="print_template_name">
                    <option value="">Use Default Template</option>
                    <option value="submission-forms.print.default" {{ old('print_template_name', $submissionForm->print_template_name) == 'submission-forms.print.default' ? 'selected' : '' }}>Default Template</option>
                    <option value="submission-forms.print.microbiology" {{ old('print_template_name', $submissionForm->print_template_name) == 'submission-forms.print.microbiology' ? 'selected' : '' }}>Microbiology Template</option>
                    <option value="submission-forms.print.serology" {{ old('print_template_name', $submissionForm->print_template_name) == 'submission-forms.print.serology' ? 'selected' : '' }}>Serology Template</option>
                  </select>
                  @error('print_template_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">Select a custom print template for this form. If not specified, the default template will be used.</small>
                </div>

                <div class="form-group">
                  <label for="sample_analysis_stage_ids">Lab Sections</label>
                  <select class="form-control select2 @error('sample_analysis_stage_ids') is-invalid @enderror" id="sample_analysis_stage_ids" name="sample_analysis_stage_ids[]" multiple>
                    @php($selectedStages = old('sample_analysis_stage_ids', $submissionForm->sampleAnalysisStages->pluck('id')->toArray()))
                    @foreach($labSections as $section)
                      <option value="{{ $section->id }}" {{ in_array($section->id, $selectedStages) ? 'selected' : '' }}>{{ $section->name }}</option>
                    @endforeach
                  </select>
                  @error('sample_analysis_stage_ids')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">Select the lab sections associated with this form.</small>
                </div>

                <div class="form-group" id="placement-mode-wrapper">
                  <label for="placement_mode" class="required">How Should The Form Appear?</label>
                  <select class="form-control @error('placement_mode') is-invalid @enderror" id="placement_mode" name="placement_mode" required>
                    <option value="button_trigger" {{ old('placement_mode', $submissionForm->placement_mode ?? 'button_trigger') === 'button_trigger' ? 'selected' : '' }}>Open by button/action</option>
                    <option value="page_section" {{ old('placement_mode', $submissionForm->placement_mode) === 'page_section' ? 'selected' : '' }}>Render inside page section</option>
                  </select>
                  @error('placement_mode')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">Choose whether users open this form from a page action or fill it directly inside a page section.</small>
                </div>

                <div class="form-group" id="display-mode-wrapper">
                  <label for="display_mode" class="required">Display Mode</label>
                  <select class="form-control @error('display_mode') is-invalid @enderror" id="display_mode" name="display_mode" required>
                    <option value="expanded" {{ old('display_mode', $submissionForm->display_mode ?? 'expanded') === 'expanded' ? 'selected' : '' }}>Always visible</option>
                    <option value="collapsible" {{ old('display_mode', $submissionForm->display_mode) === 'collapsible' ? 'selected' : '' }}>Collapsible</option>
                  </select>
                  @error('display_mode')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted"><strong>Always visible</strong>: form content is shown immediately on the page. <strong>Collapsible</strong>: only a header/toggle is shown; users expand it when needed.</small>
                </div>

                <div class="form-group">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_customer_portal_form" name="is_customer_portal_form" value="1" {{ old('is_customer_portal_form', $submissionForm->is_customer_portal_form) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_customer_portal_form">Filled only from customer portal</label>
                  </div>
                  <small class="form-text text-muted">If checked, this form can only be submitted via customer portal and will route to LIMS destination page(s).</small>
                </div>

                <div class="form-group" id="target-pages-wrapper">
                  <label for="target_pages">Target Pages</label>
                  <select class="form-control select2 @error('target_pages') is-invalid @enderror" id="target_pages" name="target_pages[]" multiple>
                    @php($selectedPages = old('target_pages', $submissionForm->target_pages ?? []))
                    @foreach($availablePages as $page)
                      <option value="{{ $page['value'] }}" {{ in_array($page['value'], $selectedPages, true) ? 'selected' : '' }}>{{ $page['label'] }}</option>
                    @endforeach
                  </select>
                  @error('target_pages')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                  @error('target_pages.*')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">Select one or more system pages where this form should be used.</small>
                </div>

                <div class="form-group" id="lims-destination-wrapper" style="display:none;">
                  <label for="lims_destination_pages">LIMS Destination Page(s)</label>
                  <select class="form-control select2 @error('lims_destination_pages') is-invalid @enderror" id="lims_destination_pages" name="lims_destination_pages[]" multiple>
                    @php($selectedDestinations = old('lims_destination_pages', $submissionForm->lims_destination_pages ?? []))
                    @foreach($availablePages as $page)
                      <option value="{{ $page['value'] }}" {{ in_array($page['value'], $selectedDestinations, true) ? 'selected' : '' }}>{{ $page['label'] }}</option>
                    @endforeach
                  </select>
                  @error('lims_destination_pages')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                  @error('lims_destination_pages.*')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">Default destination is Samples En-Route when no page is selected.</small>
                </div>

                {{-- Advanced Placement: per-page slot / button binding --}}
                <div id="advanced-placement-section" class="d-none">
                  <div class="col-12 mt-2 mb-1 px-0">
                    <h6 class="text-primary font-weight-bold small text-uppercase">
                      <i class="mdi mdi-map-marker-multiple mr-1"></i> Advanced Placement
                    </h6>
                    <p class="text-muted small mb-2">Fine-tune exactly <em>where</em> on each selected page this form should appear.</p>
                  </div>
                  <div id="placement-pages-container"></div>
                </div>

                <div class="form-group form-check mt-3">
                  <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $submissionForm->is_active) ? 'checked' : '' }}>
                  <label class="form-check-label" for="is_active">Active</label>
                  <small class="form-text text-muted">Inactive forms cannot be used to create new instances.</small>
                  @if($submissionForm->instances()->exists() && !$submissionForm->is_active)
                    <div class="alert alert-info small mt-2 mb-0">
                      <i class="mdi mdi-information-outline"></i>
                      This form has existing instances but is currently inactive.
                    </div>
                  @endif
                </div>

                <div class="form-group">
                  <label for="start_submission_number">Start submission from number</label>
                  <input type="number" class="form-control @error('start_submission_number') is-invalid @enderror" id="start_submission_number" name="start_submission_number" value="{{ old('start_submission_number', $submissionForm->start_submission_number ?? 1) }}" min="1" placeholder="1">
                  @error('start_submission_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">Provide the start submission no for this submission form.</small>
                </div>

                <div class="form-group mb-0">
                  <button type="submit" class="btn btn-primary">
                    <i class="mdi mdi-check"></i> Update Form
                  </button>
                  <a href="{{ route('submission-forms.show', $submissionForm) }}" class="btn btn-outline-secondary ml-2">
                    <i class="mdi mdi-close"></i> Cancel
                  </a>
                </div>
              </form>
            </div>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">
                <i class="mdi mdi-information-outline"></i> Form Information
              </h6>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <h6>Current Status</h6>
                <div>
                  @if($submissionForm->is_published)
                    <span class="badge badge-success">Published</span>
                  @else
                    <span class="badge badge-warning">Draft</span>
                  @endif
                  
                  @if($submissionForm->is_active)
                    <span class="badge badge-outline-success ml-1">Active</span>
                  @else
                    <span class="badge badge-outline-danger ml-1">Inactive</span>
                  @endif
                </div>
              </div>
              
              <div class="mb-3">
                <h6>Statistics</h6>
                <ul class="list-unstyled small">
                  <li><strong>Version:</strong> {{ $submissionForm->version }}</li>
                  <li><strong>Created:</strong> {{ $submissionForm->created_at->format('M d, Y') }}</li>
                  <li><strong>Last Updated:</strong> {{ $submissionForm->updated_at->format('M d, Y') }}</li>
                  <li><strong>Sections:</strong> {{ $submissionForm->sections()->count() }}</li>
                  <li><strong>Instances:</strong> {{ $submissionForm->instances()->count() }}</li>
                </ul>
              </div>
              
              @if($submissionForm->instances()->exists())
                <div class="alert alert-warning small">
                  <i class="mdi mdi-alert"></i>
                  <strong>Note:</strong> This form has {{ $submissionForm->instances()->count() }} existing instance(s). 
                  Be careful when making changes that might affect data integrity.
                </div>
              @endif
              
              <div class="alert alert-info small">
                <i class="mdi mdi-lightbulb-outline"></i>
                <strong>Tip:</strong> After updating form details, you can modify the form structure by adding or editing sections and fields.
              </div>
            </div>
          </div>
          
          <div class="card mt-3">
            <div class="card-header">
              <h6 class="mb-0">
                <i class="mdi mdi-cog"></i> Quick Actions
              </h6>
            </div>
            <div class="card-body">
              <div class="d-grid gap-2">
                <a href="{{ route('submission-forms.preview', $submissionForm) }}" class="btn btn-outline-info btn-sm">
                  <i class="mdi mdi-eye-outline"></i> Preview Form
                </a>
                
                <form method="POST" action="{{ route('submission-forms.clone', $submissionForm) }}" 
                      onsubmit="return confirm('Are you sure you want to clone this form?')">
                  @csrf
                  <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="mdi mdi-content-copy"></i> Clone Form
                  </button>
                </form>
                
                <a href="{{ route('submission-forms.export', $submissionForm) }}" class="btn btn-outline-info btn-sm">
                  <i class="mdi mdi-download"></i> Export Structure
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Success/Error Messages -->
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif

  <!-- Validation Errors -->
  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <strong>Please correct the following errors:</strong>
      <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif
@endsection

@section('script2')
<script>
  // ── Form number format preview ────────────────────────────────────────────────
  document.getElementById('naming_convention_format').addEventListener('change', function() {
    const prefix = document.getElementById('naming_convention_prefix').value || 'SF';
    const format = this.value;
    const year = new Date().getFullYear();
    let preview = format
      .replace('{prefix}', prefix)
      .replace('{year}', year)
      .replace('{sequence}', '001');
    const selectedOption = this.options[this.selectedIndex];
    const originalText = selectedOption.textContent.split(' → ')[0].trim();
    Array.from(this.options).forEach(opt => {
      opt.textContent = opt.textContent.split(' → ')[0].trim();
    });
    selectedOption.textContent = originalText + ' → ' + preview;
  });

  document.getElementById('naming_convention_prefix').addEventListener('input', function() {
    document.getElementById('naming_convention_format').dispatchEvent(new Event('change'));
  });

  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('naming_convention_format').dispatchEvent(new Event('change'));
  });

  // ── Advanced Placement ────────────────────────────────────────────────────────
  const pageLayoutUrl = '{{ route('submission-forms.page-layout') }}';
  const savedPlacementSlot    = @json($submissionForm->placement_slot ?? []);
  const savedTriggerButtonIds = @json($submissionForm->trigger_button_ids ?? []);
  const oldPlacementSlot    = @json(old('placement_slot', null)) || savedPlacementSlot;
  const oldTriggerButtonIds = @json(old('trigger_button_ids', null)) || savedTriggerButtonIds;

  function getSelectedRoutes() {
    return $('#target_pages').val() || [];
  }

  function getPlacementMode() {
    return $('#placement_mode').val();
  }

  function toggleLimsFieldsVisibility() {
    const isPortal = $('#is_customer_portal_form').is(':checked');
    const $targetPages = $('#target-pages-wrapper');
    const $placementMode = $('#placement-mode-wrapper');
    const $displayMode = $('#display-mode-wrapper');
    const $advancedPlacement = $('#advanced-placement-section');

    if (isPortal) {
      $targetPages.hide();
      $placementMode.hide();
      $displayMode.hide();
      $advancedPlacement.addClass('d-none');
      $('#lims-destination-wrapper').show();
      return;
    }

    $targetPages.show();
    $placementMode.show();
    $displayMode.show();
    $('#lims-destination-wrapper').hide();
    $('#lims_destination_pages').val(null).trigger('change');

    if (getSelectedRoutes().length > 0) {
      refreshAdvancedPlacement();
    }
  }

  function refreshAdvancedPlacement() {
    if ($('#is_customer_portal_form').is(':checked')) {
      $('#advanced-placement-section').addClass('d-none');
      $('#placement-pages-container').empty();
      return;
    }

    const routes = getSelectedRoutes();
    const mode   = getPlacementMode();
    const $section   = $('#advanced-placement-section');
    const $container = $('#placement-pages-container');

    if (routes.length === 0) {
      $section.addClass('d-none');
      $container.empty();
      return;
    }

    const qs = routes.map(r => 'routes[]=' + encodeURIComponent(r)).join('&');

    $.get(pageLayoutUrl + '?' + qs, function(resp) {
      if (!resp.success) return;
      $container.empty();
      const layout = resp.layout;

      routes.forEach(function(route) {
        const info = layout[route] || { label: route, slots: [], buttons: [] };
        const $card = buildPageCard(route, info, mode);
        $container.append($card);
      });

      $section.removeClass('d-none');

      // Init select2 on newly added button pickers
      $container.find('.select2-btn-picker').not('.select2-hidden-accessible').select2({
        placeholder: 'Any button (global Page Forms dropdown)',
        allowClear: true,
        width: '100%',
      });
    });
  }

  function buildPageCard(route, info, mode) {
    const $card = $('<div class="card border-left border-primary mb-3 shadow-sm">');
    const $header = $('<div class="card-header py-2 d-flex align-items-center">').html(
      '<i class="mdi mdi-file-document-outline mr-2 text-primary"></i>' +
      '<strong class="mr-1">' + escapeHtml(info.label) + '</strong>' +
      '<small class="text-muted">(' + escapeHtml(route) + ')</small>'
    );
    const $body = $('<div class="card-body py-3">');

    if (mode === 'page_section') {
      $body.append(buildSlotPicker(route, info.slots));
    } else {
      $body.append(buildButtonPicker(route, info.buttons));
    }

    return $card.append($header).append($body);
  }

  function buildSlotPicker(route, slots) {
    const savedSlotId = (oldPlacementSlot[route] && oldPlacementSlot[route]['slot_id']) || '';
    let optionsHtml = '<option value="">— Default position (top of page) —</option>';
    (slots || []).forEach(function(s) {
      const sel = savedSlotId === s.id ? ' selected' : '';
      optionsHtml += '<option value="' + escapeHtml(s.id) + '"' + sel + '>' + escapeHtml(s.label) + '</option>';
    });

    return $('<div class="form-group mb-0">').html(
      '<label class="small font-weight-bold"><i class="mdi mdi-map-marker mr-1"></i>Where on this page should the form appear?</label>' +
      '<select class="form-control form-control-sm" name="placement_slot[' + escapeHtml(route) + '][slot_id]">' +
        optionsHtml +
      '</select>' +
      '<small class="form-text text-muted">Select the exact position on the page where this form will be embedded.</small>'
    );
  }

  function buildButtonPicker(route, buttons) {
    const savedButtons = (oldTriggerButtonIds && oldTriggerButtonIds[route]) || [];

    if (!buttons || buttons.length === 0) {
      return $('<div class="alert alert-light border small py-2 mb-0">').html(
        '<i class="mdi mdi-information-outline mr-1 text-info"></i>' +
        'No specific buttons are registered for this page. The form will appear in the global <strong>Page Forms</strong> dropdown.'
      );
    }

    let optionsHtml = '';
    buttons.forEach(function(b) {
      const sel = savedButtons.includes(b.trigger_id) ? ' selected' : '';
      optionsHtml += '<option value="' + escapeHtml(b.trigger_id) + '"' + sel + '>' + escapeHtml(b.label) + '</option>';
    });

    return $('<div class="form-group mb-0">').html(
      '<label class="small font-weight-bold"><i class="mdi mdi-gesture-tap mr-1"></i>Which button(s) should open this form?</label>' +
      '<select class="form-control form-control-sm select2-btn-picker" name="trigger_button_ids[' + escapeHtml(route) + '][]" multiple>' +
        optionsHtml +
      '</select>' +
      '<small class="form-text text-muted">Select one or more buttons. If none selected, the form appears in the global <strong>Page Forms</strong> dropdown.</small>'
    );
  }

  function escapeHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  $('#target_pages, #placement_mode').on('change', refreshAdvancedPlacement);

  $(function() {
    if (getSelectedRoutes().length > 0) {
      refreshAdvancedPlacement();
    }

    toggleLimsFieldsVisibility();
    $('#is_customer_portal_form').on('change', toggleLimsFieldsVisibility);
  });
</script>

<style>
  .required::after { content: " *"; color: red; }
  .d-grid { display: grid; }
  .gap-2 { gap: 0.5rem; }
  #advanced-placement-section .card { border-left-width: 3px !important; }
</style>
@endsection
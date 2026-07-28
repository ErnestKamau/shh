@extends('layouts.lab.layout.app',['select2' => true])

@section('title2')
  <title>Edit Submission Form - {{ $submissionForm->name }}</title>
  @include('submission-forms.partials.lab-theme-styles')
@endsection

@section('content2')
  <main>
    @php
      $fromRft = request()->query('from') === 'rft' || $submissionForm->isTestRequestTemplate();
      $items = [
        [
          'link' => route('dashboard-lab'),
          'name' => 'Dashboard',
          'icon' => null,
        ],
        [
          'link' => $fromRft ? route('sample-workflow.request-for-testing') : route('submission-forms.index'),
          'name' => $fromRft ? 'Request For Testing' : 'Submission Forms',
          'icon' => null,
        ],
        [
          'link' => route('submission-forms.show', ['submissionForm' => $submissionForm, 'from' => $fromRft ? 'rft' : null]),
          'name' => $submissionForm->name,
          'icon' => null,
        ],
        [
          'link' => '#',
          'name' => 'Edit',
          'icon' => null,
        ],
      ];
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="container-fluid workflow-board-page rft-page-shell rft-theme lab-panel-theme sf-admin-page px-3 px-md-4 pt-2 pb-4">
      @if(session('success'))
        <div class="alert alert-success mt-2">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger mt-2">{{ session('error') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger mt-2">
          <strong>Please correct the following errors:</strong>
          <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="workflow-board-panel mb-3">
        <div class="workflow-board-panel-header">
          <div>
            <h5 class="sf-page-title mb-0"><i class="mdi mdi-pencil"></i> Edit Submission Form</h5>
            <p class="sf-page-subtitle">{{ $submissionForm->name }}</p>
          </div>
          <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
            <a href="{{ route('submission-forms.show', $submissionForm) }}" class="btn btn-sm btn-outline-secondary btn-action-sm">
              <i class="mdi mdi-eye"></i> View Form
            </a>
            <a href="{{ $fromRft ? route('sample-workflow.request-for-testing') : route('submission-forms.index') }}" class="btn btn-sm btn-outline-secondary btn-action-sm">
              <i class="mdi mdi-arrow-left"></i> {{ $fromRft ? 'Back to RFT' : 'Back to Forms' }}
            </a>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-8">
          <div class="workflow-board-panel mb-3">
            <div class="workflow-board-panel-header">
              <h6 class="mb-0"><i class="mdi mdi-form-select"></i> Form Details</h6>
            </div>
            <div class="workflow-board-panel-body">
              <form method="POST" action="{{ route('submission-forms.update', $submissionForm) }}">
                @csrf
                @method('PUT')

                {{-- Form Type selector --}}
                <div class="form-group">
                  <label class="required">Form Type</label>
                  <div class="d-flex gap-3 mt-1">
                    <div class="custom-control custom-radio custom-control-inline">
                      <input type="radio" id="form_type_template" name="form_type" value="template" class="custom-control-input"
                        {{ old('form_type', $submissionForm->form_type ?? 'template') === 'template' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="form_type_template">
                        <strong>Template Form</strong>
                        <small class="d-block text-muted">Standalone form assigned to customers and sample types.</small>
                      </label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline ml-4">
                      <input type="radio" id="form_type_attachment" name="form_type" value="attachment" class="custom-control-input"
                        {{ old('form_type', $submissionForm->form_type ?? 'template') === 'attachment' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="form_type_attachment">
                        <strong>Attachment Form</strong>
                        <small class="d-block text-muted">Optional form linked to one or more template forms.</small>
                      </label>
                    </div>
                  </div>
                  @error('form_type')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                  @enderror
                </div>

                <div class="form-group">
                  <label for="name" class="required">Form Name</label>
                  <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $submissionForm->name) }}" required maxlength="255" placeholder="Enter a descriptive name for your form">
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-12 mt-3 mb-1 px-0">
                  <h6 class="sf-section-heading">
                    <i class="mdi mdi-certificate mr-1"></i> Form Quality Control Metadata
                  </h6>
                  <p class="text-muted small mb-3">Document Control Number, Revision Number, and Issue Date identify this form.</p>
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
                        <div class="sf-alert-soft is-warning small mt-2">
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

                <div id="template_form_type_section" style="display:none;">
                <div class="form-group">
                  <label for="template_form_type_id" class="required">Template Form Type</label>
                  <select class="form-control @error('template_form_type_id') is-invalid @enderror" id="template_form_type_id" name="template_form_type_id">
                    <option value="">Select a template form type</option>
                    @foreach($templateFormTypes as $templateFormType)
                      <option value="{{ $templateFormType->id }}" {{ (string) old('template_form_type_id', $submissionForm->template_form_type_id) === (string) $templateFormType->id ? 'selected' : '' }}>
                        {{ $templateFormType->name }}
                      </option>
                    @endforeach
                    <option value="__add_new__">+ Add New Template Form Type</option>
                  </select>
                  @error('template_form_type_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">Select an existing type, or choose <strong>+ Add New Template Form Type</strong> to create one instantly.</small>
                </div>
                </div>

                {{-- Linked Template Forms — only visible when form_type = attachment --}}
                <div class="form-group" id="template_forms_section" style="display:none;">
                  <label for="template_form_ids" class="required">Template Form Type</label>
                  <select class="form-control ls-select2 @error('template_form_ids') is-invalid @enderror" id="template_form_ids" name="template_form_ids[]" multiple>
                    @php($selectedTemplateFormIds = old('template_form_ids', $submissionForm->relationLoaded('templateForms') ? $submissionForm->templateForms->pluck('id')->toArray() : []))
                    @foreach($templateForms as $tf)
                      <option value="{{ $tf->id }}" {{ in_array($tf->id, $selectedTemplateFormIds) ? 'selected' : '' }}>
                        {{ $tf->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('template_form_ids')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">Link this attachment form to one or more template forms.</small>
                </div>

                <div class="form-group">
                  <label for="customer_ids">Customers</label>
                  <select class="form-control ls-select2 @error('customer_ids') is-invalid @enderror" id="customer_ids" name="customer_ids[]" multiple>
                    @php($selectedCustomers = old('customer_ids', $submissionForm->customers->pluck('id')->toArray()))
                    @foreach($customers as $customer)
                      <option value="{{ $customer->id }}" {{ in_array($customer->id, $selectedCustomers) ? 'selected' : '' }}>
                        {{ $customer->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('customer_ids')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">Restrict this form to specific customers. Leave empty to allow all customers.</small>
                </div>

                <div class="form-group">
                  <label for="sample_type_ids">Sample Types</label>
                  <select class="form-control ls-select2 @error('sample_type_ids') is-invalid @enderror" id="sample_type_ids" name="sample_type_ids[]" multiple>
                    @php($selectedSampleTypes = old('sample_type_ids', $submissionForm->sampleTypes->pluck('id')->toArray()))
                    @foreach($sampleTypes as $sampleType)
                      <option value="{{ $sampleType->id }}" {{ in_array($sampleType->id, $selectedSampleTypes) ? 'selected' : '' }}>
                        {{ $sampleType->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('sample_type_ids')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">Restrict this form to specific sample types. Leave empty to apply to all sample types.</small>
                </div>

                <div class="form-group">
                  <label for="sample_analysis_stage_ids">Lab Sections</label>
                  <select class="form-control ls-select2 @error('sample_analysis_stage_ids') is-invalid @enderror" id="sample_analysis_stage_ids" name="sample_analysis_stage_ids[]" multiple>
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
                  <div class="checkbox-option-card d-flex align-items-start p-3" style="gap:12px;">
                    <div class="mt-1">
                      <input class="form-check-input" type="checkbox" id="is_customer_portal_form" name="is_customer_portal_form" value="1" {{ old('is_customer_portal_form', $submissionForm->is_customer_portal_form) ? 'checked' : '' }} style="width:18px;height:18px;cursor:pointer;">
                    </div>
                    <div>
                      <label class="form-check-label font-weight-semibold mb-0" for="is_customer_portal_form" style="cursor:pointer;font-size:0.92rem;">Filled only from customer portal</label>
                      <p class="text-muted small mb-0 mt-1">If checked, this form can only be submitted via customer portal and will route to LIMS destination page(s).</p>
                    </div>
                  </div>
                </div>

                <div class="form-group" id="customer-request-form-wrapper" style="display:none;">
                  <div class="checkbox-option-card d-flex align-items-start p-3 ml-4" style="gap:12px;background:#eff6ff !important;border-color:#bfdbfe !important;">
                    <div class="mt-1">
                      <input class="form-check-input" type="checkbox" id="is_customer_request_form" name="is_customer_request_form" value="1" {{ old('is_customer_request_form', $submissionForm->is_customer_request_form) ? 'checked' : '' }} style="width:18px;height:18px;cursor:pointer;">
                    </div>
                    <div>
                      <label class="form-check-label font-weight-semibold mb-0" for="is_customer_request_form" style="cursor:pointer;font-size:0.92rem;">Mark as customer request form</label>
                      <p class="text-muted small mb-0 mt-1">When multiple forms are marked, the portal API returns the most recently updated one as the default customer request form.</p>
                    </div>
                  </div>
                </div>

                <div class="form-group" id="target-pages-wrapper">
                  <label for="target_pages">Target Pages</label>
                  <select class="form-control ls-select2 @error('target_pages') is-invalid @enderror" id="target_pages" name="target_pages[]" multiple>
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
                  <select class="form-control ls-select2 @error('lims_destination_pages') is-invalid @enderror" id="lims_destination_pages" name="lims_destination_pages[]" multiple>
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
                  <small class="form-text text-muted">Default destination is Sample Receiving when no page is selected.</small>
                </div>

                {{-- Advanced Placement: per-page slot / button binding --}}
                <div id="advanced-placement-section" class="d-none">
                  <div class="col-12 mt-2 mb-1 px-0">
                    <h6 class="sf-section-heading">
                      <i class="mdi mdi-map-marker-multiple mr-1"></i> Advanced Placement
                    </h6>
                    <p class="text-muted small mb-2">Fine-tune exactly <em>where</em> on each selected page this form should appear.</p>
                  </div>
                  <div id="placement-pages-container"></div>
                </div>

                <div class="form-group mt-3">
                  <div class="checkbox-option-card d-flex align-items-start p-3" style="gap:12px;">
                    <div class="mt-1">
                      <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $submissionForm->is_active) ? 'checked' : '' }} style="width:18px;height:18px;cursor:pointer;">
                    </div>
                    <div>
                      <label class="form-check-label font-weight-semibold mb-0" for="is_active" style="cursor:pointer;font-size:0.92rem;">Active</label>
                      <p class="text-muted small mb-0 mt-1">Inactive forms cannot be used to create new instances.</p>
                      @if($submissionForm->instances()->exists() && !$submissionForm->is_active)
                        <div class="sf-alert-soft is-info small mt-2 mb-0">
                          <i class="mdi mdi-information-outline"></i>
                          This form has existing instances but is currently inactive.
                        </div>
                      @endif
                    </div>
                  </div>
                </div>

                @if($submissionForm->isTestRequestTemplate())
                <div class="form-group">
                  <div class="checkbox-option-card d-flex align-items-start p-3" style="gap:12px;">
                    <div class="mt-1">
                      <input type="checkbox" class="form-check-input" id="is_hidden_from_rft" name="is_hidden_from_rft" value="1" {{ old('is_hidden_from_rft', $submissionForm->is_hidden_from_rft) ? 'checked' : '' }} style="width:18px;height:18px;cursor:pointer;">
                    </div>
                    <div>
                      <label class="form-check-label font-weight-semibold mb-0" for="is_hidden_from_rft" style="cursor:pointer;font-size:0.92rem;">Hide from Request For Testing</label>
                      <p class="text-muted small mb-0 mt-1">Keeps the form in the system (and existing submissions) but removes it from the RFT form list.</p>
                    </div>
                  </div>
                </div>
                @endif

                <div class="form-group">
                  <label for="start_submission_number">Start submission from number</label>
                  <input type="number" class="form-control @error('start_submission_number') is-invalid @enderror" id="start_submission_number" name="start_submission_number" value="{{ old('start_submission_number', $submissionForm->start_submission_number ?? 1) }}" min="1" placeholder="1">
                  @error('start_submission_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">Provide the start submission no for this submission form.</small>
                </div>

                <div class="sf-form-actions">
                  <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
                    <button type="submit" class="btn btn-sm btn-primary btn-action-sm">
                      <i class="mdi mdi-content-save mr-1"></i> Save Changes
                    </button>
                    <a href="{{ route('submission-forms.show', $submissionForm) }}" class="btn btn-sm btn-outline-secondary btn-action-sm">
                      <i class="mdi mdi-close mr-1"></i> Cancel
                    </a>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="workflow-board-panel mb-3">
            <div class="workflow-board-panel-header">
              <h6 class="mb-0"><i class="mdi mdi-information-outline"></i> Form Information</h6>
            </div>
            <div class="workflow-board-panel-body">
              <div class="mb-3">
                <div class="text-muted small mb-1">Current Status</div>
                <div>
                  @if($submissionForm->is_published)
                    <span class="sf-meta-chip is-success">Published</span>
                  @else
                    <span class="sf-meta-chip is-warning">Draft</span>
                  @endif
                  @if($submissionForm->is_active)
                    <span class="sf-meta-chip is-success ml-1">Active</span>
                  @else
                    <span class="sf-meta-chip is-danger ml-1">Inactive</span>
                  @endif
                </div>
              </div>

              <div class="mb-3">
                <div class="text-muted small mb-1">Statistics</div>
                <ul class="list-unstyled small mb-0">
                  <li><strong>Version:</strong> {{ $submissionForm->version }}</li>
                  <li><strong>Created:</strong> {{ $submissionForm->created_at->format('M d, Y') }}</li>
                  <li><strong>Last Updated:</strong> {{ $submissionForm->updated_at->format('M d, Y') }}</li>
                  <li><strong>Sections:</strong> {{ $submissionForm->sections()->count() }}</li>
                  <li><strong>Instances:</strong> {{ $submissionForm->instances()->count() }}</li>
                </ul>
              </div>

              @if($submissionForm->instances()->exists())
                <div class="sf-alert-soft is-warning small mb-3">
                  <i class="mdi mdi-alert"></i>
                  <strong>Note:</strong> This form has {{ $submissionForm->instances()->count() }} existing instance(s).
                  Be careful when making changes that might affect data integrity.
                </div>
              @endif

              <div class="sf-alert-soft is-info small mb-0">
                <i class="mdi mdi-lightbulb-outline"></i>
                <strong>Tip:</strong> After updating form details, you can modify the form structure by adding or editing sections and fields.
              </div>
            </div>
          </div>

          <div class="workflow-board-panel mb-3">
            <div class="workflow-board-panel-header">
              <h6 class="mb-0"><i class="mdi mdi-lightning-bolt-outline"></i> Quick Actions</h6>
            </div>
            <div class="workflow-board-panel-body">
              <div class="sf-action-stack">
                <a href="{{ route('submission-forms.preview', $submissionForm) }}" class="btn btn-sm btn-outline-secondary btn-action-sm">
                  <i class="mdi mdi-eye-outline"></i> Preview Form
                </a>
                <form method="POST" action="{{ route('submission-forms.clone', $submissionForm) }}"
                      onsubmit="return confirm('Are you sure you want to clone this form?')">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-outline-secondary btn-action-sm w-100">
                    <i class="mdi mdi-content-copy"></i> Clone Form
                  </button>
                </form>
                <a href="{{ route('submission-forms.export', $submissionForm) }}" class="btn btn-sm btn-outline-secondary btn-action-sm">
                  <i class="mdi mdi-download"></i> Export Structure
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
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

  const createTemplateFormTypeUrl = '{{ route('submission-forms.template-form-types.store') }}';
  const addNewTemplateTypeValue = '__add_new__';
  let previousTemplateTypeValue = $('#template_form_type_id').val() || '';

  function createTemplateFormTypeInline() {
    const templateTypeName = window.prompt('Enter new Template Form Type name:');

    if (templateTypeName === null) {
      $('#template_form_type_id').val(previousTemplateTypeValue);
      return;
    }

    const normalizedName = templateTypeName.trim().replace(/\s+/g, ' ');
    if (!normalizedName) {
      alert('Template Form Type name cannot be empty.');
      $('#template_form_type_id').val(previousTemplateTypeValue);
      return;
    }

    const $select = $('#template_form_type_id');
    $select.prop('disabled', true);

    $.ajax({
      url: createTemplateFormTypeUrl,
      method: 'POST',
      dataType: 'json',
      data: {
        _token: '{{ csrf_token() }}',
        name: normalizedName,
      },
    }).done(function(response) {
      if (!response.success || !response.templateFormType) {
        alert('Unable to save Template Form Type. Please try again.');
        $select.val(previousTemplateTypeValue);
        return;
      }

      const newType = response.templateFormType;
      const optionSelector = 'option[value="' + newType.id + '"]';

      if ($select.find(optionSelector).length === 0) {
        const $addOption = $select.find('option[value="' + addNewTemplateTypeValue + '"]');
        $('<option>', {
          value: newType.id,
          text: newType.name,
        }).insertBefore($addOption);
      }

      previousTemplateTypeValue = String(newType.id);
      $select.val(previousTemplateTypeValue);
    }).fail(function(xhr) {
      let errorMessage = 'Unable to save Template Form Type. Please try again.';

      if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.name && xhr.responseJSON.errors.name[0]) {
        errorMessage = xhr.responseJSON.errors.name[0];
      } else if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMessage = xhr.responseJSON.message;
      }

      alert(errorMessage);
      $select.val(previousTemplateTypeValue);
    }).always(function() {
      $select.prop('disabled', false);
    });
  }

  $('#template_form_type_id').on('change', function() {
    const selectedValue = $(this).val() || '';

    if (selectedValue === addNewTemplateTypeValue) {
      createTemplateFormTypeInline();
      return;
    }

    previousTemplateTypeValue = selectedValue;
  });

  // ── Form Type toggle ──────────────────────────────────────────────────────────
  function syncFormTypeUI() {
    const isAttachment = $('input[name="form_type"]:checked').val() === 'attachment';
    $('#template_forms_section').toggle(isAttachment);
  }

  $('input[name="form_type"]').on('change', syncFormTypeUI);
  syncFormTypeUI(); // run on page load

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
    const $customerRequestWrapper = $('#customer-request-form-wrapper');

    if (isPortal) {
      $targetPages.hide();
      $placementMode.hide();
      $displayMode.hide();
      $advancedPlacement.addClass('d-none');
      $('#lims-destination-wrapper').show();
      $customerRequestWrapper.show();
      return;
    }

    $customerRequestWrapper.hide();
    $('#is_customer_request_form').prop('checked', false);
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

    // Build query string
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
    });
  }

  function buildPageCard(route, info, mode) {
    const $card = $('<div class="workflow-board-panel mb-3">');
    const $header = $('<div class="workflow-board-panel-header py-2">').html(
      '<h6 class="mb-0"><i class="mdi mdi-file-document-outline"></i> ' +
      escapeHtml(info.label) +
      ' <small class="text-muted font-weight-normal">(' + escapeHtml(route) + ')</small></h6>'
    );
    const $body = $('<div class="workflow-board-panel-body py-3">');

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

@endsection
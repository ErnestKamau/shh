@extends('layouts.lab.layout.app', ['select2' => true])

@section('title2')
  <title>Create Submission Form</title>
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
          'link' => '#',
          'name' => 'Create Form',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @include('submission-forms.partials.horizontal-gutter-styles')
    <div class="submission-forms-horizontal-gutter">

    <div class="row mb-4">
      <div class="col-12">
        <div class="card shadow-sm border-0 bg-white" style="border-radius: 15px;">
          <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
              <div>
                <h2 class="mb-1">
                  <i class="mdi mdi-form-select text-primary"></i> Create Submission Form
                </h2>
                <p class="text-muted mb-0">Provide form details, document control metadata, and placement for your new template.</p>
              </div>
              <a href="{{ route('submission-forms.index') }}" class="btn btn-outline-secondary" style="border-radius: 9px;">
                <i class="mdi mdi-arrow-left"></i> Back to Forms
              </a>
            </div>
          </div>
        </div>
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
              <form method="POST" action="{{ route('submission-forms.store') }}">
                @csrf

                {{-- Form Type selector --}}
                <div class="form-group">
                  <label class="required">Form Type</label>
                  <div class="d-flex gap-3 mt-1">
                    <div class="custom-control custom-radio custom-control-inline">
                      <input type="radio" id="form_type_template" name="form_type" value="template" class="custom-control-input"
                        {{ old('form_type', 'template') === 'template' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="form_type_template">
                        <strong>Template Form</strong>
                        <small class="d-block text-muted">Standalone form assigned to customers and sample types.</small>
                      </label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline ml-4">
                      <input type="radio" id="form_type_attachment" name="form_type" value="attachment" class="custom-control-input"
                        {{ old('form_type') === 'attachment' ? 'checked' : '' }}>
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
                  <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required maxlength="255" placeholder="Enter a descriptive name for your form">
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
                      <input type="text" class="form-control @error('document_code') is-invalid @enderror" id="document_code" name="document_code" value="{{ old('document_code') }}" required maxlength="50" placeholder="e.g. FM/QA/047">
                      @error('document_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="version" class="required">Revision Number</label>
                      <input type="text" class="form-control @error('version') is-invalid @enderror" id="version" name="version" value="{{ old('version', '1.0') }}" required maxlength="50" placeholder="e.g. 01">
                      @error('version')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="issue_date" class="required">Issue Date</label>
                      <input type="date" class="form-control @error('issue_date') is-invalid @enderror" id="issue_date" name="issue_date" value="{{ old('issue_date') }}" required>
                      @error('issue_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label for="description">Description</label>
                  <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" maxlength="1000" placeholder="Provide a brief description of what this form is used for">{{ old('description') }}</textarea>
                  @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">Optional. This helps users understand the purpose of the form.</small>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="naming_convention_prefix" class="required">Form Number Prefix</label>
                      <input type="text" class="form-control @error('naming_convention_prefix') is-invalid @enderror" id="naming_convention_prefix" name="naming_convention_prefix" value="{{ old('naming_convention_prefix', 'SF') }}" required maxlength="50" placeholder="SF">
                      @error('naming_convention_prefix')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                      <small class="form-text text-muted">Used to generate unique form numbers (e.g., SF for Submission Form).</small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="naming_convention_format" class="required">Form Number Format</label>
                      <select class="form-control @error('naming_convention_format') is-invalid @enderror" id="naming_convention_format" name="naming_convention_format" required>
                        <option value="{prefix}/{year}/{sequence}" {{ old('naming_convention_format', '{prefix}/{year}/{sequence}') == '{prefix}/{year}/{sequence}' ? 'selected' : '' }}>{prefix}/{year}/{sequence}</option>
                        <option value="{prefix}-{year}-{sequence}" {{ old('naming_convention_format') == '{prefix}-{year}-{sequence}' ? 'selected' : '' }}>{prefix}-{year}-{sequence}</option>
                        <option value="{prefix}{year}{sequence}" {{ old('naming_convention_format') == '{prefix}{year}{sequence}' ? 'selected' : '' }}>{prefix}{year}{sequence}</option>
                        <option value="{prefix}/{sequence}" {{ old('naming_convention_format') == '{prefix}/{sequence}' ? 'selected' : '' }}>{prefix}/{sequence}</option>
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
                    <option value="submission-forms.print.default" {{ old('print_template_name') == 'submission-forms.print.default' ? 'selected' : '' }}>Default Template</option>
                    <option value="submission-forms.print.microbiology" {{ old('print_template_name') == 'submission-forms.print.microbiology' ? 'selected' : '' }}>Microbiology Template</option>
                    <option value="submission-forms.print.serology" {{ old('print_template_name') == 'submission-forms.print.serology' ? 'selected' : '' }}>Serology Template</option>
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
                      <option value="{{ $templateFormType->id }}" {{ (string) old('template_form_type_id') === (string) $templateFormType->id ? 'selected' : '' }}>
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
                  <select class="form-control select2 @error('template_form_ids') is-invalid @enderror" id="template_form_ids" name="template_form_ids[]" multiple>
                    @php($selectedTemplateFormIds = old('template_form_ids', []))
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
                  <select class="form-control select2 @error('customer_ids') is-invalid @enderror" id="customer_ids" name="customer_ids[]" multiple>
                    @php($selectedCustomers = old('customer_ids', []))
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
                  <select class="form-control select2 @error('sample_type_ids') is-invalid @enderror" id="sample_type_ids" name="sample_type_ids[]" multiple>
                    @php($selectedSampleTypes = old('sample_type_ids', []))
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
                  <select class="form-control select2 @error('sample_analysis_stage_ids') is-invalid @enderror" id="sample_analysis_stage_ids" name="sample_analysis_stage_ids[]" multiple>
                    @php($selectedStages = old('sample_analysis_stage_ids', []))
                    @foreach($labSections as $section)
                      <option value="{{ $section->id }}" {{ in_array($section->id, $selectedStages) ? 'selected' : '' }}>{{ $section->name }}</option>
                    @endforeach
                  </select>
                  @error('sample_analysis_stage_ids')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">Select the lab sections associated with this form.</small>
                </div>

                <div class="form-group">
                  <div class="checkbox-option-card d-flex align-items-start p-3" style="background:#f8fafc;border:1px solid #e3e8ee;border-radius:8px;gap:12px;">
                    <div class="mt-1">
                      <input class="form-check-input" type="checkbox" id="is_customer_portal_form" name="is_customer_portal_form" value="1" {{ old('is_customer_portal_form') ? 'checked' : '' }} style="width:18px;height:18px;cursor:pointer;">
                    </div>
                    <div>
                      <label class="form-check-label font-weight-semibold mb-0" for="is_customer_portal_form" style="cursor:pointer;font-size:0.92rem;">Filled only from customer portal</label>
                      <p class="text-muted small mb-0 mt-1">If checked, this form can only be submitted via customer portal and will route to LIMS destination page(s).</p>
                    </div>
                  </div>
                </div>

                <div class="form-group" id="customer-request-form-wrapper" style="display:none;">
                  <div class="checkbox-option-card d-flex align-items-start p-3 ml-4" style="background:#f0f4ff;border:1px solid #c7d5f8;border-radius:8px;gap:12px;">
                    <div class="mt-1">
                      <input class="form-check-input" type="checkbox" id="is_customer_request_form" name="is_customer_request_form" value="1" {{ old('is_customer_request_form') ? 'checked' : '' }} style="width:18px;height:18px;cursor:pointer;">
                    </div>
                    <div>
                      <label class="form-check-label font-weight-semibold mb-0" for="is_customer_request_form" style="cursor:pointer;font-size:0.92rem;">Mark as customer request form</label>
                      <p class="text-muted small mb-0 mt-1">When multiple forms are marked, the portal API returns the most recently updated one as the default customer request form.</p>
                    </div>
                  </div>
                </div>

                <div class="form-group" id="target-pages-wrapper">
                  <label for="target_pages">Target Pages</label>
                  <select class="form-control select2 @error('target_pages') is-invalid @enderror" id="target_pages" name="target_pages[]" multiple>
                    @php($selectedPages = old('target_pages', []))
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
                    @foreach($availablePages as $page)
                      <option value="{{ $page['value'] }}" {{ in_array($page['value'], old('lims_destination_pages', []), true) ? 'selected' : '' }}>{{ $page['label'] }}</option>
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

                <div class="form-group" id="placement-mode-wrapper">
                  <label for="placement_mode" class="required">How Should The Form Appear?</label>
                  <select class="form-control @error('placement_mode') is-invalid @enderror" id="placement_mode" name="placement_mode" required>
                    <option value="button_trigger" {{ old('placement_mode', 'button_trigger') === 'button_trigger' ? 'selected' : '' }}>Open by button/action</option>
                    <option value="page_section" {{ old('placement_mode') === 'page_section' ? 'selected' : '' }}>Render inside page section</option>
                  </select>
                  @error('placement_mode')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">Choose whether users open this form from a page action or fill it directly inside a page section.</small>
                </div>

                <div class="form-group" id="display-mode-wrapper">
                  <label for="display_mode" class="required">Display Mode</label>
                  <select class="form-control @error('display_mode') is-invalid @enderror" id="display_mode" name="display_mode" required>
                    <option value="expanded" {{ old('display_mode', 'expanded') === 'expanded' ? 'selected' : '' }}>Always visible</option>
                    <option value="collapsible" {{ old('display_mode') === 'collapsible' ? 'selected' : '' }}>Collapsible</option>
                  </select>
                  @error('display_mode')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted"><strong>Always visible</strong>: form content is shown immediately on the page. <strong>Collapsible</strong>: only a header/toggle is shown; users expand it when needed.</small>
                </div>

                {{-- Advanced Placement: per-page slot / button binding --}}
                <div id="advanced-placement-section" class="d-none">
                  <div class="col-12 mt-2 mb-1 px-0">
                    <h6 class="text-primary font-weight-bold small text-uppercase">
                      <i class="mdi mdi-map-marker-multiple mr-1"></i> Advanced Placement
                    </h6>
                    <p class="text-muted small mb-2">
                      Fine-tune exactly <em>where</em> on each selected page this form should appear.
                    </p>
                  </div>
                  <div id="placement-pages-container">
                    {{-- Populated by JS --}}
                  </div>
                </div>

                <div class="form-group mt-3">
                  <div class="checkbox-option-card d-flex align-items-start p-3" style="background:#f8fafc;border:1px solid #e3e8ee;border-radius:8px;gap:12px;">
                    <div class="mt-1">
                      <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="width:18px;height:18px;cursor:pointer;">
                    </div>
                    <div>
                      <label class="form-check-label font-weight-semibold mb-0" for="is_active" style="cursor:pointer;font-size:0.92rem;">Active</label>
                      <p class="text-muted small mb-0 mt-1">Inactive forms cannot be used to create new instances.</p>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label for="start_submission_number">Start submission from number</label>
                  <input type="number" class="form-control @error('start_submission_number') is-invalid @enderror" id="start_submission_number" name="start_submission_number" value="{{ old('start_submission_number', 1) }}" min="1" placeholder="1">
                  @error('start_submission_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">
                    Provide the start submission no for this submission form.
                  </small>
                </div>

                <div class="form-group mb-0 pt-3" style="border-top:1px solid #e9ecef;">
                  <div class="d-flex align-items-center" style="gap:10px;">
                    <button type="submit" class="btn btn-primary px-4" style="border-radius:8px;font-weight:600;box-shadow:0 2px 8px rgba(0,93,255,0.15);">
                      <i class="mdi mdi-content-save mr-1"></i> Create Form
                    </button>
                    <a href="{{ route('submission-forms.index') }}" class="btn btn-outline-secondary" style="border-radius:8px;">
                      <i class="mdi mdi-close mr-1"></i> Cancel
                    </a>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">
                <i class="mdi mdi-information-outline"></i> Getting Started
              </h6>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <h6>What happens next?</h6>
                <ol class="small">
                  <li>Create your form template</li>
                  <li>Add sections to organize your fields</li>
                  <li>Add form elements (fields) to collect data</li>
                  <li>Preview and test your form</li>
                  <li>Publish the form for users</li>
                </ol>
              </div>
              
              <div class="mb-3">
                <h6>Form Naming</h6>
                <p class="small text-muted">
                  Choose a clear, descriptive name that helps users understand the form's purpose. 
                  This name will appear in form lists and user interfaces.
                </p>
              </div>
              
              <div class="mb-3">
                <h6>Form Numbers</h6>
                <p class="small text-muted">
                  Each form submission gets a unique number based on your prefix and format settings. 
                  This helps with tracking and referencing submissions.
                </p>
              </div>

              <div class="mb-3">
                <h6>Template form types</h6>
                <p class="small text-muted mb-2">
                  A <strong>template form type</strong> groups and labels your <strong>template</strong> submission forms (for example by report family or workflow). It is metadata for discovery and configuration, not the same as linking an attachment to a template below.
                </p>
                <ul class="small text-muted mb-0 pl-3">
                  <li>Pick a type from <strong>Template Form Type</strong> when that field is shown, or use <strong>+ Add New Template Form Type</strong> to define a new label on the fly.</li>
                  <li>Keeping types consistent makes it easier to find the right template when you have many forms.</li>
                </ul>
              </div>

              <div class="mb-3">
                <h6>Attachment forms and template forms</h6>
                <p class="small text-muted mb-2">
                  Use <strong>Attachment Form</strong> when this form is optional or extra paperwork that goes with one or more standalone <strong>template</strong> forms (not the PDF/print layout).
                </p>
                <ul class="small text-muted mb-0 pl-3">
                  <li><strong>Template Form</strong>: the main form (assigned to customers/sample types, etc.).</li>
                  <li><strong>Attachment Form</strong>: after you select that type, use <strong>Linked Template Forms</strong> to choose which template form(s) this attachment is tied to. Users can then open the attachment in context of those templates.</li>
                  <li><strong>Print Template</strong> (Dropdown in form details) controls how a filled form is rendered for printing—not the template form link above.</li>
                </ul>
              </div>

              <div class="mb-3">
                <h6>Filled only from customer portal</h6>
                <p class="small text-muted mb-2">
                  When <strong>Filled only from customer portal</strong> is checked, new instances are meant to be started and completed by customers through the <strong>customer portal</strong>, not from the usual in-lab form capture entry points.
                </p>
                <ul class="small text-muted mb-0 pl-3">
                  <li>Set <strong>LIMS destination page(s)</strong> so that when a portal submission is received, the lab system opens the right area of the LIMS.</li>
                  <li>For portal-only forms, placement options that target specific lab UI pages are hidden; routing is driven by those destination pages instead.</li>
                  <li>Leave this unchecked if laboratory staff should launch or fill this form from inside the lab app as usual.</li>
                </ul>
              </div>
              
              <div class="alert alert-info small">
                <i class="mdi mdi-lightbulb-outline"></i>
                <strong>Tip:</strong> Start with a simple form structure. You can always add more sections and fields later.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
  </main>

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

  <!-- (no additional modals) -->
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
  const oldPlacementSlot    = @json(old('placement_slot', []));
  const oldTriggerButtonIds = @json(old('trigger_button_ids', []));

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

  // Wire up listeners
  $('#target_pages, #placement_mode').on('change', refreshAdvancedPlacement);

  // Delegate select2 init to dynamically added selects
  $(document).on('DOMNodeInserted', '#placement-pages-container', function() {
    $('#placement-pages-container .select2-btn-picker').not('.select2-hidden-accessible').select2({
      placeholder: 'Any button (global Page Forms dropdown)',
      allowClear: true,
      width: '100%',
    });
  });

  // Trigger on load if old values were restored after validation failure
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
  #advanced-placement-section .card { border-left-width: 3px !important; }
</style>
@endsection
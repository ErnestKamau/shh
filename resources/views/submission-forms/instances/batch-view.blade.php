@extends('layouts.lab.layout.app')

@section('title2')
  <title>Submission Form - {{ $instance->form_number }}</title>
  <style>
    body {
      overflow-x: hidden !important;
    }

    .signature-image {
      width: 100% !important;
      max-height: 50px !important;
      border: 1px solid #ddd !important;
      border-radius: 4px !important;
      background: white !important;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
      object-fit: contain !important;
    }

    .compact-section .form-group {
      margin-bottom: 5px !important;
    }

    .compact-section .form-control-plaintext {
      padding: 4px 6px !important;
      font-size: 13px !important;
    }

    .compact-section .row {
      margin-bottom: 8px !important;
    }

    @media print {

      /* Hide everything except the card content */
      body * {
        visibility: hidden;
      }

      .card,
      .card * {
        visibility: visible;
      }

      .card {
        position: absolute;
        left: 50% !important;
        top: 0;
        transform: translateX(-50%) !important;
        width: 80% !important;
        max-width: 800px !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        border: none !important;
      }

      .card-body {
        padding: 15px !important;
        margin: 0 !important;
      }

      .company-header {
        padding: 15px !important;
        margin-bottom: 20px !important;
        border-bottom: 1px solid #000 !important;
      }

      .form-section {
        margin-bottom: 20px !important;
        padding: 0 !important;
        border: none !important;
      }

      .form-section-title {
        font-size: 16px !important;
        margin-bottom: 10px !important;
        padding-bottom: 5px !important;
        border-bottom: 2px solid #000 !important;
        text-align: center !important;
      }

      .form-group {
        margin-bottom: 10px !important;
      }

      .form-control-plaintext {
        padding: 5px 8px !important;
        font-size: 13px !important;
        border: 1px solid #ccc !important;
      }

      .signature-image {
        width: 100% !important;
        max-height: 60px !important;
        object-fit: contain !important;
      }

      .compact-section .form-group {
        margin-bottom: 5px !important;
      }

      .compact-section .form-control-plaintext {
        padding: 3px 6px !important;
        font-size: 12px !important;
      }

      .compact-section .row {
        margin-bottom: 8px !important;
      }

      .test-required-table {
        font-size: 12px !important;
        margin-bottom: 15px !important;
        width: 100% !important;
      }

      .test-required-table th,
      .test-required-table td {
        padding: 5px 8px !important;
        border: 1px solid #000 !important;
      }

      .sample-type-row {
        background-color: #f5f5f5 !important;
        font-weight: bold !important;
      }

      .company-logo {
        max-width: 150px !important;
        max-height: 150px !important;
      }

      .form-info-box {
        font-size: 12px !important;
        padding: 8px !important;
      }

      /* Remove all section borders */
      .form-section,
      .form-section * {
        border: none !important;
        box-shadow: none !important;
      }

      /* Center content better */
      .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
      }

      .col-md-4,
      .col-md-6,
      .col-md-12 {
        padding-left: 5px !important;
        padding-right: 5px !important;
      }

      /* Hide action buttons and breadcrumbs */
      .no-print,
      .breadcrumb,
      .btn,
      .action-buttons {
        display: none !important;
      }
    }

    .company-header {
      border-bottom: 2px solid #dee2e6;
      border-radius: 8px;
      padding: 20px 20px 10px 20px;
      margin-bottom: 30px;
      background: white;
    }

    .company-logo {
      max-width: 200px;
      max-height: 200px;
      width: auto;
      height: auto;
    }

    .form-info-box {
      /* background: #f8f9fa;
              border: 1px solid #dee2e6;
              border-radius: 8px; */
      padding: 15px;
      margin-left: 45%
    }

    .form-section {
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      background: white;
    }

    .form-section-title {
      font-size: 1.25rem;
      font-weight: 600;
      color: #007bff;
      border-bottom: 2px solid #007bff;
      padding-bottom: 10px;
      margin-bottom: 20px;
    }

    .test-required-table {
      border-collapse: collapse;
      width: 100%;
    }

    .test-required-table th {
      background: #007bff;
      color: white;
      padding: 12px;
      text-align: left;
      font-weight: 600;
    }

    .test-required-table td {
      padding: 10px 12px;
      border: 1px solid #dee2e6;
    }

    .sample-type-row {
      background: #f8f9fa;
      font-weight: 600;
      font-size: 1.1rem;
    }

    .analysis-type-row:hover {
      background: #f1f3f5;
    }

    @media print {
      .no-print {
        display: none;
      }

      .company-header {
        border-bottom: 1px solid #000;
      }

      .form-section {
        page-break-inside: avoid;
      }

      * {
        margin: 0;
        padding: 0;
      }

      @page {
        margin: 0;
        padding: 0;
      }
    }
  </style>
@endsection

@section('content2')
  <main>
    <?php
  $items = [
    [
      'link' => route('lab-home'),
      'name' => 'Lab Management',
      'icon' => null
    ],
    [
      'link' => route('submission-forms.index'),
      'name' => 'Submission Forms',
      'icon' => null
    ],
    [
      'link' => '#',
      'name' => 'Batch View - ' . $instance->form_number,
      'icon' => null
    ]
  ];
            ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-end align-items-center p-4 no-print">
      <a href="{{ route('submission-forms.instances.batch-view-print', $instance->id) }}" target="_blank"
        class="btn btn-primary mr-2">
        <i class="mdi mdi-printer"></i> Print
      </a>
      @if($batches->count() > 0)
        <a href="{{ route('view-batch-details', $batches->first()->id) }}" class="btn btn-outline-secondary">
          <i class="mdi mdi-arrow-left"></i> Back to Batch
        </a>
      @endif
    </div>

    <!-- Company Info Section -->

    <div class="card m-3">
      <div class="card-body">

        <div class="company-header">
          <div class="row align-items-center">
            <!-- Left: Company Details -->
            <div class="col-md-4">
              {{-- <h3 class="mb-3" style="font-weight: 700;">{{ strtoupper($company->name) }}</h3> --}}
              <p class="mb-1"> {{ $company->name }}</p>
              <p class="mb-1">{{ $company->street }}</p>
              <p class="mb-1">P.O. Box {{ $company->address }}</p>
              <p class="mb-1"> {{ $company->location }}</p>
            </div>

            <!-- Center: Company Logo -->
            <div class="col-md-4 text-center">
              <img src="{{ $company->logo }}" alt="{{ $company->name }} Logo" class="company-logo">
              <p class="mt-3" style="font-size:18px"><b><u>{{ $instance->submissionForm->name }}</u></b></p>
            </div>

            <!-- Right: Form Details -->
            <div class="col-md-4">
              <div class="form-info-box">

                <p class="mb-2">FM/QA/047</p>
                <p class="mb-2">Reveion : 05</p>
                <p class="mb-4">Issue Date: 11/09/2023</p>
                <p class="mb-0"><b class="text-danger">Form Number:</b> {{ $instance->form_number }}</p>

              </div>
            </div>
          </div>
        </div>

        <!-- Form Sections -->
        @foreach($instance->submissionForm->sections as $section)
          @php
            $sectionTitle = strtoupper($section->title);
            $excludeTitles = ['TESTS REQUIRED', 'TEST REQUIRED'];
          @endphp
          @if(!in_array($sectionTitle, $excludeTitles))
            @php
              $isSamplingSection = in_array(strtolower($section->title), [
                'sampling and submission details',
                'submission details',
                'sampling details'
              ]);
             @endphp
            <div class="form-section {{ $isSamplingSection ? 'compact-section' : '' }}">
              <h4 class="form-section-title">
                <i class="mdi mdi-folder-outline"></i> {{ $section->title }}
              </h4>
              @if($sectionTitle === 'DECLARATION')
                {{-- Declaration section: show only title and description --}}
                @if($section->description)
                  <p class="text-muted mb-3">{{ $section->description }}</p>
                @endif
              @else
                {{-- Other sections: show form fields but no description --}}
                @foreach($section->elementHolders as $holder)
                  @if($holder->holder_type === 'field')
                    <div class="row">
                      @foreach($holder->elements as $element)
                        @php
                          // Skip client unit field if we're in Client Details section as it's included in the table
                          $isClientUnitInClientDetails = $element->element_type === 'client_unit_select' &&
                            $sectionTitle === 'CLIENT DETAILS';
                         @endphp

                        @if(!$isClientUnitInClientDetails)
                          <div
                            class="col-md-{{  $sectionTitle === 'CLIENT DETAILS' ? 12 : getColumnWidth($holder->elements->count()) }} mb-3">
                            <div class="{{  $sectionTitle === 'CLIENT DETAILS' ? '' : 'form-group' }}">
                              @if($sectionTitle !== 'CLIENT DETAILS')
                                <label class="font-weight-bold">
                                  {{ $element->label }}
                                  @if($element->is_required)
                                    <span class="text-danger">*</span>
                                  @endif
                                </label>
                              @endif
                              <div
                                class="{{  strtolower($section->title) === 'client details' ? '' : 'form-control-plaintext border rounded p-2 bg-light' }}">
                                @php
                                  $elementValue = $instance->values()
                                    ->where('submission_form_element_id', $element->id)
                                    ->first();
                                  $value = $elementValue ? $elementValue->value : ($element->default_value ?? '-');

                                  // Handle special element types with custom display names
                                  if ($element->element_type === 'client_select' && $value && is_numeric($value)) {
                                    $client = \App\Models\CRM\CRMCustomer::find($value);
                                    if ($client) {
                                      // Get client unit values from form instance (can be multiple like Unit and Section)
                                      $clientUnitElements = $instance->values()
                                        ->with('element')
                                        ->whereHas('element', function ($query) {
                                          $query->where('element_type', 'client_unit_select');
                                        })
                                        ->get();

                                      $clientUnitValue = 'N/A';
                                      $clientSectionValue = 'N/A';

                                      foreach ($clientUnitElements as $unitVal) {
                                        $resolvedVal = 'N/A';
                                        if ($unitVal->value) {
                                          $unitModel = \App\Models\CRM\CRMCompanyUnit::find($unitVal->value);
                                          $resolvedVal = $unitModel ? $unitModel->name : $unitVal->value;
                                        }

                                        if (str_contains(strtolower($unitVal->element->label), 'section')) {
                                          $clientSectionValue = $resolvedVal;
                                        } else {
                                          $clientUnitValue = $resolvedVal;
                                        }
                                      }

                                      $value = '<div class="table-responsive">
                                                                                                                       <table class="table table-sm table-bordered mb-0 w-100" style="font-size: 14px; background-color: white;">
                                                                                                                         <tr>
                                                                                                                           <td class="font-weight-bold" style="width: 25%; background-color: #f8f9fa;">Client Name</td>
                                                                                                                           <td>' . ($client->name ?? 'N/A') . '</td>
                                                                                                                         </tr>
                                                                                                                         <tr>
                                                                                                                           <td class="font-weight-bold" style="background-color: #f8f9fa;">Address</td>
                                                                                                                           <td>' . ($client->address ?? 'N/A') . '</td>
                                                                                                                         </tr>
                                                                                                                         <tr>
                                                                                                                           <td class="font-weight-bold" style="background-color: #f8f9fa;">Telephone</td>
                                                                                                                           <td>' . ($client->telephone ?? 'N/A') . '</td>
                                                                                                                         </tr>
                                                                                                                         <tr>
                                                                                                                           <td class="font-weight-bold" style="background-color: #f8f9fa;">Email</td>
                                                                                                                           <td>' . ($client->email ?? 'N/A') . '</td>
                                                                                                                         </tr>
                                                                                                                         <tr>
                                                                                                                           <td class="font-weight-bold" style="background-color: #f8f9fa;">Client Unit</td>
                                                                                                                           <td>' . $clientUnitValue . '</td>
                                                                                                                         </tr>
                                                                                                                         <tr>
                                                                                                                           <td class="font-weight-bold" style="background-color: #f8f9fa;">Client Section</td>
                                                                                                                           <td>' . $clientSectionValue . '</td>
                                                                                                                         </tr>
                                                                                                                       </table>
                                                                                                                     </div>';
                                    }
                                  } else {
                                    $value = $instance->resolveDisplayValue($element, $value);
                                  }

                                  // Check if this is a signature field
                                  $isSignature = str_contains(strtolower($element->label ?? ''), 'signature') ||
                                    str_contains(strtolower($element->name ?? ''), 'signature');
                                 @endphp

                                @if($isSignature && $value)
                                  @php
                                    $sigSrc = $value;
                                    if (!str_starts_with($value, 'data:image')) {
                                      if (str_starts_with($value, '/storage') || str_starts_with($value, 'http')) {
                                        $sigSrc = $value;
                                      } else {
                                        $sigSrc = \Illuminate\Support\Facades\Storage::disk('public')->url($value);
                                      }
                                    }
                                  @endphp
                                  <img src="{{ $sigSrc }}" alt="Signature" class="signature-image"
                                    style="max-width: 200px; max-height: 100px; border: 1px solid #ddd; border-radius: 4px;">
                                @else
                                  {{-- Display regular text value or HTML content --}}
                                  {!! $value !!}
                                @endif
                              </div>
                            </div>
                          </div>
                        @endif
                      @endforeach
                    </div>
                  @else
                    <!-- Text holder -->
                    @foreach($holder->elements as $element)
                      <div class="alert alert-light">
                        <strong>{{ $element->label }}</strong>
                        @if($element->help_text)
                          <p class="mb-0 mt-2">{{ $element->help_text }}</p>
                        @endif
                      </div>
                    @endforeach
                  @endif
                @endforeach
              @endif
            </div>
          @endif
        @endforeach

        <!-- Test Required Section -->
        <div class="form-section">
          <h4 class="form-section-title">
            <i class="mdi mdi-flask-outline"></i> Tests Required
          </h4>

          @if(count($processedSampleData) > 0)
            <div class="table-responsive">
              <table class="test-required-table table table-bordered">
                <thead>
                  <tr>
                    <th>Test Required</th>
                    <th>No of Samples</th>
                    <th>Lab No</th>
                    <th>Reported By & Date</th>
                    <th>Sent By & Date</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($processedSampleData as $sampleTypeGroup)

                    <!-- Analysis Type Rows -->
                    @foreach($sampleTypeGroup['analyses'] as $analysisData)
                      <tr class="analysis-type-row">
                        <td>{{ $analysisData['analysis_type_name'] }}</td>
                        <td class="text-center">{{ $analysisData['sample_count'] }}</td>
                        <td>{{ $analysisData['code_range'] }}</td>
                        <td>{{ $analysisData['reported_info'] }}</td>
                        <td>{{ $analysisData['sent_info'] }}</td>
                      </tr>
                    @endforeach
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="alert alert-info">
              <i class="mdi mdi-information-outline"></i> No samples found for this submission form.
            </div>
          @endif
        </div>
      </div>
    </div>
  </main>
@endsection

@section('script2')
  <script>
          // Auto-hide alerts after 5 seconds
          setTimeout (fun ction ()  {
            $('.alert-dismissible').fadeOut('slow');
          }, 5000);
  </script>
@endsection

@php
  function getColumnWidth($elementCount)
  {
    switch ($elementCount) {
      case 1:
        return 12;
      case 2:
        return 6;
      case 3:
        return 4;
      case 4:
        return 3;
      default:
        return 12 / min($elementCount, 6);
    }
  }
@endphp
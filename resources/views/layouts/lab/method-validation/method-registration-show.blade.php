@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>{{ $method->name }} - Method Registration Details</title>
  <style type="text/css">
    .tab-card {
      border: 1px solid #eee;
    }

    .tab-card-header {
      background: none;
    }

    /* Default mode */
    .tab-card-header > .nav-tabs {
      border: none;
      margin: 0px;
    }

    .tab-card-header > .nav-tabs > li {
      margin-right: 2px;
    }

    .tab-card-header > .nav-tabs > li > a {
      border: 0;
      border-bottom: 2px solid transparent;
      margin-right: 0;
      color: #737373;
      padding: 2px 15px;
    }

    .tab-card-header > .nav-tabs > li > a.show {
      border-bottom: 2px solid #007bff;
      color: #007bff;
    }

    .tab-card-header > .nav-tabs > li > a:hover {
      color: #007bff;
    }

    .tab-card .nav-link.active {
      background-color: #dadccd !important;
      border: 1px solid #cccebf !important;
    }

    .tab-card-header > .tab-content {
      padding-bottom: 0;
    }

    .my-small-text {
      font-size: 12px !important;
    }
  </style>
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
          'link' => route('method-validation.registration'),
          'name' => 'Method Registration',
          'icon' => null
        ),
        array(
          'link' => null,
          'name' => $method->name,
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <h2 class="p-4">
      <i class="mdi mdi-file-document-multiple"></i> Method Registration Details
      <a href="{{ route('method-validation.registration') }}" class="btn btn-primary btn-sm float-right">
        <i class="mdi mdi-arrow-left"></i> Back to Registration
      </a>
    </h2>
    <br>
    
    <div class="row">
      <div class="col-sm-4 p-2">
        <div class="card tab-card">
          <div class="card-header tab-card-header">
            <h5 class="card-title"><i class="mdi mdi-information-outline"></i> Method Information</h5>
          </div>
          <div class="card-body">
            <table class="table table-condensed my-small-text table-borderless">
              <tr>
                <td><strong>Method Name:</strong></td>
                <td>{{ $method->name }}</td>
              </tr>
              <tr>
                <td><strong>Method Code:</strong></td>
                <td><code class="text-info">{{ $method->code ?? '-' }}</code></td>
              </tr>
              <tr>
                <td><strong>Method Type:</strong></td>
                <td>
                  @php
                    $methodTypeConfig = \App\Models\System\SystemConfigurationsType::where('configuration_type', 'Methods Types')->first();
                    $methodType = $methodTypeConfig ? \App\Models\System\SystemConfiguration::where('configuration_type_id', $methodTypeConfig->id)->where('id', $method->method_type_id)->first() : null;
                  @endphp
                  <span class="badge badge-primary">{{ $methodType->value ?? 'Not Set' }}</span>
                </td>
              </tr>
              <tr>
                <td><strong>Category:</strong></td>
                <td>
                  @if($method->is_ltm == 1)
                    <span class="badge badge-success">
                      <i class="mdi mdi-flask"></i> Laboratory Test Method
                    </span>
                  @else
                    <span class="badge badge-info">
                      <i class="mdi mdi-book-open-page-variant"></i> Reference Method
                    </span>
                  @endif
                </td>
              </tr>
              <tr>
                <td><strong>Status:</strong></td>
                <td>
                  @if($method->active == 1)
                    <span class="badge badge-success">
                      <i class="mdi mdi-check-circle"></i> Active
                    </span>
                  @else
                    <span class="badge badge-danger">
                      <i class="mdi mdi-close-circle"></i> Inactive
                    </span>
                  @endif
                </td>
              </tr>
              <tr>
                <td><strong>Validation Status:</strong></td>
                <td>
                  <span class="badge badge-warning">
                    <i class="mdi mdi-clock-outline"></i> Sent for Validation
                  </span>
                </td>
              </tr>
              <tr>
                <td><strong>Company:</strong></td>
                <td>{{ $method->company->name ?? 'Not Set' }}</td>
              </tr>
              <tr>
                <td><strong>Lab Method:</strong></td>
                <td>
                  <div class="d-flex flex-column">
                    <strong class="text-primary">{{ $method->name }}</strong>
                    <small class="text-muted">Code: {{ $method->code }}</small>
                  </div>
                </td>
              </tr>
              <tr>
                <td><strong>Reference Method:</strong></td>
                <td>
                  @if($method->reference_type_id)
                    @php
                      $referenceMethod = \App\AnalysisMethod::find($method->reference_type_id);
                    @endphp
                    <div class="d-flex flex-column">
                      <strong class="text-success">{{ $referenceMethod->name ?? 'Reference method not found' }}</strong>
                      <small class="text-muted">Code: {{ $referenceMethod->code ?? '-' }}</small>
                    </div>
                  @else
                    <span class="text-muted">No reference method assigned</span>
                  @endif
                </td>
              </tr>
              @if($method->description)
              <tr>
                <td><strong>Description:</strong></td>
                <td>{{ $method->description }}</td>
              </tr>
              @endif
              <tr>
                <td><strong>Created Date:</strong></td>
                <td>{{ $method->created_at ? $method->created_at->format('M d, Y H:i') : 'Not Available' }}</td>
              </tr>
              <tr>
                <td><strong>Last Updated:</strong></td>
                <td>{{ $method->updated_at ? $method->updated_at->format('M d, Y H:i') : 'Not Available' }}</td>
              </tr>
            </table>
          </div>
        </div>
      </div>
      
      <div class="col-sm-8 p-2">
        <div class="card tab-card">
          <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="method-tabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="method-comparison-tab" data-toggle="tab" href="#method-comparison" role="tab" aria-controls="method-comparison" aria-selected="true">
                  <i class="mdi mdi-compare"></i> Method Comparison
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="validation-sample-tab" data-toggle="tab" href="#validation-sample" role="tab" aria-controls="validation-sample" aria-selected="false">
                  <i class="mdi mdi-flask-outline"></i> Validation Sample
                </a>
              </li>
            </ul>
          </div>

          <div class="tab-content" id="method-tabs-content">
            <div class="tab-pane fade show active p-3" id="method-comparison" role="tabpanel" aria-labelledby="method-comparison-tab">
              <h5 class="p-2"><i class="mdi mdi-compare"></i> Lab Method vs Reference Method</h5>
              <div class="row">
                <div class="col-md-6">
                  <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                      <h6 class="mb-0"><i class="mdi mdi-flask"></i> Lab Method (Being Validated)</h6>
                    </div>
                    <div class="card-body">
                      <table class="table table-sm table-borderless my-small-text">
                        <tr>
                          <td><strong>Name:</strong></td>
                          <td>{{ $method->name }}</td>
                        </tr>
                        <tr>
                          <td><strong>Code:</strong></td>
                          <td><code class="text-info">{{ $method->code }}</code></td>
                        </tr>
                        <tr>
                          <td><strong>Type:</strong></td>
                          <td>
                            @php
                              $methodTypeConfig = \App\Models\System\SystemConfigurationsType::where('configuration_type', 'Methods Types')->first();
                              $methodType = $methodTypeConfig ? \App\Models\System\SystemConfiguration::where('configuration_type_id', $methodTypeConfig->id)->where('id', $method->method_type_id)->first() : null;
                            @endphp
                            <span class="badge badge-primary">{{ $methodType->value ?? 'Not Set' }}</span>
                          </td>
                        </tr>
                        <tr>
                          <td><strong>Status:</strong></td>
                          <td>
                            @if($method->active == 1)
                              <span class="badge badge-success">Active</span>
                            @else
                              <span class="badge badge-danger">Inactive</span>
                            @endif
                          </td>
                        </tr>
                        <tr>
                          <td><strong>Description:</strong></td>
                          <td>{{ $method->description ?? '-' }}</td>
                        </tr>
                      </table>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-6">
                  @if($method->reference_type_id)
                    @php
                      $referenceMethod = \App\AnalysisMethod::find($method->reference_type_id);
                    @endphp
                    <div class="card border-success">
                      <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="mdi mdi-book-open-page-variant"></i> Reference Method</h6>
                      </div>
                      <div class="card-body">
                        <table class="table table-sm table-borderless my-small-text">
                          <tr>
                            <td><strong>Name:</strong></td>
                            <td>{{ $referenceMethod->name ?? 'Not Found' }}</td>
                          </tr>
                          <tr>
                            <td><strong>Code:</strong></td>
                            <td><code class="text-info">{{ $referenceMethod->code ?? '-' }}</code></td>
                          </tr>
                          <tr>
                            <td><strong>Type:</strong></td>
                            <td>
                              @if($referenceMethod)
                                @php
                                  $refMethodType = $methodTypeConfig ? \App\Models\System\SystemConfiguration::where('configuration_type_id', $methodTypeConfig->id)->where('id', $referenceMethod->method_type_id)->first() : null;
                                @endphp
                                <span class="badge badge-success">{{ $refMethodType->value ?? 'Not Set' }}</span>
                              @else
                                <span class="text-muted">-</span>
                              @endif
                            </td>
                          </tr>
                          <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                              @if($referenceMethod && $referenceMethod->active == 1)
                                <span class="badge badge-success">Active</span>
                              @else
                                <span class="badge badge-danger">Inactive</span>
                              @endif
                            </td>
                          </tr>
                          <tr>
                            <td><strong>Description:</strong></td>
                            <td>{{ $referenceMethod->description ?? '-' }}</td>
                          </tr>
                        </table>
                      </div>
                    </div>
                  @else
                    <div class="card border-warning">
                      <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="mdi mdi-alert"></i> No Reference Method</h6>
                      </div>
                      <div class="card-body text-center">
                        <i class="mdi mdi-information-outline text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No reference method has been assigned to this lab method.</p>
                      </div>
                    </div>
                  @endif
                </div>
              </div>
            </div>
            
            <div class="tab-pane fade p-3" id="validation-sample" role="tabpanel" aria-labelledby="validation-sample-tab">
              @if($method->sampleHeader)
              <h5 class="p-2"><i class="mdi mdi-flask-outline"></i> Validation Sample Information</h5>
              <div class="table-responsive p-2">
                <table class="table table-sm table-condensed table-bordered table-hover my-small-text">
                  <tr>
                    <td><strong>Batch Code:</strong></td>
                    <td><code class="text-success">{{ $method->sampleHeader->batch_code }}</code></td>
                  </tr>
                  <tr>
                    <td><strong>Sample Codes:</strong></td>
                    <td>
                      @if($method->sampleHeader->samples->count() > 0)
                        @foreach($method->sampleHeader->samples as $sample)
                          <code class="text-primary mr-1">{{ $sample->sample_code }}</code>
                        @endforeach
                      @else
                        <span class="text-muted">No sample codes available</span>
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <td><strong>Sample Type:</strong></td>
                    <td>{{ $method->sampleHeader->sample_type->name ?? 'Not Set' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Client:</strong></td>
                    <td>{{ $method->sampleHeader->client->name ?? 'Not Set' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Sample Status:</strong></td>
                    <td><span class="badge badge-info">{{ $method->sampleHeader->status ?? 'Not Set' }}</span></td>
                  </tr>
                  <tr>
                    <td><strong>Date Received:</strong></td>
                    <td>{{ $method->sampleHeader->receipt_date ? \Carbon\Carbon::parse($method->sampleHeader->receipt_date)->format('M d, Y H:i') : 'Not Set' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Date Collected:</strong></td>
                    <td>{{ $method->sampleHeader->date_collected ? \Carbon\Carbon::parse($method->sampleHeader->date_collected)->format('M d, Y H:i') : 'Not Set' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Target Date:</strong></td>
                    <td>{{ $method->sampleHeader->get_target_date->date ?? 'Not Set' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Priority:</strong></td>
                    <td>
                      @if($method->sampleHeader->priority == 'High')
                        <span class="badge badge-danger">High</span>
                      @elseif($method->sampleHeader->priority == 'Medium')
                        <span class="badge badge-warning">Medium</span>
                      @else
                        <span class="badge badge-secondary">Low</span>
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <td><strong>Validation Method:</strong></td>
                    <td>
                      @if($method->sampleHeader->is_validation_method == 1)
                        <span class="badge badge-warning"><i class="mdi mdi-check-circle"></i> Yes</span>
                      @else
                        <span class="badge badge-secondary">No</span>
                      @endif
                    </td>
                  </tr>
                  @if($method->sampleHeader->description)
                  <tr>
                    <td><strong>Sample Description:</strong></td>
                    <td>{{ $method->sampleHeader->description }}</td>
                  </tr>
                  @endif
                </table>
              </div>
              @else
              <div class="alert alert-info">
                <i class="mdi mdi-information-outline"></i> No validation sample information available for this method.
              </div>
              @endif
            </div>
            
          </div>
        </div>
      </div>
    </div>
  </main>
@endsection

@section('script2')
<script>
$(document).ready(function() {
    // Any additional JavaScript for the show page
    console.log('Method registration show page loaded');
});
</script>
@endsection

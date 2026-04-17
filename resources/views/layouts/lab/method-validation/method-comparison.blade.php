@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true, 'datePicker'=>true])

@section('title2')
<title>Method Comparison | Lab Management</title>
@endsection

@section('content2')
<?php
    $items = array(
        array(
            'link' => route('dashboard-lab'),
            'name' => 'Dashboard',
            'icon' => null
        ),
        array(
            'link' => route('method-validation.registration'),
            'name' => 'Method Validation',
            'icon' => null
        ),
        array(
            'link' => route('method-validation.data-review'),
            'name' => 'Data Review & Analytics',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => 'Method Comparison',
            'icon' => null
        )
    );
?>
<x-bread-crumb :items="$items"></x-bread-crumb>

<style>
    .statistics-card {
        background-color: #f8f9fa;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .comparison-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .acceptable {
        color: #28a745;
        font-weight: 600;
    }
    
    .not-acceptable {
        color: #dc3545;
        font-weight: 600;
    }
</style>

<div class="container-fluid">

    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                        <h4 class="mb-0">
                            <i class="mdi mdi-chart-line text-primary"></i> Method Validation Comparison
                        </h4>
                            @if(isset($all_analytes) && count($all_analytes) > 0)
                                <div class="mt-2">
                                    <span class="h5 text-primary">
                                        <strong>Analytes:</strong> 
                                        @foreach($all_analytes as $index => $analyte)
                                            <span class="badge badge-primary badge-lg">{{ $analyte }}</span>@if($index < count($all_analytes) - 1), @endif
                                        @endforeach
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="d-flex" style="gap: 0.5rem;">
                            @if(auth()->user()->checkApproveMethodsRole() && isset($validation_method))
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary dropdown-toggle" 
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                        title="Approve/Reject Method">
                                    <i class="mdi mdi-check-decagram"></i> Actions
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item text-success" href="#" 
                                       onclick="openApprovalModal({{ $validation_method->id }}, 'approve')">
                                        <i class="mdi mdi-thumb-up"></i> Approve Method
                                    </a>
                                    <a class="dropdown-item text-danger" href="#" 
                                       onclick="openApprovalModal({{ $validation_method->id }}, 'reject')">
                                        <i class="mdi mdi-thumb-down"></i> Reject Method
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-warning" href="#" 
                                       onclick="openReturnSampleModal({{ $validation_method->id }})">
                                        <i class="mdi mdi-arrow-left"></i> Return Sample to Lab
                                    </a>
                                </div>
                            </div>
                            @endif
                            <a href="{{ route('method-validation.data-review') }}" class="btn btn-secondary">
                                <i class="mdi mdi-arrow-left"></i> Back to Data Review
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($error))
        <!-- Error Message -->
        <div class="row">
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="mdi mdi-alert-circle"></i> {{ $error }}
                </div>
            </div>
        </div>
    @else
        <!-- Method Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="mdi mdi-flask text-primary"></i> Lab Method (Being Validated)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="statistics-card">
                            <p><strong>Code:</strong> {{ $validation_method->code }}</p>
                            <p><strong>Name:</strong> {{ $validation_method->name }}</p>
                            <p><strong>Description:</strong> {{ $validation_method->description ?? 'N/A' }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge {{ $validation_method->validation_status_badge_class }}">
                                    <i class="mdi {{ $validation_method->validation_status_icon }}"></i> {{ $validation_method->validation_status_display }}
                                </span>
                            </p>
                            @if(isset($testing_option))
                            <p><strong>Testing Approach:</strong>
                                @if($testing_option === 'lab_with_reference_results')
                                    <span class="badge badge-info">Lab + Reference Results</span>
                                    <small class="text-muted d-block">Single sample with pre-provided reference values</small>
                                @else
                                    <span class="badge badge-primary">Lab + Reference Method</span>
                                    <small class="text-muted d-block">Separate samples for lab and reference methods</small>
                                @endif
                            </p>
                            @endif
                            @if($validation_method->sampleHeader)
                            <p><strong>Validation Sample:</strong> 
                                <code class="text-success">{{ $validation_method->sampleHeader->batch_code }}</code>
                            </p>
                            <p><strong>Sample Codes:</strong>
                                @if($validation_method->sampleHeader->samples->count() > 0)
                                    @foreach($validation_method->sampleHeader->samples as $sample)
                                        <code class="text-primary mr-1">{{ $sample->sample_code }}</code>
                                    @endforeach
                                @else
                                    <span class="text-muted">No sample codes</span>
                                @endif
                            </p>
                            @endif
                            <p><strong>Created:</strong> {{ $validation_method->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="mdi mdi-check-circle text-success"></i> Reference Method
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="statistics-card">
                            <p><strong>Code:</strong> {{ $reference_method->code }}</p>
                            <p><strong>Name:</strong> {{ $reference_method->name }}</p>
                            <p><strong>Description:</strong> {{ $reference_method->description ?? 'N/A' }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge {{ $reference_method->validation_status_badge_class }}">
                                    <i class="mdi {{ $reference_method->validation_status_icon }}"></i> {{ $reference_method->validation_status_display }}
                                </span>
                            </p>
                            @if(isset($testing_option) && $testing_option === 'lab_with_reference_results')
                            <p><strong>Reference Data:</strong> 
                                <span class="badge badge-warning">Pre-provided Results</span>
                            </p>
                            <p><strong>Source:</strong> <span class="text-muted">Reference values provided during validation setup</span></p>
                            @if(isset($validation_info['reference_results']))
                                <p><strong>Reference Values:</strong> 
                                    @foreach($validation_info['reference_results'] as $paramId => $value)
                                        <span class="badge badge-primary badge-lg mr-1">{{ $value }}</span>@if(!$loop->last), @endif
                                    @endforeach
                                </p>
                            @endif
                            @else
                            @if($reference_method->sampleHeader)
                            <p><strong>Reference Sample:</strong> 
                                <code class="text-info">{{ $reference_method->sampleHeader->batch_code }}</code>
                            </p>
                            <p><strong>Sample Codes:</strong>
                                @if($reference_method->sampleHeader->samples->count() > 0)
                                    @foreach($reference_method->sampleHeader->samples as $sample)
                                        <code class="text-success mr-1">{{ $sample->sample_code }}</code>
                                    @endforeach
                                @else
                                    <span class="text-muted">No sample codes</span>
                                @endif
                            </p>
                            @else
                            <p><strong>Reference Sample:</strong> 
                                <span class="text-muted">Uses same validation sample</span>
                            </p>
                            @endif
                            @endif
                            <p><strong>Created:</strong> {{ $reference_method->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Raw Results Display - Separate Tables for Each Analyte -->
        @if(count($results) > 0)
            @foreach($results as $analyte => $result)
                @if(!isset($result['error']))
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                                    <i class="mdi mdi-database text-primary"></i> Raw Results Data - {{ $result['analyte'] }}
                        </h6>
                    </div>
                    <div class="card-body">
                            <div class="row">
                                    <!-- Validation Method Raw Results (Lab Method) -->
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">
                                            <i class="mdi mdi-flask"></i> {{ $validation_method->name }} (Lab Method)
                                    </h6>
                                        @if(!empty($result['validation']['detailed_results']))
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Sample Code</th>
                                                            <th>Result</th>
                                                            <th>Analyst</th>
                                                            <th>Date/Time</th>
                                                            <th>Equipment</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($result['validation']['detailed_results'] as $detail)
                                                            <tr>
                                                                <td>
                                                                    <span class="badge badge-primary">{{ $detail['sample_code'] }}</span>
                                                                </td>
                                                                <td><strong>{{ $detail['result'] }}</strong></td>
                                                                <td>{{ $detail['analyst'] }}</td>
                                                                <td>
                                                                    <small class="text-muted">
                                                                        {{ $detail['reporting_datetime'] ? \Carbon\Carbon::parse($detail['reporting_datetime'])->format('M d, Y H:i') : 'N/A' }}
                                                                    </small>
                                                                </td>
                                                                <td>
                                                                    <small class="text-muted">{{ $detail['equipment'] }}</small>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                </div>
                                                                @else
                                            <div class="alert alert-info">
                                                <i class="mdi mdi-information"></i> No detailed results available for validation method
                                            </div>
                                        @endif
                                </div>

                                <!-- Reference Method Raw Results -->
                                <div class="col-md-6">
                                    <h6 class="text-success mb-3">
                                        <i class="mdi mdi-check-circle"></i> {{ $reference_method->name }} (Reference Method)
                                    </h6>
                                        @if(!empty($result['reference']['detailed_results']))
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Sample Code</th>
                                                            <th>Result</th>
                                                            @if(!isset($result['reference']['detailed_results'][0]['is_pre_provided']) || !$result['reference']['detailed_results'][0]['is_pre_provided'])
                                                            <th>Analyst</th>
                                                            <th>Date/Time</th>
                                                            <th>Equipment</th>
                                                            @endif
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($result['reference']['detailed_results'] as $detail)
                                                            <tr>
                                                                <td>
                                                                    @if(isset($detail['is_pre_provided']) && $detail['is_pre_provided'])
                                                                        <span class="badge badge-warning">{{ $detail['sample_code'] }}</span>
                                                                    @else
                                                                        <span class="badge badge-success">{{ $detail['sample_code'] }}</span>
                                                                    @endif
                                                                </td>
                                                                <td><strong>{{ $detail['result'] }}</strong></td>
                                                                @if(!isset($detail['is_pre_provided']) || !$detail['is_pre_provided'])
                                                                <td>{{ $detail['analyst'] }}</td>
                                                                <td>
                                                                    <small class="text-muted">
                                                                        {{ $detail['reporting_datetime'] ? \Carbon\Carbon::parse($detail['reporting_datetime'])->format('M d, Y H:i') : 'N/A' }}
                                                                    </small>
                                                                </td>
                                                                <td>
                                                                    <small class="text-muted">{{ $detail['equipment'] }}</small>
                                                                </td>
                                                                @endif
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                </div>
                                                                @else
                                            <div class="alert alert-info">
                                                <i class="mdi mdi-information"></i> No detailed results available for reference method
                                            </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                        @else
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert-circle"></i> 
                        <strong>No comparison data available</strong>
                        <br>
                        @if(isset($testing_option) && $testing_option === 'lab_with_reference_results')
                            <small>This method uses pre-provided reference results, but either:</small>
                            <ul class="mb-0 mt-2">
                                <li>Lab testing is not yet complete, or</li>
                                <li>Reference results were not properly stored during validation setup</li>
                            </ul>
                        @else
                            <small>Either lab testing is not complete or reference method results are not available.</small>
                        @endif
                        <br>
                        <small class="text-muted mt-2 d-block">
                            <strong>Troubleshooting:</strong> Check that results have been entered for both the lab method and reference method/values.
                        </small>
                        
                        @if(config('app.debug'))
                        <div class="mt-3 p-2 bg-light border rounded">
                            <small class="text-muted">
                                <strong>Debug Info:</strong><br>
                                Testing Option: {{ $testing_option ?? 'Unknown' }}<br>
                                Method ID: {{ $validation_method->id }}<br>
                                Reference Method ID: {{ $reference_method->id }}<br>
                                Sample Header ID: {{ $validation_method->sampleHeader ? $validation_method->sampleHeader->id : 'None' }}<br>
                                @if(isset($validation_analytes))
                                Validation Analytes: {{ implode(', ', $validation_analytes) }}<br>
                                @endif
                                @if(isset($reference_analytes))
                                Reference Analytes: {{ implode(', ', $reference_analytes) }}
                                @endif
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Comparison Results -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="mdi mdi-chart-bar text-info"></i> Statistical Comparison Results
                        </h6>
                    </div>
                    <div class="card-body">
                        @if(count($results) > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered comparison-table">
                                    <thead>
                                        <tr>
                                            <th>Analyte</th>
                                            <th colspan="2" class="text-center text-primary">Validation Method</th>
                                            <th class="text-center text-success">Reference Method</th>
                                            <th colspan="3" class="text-center">Comparison</th>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <th class="text-primary">Mean</th>
                                            <th class="text-primary">Std Dev</th>
                                            <th class="text-success">Mean</th>
                                            <th>% Difference</th>
                                            <th>T-Value</th>
                                            <th>Acceptable</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($results as $analyte => $result)
                                        @if(!isset($result['error']))
                                        <tr>
                                            <td><strong>{{ $result['analyte'] }}</strong></td>
                                            <td>{{ $result['validation']['mean'] }}</td>
                                            <td>{{ $result['validation']['std_dev'] }}</td>
                                            <td>{{ $result['reference']['mean'] }}</td>
                                            <td>{{ $result['comparison']['percentage_difference'] }}%</td>
                                            <td>{{ $result['comparison']['t_value'] }}</td>
                                            <td>
                                                @if($result['comparison']['is_acceptable'])
                                                    <span class="acceptable">
                                                        <i class="mdi mdi-check"></i> Yes
                                                    </span>
                                                @else
                                                    <span class="not-acceptable">
                                                        <i class="mdi mdi-close"></i> No
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        @else
                                        <tr>
                                            <td><strong>{{ $result['analyte'] }}</strong></td>
                                            <td colspan="6" class="text-center text-warning">
                                                <i class="mdi mdi-alert"></i> {{ $result['error'] }}
                                            </td>
                                        </tr>
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="mdi mdi-information"></i> No comparison data available. 
                                Both methods need to have results for the same analytes to perform comparison.
                            </div>
                        @endif

                        <!-- Validation Metrics Section -->
                        @if(count($results) > 0)
                            <div class="mt-4">
                                <h6 class="mb-3">
                                    <i class="mdi mdi-chart-line text-primary"></i> Method Performance Assessment
                                </h6>
                                
                                @foreach($results as $analyte => $result)
                                    @if(!isset($result['error']) && isset($result['validation_metrics']))
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                <h6 class="mb-0">{{ $result['analyte'] }} - Method Performance Summary</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <!-- Accuracy -->
                                                    <div class="col-md-3">
                                                        <div class="statistics-card text-center">
                                                            <div class="mb-2">
                                                                <i class="mdi mdi-target text-primary" style="font-size: 2rem;"></i>
                                                            </div>
                                                            <h6 class="text-primary mb-2">Accuracy</h6>
                                                            <p class="mb-1"><strong>How close to target:</strong></p>
                                                            <p class="mb-1">{{ $result['validation_metrics']['accuracy']['recovery_percentage'] }}% recovery</p>
                                                            <small class="text-muted">Target: {{ $result['validation_metrics']['accuracy']['acceptable_range'] }}</small>
                                                            <div class="mt-2">
                                                                @if($result['validation_metrics']['accuracy']['is_acceptable'])
                                                                    <span class="badge badge-success badge-pill">
                                                                        <i class="mdi mdi-check"></i> Good
                                                                    </span>
                                                                @else
                                                                    <span class="badge badge-danger badge-pill">
                                                                        <i class="mdi mdi-alert"></i> Needs Attention
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Precision -->
                                                    <div class="col-md-3">
                                                        <div class="statistics-card text-center">
                                                            <div class="mb-2">
                                                                <i class="mdi mdi-chart-line text-info" style="font-size: 2rem;"></i>
                                                            </div>
                                                            <h6 class="text-info mb-2">Precision</h6>
                                                            <p class="mb-1"><strong>Consistency of results:</strong></p>
                                                            <p class="mb-1">{{ $result['validation_metrics']['precision']['rsd_percentage'] }}% variation</p>
                                                            <small class="text-muted">Lower is better</small>
                                                            <div class="mt-2">
                                                                @if($result['validation_metrics']['precision']['is_acceptable'])
                                                                    <span class="badge badge-success badge-pill">
                                                                        <i class="mdi mdi-check"></i> Consistent
                                                                    </span>
                                                                @else
                                                                    <span class="badge badge-danger badge-pill">
                                                                        <i class="mdi mdi-alert"></i> Too Variable
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Linearity -->
                                                    <div class="col-md-3">
                                                        <div class="statistics-card text-center">
                                                            <div class="mb-2">
                                                                <i class="mdi mdi-trending-up text-warning" style="font-size: 2rem;"></i>
                                                            </div>
                                                            <h6 class="text-warning mb-2">Linearity</h6>
                                                            <p class="mb-1"><strong>Straight line relationship:</strong></p>
                                                            <p class="mb-1">R² = {{ $result['validation_metrics']['linearity']['correlation_r2'] }}</p>
                                                            <small class="text-muted">Closer to 1.0 is better</small>
                                                            <div class="mt-2">
                                                                @if($result['validation_metrics']['linearity']['is_acceptable'])
                                                                    <span class="badge badge-success badge-pill">
                                                                        <i class="mdi mdi-check"></i> Linear
                                                                    </span>
                                                                @else
                                                                    <span class="badge badge-danger badge-pill">
                                                                        <i class="mdi mdi-alert"></i> Not Linear
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Robustness -->
                                                    <div class="col-md-3">
                                                        <div class="statistics-card text-center">
                                                            <div class="mb-2">
                                                                <i class="mdi mdi-shield-check text-success" style="font-size: 2rem;"></i>
                                                            </div>
                                                            <h6 class="text-success mb-2">Robustness</h6>
                                                            <p class="mb-1"><strong>Method stability:</strong></p>
                                                            <p class="mb-1">{{ $result['validation_metrics']['robustness']['deviation_percentage'] }}% deviation</p>
                                                            <small class="text-muted">Lower is better</small>
                                                            <div class="mt-2">
                                                                @if($result['validation_metrics']['robustness']['is_acceptable'])
                                                                    <span class="badge badge-success badge-pill">
                                                                        <i class="mdi mdi-check"></i> Stable
                                                                    </span>
                                                                @else
                                                                    <span class="badge badge-danger badge-pill">
                                                                        <i class="mdi mdi-alert"></i> Unstable
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Overall Assessment -->
                                                <div class="mt-3 p-3 bg-light rounded">
                                                    <h6 class="mb-2">
                                                        <i class="mdi mdi-clipboard-check text-primary"></i> Overall Assessment
                                                    </h6>
                                                    @php
                                                        $totalMetrics = 4;
                                                        $acceptableMetrics = 0;
                                                        if($result['validation_metrics']['accuracy']['is_acceptable']) $acceptableMetrics++;
                                                        if($result['validation_metrics']['precision']['is_acceptable']) $acceptableMetrics++;
                                                        if($result['validation_metrics']['linearity']['is_acceptable']) $acceptableMetrics++;
                                                        if($result['validation_metrics']['robustness']['is_acceptable']) $acceptableMetrics++;
                                                        $overallScore = round(($acceptableMetrics / $totalMetrics) * 100);
                                                    @endphp
                                                    
                                                    <div class="row align-items-center">
                                                        <div class="col-md-6">
                                                            <p class="mb-1">
                                                                <strong>Method Performance Score:</strong> {{ $acceptableMetrics }}/{{ $totalMetrics }} parameters
                                                            </p>
                                                            <div class="progress mb-2" style="height: 20px;">
                                                                <div class="progress-bar {{ $overallScore >= 75 ? 'bg-success' : ($overallScore >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                                                     role="progressbar" style="width: {{ $overallScore }}%">
                                                                    {{ $overallScore }}%
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 text-right">
                                                            @if($overallScore >= 75)
                                                                <span class="badge badge-success badge-lg">
                                                                    <i class="mdi mdi-check-circle"></i> Method is Ready for Use
                                                                </span>
                                                            @elseif($overallScore >= 50)
                                                                <span class="badge badge-warning badge-lg">
                                                                    <i class="mdi mdi-alert-circle"></i> Method Needs Improvement
                                                                </span>
                                                            @else
                                                                <span class="badge badge-danger badge-lg">
                                                                    <i class="mdi mdi-close-circle"></i> Method Requires Major Changes
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary -->
        @if(count($results) > 0)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="mdi mdi-summary text-info"></i> Validation Summary
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $totalAnalytes = count($results);
                            $acceptableCount = 0;
                            foreach($results as $result) {
                                if(!isset($result['error']) && $result['comparison']['is_acceptable']) {
                                    $acceptableCount++;
                                }
                            }
                            $acceptanceRate = $totalAnalytes > 0 ? round(($acceptableCount / $totalAnalytes) * 100, 2) : 0;
                        @endphp
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="statistics-card text-center">
                                    <h4 class="text-primary">{{ $totalAnalytes }}</h4>
                                    <p class="mb-0">Total Analytes</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="statistics-card text-center">
                                    <h4 class="text-success">{{ $acceptableCount }}</h4>
                                    <p class="mb-0">Acceptable</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="statistics-card text-center">
                                    <h4 class="text-danger">{{ $totalAnalytes - $acceptableCount }}</h4>
                                    <p class="mb-0">Not Acceptable</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="statistics-card text-center">
                                    <h4 class="{{ $acceptanceRate >= 80 ? 'text-success' : 'text-warning' }}">{{ $acceptanceRate }}%</h4>
                                    <p class="mb-0">Acceptance Rate</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif
</div>

@endsection

<!-- Method Approval Modal -->
<div class="modal fade" id="methodApprovalModal" tabindex="-1" role="dialog" aria-labelledby="methodApprovalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="methodApprovalModalLabel">
                    <i class="mdi mdi-check-decagram text-primary"></i> 
                    <span id="approvalModalTitle">Method Approval</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="mdi mdi-information"></i>
                    <span id="approvalModalMessage">Please confirm your decision for this method validation.</span>
                </div>
                
                <div class="form-group">
                    <label for="approvalAction" class="control-label">Action <span class="text-danger">*</span></label>
                    <select id="approvalAction" class="form-control" required>
                        <option value="">Select Action...</option>
                        <option value="approve">Approve Method</option>
                        <option value="reject">Reject Method</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="approvalReason" class="control-label">Reason/Comments <span class="text-danger">*</span></label>
                    <textarea id="approvalReason" class="form-control" rows="4" 
                              placeholder="Please provide reason for approval or rejection..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="mdi mdi-close"></i> Cancel
                </button>
                <button type="button" onclick="submitApproval()" class="btn btn-primary" 
                        id="submitApprovalBtn" disabled>
                    <i class="mdi mdi-check"></i> Submit Decision
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Return Sample to Lab Modal -->
<div class="modal fade" id="returnSampleModal" tabindex="-1" role="dialog" aria-labelledby="returnSampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returnSampleModalLabel">
                    <i class="mdi mdi-arrow-left text-warning"></i> 
                    <span id="returnModalTitle">Return Sample to Lab</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="mdi mdi-alert"></i>
                    <span id="returnModalMessage">Please provide a reason for returning this sample to the lab.</span>
                </div>
                
                <div class="form-group">
                    <label for="returnAction" class="control-label">Action <span class="text-danger">*</span></label>
                    <select id="returnAction" class="form-control" required>
                        <option value="">Select Action...</option>
                        <option value="return_to_lab">Return Sample to Lab</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="returnReason" class="control-label">Reason/Comments <span class="text-danger">*</span></label>
                    <textarea id="returnReason" class="form-control" rows="4" 
                              placeholder="Please provide reason for returning the sample to the lab..." required></textarea>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="sendReturnNotification">
                    <label class="form-check-label" for="sendReturnNotification">
                        Send Email Notification
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="mdi mdi-close"></i> Cancel
                </button>
                <button type="button" onclick="submitReturnSample()" class="btn btn-warning" 
                        id="submitReturnBtn" disabled>
                    <i class="mdi mdi-arrow-left"></i> Submit Decision
                </button>
            </div>
        </div>
    </div>
</div>

@section('script2')
<script>
let currentMethodId = null;

// Open approval modal
function openApprovalModal(methodId, action) {
    currentMethodId = methodId;
    
    // Pre-select the action in the dropdown
    $('#approvalAction').val(action);
    
    if (action === 'approve') {
        $('#approvalModalTitle').text('Approve Method Validation');
        $('#approvalModalMessage').text('Please confirm your decision to approve this method validation.');
    } else if (action === 'reject') {
        $('#approvalModalTitle').text('Reject Method Validation');
        $('#approvalModalMessage').text('Please confirm your decision to reject this method validation.');
    }
    
    // Clear the reason field
    $('#approvalReason').val('');
    
    // Trigger validation check
    $('#approvalAction, #approvalReason').trigger('change');
    
    $('#methodApprovalModal').modal('show');
}

// Open return sample modal
function openReturnSampleModal(methodId) {
    currentMethodId = methodId;
    
    // Pre-select the action in the dropdown
    $('#returnAction').val('return_to_lab');
    
    // Clear the reason field and notification checkbox
    $('#returnReason').val('');
    $('#sendReturnNotification').prop('checked', false);
    
    // Trigger validation check
    $('#returnAction, #returnReason').trigger('change');
    
    $('#returnSampleModal').modal('show');
}

// Submit approval
function submitApproval() {
    const action = $('#approvalAction').val();
    const reason = $('#approvalReason').val();
    
    if (!action || !reason || reason.length < 10) {
        alert('Please select an action and provide a reason (minimum 10 characters).');
        return;
    }
    
    submitMethodAction(currentMethodId, action, reason);
}

// Submit return sample
function submitReturnSample() {
    const action = $('#returnAction').val();
    const reason = $('#returnReason').val();
    
    if (!action || !reason || reason.length < 10) {
        alert('Please select an action and provide a reason (minimum 10 characters).');
        return;
    }
    
    submitMethodAction(currentMethodId, action, reason);
}

// Submit method action via AJAX
function submitMethodAction(methodId, action, reason) {
    // Show loading state
    $('#submitApprovalBtn, #submitReturnBtn').prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Processing...');
    
    $.ajax({
        url: '{{ route("method-validation.process-action") }}',
        method: 'POST',
        data: {
            method_id: methodId,
            action: action,
            reason: reason,
            _token: '{{ csrf_token() }}'
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            // Hide modals
            $('#methodApprovalModal, #returnSampleModal').modal('hide');
            
            // Show success message
            if (action === 'approve') {
                alert('Method approved successfully!');
            } else if (action === 'reject') {
                alert('Method rejected successfully!');
            } else if (action === 'return_to_lab') {
                alert('Sample returned to lab successfully!');
            }
            
            // Redirect back to data review
            window.location.href = '{{ route("method-validation.data-review") }}';
        },
        error: function(xhr) {
            alert('Error processing action. Please try again.');
            console.error('Error:', xhr);
            
            // Reset buttons
            $('#submitApprovalBtn').prop('disabled', false).html('<i class="mdi mdi-check"></i> Submit Decision');
            $('#submitReturnBtn').prop('disabled', false).html('<i class="mdi mdi-arrow-left"></i> Submit Decision');
        }
    });
}

// Enable/disable submit buttons based on form completion
$(document).ready(function() {
    $('#approvalAction, #approvalReason').on('change input', function() {
        var action = $('#approvalAction').val();
        var reason = $('#approvalReason').val();
        var isValid = action && reason && reason.length >= 10;
        $('#submitApprovalBtn').prop('disabled', !isValid);
    });
    
    $('#returnAction, #returnReason').on('change input', function() {
        var action = $('#returnAction').val();
        var reason = $('#returnReason').val();
        var isValid = action && reason && reason.length >= 10;
        $('#submitReturnBtn').prop('disabled', !isValid);
    });
    
    // Reset forms when modals are hidden
    $('#methodApprovalModal').on('hidden.bs.modal', function() {
        // Only reset if not being opened again immediately
        setTimeout(function() {
            $('#approvalAction').val('');
            $('#approvalReason').val('');
            $('#submitApprovalBtn').prop('disabled', true).html('<i class="mdi mdi-check"></i> Submit Decision');
        }, 100);
    });
    
    $('#returnSampleModal').on('hidden.bs.modal', function() {
        // Only reset if not being opened again immediately
        setTimeout(function() {
            $('#returnAction').val('');
            $('#returnReason').val('');
            $('#sendReturnNotification').prop('checked', false);
            $('#submitReturnBtn').prop('disabled', true).html('<i class="mdi mdi-arrow-left"></i> Submit Decision');
        }, 100);
    });
});
</script>
@endsection

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AnalysisMethod;

class MethodValidationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function methodComparison($methodId)
    {
        try {
            $method = AnalysisMethod::with(['sampleHeader.samples', 'company'])->findOrFail($methodId);
            
            // Log the method being compared
            \Log::info('Method comparison started', [
                'method_id' => $method->id,
                'method_name' => $method->name,
                'method_code' => $method->code,
                'validation_status' => $method->validation_status,
                'is_ltm' => $method->is_ltm,
                'reference_type_id' => $method->reference_type_id,
                'sample_header_id' => $method->sample_header_id
            ]);
            
            // Get the reference method using the reference_type_id
            $referenceMethod = null;
            
            if ($method->reference_type_id) {
                // Get the reference method with its own sample data (if any)
                $referenceMethod = AnalysisMethod::with(['sampleHeader.samples', 'company'])->find($method->reference_type_id);
            }

            if (!$referenceMethod) {
                // Log debug information
                \Log::info('Reference method not found', [
                    'validation_method_id' => $method->id,
                    'validation_method_name' => $method->name,
                    'has_reference_type_id' => !is_null($method->reference_type_id),
                    'reference_type_id' => $method->reference_type_id,
                    'sample_header_id' => $method->sample_header_id
                ]);
                
                $errorMessage = 'No reference method found for comparison. ';
                if ($method->reference_type_id) {
                    $errorMessage .= 'Reference method ID ' . $method->reference_type_id . ' was not found.';
                } else {
                    $errorMessage .= 'No reference method is assigned to this lab method.';
                }
                
                return view('layouts.lab.method-validation.method-comparison', [
                    'error' => $errorMessage,
                    'validation_method' => $method,
                    'reference_method' => null,
                    'results' => []
                ]);
            }

            // Get validation sample results using the sample header
            // Detect testing option and handle accordingly
            $testingOption = $this->getTestingOption($method);
            $validationInfo = $this->getValidationInfo($method);
            
            if ($testingOption === 'lab_with_reference_results') {
                // Option 2: Single sample with pre-provided reference results
                [$validationResults, $referenceResults] = $this->handleSingleSampleComparison($method, $referenceMethod);
            } else {
                // Option 1: Two separate samples (existing logic)
                [$validationResults, $referenceResults] = $this->handleDualSampleComparison($method, $referenceMethod);
            }

            // Group by analyte name - handle both RawResult and Result models
            $validationResults = $validationResults->groupBy(function($item) {
                // Handle both RawResult and Result models
                if (isset($item->analyte) && $item->analyte) {
                    return $item->analyte->name; // RawResult model
                } elseif (isset($item->analyte_id)) {
                    $analyte = \App\Analyte::find($item->analyte_id);
                    return $analyte ? $analyte->name : 'Unknown Analyte'; // Result model
                }
                return 'Unknown Analyte';
            });

            $referenceResults = $referenceResults->groupBy(function($item) {
                // For Result model, we need to get analyte name from analyte_id
                if (isset($item->analyte_id)) {
                    $analyte = \App\Analyte::find($item->analyte_id);
                    return $analyte ? $analyte->name : 'Unknown Analyte';
                } elseif (isset($item->analyte) && $item->analyte) {
                    return $item->analyte->name; // In case it has the relationship
                }
                return 'Unknown Analyte';
            });

            // Enhanced debug information
            \Log::info('Method Validation Debug', [
                'validation_method_id' => $method->id,
                'reference_method_id' => $referenceMethod->id,
                'testing_option' => $testingOption,
                'sample_header_id' => $method->sampleHeader ? $method->sampleHeader->id : null,
                'validation_results_count' => $validationResults->count(),
                'reference_results_count' => $referenceResults->count(),
                'validation_analytes' => $validationResults->keys()->toArray(),
                'reference_analytes' => $referenceResults->keys()->toArray(),
                'validation_results_sample' => $validationResults->flatten()->take(3)->toArray(),
                'reference_results_sample' => $referenceResults->flatten()->take(3)->toArray()
            ]);

            // Perform statistical calculations
            $comparisonResults = [];
            $analyteNames = [];
            
            // Check if we have any data at all
            if ($validationResults->isEmpty() && $referenceResults->isEmpty()) {
                \Log::warning('No validation or reference results found', [
                    'method_id' => $method->id,
                    'sample_header_id' => $method->sampleHeader ? $method->sampleHeader->id : null,
                    'testing_option' => $testingOption
                ]);
            } else if ($validationResults->isEmpty()) {
                \Log::warning('No validation results found - lab testing may not be complete', [
                    'method_id' => $method->id,
                    'sample_header_id' => $method->sampleHeader ? $method->sampleHeader->id : null
                ]);
            } else if ($referenceResults->isEmpty()) {
                \Log::warning('No reference results found', [
                    'method_id' => $method->id,
                    'reference_method_id' => $referenceMethod->id,
                    'testing_option' => $testingOption
                ]);
            }
            
            foreach ($validationResults as $analyteName => $validationData) {
                $referenceData = $referenceResults->get($analyteName, collect());
                
                if ($referenceData->isNotEmpty()) {
                    $comparisonResults[$analyteName] = $this->calculateStatistics(
                        $validationData, 
                        $referenceData, 
                        $analyteName,
                        $testingOption
                    );
                    $analyteNames[] = $analyteName;
                } else {
                    \Log::info("No reference data found for analyte: {$analyteName}");
                }
            }

            // Get analyte names from both methods for header display
            $validationAnalytes = $validationResults->keys()->toArray();
            $referenceAnalytes = $referenceResults->keys()->toArray();

            return view('layouts.lab.method-validation.method-comparison', [
                'validation_method' => $method,
                'reference_method' => $referenceMethod,
                'results' => $comparisonResults,
                'validation_analytes' => $validationAnalytes,
                'reference_analytes' => $referenceAnalytes,
                'all_analytes' => array_unique(array_merge($validationAnalytes, $referenceAnalytes)),
                'testing_option' => $testingOption,
                'validation_info' => $validationInfo
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading method comparison: ' . $e->getMessage());
        }
    }

    private function calculateStatistics($validationData, $referenceData, $analyteName, $testingOption = 'lab_and_reference')
    {
        $validationValues = $validationData->pluck('result')->filter()->map(function($value) {
            return is_numeric($value) ? (float)$value : null;
        })->filter();

        $referenceValues = $referenceData->pluck('result')->filter()->map(function($value) {
            return is_numeric($value) ? (float)$value : null;
        })->filter();

        if ($validationValues->isEmpty() || $referenceValues->isEmpty()) {
            return [
                'analyte' => $analyteName,
                'error' => 'Insufficient data for comparison'
            ];
        }

        // Basic Statistics
        $validationMean = $validationValues->avg();
        $validationStd = $this->calculateStandardDeviation($validationValues);
        $validationCount = $validationValues->count();
        
        $referenceMean = $referenceValues->avg();
        $referenceCount = $referenceValues->count();

        // 1. ACCURACY (Spike Recovery Test)
        $accuracy = $this->calculateAccuracy($validationValues, $referenceMean);
        
        // 2. PRECISION (Repeatability Test)
        $precision = $this->calculatePrecision($validationValues);
        
        // 3. LINEARITY (if we have multiple concentration levels)
        $linearity = $this->calculateLinearity($validationValues, $referenceValues);
        
        // 4. ROBUSTNESS (Method Stability)
        $robustness = $this->calculateRobustness($validationValues, $referenceMean);

        // Basic Comparison
        $percentageDiff = $referenceMean != 0 ? abs(($validationMean - $referenceMean) / $referenceMean) * 100 : 0;
        $tValue = $this->calculateTTest($validationValues, $referenceValues);
        
        // Get acceptable percentage difference from system configuration
        $maxPercentageDiff = \App\Models\System\SystemConfiguration::where('key', 'method_comparison_max_percentage_difference')->value('value') ?? 10;

        // Get unique sample codes for both methods
        $validationSampleCodes = $validationData->pluck('sample.sample_code')->unique()->filter()->values()->toArray();
        $referenceSampleCodes = $referenceData->pluck('sample.sample_code')->unique()->filter()->values()->toArray();

        // Get detailed raw results with analyst information
        $validationDetailedResults = $validationData->map(function($result) {
            return [
                'sample_code' => $result->sample ? $result->sample->sample_code : 'N/A',
                'result' => $result->result,
                'analyst' => $result->user ? $result->user->name : ($result->operator ? $result->operator->name : 'Unknown'),
                'reporting_datetime' => $result->reporting_datetime,
                'equipment' => $result->equipment ? $result->equipment->name : 'N/A'
            ];
        })->values()->toArray();

        // Handle reference detailed results differently based on testing option
        if ($testingOption === 'lab_with_reference_results') {
            // Option 2: Pre-provided reference results - only show sample code and result
            $referenceDetailedResults = $referenceData->map(function($result) {
                return [
                    'sample_code' => $result->sample_detail_code ?? 'REF',
                    'result' => $result->result,
                    'is_pre_provided' => true
                ];
            })->values()->toArray();
        } else {
            // Option 1: Lab-tested reference results - show all fields
            $referenceDetailedResults = $referenceData->map(function($result) {
                return [
                    'sample_code' => $result->sample ? $result->sample->sample_code : 'N/A',
                    'result' => $result->result,
                    'analyst' => $result->user ? $result->user->name : ($result->operator ? $result->operator->name : 'Unknown'),
                    'reporting_datetime' => $result->reporting_datetime,
                    'equipment' => $result->equipment ? $result->equipment->name : 'N/A',
                    'is_pre_provided' => false
                ];
            })->values()->toArray();
        }

        return [
            'analyte' => $analyteName,
            'validation' => [
                'mean' => round($validationMean, 4),
                'std_dev' => round($validationStd, 4),
                'count' => $validationCount,
                'values' => $validationValues->toArray(),
                'sample_codes' => $validationSampleCodes,
                'detailed_results' => $validationDetailedResults
            ],
            'reference' => [
                'mean' => round($referenceMean, 4),
                'count' => $referenceCount,
                'values' => $referenceValues->toArray(),
                'sample_codes' => $referenceSampleCodes,
                'detailed_results' => $referenceDetailedResults
            ],
            'comparison' => [
                'percentage_difference' => round($percentageDiff, 2),
                't_value' => round($tValue, 4),
                'is_acceptable' => $percentageDiff <= $maxPercentageDiff,
                'acceptable_range' => '≤ ' . $maxPercentageDiff . '%'
            ],
            'validation_metrics' => [
                'accuracy' => $accuracy,
                'precision' => $precision,
                'linearity' => $linearity,
                'robustness' => $robustness
            ]
        ];
    }

    private function calculateStandardDeviation($values)
    {
        $mean = $values->avg();
        $variance = $values->map(function($value) use ($mean) {
            return pow($value - $mean, 2);
        })->avg();
        return sqrt($variance);
    }

    private function calculateTTest($values1, $values2)
    {
        $mean1 = $values1->avg();
        $mean2 = $values2->avg();
        $std1 = $this->calculateStandardDeviation($values1);
        $std2 = $this->calculateStandardDeviation($values2);
        $n1 = $values1->count();
        $n2 = $values2->count();

        $standardError = sqrt(($std1 * $std1 / $n1) + ($std2 * $std2 / $n2));
        
        return $standardError != 0 ? ($mean1 - $mean2) / $standardError : 0;
    }

    /**
     * Calculate Accuracy (Spike Recovery Test)
     * % Recovery = (Mean measured value / Spiked value) × 100
     */
    private function calculateAccuracy($validationValues, $referenceMean)
    {
        $meanMeasured = $validationValues->avg();
        
        // Get acceptable range from system configuration
        $minAccuracy = \App\Models\System\SystemConfiguration::where('key', 'accuracy_min_percentage')->value('value') ?? 95;
        $maxAccuracy = \App\Models\System\SystemConfiguration::where('key', 'accuracy_max_percentage')->value('value') ?? 105;
        
        $recoveryPercentage = $referenceMean != 0 ? ($meanMeasured / $referenceMean) * 100 : 0;
        
        return [
            'parameter' => 'Accuracy',
            'spiked_value' => round($referenceMean, 4),
            'measured_mean' => round($meanMeasured, 4),
            'recovery_percentage' => round($recoveryPercentage, 2),
            'acceptable_range' => $minAccuracy . '-' . $maxAccuracy . '%',
            'is_acceptable' => $referenceMean != 0 ? ($recoveryPercentage >= $minAccuracy && $recoveryPercentage <= $maxAccuracy) : false
        ];
    }

    /**
     * Calculate Precision (Repeatability Test)
     * %RSD = (SD / Mean) × 100
     */
    private function calculatePrecision($validationValues)
    {
        $mean = $validationValues->avg();
        $sd = $this->calculateStandardDeviation($validationValues);
        $rsd = $mean != 0 ? ($sd / $mean) * 100 : 0;
        
        // Get acceptable range from system configuration
        $maxRsd = \App\Models\System\SystemConfiguration::where('key', 'precision_max_rsd_percentage')->value('value') ?? 5;
        
        return [
            'parameter' => 'Precision',
            'sample_mean' => round($mean, 4),
            'standard_deviation' => round($sd, 4),
            'rsd_percentage' => round($rsd, 2),
            'acceptable_range' => '≤ ' . $maxRsd . '%',
            'is_acceptable' => $rsd <= $maxRsd
        ];
    }

    /**
     * Calculate Linearity (Regression Analysis)
     * R² correlation coefficient
     */
    private function calculateLinearity($validationValues, $referenceValues)
    {
        // Get acceptable range from system configuration
        $minRSquared = \App\Models\System\SystemConfiguration::where('key', 'linearity_min_r_squared')->value('value') ?? 0.99;
        
        // For simplicity, assume validation values are responses and reference values are concentrations
        // In a real scenario, you'd have multiple concentration levels
        $n = min($validationValues->count(), $referenceValues->count());
        
        if ($n < 2) {
            return [
                'parameter' => 'Linearity',
                'correlation_r' => 0,
                'correlation_r2' => 0,
                'slope' => 0,
                'intercept' => 0,
                'acceptable_range' => 'R² ≥ ' . $minRSquared,
                'is_acceptable' => false
            ];
        }

        $xValues = $referenceValues->take($n)->values();
        $yValues = $validationValues->take($n)->values();
        
        $sumX = $xValues->sum();
        $sumY = $yValues->sum();
        $sumXY = $xValues->zip($yValues)->map(function($pair) {
            return $pair[0] * $pair[1];
        })->sum();
        $sumX2 = $xValues->map(function($x) { return $x * $x; })->sum();
        $sumY2 = $yValues->map(function($y) { return $y * $y; })->sum();
        
        // Calculate slope (m)
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        
        // Calculate intercept (b)
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        // Calculate correlation coefficient (R)
        $numerator = $n * $sumXY - $sumX * $sumY;
        $denominator = sqrt(($n * $sumX2 - $sumX * $sumX) * ($n * $sumY2 - $sumY * $sumY));
        $r = $denominator != 0 ? $numerator / $denominator : 0;
        $r2 = $r * $r;
        
        return [
            'parameter' => 'Linearity',
            'correlation_r' => round($r, 4),
            'correlation_r2' => round($r2, 4),
            'slope' => round($slope, 4),
            'intercept' => round($intercept, 4),
            'acceptable_range' => 'R² ≥ ' . $minRSquared,
            'is_acceptable' => $r2 >= $minRSquared
        ];
    }

    /**
     * Calculate Robustness (Method Stability)
     * % Deviation from target
     */
    private function calculateRobustness($validationValues, $targetValue)
    {
        $mean = $validationValues->avg();
        $sd = $this->calculateStandardDeviation($validationValues);
        $deviation = $targetValue != 0 ? abs(($mean - $targetValue) / $targetValue) * 100 : 0;
        
        // Get acceptable range from system configuration
        $maxDeviation = \App\Models\System\SystemConfiguration::where('key', 'robustness_max_deviation_percentage')->value('value') ?? 2;
        
        return [
            'parameter' => 'Robustness',
            'target_value' => round($targetValue, 4),
            'measured_mean' => round($mean, 4),
            'standard_deviation' => round($sd, 4),
            'deviation_percentage' => round($deviation, 2),
            'acceptable_range' => '≤ ±' . $maxDeviation . '%',
            'is_acceptable' => $deviation <= $maxDeviation
        ];
    }
    
    public function processAction(Request $request)
    {
        // Check if user has permission to approve methods
        if (!auth()->user()->checkApproveMethodsRole()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to approve or reject methods.'
            ], 403);
        }
        
        $request->validate([
            'method_id' => 'required|exists:analysis_methods,id',
            'action' => 'required|in:approve,reject,return_to_lab',
            'reason' => 'required|string|min:10'
        ]);
        
        try {
            $method = AnalysisMethod::findOrFail($request->method_id);
            
            switch ($request->action) {
                case 'approve':
                    $method->validation_status = 'validated';
                    $message = 'Method approved successfully.';
                    break;
                    
                case 'reject':
                    $method->validation_status = 'validation_failed';
                    $message = 'Method rejected successfully.';
                    break;
                    
                case 'return_to_lab':
                    $method->validation_status = 'in_validation';
                    
                    // Update sample header status back to lab analysis
                    if ($method->sampleHeader) {
                        $method->sampleHeader->status = 'Samples In Lab';
                        $method->sampleHeader->save();
                        
                        // Update chain of custody
                        $this->updateChainOfCustody($method->sampleHeader->id, $request->reason);
                    }
                    
                    $message = 'Sample returned to lab successfully.';
                    break;
            }
            
            $method->save();
            
            // Log the action
            \Log::info("Method validation action processed", [
                'method_id' => $request->method_id,
                'method_name' => $method->name,
                'action' => $request->action,
                'reason' => $request->reason,
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name
            ]);
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Method action processing failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process action. Please try again.'
            ], 500);
        }
    }
    
    private function updateChainOfCustody($sampleHeaderId, $reason)
    {
        try {
            // Close the current chain of custody entry
            \App\ChainOfCustody::where('sample_header_id', $sampleHeaderId)
                ->whereNull('moved_out_date')
                ->update([
                    'moved_out_date' => \Carbon\Carbon::now(),
                    'moved_out_by' => \Auth::user()->id,
                    'comments' => 'Sample returned to lab for continued validation. Reason: ' . $reason
                ]);

            // Create new chain of custody entry for lab analysis
            $custody = new \App\ChainOfCustody;
            $custody->workflow_stage = 'Samples In Lab';
            $custody->tracking_stage_id = 1;
            $custody->moved_in_by = \Auth::user()->id;
            $custody->sample_header_id = $sampleHeaderId;
            $custody->comments = 'Sample returned from method comparison for continued validation. Return reason: ' . $reason;
            $custody->save();

            return true;
        } catch (\Exception $e) {
            \Log::error("Chain of custody update failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Detect testing option from validation info stored in sample header
     */
    private function getTestingOption($method)
    {
        if (!$method->sampleHeader) {
            return 'lab_and_reference'; // Default fallback
        }
        
        try {
            $validationInfo = json_decode($method->sampleHeader->how_sample_was_obtained, true);
            return $validationInfo['testing_option'] ?? 'lab_and_reference';
        } catch (\Exception $e) {
            \Log::warning('Could not parse validation info for method ' . $method->id);
            return 'lab_and_reference'; // Default fallback
        }
    }
    
    /**
     * Get validation info from sample header
     */
    private function getValidationInfo($method)
    {
        if (!$method->sampleHeader) {
            return null;
        }
        
        try {
            return json_decode($method->sampleHeader->how_sample_was_obtained, true);
        } catch (\Exception $e) {
            \Log::warning('Could not parse validation info for method ' . $method->id);
            return null;
        }
    }
    
    /**
     * Handle Option 2: Single sample with pre-provided reference results
     */
    private function handleSingleSampleComparison($method, $referenceMethod)
    {
        $validationResults = collect();
        $referenceResults = collect();
        
        if ($method->sampleHeader) {
            // Get lab results from RawResult table (actual testing)
            $validationResults = \App\RawResult::where('sample_header_id', $method->sampleHeader->id)
                ->where('method_id', $method->id)
                ->where('is_readability', 1)
                ->with(['analyte', 'sample', 'user', 'operator'])
                ->get();
            
            // Fallback: also check Result table for lab results
            if ($validationResults->isEmpty()) {
                $validationResults = \App\Result::where('sample_header_id', $method->sampleHeader->id)
                    ->where('captured_result_id', '>', 0)
                    ->whereNotNull('result')
                    ->get();
            }

            // Get pre-provided reference results from Result table (exactly as they were inserted)
            $referenceResults = \App\Result::where('sample_header_id', $method->sampleHeader->id)
                ->where('ltm_method_id', $referenceMethod->id)
                ->where('captured_result_id', 0)
                ->where('comments', 'Reference result provided for validation comparison')
                ->get();
            
            // Fallback: try with -REF suffix pattern if no results found
            if ($referenceResults->isEmpty()) {
                $referenceResults = \App\Result::where('sample_header_id', $method->sampleHeader->id)
                    ->where('captured_result_id', 0)
                    ->where('sample_detail_code', 'LIKE', '%-REF')
                    ->get();
            }
            
            // Another fallback: broader search for reference results
            if ($referenceResults->isEmpty()) {
                $referenceResults = \App\Result::where('sample_header_id', $method->sampleHeader->id)
                    ->where('captured_result_id', 0)
                    ->whereNotNull('result')
                    ->where('comments', 'LIKE', '%reference%')
                    ->get();
            }
            
            \Log::info('Option 2 Data Retrieval', [
                'sample_header_id' => $method->sampleHeader->id,
                'lab_method_id' => $method->id,
                'reference_method_id' => $referenceMethod->id,
                'validation_results_count' => $validationResults->count(),
                'reference_results_count' => $referenceResults->count(),
                'validation_query_debug' => [
                    'sample_header_id' => $method->sampleHeader->id,
                    'method_id' => $method->id,
                    'is_readability' => 1
                ],
                'reference_query_debug' => [
                    'sample_header_id' => $method->sampleHeader->id,
                    'ltm_method_id' => $referenceMethod->id,
                    'captured_result_id' => 0,
                    'sample_detail_code_pattern' => '%-REF'
                ]
            ]);
        }
        
        return [$validationResults, $referenceResults];
    }
    
    /**
     * Handle Option 1: Two separate samples (existing logic)
     */
    private function handleDualSampleComparison($method, $referenceMethod)
    {
        $validationResults = collect();
        $referenceResults = collect();
        
        if ($method->sampleHeader) {
            $validationResults = \App\RawResult::where('sample_header_id', $method->sampleHeader->id)
                ->where('method_id', $method->id)
                ->where('is_readability', 1)
                ->with(['analyte', 'sample', 'user', 'operator'])
                ->get();

            if ($referenceMethod->sampleHeader) {
                $referenceResults = \App\RawResult::where('sample_header_id', $referenceMethod->sampleHeader->id)
                    ->where('method_id', $referenceMethod->id)
                    ->where('is_readability', 1)
                    ->with(['analyte', 'sample', 'user', 'operator'])
                    ->get();
            } else {
                $referenceResults = \App\RawResult::where('sample_header_id', $method->sampleHeader->id)
                    ->where('method_id', $referenceMethod->id)
                    ->where('is_readability', 1)
                    ->with(['analyte', 'sample', 'user', 'operator'])
                    ->get();
            }
        }
        
        return [$validationResults, $referenceResults];
    }
}

<?php

namespace App\Http\Controllers\Lab\Reports;

use App\Http\Controllers\Controller;
use App\AnalysisType;
use App\Analyte;
use App\CapturedResult;
use App\SampleType;
use App\StandardAnalytes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ParameterPerformanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        // Use without() to prevent auto-eager-loading of analysis_types (22k+ records)
        // Only load what we need for the dropdown
        // Load all analytes (not just active ones)
        $analytes = Analyte::select('id', 'name', 'code')
            ->orderBy('name')
            ->get();
        
        $sample_types = SampleType::without('analysis_types', 'sample_condition')
            ->where('active', 1)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        
        return view('layouts.lab.reports.parameter_performance.index', compact('analytes', 'sample_types'));
    }

    public function generate(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subMonths(3)->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        $productIds = $request->product_ids ?? [];
        $analyteIds = $request->analyte_ids ?? [];
        $analysisTypeIds = $request->analysis_type_ids ?? [];
        $sampleTypeIds = $request->sample_type_ids ?? [];

        // Build base query
        $query = CapturedResult::query()
            ->join('analytes', 'captured_results.analyte_id', '=', 'analytes.id')
            ->join('sample_details', 'captured_results.sample_detail_id', '=', 'sample_details.id')
            ->join('sample_headers', 'sample_details.sample_header_id', '=', 'sample_headers.id')
            ->leftJoin('analysis_types', 'captured_results.analysis_type_id', '=', 'analysis_types.id')
            ->leftJoin('standards_analytes', function($join) {
                $join->on('standards_analytes.analyte_id', '=', 'captured_results.analyte_id')
                     ->on('standards_analytes.standard_id', '=', 'sample_details.main_standard');
            })
            ->whereNotNull('captured_results.result')
            ->where('captured_results.result', '!=', '')
            ->whereBetween('captured_results.created_at', [$startDate, $endDate]);

        // Apply filters
        if (!empty($productIds) && !in_array('all', $productIds)) {
            $query->whereIn('sample_details.company_product_id', $productIds);
        }

        if (!empty($analyteIds) && !in_array('all', $analyteIds)) {
            $query->whereIn('captured_results.analyte_id', $analyteIds);
        }

        if (!empty($analysisTypeIds) && !in_array('all', $analysisTypeIds)) {
            $query->whereIn('captured_results.analysis_type_id', $analysisTypeIds);
        }

        if (!empty($sampleTypeIds) && !in_array('all', $sampleTypeIds)) {
            $query->whereIn('sample_headers.sample_type_id', $sampleTypeIds);
        }

        // Select fields needed for calculations
        // Note: We use main_value from captured_results, but may need value_type from standards_analytes for formatting
        $results = $query->select(
                'captured_results.id',
                'captured_results.analyte_id',
                'captured_results.analysis_type_id',
                'captured_results.result',
                'captured_results.remark',
                'captured_results.main_value',
                'captured_results.main_standard_id',
                'captured_results.created_at',
                'analytes.name as parameter_name',
                'analytes.code as parameter_code',
                'analysis_types.name as analysis_type_name',
                'standards_analytes.low as standard_low',
                'standards_analytes.high as standard_high',
                'standards_analytes.value_type as standard_value_type',
                'standards_analytes.value_type as value_type',
                'standards_analytes.standard_is_value as standard_is_value'
            )
            ->get();

        // Group by analysis type and parameter
        $groupedData = [];
        
        foreach ($results as $result) {
            $analysisTypeId = $result->analysis_type_id ?? 0;
            $analysisTypeName = $result->analysis_type_name ?? 'N/A';
            $paramKey = $result->analyte_id;
            $key = $analysisTypeId . '_' . $paramKey;

            if (!isset($groupedData[$key])) {
                // Get main_value directly from captured_results, exactly as stored
                // Use the main_value from the query result (already selected from captured_results)
                $mainValue = $result->main_value;
                
                // If main_value is empty, null, or "Unobjectionable", try to construct from standards_analytes
                // This matches how show_standard.blade.php displays: value_type + standard_is_value
                if (empty($mainValue) || $mainValue === null || trim($mainValue) === '' || trim($mainValue) === 'Unobjectionable') {
                    // Try to construct from standards_analytes (same logic as standard show page)
                    // Get value_type from standards_analytes using the joined data
                    if ($result->standard_value_type === 'is_standard_value') {
                        // We already have standard_value_type from the join, now get value_type
                        $standardAnalyte = \App\StandardAnalytes::where('standard_id', $result->main_standard_id ?? 0)
                            ->where('analyte_id', $result->analyte_id)
                            ->first();
                        
                        if ($standardAnalyte && !empty($standardAnalyte->standard_is_value)) {
                            $valueType = $standardAnalyte->value_type ?? '';
                            $standardIsValue = $standardAnalyte->standard_is_value ?? '';
                            
                            // Format like show_standard.blade.php line 62: value_type + standard_is_value
                            if (!empty($valueType) && !empty($standardIsValue)) {
                                // Map value_type to display format (same as show_standard.blade.php)
                                $valueTypeDisplay = '';
                                switch($valueType) {
                                    case 'Min':
                                        $valueTypeDisplay = 'Min';
                                        break;
                                    case 'Max':
                                        $valueTypeDisplay = 'Max';
                                        break;
                                    case 'less_than':
                                        $valueTypeDisplay = '<';
                                        break;
                                    case 'greater_than':
                                        $valueTypeDisplay = '>';
                                        break;
                                }
                                
                                if (!empty($valueTypeDisplay)) {
                                    $mainValue = $valueTypeDisplay . ' ' . $standardIsValue;
                                } else {
                                    $mainValue = $standardIsValue;
                                }
                            } else {
                                $mainValue = null;
                            }
                        } else {
                            $mainValue = null;
                        }
                    } else {
                        $mainValue = null;
                    }
                } else {
                    // main_value exists, but check if it's just a number and needs prefix from value_type
                    // If main_value is just a number (like "0.2"), check if we need to add prefix
                    // This matches show_standard.blade.php line 62: value_type + standard_is_value
                    $trimmedValue = trim($mainValue);
                    
                    // Check if main_value is just a number (no prefix like "min", "max", "<", ">")
                    $isJustNumber = is_numeric($trimmedValue) || (preg_match('/^[\d.]+$/', $trimmedValue));
                    
                    if ($isJustNumber && !empty($result->value_type)) {
                        // It's just a number, add prefix from standards_analytes.value_type
                        $valueType = $result->value_type ?? '';
                        // Map value_type to display format (same as show_standard.blade.php)
                        $valueTypeDisplay = '';
                        switch($valueType) {
                            case 'Min':
                                $valueTypeDisplay = 'Min';
                                break;
                            case 'Max':
                                $valueTypeDisplay = 'Max';
                                break;
                            case 'less_than':
                                $valueTypeDisplay = '<';
                                break;
                            case 'greater_than':
                                $valueTypeDisplay = '>';
                                break;
                        }
                        
                        if (!empty($valueTypeDisplay)) {
                            $mainValue = $valueTypeDisplay . ' ' . $trimmedValue;
                        }
                    }
                }
                
                $groupedData[$key] = [
                    'analysis_type_id' => $analysisTypeId,
                    'analysis_type_name' => $analysisTypeName,
                    'parameter_id' => $result->analyte_id,
                    'parameter_name' => $result->parameter_name,
                    'parameter_code' => $result->parameter_code,
                    'expected_value' => $mainValue,
                    'standard_low' => $result->standard_low,
                    'standard_high' => $result->standard_high,
                    'standard_value_type' => $result->standard_value_type,
                    'total_tests' => 0,
                    'pass_count' => 0,
                    'fail_count' => 0,
                    'results' => [],
                ];
            }

            $groupedData[$key]['total_tests']++;
            
            // Check pass/fail
            $remark = strtoupper(trim($result->remark ?? ''));
            if ($remark === 'PASS') {
                $groupedData[$key]['pass_count']++;
            } elseif ($remark === 'FAIL') {
                $groupedData[$key]['fail_count']++;
            }

            // Store numeric result for calculations
            $numericResult = $this->extractNumericValue($result->result);
            if ($numericResult !== null) {
                $groupedData[$key]['results'][] = $numericResult;
            }
        }

        // Calculate metrics for each group
        $performanceData = [];

        foreach ($groupedData as $key => $data) {
            $passRate = $data['total_tests'] > 0 
                ? round(($data['pass_count'] / $data['total_tests']) * 100, 2) 
                : 0;

            $avgResult = !empty($data['results']) 
                ? round(array_sum($data['results']) / count($data['results']), 4) 
                : null;

            $stdDev = null;
            if (!empty($data['results']) && count($data['results']) > 1) {
                $variance = 0.0;
                $avg = $avgResult;
                foreach ($data['results'] as $value) {
                    $variance += pow($value - $avg, 2);
                }
                $stdDev = round(sqrt($variance / count($data['results'])), 4);
            }

            $performanceData[] = [
                'analysis_type_id' => $data['analysis_type_id'],
                'analysis_type_name' => $data['analysis_type_name'],
                'parameter_id' => $data['parameter_id'],
                'parameter_name' => $data['parameter_name'],
                'parameter_code' => $data['parameter_code'],
                'expected_value' => $data['expected_value'],
                'standard_low' => $data['standard_low'],
                'standard_high' => $data['standard_high'],
                'standard_value_type' => $data['standard_value_type'],
                'total_tests' => $data['total_tests'],
                'pass_count' => $data['pass_count'],
                'fail_count' => $data['fail_count'],
                'pass_rate' => $passRate,
                'average_result' => $avgResult,
                'standard_deviation' => $stdDev,
            ];
        }

        // Sort by analysis type and parameter
        usort($performanceData, function($a, $b) {
            if ($a['analysis_type_name'] === $b['analysis_type_name']) {
                return strcmp($a['parameter_name'], $b['parameter_name']);
            }
            return strcmp($a['analysis_type_name'], $b['analysis_type_name']);
        });

        // Prepare chart data
        $chartData = $this->prepareChartData($performanceData);

        $filters = [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'product_ids' => $productIds,
            'analyte_ids' => $analyteIds,
            'analysis_type_ids' => $analysisTypeIds,
            'sample_type_ids' => $sampleTypeIds,
        ];

        return view('layouts.lab.reports.parameter_performance.show', compact('performanceData', 'chartData', 'filters'));
    }

    private function extractNumericValue($value)
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Try to extract numeric value from strings like "5.2", "<5.2", ">5.2", "5.2-10.5"
        $value = trim($value);
        $value = preg_replace('/[<>≤≥]/', '', $value);
        
        // If it's a range, take the first value
        if (strpos($value, '-') !== false) {
            $parts = explode('-', $value);
            $value = trim($parts[0]);
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function prepareChartData($performanceData)
    {
        // Group by analysis type for chart
        $analysisTypes = collect($performanceData)->pluck('analysis_type_name')->unique()->values();
        $parameters = collect($performanceData)->pluck('parameter_name')->unique()->values();

        $chartData = [
            'labels' => $analysisTypes->toArray(),
            'datasets' => [],
        ];

        $colors = [
            '#667eea', '#f093fb', '#4facfe', '#43e97b', '#fa709a', 
            '#ff9a9e', '#a8edea', '#fed6e3', '#ffecd2', '#fcb69f'
        ];

        $colorIndex = 0;

        foreach ($parameters as $paramName) {
            $paramData = collect($performanceData)
                ->where('parameter_name', $paramName)
                ->values();

            $passRates = [];

            foreach ($analysisTypes as $analysisType) {
                $dataPoint = $paramData->firstWhere('analysis_type_name', $analysisType);
                $passRates[] = $dataPoint ? $dataPoint['pass_rate'] : null;
            }

            $chartData['datasets'][] = [
                'label' => $paramName . ' - Pass Rate (%)',
                'data' => $passRates,
                'borderColor' => $colors[$colorIndex % count($colors)],
                'backgroundColor' => $colors[$colorIndex % count($colors)] . '40',
                'fill' => false,
                'yAxisID' => 'y',
            ];

            $colorIndex++;
        }

        return $chartData;
    }
}

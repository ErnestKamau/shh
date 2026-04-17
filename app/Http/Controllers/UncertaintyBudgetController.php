<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\UncertaintyBudget;
use App\UncertaintySource;
use App\Analyte;
use App\AnalysisMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UncertaintyBudgetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of uncertainty budgets
     */
    public function index()
    {
        $dataTable = true;
        $select2 = true;
        return view('layouts.lab.uncertainty-budgets.index', compact('dataTable', 'select2'));
    }

    /**
     * Show the form for creating a new uncertainty budget
     */
    public function create()
    {
        $analytes = Analyte::where('active', true)
            ->where('company_id', getUserCompany())
            ->orderBy('name')
            ->get();

        $methods = AnalysisMethod::where('active', true)
            ->where('company_id', getUserCompany())
            ->orderBy('name')
            ->get();

        $select2 = true;
        return view('layouts.lab.uncertainty-budgets.create', compact('analytes', 'methods', 'select2'));
    }

    /**
     * Store a newly created uncertainty budget
     */
    public function store(Request $request)
    {
        \Log::info('Store method called', [
            'request_data' => $request->all(),
            'is_ajax' => $request->ajax(),
            'wants_json' => $request->wantsJson(),
            'headers' => $request->headers->all()
        ]);
        
        try {
            $request->validate([
                'analyte_id' => 'required|exists:analytes,id',
                'method_ids' => 'required|array|min:1',
                'method_ids.*' => 'exists:analysis_methods,id',
                'coverage_factor_k' => 'required|numeric|min:1|max:10',
                'confidence_level' => 'required|numeric|min:50|max:99.99'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        try {
            // Convert method IDs to comma-separated string
            $methodIdsString = implode(',', $request->method_ids);
            
            // Check if budget already exists for this analyte with any of the selected methods
            $existingBudget = UncertaintyBudget::where('analyte_id', $request->analyte_id)
                ->where('company_id', getUserCompany())
                ->where('active', true)
                ->where(function($query) use ($request) {
                    foreach ($request->method_ids as $methodId) {
                        $query->orWhere('method_ids', 'LIKE', '%' . $methodId . '%');
                    }
                })
                ->first();

            if ($existingBudget) {
                $analyte = Analyte::find($request->analyte_id);
                $message = 'An uncertainty budget already exists for this analyte with one or more of the selected methods.';
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 422);
                }
                
                return redirect()->back()
                    ->withErrors(['error' => $message])
                    ->withInput();
            }

            // Create a single budget record with comma-separated method IDs
            $budget = UncertaintyBudget::create([
                'analyte_id' => $request->analyte_id,
                'method_ids' => $methodIdsString,
                'coverage_factor_k' => $request->coverage_factor_k,
                'confidence_level' => $request->confidence_level,
                'created_by' => Auth::id(),
                'company_id' => getUserCompany(),
                'active' => true
            ]);

            $createdBudgets = [$budget];
            $skippedBudgets = [];

        // Check if this is an AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            $message = '';
            
            if (!empty($createdBudgets)) {
                $message = count($createdBudgets) . ' uncertainty budget(s) created successfully.';
            }
            
            if (!empty($skippedBudgets)) {
                if (!empty($message)) {
                    $message .= ' ';
                }
                $message .= 'Skipped ' . count($skippedBudgets) . ' method(s) that already have budgets: ' . implode(', ', $skippedBudgets) . '.';
            }
            
            if (empty($createdBudgets) && !empty($skippedBudgets)) {
                return response()->json([
                    'success' => false,
                    'message' => 'All selected methods already have uncertainty budgets for this analyte.',
                    'skipped' => $skippedBudgets
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'budgets' => $createdBudgets,
                'skipped' => $skippedBudgets
            ]);
        }

        // Handle regular form submissions (redirects)
        $message = '';
        
        if (!empty($createdBudgets)) {
            $message = count($createdBudgets) . ' uncertainty budget(s) created successfully.';
        }
        
        if (!empty($skippedBudgets)) {
            if (!empty($message)) {
                $message .= ' ';
            }
            $message .= 'Skipped ' . count($skippedBudgets) . ' method(s) that already have budgets: ' . implode(', ', $skippedBudgets) . '.';
        }
        
        if (empty($createdBudgets) && !empty($skippedBudgets)) {
            return redirect()->back()
                ->withErrors(['error' => 'All selected methods already have uncertainty budgets for this analyte.'])
                ->withInput();
        }

            if (count($createdBudgets) === 1) {
                return redirect()->route('uncertainty-budgets.show', $createdBudgets[0]->id)
                    ->with('success', $message);
            } else {
                return redirect()->route('uncertainty-budgets.index')
                    ->with('success', $message);
            }
        } catch (\Exception $e) {
            \Log::error('Store method error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            // Check if this is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating uncertainty budget: ' . $e->getMessage()
                ], 500);
            }

            // Handle regular form submissions
            return redirect()->back()
                ->withErrors(['error' => 'Error creating uncertainty budget: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified uncertainty budget
     */
    public function show($id)
    {
        $budget = UncertaintyBudget::with(['analyte', 'sources', 'creator'])
            ->where('id', $id)
            ->where('company_id', getUserCompany())
            ->firstOrFail();

        $dataTable = true;
        $select2 = true;
        return view('layouts.lab.uncertainty-budgets.show', compact('budget', 'dataTable', 'select2'));
    }

    /**
     * Show the form for editing the specified uncertainty budget
     */
    public function edit($id)
    {
        $budget = UncertaintyBudget::with(['analyte'])
            ->where('id', $id)
            ->where('company_id', getUserCompany())
            ->firstOrFail();

        $analytes = Analyte::where('active', true)
            ->where('company_id', getUserCompany())
            ->orderBy('name')
            ->get();

        $select2 = true;
        return view('layouts.lab.uncertainty-budgets.edit', compact('budget', 'analytes', 'select2'));
    }

    /**
     * Update the specified uncertainty budget
     */
    public function update(Request $request, $id)
    {
        $budget = UncertaintyBudget::where('id', $id)
            ->where('company_id', getUserCompany())
            ->firstOrFail();

        $request->validate([
            'analyte_id' => 'required|exists:analytes,id',
            'coverage_factor_k' => 'required|numeric|min:1|max:10',
            'confidence_level' => 'required|numeric|min:50|max:99.99'
        ]);

        // Check if another budget already exists for this analyte
        $existingBudget = UncertaintyBudget::where('analyte_id', $request->analyte_id)
            ->where('company_id', getUserCompany())
            ->where('active', true)
            ->where('id', '!=', $id)
            ->first();

        if ($existingBudget) {
            return redirect()->back()
                ->withErrors(['error' => 'An uncertainty budget already exists for this analyte.'])
                ->withInput();
        }

        $budget->update([
            'analyte_id' => $request->analyte_id,
            'coverage_factor_k' => $request->coverage_factor_k,
            'confidence_level' => $request->confidence_level,
            'version_number' => $budget->version_number + 1
        ]);

        // Recalculate uncertainties
        $budget->calculateUncertainties();

        return redirect()->route('uncertainty-budgets.show', $budget->id)
            ->with('success', 'Uncertainty budget updated successfully.');
    }

    /**
     * Remove the specified uncertainty budget
     */
    public function destroy($id)
    {
        $budget = UncertaintyBudget::where('id', $id)
            ->where('company_id', getUserCompany())
            ->firstOrFail();

        $budget->update(['active' => false]);

        return redirect()->route('uncertainty-budgets.index')
            ->with('success', 'Uncertainty budget deactivated successfully.');
    }

    /**
     * Add a new uncertainty source to the budget
     */
    public function addSource(Request $request, $budgetId)
    {
        try {
            \Log::info('AddSource called', ['budgetId' => $budgetId, 'request_data' => $request->all()]);
            
            $budget = UncertaintyBudget::where('id', $budgetId)
                ->where('company_id', getUserCompany())
                ->firstOrFail();

            $request->validate([
                'source_name' => 'required|string|max:255',
                'type' => 'required|in:A,B',
                'std_uncertainty_value' => 'required|numeric|min:0',
                'sensitivity_coefficient' => 'required|numeric|min:0',
                'notes' => 'nullable|string'
            ]);

            $source = UncertaintySource::create([
                'uncertainty_budget_id' => $budget->id,
                'source_name' => $request->source_name,
                'type' => $request->type,
                'std_uncertainty_value' => $request->std_uncertainty_value,
                'sensitivity_coefficient' => $request->sensitivity_coefficient,
                'notes' => $request->notes
            ]);

            // Recalculate uncertainties after adding source
            \Log::info('Before calculation - Budget ID: ' . $budget->id . ', Sources count: ' . $budget->sources->count());
            $budget->calculateUncertainties();
            
            // Refresh the budget to get updated values
            $budget->refresh();
            \Log::info('After calculation - Combined: ' . $budget->combined_standard_uncertainty . ', Expanded: ' . $budget->expanded_uncertainty);

            // Check if this is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Uncertainty source added successfully.',
                    'source' => $source,
                    'updated_budget' => [
                        'combined_standard_uncertainty' => $budget->combined_standard_uncertainty,
                        'expanded_uncertainty' => $budget->expanded_uncertainty,
                        'formatted_combined_uncertainty' => $budget->formatted_combined_uncertainty,
                        'formatted_expanded_uncertainty' => $budget->formatted_expanded_uncertainty
                    ]
                ]);
            }

            return redirect()->route('uncertainty-budgets.show', $budget->id)
                ->with('success', 'Uncertainty source added successfully.');
        } catch (\Exception $e) {
            \Log::error('AddSource error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            // Check if this is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error adding uncertainty source: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Error adding uncertainty source: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Update an uncertainty source
     */
    public function updateSource(Request $request, $sourceId)
    {
        try {
            \Log::info('UpdateSource called', ['sourceId' => $sourceId, 'request_data' => $request->all()]);
            
            $source = UncertaintySource::whereHas('uncertaintyBudget', function($query) {
                $query->where('company_id', getUserCompany());
            })->findOrFail($sourceId);

            $request->validate([
                'source_name' => 'required|string|max:255',
                'type' => 'required|in:A,B',
                'std_uncertainty_value' => 'required|numeric|min:0',
                'sensitivity_coefficient' => 'required|numeric|min:0',
                'notes' => 'nullable|string'
            ]);

            $source->update([
                'source_name' => $request->source_name,
                'type' => $request->type,
                'std_uncertainty_value' => $request->std_uncertainty_value,
                'sensitivity_coefficient' => $request->sensitivity_coefficient,
                'notes' => $request->notes
            ]);

            // Recalculate uncertainties after updating source
            $budget = $source->uncertaintyBudget;
            $budget->calculateUncertainties();
            $budget->refresh();

            // Check if this is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Uncertainty source updated successfully.',
                    'source' => $source,
                    'updated_budget' => [
                        'combined_standard_uncertainty' => $budget->combined_standard_uncertainty,
                        'expanded_uncertainty' => $budget->expanded_uncertainty,
                        'formatted_combined_uncertainty' => $budget->formatted_combined_uncertainty,
                        'formatted_expanded_uncertainty' => $budget->formatted_expanded_uncertainty
                    ]
                ]);
            }

            return redirect()->route('uncertainty-budgets.show', $budget->id)
                ->with('success', 'Uncertainty source updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('UpdateSource error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            // Check if this is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating uncertainty source: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Error updating uncertainty source: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Delete an uncertainty source
     */
    public function deleteSource($sourceId)
    {
        $source = UncertaintySource::whereHas('uncertaintyBudget', function($query) {
            $query->where('company_id', getUserCompany());
        })->findOrFail($sourceId);

        $budget = $source->uncertaintyBudget;
        $source->delete();

        // Recalculate uncertainties after deleting source
        $budget->calculateUncertainties();
        $budget->refresh();

        // Check if this is an AJAX request
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Uncertainty source deleted successfully.',
                'updated_budget' => [
                    'combined_standard_uncertainty' => $budget->combined_standard_uncertainty,
                    'expanded_uncertainty' => $budget->expanded_uncertainty,
                    'formatted_combined_uncertainty' => $budget->formatted_combined_uncertainty,
                    'formatted_expanded_uncertainty' => $budget->formatted_expanded_uncertainty
                ]
            ]);
        }

        return redirect()->route('uncertainty-budgets.show', $budget->id)
            ->with('success', 'Uncertainty source deleted successfully.');
    }

    /**
     * Get analytes for API
     */
    public function getAnalytes()
    {
        $analytes = Analyte::where('active', true)
            ->where('company_id', getUserCompany())
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        return response()->json($analytes);
    }

    /**
     * Get methods for a specific analyte
     */
    public function getMethods($analyteId)
    {
        try {
            $analyte = Analyte::where('id', $analyteId)
                ->where('company_id', getUserCompany())
                ->firstOrFail();

            \Log::info('Getting methods for analyte', [
                'analyte_id' => $analyteId,
                'analyte_name' => $analyte->name,
                'method_field' => $analyte->method,
                'company_id' => getUserCompany()
            ]);

            // Handle empty or null method field
            if (empty($analyte->method) || $analyte->method === '0' || $analyte->method === '') {
                \Log::info('No methods found - empty method field');
                return response()->json([]);
            }

            // Parse method IDs and filter out empty values
            $methodIds = array_filter(explode(',', $analyte->method), function($id) {
                return !empty(trim($id)) && is_numeric(trim($id));
            });

            \Log::info('Parsed method IDs', ['method_ids' => $methodIds]);

            if (empty($methodIds)) {
                \Log::info('No valid method IDs found');
                return response()->json([]);
            }

            // Get methods that exist and are active
            $methods = AnalysisMethod::whereIn('id', $methodIds)
                ->where('active', true)
                ->where('company_id', getUserCompany())
                ->select('id', 'name', 'code')
                ->orderBy('name')
                ->get();

            \Log::info('Found methods', ['methods_count' => $methods->count(), 'methods' => $methods->toArray()]);

            return response()->json($methods);
        } catch (\Exception $e) {
            \Log::error('Error getting methods for analyte', [
                'analyte_id' => $analyteId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to load methods'], 500);
        }
    }

    /**
     * Get uncertainty source data for editing
     */
    public function getSource($sourceId)
    {
        $source = UncertaintySource::whereHas('uncertaintyBudget', function($query) {
            $query->where('company_id', getUserCompany());
        })->findOrFail($sourceId);

        return response()->json($source);
    }

    /**
     * Get existing budgets for an analyte
     */
    public function getExistingBudgets($analyteId)
    {
        try {
            $existingBudgets = UncertaintyBudget::where('analyte_id', $analyteId)
                ->where('company_id', getUserCompany())
                ->where('active', true)
                ->whereNotNull('method_ids')
                ->get();

            $existingMethodIds = [];
            foreach($existingBudgets as $budget) {
                if ($budget->method_ids) {
                    $methodIds = explode(',', $budget->method_ids);
                    $existingMethodIds = array_merge($existingMethodIds, $methodIds);
                }
            }

            $existingMethodIds = array_unique(array_map('intval', $existingMethodIds));

            return response()->json([
                'existing_methods' => $existingMethodIds
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting existing budgets', [
                'analyte_id' => $analyteId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'existing_methods' => []
            ], 500);
        }
    }

    /**
     * Manually recalculate uncertainties for a budget
     */
    public function recalculate($budgetId)
    {
        try {
            $budget = UncertaintyBudget::where('id', $budgetId)
                ->where('company_id', getUserCompany())
                ->firstOrFail();

            \Log::info('Manual recalculation - Budget ID: ' . $budget->id . ', Sources count: ' . $budget->sources->count());
            $budget->calculateUncertainties();
            $budget->refresh();

            // Check if this is an AJAX request
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Uncertainties recalculated successfully.',
                    'updated_budget' => [
                        'combined_standard_uncertainty' => $budget->combined_standard_uncertainty,
                        'expanded_uncertainty' => $budget->expanded_uncertainty,
                        'formatted_combined_uncertainty' => $budget->formatted_combined_uncertainty,
                        'formatted_expanded_uncertainty' => $budget->formatted_expanded_uncertainty
                    ]
                ]);
            }

            return redirect()->route('uncertainty-budgets.show', $budget->id)
                ->with('success', 'Uncertainties recalculated successfully.');
        } catch (\Exception $e) {
            \Log::error('Recalculate error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            // Check if this is an AJAX request
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error recalculating uncertainties: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Error recalculating uncertainties: ' . $e->getMessage()]);
        }
    }
}

<?php

namespace App\Http\Controllers\RiskManagement;

use App\Http\Controllers\Controller;
use App\Models\RiskManagement\RiskConfigurationOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RiskConfigurationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the settings index page
     */
    public function index()
    {
        $optionTypes = [
            'risk_level' => 'Risk Levels',
            'evaluation_result' => 'Evaluation Results',
            'acceptance_threshold_rpn' => 'Acceptance Threshold RPN',
            'implementation_status' => 'Implementation Statuses',
            'treatment_priority' => 'Treatment Priorities',
            'review_type' => 'Review Types',
            'review_decision' => 'Review Decisions',
            'closure_type' => 'Closure Types',
            'likelihood_score' => 'Likelihood Scores',
            'severity_score' => 'Severity Scores',
        ];

        return view('layouts.risk.settings.index', compact('optionTypes'));
    }

    /**
     * Show options for a specific type
     */
    public function show($optionType)
    {
        $companyId = getUserCompany() ?? 0;
        
        $options = RiskConfigurationOption::forType($optionType)
            ->forCompany($companyId)
            ->ordered()
            ->get();

        $optionTypes = [
            'risk_level' => 'Risk Levels',
            'evaluation_result' => 'Evaluation Results',
            'acceptance_threshold_rpn' => 'Acceptance Threshold RPN',
            'implementation_status' => 'Implementation Statuses',
            'treatment_priority' => 'Treatment Priorities',
            'review_type' => 'Review Types',
            'review_decision' => 'Review Decisions',
            'closure_type' => 'Closure Types',
            'likelihood_score' => 'Likelihood Scores',
            'severity_score' => 'Severity Scores',
        ];

        if (!isset($optionTypes[$optionType])) {
            abort(404, 'Invalid option type');
        }

        return view('layouts.risk.settings.show', compact('optionType', 'options'));
    }

    /**
     * Store a new configuration option
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'option_type' => 'required|string',
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color_code' => 'nullable|string|max:7',
            'order_index' => 'nullable|integer',
            'is_active' => 'boolean',
            'metadata' => 'nullable|json',
            'required_actions' => 'nullable|string',
        ]);

        $companyId = getUserCompany() ?? 0;

        // Check for uniqueness
        $exists = RiskConfigurationOption::where('option_type', $validated['option_type'])
            ->where('code', $validated['code'])
            ->where('company_id', $companyId)
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => 'An option with this code already exists for this type.'])->withInput();
        }

        // Build metadata for evaluation_result
        if ($validated['option_type'] === 'evaluation_result' && !empty($validated['required_actions'])) {
            $validated['metadata'] = json_encode(['required_actions' => $validated['required_actions']]);
        }

        // Handle acceptance_threshold_rpn - use rpn_value as code
        if ($validated['option_type'] === 'acceptance_threshold_rpn' && isset($request->rpn_value)) {
            $validated['code'] = (string)$request->rpn_value;
            $validated['name'] = $request->rpn_value . ' RPN';
        }

        $validated['company_id'] = $companyId;
        $validated['created_by'] = Auth::id();
        $validated['order_index'] = $validated['order_index'] ?? RiskConfigurationOption::forType($validated['option_type'])->max('order_index') + 1;

        unset($validated['required_actions']); // Remove from fillable array

        RiskConfigurationOption::create($validated);

        return redirect()->route('risk.settings.show', $validated['option_type'])
            ->with('success', 'Configuration option created successfully.');
    }

    /**
     * Update a configuration option
     */
    public function update(Request $request, $id)
    {
        $option = RiskConfigurationOption::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color_code' => 'nullable|string|max:7',
            'order_index' => 'nullable|integer',
            'is_active' => 'boolean',
            'metadata' => 'nullable|json',
            'required_actions' => 'nullable|string',
        ]);

        // Build metadata for evaluation_result
        if ($option->option_type === 'evaluation_result' && isset($validated['required_actions'])) {
            $metadata = $option->metadata ?? [];
            $metadata['required_actions'] = $validated['required_actions'];
            $validated['metadata'] = json_encode($metadata);
        }

        $validated['updated_by'] = Auth::id();
        unset($validated['required_actions']); // Remove from fillable array

        $option->update($validated);

        return redirect()->route('risk.settings.show', $option->option_type)
            ->with('success', 'Configuration option updated successfully.');
    }

    /**
     * Delete a configuration option
     */
    public function destroy($id)
    {
        $option = RiskConfigurationOption::findOrFail($id);
        $optionType = $option->option_type;
        
        $option->delete();

        return redirect()->route('risk.settings.show', $optionType)
            ->with('success', 'Configuration option deleted successfully.');
    }

    /**
     * Reorder configuration options
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'option_type' => 'required|string',
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:risk_configuration_options,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            RiskConfigurationOption::where('id', $id)
                ->update(['order_index' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}

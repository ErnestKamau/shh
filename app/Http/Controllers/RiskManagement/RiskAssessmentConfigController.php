<?php

namespace App\Http\Controllers\RiskManagement;

use App\Http\Controllers\Controller;
use App\Models\AuditModule\LikelihoodScale;
use App\Models\AuditModule\SeverityScale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiskAssessmentConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display likelihood scales management page
     */
    public function likelihoodScales()
    {
        $companyId = getUserCompany() ?? 0;
        $scales = LikelihoodScale::forCompany()
            ->ordered()
            ->get();

        return view('layouts.risk.assessment-config.likelihood-scales', compact('scales'));
    }

    /**
     * Display severity scales management page
     */
    public function severityScales()
    {
        $companyId = getUserCompany() ?? 0;
        $scales = SeverityScale::forCompany()
            ->ordered()
            ->get();

        return view('layouts.risk.assessment-config.severity-scales', compact('scales'));
    }

    /**
     * Store a new likelihood scale
     */
    public function storeLikelihoodScale(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'score' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string',
            'color_code' => 'nullable|string|max:7',
            'order_index' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $companyId = getUserCompany() ?? 0;

        // Auto-generate code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = strtoupper(substr(str_replace(' ', '_', $validated['name']), 0, 20));
        }

        $validated['company_id'] = $companyId;
        $validated['order_index'] = $validated['order_index'] ?? (LikelihoodScale::forCompany()->max('order_index') ?? 0) + 1;
        $validated['is_active'] = $validated['is_active'] ?? true;

        LikelihoodScale::create($validated);

        return redirect()->route('risk.assessment.likelihood-scales')
            ->with('success', 'Likelihood scale created successfully.');
    }

    /**
     * Store a new severity scale
     */
    public function storeSeverityScale(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'score' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string',
            'color_code' => 'nullable|string|max:7',
            'order_index' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $companyId = getUserCompany() ?? 0;

        // Auto-generate code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = strtoupper(substr(str_replace(' ', '_', $validated['name']), 0, 20));
        }

        $validated['company_id'] = $companyId;
        $validated['order_index'] = $validated['order_index'] ?? (SeverityScale::forCompany()->max('order_index') ?? 0) + 1;
        $validated['is_active'] = $validated['is_active'] ?? true;

        SeverityScale::create($validated);

        return redirect()->route('risk.assessment.severity-scales')
            ->with('success', 'Severity scale created successfully.');
    }

    /**
     * Update a likelihood scale
     */
    public function updateLikelihoodScale(Request $request, $id)
    {
        $scale = LikelihoodScale::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'score' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string',
            'color_code' => 'nullable|string|max:7',
            'order_index' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $scale->update($validated);

        return redirect()->route('risk.assessment.likelihood-scales')
            ->with('success', 'Likelihood scale updated successfully.');
    }

    /**
     * Update a severity scale
     */
    public function updateSeverityScale(Request $request, $id)
    {
        $scale = SeverityScale::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'score' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string',
            'color_code' => 'nullable|string|max:7',
            'order_index' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $scale->update($validated);

        return redirect()->route('risk.assessment.severity-scales')
            ->with('success', 'Severity scale updated successfully.');
    }

    /**
     * Delete a likelihood scale
     */
    public function destroyLikelihoodScale($id)
    {
        $scale = LikelihoodScale::findOrFail($id);
        $scale->delete();

        return redirect()->route('risk.assessment.likelihood-scales')
            ->with('success', 'Likelihood scale deleted successfully.');
    }

    /**
     * Delete a severity scale
     */
    public function destroySeverityScale($id)
    {
        $scale = SeverityScale::findOrFail($id);
        $scale->delete();

        return redirect()->route('risk.assessment.severity-scales')
            ->with('success', 'Severity scale deleted successfully.');
    }

    /**
     * Toggle active status of a likelihood scale
     */
    public function toggleLikelihoodScale(Request $request, $id)
    {
        $scale = LikelihoodScale::findOrFail($id);
        $scale->is_active = $request->input('is_active', !$scale->is_active);
        $scale->save();

        $status = $scale->is_active ? 'activated' : 'deactivated';
        return redirect()->route('risk.assessment.likelihood-scales')
            ->with('success', "Likelihood scale {$status} successfully.");
    }

    /**
     * Toggle active status of a severity scale
     */
    public function toggleSeverityScale(Request $request, $id)
    {
        $scale = SeverityScale::findOrFail($id);
        $scale->is_active = $request->input('is_active', !$scale->is_active);
        $scale->save();

        $status = $scale->is_active ? 'activated' : 'deactivated';
        return redirect()->route('risk.assessment.severity-scales')
            ->with('success', "Severity scale {$status} successfully.");
    }
}


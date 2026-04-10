<?php

namespace App\Http\Controllers\AuditModule;

use App\Http\Controllers\Controller;
use App\Models\AuditModule\AuditType;
use App\Models\AuditModule\FindingCategory;
use App\Models\AuditModule\RiskLevel;
use App\Models\AuditModule\RootCauseMethod;
use App\Models\AuditModule\CapaCategory;
use Illuminate\Http\Request;

class AuditConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function auditTypes()
    {
        return view('layouts.audit.config.audit-types');
    }

    public function findingCategories()
    {
        return view('layouts.audit.config.finding-categories');
    }

    public function riskLevels()
    {
        return view('layouts.audit.config.risk-levels');
    }

    public function rcaMethods()
    {
        return view('layouts.audit.config.rca-methods');
    }

    public function capaCategories()
    {
        return view('layouts.audit.config.capa-categories');
    }

    public function complianceStatuses()
    {
        return view('layouts.audit.config.compliance-statuses');
    }

    public function auditStatuses()
    {
        return view('layouts.audit.config.audit-statuses');
    }

    public function workflowActions()
    {
        return view('layouts.audit.config.workflow-actions');
    }

    public function workflowActionRules()
    {
        return view('layouts.audit.config.workflow-action-rules');
    }

    public function verificationResults()
    {
        return view('layouts.audit.config.verification-results');
    }

    public function storeVerificationResult(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:verification_results,code,NULL,id,deleted_at,NULL',
            'description' => 'nullable|string',
            'color_code' => 'nullable|string|max:7',
            'next_workflow_step' => 'required|integer|min:1|max:8',
            'requires_reopen' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $companyId = getUserCompany() ?? 0;

        \App\Models\AuditModule\VerificationResult::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'color_code' => $validated['color_code'] ?? '#17a2b8',
            'next_workflow_step' => $validated['next_workflow_step'],
            'requires_reopen' => $request->has('requires_reopen') ? 1 : 0,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'company_id' => $companyId,
        ]);

        return redirect()->route('audit.config.verification-results')
            ->with('success', 'Verification result created successfully.');
    }

    public function getVerificationResult($id)
    {
        $result = \App\Models\AuditModule\VerificationResult::where(function($q) {
            $companyId = getUserCompany() ?? 0;
            $q->where('company_id', $companyId)->orWhere('company_id', 0);
        })->findOrFail($id);

        return response()->json([
            'id' => $result->id,
            'name' => $result->name,
            'code' => $result->code,
            'description' => $result->description,
            'color_code' => $result->color_code,
            'next_workflow_step' => $result->next_workflow_step,
            'requires_reopen' => $result->requires_reopen,
            'is_active' => $result->is_active,
        ]);
    }

    public function updateVerificationResult(Request $request, $id)
    {
        $result = \App\Models\AuditModule\VerificationResult::where(function($q) {
            $companyId = getUserCompany() ?? 0;
            $q->where('company_id', $companyId)->orWhere('company_id', 0);
        })->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:verification_results,code,' . $id . ',id,deleted_at,NULL',
            'description' => 'nullable|string',
            'color_code' => 'nullable|string|max:7',
            'next_workflow_step' => 'required|integer|min:1|max:8',
            'requires_reopen' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $result->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'color_code' => $validated['color_code'] ?? null,
            'next_workflow_step' => $validated['next_workflow_step'],
            'requires_reopen' => $request->has('requires_reopen') ? 1 : 0,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('audit.config.verification-results')
            ->with('success', 'Verification result updated successfully.');
    }

    public function severityScales()
    {
        return view('layouts.audit.config.severity-scales');
    }

    public function likelihoodScales()
    {
        return view('layouts.audit.config.likelihood-scales');
    }
}


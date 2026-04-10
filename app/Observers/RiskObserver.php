<?php

namespace App\Observers;

use App\Models\RiskManagement\Risk;
use App\Models\RiskManagement\RiskCategory;
use App\Models\RiskManagement\RiskSource;
use App\Models\RiskManagement\RiskStatus;
use App\Models\AuditModule\NonConformance;
use App\Models\AuditModule\AuditModuleFinding;
use Illuminate\Support\Facades\Auth;

class RiskObserver
{
    /**
     * Automatically create risk when non-conformance is created
     */
    public function created(NonConformance $nc)
    {
        // Check if auto-create risk is enabled (can be configured)
        $autoCreateRisk = true; // Can be moved to config
        
        if (!$autoCreateRisk) {
            return;
        }

        // Check if risk already exists for this NC
        $existingRisk = Risk::where('non_conformance_id', $nc->id)->first();
        if ($existingRisk) {
            return;
        }

        // Get or create risk source for NC
        $source = RiskSource::where('code', 'NC')->first();
        if (!$source) {
            return; // Source not configured
        }

        // Get or create risk category (default to Quality)
        $category = RiskCategory::where('code', 'QUAL')->first();
        if (!$category) {
            $category = RiskCategory::first(); // Fallback to first available
        }

        // Get initial status
        $status = RiskStatus::forCompany()
            ->where('workflow_step', 1)
            ->ordered()
            ->first();

        if (!$status) {
            return; // Status not configured
        }

        // Create risk from NC
        $risk = Risk::create([
            'risk_number' => Risk::generateRiskNumber(),
            'title' => 'Risk from NC: ' . $nc->title,
            'description' => $nc->description,
            'category_id' => $category->id ?? null,
            'category_name' => $category->name ?? null,
            'source_id' => $source->id,
            'source_name' => $source->name,
            'non_conformance_id' => $nc->id,
            'audit_id' => $nc->audit_id,
            'audit_finding_id' => $nc->audit_finding_id,
            'date_identified' => $nc->date_identified,
            'identified_by' => $nc->identified_by,
            'identified_by_user_id' => $nc->identified_by_user_id ?? Auth::id(),
            'risk_owner_id' => $nc->identified_by_user_id,
            'risk_owner_name' => $nc->identified_by,
            'department' => $nc->department,
            'department_id' => $nc->department_id,
            'status_id' => $status->id,
            'status_name' => $status->name,
            'workflow_step' => 1,
            'created_by' => $nc->created_by ?? Auth::id(),
            'company_id' => $nc->company_id ?? (getUserCompany() ?? 0),
        ]);

        // Copy risk assessment if available
        if ($nc->likelihood_score && $nc->severity_score) {
            $risk->update([
                'likelihood_score' => $nc->likelihood_score,
                'severity_score' => $nc->severity_score,
                'rpn' => $nc->likelihood_score * $nc->severity_score,
                'risk_level' => $risk->determineRiskLevel($nc->likelihood_score * $nc->severity_score),
            ]);
        }
    }
}



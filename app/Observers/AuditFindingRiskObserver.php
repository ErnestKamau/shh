<?php

namespace App\Observers;

use App\Models\RiskManagement\Risk;
use App\Models\RiskManagement\RiskCategory;
use App\Models\RiskManagement\RiskSource;
use App\Models\RiskManagement\RiskStatus;
use App\Models\AuditModule\AuditModuleFinding;
use Illuminate\Support\Facades\Auth;

class AuditFindingRiskObserver
{
    /**
     * Automatically create risk when audit finding is created (if finding category requires risk assessment)
     */
    public function created(AuditModuleFinding $finding)
    {
        // Check if finding category requires risk assessment
        if (!$finding->findingCategory || !$finding->findingCategory->requires_capa) {
            return; // Only create risk for findings that require CAPA (typically NCs)
        }

        // Check if risk already exists for this finding
        $existingRisk = Risk::where('audit_finding_id', $finding->id)->first();
        if ($existingRisk) {
            return;
        }

        // Get or create risk source for Audit Finding
        $source = RiskSource::where('code', 'AUDIT')->first();
        if (!$source) {
            return; // Source not configured
        }

        // Get risk category based on finding category
        $categoryCode = 'QUAL'; // Default
        if ($finding->findingCategory) {
            // Map finding category to risk category
            $categoryMap = [
                'NC-MIN' => 'QUAL',
                'NC-MAJ' => 'QUAL',
                'NC-CRT' => 'QUAL',
            ];
            $categoryCode = $categoryMap[$finding->findingCategory->code] ?? 'QUAL';
        }

        $category = RiskCategory::where('code', $categoryCode)->first();
        if (!$category) {
            $category = RiskCategory::first(); // Fallback
        }

        // Get initial status
        $status = RiskStatus::forCompany()
            ->where('workflow_step', 1)
            ->ordered()
            ->first();

        if (!$status) {
            return;
        }

        // Create risk from audit finding
        Risk::create([
            'risk_number' => Risk::generateRiskNumber(),
            'title' => 'Risk from Audit Finding: ' . ($finding->requirement ?? $finding->observation),
            'description' => $finding->observation,
            'category_id' => $category->id ?? null,
            'category_name' => $category->name ?? null,
            'source_id' => $source->id,
            'source_name' => $source->name,
            'audit_id' => $finding->audit_id,
            'audit_finding_id' => $finding->id,
            'date_identified' => now(),
            'identified_by' => Auth::user()->name ?? 'System',
            'identified_by_user_id' => Auth::id(),
            'risk_owner_id' => $finding->responsible_user_id,
            'risk_owner_name' => $finding->responsible_person,
            'status_id' => $status->id,
            'status_name' => $status->name,
            'workflow_step' => 1,
            'created_by' => Auth::id(),
            'company_id' => getUserCompany() ?? 0,
        ]);
    }
}



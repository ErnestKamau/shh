<?php

/**
 * Audit Module Helper Functions
 *
 * These functions provide utility methods for the audit module.
 */

if (!function_exists('getAuditModuleDashboardStats')) {
    /**
     * Dashboard statistics for the Audit module.
     * Defaults to current month if no dates are supplied.
     *
     * @param string|null $startDate Start date for filtering (optional)
     * @param string|null $endDate End date for filtering (optional)
     * @return array
     */
    function getAuditModuleDashboardStats($startDate = null, $endDate = null)
    {
        $start = $startDate ? \Carbon\Carbon::parse($startDate)->startOfDay() : \Carbon\Carbon::now()->startOfMonth();
        $end = $endDate ? \Carbon\Carbon::parse($endDate)->endOfDay() : \Carbon\Carbon::now()->endOfMonth();

        $companyId = getUserCompany() ?? 0;

        $auditsBase = \App\Models\AuditModule\Audit::where('company_id', $companyId)
            ->whereBetween('created_at', [$start, $end]);

        $ncsBase = \App\Models\AuditModule\NonConformance::where('company_id', $companyId)
            ->whereBetween('date_identified', [$start, $end]);

        $capasBase = \App\Models\AuditModule\CorrectiveAction::where('company_id', $companyId)
            ->whereBetween('created_at', [$start, $end]);

        return [
            'audits' => [
                'total' => $auditsBase->count(),
                'scheduled' => (clone $auditsBase)->where('status_name', 'Scheduled')->count(),
                'in_progress' => (clone $auditsBase)->whereIn('status_name', ['In Progress', 'Record Findings & NC', 'Findings Review'])->count(),
                'rca_phase' => (clone $auditsBase)->where('status_name', 'Root Cause Analysis')->count(),
                'pending_closure' => (clone $auditsBase)->where('status_name', 'Pending Closure')->count(),
            ],
            'non_conformances' => [
                'identified' => (clone $ncsBase)->where('status_name', 'Identified')->count(),
                'rca_in_progress' => (clone $ncsBase)->where('status_name', 'RCA In Progress')->count(),
                'capa_assigned' => (clone $ncsBase)->where('status_name', 'CAPA Assigned')->count(),
                'verification_pending' => (clone $ncsBase)->where('status_name', 'Verification Pending')->count(),
                'overdue' => (clone $ncsBase)
                    ->whereNotIn('status_name', ['Closed', 'Cancelled'])
                    ->whereNotNull('target_closure_date')
                    ->where('target_closure_date', '<', now())
                    ->count(),
                'total' => (clone $ncsBase)->count(),
            ],
            'corrective_actions' => [
                'in_progress' => (clone $capasBase)->where('status_name', 'In Progress')->count(),
                'verified' => (clone $capasBase)->where('status_name', 'Verified')->count(),
                'overdue' => (clone $capasBase)->overdue()->count(),
                'total' => (clone $capasBase)->count(),
            ],
        ];
    }
}

if (!function_exists('getWorkflowStepName')) {
    /**
     * Get the name of a workflow step.
     *
     * @param int $step
     * @param string $module
     * @return string|null
     */
    function getWorkflowStepName($step, $module = 'risk')
    {
        if ($module === 'risk') {
            $steps = [
                1 => 'New',
                2 => 'Identified',
                3 => 'Under Assessment',
                4 => 'Under Evaluation',
                5 => 'Treatment Planning',
                6 => 'Treatment Implementation',
                7 => 'Risk Monitoring',
                8 => 'Closed',
            ];

            return $steps[$step] ?? 'Step ' . $step;
        }

        $steps = [
            1 => 'Planning',
            2 => 'Fieldwork',
            3 => 'Reporting',
            4 => 'Follow-up',
            5 => 'Closed',
        ];

        return $steps[$step] ?? 'Step ' . $step;
    }
}

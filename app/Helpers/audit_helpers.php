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

if (!function_exists('auditSqlMonthExpression')) {
    /**
     * SQL expression for grouping timestamps/dates by year-month (driver-aware).
     */
    function auditSqlMonthExpression(string $column): string
    {
        return match (\Illuminate\Support\Facades\DB::connection()->getDriverName()) {
            'pgsql' => "TO_CHAR({$column}, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', {$column})",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }
}

if (!function_exists('getActiveAuditStatuses')) {
    /**
     * Get all active audit statuses for the current company.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getActiveAuditStatuses()
    {
        return \App\Models\AuditModule\AuditStatus::active()->forCompany()->ordered()->get();
    }
}

if (!function_exists('getActiveAuditTypes')) {
    /**
     * Get all active audit types for the current company.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getActiveAuditTypes()
    {
        return \App\Models\AuditModule\AuditType::active()->forCompany()->orderBy('name')->get();
    }
}

if (!function_exists('getAuditorUsers')) {
    /**
     * Get users who can act as auditors.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getAuditorUsers()
    {
        // Default: return all active non-client employees
        return \App\User::where('is_client', 0)
            ->whereNull('supplier_id')
            ->where('active', 1)
            ->where('is_support_staff', 0)
            ->orderBy('name')
            ->get();
    }
}

if (!function_exists('getActiveFindingCategories')) {
    /**
     * Get all active finding categories for the current company.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getActiveFindingCategories()
    {
        return \App\Models\AuditModule\FindingCategory::active()->forCompany()->orderBy('name')->get();
    }
}

if (!function_exists('getActiveRiskLevels')) {
    /**
     * Get all active risk levels for the current company.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getActiveRiskLevels()
    {
        return \App\Models\AuditModule\RiskLevel::active()->forCompany()->ordered()->get();
    }
}

if (!function_exists('getActiveWorkflowActions')) {
    /**
     * Get all active workflow actions for the current company.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getActiveWorkflowActions()
    {
        return \App\Models\AuditModule\WorkflowAction::active()->forCompany()->ordered()->get();
    }
}

if (!function_exists('getAvailableWorkflowActions')) {
    /**
     * Get available workflow action rules based on current status.
     *
     * @param int|null $statusId
     * @param string|null $statusName
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getAvailableWorkflowActions($statusId = null, $statusName = null)
    {
        return \App\Models\AuditModule\WorkflowActionRule::active()
            ->forCompany()
            ->forStatus($statusId, $statusName)
            ->with(['workflowAction', 'targetStatus'])
            ->ordered()
            ->get();
    }
}

if (!function_exists('getWorkflowActionByCode')) {
    /**
     * Get a workflow action by its code.
     *
     * @param string $code
     * @return \App\Models\AuditModule\WorkflowAction|null
     */
    function getWorkflowActionByCode($code)
    {
        return \App\Models\AuditModule\WorkflowAction::active()
            ->forCompany()
            ->where('code', $code)
            ->first();
    }
}

if (!function_exists('getWorkflowActionById')) {
    /**
     * Get a workflow action by its ID.
     *
     * @param int $id
     * @return \App\Models\AuditModule\WorkflowAction|null
     */
    function getWorkflowActionById($id)
    {
        return \App\Models\AuditModule\WorkflowAction::active()
            ->forCompany()
            ->find($id);
    }
}

if (!function_exists('auditConfigurationForCompany')) {
    /**
     * Scope configuration rows to current company or global (null company_id).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    function auditConfigurationForCompany($query)
    {
        $companyId = getUserCompany();

        return $query->where(function ($q) use ($companyId) {
            $q->whereNull('company_id');
            if ($companyId !== null && $companyId !== '') {
                $q->orWhere('company_id', $companyId);
            }
        });
    }
}

if (!function_exists('getActiveRootCauseMethods')) {
    /**
     * Get all active root cause analysis methods for the current company.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getActiveRootCauseMethods()
    {
        return \App\Models\AuditModule\RootCauseMethod::active()
            ->forCompany()
            ->orderBy('name')
            ->get();
    }
}

if (!function_exists('getActiveCapaCategories')) {
    /**
     * Get all active CAPA categories for the current company.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getActiveCapaCategories()
    {
        return \App\Models\AuditModule\CapaCategory::active()
            ->forCompany()
            ->orderBy('name')
            ->get();
    }
}

if (!function_exists('getActiveCapaActionTypes')) {
    /**
     * Get all active CAPA action types for the current company.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getActiveCapaActionTypes()
    {
        return auditConfigurationForCompany(
            \App\Models\AuditModule\CapaActionType::active()
        )->orderBy('name')->get();
    }
}

if (!function_exists('getActiveCapaPriorities')) {
    /**
     * Get all active CAPA priorities for the current company.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getActiveCapaPriorities()
    {
        return auditConfigurationForCompany(
            \App\Models\AuditModule\CapaPriority::active()
        )->ordered()->get();
    }
}

if (!function_exists('getActiveNcOrigins')) {
    /**
     * Get all active NC origins for the current company.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getActiveNcOrigins()
    {
        return auditConfigurationForCompany(
            \App\Models\AuditModule\NcOrigin::active()
        )->orderBy('name')->get();
    }
}

<?php

namespace App\Services\AuditModule;

use App\Models\AuditModule\Audit;
use App\Models\AuditModule\NonConformance;
use App\Models\AuditModule\CorrectiveAction;
use App\Models\AuditModule\AuditEmailTemplate;
use Carbon\Carbon;

class AuditNotificationService
{
    /**
     * Send upcoming audit notification
     */
    public function sendUpcomingAuditNotification(Audit $audit, int $daysBefore = 7): bool
    {
        $template = AuditEmailTemplate::forCompany()
            ->where('template_code', 'UPCOMING_AUDIT')
            ->active()
            ->first();

        if (!$template) {
            return false;
        }

        $variables = [
            'audit_number' => $audit->audit_number ?? 'N/A',
            'audit_title' => $audit->title ?? 'N/A',
            'audit_type' => $audit->auditType->name ?? $audit->audit_type_name ?? 'N/A',
            'scheduled_date' => $audit->scheduled_date ? Carbon::parse($audit->scheduled_date)->format('d/m/Y') : 'N/A',
            'lead_auditor' => $audit->leadAuditor->name ?? $audit->lead_auditor_name ?? 'N/A',
            'days_until' => $daysBefore,
        ];

        $rendered = $template->render($variables);

        // Send to lead auditor
        if ($audit->leadAuditor && $audit->leadAuditor->email) {
            notify_user($rendered['body'], $audit->leadAuditor->email, $rendered['subject']);
        }

        // Send to audit team members
        if ($audit->relationLoaded('teamMembers')) {
            foreach ($audit->teamMembers as $member) {
                if ($member->user && $member->user->email) {
                    notify_user($rendered['body'], $member->user->email, $rendered['subject']);
                }
            }
        }

        return true;
    }

    /**
     * Send overdue CAPA notification
     */
    public function sendOverdueCAPANotification(CorrectiveAction $capa): bool
    {
        $template = AuditEmailTemplate::forCompany()
            ->where('template_code', 'OVERDUE_CAPA')
            ->active()
            ->first();

        if (!$template) {
            return false;
        }

        $daysOverdue = now()->diffInDays(Carbon::parse($capa->due_date));

        $variables = [
            'capa_number' => $capa->capa_number ?? 'N/A',
            'capa_title' => $capa->title ?? 'N/A',
            'nc_number' => ($capa->relationLoaded('nonConformance') && $capa->nonConformance) ? $capa->nonConformance->nc_number : 'N/A',
            'action_owner' => ($capa->relationLoaded('actionOwnerUser') && $capa->actionOwnerUser) ? $capa->actionOwnerUser->name : 'N/A',
            'due_date' => $capa->due_date ? Carbon::parse($capa->due_date)->format('d/m/Y') : 'N/A',
            'days_overdue' => $daysOverdue,
        ];

        $rendered = $template->render($variables);

        // Send to action owner
        if ($capa->actionOwnerUser && $capa->actionOwnerUser->email) {
            notify_user($rendered['body'], $capa->actionOwnerUser->email, $rendered['subject']);
        }

        return true;
    }

    /**
     * Send NC requires action notification
     */
    public function sendNCRequiresActionNotification(NonConformance $nc): bool
    {
        $template = AuditEmailTemplate::forCompany()
            ->where('template_code', 'NC_REQUIRES_ACTION')
            ->active()
            ->first();

        if (!$template) {
            return false;
        }

        $variables = [
            'nc_number' => $nc->nc_number ?? 'N/A',
            'nc_title' => $nc->title ?? 'N/A',
            'origin' => $nc->origin_name ?? 'N/A',
            'risk_level' => ($nc->relationLoaded('riskLevel') && $nc->riskLevel) ? $nc->riskLevel->name : ($nc->risk_level_name ?? 'N/A'),
            'date_identified' => $nc->date_identified ? Carbon::parse($nc->date_identified)->format('d/m/Y') : 'N/A',
            'identified_by' => ($nc->relationLoaded('identifiedByUser') && $nc->identifiedByUser) ? $nc->identifiedByUser->name : 'N/A',
            'department' => $nc->department ?? 'N/A',
        ];

        $rendered = $template->render($variables);

        // Send to identified by user
        if ($nc->identifiedByUser && $nc->identifiedByUser->email) {
            notify_user($rendered['body'], $nc->identifiedByUser->email, $rendered['subject']);
        }

        return true;
    }

    /**
     * Send verification pending notification
     */
    public function sendVerificationPendingNotification(CorrectiveAction $capa): bool
    {
        $template = AuditEmailTemplate::forCompany()
            ->where('template_code', 'VERIFICATION_PENDING')
            ->active()
            ->first();

        if (!$template) {
            return false;
        }

        $variables = [
            'capa_number' => $capa->capa_number ?? 'N/A',
            'capa_title' => $capa->title ?? 'N/A',
            'nc_number' => ($capa->relationLoaded('nonConformance') && $capa->nonConformance) ? $capa->nonConformance->nc_number : 'N/A',
            'implementation_date' => $capa->implementation_date ? Carbon::parse($capa->implementation_date)->format('d/m/Y') : 'N/A',
            'action_owner' => ($capa->relationLoaded('actionOwnerUser') && $capa->actionOwnerUser) ? $capa->actionOwnerUser->name : 'N/A',
        ];

        $rendered = $template->render($variables);

        // Send to QA Manager or system admin (can be configured)
        // For now, send to action owner
        if ($capa->actionOwnerUser && $capa->actionOwnerUser->email) {
            notify_user($rendered['body'], $capa->actionOwnerUser->email, $rendered['subject']);
        }

        return true;
    }

    /**
     * Send audit completed notification
     */
    public function sendAuditCompletedNotification(Audit $audit): bool
    {
        $template = AuditEmailTemplate::forCompany()
            ->where('template_code', 'AUDIT_COMPLETED')
            ->active()
            ->first();

        if (!$template) {
            return false;
        }

        $variables = [
            'audit_number' => $audit->audit_number ?? 'N/A',
            'audit_title' => $audit->title ?? 'N/A',
            'audit_type' => ($audit->relationLoaded('auditType') && $audit->auditType) ? $audit->auditType->name : ($audit->audit_type_name ?? 'N/A'),
            'start_date' => $audit->start_date ? Carbon::parse($audit->start_date)->format('d/m/Y') : 'N/A',
            'end_date' => $audit->end_date ? Carbon::parse($audit->end_date)->format('d/m/Y') : 'N/A',
            'findings_count' => $audit->relationLoaded('findings') ? $audit->findings->count() : $audit->findings()->count(),
            'nc_count' => $audit->relationLoaded('nonConformances') ? $audit->nonConformances->count() : $audit->nonConformances()->count(),
            'lead_auditor' => ($audit->relationLoaded('leadAuditor') && $audit->leadAuditor) ? $audit->leadAuditor->name : ($audit->lead_auditor_name ?? 'N/A'),
        ];

        $rendered = $template->render($variables);

        // Send to lead auditor
        if ($audit->leadAuditor && $audit->leadAuditor->email) {
            notify_user($rendered['body'], $audit->leadAuditor->email, $rendered['subject']);
        }

        // Send to audit team
        if ($audit->relationLoaded('teamMembers')) {
            foreach ($audit->teamMembers as $member) {
                if ($member->user && $member->user->email) {
                    notify_user($rendered['body'], $member->user->email, $rendered['subject']);
                }
            }
        }

        return true;
    }
}


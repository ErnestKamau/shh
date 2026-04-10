<?php

namespace App\Http\Controllers\AuditModule;

use App\Http\Controllers\Controller;
use App\Models\AuditModule\AuditEmailTemplate;
use App\Models\AuditModule\AuditNotificationType;
use App\Services\AuditModule\AuditNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditEmailTemplateController extends Controller
{
    protected $notificationService;

    public function __construct(AuditNotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $templates = AuditEmailTemplate::forCompany()
            ->with('notificationType')
            ->orderBy('name')
            ->get();

        return view('layouts.audit.config.email-templates.index', compact('templates'));
    }

    public function create()
    {
        $notificationTypes = AuditNotificationType::active()
            ->where(function($q) {
                $companyId = getUserCompany() ?? 0;
                $q->where('company_id', $companyId)->orWhere('company_id', 0);
            })
            ->orderBy('name')
            ->get();

        return view('layouts.audit.config.email-templates.create', compact('notificationTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_code' => 'required|string|max:100|unique:audit_email_templates,template_code',
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'notification_type_id' => 'nullable|exists:audit_notification_types,id',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = getUserCompany() ?? 0;
        $validated['is_active'] = $request->has('is_active');

        AuditEmailTemplate::create($validated);

        return redirect()->route('audit.config.email-templates.index')
            ->with('success', 'Email template created successfully.');
    }

    public function edit($id)
    {
        $template = AuditEmailTemplate::forCompany()->findOrFail($id);
        
        $notificationTypes = AuditNotificationType::active()
            ->where(function($q) {
                $companyId = getUserCompany() ?? 0;
                $q->where('company_id', $companyId)->orWhere('company_id', 0);
            })
            ->orderBy('name')
            ->get();

        return view('layouts.audit.config.email-templates.edit', compact('template', 'notificationTypes'));
    }

    public function update(Request $request, $id)
    {
        $template = AuditEmailTemplate::forCompany()->findOrFail($id);

        $validated = $request->validate([
            'template_code' => 'required|string|max:100|unique:audit_email_templates,template_code,' . $id,
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'notification_type_id' => 'nullable|exists:audit_notification_types,id',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $template->update($validated);

        return redirect()->route('audit.config.email-templates.index')
            ->with('success', 'Email template updated successfully.');
    }

    public function destroy($id)
    {
        $template = AuditEmailTemplate::forCompany()->findOrFail($id);
        $template->delete();

        return redirect()->route('audit.config.email-templates.index')
            ->with('success', 'Email template deleted successfully.');
    }

    public function preview($id)
    {
        $template = AuditEmailTemplate::forCompany()->findOrFail($id);
        
        // Sample variables for preview
        $sampleVariables = [
            'audit_number' => 'AUD/2025/0001',
            'audit_title' => 'Internal Quality Audit - Q1 2025',
            'audit_type' => 'Internal Audit',
            'scheduled_date' => now()->addDays(7)->format('d/m/Y'),
            'lead_auditor' => 'John Doe',
            'days_until' => '7',
            'capa_number' => 'CAPA/2025/0001',
            'capa_title' => 'Corrective Action for Equipment Calibration',
            'nc_number' => 'NC/2025/0001',
            'action_owner' => 'Jane Smith',
            'due_date' => now()->subDays(5)->format('d/m/Y'),
            'days_overdue' => '5',
            'origin' => 'Audit Finding',
            'risk_level' => 'High',
            'date_identified' => now()->subDays(10)->format('d/m/Y'),
            'identified_by' => 'Quality Manager',
            'department' => 'Laboratory',
            'implementation_date' => now()->subDays(2)->format('d/m/Y'),
            'start_date' => now()->subDays(5)->format('d/m/Y'),
            'end_date' => now()->format('d/m/Y'),
            'findings_count' => '5',
            'nc_count' => '2',
        ];

        $rendered = $template->render($sampleVariables);

        return view('layouts.audit.config.email-templates.preview', compact('template', 'rendered', 'sampleVariables'));
    }

    public function test($id)
    {
        $template = AuditEmailTemplate::forCompany()->findOrFail($id);
        
        // Sample variables based on template code
        $sampleVariables = [
            'audit_number' => 'AUD/2025/0001',
            'audit_title' => 'Test Audit',
            'audit_type' => 'Internal Audit',
            'scheduled_date' => now()->addDays(7)->format('d/m/Y'),
            'lead_auditor' => Auth::user()->name,
            'days_until' => '7',
            'capa_number' => 'CAPA/2025/0001',
            'capa_title' => 'Test Corrective Action',
            'nc_number' => 'NC/2025/0001',
            'action_owner' => Auth::user()->name,
            'due_date' => now()->subDays(5)->format('d/m/Y'),
            'days_overdue' => '5',
            'origin' => 'Audit Finding',
            'risk_level' => 'High',
            'date_identified' => now()->subDays(10)->format('d/m/Y'),
            'identified_by' => Auth::user()->name,
            'department' => 'Laboratory',
            'implementation_date' => now()->subDays(2)->format('d/m/Y'),
            'start_date' => now()->subDays(5)->format('d/m/Y'),
            'end_date' => now()->format('d/m/Y'),
            'findings_count' => '5',
            'nc_count' => '2',
        ];

        $rendered = $template->render($sampleVariables);

        try {
            notify_user($rendered['body'], Auth::user()->email, $rendered['subject']);
            return back()->with('success', 'Test email sent successfully to ' . Auth::user()->email);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }
}


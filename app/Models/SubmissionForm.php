<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use App\CertificateTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionForm extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name',
        'document_code',
        'description',
        'naming_convention_prefix',
        'naming_convention_format',
        'is_published',
        'is_active',
        'is_hidden_from_rft',
        'is_customer_portal_form',
        'is_customer_request_form',
        'start_submission_number',
        'version',
        'issue_date',
        'print_template_name',
        'template_form_type_id',
        'form_type',
        'target_pages',
        'lims_destination_pages',
        'placement_mode',
        'display_mode',
        'placement_slot',
        'trigger_button_ids',
        'created_by'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_active' => 'boolean',
        'is_hidden_from_rft' => 'boolean',
        'is_customer_portal_form' => 'boolean',
        'is_customer_request_form' => 'boolean',
        'issue_date' => 'date',
        'template_form_type_id' => 'integer',
        'form_type' => 'string',
        'target_pages' => 'array',
        'lims_destination_pages' => 'array',
        'placement_slot' => 'array',
        'trigger_button_ids' => 'array'
    ];

    /**
     * Get the sections for this submission form
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sections()
    {
        return $this->hasMany(SubmissionFormSection::class)->orderBy('sort_order');
    }

    /**
     * Get the instances for this submission form
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function instances()
    {
        return $this->hasMany(SubmissionFormInstance::class);
    }

    /**
     * Get the user who created this form
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the template form type for this submission form.
     */
    public function templateFormType()
    {
        return $this->belongsTo(SubmissionFormTemplateType::class, 'template_form_type_id');
    }

    public function customers()
    {
        return $this->belongsToMany(
            \App\Models\CRM\CRMCustomer::class,
            'submission_form_customers',
            'submission_form_id',
            'crm_customer_id'
        );
    }

    public function sampleTypeCategories()
    {
        return $this->belongsToMany(
            \App\SampleTypeCategory::class,
            'submission_form_sample_type_categories',
            'submission_form_id',
            'sample_type_category_id'
        );
    }

    /**
     * (For attachment forms) The template forms this attachment form is linked to.
     */
    public function templateForms()
    {
        return $this->belongsToMany(
            SubmissionForm::class,
            'submission_form_template_links',
            'attachment_form_id',
            'template_form_id'
        );
    }

    /**
     * (For template forms) The attachment forms linked to this template form.
     */
    public function attachmentForms()
    {
        return $this->belongsToMany(
            SubmissionForm::class,
            'submission_form_template_links',
            'template_form_id',
            'attachment_form_id'
        )->where('form_type', 'attachment');
    }

    public function isTemplate(): bool
    {
        return $this->form_type === 'template';
    }

    public function isAttachment(): bool
    {
        return $this->form_type === 'attachment';
    }

    /**
     * Whether lab staff may fill this form in LIMS (e.g. Capture Samples).
     * Portal-only forms without samples_receiving/admin_portal placement remain portal-exclusive.
     */
    public function isLimsFillable(): bool
    {
        if (! $this->is_customer_portal_form) {
            return true;
        }

        $slots = $this->placement_slot ?? [];

        foreach ($slots as $value) {
            if (is_string($value) && in_array($value, ['samples_receiving', 'admin_portal'], true)) {
                return true;
            }

            if (is_array($value) && in_array((string) ($value['slot_id'] ?? ''), ['samples_receiving', 'admin_portal'], true)) {
                return true;
            }
        }

        return false;
    }

    public function isTestRequestTemplate(): bool
    {
        $code = strtoupper((string) ($this->document_code ?? ''));

        if (str_starts_with($code, 'TRF-')) {
            return true;
        }

        return str_contains(strtolower((string) ($this->name ?? '')), 'test request form');
    }

    public function hasPlacementSlot(string $slotId): bool
    {
        foreach ($this->placement_slot ?? [] as $value) {
            if (is_string($value) && $value === $slotId) {
                return true;
            }

            if (is_array($value) && (string) ($value['slot_id'] ?? '') === $slotId) {
                return true;
            }
        }

        return false;
    }

    public function canSubmitFromCustomerPortal(): bool
    {
        if (! $this->is_customer_portal_form || ! $this->isPublishedAndActive()) {
            return false;
        }

        $slots = $this->placement_slot ?? [];

        if ($slots === []) {
            return true;
        }

        return $this->hasPlacementSlot('customer_portal');
    }

    /**
     * Get the permissions for this submission form
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions()
    {
        return $this->hasMany(SubmissionFormPermission::class);
    }

    /**
     * Get the certificate templates for this submission form
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function certificateTemplates()
    {
        return $this->hasMany(CertificateTemplate::class);
    }

    /**
     * Check if the form is published and active
     * 
     * @return bool
     */
    public function isPublishedAndActive()
    {
        return $this->is_published && $this->is_active;
    }

    /**
     * Get the total number of sections in this form
     * 
     * @return int
     */
    public function getSectionCount()
    {
        return $this->sections()->count();
    }

    /**
     * Get the total number of elements across all sections
     * 
     * @return int
     */
    public function getElementCount()
    {
        return $this->sections()
            ->with('elementHolders.elements')
            ->get()
            ->sum(function ($section) {
                return $section->elementHolders->sum(function ($holder) {
                    return $holder->elements->count();
                });
            });
    }

    /**
     * Check if the form has any sections
     * 
     * @return bool
     */
    public function hasSections()
    {
        return $this->sections()->exists();
    }

    /**
     * Get form statistics
     * 
     * @return array
     */
    public function getStatistics()
    {
        return [
            'total_sections' => $this->getSectionCount(),
            'total_elements' => $this->getElementCount(),
            'total_instances' => $this->instances()->count(),
            'draft_instances' => $this->instances()->where('status', 'draft')->count(),
            'submitted_instances' => $this->instances()->where('status', 'submitted')->count(),
            'approved_instances' => $this->instances()->where('status', 'approved')->count(),
        ];
    }

    /**
     * Get the print template name for this form.
     * Uses print_template_name if set; otherwise resolves by form name (e.g. "Microbiology Submission Form" -> microbiology).
     *
     * @return string
     */
    public function getPrintTemplateName(): string
    {
        if (! empty($this->print_template_name)) {
            // Treat legacy "serology" template selection as using the microbiology-style layout for consistency
            if ($this->print_template_name === 'submission-forms.print.serology') {
                return 'submission-forms.print.microbiology';
            }

            if (view()->exists($this->print_template_name)) {
                return $this->print_template_name;
            }
        }

        $name = (string) $this->name;
        $nameLower = strtolower($name);

        $nameToTemplate = [
            'microbiology' => 'submission-forms.print.microbiology',
            'serology'     => 'submission-forms.print.microbiology',
        ];

        foreach ($nameToTemplate as $keyword => $templateName) {
            if (str_contains($nameLower, $keyword) && view()->exists($templateName)) {
                return $templateName;
            }
        }

        return 'submission-forms.print.default';
    }

    /**
     * Get the sample analysis stages (lab sections) for this submission form
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sampleAnalysisStages()
    {
        return $this->belongsToMany(\App\SampleAnalysisStage::class, 'submission_form_sample_analysis_stage');
    }
}
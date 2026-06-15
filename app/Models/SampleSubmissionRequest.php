<?php

namespace App\Models;

use App\Casts\SafeEncrypted;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\QuotationHeader;
use App\SampleHeader;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SampleSubmissionRequest extends Model
{
    use HasUuids;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_REQUESTED = 'Requested';

    public const STATUS_QUOTATION_IN_PROGRESS = 'Quotation In Progress';

    public const STATUS_QUOTATION_SENT = 'Quotation Sent';

    public const STATUS_QUOTATION_UNDER_REVIEW = 'Quotation Under Review';

    public const STATUS_QUOTATION_ACCEPTED = 'Quotation Accepted';

    public const STATUS_READY_FOR_RECEPTION = 'Ready for Reception';

    public const CUSTOMER_FEEDBACK_PREFIX = '[Customer feedback]';

    /** @var list<string> */
    public const COMMERCIAL_PIPELINE_STATUSES = [
        self::STATUS_REQUESTED,
        self::STATUS_QUOTATION_IN_PROGRESS,
        self::STATUS_QUOTATION_SENT,
        self::STATUS_QUOTATION_UNDER_REVIEW,
        self::STATUS_QUOTATION_ACCEPTED,
        self::STATUS_READY_FOR_RECEPTION,
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'sample_header_id',
        'submission_form_instance_id',
        'crm_customer_id',
        'crm_contact_id',
        'source_channel',
        'current_quotation_header_id',
        'accepted_quotation_header_id',
        'enquiry_notes',
        'quotation_accepted_at',
        'client_po_number',
        'po_skipped',
        'advance_payment_reference',
        'pricing_source',
        'reporting_language',
        'statement_of_conformity',
        'request_for_sampling',
        'sample_description',
        'reference_number',
        'date_expected',
        'priority',
        'batch_sample_type_id',
        'collection_data',
        'sample_lines',
        'request_date_of_service',
        'zone_id',
        'sample_id',
        'sample_type_id',
        'matrix_id',
        'parameter_ids',
        'submitting_agency',
        'submitting_officer_full_name',
        'submitting_officer_title',
        'physical_address',
        'region',
        'district',
        'working_station',
        'office_telephone_no',
        'mobile_telephone_no',
        'fax',
        'email',
        'case_no',
        'offence',
        'date_of_seizure',
        'seizure_region',
        'seizure_district',
        'seizure_ward',
        'seizure_village_street',
        'submitted_by_full_name',
        'submitted_by_title',
        'submitted_by_signature',
        'submitted_by_date',
        'submitted_by_time',
        'received_by_full_name',
        'received_by_title',
        'received_by_signature',
        'received_by_date',
        'received_by_time',
        'submission_date',
        'group_of_samples',
        'number_of_samples',
        'description_of_samples',
        'gcla_file_reference_number',
        'is_police_sample',
        'ir_number',
        'booking_date_status',
        'booking_date_reviewed_at',
        'purpose',
        'nature_of_sample',
        'safety_precautions',
        'safety_precautions_mention',
        'unique_identification',
        'mode_of_payment',
        'further_request',
        'mode_of_service_priority',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_seizure' => 'date',
            'submitted_by_date' => 'date',
            'submitted_by_title' => SafeEncrypted::class,
            'received_by_date' => 'date',
            'submission_date' => 'date',
            'request_date_of_service' => 'date',
            'date_expected' => 'date',
            'booking_date_reviewed_at' => 'datetime',
            'quotation_accepted_at' => 'datetime',
            'number_of_samples' => 'integer',
            'is_police_sample' => 'boolean',
            'request_for_sampling' => 'boolean',
            'po_skipped' => 'boolean',
            'parameter_ids' => 'array',
            'collection_data' => 'array',
            'sample_lines' => 'array',
        ];
    }

    public function getFormattedNumberAttribute(): string
    {
        return 'REQ-'.str_pad((string) $this->request_number, 4, '0', STR_PAD_LEFT);
    }

    public function commercialStatus(): string
    {
        $status = (string) ($this->status ?? '');

        return match ($status) {
            'submitted', 'Submitted' => self::STATUS_REQUESTED,
            'Quotation Ready to Send', 'Pending Quotation' => 'Quotation Pending',
            self::STATUS_READY_FOR_RECEPTION => $this->sample_header_id ? 'Sales Order Created' : 'Ready for Reception',
            'Received at Lab' => 'Sales Order Created',
            default => $status,
        };
    }

    public function isCommercialEnquiry(): bool
    {
        return in_array((string) $this->status, self::COMMERCIAL_PIPELINE_STATUSES, true)
            || in_array((string) $this->source_channel, ['portal', 'walk_in'], true);
    }

    public function isReadyForPhysicalReception(): bool
    {
        if (! in_array((string) $this->status, [
            self::STATUS_QUOTATION_ACCEPTED,
            self::STATUS_READY_FOR_RECEPTION,
        ], true)) {
            return false;
        }

        return $this->accepted_quotation_header_id !== null
            || $this->current_quotation_header_id !== null
            || $this->quotation_accepted_at !== null;
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SampleHeader::class, 'sample_header_id');
    }

    public function submissionFormInstance(): BelongsTo
    {
        return $this->belongsTo(SubmissionFormInstance::class, 'submission_form_instance_id');
    }

    public function resolveLinkedFormInstance(): ?SubmissionFormInstance
    {
        if ($this->relationLoaded('submissionFormInstance') && $this->submissionFormInstance !== null) {
            return $this->submissionFormInstance;
        }

        if ($this->submission_form_instance_id) {
            $instance = SubmissionFormInstance::query()
                ->with('submissionForm')
                ->find($this->submission_form_instance_id);

            if ($instance !== null) {
                return $instance;
            }
        }

        return SubmissionFormInstance::query()
            ->with('submissionForm')
            ->where('portal_request_id', (string) $this->id)
            ->first();
    }

    public function staffViewUrl(): string
    {
        $instance = ($this->relationLoaded('submissionFormInstance') && $this->submissionFormInstance !== null)
            ? $this->submissionFormInstance
            : $this->resolveLinkedFormInstance();

        if ($instance?->submissionForm) {
            return route('submission-forms.instances.show', [$instance->submissionForm, $instance]);
        }

        return route('sample-submission-requests.show', $this);
    }

    public function currentQuotation(): BelongsTo
    {
        return $this->belongsTo(QuotationHeader::class, 'current_quotation_header_id');
    }

    public function acceptedQuotation(): BelongsTo
    {
        return $this->belongsTo(QuotationHeader::class, 'accepted_quotation_header_id');
    }

    /**
     * @return BelongsTo<CRMCustomer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(CRMCustomer::class, 'crm_customer_id');
    }

    /**
     * @return BelongsTo<CustomerContact, $this>
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(CustomerContact::class, 'crm_contact_id');
    }

    public function suspects(): HasMany
    {
        return $this->hasMany(SampleSubmissionRequestSuspect::class)
            ->orderBy('serial_number')
            ->orderBy('id');
    }

    public function exhibits(): HasMany
    {
        return $this->hasMany(SampleSubmissionRequestExhibit::class)
            ->orderBy('serial_number')
            ->orderBy('id');
    }

    public function requestedAnalyses(): HasMany
    {
        return $this->hasMany(SampleSubmissionRequestRequestedAnalysis::class)
            ->orderBy('id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(QuotationHeader::class, 'sample_submission_request_id');
    }

    /**
     * @return BelongsToMany<SupportingDocumentTemplate, $this>
     */
    public function supportingDocumentTemplates(): BelongsToMany
    {
        return $this->belongsToMany(
            SupportingDocumentTemplate::class,
            'sample_submission_request_supporting_document_templates',
            'sample_submission_request_id',
            'supporting_document_template_id'
        )->withTimestamps();
    }

    /**
     * @return HasMany<SupportingDocumentInstance, $this>
     */
    public function supportingDocumentInstances(): HasMany
    {
        return $this->hasMany(SupportingDocumentInstance::class, 'sample_submission_request_id')
            ->orderByDesc('id');
    }

    public function workflowForms(): HasMany
    {
        return $this->hasMany(RequestWorkflowForm::class, 'sample_submission_request_id', 'id')
            ->latest('submitted_at');
    }

    public function customerFeedbackNotes(): string
    {
        $notes = (string) ($this->enquiry_notes ?? '');
        if ($notes === '') {
            return '';
        }

        $feedbackBlocks = [];
        foreach (preg_split("/\n\n/", $notes) ?: [] as $block) {
            $trimmed = trim((string) $block);
            if ($trimmed !== '' && str_starts_with($trimmed, self::CUSTOMER_FEEDBACK_PREFIX)) {
                $feedbackBlocks[] = trim(substr($trimmed, strlen(self::CUSTOMER_FEEDBACK_PREFIX)));
            }
        }

        return implode("\n\n", $feedbackBlocks);
    }

    public function hasCustomerFeedback(): bool
    {
        return $this->customerFeedbackNotes() !== '';
    }

    public function staffCommercialNotes(): string
    {
        $notes = (string) ($this->enquiry_notes ?? '');
        if ($notes === '') {
            return '';
        }

        $staffBlocks = [];
        foreach (preg_split("/\n\n/", $notes) ?: [] as $block) {
            $trimmed = trim((string) $block);
            if ($trimmed !== '' && ! str_starts_with($trimmed, self::CUSTOMER_FEEDBACK_PREFIX)) {
                $staffBlocks[] = $trimmed;
            }
        }

        return implode("\n\n", $staffBlocks);
    }

    public static function mergeEnquiryNotesPreservingFeedback(string $staffNotes, ?string $existingNotes): string
    {
        $feedbackBlocks = [];
        if (is_string($existingNotes) && trim($existingNotes) !== '') {
            foreach (preg_split("/\n\n/", $existingNotes) ?: [] as $block) {
                $trimmed = trim((string) $block);
                if ($trimmed !== '' && str_starts_with($trimmed, self::CUSTOMER_FEEDBACK_PREFIX)) {
                    $feedbackBlocks[] = $trimmed;
                }
            }
        }

        $staff = trim($staffNotes);
        $feedback = implode("\n\n", $feedbackBlocks);

        if ($staff === '' && $feedback === '') {
            return '';
        }

        if ($staff === '') {
            return $feedback;
        }

        if ($feedback === '') {
            return $staff;
        }

        return $staff."\n\n".$feedback;
    }
}

<?php

namespace App\Models;

use App\Casts\SafeEncrypted;
use App\Concerns\HasVarcharUuidRelationships;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\QuotationHeader;
use App\SampleHeader;
use App\Services\Sampleworkflow\SubcontractingAssignmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SampleSubmissionRequest extends Model
{
    use HasUuids;
    use HasVarcharUuidRelationships;

    private static ?bool $crmContactIdUsesNumericColumn = null;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_REQUESTED = 'Requested';

    public const STATUS_QUOTATION_IN_PROGRESS = 'Quotation In Progress';

    public const STATUS_QUOTATION_PENDING_APPROVAL = 'Quotation Pending Approval';

    public const STATUS_QUOTATION_READY_TO_SEND = 'Quotation Ready to Send';

    public const STATUS_QUOTATION_SENT = 'Quotation Sent';

    public const STATUS_QUOTATION_UNDER_REVIEW = 'Quotation Under Review';

    public const STATUS_QUOTATION_ACCEPTED = 'Quotation Accepted';

    public const STATUS_READY_FOR_RECEPTION = 'Ready for Reception';

    public const STATUS_SAMPLE_INTEGRITY_CHECK = 'Sample Integrity Check';

    public const STATUS_IN_REVIEW = 'In Review';

    /**
     * Samples accepted at AmSpec (job/batch created; may be in Samples In Lab).
     * Stored value remains `received_at_lab` for legacy rows — this is NOT
     * "received by the subcontracting laboratory".
     */
    public const STATUS_ACCEPTED = 'received_at_lab';

    public const SUBCONTRACT_DISPATCH_AWAITING = 'awaiting_dispatch';

    public const SUBCONTRACT_DISPATCH_DISPATCHED = 'dispatched';

    public const SUBCONTRACT_DISPATCH_DISPATCHED_AND_ASSIGNED = 'dispatched_and_assigned';

    /**
     * Completed dispatch statuses (legacy dispatched + dispatch with in-house analyst assignment).
     *
     * @return list<string>
     */
    public static function subcontractDispatchCompletedStatuses(): array
    {
        return [
            self::SUBCONTRACT_DISPATCH_DISPATCHED,
            self::SUBCONTRACT_DISPATCH_DISPATCHED_AND_ASSIGNED,
        ];
    }

    /**
     * Enquiry statuses that may appear on Sample Receiving → Sub-contracting.
     * Includes AmSpec-accepted enquiries so pending external dispatch stays visible
     * after the batch is routed to Samples In Lab.
     *
     * @return list<string>
     */
    public static function subcontractingQueueEnquiryStatuses(): array
    {
        return [
            self::STATUS_QUOTATION_ACCEPTED,
            self::STATUS_READY_FOR_RECEPTION,
            self::STATUS_SAMPLE_INTEGRITY_CHECK,
            self::STATUS_IN_REVIEW,
            self::STATUS_ACCEPTED,
        ];
    }

    public const CUSTOMER_FEEDBACK_PREFIX = '[Customer feedback]';

    /** Written by the customer portal / gateway when a quote is sent back for review. */
    public const CUSTOMER_QUOTATION_REVIEW_PREFIX = '[Customer quotation review request]';

    /**
     * Prefixes that mark customer-authored enquiry note blocks.
     *
     * @return list<string>
     */
    public static function customerFeedbackPrefixes(): array
    {
        return [
            self::CUSTOMER_FEEDBACK_PREFIX,
            self::CUSTOMER_QUOTATION_REVIEW_PREFIX,
        ];
    }

    /** @var list<string> */
    public const COMMERCIAL_PIPELINE_STATUSES = [
        self::STATUS_REQUESTED,
        self::STATUS_QUOTATION_IN_PROGRESS,
        self::STATUS_QUOTATION_PENDING_APPROVAL,
        self::STATUS_QUOTATION_READY_TO_SEND,
        self::STATUS_QUOTATION_SENT,
        self::STATUS_QUOTATION_UNDER_REVIEW,
        self::STATUS_QUOTATION_ACCEPTED,
        self::STATUS_READY_FOR_RECEPTION,
        self::STATUS_SAMPLE_INTEGRITY_CHECK,
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
        'created_from_quotation_header_id',
        'quotation_creation_token',
        'enquiry_notes',
        'enquiry_sample_configuration',
        'trf_section_field_values',
        'quotation_accepted_at',
        'quotation_first_sent_to_customer_at',
        'subcontracting_dispatch_status',
        'subcontracting_dispatch_date',
        'subcontracting_dispatch_lab_ids',
        'subcontracting_dispatch_lab_names',
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
            'quotation_first_sent_to_customer_at' => 'datetime',
            'subcontracting_dispatch_date' => 'datetime',
            'subcontracting_dispatch_lab_ids' => SafeEncrypted::class,
            'subcontracting_dispatch_lab_names' => SafeEncrypted::class,
            'number_of_samples' => 'integer',
            'is_police_sample' => 'boolean',
            'request_for_sampling' => 'boolean',
            'po_skipped' => 'boolean',
            'parameter_ids' => 'array',
            'collection_data' => 'array',
            'sample_lines' => 'array',
            'enquiry_sample_configuration' => 'array',
            'trf_section_field_values' => 'array',
        ];
    }

    public function setCrmContactIdAttribute(mixed $value): void
    {
        $this->attributes['crm_contact_id'] = $this->normalizeCrmContactId($value);
    }

    private function normalizeCrmContactId(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);
        if ($string === '') {
            return null;
        }

        if (self::crmContactIdUsesNumericColumn()) {
            return ctype_digit($string) ? $string : null;
        }

        return Str::isUuid($string) ? $string : null;
    }

    private static function crmContactIdUsesNumericColumn(): bool
    {
        if (self::$crmContactIdUsesNumericColumn !== null) {
            return self::$crmContactIdUsesNumericColumn;
        }

        try {
            $type = strtolower((string) Schema::getColumnType((new self)->getTable(), 'crm_contact_id'));
        } catch (\Throwable) {
            self::$crmContactIdUsesNumericColumn = false;

            return self::$crmContactIdUsesNumericColumn;
        }

        self::$crmContactIdUsesNumericColumn = str_contains($type, 'int');

        return self::$crmContactIdUsesNumericColumn;
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
            self::STATUS_QUOTATION_READY_TO_SEND, 'Pending Quotation' => 'Quotation Pending',
            self::STATUS_QUOTATION_PENDING_APPROVAL => self::STATUS_QUOTATION_PENDING_APPROVAL,
            self::STATUS_SAMPLE_INTEGRITY_CHECK => self::STATUS_SAMPLE_INTEGRITY_CHECK,
            self::STATUS_READY_FOR_RECEPTION => $this->sample_header_id ? 'Sales Order Created' : 'Ready for Reception',
            // Legacy In Review folds into Ready for Reception (Accept Samples).
            self::STATUS_IN_REVIEW => $this->sample_header_id ? 'Sales Order Created' : 'Ready for Reception',
            self::STATUS_ACCEPTED => 'Accepted',
            'Received at Lab' => 'Sales Order Created',
            default => $status,
        };
    }

    public function isCommercialEnquiry(): bool
    {
        return in_array((string) $this->status, self::COMMERCIAL_PIPELINE_STATUSES, true)
            || in_array((string) $this->source_channel, ['portal', 'walk_in', 'offline', 'scheduled'], true);
    }

    public function isReadyForPhysicalReception(): bool
    {
        if (! in_array((string) $this->status, [
            self::STATUS_QUOTATION_ACCEPTED,
            self::STATUS_READY_FOR_RECEPTION,
        ], true)) {
            return false;
        }

        // Subcontract dispatch is enforced at Integrity Accept, not at Ready for Reception.
        return $this->accepted_quotation_header_id !== null
            || $this->current_quotation_header_id !== null
            || $this->quotation_accepted_at !== null;
    }

    public function hasSubcontractedWork(): bool
    {
        return app(SubcontractingAssignmentService::class)
            ->resolveSubcontractedElementIds($this) !== [];
    }

    public function needsSubcontractDispatch(): bool
    {
        return $this->hasSubcontractedWork() && ! $this->isSubcontractDispatchCompleted();
    }

    /**
     * Enquiries with at least one subcontracted parameter (master flag or enquiry sample config).
     */
    public function scopeWhereHasSubcontractedWork(Builder $query): Builder
    {
        $driver = DB::connection()->getDriverName();

        return $query->where(function (Builder $matchQuery) use ($driver): void {
            $matchQuery->whereHas('requestedAnalyses', function (Builder $analysisQuery): void {
                $analysisQuery->whereHas('analysisElement', function (Builder $elementQuery): void {
                    $elementQuery->where('sub_contracted', 1);
                });
            })->orWhere(function (Builder $configQuery) use ($driver): void {
                if ($driver !== 'pgsql') {
                    // SQLite / other: match non-empty subcontracted_parameter_keys JSON text.
                    $configQuery->whereNotNull('enquiry_sample_configuration')
                        ->where('enquiry_sample_configuration', '!=', '[]')
                        ->where('enquiry_sample_configuration', 'like', '%subcontracted_parameter_keys%')
                        ->where('enquiry_sample_configuration', 'not like', '%"subcontracted_parameter_keys":[]%')
                        ->where('enquiry_sample_configuration', 'not like', '%"subcontracted_parameter_keys": []%');

                    return;
                }

                $configQuery->whereRaw(<<<'SQL'
EXISTS (
    SELECT 1
    FROM jsonb_array_elements(COALESCE(sample_submission_requests.enquiry_sample_configuration::jsonb, '[]'::jsonb)) AS cfg
    WHERE jsonb_typeof(COALESCE(cfg->'subcontracted_parameter_keys', '[]'::jsonb)) = 'array'
      AND jsonb_array_length(COALESCE(cfg->'subcontracted_parameter_keys', '[]'::jsonb)) > 0
)
SQL);
            })->orWhere(function (Builder $jsonSelectionQuery) use ($driver): void {
                if ($driver !== 'pgsql') {
                    $jsonSelectionQuery->whereRaw('1 = 0');

                    return;
                }

                $jsonSelectionQuery->whereExists(function ($jsonExists): void {
                    $jsonExists->selectRaw('1')
                        ->from('analysis_elements as ae')
                        ->where('ae.sub_contracted', 1)
                        ->where(function ($selectedIds): void {
                            $selectedIds
                                ->whereRaw("ae.id::text IN (SELECT jsonb_array_elements_text(COALESCE(sample_submission_requests.parameter_ids::jsonb, '[]'::jsonb)))")
                                ->orWhereRaw("ae.id::text IN (SELECT elem->>'analysis_element_id' FROM jsonb_array_elements(COALESCE(sample_submission_requests.sample_lines::jsonb, '[]'::jsonb)) AS elem WHERE COALESCE(elem->>'analysis_element_id', '') <> '')");
                        });
                });
            });
        });
    }

    /**
     * Subcontracted work that has not yet been dispatched to an external lab.
     */
    public function scopeWhereSubcontractDispatchPending(Builder $query): Builder
    {
        return $query->whereHasSubcontractedWork()
            ->where(function (Builder $statusQuery): void {
                $statusQuery
                    ->whereNull('subcontracting_dispatch_status')
                    ->orWhere('subcontracting_dispatch_status', '')
                    ->orWhere('subcontracting_dispatch_status', self::SUBCONTRACT_DISPATCH_AWAITING);
            });
    }

    public function subcontractingDispatchStatus(): string
    {
        $status = trim((string) ($this->subcontracting_dispatch_status ?? ''));

        return $status !== ''
            ? $status
            : self::SUBCONTRACT_DISPATCH_AWAITING;
    }

    public function isSubcontractDispatchAwaiting(): bool
    {
        return $this->subcontractingDispatchStatus() === self::SUBCONTRACT_DISPATCH_AWAITING;
    }

    public function isSubcontractDispatchCompleted(): bool
    {
        return in_array(
            $this->subcontractingDispatchStatus(),
            self::subcontractDispatchCompletedStatuses(),
            true
        );
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SampleHeader::class, 'sample_header_id');
    }

    public function submissionFormInstance(): BelongsTo
    {
        return $this->uuidBelongsTo(SubmissionFormInstance::class, 'submission_form_instance_id');
    }


    public function resolveLinkedFormInstance(): ?SubmissionFormInstance
    {
        if ($this->relationLoaded('submissionFormInstance') && $this->submissionFormInstance !== null) {
            return $this->submissionFormInstance;
        }

        $submissionFormInstanceId = trim((string) ($this->submission_form_instance_id ?? ''));
        if ($submissionFormInstanceId !== '') {
            $instance = SubmissionFormInstance::query()
                ->with('submissionForm')
                ->find($submissionFormInstanceId);

            if ($instance !== null) {
                return $instance;
            }
        }

        return SubmissionFormInstance::query()
            ->with('submissionForm')
            ->where('portal_request_id', (string) $this->id)
            ->orWhere(function ($query): void {
                $query
                    ->whereRaw("LOWER(COALESCE(target_record_type, '')) IN ('sample_submission_request', 'sample_submission_requests')")
                    ->where('target_record_id', (string) $this->id);
            })
            ->orWhere(function ($query): void {
                $identifier = trim((string) ($this->unique_identification ?? ''));

                if ($identifier === '') {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('form_number', $identifier);
            })
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

    public function createdFromQuotation(): BelongsTo
    {
        return $this->belongsTo(QuotationHeader::class, 'created_from_quotation_header_id');
    }

    public function enquiryQuotations(): HasMany
    {
        return $this->hasMany(EnquiryQuotation::class, 'sample_submission_request_id');
    }

    /**
     * @return BelongsToMany<QuotationHeader, $this>
     */
    public function linkedQuotations(): BelongsToMany
    {
        return $this->belongsToMany(
            QuotationHeader::class,
            'enquiry_quotations',
            'sample_submission_request_id',
            'quotation_header_id',
        )->withPivot([
            'link_source',
            'linked_at',
            'sent_to_customer_at',
            'accepted_at',
            'customer_acceptance_signature',
            'customer_acceptance_signer_name',
            'customer_acceptance_signed_at',
        ])->withTimestamps();
    }

    public function currentQuotationEngagement(): ?EnquiryQuotation
    {
        if ($this->current_quotation_header_id === null) {
            return null;
        }

        if ($this->relationLoaded('enquiryQuotations')) {
            return $this->enquiryQuotations
                ->firstWhere('quotation_header_id', $this->current_quotation_header_id);
        }

        return $this->enquiryQuotations()
            ->where('quotation_header_id', $this->current_quotation_header_id)
            ->first();
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

    public function subcontractingDispatchAssignments(): HasMany
    {
        return $this->hasMany(SubcontractingDispatchAssignment::class, 'sample_submission_request_id');
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
        $feedbackBlocks = [];
        foreach ($this->enquiryNoteBlocks() as $trimmed) {
            $message = self::stripCustomerFeedbackPrefix($trimmed);
            if ($message !== null) {
                $feedbackBlocks[] = $message;
            }
        }

        return implode("\n\n", $feedbackBlocks);
    }

    /**
     * Most recent customer review / feedback message (portal send-back reason).
     */
    public function latestCustomerFeedbackNotes(): string
    {
        $all = $this->customerFeedbackNotes();
        if ($all === '') {
            return '';
        }

        $parts = preg_split("/\n\n/", $all) ?: [];
        if ($parts === []) {
            return '';
        }

        return trim((string) end($parts));
    }

    public function hasCustomerFeedback(): bool
    {
        return $this->customerFeedbackNotes() !== '';
    }

    public function isQuotationUnderReview(): bool
    {
        $status = strtolower(trim((string) ($this->status ?? '')));

        return $status === strtolower(self::STATUS_QUOTATION_UNDER_REVIEW)
            || str_contains($status, 'quotation under review');
    }

    public function staffCommercialNotes(): string
    {
        $staffBlocks = [];
        foreach ($this->enquiryNoteBlocks() as $trimmed) {
            if (! self::isCustomerFeedbackBlock($trimmed)) {
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
                if ($trimmed !== '' && self::isCustomerFeedbackBlock($trimmed)) {
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

    public static function isCustomerFeedbackBlock(string $block): bool
    {
        return self::stripCustomerFeedbackPrefix($block) !== null;
    }

    public static function stripCustomerFeedbackPrefix(string $block): ?string
    {
        $trimmed = trim($block);
        if ($trimmed === '') {
            return null;
        }

        foreach (self::customerFeedbackPrefixes() as $prefix) {
            if (str_starts_with($trimmed, $prefix)) {
                return trim(substr($trimmed, strlen($prefix)));
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function enquiryNoteBlocks(): array
    {
        $notes = trim((string) ($this->enquiry_notes ?? ''));
        if ($notes === '') {
            return [];
        }

        $blocks = [];
        foreach (preg_split("/\n\n/", $notes) ?: [] as $block) {
            $trimmed = trim((string) $block);
            if ($trimmed !== '') {
                $blocks[] = $trimmed;
            }
        }

        return $blocks;
    }
}

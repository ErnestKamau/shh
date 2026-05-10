<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\Casts\SafeEncrypted;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\SampleHeader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SampleSubmissionRequest extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'sample_header_id',
        'crm_customer_id',
        'crm_contact_id',
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
        'status',
    ];

    protected $casts = [
        'date_of_seizure' => 'date',
        'submitted_by_date' => 'date',
        'submitted_by_title' => SafeEncrypted::class,
        'received_by_date' => 'date',
        'submission_date' => 'date',
        'booking_date_reviewed_at' => 'datetime',
        'number_of_samples' => 'integer',
        'is_police_sample' => 'boolean',
    ];

    public function getFormattedNumberAttribute(): string
    {
        return 'REQ-' . str_pad((string) $this->request_number, 4, '0', STR_PAD_LEFT);
    }

    public function batch()
    {
        return $this->belongsTo(SampleHeader::class, 'sample_header_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<CRMCustomer, $this>
     */
    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CRMCustomer::class, 'crm_customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<CustomerContact, $this>
     */
    public function contact(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CustomerContact::class, 'crm_contact_id');
    }

    public function suspects()
    {
        return $this->hasMany(SampleSubmissionRequestSuspect::class)
            ->orderBy('serial_number')
            ->orderBy('id');
    }

    public function exhibits()
    {
        return $this->hasMany(SampleSubmissionRequestExhibit::class)
            ->orderBy('serial_number')
            ->orderBy('id');
    }

    public function requestedAnalyses()
    {
        return $this->hasMany(SampleSubmissionRequestRequestedAnalysis::class)
            ->orderBy('id');
    }

    /**
     * Published templates selected for this request.
     *
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
     * Filled supporting document instances for this request (draft or submitted).
     *
     * @return HasMany<SupportingDocumentInstance, $this>
     */
    public function supportingDocumentInstances(): HasMany
    {
        return $this->hasMany(SupportingDocumentInstance::class, 'sample_submission_request_id')
            ->orderByDesc('id');
    }

    /**
     * Workflow-specific approval/rejection forms linked to this request.
     */
    public function workflowForms(): HasMany
    {
        return $this->hasMany(RequestWorkflowForm::class, 'sample_submission_request_id', 'id')
            ->latest('submitted_at');
    }
}
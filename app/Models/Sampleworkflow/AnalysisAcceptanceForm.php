<?php

namespace App\Models\Sampleworkflow;

use App\Invoice;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerNotification;
use App\Models\Billing\Pricelist;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\SampleHeader;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AnalysisAcceptanceForm extends Model
{
    use HasUuids;

    public const STATUS_AWAITING_CUSTOMER_SIGN = 'awaiting_customer_sign';

    public const STATUS_AWAITING_LAB_MANAGER_SIGN = 'awaiting_lab_manager_sign';

    public const STATUS_COMPLETED = 'completed';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'status',
        'submission_form_instance_id',
        'sample_submission_request_id',
        'crm_customer_id',
        'pricelist_id',
        'currency_id',
        'sample_header_id',
        'invoice_id',
        'customer_name',
        'request_date',
        'number_of_samples',
        'mode_of_work',
        'date_of_sampling',
        'total_amount',
        'customer_certification_text',
        'customer_signer_name',
        'customer_signature',
        'customer_signed_at',
        'manager_signer_name',
        'manager_signature',
        'manager_signed_at',
        'manager_assignment_payload',
        'processing_error',
        'receipt_notification_payload',
        'sample_configuration_payload',
        'raises_sample_disclaimer',
        'sample_disclaimer_payload',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'date_of_sampling' => 'date',
            'total_amount' => 'float',
            'number_of_samples' => 'integer',
            'customer_signed_at' => 'datetime',
            'manager_signed_at' => 'datetime',
            'manager_assignment_payload' => 'array',
            'receipt_notification_payload' => 'array',
            'sample_configuration_payload' => 'array',
            'raises_sample_disclaimer' => 'boolean',
            'sample_disclaimer_payload' => 'array',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(AnalysisAcceptanceFormLine::class, 'analysis_acceptance_form_id')->orderBy('sort_order');
    }

    public function submissionFormInstance(): BelongsTo
    {
        return $this->belongsTo(SubmissionFormInstance::class, 'submission_form_instance_id');
    }

    public function sampleSubmissionRequest(): BelongsTo
    {
        return $this->belongsTo(SampleSubmissionRequest::class, 'sample_submission_request_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CRMCustomer::class, 'crm_customer_id');
    }

    public function pricelist(): BelongsTo
    {
        return $this->belongsTo(Pricelist::class, 'pricelist_id');
    }

    public function sampleHeader(): BelongsTo
    {
        return $this->belongsTo(SampleHeader::class, 'sample_header_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customerNotifications(): MorphMany
    {
        return $this->morphMany(CustomerNotification::class, 'entity');
    }

    public function recalculateTotal(): void
    {
        $total = $this->lines()
            ->where('is_approved', true)
            ->get()
            ->sum(fn (AnalysisAcceptanceFormLine $line) => (float) $line->unit_amount * (int) $line->number_of_samples);

        $this->update(['total_amount' => $total]);
    }
}

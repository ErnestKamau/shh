<?php

namespace App\Models;

use App\QuotationHeader;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Links an enquiry to a quotation version and stores per-enquiry send/accept state.
 *
 * @see EnquiryQuotationService
 */
class EnquiryQuotation extends Model
{
    use HasUuids;

    public const LINK_SOURCE_BILLING_WIZARD = 'billing_wizard';

    public const LINK_SOURCE_PROCESS_ENQUIRY_EXISTING = 'process_enquiry_existing';

    public const LINK_SOURCE_REVISION_OPT_IN = 'revision_opt_in';

    public const LINK_SOURCE_ENQUIRY_BORN = 'enquiry_born';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'sample_submission_request_id',
        'quotation_header_id',
        'link_source',
        'linked_at',
        'linked_by_user_id',
        'sent_to_customer_at',
        'sent_via_portal',
        'sent_via_email',
        'accepted_at',
        'customer_acceptance_signature',
        'customer_acceptance_signer_name',
        'customer_acceptance_signed_at',
        'customer_acceptance_contact_id',
        'customer_acceptance_channel',
    ];

    protected function casts(): array
    {
        return [
            'linked_at' => 'datetime',
            'sent_to_customer_at' => 'datetime',
            'sent_via_portal' => 'boolean',
            'sent_via_email' => 'boolean',
            'accepted_at' => 'datetime',
            'customer_acceptance_signed_at' => 'datetime',
        ];
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(SampleSubmissionRequest::class, 'sample_submission_request_id');
    }

    public function quotationHeader(): BelongsTo
    {
        return $this->belongsTo(QuotationHeader::class, 'quotation_header_id');
    }

    public function wasSentToCustomer(): bool
    {
        return $this->sent_to_customer_at !== null;
    }

    public function wasAccepted(): bool
    {
        return $this->accepted_at !== null;
    }
}

<?php

namespace App\Models;

use App\QuotationHeader;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationApprovalLog extends Model
{
    use HasUuids;

    public const ACTION_SUBMITTED = 'submitted';

    public const ACTION_APPROVED = 'approved';

    public const ACTION_REJECTED = 'rejected';

    public const ACTION_REASSIGNED = 'reassigned';

    public const ACTION_SENT = 'sent';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'quotation_header_id',
        'sample_submission_request_id',
        'actor_user_id',
        'assignee_user_id',
        'action',
        'comments',
    ];

    public function quotationHeader(): BelongsTo
    {
        return $this->belongsTo(QuotationHeader::class, 'quotation_header_id');
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(SampleSubmissionRequest::class, 'sample_submission_request_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_user_id');
    }
}

<?php

namespace App\Models\Commercial;

use App\Models\SampleSubmissionRequest;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnquiryPurchaseOrderChange extends Model
{
    /** @use HasFactory<\Database\Factories\Commercial\EnquiryPurchaseOrderChangeFactory> */
    use HasFactory;

    use HasUuids;

    protected $table = 'enquiry_purchase_order_changes';

    protected $fillable = [
        'sample_submission_request_id',
        'from_customer_purchase_order_id',
        'to_customer_purchase_order_id',
        'from_po_number',
        'to_po_number',
        'enquiry_status',
        'reason',
        'changed_by',
    ];

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(SampleSubmissionRequest::class, 'sample_submission_request_id');
    }

    public function fromPurchaseOrder(): BelongsTo
    {
        return $this->belongsTo(CustomerPurchaseOrder::class, 'from_customer_purchase_order_id');
    }

    public function toPurchaseOrder(): BelongsTo
    {
        return $this->belongsTo(CustomerPurchaseOrder::class, 'to_customer_purchase_order_id');
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

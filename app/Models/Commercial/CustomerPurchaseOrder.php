<?php

namespace App\Models\Commercial;

use App\Models\CRM\CRMCustomer;
use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CustomerPurchaseOrder extends Model
{
    use HasUuids;

    protected $table = 'customer_purchase_orders';

    protected $fillable = [
        'po_number',
        'po_skipped',
        'file_path',
        'file_name',
        'mime',
        'size',
        'enquiry_id',
        'quotation_header_id',
        'customer_id',
        'uploaded_by',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'po_skipped' => 'boolean',
            'size' => 'integer',
            'recorded_at' => 'datetime',
        ];
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(SampleSubmissionRequest::class, 'enquiry_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(QuotationHeader::class, 'quotation_header_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CRMCustomer::class, 'customer_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function hasFile(): bool
    {
        return filled($this->file_path);
    }

    public function fileExists(): bool
    {
        return $this->hasFile()
            && Storage::disk('public')->exists((string) $this->file_path);
    }

    public function publicFileUrl(): ?string
    {
        if (! $this->hasFile()) {
            return null;
        }

        return Storage::disk('public')->url((string) $this->file_path);
    }

    public function isPdf(): bool
    {
        $mime = strtolower((string) ($this->mime ?? ''));
        if ($mime === 'application/pdf') {
            return true;
        }

        return str_ends_with(strtolower((string) ($this->file_name ?? '')), '.pdf');
    }

    public function isImage(): bool
    {
        $mime = strtolower((string) ($this->mime ?? ''));
        if (str_starts_with($mime, 'image/')) {
            return true;
        }

        $name = strtolower((string) ($this->file_name ?? ''));

        return str_ends_with($name, '.png')
            || str_ends_with($name, '.jpg')
            || str_ends_with($name, '.jpeg')
            || str_ends_with($name, '.gif')
            || str_ends_with($name, '.webp');
    }

    public function statusLabel(): string
    {
        if ($this->po_skipped) {
            return 'Skipped';
        }

        if (filled($this->po_number)) {
            return 'Recorded';
        }

        return 'Recorded';
    }
}

<?php

namespace App\Models\Commercial;

use App\Enums\Commercial\PurchaseOrderInvoicingMode;
use App\Enums\Commercial\PurchaseOrderStatus;
use App\Enums\Commercial\PurchaseOrderType;
use App\Models\CRM\CRMCustomer;
use App\Models\Currency;
use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use App\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class CustomerPurchaseOrder extends Model
{
    /** @use HasFactory<\Database\Factories\Commercial\CustomerPurchaseOrderFactory> */
    use HasFactory;

    use HasUuids;

    public const PERMISSION_VIEW = 'laboratory.components.purchase orders.view';

    public const PERMISSION_CREATE = 'laboratory.components.purchase orders.add';

    public const PERMISSION_AMEND = 'laboratory.components.purchase orders.edit';

    public const PERMISSION_CLOSE = 'laboratory.components.purchase orders.delete';

    public const PERMISSION_CHANGE_AT_RECEPTION = 'laboratory.components.po change at reception.edit';

    public const PERMISSION_APPLY_TO_HELD_JOB = 'laboratory.components.po held jobs.edit';

    public const PERMISSION_CANCEL_HELD_JOB = 'laboratory.components.po held jobs.delete';

    protected $table = 'customer_purchase_orders';

    protected $fillable = [
        'po_number',
        'po_skipped',
        'po_type',
        'status',
        'file_path',
        'file_name',
        'mime',
        'size',
        'enquiry_id',
        'quotation_header_id',
        'customer_id',
        'currency_id',
        'valid_from',
        'valid_to',
        'invoicing_mode',
        'invoicing_period',
        'expiry_notice_days',
        'expiry_notified_at',
        'closed_at',
        'notes',
        'uploaded_by',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'po_skipped' => 'boolean',
            'po_type' => PurchaseOrderType::class,
            'status' => PurchaseOrderStatus::class,
            'invoicing_mode' => PurchaseOrderInvoicingMode::class,
            'size' => 'integer',
            'expiry_notice_days' => 'integer',
            'valid_from' => 'date',
            'valid_to' => 'date',
            'expiry_notified_at' => 'datetime',
            'closed_at' => 'datetime',
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

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * @return HasMany<CustomerPurchaseOrderLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(CustomerPurchaseOrderLine::class, 'customer_purchase_order_id')
            ->orderBy('line_no');
    }

    /**
     * @return HasMany<CustomerPurchaseOrderLedgerEntry, $this>
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(CustomerPurchaseOrderLedgerEntry::class, 'customer_purchase_order_id');
    }

    /**
     * @return HasMany<CustomerPurchaseOrderAmendment, $this>
     */
    public function amendments(): HasMany
    {
        return $this->hasMany(CustomerPurchaseOrderAmendment::class, 'customer_purchase_order_id');
    }

    /**
     * @return HasMany<SampleSubmissionRequest, $this>
     */
    public function boundEnquiries(): HasMany
    {
        return $this->hasMany(SampleSubmissionRequest::class, 'customer_purchase_order_id');
    }

    public function isWithinValidity(CarbonInterface $date): bool
    {
        if ($this->valid_from !== null && $date->lt($this->valid_from->copy()->startOfDay())) {
            return false;
        }

        if ($this->valid_to !== null && $date->gt($this->valid_to->copy()->endOfDay())) {
            return false;
        }

        return true;
    }

    /**
     * True when quantity may be drawn from this PO for an event on the given date.
     */
    public function acceptsDrawsOn(CarbonInterface $date): bool
    {
        $status = $this->status ?? PurchaseOrderStatus::Active;

        return $status->acceptsDraws() && $this->isWithinValidity($date);
    }

    /**
     * Stored status, except that an active / exhausted PO past its valid_to shows as Expired
     * before the daily expiry command has caught up.
     */
    public function effectiveStatus(?CarbonInterface $on = null): PurchaseOrderStatus
    {
        $status = $this->status ?? PurchaseOrderStatus::Active;
        $on ??= now();

        if (in_array($status, [PurchaseOrderStatus::Active, PurchaseOrderStatus::Exhausted], true)
            && $this->valid_to !== null
            && $on->gt($this->valid_to->copy()->endOfDay())) {
            return PurchaseOrderStatus::Expired;
        }

        return $status;
    }

    /**
     * Closed and cancelled POs are final: no top-ups, new lines, or validity changes.
     */
    public function isAmendable(): bool
    {
        return ! in_array($this->status ?? PurchaseOrderStatus::Active, [PurchaseOrderStatus::Closed, PurchaseOrderStatus::Cancelled], true);
    }

    public function isBlanket(): bool
    {
        return ($this->po_type ?? PurchaseOrderType::Single) === PurchaseOrderType::Blanket;
    }

    public function daysUntilExpiry(?CarbonInterface $on = null): ?int
    {
        if ($this->valid_to === null) {
            return null;
        }

        return (int) ($on ?? now())->copy()->startOfDay()->diffInDays($this->valid_to->copy()->startOfDay(), false);
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

        return $this->effectiveStatus()->label();
    }
}

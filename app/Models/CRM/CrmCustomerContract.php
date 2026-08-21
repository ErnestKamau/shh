<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class CrmCustomerContract extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'crm_customer_contracts';

    protected $guarded = [];

    public const SCOPE_INSPECTION = 'inspection';

    public const SCOPE_STANDING_QC = 'standing_qc';

    public const SCOPE_MIXED = 'mixed';

    public const COLLECTION_COURIER_PICKUP = 'courier_pickup';

    public const COLLECTION_DROP_OFF = 'drop_off';

    public const COLLECTION_WALK_IN = 'walk_in';

    /**
     * @return array<string, string>
     */
    public static function contractScopeOptions(): array
    {
        return [
            self::SCOPE_INSPECTION => 'Inspection',
            self::SCOPE_STANDING_QC => 'Standing QC',
            self::SCOPE_MIXED => 'Mixed',
        ];
    }

    /**
     * Clear explanations for each contract scope (tooltips / help text).
     *
     * @return array<string, string>
     */
    public static function contractScopeTooltips(): array
    {
        return [
            self::SCOPE_INSPECTION => 'Lab or certified surveyor samples (or witnesses sampling) to a standard method. Used for custody/cargo-style work — not client self-sample walk-ins.',
            self::SCOPE_STANDING_QC => 'Routine QC or compliance under a standing agreement. Who draws the sample is set by Scheduled sampling (lab visit) or Collection method (client draws; lab only collects).',
            self::SCOPE_MIXED => 'Contract covers both Inspection and Standing QC lines of work. Apply the right sampling/collection rules per job.',
        ];
    }

    public static function contractScopeTooltip(?string $scope): ?string
    {
        if (! filled($scope)) {
            return null;
        }

        return self::contractScopeTooltips()[(string) $scope] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public static function collectionMethodOptions(): array
    {
        return [
            self::COLLECTION_COURIER_PICKUP => 'Courier pickup',
            self::COLLECTION_DROP_OFF => 'Drop-off',
            self::COLLECTION_WALK_IN => 'Walk-in',
        ];
    }

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_to' => 'date',
            'is_current' => 'boolean',
            'is_scheduled_sampling' => 'boolean',
            'file_size' => 'integer',
        ];
    }

    public function contractScopeLabel(): ?string
    {
        if (! filled($this->contract_scope)) {
            return null;
        }

        return self::contractScopeOptions()[(string) $this->contract_scope] ?? (string) $this->contract_scope;
    }

    public function collectionMethodLabel(): ?string
    {
        if (! filled($this->default_collection_method)) {
            return null;
        }

        return self::collectionMethodOptions()[(string) $this->default_collection_method]
            ?? (string) $this->default_collection_method;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CRMCustomer::class, 'crm_customer_id');
    }

    public function hasFile(): bool
    {
        return filled($this->file_path);
    }

    public function isPreviewable(): bool
    {
        $extension = strtolower((string) ($this->file_extension ?: pathinfo((string) $this->original_name, PATHINFO_EXTENSION)));
        $mime = strtolower((string) $this->mime_type);

        if (in_array($extension, ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
            return true;
        }

        return str_starts_with($mime, 'image/') || $mime === 'application/pdf';
    }

    public function isImagePreview(): bool
    {
        $extension = strtolower((string) ($this->file_extension ?: pathinfo((string) $this->original_name, PATHINFO_EXTENSION)));
        $mime = strtolower((string) $this->mime_type);

        return in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true)
            || str_starts_with($mime, 'image/');
    }
}

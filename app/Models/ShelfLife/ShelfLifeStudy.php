<?php

namespace App\Models\ShelfLife;

use App\Concerns\HasVarcharUuidRelationships;
use App\Models\CRM\CompanyProduct;
use App\Models\CRM\CRMCustomer;
use App\Models\SampleSubmissionRequest;
use App\SampleHeader;
use App\SampleType;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShelfLifeStudy extends Model
{
    use HasUuids;
    use HasVarcharUuidRelationships;

    public const TYPE_REAL_TIME = 'real_time';

    public const TYPE_ACCELERATED = 'accelerated';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ON_HOLD = 'on_hold';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_ABORTED = 'aborted';

    protected $table = 'shelf_life_studies';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'title',
        'company_id',
        'sample_header_id',
        'sample_type_id',
        'company_product_id',
        'sample_submission_request_id',
        'crm_customer_id',
        'batch_lot_no',
        'mfg_date',
        'study_type',
        'storage_temp_c',
        'storage_rh_percent',
        'storage_condition_label',
        'target_duration_value',
        'target_duration_unit',
        'start_date',
        'linked_real_time_study_id',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'mfg_date' => 'date',
            'start_date' => 'date',
            'storage_temp_c' => 'float',
            'storage_rh_percent' => 'float',
            'target_duration_value' => 'integer',
        ];
    }

    public function isAccelerated(): bool
    {
        return $this->study_type === self::TYPE_ACCELERATED;
    }

    public function isRealTime(): bool
    {
        return $this->study_type === self::TYPE_REAL_TIME;
    }

    public function sampleHeader(): BelongsTo
    {
        return $this->belongsTo(SampleHeader::class, 'sample_header_id');
    }

    public function sampleType(): BelongsTo
    {
        return $this->belongsTo(SampleType::class, 'sample_type_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(CompanyProduct::class, 'company_product_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CRMCustomer::class, 'crm_customer_id');
    }

    public function submissionRequest(): BelongsTo
    {
        return $this->belongsTo(SampleSubmissionRequest::class, 'sample_submission_request_id');
    }

    public function linkedRealTimeStudy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'linked_real_time_study_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parameterSpecs(): HasMany
    {
        return $this->hasMany(ShelfLifeStudyParameterSpec::class, 'shelf_life_study_id')
            ->orderBy('sort_order')
            ->orderBy('parameter_label');
    }

    public function pullPoints(): HasMany
    {
        return $this->hasMany(ShelfLifePullPoint::class, 'shelf_life_study_id')
            ->orderBy('sort_order')
            ->orderBy('scheduled_date');
    }

    public function studyTypeLabel(): string
    {
        return $this->isAccelerated() ? 'Accelerated' : 'Real-time';
    }

    public function storageConditionDisplay(): string
    {
        $label = trim((string) ($this->storage_condition_label ?? ''));
        if ($label !== '') {
            return $label;
        }

        $parts = [];
        if ($this->storage_temp_c !== null) {
            $parts[] = rtrim(rtrim(number_format((float) $this->storage_temp_c, 2, '.', ''), '0'), '.').'°C';
        }
        if ($this->storage_rh_percent !== null) {
            $parts[] = rtrim(rtrim(number_format((float) $this->storage_rh_percent, 2, '.', ''), '0'), '.').'% RH';
        }

        return $parts !== [] ? implode(' / ', $parts) : '—';
    }
}

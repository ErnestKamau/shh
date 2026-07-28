<?php

namespace App\Models\ShelfLife;

use App\CapturedResult;
use App\Concerns\HasVarcharUuidRelationships;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShelfLifePullPoint extends Model
{
    use HasUuids;
    use HasVarcharUuidRelationships;

    public const STATUS_PENDING = 'pending';

    public const STATUS_DUE = 'due';

    public const STATUS_TESTED = 'tested';

    public const STATUS_PASSED = 'passed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    protected $table = 'shelf_life_pull_points';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'shelf_life_study_id',
        'label',
        'offset_value',
        'offset_unit',
        'is_baseline',
        'scheduled_date',
        'actual_pull_date',
        'status',
        'sort_order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'offset_value' => 'integer',
            'is_baseline' => 'boolean',
            'scheduled_date' => 'date',
            'actual_pull_date' => 'date',
            'sort_order' => 'integer',
        ];
    }

    public function study(): BelongsTo
    {
        return $this->belongsTo(ShelfLifeStudy::class, 'shelf_life_study_id');
    }

    public function capturedResults(): HasMany
    {
        return $this->hasMany(CapturedResult::class, 'shelf_life_pull_point_id');
    }

    public function isDue(): bool
    {
        if ($this->status !== self::STATUS_PENDING && $this->status !== self::STATUS_DUE) {
            return false;
        }

        if ($this->scheduled_date === null) {
            return false;
        }

        return $this->scheduled_date->lte(now()->startOfDay());
    }
}

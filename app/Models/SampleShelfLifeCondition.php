<?php

namespace App\Models;

use App\Concerns\HasVarcharUuidRelationships;
use App\SampleDetails;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SampleShelfLifeCondition extends Model
{
    use HasUuids;
    use HasVarcharUuidRelationships;

    public const DURATION_DAYS = 'days';

    public const DURATION_WEEKS = 'weeks';

    public const DURATION_MONTHS = 'months';

    protected $table = 'sample_shelf_life_conditions';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'sample_detail_id',
        'study_type',
        'accelerated_temperature',
        'study_duration_value',
        'study_duration_unit',
        'relative_humidity',
        'evaluation_type',
        'sampling_frequency',
        'declared_shelf_life',
        'storage_condition',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'study_duration_value' => 'integer',
        ];
    }

    public function sampleDetail(): BelongsTo
    {
        return $this->belongsTo(SampleDetails::class, 'sample_detail_id');
    }

    public function studyDurationDisplay(): string
    {
        if ($this->study_duration_value === null) {
            return '—';
        }

        $unit = trim((string) ($this->study_duration_unit ?? ''));
        if ($unit === '') {
            return (string) $this->study_duration_value;
        }

        return $this->study_duration_value.' '.ucfirst($unit);
    }
}

<?php

namespace App\Models\ShelfLife;

use App\Analyte;
use App\AnalysisMethod;
use App\Concerns\HasVarcharUuidRelationships;
use App\ReportingUnit;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShelfLifeStudyParameterSpec extends Model
{
    use HasUuids;
    use HasVarcharUuidRelationships;

    public const SPEC_RANGE = 'range';

    public const SPEC_MAX = 'max';

    public const SPEC_MIN = 'min';

    public const SPEC_DELTA_FROM_BASELINE = 'delta_from_baseline';

    public const SPEC_PANEL_SCORE_MAX = 'panel_score_max';

    protected $table = 'shelf_life_study_parameter_specs';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'shelf_life_study_id',
        'analyte_id',
        'method_id',
        'reporting_unit_id',
        'parameter_label',
        'spec_type',
        'spec_low',
        'spec_high',
        'safety_margin_percent',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'spec_low' => 'float',
            'spec_high' => 'float',
            'safety_margin_percent' => 'float',
            'sort_order' => 'integer',
        ];
    }

    public function study(): BelongsTo
    {
        return $this->belongsTo(ShelfLifeStudy::class, 'shelf_life_study_id');
    }

    public function analyte(): BelongsTo
    {
        return $this->belongsTo(Analyte::class, 'analyte_id');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(AnalysisMethod::class, 'method_id');
    }

    public function reportingUnit(): BelongsTo
    {
        return $this->belongsTo(ReportingUnit::class, 'reporting_unit_id');
    }

    public function evaluate(?float $value, ?float $baselineValue = null): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($this->spec_type) {
            self::SPEC_MAX => $this->spec_high !== null && $value <= (float) $this->spec_high ? 'PASS' : 'FAIL',
            self::SPEC_MIN => $this->spec_low !== null && $value >= (float) $this->spec_low ? 'PASS' : 'FAIL',
            self::SPEC_PANEL_SCORE_MAX => $this->spec_high !== null && $value <= (float) $this->spec_high ? 'PASS' : 'FAIL',
            self::SPEC_DELTA_FROM_BASELINE => $this->evaluateDelta($value, $baselineValue),
            default => $this->evaluateRange($value),
        };
    }

    private function evaluateRange(float $value): string
    {
        $lowOk = $this->spec_low === null || $value >= (float) $this->spec_low;
        $highOk = $this->spec_high === null || $value <= (float) $this->spec_high;

        return ($lowOk && $highOk) ? 'PASS' : 'FAIL';
    }

    private function evaluateDelta(float $value, ?float $baselineValue): ?string
    {
        if ($baselineValue === null) {
            return null;
        }

        $delta = abs($value - $baselineValue);
        $limit = $this->spec_high ?? $this->spec_low;

        if ($limit === null) {
            return null;
        }

        return $delta <= (float) $limit ? 'PASS' : 'FAIL';
    }
}

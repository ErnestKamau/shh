<?php

namespace App\Models\QcModule;

use App\Models\QcModule\Configurations\QcSchemes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class QcSchemeRule extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    public const TYPE_BLANK_FREQUENCY = 'blank_frequency';

    public const TYPE_DUPLICATE_FREQUENCY = 'duplicate_frequency';

    public const TYPE_SPIKE_FREQUENCY = 'spike_frequency';

    public const TYPE_CRM_FREQUENCY = 'crm_frequency';

    public const TYPE_REPEAT_TOLERANCE_PERCENT = 'repeat_tolerance_percent';

    public const TYPE_CONTROL_LIMIT_SD = 'control_limit_sd';

    public const TYPE_MIN_REPLICATES = 'min_replicates';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'qc_scheme_rules';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function ruleTypeLabels(): array
    {
        return [
            self::TYPE_BLANK_FREQUENCY => 'Blank frequency',
            self::TYPE_DUPLICATE_FREQUENCY => 'Duplicate frequency',
            self::TYPE_SPIKE_FREQUENCY => 'Spike frequency',
            self::TYPE_CRM_FREQUENCY => 'CRM frequency',
            self::TYPE_REPEAT_TOLERANCE_PERCENT => 'Repeat tolerance %',
            self::TYPE_CONTROL_LIMIT_SD => 'Control limit (SD multiples)',
            self::TYPE_MIN_REPLICATES => 'Minimum replicates',
        ];
    }

    public function scheme(): BelongsTo
    {
        return $this->belongsTo(QcSchemes::class, 'qc_scheme_id');
    }
}

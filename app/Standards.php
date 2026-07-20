<?php

namespace App;

use App\Models\QcModule\Configurations\QcSchemes;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\QualityControl\Entities\Configurations\QcTypes as ConfigurationsQcTypes;
use OwenIt\Auditing\Contracts\Auditable;

class Standards extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'standards';

    protected $fillable = [
        'name', 'code', 'main_standard', 'is_qc_standard',
        'qc_type_id', 'qc_scheme_ids', 'status', 'edited_by',
    ];

    protected $appends = ['qcschemeidsarr', 'qcschemenames'];

    public function getQcType()
    {
        return ConfigurationsQcTypes::find($this->qc_type_id);
    }

    public function creator()
    {
        return User::find($this->edited_by);
    }

    public function qcSchemes(): BelongsToMany
    {
        return $this->belongsToMany(
            QcSchemes::class,
            'standard_qc_scheme',
            'standard_id',
            'qc_scheme_id'
        )->withTimestamps();
    }

    /**
     * Replace QC scheme links via bindings + pivot; keep legacy CSV column in sync.
     *
     * @param  array<int, string|int|null>  $schemeIds
     * @param  array{mode?: string, priority?: int}  $options
     */
    public function syncQcSchemes(array $schemeIds, array $options = []): void
    {
        app(\App\Services\Qc\QcSchemeBindingSync::class)->syncForStandard($this, $schemeIds, $options);
    }

    public function analysisMethods(): HasMany
    {
        return $this->hasMany(AnalysisMethod::class, 'based_on_standard_id');
    }

    /**
     * @return array<int, string>
     */
    public function getQcSchemeIdsArrAttribute(): array
    {
        if ($this->exists) {
            return $this->qcSchemes()
                ->allRelatedIds()
                ->map(static fn ($id) => (string) $id)
                ->values()
                ->all();
        }

        return array_values(array_filter(array_map(
            'trim',
            explode(',', (string) ($this->qc_scheme_ids ?? ''))
        )));
    }

    public function getQcSchemeNamesAttribute(): string
    {
        if ($this->exists) {
            $codes = $this->relationLoaded('qcSchemes')
                ? $this->qcSchemes->pluck('code')->filter()->values()->all()
                : $this->qcSchemes()->pluck('code')->filter()->values()->all();

            if ($codes !== []) {
                return implode(', ', array_values(array_unique($codes)));
            }
        }

        if (empty($this->qc_scheme_ids)) {
            return '';
        }

        $values = array_values(array_filter(array_map('trim', explode(',', (string) $this->qc_scheme_ids))));
        if ($values === []) {
            return '';
        }

        $uuidIds = [];
        $codes = [];

        foreach ($values as $value) {
            if ($this->isUuid($value)) {
                $uuidIds[] = $value;
            } else {
                $codes[] = $value;
            }
        }

        $resolved = [];

        if ($uuidIds !== []) {
            $resolved = array_merge($resolved, QcSchemes::query()->whereIn('id', $uuidIds)->pluck('code')->all());
        }

        if ($codes !== []) {
            $foundByCode = QcSchemes::query()->whereIn('code', $codes)->pluck('code')->all();
            $resolved = array_merge($resolved, $foundByCode);

            foreach ($codes as $code) {
                if (! in_array($code, $foundByCode, true)) {
                    $resolved[] = $code;
                }
            }
        }

        return implode(', ', array_values(array_unique($resolved)));
    }

    private function isUuid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value);
    }

    public function standardAnalytes(): HasMany
    {
        return $this->hasMany(StandardAnalytes::class, 'standard_id');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class TypeOfAnalysis extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'types_of_analysis';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'sort_order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function analysisElements(): HasMany
    {
        return $this->hasMany(AnalysisElements::class, 'type_of_analysis_id');
    }

    public function isInUse(): bool
    {
        return $this->analysisElements()->exists();
    }

    /**
     * @return list<string>
     */
    public static function defaultNames(): array
    {
        return [
            'Microbiological',
            'Legionella',
            'Chemical',
            'Migration',
            'Halal',
            'Shelf life',
            'Nutritional',
            'Residue & Contaminants',
            'Physical & Sensory',
            'Other',
        ];
    }
}

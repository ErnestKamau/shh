<?php

namespace App\Models\Equipments\Depreciation;

use App\Enums\Equipment\DepreciationMethodCode;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepreciationMethod extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'depreciation_methods';

    protected $fillable = [
        'code',
        'name',
        'description',
        'default_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_rate' => 'decimal:4',
            'is_active' => 'boolean',
            'code' => DepreciationMethodCode::class,
        ];
    }

    public function configs(): HasMany
    {
        return $this->hasMany(EquipmentDepreciationConfig::class, 'depreciation_method_id');
    }
}

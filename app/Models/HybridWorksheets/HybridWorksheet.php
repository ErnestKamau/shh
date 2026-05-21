<?php

namespace App\Models\HybridWorksheets;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class HybridWorksheet extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'document_control_no',
        'revision',
        'issue_date',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'issue_date' => 'date',
        ];
    }

    public function versions(): HasMany
    {
        return $this->hasMany(HybridWorksheetVersion::class);
    }

    public function activeVersion(): HasOne
    {
        return $this->hasOne(HybridWorksheetVersion::class)->where('is_active', true);
    }
}

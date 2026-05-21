<?php

namespace App\Models\HybridWorksheets;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class HybridWorksheetVersion extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'hybrid_worksheet_id',
        'version_number',
        'is_active',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'approved_at' => 'datetime',
            'created_by' => 'string',
            'approved_by' => 'string',
        ];
    }

    public function hybridWorksheet(): BelongsTo
    {
        return $this->belongsTo(HybridWorksheet::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(HybridWorksheetBlock::class)->orderBy('sort_order');
    }
}

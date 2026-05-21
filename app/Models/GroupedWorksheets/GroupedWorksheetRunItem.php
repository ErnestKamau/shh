<?php

namespace App\Models\GroupedWorksheets;

use App\Enums\GroupedWorksheetRunItemStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class GroupedWorksheetRunItem extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'grouped_worksheet_run_id',
        'grouped_worksheet_item_id',
        'status',
        'completed_by',
        'completed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => GroupedWorksheetRunItemStatus::class,
            'completed_at' => 'datetime',
            'metadata' => 'array',
            'completed_by' => 'string',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(GroupedWorksheetRun::class, 'grouped_worksheet_run_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(GroupedWorksheetItem::class, 'grouped_worksheet_item_id');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}

<?php

namespace App\Models\GroupedWorksheets;

use App\Enums\GroupedWorksheetRunStatus;
use App\Models\User;
use App\SampleHeader;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class GroupedWorksheetRun extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'sample_header_id',
        'grouped_worksheet_holder_id',
        'current_item_index',
        'status',
        'started_by',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => GroupedWorksheetRunStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'started_by' => 'string',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SampleHeader::class, 'sample_header_id');
    }

    public function holder(): BelongsTo
    {
        return $this->belongsTo(GroupedWorksheetHolder::class, 'grouped_worksheet_holder_id');
    }

    public function starter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function runItems(): HasMany
    {
        return $this->hasMany(GroupedWorksheetRunItem::class);
    }

    public function currentItem(): ?GroupedWorksheetItem
    {
        $items = $this->holder?->orderedItems;

        if (! $items || $items->isEmpty()) {
            return null;
        }

        return $items->values()->get($this->current_item_index);
    }
}

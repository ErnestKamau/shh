<?php

namespace App\Models\GroupedWorksheets;

use App\Enums\GroupedWorksheetItemType;
use App\Models\Formulars\Formula;
use App\Models\HybridWorksheets\HybridWorksheet;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\StageHeader;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class GroupedWorksheetItem extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'grouped_worksheet_holder_id',
        'sort_order',
        'label',
        'description',
        'item_type',
        'reference_id',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'item_type' => GroupedWorksheetItemType::class,
        ];
    }

    public function holder(): BelongsTo
    {
        return $this->belongsTo(GroupedWorksheetHolder::class, 'grouped_worksheet_holder_id');
    }

    public function getItemTypeEnum(): GroupedWorksheetItemType
    {
        return $this->item_type instanceof GroupedWorksheetItemType
            ? $this->item_type
            : GroupedWorksheetItemType::from($this->item_type);
    }

    public function referencedEntity(): ?Model
    {
        return match ($this->getItemTypeEnum()) {
            GroupedWorksheetItemType::Formula => Formula::find($this->reference_id),
            GroupedWorksheetItemType::Procedure => ProcedureWorksheet::find($this->reference_id),
            GroupedWorksheetItemType::StageHeader => StageHeader::find($this->reference_id),
            GroupedWorksheetItemType::HybridWorksheet => HybridWorksheet::find($this->reference_id),
        };
    }

    public function referenceName(): string
    {
        $entity = $this->referencedEntity();

        return $entity?->name ?? '—';
    }
}

<?php

namespace App\Models\HybridWorksheets;

use App\Enums\HybridWorksheetBlockType;
use App\Models\Formulars\Formula;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\StageHeader;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class HybridWorksheetBlock extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'hybrid_worksheet_version_id',
        'sort_order',
        'label',
        'block_type',
        'reference_id',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'block_type' => HybridWorksheetBlockType::class,
            'settings' => 'array',
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(HybridWorksheetVersion::class, 'hybrid_worksheet_version_id');
    }

    public function getBlockTypeEnum(): HybridWorksheetBlockType
    {
        return $this->block_type instanceof HybridWorksheetBlockType
            ? $this->block_type
            : HybridWorksheetBlockType::from($this->block_type);
    }

    public function formulaSteps(): HasMany
    {
        return $this->hasMany(HybridFormulaStep::class)->orderBy('step_number');
    }

    public function procedureSteps(): HasMany
    {
        return $this->hasMany(HybridProcedureStep::class)->orderBy('order');
    }

    public function sequenceStages(): HasMany
    {
        return $this->hasMany(HybridSequenceStage::class)->orderBy('order');
    }

    public function referenceName(): string
    {
        $type = $this->getBlockTypeEnum();

        if (! $type->isReference() || ! $this->reference_id) {
            return $this->label ?? $type->label();
        }

        $entity = match ($type) {
            HybridWorksheetBlockType::FormulaReference => Formula::find($this->reference_id),
            HybridWorksheetBlockType::ProcedureReference => ProcedureWorksheet::find($this->reference_id),
            HybridWorksheetBlockType::StageHeaderReference => StageHeader::find($this->reference_id),
            default => null,
        };

        return $entity?->name ?? '—';
    }
}

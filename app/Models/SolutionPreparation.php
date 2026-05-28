<?php

namespace App\Models;

use App\LabStockMovement;
use App\LabSubCategory;
use App\ReportingUnit;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class SolutionPreparation extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'solution_preparations';

    protected $fillable = [
        'solution_id',
        'preparation_number',
        'batch_number',
        'is_new_batch',
        'prepared_by',
        'prepared_at',
        'status',
        'notes',
        'quantity_prepared',
        'uom_id',
        'approved_by',
        'approved_at',
        'approval_notes',
        'ingredient_payload',
        'alternative_aware',
        'source_id',
    ];

    protected function casts(): array
    {
        return [
            'prepared_at' => 'datetime',
            'approved_at' => 'datetime',
            'is_new_batch' => 'boolean',
            'alternative_aware' => 'boolean',
            'ingredient_payload' => 'array',
            'quantity_prepared' => 'decimal:4',
        ];
    }

    public static function generatePreparationNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "PREP-{$date}-";
        $last = static::query()
            ->where('preparation_number', 'like', $prefix.'%')
            ->orderByDesc('preparation_number')
            ->value('preparation_number');

        $sequence = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function solution(): BelongsTo
    {
        return $this->belongsTo(LabSubCategory::class, 'solution_id');
    }

    public function preparedUom(): BelongsTo
    {
        return $this->belongsTo(ReportingUnit::class, 'uom_id');
    }

    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function sourcePreparation(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(PreparationStep::class, 'preparation_id')->orderBy('step_number');
    }

    public function inoculatedMedia(): HasMany
    {
        return $this->hasMany(PreparationInoculatedMedia::class, 'preparation_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(LabStockMovement::class, 'preparation_id');
    }

    public function isInProgress(): bool
    {
        return $this->status === 'preparing';
    }

    public function isAwaitingApproval(): bool
    {
        return $this->status === 'awaiting_approval';
    }

    public function allStepsCompleted(): bool
    {
        $steps = $this->relationLoaded('steps') ? $this->steps : $this->steps()->get();

        if ($steps->isEmpty()) {
            return false;
        }

        return $steps->every(fn (PreparationStep $step) => $step->isCompleted());
    }

    public function allResultsCaptured(): bool
    {
        return count($this->getMissingResultReasons()) === 0;
    }

    /**
     * @return list<string>
     */
    public function getMissingResultReasons(): array
    {
        $reasons = [];
        $steps = $this->relationLoaded('steps') ? $this->steps : $this->steps()->with(['results', 'controls', 'media'])->get();

        foreach ($steps as $step) {
            foreach ($step->getCompletionBlockers() as $blocker) {
                $reasons[] = "Step {$step->step_number} ({$step->step_name}): {$blocker}";
            }
        }

        if ($this->requiresInoculatedMedia() && $this->inoculatedMedia()->count() === 0) {
            $reasons[] = 'Inoculated media results are required.';
        }

        return $reasons;
    }

    public function requiresInoculatedMedia(): bool
    {
        $steps = $this->relationLoaded('steps') ? $this->steps : $this->steps()->with('media')->get();

        return $steps->contains(fn (PreparationStep $step) => $step->media->isNotEmpty());
    }

    public function canMoveToAwaitingApproval(): bool
    {
        return $this->isInProgress()
            && $this->steps()->count() > 0
            && $this->allStepsCompleted()
            && $this->allResultsCaptured();
    }

    public function progressPercent(): int
    {
        $total = $this->steps()->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->steps()->get()->filter(fn (PreparationStep $s) => $s->isCompleted())->count();

        return (int) round(($completed / $total) * 100);
    }
}

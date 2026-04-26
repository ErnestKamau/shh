<?php

namespace App\Models\MethodSequences;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\LabSubCategory;
use App\Models\Equipments\Equipment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Collection;

class MethodSequenceStage extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'method_sequence_version_id',
        'name',
        'description',
        'order',
        'equipment_ids',
        'media_ids',
        'control_ids',
        'is_result_stage',
        'fail_move_next_stage',
        'duration',
        'move_to_next_stage_safe_duration',
        'is_end_stage',
        'is_end_stage_if_pass',
    ];

    protected $casts = [
        'equipment_ids' => 'array',
        'media_ids' => 'array',
        'control_ids' => 'array',
        'is_result_stage' => 'boolean',
        'fail_move_next_stage' => 'boolean',
        'duration' => 'decimal:2',
        'move_to_next_stage_safe_duration' => 'decimal:2',
        'is_end_stage' => 'boolean',
        'is_end_stage_if_pass' => 'boolean',
    ];

    /**
     * Get the method sequence version that owns this stage.
     */
    public function methodSequenceVersion(): BelongsTo
    {
        return $this->belongsTo(MethodSequenceVersion::class);
    }

    /**
     * Get the equipment items for this stage.
     */
    public function equipments(): Collection
    {
        if (empty($this->equipment_ids)) {
            return collect([]);
        }
        
        return Equipment::whereIn('id', $this->equipment_ids)->get();
    }

    /**
     * Get the media items for this stage.
     */
    public function medias(): Collection
    {
        if (empty($this->media_ids)) {
            return collect([]);
        }
        
        return LabSubCategory::whereIn('id', $this->media_ids)->get();
    }

    /**
     * Get the control items for this stage.
     */
    public function controls(): Collection
    {
        if (empty($this->control_ids)) {
            return collect([]);
        }
        
        return LabSubCategory::whereIn('id', $this->control_ids)->get();
    }

    /**
     * Get the duration range display.
     */
    public function getDurationRangeAttribute(): string
    {
        if (empty($this->duration)) {
            return 'Not specified';
        }

        if (empty($this->move_to_next_stage_safe_duration)) {
            return $this->duration . ' hrs';
        }

        return $this->duration . '-' . $this->move_to_next_stage_safe_duration . ' hrs';
    }
}


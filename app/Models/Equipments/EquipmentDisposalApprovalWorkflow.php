<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use App\Models\Assets\AssetType;
use App\Models\Assets\AssetLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentDisposalApprovalWorkflow extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'workflow_name',
        'description',
        'equipment_type_id',
        'location_id',
        'is_active',
        'created_by',
        'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'equipment_type_id' => 'integer',
        'location_id' => 'integer',
        'company_id' => 'integer',
    ];

    /**
     * Get the asset type this workflow applies to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(AssetType::class, 'equipment_type_id');
    }

    /**
     * Get the location (plant) this workflow applies to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(AssetLocation::class, 'location_id');
    }

    /**
     * Get the workflow steps
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function steps(): HasMany
    {
        return $this->hasMany(EquipmentDisposalApprovalWorkflowStep::class, 'workflow_id')->orderBy('step_order');
    }

    /**
     * Get the user who created this workflow
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get only active workflow steps
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActiveSteps()
    {
        return $this->steps()->get();
    }

    /**
     * Get the next step in the workflow
     *
     * @param int|null $currentStepOrder
     * @return EquipmentDisposalApprovalWorkflowStep|null
     */
    public function getNextStep(?int $currentStepOrder = null)
    {
        if ($currentStepOrder === null) {
            return $this->steps()->first();
        }

        return $this->steps()
            ->where('step_order', '>', $currentStepOrder)
            ->first();
    }

    /**
     * Scope to get only active workflows
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Find workflow for equipment based on type, location, and company
     *
     * @param int|null $equipmentTypeId
     * @param int|null $locationId
     * @param int $companyId
     * @return static|null
     */
    public static function findForEquipment(?int $equipmentTypeId, ?int $locationId, int $companyId)
    {
        // Try to find exact match first
        $workflow = static::active()
            ->where('company_id', $companyId)
            ->where(function($query) use ($equipmentTypeId, $locationId) {
                $query->where(function($q) use ($equipmentTypeId, $locationId) {
                    $q->where('equipment_type_id', $equipmentTypeId)
                      ->where('location_id', $locationId);
                })
                ->orWhere(function($q) use ($equipmentTypeId) {
                    $q->where('equipment_type_id', $equipmentTypeId)
                      ->whereNull('location_id');
                })
                ->orWhere(function($q) use ($locationId) {
                    $q->whereNull('equipment_type_id')
                      ->where('location_id', $locationId);
                })
                ->orWhere(function($q) {
                    $q->whereNull('equipment_type_id')
                      ->whereNull('location_id');
                });
            })
            ->orderByRaw('CASE 
                WHEN equipment_type_id IS NOT NULL AND location_id IS NOT NULL THEN 1
                WHEN equipment_type_id IS NOT NULL THEN 2
                WHEN location_id IS NOT NULL THEN 3
                ELSE 4
            END')
            ->first();

        return $workflow;
    }
}


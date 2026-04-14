<?php

namespace App\Models\DMS;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentApprovalWorkflow extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'document_type_id',
        'workflow_name',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the document type this workflow belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Get the workflow steps
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function steps(): HasMany
    {
        return $this->hasMany(DocumentApprovalWorkflowStep::class, 'workflow_id')->orderBy('step_order');
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
     * @return DocumentApprovalWorkflowStep|null
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
}


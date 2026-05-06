<?php

namespace App\Models\Workflow;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'workflow_approvals';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'stage_name',
        'code',
        'name',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function checklistItems()
    {
        return $this->hasMany(ChecklistItem::class, 'approval_id')->orderBy('order');
    }

    public function checklistResponses()
    {
        return $this->hasMany(ChecklistResponse::class, 'approval_id');
    }

    public function approvalLogs()
    {
        return $this->hasMany(ApprovalLog::class, 'approval_id')->orderByDesc('approved_at');
    }
}
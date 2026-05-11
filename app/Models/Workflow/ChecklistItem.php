<?php

namespace App\Models\Workflow;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'workflow_checklist_items';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'approval_id',
        'label',
        'type',
        'is_required',
        'options',
        'order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'options' => 'array',
        'order' => 'integer',
    ];

    public function approval()
    {
        return $this->belongsTo(Approval::class, 'approval_id');
    }

    public function responses()
    {
        return $this->hasMany(ChecklistResponse::class, 'checklist_item_id');
    }
}
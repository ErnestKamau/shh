<?php

namespace App\Models\Workflow;

use App\SampleHeader;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistResponse extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'workflow_checklist_responses';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'sample_id',
        'approval_id',
        'checklist_item_id',
        'value',
        'user_id',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    public function approval()
    {
        return $this->belongsTo(Approval::class, 'approval_id');
    }

    public function checklistItem()
    {
        return $this->belongsTo(ChecklistItem::class, 'checklist_item_id');
    }

    public function sample()
    {
        return $this->belongsTo(SampleHeader::class, 'sample_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
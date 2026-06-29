<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class FeedbackCorrectiveAction extends Model
{
    protected $table = 'crm_feedback_corrective_actions';

    protected $fillable = [
        'customer_feedback_id',
        'reference_no',
        'status',
        'auto_triggered',
        'triggered_at',
        'opened_by',
        'assigned_to',
        'verified_by',
        'verified_at',
        'closed_at',
        'summary_notes',
    ];

    protected $casts = [
        'auto_triggered' => 'boolean',
        'triggered_at' => 'datetime',
        'verified_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public static function statuses()
    {
        return ['Draft', 'Open', 'In Progress', 'Completed', 'Verified', 'Closed'];
    }

    public function feedback()
    {
        return $this->belongsTo(CustomerFeedback::class, 'customer_feedback_id');
    }

    public function opener()
    {
        return $this->belongsTo(\App\User::class, 'opened_by');
    }

    public function assignedUser()
    {
        return $this->belongsTo(\App\User::class, 'assigned_to');
    }

    public function verifiedUser()
    {
        return $this->belongsTo(\App\User::class, 'verified_by');
    }

    public function items()
    {
        return $this->hasMany(FeedbackCorrectiveActionItem::class, 'feedback_corrective_action_id');
    }
}
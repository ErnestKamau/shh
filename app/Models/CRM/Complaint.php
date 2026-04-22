<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Complaint extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $fillable = [
        'complaint_id',
        'description',
        'priority',
        'type',
        'received_from',
        'registered_by',
        'complaint_workflow',
        'is_closed',
        'edited_by',
        'rejected',
        'date',
        'reject_workflow',
        'client_id',
        'closure_recipient_emails',
        'closure_sent_at',
        'mode_of_delivery',
        'received_by',
        'received_from_type',
        'is_lab_related',
        'nature_of_complaint',
        'test_item_report_serial_no',
        'intake_approved_by',
        'intake_approved_at',
        'closed_by',
        'date_closed',
        'organization_name',
        'contact_name',
        'title_position',
        'test_item',
        'report_serial_no',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'closure_sent_at' => 'datetime',
            'closure_recipient_emails' => 'array',
            'is_closed' => 'boolean',
            'rejected' => 'boolean',
            'is_lab_related' => 'boolean',
            'date_closed' => 'date',
            'intake_approved_at' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(CRMCustomer::class, 'client_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function ticketStatus(): BelongsTo
    {
        return $this->belongsTo(TicketStatus::class, 'complaint_workflow', 'workflow_value');
    }

    public function ticketPriority(): BelongsTo
    {
        return $this->belongsTo(TicketPriority::class, 'priority', 'value');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'assigned_to');
    }

    public function assignedDevelopers(): HasMany
    {
        return $this->hasMany(TicketAssignment::class, 'ticket_id');
    }

    public function chat(): HasMany
    {
        return $this->hasMany(TicketChat::class, 'ticket_id');
    }

    public function intakeApprovedBy()
    {
        return $this->belongsTo(\App\User::class, 'intake_approved_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(\App\User::class, 'closed_by');
    }

    public function notes()
    {
        return $this->hasMany(Complaintnotes::class);
    }

    public function attachments()
    {
        return $this->hasMany(Complaintattachment::class);
    }

    public function resolutions()
    {
        return $this->hasMany(Complaintsresolutions::class);
    }

    public function capaRecord()
    {
        return $this->hasOne(CapaRecord::class);
    }

    public function chainOfCustody()
    {
        return $this->hasMany(Chain_of_Custody_Complaint::class);
    }

    public function scopeForUser($query, int $userId)
    {
        $user = \App\User::query()->find($userId);

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
                ->orWhere('created_by', $user->name);
        });
    }
}

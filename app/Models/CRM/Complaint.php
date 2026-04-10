<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use App\User;

class Complaint extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $fillable = [
        'complaint_id',
        'description',
        'priority',
        'type',
        'received_from',
        'registered_by',
        'date',
        'complaint_workflow',
        'is_closed',
        'edited_by',
        'rejected',
        'reject_workflow',
        'client_id',
        'ticket_no',
        'time_created',
        'disposition_id',
        'is_fcr',
        'issue_source',
        'ticket_status',
        'issue_category',
        'initial_department',
        'current_department',
        'raised_by',
        'customer_type',
        'phone',
        'depot',
        'dealer_name',
        'dealer_code',
        'comments',
        'resolution',
        'investigation_and_findings',
        'preventive_measures',
        'root_cause',
        'ticket_products',
        'token_received',
        'batch_numbers',
        'resolved_time',
        'resolved_by',
        'created_by',
        'assigned_to',
        'assigned_date',
        'age',
        'resolution_approved',
        'resolution_approved_at',
        'resolution_approved_by',
        'deleted_at',
        'submitted_from',
        'ticket_category_id',
        'is_internal_note',
        'escalated_to_user_id',
        'escalated_from_user_id',
        'escalation_reason',
        'sla_level',
        'first_response_at',
        'first_response_sla_status',
        'resolution_sla_status'
    ];

    protected $casts = [
        'date' => 'datetime',
        'time_created' => 'datetime',
        'resolved_time' => 'datetime',
        'assigned_date' => 'date',
        'is_closed' => 'boolean',
        'rejected' => 'boolean',
        'is_fcr' => 'boolean',
        'resolution_approved' => 'boolean',
        'resolution_approved_at' => 'datetime',
        'is_internal_note' => 'boolean',
        'deleted_at' => 'datetime',
        'archived_at' => 'datetime',
        'is_archived' => 'boolean',
        'first_response_at' => 'datetime',
    ];

    public function disposition()
    {
        return $this->belongsTo(Complaint_Type::class, 'disposition_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\CRM\CompanyProduct::class, 'product_id');
    }

    public function customer()
    {
        return $this->belongsTo(CRMCustomer::class, 'client_id');
    }

    public function chainOfCustody()
    {
        return $this->hasMany(Chain_of_Custody_Complaint::class, 'complaint_id');
    }

    public function attachments()
    {
        return $this->hasMany(Complaintattachment::class, 'complaint_id');
    }

    public function notes()
    {
        return $this->hasMany(Complaintnotes::class, 'complaint_id');
    }

    public function resolutions()
    {
        return $this->hasMany(Complaintsresolutions::class, 'complaint_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by', 'name');
    }

    public function registeredByUser()
    {
        return $this->belongsTo(User::class, 'registered_by', 'name');
    }

    public function resolvedByUser()
    {
        return $this->belongsTo(User::class, 'resolved_by', 'name');
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class, 'ticket_id');
    }

    public function publicComments()
    {
        return $this->hasMany(TicketComment::class, 'ticket_id')
            ->where('is_internal', false);
    }

    public function allComments()
    {
        return $this->hasMany(TicketComment::class, 'ticket_id');
    }

    public function internalComments()
    {
        return $this->hasMany(TicketComment::class, 'ticket_id')->where('is_internal', true);
    }

    public function changeHistory()
    {
        return $this->hasMany(TicketChangeHistory::class, 'ticket_id')->orderBy('created_at', 'desc');
    }

    public function teamChat()
    {
        return $this->hasMany(TicketTeamChat::class, 'ticket_id')->orderBy('created_at', 'asc');
    }

    public function chat()
    {
        return $this->hasMany(TicketChat::class, 'ticket_id')->orderBy('created_at', 'asc');
    }

    public function escalatedTo()
    {
        return $this->belongsTo(User::class, 'escalated_to_user_id');
    }

    public function escalatedFrom()
    {
        return $this->belongsTo(User::class, 'escalated_from_user_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'name');
    }

    public function assignedDevelopers()
    {
        return $this->belongsToMany(User::class, 'ticket_assignments', 'ticket_id', 'user_id')
            ->withPivot('assigned_date', 'assigned_by', 'notes', 'tat_value', 'tat_unit')
            ->withTimestamps()
            ->orderBy('ticket_assignments.created_at', 'asc');
    }

    public function ticketStatus()
    {
        return $this->belongsTo(TicketStatus::class, 'complaint_workflow', 'workflow_value');
    }

    public function ticketPriority()
    {
        return $this->belongsTo(TicketPriority::class, 'priority', 'value');
    }

    public function getPriorityBadgeAttribute()
    {
        if ($this->ticketPriority && $this->ticketPriority->color) {
            return $this->ticketPriority->color;
        }

        return match ($this->priority) {
            'high' => 'bg-danger',
            'medium' => 'bg-warning text-dark',
            'low' => 'bg-success',
            default => 'bg-secondary'
        };
    }

    public function getWorkflowNameAttribute()
    {
        if ($this->ticketStatus) {
            return $this->ticketStatus->name;
        }

        $workflows = getComplaintWorkflow();
        return $workflows[$this->complaint_workflow] ?? 'Unknown';
    }

    public function scopeByWorkflow($query, $workflow)
    {
        return $query->where('complaint_workflow', $workflow);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeActive($query)
    {
        return $query->where('is_closed', false);
    }

    public function scopeForDeveloper($query)
    {
        return $query->where('is_closed', false);
    }

    public function scopeForUser($query, $userId)
    {
        $user = User::find($userId);
        $clientId = $user ? $user->client_id : null;
        $userName = $user ? $user->name : null;

        return $query->where(function ($q) use ($userId, $clientId, $userName) {
            $q->where('created_by', $userId);
            if ($userName) {
                $q->orWhere('created_by', $userName);
            }
            if ($clientId) {
                $q->orWhere('client_id', $clientId);
            }
        });
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('ticket_category_id', $categoryId);
    }

    public function scopeFifo($query)
    {
        return $query->orderBy('created_at', 'asc')
            ->orderBy('priority', 'desc');
    }

    public function getTicketNumberAttribute()
    {
        return $this->ticket_no ?: $this->complaint_id;
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->is_closed) {
            return 'bg-success';
        }

        if ($this->rejected) {
            return 'bg-danger';
        }

        if ($this->ticketStatus && $this->ticketStatus->color) {
            return $this->ticketStatus->color;
        }

        return match ($this->complaint_workflow) {
            1 => 'bg-info',
            2 => 'bg-warning',
            3 => 'bg-primary',
            4 => 'bg-secondary',
            default => 'bg-secondary'
        };
    }

    public function getTatDisplayAttribute()
    {
        if ($this->assignedDevelopers && $this->assignedDevelopers->count() > 0) {
            $firstDev = $this->assignedDevelopers->first();
            if (isset($firstDev->pivot->tat_value) && isset($firstDev->pivot->tat_unit)) {
                return $firstDev->pivot->tat_value . ' ' . $firstDev->pivot->tat_unit;
            }
        }
        return 'N/A';
    }
}

<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\User;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Complaintsresolutions extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'complaintsresolutions';

    protected $fillable = [
        'car_no',
        'action_taken',
        'findings',
        'root_cause_analysis',
        'corrective_action_taken',
        'preventive_action',
        'officer_responsible',
        'resolved_by_user_id',
        'registered_by',
        'reject',
        'approve',
        'approved_by',
        'complaint_id',
        'workflow_stage',
        'edited_by',
        'request_approve',
        'cause_of_complaint',
        'action_taken_date',
        'action_taken_by',
        'corrective_action_date',
        'corrective_action_by',
        'car_required',
        'ncr_required',
        'client_remarks',
        'send_to_customer',
        'issued_to',
        'issued_by',
        'date_issued',
        'proposed_close_out_date',
        'ref_clause',
        'car_type',
        'risk_level',
        'capa_approved_by',
        'capa_approved_at',
        'capa_identified_by',
        'capa_identified_date',
    ];

    protected function casts(): array
    {
        return [
            'reject' => 'boolean',
            'approve' => 'boolean',
            'car_required' => 'boolean',
            'ncr_required' => 'boolean',
            'send_to_customer' => 'boolean',
            'action_taken_date' => 'date',
            'corrective_action_date' => 'date',
            'date_issued' => 'date',
            'proposed_close_out_date' => 'date',
            'capa_identified_date' => 'date',
            'capa_approved_at' => 'datetime',
        ];
    }

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function resolvedByUser()
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    /**
     * Alias for resolvedByUser(); used by closure report and ComplaintClosureMail.
     */
    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    public function capaApprovedBy()
    {
        return $this->belongsTo(User::class, 'capa_approved_by');
    }
}

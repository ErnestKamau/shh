<?php

namespace App\Models\CRM;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class Complaintsresolutions extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasUuids;

    protected $table = 'complaintsresolutions';

    protected $fillable = [
        'car_no',
        'action_taken',
        'findings',
        'root_cause_analysis',
        'root_cause_by',
        'root_cause_date',
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
        'internal_remarks',
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
            'root_cause_date' => 'date',
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

    public function actionTakenBy()
    {
        return $this->belongsTo(User::class, 'action_taken_by');
    }

    public function correctiveActionBy()
    {
        return $this->belongsTo(User::class, 'corrective_action_by');
    }

    public static function isValidUuid($value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value) === 1;
    }

    public function getRootCauseByNamesAttribute(): string
    {
        $val = $this->root_cause_by;
        if (empty($val)) {
            return 'System';
        }

        $parts = array_map('trim', explode(',', $val));
        $names = [];

        foreach ($parts as $part) {
            if (self::isValidUuid($part)) {
                $user = \App\User::find($part);
                if ($user) {
                    $names[] = $user->name;
                }
            } else {
                $names[] = $part;
            }
        }

        return !empty($names) ? implode(', ', $names) : 'System';
    }

    public function getActionTakenByNamesAttribute(): string
    {
        $val = $this->action_taken_by;
        if (empty($val)) {
            return 'System';
        }

        $parts = array_map('trim', explode(',', $val));
        $names = [];

        foreach ($parts as $part) {
            if (self::isValidUuid($part)) {
                $user = \App\User::find($part);
                if ($user) {
                    $names[] = $user->name;
                }
            } else {
                $names[] = $part;
            }
        }

        return !empty($names) ? implode(', ', $names) : 'System';
    }

    public function getCorrectiveActionByNamesAttribute(): string
    {
        $val = $this->corrective_action_by;
        if (empty($val)) {
            return 'System';
        }

        $parts = array_map('trim', explode(',', $val));
        $names = [];

        foreach ($parts as $part) {
            if (self::isValidUuid($part)) {
                $user = \App\User::find($part);
                if ($user) {
                    $names[] = $user->name;
                }
            } else {
                $names[] = $part;
            }
        }

        return !empty($names) ? implode(', ', $names) : 'System';
    }
}

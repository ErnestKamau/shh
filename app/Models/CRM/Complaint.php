<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Complaint extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasUuids;
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
        'feedback_id',
        'is_feedback_related',
        'origin',
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

    public function getContactNameAttribute($value)
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value)) {
            $contact = CustomerContact::find($value);
            if ($contact) {
                return trim($contact->first_name . ' ' . ($contact->middle_name ? $contact->middle_name . ' ' : '') . $contact->last_name);
            }
        }
        return $value;
    }

    public function client()
    {
        return $this->belongsTo(CRMCustomer::class, 'client_id');
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

    public function feedback()
    {
        return $this->belongsTo(CustomerFeedback::class, 'feedback_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Closure Status Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the complaint has an interim approval recorded in chain of custody.
     */
    public function getIsApprovedForClosureAttribute(): bool
    {
        return $this->chainOfCustody()
            ->where('action', 'like', '%Approval Recorded%')
            ->exists();
    }

    /**
     * Check if closure remarks have been added to the resolution.
     */
    public function getHasClosureRemarksAttribute(): bool
    {
        $res = $this->resolutions()->first();
        return !empty(trim(strip_tags((string)($res->internal_remarks ?? ''))));
    }

    /**
     * Determine if the complaint can move to final closure.
     */
    public function getCanFinallyCloseAttribute(): bool
    {
        return $this->is_approved_for_closure && $this->has_closure_remarks;
    }
}

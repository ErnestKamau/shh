<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Complaint extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

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
}

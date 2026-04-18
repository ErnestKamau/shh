<?php

namespace App\Models;

use App\SampleHeader;
use Illuminate\Database\Eloquent\Model;

class SampleSubmissionRequest extends Model
{
    protected $fillable = [
        'sample_header_id',
        'crm_customer_id',
        'crm_contact_id',
        'submitting_agency',
        'submitting_officer_full_name',
        'submitting_officer_title',
        'physical_address',
        'region',
        'district',
        'working_station',
        'office_telephone_no',
        'mobile_telephone_no',
        'fax',
        'email',
        'case_no',
        'offence',
        'date_of_seizure',
        'seizure_region',
        'seizure_district',
        'seizure_ward',
        'seizure_village_street',
        'submitted_by_full_name',
        'submitted_by_title',
        'submitted_by_signature',
        'submitted_by_date',
        'submitted_by_time',
        'received_by_full_name',
        'received_by_title',
        'received_by_signature',
        'received_by_date',
        'received_by_time',
        'status',
    ];

    protected $casts = [
        'date_of_seizure' => 'date',
        'submitted_by_date' => 'date',
        'received_by_date' => 'date',
    ];

    public function batch()
    {
        return $this->belongsTo(SampleHeader::class, 'sample_header_id');
    }

    public function suspects()
    {
        return $this->hasMany(SampleSubmissionRequestSuspect::class)
            ->orderBy('serial_number')
            ->orderBy('id');
    }

    public function exhibits()
    {
        return $this->hasMany(SampleSubmissionRequestExhibit::class)
            ->orderBy('serial_number')
            ->orderBy('id');
    }

    public function requestedAnalyses()
    {
        return $this->hasMany(SampleSubmissionRequestRequestedAnalysis::class)
            ->orderBy('id');
    }
}
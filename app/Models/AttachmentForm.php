<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttachmentForm extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function templateTypes()
    {
        return $this->belongsToMany(
            SubmissionFormTemplateType::class,
            'attachment_form_template_type',
            'attachment_form_id',
            'submission_form_template_type_id'
        );
    }

    public function customers()
    {
        return $this->belongsToMany(
            \App\Models\CRM\CRMCustomer::class,
            'attachment_form_customers',
            'attachment_form_id',
            'crm_customer_id'
        );
    }

    public function sampleTypes()
    {
        return $this->belongsToMany(
            \App\SampleType::class,
            'attachment_form_sample_types',
            'attachment_form_id',
            'sample_type_id'
        );
    }
}

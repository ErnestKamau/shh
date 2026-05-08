<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionFormTemplateType extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function submissionForms()
    {
        return $this->hasMany(SubmissionForm::class, 'template_form_type_id');
    }

    public function attachmentForms()
    {
        return $this->belongsToMany(
            AttachmentForm::class,
            'attachment_form_template_type',
            'submission_form_template_type_id',
            'attachment_form_id'
        );
    }
}

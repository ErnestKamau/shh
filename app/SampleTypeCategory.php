<?php

namespace App;

use App\Models\SubmissionForm;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SampleTypeCategory extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = "sample_type_categories";

    protected $fillable = [
        'sample_type_category',
        'active',
        'zoho_id',
    ];

    public function submissionForms()
    {
        return $this->belongsToMany(
            SubmissionForm::class,
            'submission_form_sample_type_categories',
            'sample_type_category_id',
            'submission_form_id'
        );
    }
}

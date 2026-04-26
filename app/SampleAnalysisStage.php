<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;
use App\User;

class SampleAnalysisStage extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

  use \OwenIt\Auditing\Auditable;
  protected $table = 'sample_analysis_stages';
  protected $appends = ['namecode'];

  protected $fillable = [
    'name',
    'code',
    'active',
    'company_id',
    'sample_workflow',
    'level',
    'section_head_id',
    'lab_id',
    'is_system',
    'is_sample_stage',
    'title',
  ];

  public function sample_analysis_stage()
  {
    return $this->hasMany('App\SampleToSampleAnalysisStage');
  }
  public function getSectionHead()
  {
    return User::find($this->section_head_id);
  }
  public function getLabDetails()
  {
    return Lab::find($this->lab_id);
  }
  public function getNameCodeAttribute()
  {
    return $this->code . '-' . $this->name;
  }

  /**
   * Get the submission forms for this sample analysis stage
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function submissionForms()
  {
    return $this->belongsToMany(Models\SubmissionForm::class, 'submission_form_sample_analysis_stage');
  }

  /**
   * Get the report format configurations for this lab section.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function reportConfigurations()
  {
    return $this->hasMany(Models\LabSectionReportConfig::class, 'sample_analysis_stage_id');
  }
}

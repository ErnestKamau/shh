<?php

namespace App;

use App\Concerns\DefaultsActiveOnCreate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;
use App\SampleTypeCategory;

class SampleType extends Model implements Auditable
{
    use DefaultsActiveOnCreate;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'name',
		'code', 
		'description',
		'company_id',
		'active',
		'is_results_attachable',
		'sample_type_category',
		'rating_header_id',
		'report_template_id',
		'report_format_id',
		'default_product_id',
    'disposal_count',
    'exhibit_returned_on_reception'
	];
	
	protected $casts = [
		'active' => 'boolean',
		'is_results_attachable' => 'boolean',
    'exhibit_returned_on_reception' => 'boolean',
	];

  public $with =['analysis_types', 'sample_condition'];
  public function analysis_types(){
    return $this->hasMany('App\AnalysisType')->orderBy('level','asc');
  }
  public function sample_condition(){
    return $this->hasMany('App\SampleCondition');
  }
  public function sample_analysis_stage(){
    return $this->hasMany('App\SampleToSampleAnalysisStage');
  }
  public function sampleTypeCategory()
  {
      return $this->belongsTo(SampleTypeCategory::class, 'sample_type_category');
  }

  /**
   * Legacy helper: category name string (not an Eloquent relation).
   */
  public function category(): string
  {
      return (string) ($this->sampleTypeCategory?->sample_type_category ?? '');
  }

  public function ratingHeader(){
    return $this->belongsTo('App\Models\RatingHeader');
  }

  public function reportFormat(){
    return $this->belongsTo('App\ReportFormat');
  }

  public function defaultProduct(){
    return $this->belongsTo('App\Models\CRM\CompanyProduct', 'default_product_id');
  }

  /**
   * Get the sample points assigned to this sample type
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function samplePoints()
  {
    return $this->belongsToMany(
      \App\Models\SamplePoint::class,
      'sampletype_sample_point_relation',
      'sample_type_id',
      'sample_point_id'
    )->withTimestamps();
  }

  /**
   * Get the lab sections (Sample Analysis Stages) for this sample type
   */
  public function sampleAnalysisStages()
  {
      return $this->belongsToMany(
          \App\SampleAnalysisStage::class,
          'sample_to_sample_analysis_stages',
          'sample_type_id',
          'sample_analysis_stage_id'
      )->withTimestamps();
  }
}

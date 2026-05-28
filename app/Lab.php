<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Lab extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
  
  protected $fillable = [
    'code',
    'name',
    'address',
    'location',
    'fax',
    'email',
    'website',
    'company_id',
    'is_external',
    'phone1',
    'phone2',
    'phone3',
    'active',
    'start_sample_no',
    'directorate_id',
    'zone_id',
    'manager_id',
    'section_head_user_id',
    'analyst_ids',
  ];

  protected $casts = [
    'active' => 'boolean',
    'is_external' => 'boolean',
    'analyst_ids' => 'array',
  ];
  
  public function company(){
    return $this->belongsTo('App\Company');
  }

  public function directorate(): BelongsTo
  {
    return $this->belongsTo('App\Directorate');
  }

  public function zone(): BelongsTo
  {
    return $this->belongsTo('App\Zone');
  }

  public function manager(): BelongsTo
  {
    return $this->belongsTo('App\User', 'manager_id');
  }

  public function sectionHeadUser(): BelongsTo
  {
    return $this->belongsTo('App\\User', 'section_head_user_id');
  }

  public function analysis_types(){
    return $this->hasMany('App\AnalysisType');
  }

  public function labSections(): HasMany
  {
    return $this->hasMany(LabSection::class);
  }

  public function decontaminationAreas(): HasMany
  {
    return $this->hasMany(LabDecontaminationArea::class);
  }
}

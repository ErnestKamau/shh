<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class AnalysisType extends Model implements Auditable
{
  use HasUuids;
  use \OwenIt\Auditing\Auditable;

  protected $keyType = 'string';
  public $incrementing = false;
  protected $fillable = [
    'name',
    'code',
    'description',
    'sample_type_id',
    'lab_id',
    'company_id',
    'active',
    'has_no_result',
    'level',
    'reporting_time',
    'short_name',
    'lab_section_id',
    'brand_id',
    'is_pesticide',
    'zoho_id',
    'product_type',
    'result_expo',
    'include_hygiene_score',
    'include_sanitizer_efficiency',
    'invoicable_item_id',
    'procedure_worksheet_id',
    'grouped_worksheet_holder_id',
    'hybrid_worksheet_id',
  ];

  protected $casts = [
    'active' => 'boolean',
    'has_no_result' => 'boolean',
    'include_hygiene_score' => 'boolean',
    'include_sanitizer_efficiency' => 'boolean'
  ];
  protected $appends = ['labsectionname'];
  public function lab()
  {
    return $this->belongsTo('App\Lab');
  }

  public function labs()
  {
    return $this->belongsToMany(
      Lab::class,
      'analysis_type_lab_relation',
      'analysis_type_id',
      'lab_id'
    )->withTimestamps();
  }

  public function sample_type()
  {
    return $this->belongsTo('App\SampleType');
  }

  public function analysis_elements()
  {
    return $this->hasMany('App\AnalysisElements')->with(['mmethod', 'ltmethod', 'analyte', 'equipment', 'operator'])->orderBy('level', 'asc');
  }

  public function guides()
  {
    return $this->hasMany('App\AnalysisGuide');
  }

  public function active_analysis_elements()
  {
    $active = 1;
    return AnalysisElements::join('analytes as a', 'a.id', '=', 'analysis_elements.analyte_id')
      ->where('analysis_elements.active', $active)->where('analysis_type_id', $this->id)
      ->selectRaw('analysis_elements.*, RTRIM(a.code) as analyte_code')->get();
  }
  public function getLabSectionNameAttribute()
  {
    if (empty($this->lab_section_id) || !\Illuminate\Support\Str::isUuid($this->lab_section_id)) {
      return '';
    }
    return SampleAnalysisStage::find($this->lab_section_id)->name ?? '';
  }
  public function zohoitem()
  {
    return $this->belongsTo(InventorySubCategories::class, 'zoho_id');
  }

  /**
   * Get the invoicable items mapped to this analysis type.
   */
  public function invoicableItems()
  {
    return $this->belongsToMany(
      InvoicableItem::class,
      'analysis_type_invoicable_item',
      'analysis_type_id',
      'invoicable_item_id'
    )->withTimestamps();
  }

  /**
   * Get the default (first) invoicable item for this analysis type.
   */
  public function defaultInvoicableItem()
  {
    return $this->belongsTo(InvoicableItem::class, 'invoicable_item_id');
  }

  public function procedureWorksheet()
  {
      return $this->belongsTo(\App\Models\Procedures\ProcedureWorksheet::class, 'procedure_worksheet_id');
  }

  public function groupedWorksheetHolder()
  {
      return $this->belongsTo(\App\Models\GroupedWorksheets\GroupedWorksheetHolder::class, 'grouped_worksheet_holder_id');
  }

  public function hybridWorksheet()
  {
      return $this->belongsTo(\App\Models\HybridWorksheets\HybridWorksheet::class, 'hybrid_worksheet_id');
  }

}

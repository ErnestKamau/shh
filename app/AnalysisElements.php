<?php

namespace App;

use App\Concerns\DefaultsActiveOnCreate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;
use App\Analyte;

class AnalysisElements extends Model implements Auditable
{
	use DefaultsActiveOnCreate;
	use \OwenIt\Auditing\Auditable;

	public $incrementing = false;
	protected $keyType = 'string';

	protected static function boot(): void
	{
		parent::boot();

		static::creating(function ($model) {
			if (empty($model->{$model->getKeyName()})) {
				$model->{$model->getKeyName()} = (string) Str::uuid();
			}
		});
	}

  protected $appends = ['parametername'];
  protected $fillable = [
    'lab_section_id',
    'analysis_type_id',
    'analyte_id', 
    'method', 
    'equipment_id',
    'operator_id',
    'reporting_unit', 
    'decimal_places',
    'significant_figures',
    'lod',
    'measurement_uncertainty',
    'hod',
    'level',
    'active',
    'non_detectable',
    'non_accredited',
    'sub_contracted',
    'show_on_report',
    'is_pesticide',
    'ltm_method_id',
    'reporting_time', 
    'recommend_remedies', 
    'remedy_header_id', 
    'remark_is_manual', 
    'result_is_calculated', 
    'formular_id',
    'has_method_sequence',
    'method_sequence_id',
    'stage_header_id',
    'procedure_worksheet_id',
    'log_entry_worksheet_id',
  ];
  
  protected $casts = [
    'operator_id' => 'string',
    'formular_id' => 'string',
    'remedy_header_id' => 'string',
    'method_sequence_id' => 'string',
    'stage_header_id' => 'string',
    'lod' => 'float',
    'measurement_uncertainty' => 'float',
    'hod' => 'float',
    'level' => 'integer',
    'recommend_remedies' => 'boolean',
    'result_is_calculated' => 'boolean',
    'has_method_sequence' => 'boolean',
    'sub_contracted' => 'boolean',
  ];

  public function getParameterNameAttribute(){
    return Analyte::find($this->analyte_id)->name ?? '';
  }

  public function analysis_type(){
    return $this->belongsTo('App\AnalysisType');
	}

  public function analyte(){
    return $this->belongsTo('App\Analyte');
	}

  public function method(){
    return AnalysisMethod::find($this->method);
	}
  public function mmethod(){
    return $this->belongsTo(AnalysisMethod::class,'method');
  }
  public function ltmethod(){
    return $this->belongsTo(AnalysisMethod::class,'ltm_method_id');
	}

  public function equipment(){
    return $this->belongsTo('App\Models\Equipments\Equipment');
	}

  public function operator(){
    return $this->belongsTo('App\User', 'operator_id')->withDefault();
  }

  public function remedyHeader(){
    return $this->belongsTo('App\Models\RemedyHeader');
  }

  public function reportingUnit(){
    return $this->belongsTo('App\ReportingUnit', 'reporting_unit', 'name');
  }

  public function methodSequence(){
    return $this->belongsTo('App\Models\MethodSequences\MethodSequence', 'method_sequence_id');
  }

  public function stageHeader()
  {
      return $this->belongsTo(\App\Models\StageHeader::class, 'stage_header_id');
  }

  public function formular(){
    return $this->belongsTo('App\Models\Formulars\Formula', 'formular_id');
  }

  public function procedureWorksheet()
  {
      return $this->belongsTo(\App\Models\Procedures\ProcedureWorksheet::class, 'procedure_worksheet_id');
  }

  public function logEntryWorksheet()
  {
      return $this->belongsTo(\App\Models\LogEntryWorksheets\LogEntryWorksheet::class, 'log_entry_worksheet_id');
  }
}

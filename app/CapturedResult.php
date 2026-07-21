<?php

namespace App;

use App\Models\System\SystemConfiguration;
use App\Observers\CapturedObserver;
use App\BatchAttachment;
use App\Concerns\HasVarcharUuidRelationships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;


// [ObservedBy([CapturedObserver::class])];
class CapturedResult extends Model implements Auditable
{
	use \Illuminate\Database\Eloquent\Concerns\HasUuids;
	use HasVarcharUuidRelationships;
	protected $keyType = 'string';
	public $incrementing = false;

    protected $casts = [
        'analyte_code' => \App\Casts\SafeEncrypted::class,
        'result' => \App\Casts\SafeEncrypted::class,
        'remark' => \App\Casts\SafeEncrypted::class,
        'main_value' => \App\Casts\SafeEncrypted::class,
        'secondary_value' => \App\Casts\SafeEncrypted::class,
        'sec_remark' => \App\Casts\SafeEncrypted::class,
        'third_remark' => \App\Casts\SafeEncrypted::class,
        'scienctific_result' => \App\Casts\SafeEncrypted::class,
        'superscript_number' => \App\Casts\SafeEncrypted::class,
        'superscript_negative' => \App\Casts\SafeEncrypted::class,
        'supercsript_base' => \App\Casts\SafeEncrypted::class,
        'operator_id' => 'string',
        'method_id' => 'string',
        'equipment_ids' => 'array',
        'main_standard_id' => 'string',
        'secondary_standard_id' => 'string',
        'lab_section_id' => 'string',
        'third_standard_id' => 'string',
        'ltm_method_id' => 'string',
        'formular_id' => 'string',
        'method_sequence_id' => 'string',
        'procedure_worksheet_id' => 'string',
        'grouped_worksheet_holder_id' => 'string',
        'hybrid_worksheet_id' => 'string',
        'has_procedure_worksheet' => 'boolean',
        'has_grouped_worksheet' => 'boolean',
        'has_hybrid_worksheet' => 'boolean',
        'log_entry_worksheet_id' => 'string',
        'has_log_entry_worksheet' => 'boolean',
    ];
	use \OwenIt\Auditing\Auditable;
	protected $appends = ['repeatsampleresult', 'isitalic'];
	protected $guarded = ['id'];
	public function getRepeatSampleResultAttribute()
	{
		if ($this->repeat_captured_id > 0) {
			$captured = CapturedResult::find($this->repeat_captured_id);
			$percentage_config = SystemConfiguration::where('key', 'qc_percentage_config')->first();
			$perc_value = intval($percentage_config->value) / 100 * intval($captured->result);
			$lower_limit = intval($captured->result) - intval($perc_value);
			$upper_limit = intval($captured->result) + intval($perc_value);
			return $lower_limit . ' - ' . $upper_limit;

		}
		return '';
	}

	public function sample()
	{
		return $this->belongsTo('App\SampleDetails', 'sample_detail_id');
	}

	public function sampleHeader()
	{
		return $this->belongsTo(SampleHeader::class, 'sample_header_id');
	}

	public function analysis_type()
	{
		return $this->belongsTo('App\AnalysisType');
	}

	/**
	 * Guard legacy mixed-ID data by restricting analyte_id to UUID-formatted values.
	 */
	public function scopeWhereValidUuidAnalyteId(Builder $query): Builder
	{
		return $query
			->whereNotNull('analyte_id')
			->whereRaw("analyte_id::text ~* '^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$'");
	}

	public function my_analyte()
	{
		return $this->uuidBelongsTo(Analyte::class, 'analyte_id');
	}

	public function analyte()
	{
		return Analyte::where('code', $this->id)->first();
	}

	public function defacto_analyst_with()
	{
		return $this->uuidBelongsTo(User::class, 'operator_id');
	}

	public function defacto_analyst()
	{
		return User::where('id', $this->operator_id)->where('active', 1)->first() ?? AnalysisElements::join('users as u', 'u.id', '=', 'operator_id')
			->where('analysis_type_id', $this->analysis_type_id)
			->where('u.active', 1)
			->selectRaw('u.*')
			->where('analyte_id', $this->analyte_id)->first();
	}

	public function equipment_with()
	{
		return $this->belongsTo(Models\Equipments\Equipment::class, 'equipment_id');
	}

	public function equipment()
	{
		return Models\Equipments\Equipment::where('id', $this->equipment_id)->first();
	}

	public function analysisMethod()
	{
		return $this->belongsTo(AnalysisMethod::class, 'method_id');
	}

	public function method()
	{
		return $this->analysisMethod()->first();
	}

	public function ltmethod()
	{
		return $this->belongsTo(AnalysisMethod::class, 'ltm_method_id');
	}

	public function operators()
	{
		$equipment = Models\Equipments\Equipment::where('id', $this->equipment_id)->get();
		$operators = array();
		foreach ($equipment as $e) {
			$ops = $e->operators();
			foreach ($ops as $o) {
				$user = User::where('id', $o->user_id)->where('active', 1)->first();
				$operators[] = $user;
			}
		}

		return $operators;
	}
	public function getIsItalicAttribute()
	{
		return Analyte::find($this->analyte_id)->is_italic;
	}

	public function operator()
	{
		return $this->uuidBelongsTo(User::class, 'operator_id');
	}

	public function analysisElement()
	{
		return $this->belongsTo(AnalysisElements::class, 'analysis_element_id');
	}

	public function formular()
	{
		return $this->belongsTo(\App\Models\Formulars\Formula::class, 'formular_id');
	}

	public function methodSequence()
	{
		return $this->belongsTo(\App\Models\MethodSequences\MethodSequence::class, 'method_sequence_id');
	}

	public function stageHeader()
	{
		return $this->belongsTo(\App\Models\StageHeader::class, 'stage_header_id');
	}

	public function run()
	{
		return $this->belongsTo(\App\Models\StageHeaderRun::class, 'run_id');
	}

	public function testStagesTracks()
	{
		return $this->hasMany(\App\Models\SampleCapturedTestStagesTrack::class, 'captured_result_id');
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

	public function logEntryWorksheet()
	{
		return $this->belongsTo(\App\Models\LogEntryWorksheets\LogEntryWorksheet::class, 'log_entry_worksheet_id');
	}
	
    public function batchAttachments()
    {
        return $this->belongsToMany(
            BatchAttachment::class,
            'analysis_element_attachments',
            'analysis_element_id',
            'batch_attachment_id',
            'analysis_element_id',
            'id'
        );
    }

    public function batchAttachment()
    {
        return $this->belongsTo(BatchAttachment::class, 'batch_attachment_id');
    }

	public function user()
	{
		return $this->uuidBelongsTo(User::class, 'user_id');
	}

	public function assignAnalyst(?string $userId): void
	{
		if ($userId) {
			$this->user_id = $userId;
		}
	}

	public function assignOperator(?string $userId): void
	{
		if ($userId) {
			$this->operator_id = $userId;
		}
	}

	public function applyAnalysisElementDefaults(): void
	{
		$element = $this->resolveAnalysisElement();

		if (! $this->lab_section_id && $element?->lab_section_id) {
			$this->lab_section_id = (string) $element->lab_section_id;
		}

		if (!$this->reporting_unit_id) {
			$this->reporting_unit_id = resolveReportingUnitIdFromAnalyte(
				$this->analysis_type_id,
				$this->analyte_id,
				$element?->reporting_unit
			);
		}

		if (!$this->method_id && $element?->method) {
			$this->method_id = $element->method;
		}

		if (!$this->equipment_id && $element?->equipment_id) {
			$this->equipment_id = $element->equipment_id;
		}

		$equipmentIds = is_array($this->equipment_ids) ? array_values(array_filter($this->equipment_ids)) : [];
		if ($equipmentIds === [] && $this->equipment_id) {
			$this->equipment_ids = [(string) $this->equipment_id];
		}
	}

	/**
	 * Link analysis_element_id from the matching analysis_elements row when missing.
	 */
	public function ensureAnalysisElementLinked(): void
	{
		if ($this->analysis_element_id) {
			return;
		}

		$element = $this->resolveAnalysisElement();
		if ($element) {
			$this->analysis_element_id = $element->id;
		}
	}

	/**
	 * Resolve the matching AnalysisElements row via FK or (analysis_type_id, analyte_id).
	 */
	public function resolveAnalysisElement(): ?AnalysisElements
	{
		if ($this->relationLoaded('analysisElement') && $this->analysisElement) {
			return $this->analysisElement;
		}

		if ($this->analysis_element_id) {
			$element = $this->analysisElement;
			if ($element) {
				return $element;
			}
		}

		if (!$this->analysis_type_id || !$this->analyte_id) {
			return null;
		}

		$element = AnalysisElements::query()
			->where('analysis_type_id', $this->analysis_type_id)
			->where('analyte_id', $this->analyte_id)
			->where('active', 1)
			->first();

		if ($element) {
			$this->setRelation('analysisElement', $element);
		}

		return $element;
	}

	/**
	 * Reporting unit name for processing: prefers the per-instance captured unit,
	 * falls back to the analysis element's reporting_unit string.
	 */
	public function effectiveReportingUnitName(): ?string
	{
		if ($this->reporting_unit_id) {
			$label = resolveReportingUnitLabel((string) $this->reporting_unit_id);
			if ($label !== '-' && $label !== '') {
				return $label;
			}
		}

		$element = $this->resolveAnalysisElement();

		return $element?->reporting_unit ?: null;
	}

	public function analystIdForTat(): ?string
	{
		return $this->user_id;
	}

	/**
	 * Whether this captured result still expects a Result Report attachment
	 * to be uploaded and linked (placeholder result, not a substantive value).
	 */
	public function requiresLinkedBatchAttachment(): bool
	{
		if (! $this->has_procedure_worksheet) {
			return false;
		}

		if ($this->batch_attachment_id) {
			return false;
		}

		$resultText = strtolower(trim((string) ($this->result ?? '')));

		if ($resultText === 'as attached') {
			return false;
		}

		return $resultText === ''
			|| in_array($resultText, ['no attachment', 'has attachment'], true);
	}

}

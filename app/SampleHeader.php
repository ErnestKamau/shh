<?php

namespace App;

use App\Models\CRM\CRMCustomer;
use App\Models\QcModule\Configurations\QcTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;
use App\InvoiceDetails;
use App\BatchLabSectionApprover;
use App\Models\CRM\CRMCompanyUnit;

class SampleHeader extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $casts = [
        'crm_unit_name' => 'encrypted',
        'reference_number' => 'encrypted',
        'method_deviation_reason' => 'encrypted',
        'document_number' => 'encrypted',
        'description' => 'encrypted',
        'importer_address' => 'encrypted',
        'reason_for_submission' => 'encrypted',
        'how_sample_was_obtained' => 'encrypted',
        'sample_appearance_description' => 'encrypted',
        'net_quantity_and_unit_of_quantity' => 'encrypted',
        'use_of_goods' => 'encrypted',
        'declared_amount' => 'encrypted',
        'where_sample_was_obtained' => 'encrypted',
        'radio_active_levels' => 'encrypted',
        'ammendment_number' => 'encrypted',
        'sampling_officer_name' => 'encrypted',
        'receiving_officer_name' => 'encrypted',
        'submit_by' => 'encrypted',
        'batch_report_url' => 'encrypted',
        'batch_instructions' => 'encrypted',
        'condition_quality_sample' => 'encrypted',
        'declaration_customer_signature' => 'encrypted',
        'invoice_amount' => 'encrypted',
        'cluster_amount' => 'encrypted',
        'cluster_balance' => 'encrypted',
        'cluster_amount_paid' => 'encrypted',
        'case_id' => 'encrypted',
        'batch_report_online_url' => 'encrypted',
        'schedule_customer_email' => 'encrypted',
        'receiving_officer' => 'string',
        'sampling_officer' => 'string',
        'specialist_analyst_id' => 'string',
        'verify_user_id' => 'string',
        'approve_user_id' => 'string',
        'invoice_id' => 'string',
        'quote_id' => 'string',
        'crm_unit_id' => 'string',
        'qc_scheme_id' => 'string',
        'qc_type_id' => 'string',
        'sampling_method_id' => 'string',
        'crm_contact_id' => 'string'
    ];


	use \OwenIt\Auditing\Auditable;
	protected $guarded = ['id'];
	// public $with = ['get_target_date', 'client', 'samples', 'specialist_analyst', 'custody', 'comments'];
	protected $appends = ['unitname','repeatsampleidarr'];

	protected function getRepeatSampleIdArrAttribute(){
		return $this->repeat_sample_id != '' ? explode(',',$this->repeat_sample_id) : [];
	}
	
	public function samples()
	{
		return $this->hasMany('App\SampleDetails')->orderBy('id', 'asc');
	}
	public function custody()
	{
		return $this->hasMany('App\ChainOfCustody')->orderBy('created_at', 'desc');
	}

	public function batch_amendments()
	{
		return $this->hasMany('App\BatchAmmendment', 'batch_id')->orderBy('created_at', 'desc');
	}

	public function payment_details()
	{
		return \App\InvoicePaymentDetail::where('invoice_id', $this->invoice_id)
			->where('is_delete', 0)
			->orderBy('created_at', 'desc')
			->get();
	}

	public function approvers()
	{
		return $this->hasMany('App\BatchLabSectionApprover', 'batch_id')->orderBy('created_at', 'desc');
	}

	public function batch_attachments()
	{
		return $this->hasMany('App\BatchAttachment', 'batch_id')->orderBy('created_at', 'desc');
	}


	public function stagingDetails()
	{
		return $this->hasMany(\App\Models\SampleDetailStaging::class, 'sample_header_id');
	}

	public function all_samples()
	{
		return SampleDetails::leftJoin('inventory_sub_categories as isc', function ($join) {
			$parentType = "\App\SampleDetails";
			$join->on('isc.parent_id', '=', 'sample_details.id');
			$join->where('isc.parent', '=', $parentType);
		})->leftJoin('inventory_items as it', 'it.inventory_sub_category_id', '=', 'isc.id')
		->leftJoin('sample_analysis_dates as sad',function($join){
			$join->on('sad.sample_detail_id','=','sample_details.id');
			$join->where('sad.sample_header_id',$this->id);
		})
		->leftJoin('labs as lb','lb.id','=','sample_details.lab_id')
			->selectRaw('analysis_type_id,sample_details.barcode,sample_details.third_standard_id as third_standard,sample_details.lab_sub_no,sample_details.ammendment_number,sample_details.main_standard,sample_details.secondary_standard,comments,company_product_id,gps,header_body,sample_details.id,main_body,notes_body,photo_url,sample_code,sample_condition_id,sample_details.sample_header_id,sample_point_id, isc.unit_type, it.inventory_store_slot_id as slot_id, it.inventory_store_id as store_id, SUM(it.stock_in) as stock_in, SUM(it.stock_out) as stock_out, isc.material_type_id,sample_details.lab_id,lb.name as lab_name,lb.code as lab_code,sample_details.disposal_date,sad.analysis_dates,sad.start_analysis_date')
			->where('sample_details.sample_header_id', $this->id)->groupBy('isc.material_type_id', 'analysis_type_id','sample_details.lab_sub_no', 'sample_details.barcode', 'comments', 'company_product_id', 'gps', 'header_body', 'sample_details.id', 'main_body', 'photo_url', 'sample_code', 'sample_condition_id', 'sample_details.sample_header_id', 'sample_point_id', 'unit_type', 'inventory_store_slot_id', 'inventory_store_id', 'sample_details.ammendment_number', 'sample_details.main_standard', 'sample_details.secondary_standard')->orderBy('sample_details.id', 'asc')->get();
	}

	public function report_header_details()
	{
		$response = array();

		$sampleHeaderReport =  \App\ReportHeaderDetail::where('sample_header_id', $this->id)
			->where('model', "App\SampleHeader")->first();

		$sampleCRMCustomerHeaderReport =  \App\ReportHeaderDetail::where('model_id', $this->crm_customer_id)
			->where('model', "App\CRMCustomer")->first();

		return array(
			"header" => $sampleHeaderReport,
			"client_header" => $sampleCRMCustomerHeaderReport
		);
	}

	public function get_captured_tally()
	{
		$results = array(
			"captured" => 0,
			"not_captured" => 0,
			"items" => array(),
		);
		$not_captured = CapturedResult::join('equipment as e', 'e.id', '=', 'captured_results.equipment_id')
			->selectRaw('e.name as equipment, captured_results.analyte_code, captured_results.result, captured_results.sample_detail_code, captured_results.machine_update_date')
			->whereNull('captured_results.result')->where('sample_header_id', $this->id)->get();

		$captured = CapturedResult::join('equipment as e', 'e.id', '=', 'captured_results.equipment_id')
			->selectRaw('e.name as equipment, captured_results.analyte_code, captured_results.result, captured_results.sample_detail_code, captured_results.machine_update_date')
			->whereNotNull('captured_results.result')->where('sample_header_id', $this->id)->get();

		$results["captured"] = $captured->count();
		$results["not_captured"] = $not_captured->count();

		foreach ($captured as $cap) {
			if (!isset($results["items"][$cap->equipment])) {
				$results["items"][$cap->equipment] = array();
			}

			if (!isset($results["items"][$cap->equipment][$cap->sample_detail_code])) {
				$results["items"][$cap->equipment][$cap->sample_detail_code] = array();
			}

			$results["items"][$cap->equipment][$cap->sample_detail_code][$cap->analyte_code] = [$cap->result, $cap->machine_update_date];
		}
		foreach ($not_captured as $cap) {
			if (!isset($results["items"][$cap->equipment])) {
				$results["items"][$cap->equipment] = array();
			}

			if (!isset($results["items"][$cap->equipment][$cap->sample_detail_code])) {
				$results["items"][$cap->equipment][$cap->sample_detail_code] = array();
			}

			$results["items"][$cap->equipment][$cap->sample_detail_code][$cap->analyte_code] = [$cap->result, $cap->machine_update_date];
		}

		return $results;
	}

	public function get_captured()
	{
		$results = array(
			"captured" => 0,
			"not_captured" => 0,
			"items" => array(),
		);
		$not_captured = CapturedResult::
			selectRaw('captured_results.analyte_code, captured_results.result, captured_results.sample_detail_code')
			->whereNull('captured_results.result')->where('sample_header_id', $this->id)->get();

		$captured = CapturedResult::
			selectRaw(' captured_results.analyte_code, captured_results.result, captured_results.sample_detail_code')
			->whereNotNull('captured_results.result')->where('sample_header_id', $this->id)->get();

		$results["captured"] = $captured->count();
		$results["not_captured"] = $not_captured->count();

		foreach ($captured as $cap) {
			if (!isset($results["items"])) {
				$results["items"] = array();
			}

			if (!isset($results["items"][$cap->sample_detail_code])) {
				$results["items"][$cap->sample_detail_code] = array();
			}

			$results["items"][$cap->sample_detail_code][$cap->analyte_code] = [$cap->result];
		}
		foreach ($not_captured as $cap) {
			if (!isset($results["items"])) {
				$results["items"]= array();
			}

			if (!isset($results["items"][$cap->sample_detail_code])) {
				$results["items"][$cap->sample_detail_code] = array();
			}

			$results["items"][$cap->sample_detail_code][$cap->analyte_code] = [$cap->result];
		}

		return $results;
	}

	public function get_request_types()
	{
		return RequestType::whereIn('id', explode(",", $this->reason_for_submission))->get();
	}

	public function comments()
	{
		return $this->hasMany('App\BatchComment');
	}

	public function labs($str = false)
	{
		$samples = $this->samples;
		$labs = array();
		$labStr = array();
		foreach ($samples as $s) {
			$labs = array_merge($labs, $s->labs());
		}

		foreach ($labs as $lab) {
			$labStr[] = $lab[0] . " - " . $lab[1];
		}

		return $str ? array_unique($labStr) : array_unique($labs);
	}

	public function get_target_date(){
		return $this->hasOne(SampleDate::class)->where('name', 'Target Date');
	}

	public function get_date($type)
	{
		return SampleDate::where('sample_header_id', $this->id)->where('name', $type)->first();
	}

	public function set_date($type, $date, $once = false)
	{
		$existsDate = SampleDate::where('sample_header_id', $this->id)
			->where('name', $type)->where('date', $date)->first();

		if ($once && $existsDate) {
			return $existsDate;
		}

		$new_date = $existsDate ?? new SampleDate;
		$new_date->sample_header_id = $this->id;
		$new_date->name = $type;
		$new_date->date = $date;

		$new_date->save();
		if ($type == 'Processing Date') {
			$this->processing_date = $date;
		}
		if ($type == 'Approval Date') {
			$this->approval_date = $date;
		}
		$this->save();
		return $new_date;
	}

	public function sample_type()
	{
		return $this->belongsTo('App\SampleType');
	}

	public function stages($wkFlow = false)
	{
		$active = 1;
		$stages = \App\SampleToSampleAnalysisStage::join('sample_analysis_stages as sas', 'sas.id', '=', 'sample_to_sample_analysis_stages.sample_analysis_stage_id')
			->where('sample_to_sample_analysis_stages.sample_type_id', $this->sample_type_id)
			->where('sample_to_sample_analysis_stages.active', $active);

		if ($wkFlow) {
			$stages = $stages->where('sas.sample_workflow', $wkFlow);
		}

		$stages = $stages->selectRaw('sas.*, sample_to_sample_analysis_stages.sample_type_id')->orderBy('sas.level', 'asc')->get();

		return $stages;
	}

	public function client()
	{
		return $this->belongsTo('App\Models\CRM\CRMCustomer', 'crm_customer_id');
	}

	public function specialist_analyst()
	{
		return $this->belongsTo('App\User', 'specialist_analyst_id');
	}

	public function submissionFormInstance()
	{
		return $this->belongsTo('App\Models\SubmissionFormInstance', 'submission_form_instance_id');
	}

	public function sampleSubmissionRequest()
	{
		return $this->hasOne('App\Models\SampleSubmissionRequest', 'sample_header_id');
	}

	public function hasSubmissionForm(): bool
	{
		return $this->submission_form_instance_id !== null && $this->submission_form_instance_id > 0;
	}

	public function hasSubmissionFormAttachment(): bool
	{
		$submissionFormAttachmentTypeId = \App\Models\System\SystemConfiguration::where('key', 'attachment_type')
			->where('value', 'Submission Form')
			->value('id');
			
		if ($submissionFormAttachmentTypeId === null) {
            $submissionFormAttachmentTypeId = \App\Models\System\SystemConfiguration::where('key', 'attachment_type')->value('id');
        }

		return $this->batch_attachments()->where('attachment_type', $submissionFormAttachmentTypeId)->exists();
	}

	public function tracking_stage()
	{
		if ($this->sample_tracking_stage == 0) {
			return (object) ["name" => "N/A"];
		}
		$stage = \App\SampleAnalysisStage::find($this->sample_tracking_stage);
		return $stage ? $stage : (object) ["name" => "NOT SET"];
	}

	public function captured_results()
	{
		return $this->hasMany('App\CapturedResult')
			->join('analysis_elements', function ($join) {
				$join->on('analysis_elements.analyte_id', '=', 'captured_results.analyte_id');
				$join->on('analysis_elements.analysis_type_id', '=', 'captured_results.analysis_type_id');
			})
			->join('analysis_types', function ($join) {
				$join->on('analysis_types.id', '=', 'captured_results.analysis_type_id');
			})
			->selectRaw('captured_results.*,analysis_types.level as analysis_level,analysis_types.name as analysis_type_name,analysis_elements.level as analyte_level,analysis_elements.non_accredited as an_analyte_accredited,analysis_types.is_pesticide as pesticide')
			->orderBy('analysis_level','asc')
			->orderBy('analyte_level', 'asc');
	}
	public function captured_results_without_pesticide()
	{
		return $this->hasMany('App\CapturedResult')
			->join('analysis_elements', function ($join) {
				$join->on('analysis_elements.analyte_id', '=', 'captured_results.analyte_id');
				$join->on('analysis_elements.analysis_type_id', '=', 'captured_results.analysis_type_id');
			})
			->join('analysis_types', function ($join) {
				$join->on('analysis_types.id', '=', 'captured_results.analysis_type_id');
			})
			->where('analysis_types.is_pesticide',0)
			->selectRaw('captured_results.*,analysis_types.level as analysis_level,analysis_types.name as analysis_type_name,analysis_elements.level as analyte_level,analysis_elements.non_accredited as an_analyte_accredited,analysis_types.is_pesticide as pesticide')
			->orderBy('analysis_level','asc')
			->orderBy('analyte_level', 'asc');
	}
	public function captured_results_pesticide()
	{
		return $this->hasMany('App\CapturedResult')
			->join('analysis_elements', function ($join) {
				$join->on('analysis_elements.analyte_id', '=', 'captured_results.analyte_id');
				$join->on('analysis_elements.analysis_type_id', '=', 'captured_results.analysis_type_id');
			})
			->join('analysis_types', function ($join) {
				$join->on('analysis_types.id', '=', 'captured_results.analysis_type_id');
			})
			->where('analysis_types.is_pesticide',1)
			->selectRaw('captured_results.*,analysis_types.level as analysis_level,analysis_types.name as analysis_type_name,analysis_elements.level as analyte_level,analysis_elements.non_accredited as an_analyte_accredited,analysis_types.is_pesticide as pesticide')
			->orderBy('analysis_level','asc')
			->orderBy('analyte_level', 'asc');
	}

	public function processed_results()
	{
		return Result::join('captured_results as cr', 'cr.id', '=', 'results.captured_result_id')
			// ->join('equipment as e', 'e.id', 'cr.equipment_id')
			->join('users as u', 'u.id', '=', 'cr.operator_id')
			->selectRaw('results.sample_detail_code, results.analyte_code, results.result, results.reporting_symbol, results.unit_code, u.name as operator ')
			->where('results.sample_header_id', $this->id)->whereNotNull('results.result')->get();
	}
	public function get_invoice_total(){
		return InvoiceDetails::where('invoice_id',$this->invoice_id)->sum('total');
	}
	public function getVerificationApprovalStatus(){
		return BatchLabSectionApprover::where('batch_id',$this->id)->where('batch_status','Sample Verification')->whereIn('status',[2,0])->get()->count();
	}
	public function getApprovalStageStatus(){
		return BatchLabSectionApprover::where('batch_id',$this->id)->where('batch_status','Sample Approval')->whereIn('status',[2,0])->get()->count();
	}
	public function getContactPersonDetail(){
		$contact = getCrmCustomerContactById($this->crm_contact_id);
		return isset($contact->id) ? $contact->first_name.' '.$contact->middle_name.' '.$contact->last_name : '-';
	}
	public function getLabSectionsNames(){
		$tracking_stages_arr = explode(',',$this->lab_section_ids ?? '');
		return implode(',',SampleAnalysisStage::whereIn('id',$tracking_stages_arr)->pluck('name')->toArray()); 
	}
	public function getUnitNameAttribute(){
		if($this->crm_unit_id > 0){
			$unit = CRMCompanyUnit::find($this->crm_unit_id);
			return $unit ? $unit->name : 'NOT SET';
		}
		return $this->crm_unit_name ?? 'NOT SET';
	}
	public function invoice(){
		return $this->belongsTo(Invoice::class,'invoice_id');
	}
	public function customer(){
		return $this->belongsTo(CRMCustomer::class,'crm_customer_id');
	}
	public function receivingofficer(){
		return $this->belongsTo(User::class,'receiving_officer_name');
	}
	public function qctype(){
		return $this->belongsTo(QcTypes::class,'qc_type_id');
	}

	/**
	 * Check if sample codes need regeneration (smart code scenario)
	 * Returns true if any sample has code equal to batch_code (1:1 scenario)
	 */
	public function needsSampleCodeRegeneration()
	{
		// Check if any sample has code equal to batch_code (no suffix)
		return $this->samples()->where('sample_code', $this->batch_code)->exists();
	}
	
}

<?php

namespace App\Http\Controllers;

use App\PricelistCustomer;
use App\Pricelist;
use App\Invoice;
use App\InvoiceDetails;
use App\TaxRegime;
use App\PricelistItem;
use App\Lab;
use App\Result;
use App\Company;
use App\SampleType;
use App\SampleResults;
use App\SampleHeader;
use App\SampleDetails;
use App\CapturedResult;
use App\AnalysisElements;
use App\AnalysisMethod;
use App\AnalysisType;
use App\Analyte;
use App\SampleAnalysisStage;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CustomerContact;
use App\BatchComment;
use App\User;
use App\Country;
use App\StandardAnalytes;
use App\sampleAnalysisTypeRelation;
use App\SampleAnalysisTypeRelationView;
use App\InterLabLog;
use App\InterLabLogView;
use App\Models\CRM\SamplePoint;
use App\CapturedResultView;
use App\Models\Equipments\Equipment;
use App\UserRoleView;

use App\Http\Controllers\System\SystemNotifications;

use App\Http\Controllers\MailController as Mailer;
use App\JobDescription;
use App\Models\System\SystemConfiguration;
use App\ModulePreConfigs;
use App\Standards;
use App\StandardValue;
use App\BatchAmmendment;
use App\BatchAttachment;
use App\InventoryCategories;
use App\InventorySubCategories;
use App\InvoicePaymentDetail;
use App\QuotationDetails;
use App\UserRole;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\QualityControl\Entities\Configurations\QcSchemes;
use Modules\QualityControl\Entities\Configurations\QcTypes;
use Modules\QualityControl\Entities\Data\QcResults;
use PhpParser\PrettyPrinter\Standard;
use App\SamplesCategory;
use App\LabSectionApproverRelationShip;
use App\BatchLabSectionApprover;
use App\LabSectionApprover;
use App\SampleCondition;
use App\Models\CRM\CompanyProduct;
use App\SampleAnalysisDates;

class SampleWorkFlowController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function index(Request $request, $status = false)
	{
		if (!$status) {
			$status = getSampleWorflowStages()[0];
		}
		$labsections  = SampleAnalysisStage::where('active', 1)->get();

		// return response()->json('test');
		$batches = SampleHeader::with('samples')->where('isactive', 1)->orderBy('receipt_date', 'desc');
		

		if ($status != "All Samples") {
			if ($status == "Schedule of Analysis") {
				$q = "Samples In Lab";
				$l = 1;
				$batches = $batches->where('status', $q)->where('schedule_sent', '<', $l);
			} else {
				$batches = $batches->where('status', $status)->orWhere('prelim_batch_status', $status);
			}
		} else {
			$batches = $batches->where('status', '!=', 'Completed')->orWhere('prelim_batch_status', '!=', '');
		}

		$batches = $batches->get();
		// return response()->json($batches);

		// return json_encode($batches);

		$role_a = SystemConfiguration::where('key', 'analyst_role_id')->first();
		$analysts = User::orderBy('name')->join('user_roles as ur', 'ur.user_id', '=', 'users.id')
			->join('roles as r', 'r.id', '=', 'ur.role_id')
			->where('r.id', $role_a->value)->where('users.active', 1)->where('users.is_support_staff', 0)->selectRaw('users.*')->get();
		$users = User::where('is_client', 0)->where('supplier_id', 0)->where('active', 1)->get();

		return view('layouts.lab.sample-workflow.index', compact('batches', 'status', 'analysts', 'labsections', 'users'));
	}

	public function print_labels(Request $request)
	{
		// return response()->json(getSampleWorkFLowTotals(), 200);
		$ids = $request->sample_code;

		if (count($ids) == 0) {
			return redirect()->back()->with('error', 'No Samples selected');
		}

		$labels = array();

		foreach ($ids as $batch_code) {
			$batch = SampleHeader::where('batch_code', $batch_code)->first();
			$strStage = "Sample Labeling";
			$samWk = "Samples Reception";

			$stage = SampleAnalysisStage::where('name', $strStage)->where('sample_workflow', $samWk)->first();

			$custodyDetails = array(
				"batch_id" => $batch->id,
				"comments" => $request->comments ?? '',
				"current" => array(
					"status" => $batch->status,
					"tracking_stage" => $batch->sample_tracking_stage,
				),
				"target" => array(
					"status" => $samWk,
					"tracking_stage" => $stage->id,
				)
			);
			$this->updateChainofCustody($custodyDetails);

			$samples = $batch->all_samples();


			// echo json_encode($batch);
			// return;
			$client = $batch->client;
			foreach ($samples as $sample) {
				// return response()->json(SampleAnalysisTypeRelationView::where('sample_detail_id',$sample->id)->pluck('analysis_type_name')->toArray(),200);
				$analysis = $sample->analysis();
				$data = array();
				!isset($data['Sample Ref']) ? $data['Sample Ref'] = $sample->sample_code  : $data;
				!isset($data['Sample Type']) ? $data['Sample Type'] = getSampleTypeByID($batch->sample_type_id)->name : $data;
				// !isset($data['Markings']) ? $data['Markings']  = $sample->comments : $data;
				// !isset($data['Requirements']) ? $data['Requirements'] = implode(', ', SampleAnalysisTypeRelationView::where('sample_detail_id', $sample->id)->pluck('analysis_type_name')->toArray()) : $data;
				!isset($data['Date Received']) ? $data['Date Received'] = $batch->receipt_date : $data;
				!isset($data['Received By']) ? $data['Received By'] = $batch->receiving_officer_name : $data;
				!isset($data['Date Expected']) ? $data['Date Expected'] =  date('Y-m-d', strtotime($batch->get_date('Target Date')['date'])) : $data;
				!isset($data['Disposal Date']) ? $data['Disposal Date'] = $sample->disposal_date : $data;

				// return response()->json($batch,200);
				// foreach ($analysis as $a) {
				// 	if (!isset($data['Date Received'])) {
				// 		$data['Date Received'] = $batch->receipt_date;
				// 	}
				// 	// if(!isset($data[$client->unit_configurable_name]) || !isset($data['unit'])){
				// 	// 	if($client->unit_configurable_name != "" ){
				// 	// 		$data[$client->unit_configurable_name] = $batch->crm_unit_name;
				// 	// 	}else{
				// 	// 		$data['unit'] = $batch->crm_unit_name;
				// 	// 	}
				// 	// 	// return response($data,200);
				// 	// 	// trim($client->unit_configurable_name) != "" ? $data[$client->unit_configurable_name] : $data['unit'] = $batch->crm_unit_name;
				// 	// }
				// 	if (isset($data[$client->sample_point_configurable_name])) {
				// 		if (isset($sample->sample_point->name)) {
				// 			if ($data[$client->sample_point_configurable_name] != $sample->sample_point->name) {
				// 				$value = $data[$client->sample_point_configurable_name];
				// 				$data[$client->sample_point_configurable_name] = $value . ',' . $sample->sample_point->name;
				// 			}
				// 		} else {
				// 			if ($data[$client->sample_point_configurable_name] != 'n/a') {
				// 				$value = $data[$client->sample_point_configurable_name];
				// 				$data[$client->sample_point_configurable_name] = $value . ',n/a';
				// 			}
				// 		}
				// 	}
				// 	if (isset($data['Sample Point'])) {
				// 		if (isset($sample->sample_point->name)) {
				// 			if ($data['Sample Point'] != $sample->sample_point->name) {

				// 				$value = $data['Sample Point'];
				// 				$data['Sample Point'] = $value . ',' . $sample->sample_point->name;
				// 			}
				// 		} else {
				// 			if ($data['Sample Point'] != 'n/a') {
				// 				$value = $data['Sample Point'];
				// 				$data['Sample Point'] = $value . ',n/a';
				// 			}
				// 		}
				// 	}
				// 	if (!isset($data[$client->sample_point_configurable_name]) || !isset($data['Sample Point'])) {
				// 		trim($client->sample_point_configurable_name) != "" ? $data[$client->sample_point_configurable_name] = $sample->sample_point->name ?? 'n/a' : $data['Sample Point'] = $sample->sample_point->name ?? 'n/a';
				// 	}
				// 	if (isset($data[$client->product_configurable_name])) {
				// 		if (isset($sample->product->name)) {
				// 			if ($data[$client->product_configurable_name] != $sample->product->name) {
				// 				$value = $data[$client->product_configurable_name];
				// 				$data[$client->product_configurable_name] = $value . ',' . $sample->product->name;
				// 			}
				// 		} else {
				// 			if ($data[$client->product_configurable_name] != 'n/a') {
				// 				$value = $data[$client->product_configurable_name];
				// 				$data[$client->product_configurable_name] = $value . ',n/a';
				// 			}
				// 		}
				// 	}
				// 	if (isset($data['product'])) {
				// 		if (isset($sample->product->name)) {
				// 			if ($data['product'] != $sample->product->name) {
				// 				$value  = $data[$client->product_configurable_name];
				// 				$data[$client->product_configurable_name] = $value . ',' . $sample->product->name;
				// 			}
				// 		} else {
				// 			if ($data['product'] != 'n/a') {
				// 				$value = $data[$client->product_configurable_name];
				// 				$data[$client->product_configurable_name] = $value . ',n/a';
				// 			}
				// 		}
				// 	}
				// 	// if(!isset($data[$client->product_configurable_name]) && !isset($data['product'])){
				// 	// 	trim($client->product_configurable_name) != "" ? $data[$client->product_configurable_name] = $sample->product->name ?? 'n/a' : $data["product"] = $sample->product->name ?? 'n/a';
				// 	// }
				// 	if (isset($data['Test To Be Done'])) {
				// 		if ($data['Test To Be Done'] != $a->name) {
				// 			$value = $data['Test To Be Done'];
				// 			$data['Test To Be Done'] = $value . ',' . $a->name;
				// 		}
				// 	}
				// 	if (!isset($data['Test To Be Done'])) {
				// 		$data['Test To Be Done'] = $a->name;
				// 	}
				// 	if (isset($data['Lab'])) {
				// 		if ($data['Lab'] != $a->lab->name . " - " . date('Y-m-d')) {
				// 			$value = $data['Test To Be Done'];
				// 			$data['Test To Be Done'] = $value . ',' . $a->lab->name . " - " . date('Y-m-d');
				// 		}
				// 	}
				// 	if (!isset($data['Test To Be Done'])) {
				// 		$data['Test To Be Done'] = $a->lab->name . " - " . date('Y-m-d');
				// 	}
				// 	// $data = array(
				// 	// 	"Code" => $sample->sample_code,
				// 	// 	"Client" => $client->name,
				// 	// 	trim($client->unit_configurable_name) != "" ? $client->unit_configurable_name : 'unit' => $batch->crm_unit_name,
				// 	// 	trim($client->sample_point_configurable_name) != "" ? $client->sample_point_configurable_name : "sample_point" => $sample->sample_point->name ?? 'n/a',
				// 	// 	trim($client->product_configurable_name) != "" ? $client->product_configurable_name : "product" => $sample->product->name ?? 'n/a',
				// 	// 	"Analysis" => $a->name,
				// 	// 	"Lab" => $a->lab->name . " - " . date('Y-m-d')
				// 	// );
				// }
				$labels[] = $data;
			}
			// return response()->json($labels,200);
			$batch->sample_tracking_stage = $stage->id;
			$batch->save();

			// $hasCapturedResults = false;

			// $analysis_to_be_done = array();

			// $batch_analysis = $batch->samples;


			// foreach ($batch_analysis as $a) {
			// 	if (!isset($analysis_to_be_done[$a->sample_code])) {
			// 		$analysis_to_be_done[$a->sample_code] = array(
			// 			"sample_detail_code" => $a->sample_code,
			// 			"sample_detail_id" => $a->id,
			// 			"sample_header_id" => $batch->id,
			// 			"analysis_to_do" => array()
			// 		);
			// 	}
			// 	$analysis_to_be_done[$a->sample_code]["analysis_to_do"] = array_merge($analysis_to_be_done[$a->sample_code]["analysis_to_do"], $a->analysis());
			// }

			// $analysis_to_be_done = array_values($analysis_to_be_done);

			// foreach ($analysis_to_be_done as $atbs) {
			// 	foreach ($atbs["analysis_to_do"] as $a) {
			// 		$analytes = $a->active_analysis_elements();

			// 		foreach ($analytes as $an) {
			// 			$analysisType = AnalysisElements::where('analysis_type_id', $a->id)
			// 				->where('analyte_id', $an->analyte_id)->where('equipment_id', $an->equipment_id)->first();

			// 			$captured = CapturedResult::where('sample_detail_code', $atbs['sample_detail_code'])
			// 				->where('sample_detail_id', $atbs['sample_detail_id'])
			// 				->where('analyte_id', $an->analyte_id)
			// 				->where('analysis_type_id', $a->id)
			// 				->where('sample_header_id', $atbs['sample_header_id'])->first()  ?? new CapturedResult;
			// 			$captured->sample_detail_code = $atbs['sample_detail_code'];
			// 			$captured->sample_detail_id = $atbs['sample_detail_id'];
			// 			$captured->sample_header_id = $atbs['sample_header_id'];
			// 			$captured->analyte_id = $an->analyte_id;
			// 			$captured->analysis_type_id = $a->id;
			// 			$captured->analyte_code = $an->analyte_code;
			// 			$captured->equipment_id = $an->equipment_id;
			// 			$captured->method_id = $analysisType->method;
			// 			$captured->user_id = \Auth::user()->id;
			// 			$captured->analyte_accredited = $analysisType->non_accredited;
			// 			$captured->save();

			// 			$result = Result::where('sample_detail_code', $atbs['sample_detail_code'])
			// 				->where('sample_detail_id', $atbs['sample_detail_id'])
			// 				->where('captured_result_id', $captured->id)
			// 				->where('analyte_id', $an->analyte_id)
			// 				->where('analysis_type_id', $a->id)
			// 				->where('sample_header_id', $atbs['sample_header_id'])->first()  ?? new Result;
			// 			$result->captured_result_id = $captured->id;
			// 			$result->sample_detail_code = $atbs['sample_detail_code'];
			// 			$result->sample_detail_id = $atbs['sample_detail_id'];
			// 			$result->sample_header_id = $atbs['sample_header_id'];
			// 			$result->analyte_id = $an->analyte_id;
			// 			$result->analysis_type_id = $a->id;
			// 			$result->analyte_code = $an->analyte_code;
			// 			$result->unit_code = $an->reporting_unit;
			// 			$result->reporting_symbol = $an->reporting_symbol;
			// 			$result->analyte_accredited = $analysisType->non_accredited;
			// 			$result->recheck = 0;

			// 			$result->save();
			// 		}
			// 	}
			// }
		}

		// return response()->json($labels, 200);

		return view('layouts.lab.sample-workflow.labels', compact('labels'));
	}

	public function add_batch_info(Request $request, $batch)
	{
		if (isset($request->is_qc_batch)) {
			if (isset($request->repeat_sample_id) && $request->repeat_sample_id > 0) {
				$repeat_samples = SampleDetails::find($request->repeat_sample_id);
				$last_header = SampleHeader::find($repeat_samples->sample_header_id);
				$selectedCustomer = CRMCustomer::find($last_header->crm_customer_id);
			} else {
				$qc_customer_id = SystemConfiguration::where('key', 'qc_customer_id')->first();
				$selectedCustomer = CRMCustomer::find($qc_customer_id->value);
			}
		} else {
			$selectedCustomer = CRMCustomer::find($request->crm_customer_id);
		}
		$selectedSampleType = SampleType::find($request->sample_type_id);
		$batch_config = SystemConfiguration::where('key', 'batch_code_config')->first();
		if (!isset($batch_config->id)) {
			return redirect()->back()->with('error', 'Kindly add batch_code_config configuration');
		}

		$cust_code = str_split($selectedCustomer->code);
		$code = [];
		$loop = 0;
		$cont = [];
		foreach ($cust_code as $cc) {
			if ((int)$cc > 0) {
				array_push($cont, $loop);
			} elseif (is_string($cc) && $cc != '0') {
				array_push($code, $cc);
			}
			++$loop;
		}
		$tt = sizeof($cust_code) - 1;

		$ranges = range($cont[0], $tt);
		$values = [];
		if (sizeof($cont) < 2) {
			array_push($values, '0');
			array_push($values, $cust_code[$cont[0]]);
		} else {

			foreach ($ranges as $r) {
				array_push($values, $cust_code[$r]);
			}
		}

		$cP = implode('', $code) . $batch_config->value . implode('', $values) . $selectedSampleType->code;
		$isNew = false;
		$isInReception = false;
		$header = SampleHeader::find($batch) ?? new SampleHeader;
		if (!isset($header->batch_code)) {
			$isNew = true;
			$isInReception = true;
			// return response()->json($cP);
			$config_batch_no = SystemConfiguration::where('key', 'batch_start_no')->first();
			if (!isset($config_batch_no->id)) {
				return redirect()->back()->with('error', 'Kindly set the start batch no');
			}
			$last_id = isset(SampleHeader::latest('id')->first()->id) ? SampleHeader::latest('id')->first()->id : 0;
			$batch_no_s = $config_batch_no->value + $last_id + 1;
			$final_no = '';
			if (strlen(strval($batch_no_s)) < 4) {
				$zerosss = str_repeat('0', 4 - strlen(strval($batch_no_s)));
				$final_no = $zerosss . '' . strval($batch_no_s);
			} else {
				$final_no = strval($batch_no_s);
			}
			$header->batch_code = $cP . '' . $final_no;
		}
		// return response()->json($header->batch_code,200);

		if (isset($header->status) && $header->status == "Samples Reception") {
			$isInReception = true;
		}

		if(!isset($request->is_bl_save)){

			$header->receipt_date = $request->receipt_date;
			$header->date_collected = $request->date_collected;
			$header->batch_scope = $request->batch_scope;
			$header->customer_survey = $request->customer_survey;
			$header->is_qc_batch = isset($request->is_qc_batch);
			$header->qc_type_id = $request->qc_type_id;
			$header->qc_scheme_id = $request->qc_scheme_id;
			$header->repeat_batch_id = isset($request->repeat_sample_id) ? $request->repeat_batch_id : 0;
			$header->repeat_sample_id = isset($request->repeat_sample_id) && $request->repeat_sample_id > 0 ? $request->repeat_sample_id : $header->repeat_sample_id;
			$header->begin_proccess = isset($request->is_qc_batch) ?  1 : 0;
			$header->quote_no = $request->quote_no;
			$header->lab_capable = isset($request->lab_capable) ? 1 : 0;
			$header->can_be_subcontracted = isset($request->can_be_subcontracted) ? 1 : 0;
			$header->batch_subcontracted_client_approval = isset($request->batch_subcontracted_client_approval) ? 1 : 0;
			$header->client_instruction_clear = isset($request->client_instruction_clear) ? 1 : 0;
			$header->batch_instructions = $request->batch_instructions;
			$header->sampling_method_id = $request->sampling_method_id;
			$header->require_mu = $request->require_mu;
			$header->payment_done_by = $request->payment_done_by;
			$header->condition_quality_sample = $request->condition_quality_sample;
			$header->invoice_amount = $request->invoice_amount;
			$header->lab_section_ids = implode(',', $request->lab_section_ids ?? []);
			$header->crm_contact_id = $request->crm_contact_id;
	
			if ($isInReception) {
				$header->sample_type_id = $request->sample_type_id;
				if (isset($request->is_qc_batch)) {
					if (isset($request->repeat_sample_id) && $request->repeat_sample_id > 0) {
						$repeat_samples = SampleDetails::find($request->repeat_sample_id);
						$last_header = SampleHeader::find($repeat_samples->sample_header_id);
	
						$header->crm_customer_id = $last_header->crm_customer_id;
						$header->sample_type_id = $last_header->sample_type_id;
						$header->crm_unit_id= $last_header->crm_unit_id;
					} else {
						$qc_customer_id = SystemConfiguration::where('key', 'qc_customer_id')->first();
						$qc_customer_unit = SystemConfiguration::where('key', 'qc_customer_unit')->first();
	
						$header->crm_customer_id = $qc_customer_id->value;
	
						$header->crm_unit_name = $qc_customer_unit->value;
					}
				} else {
	
					$header->crm_customer_id = $request->crm_customer_id;
	
					$header->crm_unit_id = $request->crm_unit_name;
	
					$customer = getCrmCustomerByID($request->crm_customer_id);
					$account = SystemConfiguration::find($customer->account_status);
					if (isset($account->id)) {
						$header->current_account_status = $account->key;
						if ($account->key == 'Suspended') {
							return redirect()->back()->with('error', 'The customer is currently suspended!');
						}
					} else {
						$header->current_account_status = 'N/a';
					}
				}
			}
			
			$header->description = $request->description;
			$header->document_number = $request->document_number;
			$header->importer_address = $request->importer_address;
			$header->date_expected = $request->date_expected;
			$header->quote_id = $request->quote_id;$header->sampling_method_id = $request->sampling_method_id;
			$header->radio_active_levels = $request->radio_active_levels;
			$header->receiving_officer_name = $request->receive_by;
			$header->receiving_officer =  $request->receive_by;
			$header->sampling_officer_name = $request->sample_by;
			$header->reference_number = $request->reference_number ?? 'n/a';
			$header->is_routine = $request->is_routine ?? 0;
			$header->routine_frequency = isset($request->is_routine) ? $request->routine_frequency : 0;
			if ($isNew) {
				if ($request->has('is_client_order')) {
					$header->status = 'Samples En-Route';
				} else {
					$header->status = 'Samples Reception';
				}
			}
		}
		
		$header->sampling_method_id = $request->sampling_method_id;
		$header->how_sample_was_obtained = $request->how_sample_was_obtained;
		$header->declared_commodity_code = $request->declared_commodity_code;
		$header->declared_amount = $request->declared_amount ?? 0;
		$header->net_quantity_and_unit_of_quantity = $request->net_quantity_and_unit_of_quantity;
		$header->use_of_goods = $request->use_of_goods;
		$header->sample_appearance_description = $request->sample_appearance_description;
		$header->kra_office_ref = $request->kra_office_ref;
		$header->kra_office_station = $request->kra_office_station;
		$header->where_sample_was_obtained = $request->where_sample_was_obtained;
		$header->submit_by = $request->submit_by;
		$header->radio_active_levels = $request->radio_active_levels;
		$header->importer_address = $request->importer_address;
		if (isset($request->sampled_by_company_personnel)) {
			$header->sampled_by_company_personnel = 1;
		} else {
			$header->sampled_by_company_personnel = 0;
		}
		if (isset($request->agreement)) {
			$header->user_agreement = 1;
		} else {
			$header->user_agreement = 0;
		}

		if ($isNew) {
			$stage = SampleAnalysisStage::where('sample_workflow', $header->status)->orderBy('level', 'asc')->first();
			$header->sample_tracking_stage = $stage->id ?? 0;
		}

		$header->save();
		if (isset($request->repeat_sample_id) && $request->repeat_sample_id > 0) {
			$samples = SampleDetails::find($request->repeat_sample_id);
			$new_sample = $samples->replicate();
			$config_start_no = SystemConfiguration::where('key', 'start_sample_no')->first();
			if (!isset($config_start_no->id)) {
				return redirect()->back()->with('error', 'Kindly configure the start sample No');
			}
			$last_sample = isset(SampleDetails::latest('id')->first()->id) ? explode('-', SampleDetails::max('sample_code'))[1]  : $config_start_no->value;
			$sample_number = intval($last_sample)  + 1;

			$new_sample->sample_code = 'S0513-' . $sample_number;
			$new_sample->sample_header_id = $header->id;
			$new_sample->save();
			$captureds = CapturedResult::where('sample_detail_id', $samples->id)->get();
			foreach ($captureds as $capture) {
				$new_capture = $capture->replicate();
				$result = Result::where('captured_result_id', $capture->id)->first();
				$new_capture->sample_detail_id = $new_sample->id;
				$new_capture->sample_header_id = $header->id;
				$new_capture->sample_detail_code = $new_sample->sample_code;
				$new_capture->result = '';
				$new_capture->remark = '';
				$new_capture->repeat_captured_id = $capture->id;
				$new_capture->save();
				$new_result = $result->replicate();
				$new_result->captured_result_id = $new_capture->id;
				$new_result->sample_detail_id = $new_sample->id;
				$new_result->sample_header_id = $header->id;
				$new_result->sample_detail_code = $new_sample->sample_code;
				$new_result->result = '';
				$new_result->remarks = '';
				$new_result->repeat_results_id = $result->id;
				$new_result->save();
			}
		}
		$maxReportingTime = 0;
		$targetDateStr = "Target Date";
		$targetDate = \App\SampleDate::where('sample_header_id', $header->id)->where('name', $targetDateStr)->first() ?? new \App\SampleDate;
		$targetDate->name = $targetDateStr;
		$targetDate->sample_header_id = $header->id;
		$targetDate->date = \Carbon\Carbon::parse($header->receipt_date)->addDays($maxReportingTime);
		$targetDate->save();

		if ($isNew) {
			$custodyDetails = array(
				"batch_id" => $header->id,
				"comments" => $request->comments ?? '',
				"current" => array(
					"status" => $header->status,
					"tracking_stage" => $header->sample_tracking_stage,
				),
				"target" => array(
					"status" => $header->status,
					"tracking_stage" => $header->sample_tracking_stage,
				)
			);
			$this->updateChainofCustody($custodyDetails);
		}

		$route_obj = ['batch' => $header->id,'client'=>0,'portal'=>0,"status"=>$header->status];

		if ($request->has('is_client_order')) {
			$route_obj['client'] = $request->crm_customer_id;
		} else {
			$header->set_date("Login Date", $header->created_at, true);
		}

		return redirect()->route('view-batch-details', $route_obj)->within('success', 'Batch Info added.');
	}

	public function add_batch_samples(Request $request, $batch)
	{
		// return response()->json($request->all(), 200);
		$SampleHeader = SampleHeader::find($batch);


		$selectedCustomer = CRMCustomer::find($SampleHeader->crm_customer_id);
		$selectedSampleType = SampleType::find($SampleHeader->sample_type_id);

		$configuration = SystemConfiguration::where('key', 'sample_code_naming')->first();
		if (isset($configuration->id)) {

			$codePrefix = $configuration->value . '-';
		} else {
			return redirect()->back()->with('error', 'Setup the naming convenction of samples in system configurations');
		}

		$samplesRequiringStorage = array("samples" => array(), "store_ids" => array());

		// return response()->json($request->all(), 200);
		$maxReportingTime = 0;
		$currentAnalysisSample = [];
		$duplicate_samples = [];
		$duplicate_samples_ids = [];
		$duplicateDataSampleIds = [];
		foreach ($request->sample_details['sample_code'] as $k => $v) {
			$detailId = $request->sample_details['detail_header'][$k];

			$detail = $detailId && $detailId != "undefined" ? SampleDetails::find($detailId) : new SampleDetails;
			$detail->sample_header_id = $SampleHeader->id;

			if (!isset($detail->sample_code)) {
				// $config_start_no = SystemConfiguration::where('key', 'start_sample_no')->first();
				$lab = Lab::find($request->sample_details['lab_id'][$k]);
				if (isset(SampleDetails::where('lab_id', $lab->id)->orderBy('id', 'DESc')->first()->id)) {
					$code = SampleDetails::where('lab_id', $lab->id)->orderBy('id', 'DESc')->first()->sample_code;
					$last_sample = substr($code, 9, strlen($code));
				} else {
					$last_sample = $lab->start_sample_no != '' ? $lab->start_sample_no : 0;
				}
				// $last_sample = isset(SampleDetails::latest('id')->first()->id) ? substr(SampleDetails::latest('id')->first()->sample_code,9,strlen(SampleDetails::latest('id')->first()->sample_code) -1) : $config_start_no->value;

				// return response()->json($request->sample_details['lab_id'][$k]);
				$sample_number = intval($last_sample)  + 1;
				$sample_number = str_pad($sample_number, 4, '0', STR_PAD_LEFT);

				$detail->sample_code = 'S' . date('Y') . $lab->code . $sample_number;
				$detail->sample_no = $sample_number;
			}
			if (isset($detail->id)) {
				$current_analysis = explode(',', $detail->analysis_type_id);
				$currentAnalysisSample[$detail->sample_code] = $current_analysis;
				$updated_analysis = $request->sample_details['sample_analysis'][$k];
				// return response()->json($updated_analysis);
				foreach ($current_analysis as $ca) {
					if (!in_array($ca, $updated_analysis)) {
						$captured = CapturedResult::where('sample_detail_id', $detail->id)->where('analysis_type_id', $ca)->get();
						foreach ($captured as $c) {
							$c->delete();
						}
						$results = Result::where('sample_detail_id', $detail->id)->where('analysis_type_id', $ca)->get();
						foreach ($results as $res) {
							$res->delete();
						}
					}
				}
			}

			$detail->analysis_type_id = implode(',', $request->sample_details['sample_analysis'][$k] ?? array());
			$detail->sample_condition_id = $request->sample_details['sample_condition'][$k];
			$detail->sample_point_id = $request->sample_details['sample_point'][$k];
			$detail->company_product_id = $request->sample_details['product'][$k];
			$detail->barcode = $request->sample_details['barcode'][$k];
			$detail->comments = $request->sample_details['comments'][$k];
			$detail->disposal_date = $request->sample_details['disposal_date'][$k];
			// $detail->lab_sub_no = $request->sample_details['submission_no'][$k];

			$detail->main_standard = $request->sample_details['main_standard'][$k];
			$detail->secondary_standard = $request->sample_details['secondary_standard'][$k];
			$detail->lab_id = $request->sample_details['lab_id'][$k];
			$detail->save();
			if ($request->sample_details['is_duplicate'][$k] != "0") {
				$duplicate_samples[$detail->id] = $request->sample_details['is_duplicate'][$k];
				array_push($duplicate_samples_ids, $detail->id);
				$duplicateDataSampleIds[$detail->id] = $detail->sample_code;
			}

			$this->createDetailAnalysisRelation($SampleHeader->id, $detail->id, explode(',', $detail->analysis_type_id));


			if (isset($current_analysis) && sizeof($current_analysis) > 0) {
				$update = $request->sample_details['sample_analysis'][$k];
				foreach ($current_analysis as $ca) {
					if (!in_array($ca, $update)) {
						$analysis_type = AnalysisType::find(intval($ca));
						if (isset($analysis_type->id)) {
							$captured_reults = CapturedResult::where('analysis_type_id', $analysis_type->id)->where('sample_detail_id', $detail->id)->where('sample_header_id', $detail->sample_header_id)->get();
							foreach ($captured_reults as $cr) {
								$result = Result::where('captured_result_id', $cr->id)->first();
								$result->delete();
								$cr->delete();
							}
						}
					}
				}
			}
			$analysis_max_report_time = AnalysisType::whereIn('id', $request->sample_details['sample_analysis'][$k])->max('reporting_time');
			$analytes_max_report_time = AnalysisElements::whereIn('analysis_type_id', $request->sample_details['sample_analysis'][$k])->max('reporting_time');
			$maxReportingTime = $analysis_max_report_time > $analytes_max_report_time ? $analysis_max_report_time : $analytes_max_report_time;


			if (trim($request->sample_details['sample_store'][$k]) != "" && trim($request->sample_details['sample_store_slot'][$k]) != "" && trim($request->sample_details['sample_quantity'][$k]) != "") {

				$ISCC = new InventorySubCategoriesController;

				$sampleLabItemExists = \App\InventorySubCategories::where('name', $SampleHeader->batch_code . "/" . $detail->sample_code)->first();

				if (!isset($sampleLabItemExists->id)) {
					$category = InventorySubCategories::where('is_lab', 1)->first();
					if (!isset($category->id)) {
						return redirect()->back()->with('error', 'Kindly a inventory sub-category for lab items;');
					}
					$req = new Request();
					$req->category_id = 20003;
					$req->name = $SampleHeader->batch_code . "/" . $detail->sample_code;
					$req->description = "Sample for batch - " . $SampleHeader->batch_code;
					$req->manufacturer = "Source: Lab";
					$req->minimum_level = 0;
					$req->unit_type = $request->sample_details['sample_reporting_unit'][$k];
					$req->unit_price = 0;
					$req->reporting_decimal_places = 2;
					$req->parent = "\App\SampleDetails";
					$req->parent_id = $detail->id;


					$sampleItem = $ISCC->add($req, true); //Create Item in Inventory for storage

					// return json_encode($sampleItem);

					$IIC = new InventoryItemController;

					$req = new Request();
					$req->category_id = 20003;
					$req->sub_category_id = $sampleItem->id;
					$req->batchcode = $sampleItem->name;
					$req->inventory_department_id = 1;
					$req->supplier_id = 4;
					$req->received_by = 0;
					$req->previous_batch_code = 'N/A';
					$req->quantity = $request->sample_details['sample_quantity'][$k] ?? 0;
					$req->barcode = $request->sample_details['barcode'][$k] ?? 'n/a';
					$req->store = $request->sample_details['sample_store'][$k] ?? 0;
					$req->slot = $request->sample_details['sample_store_slot'][$k] ?? 0;
					$req->price = 0;

					$inventoryItem = $IIC->add($req, true);

					$samplesRequiringStorage["samples"][] = $detail->sample_code;
					$samplesRequiringStorage["store_ids"][] = $request->sample_details['sample_store'][$k] ?? 0;

					// $ISSCC = new InventoryStoreSlotContentController;

					// $req = new Request();
					// $req->item = $inventoryItem->batchcode;

					// $content = $ISSCC->add($req, $request->sample_details['sample_store_slot'][$k], $request->sample_details['sample_store'][$k], true);
					// return json_encode($content);
				}
			}
		}

		if (count($samplesRequiringStorage['samples']) > 0) {
			$companyDetails = getCompanyDetails();
			$samplesRequiringStorage["batch_route"] = route('view-batch-details', ['batch' => $SampleHeader->id]);
			$samplesRequiringStorage["batch_code"] = $SampleHeader->batch_code;

			$samplesOL = "<ol>";

			foreach ($samplesRequiringStorage['samples'] as $sampleC) {
				$samplesOL .= "<li>" . $sampleC . "</li>";
			}

			$samplesOL .= "</ol>";

			$body = '
				Hi,<br>
				<p>
					The following samples have been added to the batch ' . $SampleHeader->batch_code . '.<br>
					' . $samplesOL . '
				</p>
				' . ($SampleHeader->status == "Samples En-Route" ? "The samples are expected on " . $SampleHeader->date_expected : "") . '
				<p>
					Please view the batch for more information about the samples: <a href="' . $samplesRequiringStorage["batch_route"] . '">' . $samplesRequiringStorage["batch_route"] . '</a>
				</p>
				Regards,<br>
				' . $companyDetails['name'] . '
			';

			$subject = '[' . $companyDetails["name"] . '] Samples En-Route Storage Notification for Batch - ' . $SampleHeader->batch_code;

			$emails = \App\InventoryStoreContact::join('users as u', 'u.id', 'inventory_store_contacts.user_id')
				->selectRaw('u.email')->whereIn('inventory_store_contacts.store', $samplesRequiringStorage["store_ids"])->get()->pluck('email')->toArray();

			// return json_encode($emails, JSON_PRETTY_PRINT);

			$emails = array_unique($emails);
			notify_user($body, $emails, $subject);
		}

		$targetDateStr = "Target Date";
		$targetDate = \App\SampleDate::where('sample_header_id', $SampleHeader->id)->where('name', $targetDateStr)->first() ?? new \App\SampleDate;
		$targetDate->name = $targetDateStr;
		$targetDate->sample_header_id = $SampleHeader->id;
		$targetDate->date = \Carbon\Carbon::parse($SampleHeader->receipt_date)->addDays($maxReportingTime);
		$targetDate->save();

		$batch = SampleHeader::find($request->batch);
		$strStage = "Sample Labeling";
		$samWk = "Samples Reception";

		$stage = SampleAnalysisStage::where('name', $strStage)->where('sample_workflow', $samWk)->first();
		$batch->days_of_analysis = $maxReportingTime;
		$batch->sample_tracking_stage = $stage->id;
		$batch->save();
		if (sizeof($duplicate_samples_ids) > 0) {
			foreach ($duplicate_samples as $key => $value) {
				$captured_results = CapturedResult::where('sample_detail_code', $value)->get();
				foreach ($captured_results as $c_value) {
					$new_cr = $c_value->replicate()->fill([
						'sample_detail_id' => $key,
						'sample_detail_code' => $duplicateDataSampleIds[$key],
						'result' => '',
						'user_id' => auth()->user()->id,
						'remark' => '',
					]);
					$new_cr->save();
					$result = Result::where('captured_result_id', $c_value->id)->first();
					$new_result = $result->replicate()->fill([
						'captured_result_id' => $new_cr->id,

						'sample_detail_id' => $new_cr->sample_detail_id,
						'sample_detail_code' => $new_cr->sample_detail_code,
						'result' => '',
						'remarks' => '',
					]);
					$new_result->save();
				}
			}
		}

		$hasCapturedResults = false;
		$analysis_to_be_done = array();

		$batch_analysis = $batch->samples;


		foreach ($batch_analysis as $a) {

			if (!isset($analysis_to_be_done[$a->sample_code]) && !in_array($a->id, $duplicate_samples_ids)) {
				$analysis_to_be_done[$a->sample_code] = array(
					"sample_detail_code" => $a->sample_code,
					"sample_detail_id" => $a->id,
					"sample_header_id" => $batch->id,
					"analysis_to_do" => array()
				);
			}
			if (!in_array($a->id, $duplicate_samples_ids)) {

				$analysis_to_be_done[$a->sample_code]["analysis_to_do"] = array_merge($analysis_to_be_done[$a->sample_code]["analysis_to_do"], $a->analysis());
			}
		}


		$analysis_to_be_done = array_values($analysis_to_be_done);
		$labstr = implode(',', $batch->labs(true));
		$labarr = explode(' - ', $labstr);
		$checklab = Lab::where('code', $labarr[0])->where('name', $labarr[1])->first();
		if (!isset($checklab->id)) {
			$param = explode(',', $labarr[1]);
			$lab = Lab::where('code', $labarr[0])->where('name', $param[0])->first();
		} else {
			$lab = $checklab;
		}

		// return response()->json($analysis_to_be_done,200);

		foreach ($analysis_to_be_done as $atbs) {
			foreach ($atbs["analysis_to_do"] as $a) {
				if (isset($currentAnalysisSample[$atbs['sample_detail_code']]) && in_array($a->id, $currentAnalysisSample[$atbs['sample_detail_code']])) {
					$analytes = array();
				} else {
					$analytes = $a->active_analysis_elements();
				}

				// return response()->json($analytes,200);


				foreach ($analytes as $an) {
					$analysisType = AnalysisElements::where('analysis_type_id', $a->id)
						->where('analyte_id', $an->analyte_id)->where('equipment_id', $an->equipment_id)->first();

					$captured = CapturedResult::where('sample_detail_code', $atbs['sample_detail_code'])
						->where('sample_detail_id', $atbs['sample_detail_id'])
						->where('analyte_id', $an->analyte_id)
						->where('analysis_type_id', $a->id)
						->where('sample_header_id', $atbs['sample_header_id'])->first()  ?? new CapturedResult;
					$captured->sample_detail_code = $atbs['sample_detail_code'];
					$captured->sample_detail_id = $atbs['sample_detail_id'];
					$captured->sample_header_id = $atbs['sample_header_id'];
					$captured->analyte_id = $an->analyte_id;
					$captured->analysis_type_id = $a->id;
					$captured->analyte_code = $an->analyte_code;
					$captured->equipment_id = $an->equipment_id;
					$captured->method_id = $analysisType->method;
					$captured->user_id = \Auth::user()->id;
					$captured->analyte_accredited = $analysisType->non_accredited;
					$captured->analyte_status_contracted = $lab->is_external ?? 0;
					$captured->lab_section_id  = $analysisType->lab_section_id;
					$captured->parameters_order = $analysisType->level ?? 0;
					$captured->remark_is_manual = $analysisType->remark_is_manual;

					$captured->save();

					$result = Result::where('sample_detail_code', $atbs['sample_detail_code'])
						->where('sample_detail_id', $atbs['sample_detail_id'])
						->where('captured_result_id', $captured->id)
						->where('analyte_id', $an->analyte_id)
						->where('analysis_type_id', $a->id)
						->where('sample_header_id', $atbs['sample_header_id'])->first()  ?? new Result;
					$result->captured_result_id = $captured->id;
					$result->sample_detail_code = $atbs['sample_detail_code'];
					$result->sample_detail_id = $atbs['sample_detail_id'];
					$result->sample_header_id = $atbs['sample_header_id'];
					$result->analyte_id = $an->analyte_id;
					$result->analysis_type_id = $a->id;
					$result->analyte_code = $an->analyte_code;
					$result->unit_code = $an->reporting_unit;
					$result->reporting_symbol = $an->reporting_symbol;
					$result->recheck = 0;
					$result->analyte_status_contracted = $lab->is_external ?? 0;
					$result->lab_section_id  = $analysisType->lab_section_id;
					$result->parameters_order = $analysisType->level ?? 0;
					$result->remark_is_manual = $analysisType->remark_is_manual;

					$result->save();
				}
			}
		}

		return redirect()->back()->within('success', 'Batch Samples updated.');
	}
	public function createDetailAnalysisRelation($batch_id, $sample_id, $analysis_type)
	{
		$data = [];
		sampleAnalysisTypeRelation::where('batch_id', $batch_id)->where('sample_detail_id', $sample_id)->whereNotIn('analysis_type_id', $analysis_type)->delete();
		$existing = sampleAnalysisTypeRelation::where('batch_id', $batch_id)->where('sample_detail_id', $sample_id)->pluck('analysis_type_id')->toArray();
		foreach ($analysis_type as $at) {
			if(!in_array($at,$existing)){
				$data[] = [
					"analysis_type_id" => $at,
					"batch_id" => $batch_id,
					"sample_detail_id" => $sample_id,
				];
			}
		}
		sizeof($data) > 0 ? sampleAnalysisTypeRelation::insert($data) : '';
		return "success";
	}
	public function addBatchSamplesDynamically()
	{
		$batches = SampleHeader::all();
		foreach ($batches as $batch) {
			$batch_analysis = $batch->samples;
			$hasCapturedResults = false;

			$analysis_to_be_done = array();


			foreach ($batch_analysis as $a) {
				if (!isset($analysis_to_be_done[$a->sample_code])) {
					$analysis_to_be_done[$a->sample_code] = array(
						"sample_detail_code" => $a->sample_code,
						"sample_detail_id" => $a->id,
						"sample_header_id" => $batch->id,
						"analysis_to_do" => array()
					);
				}

				$analysis_to_be_done[$a->sample_code]["analysis_to_do"] = array_merge($analysis_to_be_done[$a->sample_code]["analysis_to_do"], $a->analysis());
			}

			$analysis_to_be_done = array_values($analysis_to_be_done);
			$labstr = implode(',', $batch->labs(true));
			$labarr = explode(' - ', $labstr);
			$checklab = Lab::where('code', $labarr[0])->where('name', $labarr[1])->first();
			if (!isset($checklab->id)) {
				$param = explode(',', $labarr[1]);
				$lab = Lab::where('code', $labarr[0])->where('name', $param[0])->first();
			} else {
				$lab = $checklab;
			}

			foreach ($analysis_to_be_done as $atbs) {
				foreach ($atbs["analysis_to_do"] as $a) {
					if (isset($current_analysis) && in_array($a->id, $current_analysis)) {
						$analytes = array();
					} else {
						$analytes = $a->active_analysis_elements();
					}
					// return response()->json($analytes,200);


					foreach ($analytes as $an) {
						$analysisType = AnalysisElements::where('analysis_type_id', $a->id)
							->where('analyte_id', $an->analyte_id)->where('equipment_id', $an->equipment_id)->first();

						$captured = CapturedResult::where('sample_detail_code', $atbs['sample_detail_code'])
							->where('sample_detail_id', $atbs['sample_detail_id'])
							->where('analyte_id', $an->analyte_id)
							->where('analysis_type_id', $a->id)
							->where('sample_header_id', $atbs['sample_header_id'])->first()  ?? new CapturedResult;
						$captured->sample_detail_code = $atbs['sample_detail_code'];
						$captured->sample_detail_id = $atbs['sample_detail_id'];
						$captured->sample_header_id = $atbs['sample_header_id'];
						$captured->analyte_id = $an->analyte_id;
						$captured->analysis_type_id = $a->id;
						$captured->analyte_code = $an->analyte_code;
						$captured->equipment_id = $an->equipment_id;
						$captured->method_id = $analysisType->method;
						$captured->user_id = \Auth::user()->id;

						$captured->analyte_status_contracted = $lab->is_external ?? 0;

						$captured->save();

						$result = Result::where('sample_detail_code', $atbs['sample_detail_code'])
							->where('sample_detail_id', $atbs['sample_detail_id'])
							->where('captured_result_id', $captured->id)
							->where('analyte_id', $an->analyte_id)
							->where('analysis_type_id', $a->id)
							->where('sample_header_id', $atbs['sample_header_id'])->first()  ?? new Result;
						$result->captured_result_id = $captured->id;
						$result->sample_detail_code = $atbs['sample_detail_code'];
						$result->sample_detail_id = $atbs['sample_detail_id'];
						$result->sample_header_id = $atbs['sample_header_id'];
						$result->analyte_id = $an->analyte_id;
						$result->analysis_type_id = $a->id;
						$result->analyte_code = $an->analyte_code;
						$result->unit_code = $an->reporting_unit;
						$result->reporting_symbol = $an->reporting_symbol;
						$result->recheck = 0;
						$result->analyte_status_contracted = $lab->is_external ?? 0;
						$result->save();
					}
				}
			}
		}
		return response()->json('success', 200);
	}

	// public function add(Request $request)
	// {

	// 	$selectedCustomer = CRMCustomer::find($request->crm_customer_id);
	// 	$selectedSampleType = SampleType::find($request->sample_type_id);

	// 	$cP = "BC".$selectedCustomer->code;

	// 	$header = isset($request->sample_header_id) ? SampleHeader::find($request->sample_header_id) : new SampleHeader;
	// 	if(!isset($request->sample_header_id)){
	// 		$header->batch_code = getNamingConventionCode("Samples", false, $cP);
	// 	}

	// 	$header->receipt_date = $request->receipt_date;
	// 	$header->date_collected = $request->date_collected;
	// 	$header->crm_customer_id = $request->crm_customer_id;
	// 	$header->sample_type_id = $request->sample_type_id;
	// 	$header->crm_unit_name = $request->crm_unit_name;
	// 	$header->reference_number = $request->reference_number ?? 'n/a';
	// 	$header->is_routine = $request->is_routine ?? 0;
	// 	$header->routine_frequency = isset($request->is_routine) ? $request->routine_frequency : 0;
	// 	$header->status = 'samples_in_reception';

	// 	$header->save();

	// 	$codePrefix = $selectedCustomer->code.$selectedSampleType->code;

	// 	// return response()->json($request->all(), 200);

	// 	foreach($request->sample_details['sample_code'] as $k=>$v){
	// 		$detailId = $request->sample_details['detail_header'][$k];

	// 		$detail = gettype($detailId) == 'integer' ? SampleDetails::find($detailId) : new SampleDetails;
	// 		$detail->sample_header_id = $header->id;

	// 		if(gettype($detailId) != 'integer'){
	// 			$detail->sample_code = getNamingConventionCode("Samples", false, $codePrefix);
	// 		}

	// 		$detail->analysis_type_id =implode(',', $request->sample_details['sample_analysis'][$k]);
	// 		$detail->sample_condition_id = $request->sample_details['sample_condition'][$k];
	// 		$detail->sample_point_id = $request->sample_details['sample_point'][$k];
	// 		$detail->company_product_id = $request->sample_details['product'][$k];
	// 		$detail->barcode = $request->sample_details['barcode'][$k];
	// 		$detail->comments = $request->sample_details['comments'][$k];
	// 		$detail->gps = $request->sample_details['gps'][$k];

	// 		$detail->photo_url = null;
	// 		$detail->save();

	// 	}

	//   return redirect()->back()->within('success', 'Sample details added.');
	// }

	public function show(Request $request,$batch, $client = false, $portal = false, $status = false)
	{
		$batchID = $batch;

		$batch = SampleHeader::with('comments.creator', 'samples.sample_detail_lab', 'captured_results.my_analyte', 'captured_results.defacto_analyst_with', 'captured_results.sample')->find($batchID);
		if(isset($batch->id) && $batch->crm_unit_id < 1){
			$crm_unit = CRMCompanyUnit::where('crm_customer_id',$batch->crm_customer_id)->where('name',$batch->crm_unit_name)->first();
			$batch->crm_unit_id = isset($crm_unit->id) ? $crm_unit->id : $batch->crm_unit_id;
			// return response()->json($batch);
			$batch->save();
		}
		$section_approvers_users = isset($batch->id) ? LabSectionApproverRelationShip::whereIn('lab_section_id', explode(',', $batch->lab_section_ids))->get() : []; 
		$receiving_role = SystemConfiguration::where('key','receiving_role_id')->first();
		// $test =  UserRole::where('role_id',isset($receiving_role->value) ? $receiving_role->value : 0)->get();
		// return response()->json($test);
		$recieving_users = UserRole::where('role_id',isset($receiving_role->value) ? $receiving_role->value : 0)->join('users as u','u.id','=','user_roles.user_id')->where('u.is_support_staff', 0)->selectRaw('u.*')->get();

		$batch_scope = SystemConfiguration::where('key', 'batch_scope')->first();
		$customer_survey = SystemConfiguration::where('key', 'customer_survey')->first();
		$countries = Country::orderBy('name')->get();
		$methods = AnalysisMethod::where('active', 1)->get();
		$account_settings = getConfigTypeByName('Account Settings');
		$atachment_type = SystemConfiguration::where('key', 'attachment_type')->get();
		$users = User::where('is_client', 0)->where('supplier_id', 0)->where('active', 1)->get();
		$labsections = SampleAnalysisStage::where('active', 1)->get();
		$reportingUnits = getReportingUnits();
		// return response()->json($reportingUnits);
		$conditions = SampleCondition::where('active', 1)->get();
		$products = CompanyProduct::all();
		$workflowstages = [];
		$workflows = getSampleWorflowStages();
		$sample_types = getSampleTypes();
		$samplingmethods = getSamplingMethods();
		$interlabs = [];
		$disposal_date = '';
		if (isset($account_settings->id)) {
			$accounts = getconfigByID($account_settings->id);
		} else {

			$accounts = array();
		}
		$not_captured = [];
		$payment_detail = [];
		$contacts = [];
		$batch_sample_codes = '';
		$report_formats = [];
		$approvers = [];
		$headerDetails = isset($batch->id) ? $batch->report_header_details() : array();
		$ammendments = isset($batch->id) ? getBatchAmmendmentsById($batch->id) : [];
		$allsamples = isset($batch->id)  ? $batch->all_samples()  : [];

		if (isset($batch->id)) {
			$workflowstages = getWorkflowStage_Stages($batch->status);
			if (in_array($batch->status, ['Sample Verification', 'Sample Approval'])) {
				$report_format_config = SystemConfiguration::where('key', 'coa_report_format')->first();
				$report_formats = SystemConfiguration::where('configuration_type_id', $report_format_config->value)->get();
			}
			$disposal_date =  \Carbon\Carbon::parse($batch->receipt_date)->addMonths(3)->format('Y-m-d');
			// return response()->json($disposal_date);
			$contacts = getCrmCustomerContactSchedule($batch->crm_customer_id);
			// return response()->json($contacts);
			$batch_sample_codes = getBacthSampleCodes($batch->id);
			$payment_detail = InvoicePaymentDetail::where('batch_id', $batch->id)->get();
			$interlabs = InterLabLogView::where('sample_header_id', $batch->id)->orderBy('status', 'ASC')->orderBy('id', 'DESC')->get();
			// $equipment_data = $batch->get_captured();
			$approvers = BatchLabSectionApprover::where('batch_id', $batch->id)->get();
			// foreach ($equipment_data['items'] as $b => $d) {
			// 	foreach ($d as $a => $k) {
			// 		foreach ($k as $i => $e) {

			// 			if ($e == '') {
			// 				if (!isset($not_captured[$b])) {
			// 					$not_captured[$b] = array();
			// 					array_push($not_captured[$b], $a);
			// 				} else {
			// 					array_push($not_captured[$b], $a);
			// 				}
			// 			}
			// 		}
			// 	}
			// }
			$not_captured = CapturedResult::where('sample_header_id', $batch->id)->whereNull('result')->selectRaw('group_concat(analyte_code) as codes,sample_detail_code')->groupBy('sample_detail_id')->get();
			// return response()->json($test);
		}

		$selectedSampleType = \App\SampleType::find($batch->sample_type_id ?? 0) ?? false;
		$selected_analysis_types = isset($batch->sample_type_id) ? $selectedSampleType->analysis_types : [];
		if (isset($batch->id)) {
			if ($batch->is_qc_batch) {
				$standards = Standards::where('status', 1)->where('qc_type_id', $batch->qc_type_id)->get();
			} else {
				$standards =  Standards::where('status', 1)->get();
			}
		} else {
			$standards = [];
		}
		$defaultClient = $client > 0 ? $client : false;
		$client_portal = $portal > 0 ? $portal : false;
		if (isset($batch->id)) {
			$attachments = BatchAttachment::where('batch_id', $batch->id)->get();

			if ($batch->in_ammendment_proccess == 1) {

				$ammendment = BatchAmmendment::where('batch_id', $batch->id)->where('version_number', $batch->is_amendment)->first();
				// return response()->json($batch,200);
				if (isset($ammendment->id)) {

					$samples = json_decode($ammendment->samples, true);
					$ammendable = array_keys($samples);
				}
				// return response()->json($ammendments,200);


			} else {

				$ammendable = $batch->all_samples()->pluck('sample_code');
			}
		} else {
			$ammendable = array();
			$attachments = [];
			// return response()->json($ammendable,200);
		}

		$role_a = SystemConfiguration::where('key', 'analyst_role_id')->first();
		$labs  = Lab::where('active', 1)->get();
		// $analysts = getUsersByRole('Analyst');
		$analysts = User::orderBy('name')->join('user_roles as ur', 'ur.user_id', '=', 'users.id')
			->join('roles as r', 'r.id', '=', 'ur.role_id')
			->where('r.id', $role_a->value)->where('users.active', 1)->where('users.is_support_staff', 0)->selectRaw('users.*')->get();

		// -----------------------------------


		$analaytesHolder = array();
		$analysisBySample = array();
		$analysisBySampleNames = array();
		$labSamples = array();

		// echo date('Y-m-d H:i:s');
		$l = 1;

		// return response()->json($batch);

		$methods = getMethods()->pluck('name', 'id');

		// return response()->json($methods);

		foreach ($batch->captured_results ?? array() as $item) {
			// return response()->json($item);
			if (!isset($analaytesHolder[$item->sample_detail_code])) {
				$analaytesHolder[$item->sample_detail_code] = array();
			}

			// $item->ops = $analysts;
			$item->equip_name = $item->equipment()->name ?? '-';

			$item->def_operator = $item->defacto_analyst_with;
			$item->analysis_type = $item->analysis_type;
			$analyte = $item->my_analyte;

			if (isset($analyte->id)) {

				$item->analyte_name = $analyte->name;
			} else {
				$item->analyte_name = $item->analyte_code;
			}
			$getLimitMeasure = [
				"Max" => "Max",
				"Min" => "Min",
				"greater_than" => ">",
				"less_than" => "<"
			];
			$item->methods = $this->methodNameFromId($methods, $analyte->method);
			$lab_section = SampleAnalysisStage::find($item->lab_section_id);
			// !isset($analaytesHolder[$item->sample_detail_code]) ? $analaytesHolder[$item->sample_detail_code] = [] : '';
			// isset($lab_section->id) && !isset($analaytesHolder[$item->sample_detail_code][$item->lab_section_id]) ? $analaytesHolder[$item->sample_detail_code][$item->lab_section_id] = [] : '';
			
			$item->lab_section_id > 0 ? $analaytesHolder[$item->sample_detail_code][$item->lab_section_id]['section'] = $lab_section->name : $analaytesHolder[$item->sample_detail_code]['000']['section'] = 'Not Set';

			$item->lab_section_id > 0 ? $analaytesHolder[$item->sample_detail_code][$item->lab_section_id]['cr'][] = $item : $analaytesHolder[$item->sample_detail_code]['000']['cr'][] = $item;
			$sample_details_test = $item->sample;
			$item->standard_limit_value = '';
			if (isset($sample_details_test->id)) {
				$standard = $sample_details_test->main_standard;
				$sec = $sample_details_test->secondary_standard;
				if ($standard != '') {
					// $analyte_standard = $item->analyte_standard_value;
					$analyte_standard = StandardAnalytes::where('standard_id', $standard)->where('analyte_id', $item->analyte_id)->first();
					if (isset($analyte_standard->id)) {
						if ($analyte_standard->standard_value_type == 'is_range') {
							$item->standard_value = $analyte_standard->low . ' - ' . $analyte_standard->high;
						} elseif ($analyte_standard->standard_value_type == 'is_standard_value') {
							$value_id = StandardValue::find($analyte_standard->standard_value_id);
							if (isset($value_id->id)) {
								if ($value_id->code == 'IsValue') {
									$item->standard_value = $analyte_standard->standard_is_value;
									$item->standard_limit_value = $analyte_standard->value_type;
								} else {
									$item->standard_value = $value_id->code;
								}
							}
						}
					} else {
						$item->standard_value = 'NS';
					}
					$item->main_standard = Standards::find($standard)->code ?? '';
				}
				if (isset($sec->id)) {
					$item->secondary_standard = Standards::find($sec)->code ?? '';
				}
			}
			// return response()->json($batch);
		}

		// return response()->json($analaytesHolder);

		foreach ($batch->samples ?? array() as $sample) {
			$labSamples[$sample->sample_code] = getSampleDetailsLab($sample->id);
			if (!isset($analysisBySample[$sample->sample_code])) {
				$analysisBySample[$sample->sample_code] = array();
			}
			$analysisBySample[$sample->sample_code] = array_merge(explode(",", $sample->analysis_type_id), $analysisBySample[$sample->sample_code]);

			foreach ($analysisBySample[$sample->sample_code] as $id) {
				$analysis = getAnalysisTypeID($id);
				if (!isset($analysisBySampleNames[$sample->sample_code])) {
					$analysisBySampleNames[$sample->sample_code] = [];
				}
				$analysisBySampleNames[$sample->sample_code][$analysis->name] = $analysis->id;
			}
		}
		// echo "Ending - ".date('Y-m-d H:i:s');
		$active_company = getActiveCompany();
		// return response()->json($analaytesHolder);
		// ---------------------------------------
		$userLabSections = auth()->user()->labsectionids;
		$customer = isset($batch->id) ? getCrmCustomerByID($batch->crm_customer_id) : [];
		$requestTypes = getRequestTypes();
		$notifiable_users  = getNotifiableUsers();
		$notesReminderType = getNotesReminderTypes();
		$clients = getClients();
		// return response()->json($analysts);
		return view('layouts.lab.sample-workflow.show', compact('batch', 'batchID', 'defaultClient', 'selectedSampleType', 'client_portal', 'ammendable', 'standards', 'attachments', 'not_captured', 'analysts', 'countries', 'accounts', 'methods', 'atachment_type', 'batch_scope', 'customer_survey', 'interlabs', 'labs', 'users', 'payment_detail', 'labsections', 'contacts', 'batch_sample_codes', 'report_formats', 'approvers', 'reportingUnits', 'conditions', 'products', 'headerDetails', 'analaytesHolder', 'analysisBySample', 'analysisBySampleNames', 'labSamples', 'workflowstages', 'workflows', 'sample_types', 'samplingmethods', 'active_company', 'ammendments', 'allsamples', 'selected_analysis_types', 'userLabSections', 'customer', 'requestTypes', 'notifiable_users', 'notesReminderType', 'clients', 'disposal_date', 'status','recieving_users','section_approvers_users'));
	}

	public function fetch_unit_stuff($name, $client)
	{
		$unit = CRMCompanyUnit::where('id', $name)->where('crm_customer_id', $client)->first();

		return response()->json(array(
			'products' => $unit->products,
			'sample_points' => $unit->sample_points
		), 200);
	}

	public function updateChainofCustody($data)
	{
		\App\ChainOfCustody::where('sample_header_id', $data['batch_id'])
			->whereNull('moved_out_date')->update([
				'moved_out_date' => \Carbon\Carbon::now(),
				'moved_out_by' => \Auth::user()->id,
				'comments' => $data['comments']
			]);

		$custody = new \App\ChainOfCustody;
		$custody->workflow_stage = $data['target']['status'];
		$custody->tracking_stage_id = $data['target']['tracking_stage'];
		$custody->moved_in_by = \Auth::user()->id;
		$custody->sample_header_id = $data['batch_id'];

		$custody->save();

		return true;
	}

	public function change_workflow_status(Request $request)
	{
		// return response()->json($request->all());
		if ($request->status == 'Samples Request Review') {
			if (isset($request->batch_code)) {
				foreach ($request->batch_code as $code) {

					$batch = Sampleheader::where('batch_code', $code)->first();
					if ($batch->status == 'Samples Reception' && $batch->customer_paid == 0 && $batch->begin_proccess == 0) {
						return redirect()->back()->with('error', 'The following batch has not being paid for!');
					}
				}
			}
		}
		if (isset($request->notification)) {
			$companyDetails = getCompanyDetails();
			// return response()->json($request->all,200);

			if (isset($request->batch_id)) {
				// return response()->json('test',200);
				$batch = Sampleheader::find($request->bacth_id);
				$responsibility = SystemConfiguration::where('key', $batch->status)->first();

				$users = JobDescription::where('config_id', $responsibility->id)->join('users', 'users.position', '=', 'job_designation_responsibility.job_id')->get('users.*');
				$message = 'Batch ' . $batch->batch_code . ' needs your attention - ' . $request->status . '.';

				$emails = $users->pluck('email')->toarray();
				$position = $users->pluck('position')->toarray();
				$position = array_unique($position);
				if (!in_array(auth()->user()->email, $emails)) {

					return redirect()->back()->with('error', 'Your are not allowed to perform this task!');
				}
				foreach ($users as $user) {
					$body = 'Hi ' . $user->name . ',<br><br><br>' . $message . '<br><br> Regards, <br><br>' . $companyDetails['name'];
					$subject = '[' . $companyDetails['name'] . '] Batch Notification';
					notify_user($body, $user->email, $subject);
				}

				$notification = new SystemNotifications();
				foreach ($position as $pos) {

					$notification->batchNotification($batch, $pos, $message, $request->status);
				}
			} elseif (isset($request->batch_code)) {
				$batchcodes = $request->batch_code;
				foreach ($batchcodes as $code) {
					$batch = Sampleheader::where('batch_code', $code)->first();


					$responsibility = SystemConfiguration::where('key', $batch->status)->first();

					$users = JobDescription::where('config_id', $responsibility->id)->join('users', 'users.position', '=', 'job_designation_responsibility.job_id')->get('users.*');
					$message = 'Batch ' . $code . ' needs your attention - ' . $request->status . '.';
					$emails = $users->pluck('email')->toarray();
					$position = $users->pluck('position')->toarray();
					$position = array_unique($position);
					if (!in_array(auth()->user()->email, $emails)) {
						return redirect()->back()->with('error', 'Your are not allowed to perform this task!');
					}
					foreach ($users as $user) {
						$body = 'Hi ' . $user->name . ',<br><br><br>' . $message . '<br><br> Regards, <br><br>' . $companyDetails['name'];
						$subject = '[' . $companyDetails['name'] . '] Batch Notification';
						notify_user($body, $user->email, $subject);
					}

					// return response()->json('test',200);
					$notification = new SystemNotifications();
					foreach ($position as $pos) {

						$notification->batchNotification($batch, $pos, $message, $request->status);
					}
				}
				// $body = 'Your a have a '
			}
		} else {
			if (isset($request->batch_code)) {

				foreach ($request->batch_code as $code) {

					$batch = Sampleheader::where('batch_code', $code)->first();

					$responsibility = SystemConfiguration::where('key', $batch->status)->first();

					$users = JobDescription::where('config_id', $responsibility->id)->join('users', 'users.position', '=', 'job_designation_responsibility.job_id')->get('users.*');
					$message = 'Batch ' . $code . ' needs your attention - ' . $request->status . '.';
					$emails = $users->pluck('email')->toarray();
					$position = $users->pluck('position')->toarray();
					$position = array_unique($position);
					$notification = new SystemNotifications();
					foreach ($position as $pos) {
						// return response()->json($request->status,200);
						if ($request->status != 'Samples Request Review') {

							$notification->batchNotification($batch, $pos, $message, $request->status);
						}
					}
				}
			} elseif (isset($request->batch_id)) {
				$batch = Sampleheader::find($request->batch_id);
				$responsibility = SystemConfiguration::where('key', $batch->status)->first();

				$users = JobDescription::where('config_id', $responsibility->id)->join('users', 'users.position', '=', 'job_designation_responsibility.job_id')->get('users.*');
				$message = 'Batch ' . $batch->batch_code . ' needs your attention - ' . $request->status . '.';

				$emails = $users->pluck('email')->toarray();
				$position = $users->pluck('position')->toarray();
				$position = array_unique($position);
				$notification = new SystemNotifications();
				foreach ($position as $pos) {

					$notification->batchNotification($batch, $pos, $message, $request->status);
				}
			}
		}
		$previousStatus = '';
		if ($request->status == "Samples Request Review" && $request->has('tracking_stage')) {

			$batch = Sampleheader::find($request->bacth_id);
			// return response()->json($batch,200);
			$previousStatus = $batch->status;
			$custodyDetails = array(
				"batch_id" => $request->bacth_id,
				"comments" => $request->comments,
				"current" => array(
					"status" => $batch->status,
					"tracking_stage" => $batch->sample_tracking_stage,
				),
				"target" => array(
					"status" => $request->status,
					"tracking_stage" => $request->tracking_stage,
				)
			);

			$requestTypes = $request->request_type_id;
			$requestTypes = array_diff($requestTypes, array("Other"));

			if ($request->has('other_type') && $request->other_reason) {
				$reason = new \App\RequestType;
				$reason->name = $request->other_type;
				$reason->visible = 0;
				$reason->save();

				$requestTypes = array_merge($requestTypes, [$reason->id]);
			}

			$batch->reason_for_submission = implode(",", $requestTypes);

			$batch->sample_tracking_stage = $request->tracking_stage;
			$batch->priority = $request->is_priority ?? 'Normal';
			$batch->save();



			$this->updateChainofCustody($custodyDetails);
			return redirect()->route('sample-workflow', ['status' => $previousStatus])->with('success', 'Approval was successful');
		}

		if ($request->status == "Samples In Lab") {

			if (isset($request->batch_code)) {
				$batch_codes = $request->batch_code;
				foreach ($batch_codes as $code) {
					$batch = Sampleheader::where('batch_code', $code)->get();
					$batch[0]->in_lab_date = date('Y-m-d');

					$previousStatus = $batch[0]->status;
					$custodyDetails = array(
						"batch_id" => $batch[0]->id,
						"comments" => $request->comments,
						"current" => array(
							"status" => $batch[0]->status,
							"tracking_stage" => $batch[0]->sample_tracking_stage,
						),
						"target" => array(
							"status" => $request->status,
							"tracking_stage" => $request->tracking_stage,
						)
					);

					$requestTypes = $request->request_type_id;
					$requestTypes = array_diff($requestTypes, array("Other"));

					if ($request->has('other_type') && $request->other_reason) {
						$reason = new \App\RequestType;
						$reason->name = $request->other_type;
						$reason->visible = 0;
						$reason->save();

						$requestTypes = array_merge($requestTypes, [$reason->id]);
					}
					$stages = $batch[0]->stages($request->status);
					if (!isset($stages[0]->id)) {
						return redirect()->back()->with('error', 'Kindly add Sample Analysis Stage to the sample type');
					}
					$returnstatus = $batch[0]->status;
					$batch[0]->status = $request->status;
					$batch[0]->sample_tracking_stage = $stages[0]->id;

					$batch[0]->reason_for_submission = implode(",", $requestTypes);
					$batch[0]->specialist_analyst_id = $request->specialist_analyst_id;
					$batch[0]->sample_tracking_stage = $request->tracking_stage;
					$batch[0]->priority = $request->is_priority ?? 'Normal';
					$batch[0]->save();

					$this->updateChainofCustody($custodyDetails);
				}

				return redirect()->route('sample-workflow', ['status' => $previousStatus])->with('success', 'Batches Successfully Moved to ' . $request->status);
			}
		} else if ($request->status = "Samples Request Review") {
			$strStage = "Sample Labeling";
			$samWk = "Samples Reception";
			$stage = SampleAnalysisStage::where('name', $strStage)->where('sample_workflow', $samWk)->first();
			$batch_codes = $request->batch_code;
			$previousStatus = "Samples Reception";



			$responsibility = SystemConfiguration::where('key', $previousStatus)->first();


			$users = JobDescription::where('config_id', $responsibility->id)->join('users', 'users.position', '=', 'job_designation_responsibility.job_id')->get('users.*');
			$companyDetails = getCompanyDetails();
			$position = $users->pluck('position')->toarray();
			$position = array_unique($position);
			$emails = $users->pluck('email')->toarray();
			// return response()->json($responsibility,200);
			if (!in_array(auth()->user()->email, $emails)) {
				return redirect()->back()->with('error', 'You are not allowed to perform this task!');
			}
			// return response()->json($batch_codes,200);
			foreach ($batch_codes as $code) {

				$batch = SampleHeader::where('status', $samWk)->where('sample_tracking_stage', $stage->id)->where('batch_code', $code)->first();

				if (!isset($batch->id)) {
					return redirect()->back()->with('error', 'No label for batch ' . $code . '.');
				}
				// return response()->json('test',200);
				if (($batch->current_account_status == 'Account Holder(Overdue)' || $batch->current_account_status == 'Pay Upfront') && ($batch->begin_proccess == 0)) {
					return redirect()->back()->with('error', 'Please check account status - ' . $batch->current_account_status);
				}
				$stages = $batch->stages($request->status);
				$batch->status = $request->status;
				$batch->sample_tracking_stage = '20007';
				$message = 'Batch ' . $batch->batch_code . ' needs your attention - ' . $request->status . '.';
				if (isset($request->notification)) {
					$emails = $users->pluck('email')->toarray();


					foreach ($users as $user) {
						$body = 'Hi ' . $user->name . ',<br><br><br>' . $message . '<br><br> Regards, <br><br>' . $companyDetails['name'];
						$subject = '[' . $companyDetails['name'] . '] Batch Notification';
						notify_user($body, $user->email, $subject);
					}
				}

				$notification = new SystemNotifications();
				foreach ($position as $pos) {

					$notification->batchNotification($batch, $pos, $message, $request->status);
				}



				$custodyDetails = array(
					"batch_id" => $batch->id,
					"comments" => $request->comments ?? '',
					"current" => array(
						"status" => $batch->status,
						"tracking_stage" => $batch->sample_tracking_stage,
					),
					"target" => array(
						"status" => $request->status,
						"tracking_stage" => '20007',
					)
				);
				$this->updateChainofCustody($custodyDetails);

				$batch->save();
			}
			// return response()->json($batches,200);

		}

		if (isset($request->send_message)) {
			$responsibility = SystemConfiguration::where('key', $batch->status)->first();
			$users = JobDescription::where('config_id', $responsibility->id)->join('users', 'users.position', '=', 'job_designation_responsibility.job_id')->get('users.*');

			$numbers = $users->pluck('phone')->toarray();
			$message = 'Batch ' . $batch->batch_code . ' needs your attention - ' . $request->status . '.';
			foreach ($numbers as $num) {
				$send = sendTextMessage($num, $message);
				if ($send == 'error') {
					return redirect()->back()->with('error', 'Kindly add the sms configurations in the configurations section!');
				}
			}
		}

		// return;

		return redirect()->route('sample-workflow', ['status' => $previousStatus])->with('success', 'Batches Successfully Moved to ' . $request->status);
	}

	public function move_to_stage(Request $request, $stage, $batch_id)
	{
		$batch = SampleHeader::find($batch_id);

		$custodyDetails = array(
			"batch_id" => $batch_id,
			"comments" => $request->comments ?? '',
			"current" => array(
				"status" => $batch->status,
				"tracking_stage" => $batch->sample_tracking_stage,
			),
			"target" => array(
				"status" => $batch->status,
				"tracking_stage" => $stage,
			)
		);

		$this->updateChainofCustody($custodyDetails);

		$batch->sample_tracking_stage = $stage;
		$batch->save();

		return redirect()->back()->with('success', 'Batch move was successful');
	}

	public function move_to_workflow(Request $request, $status, $batch_id)
	{
		$batch = SampleHeader::find($batch_id);
		if (in_array($batch->status, ['Sample Verification', 'Sample Approval', 'Reports for Collection', 'Reports In Payment']) && in_array($status, ['Samples In Lab', 'Samples Reception', 'Samples Request Review', 'Sample Verification'])) {
			$batch->approve_user_id = '';
			$batch->verify_user_id = $status != 'Sample Verification' ? '' : $batch->verify_user_id;
			$batch->report_verified_date = '';
			$batch->approval_date = '';
			$batch->save();
			$status != 'Sample Verification' ? BatchLabSectionApprover::where('batch_id', $batch->id)->delete() : '';
			$status == 'Sample Verification' ? BatchLabSectionApprover::where('batch_id', $batch->id)->update(['status' => 0, 'approval_date' => '']) : '';
			BatchLabSectionApprover::where('batch_id', $batch->id)->where('batch_status', 'Sample Approval')->delete();
		}
		if (in_array($batch->status, ['Samples In Lab', 'Samples Reception', 'Samples Request Review', 'Sample Verification']) && in_array($status, ['Sample Approval', 'Reports for Collection', 'Reports In Payment']) && !isset($request->is_approval)) {
			return redirect()->back()->with('error', 'Kindly send the batch for verification');
		}
		if (in_array($batch->status, ['Sample Approval']) && !isset($request->is_approval) && in_array($status, ['Reports for Collection', 'Reports In Payment']) && $batch->approve_user_id < 0) {

			return redirect()->back()->with('error', 'Kindly approve the batch!');
		}
		if ($batch->status == 'Samples Reception' && $status == 'Samples Request Review' && $batch->customer_paid == 0 && $batch->begin_proccess == 0) {
			return redirect()->back()->with('error', 'The following batch has not being paid for!');
		}
		// return response()->json('test');
		$previousWorkflow = $batch->status;

		$custodyDetails = array(
			"batch_id" => $batch_id,
			"comments" => $request->comments ?? '',
			"current" => array(
				"status" => $batch->status,
				"tracking_stage" => $batch->sample_tracking_stage,
			),
			"target" => array(
				"status" => $status,
				"tracking_stage" => $batch->sample_tracking_stage,
			)
		);

		$this->updateChainofCustody($custodyDetails);
		if ($batch->status == 'Samples In Lab' && $status == 'Sample Verification') {
			if (isset($request->approver_id)) {
				$batch->verify_user_id = $request->approver_id;
			}
		}
		if ($batch->status == 'Sample Verification' && $status == 'Sample Approval') {
			$batch->report_verified_date = getTodayDate();
			if (isset($request->approver_id)) {
				$batch->approve_user_id = $request->approver_id;
				$batch->verify_user_id = auth()->user()->id;
			}
			// $batch->approve_user_id = auth()->user()->id;
		}
		if ($status == 'Reports for Collection') {
			$customer = getCrmCustomerByID($batch->crm_customer_id);
			$account_status = SystemConfiguration::find($customer->account_status);
			if (isset($account_status->id)) {
				if ($account_status->value != 'Account Holder(OK)') {
					// if ($batch->invoice_id == 0) {
					// 	return redirect()->back()->with('error', 'The following Batch has no invoice attached to it!');
					// }
				}
			}
		}
		if ($batch->status == 'Samples In Lab') {
			$fail = SystemConfiguration::where('key', 'lab_report_comment_fail')->first();
			$pass = SystemConfiguration::where('key', 'lab_report_comment_pass')->first();
			if (!isset($fail->id) && !isset($pass->id)) {
				return redirect()->back()->with('error', 'Kindly add the PASS and FAIL lab report comments on system configurations.');
			}
			$fail_arr = explode('_', $fail->value);

			$pass_arr = explode('_', $pass->value);

			$sample_type = SampleType::find($batch->sample_type_id);
			$samples = SampleDetails::where('sample_header_id', $batch->id)->get();
			// return response()->json($samples,200);   
			foreach ($samples as $sample) {
				$captured = CapturedResult::where('sample_detail_id', $sample->id)->where('remark', 'FAIL')->get();
				$analytes = [];
				foreach ($captured as $ca) {
					$a = Analyte::find($ca->analyte_id);
					// return response()->json($a->name,200);
					array_push($analytes, $a->name);
				}
				$main_s = Standards::find($sample->main_standard);
				if (sizeof($analytes) > 0) {
					$message = $fail_arr[0] . ' ' . $sample_type->name . ' (' . $main_s->name . ')' . $fail_arr[4] . ' ' . implode(', ', $analytes) . ' ' . $fail_arr[5];
					$sample->header_body = $message;
				} else {
					$message = $pass_arr[0] . ' ' . $sample_type->name . ' (' . $main_s->name . ')';
					$sample->header_body = $message;
				}
				$sample->save();
			}
		}

		$batch->status = $status;
		$batch->save();

		if (isset($request->send_message)) {
			$responsibility = SystemConfiguration::where('key', $batch->status)->first();
			if (isset($responsibility->id)) {
				$users = JobDescription::where('config_id', $responsibility->id)->join('users', 'users.position', '=', 'job_designation_responsibility.job_id')->get('users.*');

				$numbers = $users->pluck('phone')->toarray();
				$message = 'Batch ' . $batch->batch_code . ' needs your attention - ' . $request->status . '.';
				foreach ($numbers as $num) {
					$send = sendTextMessage($num, $message);
					if ($send == 'error') {
						return redirect()->back()->with('error', 'Kindly add the sms configurations in the configurations section!');
					}
				}
			}
		}

		if (isset($request->notification)) {
			$companyDetails = getCompanyDetails();
			// return response()->json($request->all,200);




			$responsibility = SystemConfiguration::where('key', $batch->status)->first();

			if ($batch->status == 'Samples In Lab') {
				$batch->verification_email_date = getTodayDate();
			} elseif ($batch->status == 'Sample Verification') {
				$batch->approval_email_date = getTodayDate();
			}
			if (isset($request->type) && $request->type == 'Recheck') {
				$message = 'Batch ' . $batch->batch_code . ' needs a recheck - Samples In Lab.';
			} else {

				$message = 'Batch ' . $batch->batch_code . ' needs your attention - ' . $request->status . '.';
			}

			if (isset($responsibility->id)) {

				$users = JobDescription::where('config_id', $responsibility->id)->join('users', 'users.position', '=', 'job_designation_responsibility.job_id')->get('users.*');

				$emails = $users->pluck('email')->toarray();
				$position = $users->pluck('position')->toarray();

				$position = array_unique($position);
				if (!in_array(auth()->user()->email, $emails)) {
					return redirect()->back()->with('error', 'You are not allowed to perform this task!');
				}
				$active_company = getActiveCompany();
				if (!isset($request->type)) {



					foreach ($users as $user) {
						$body = 'Hi ' . $user->name . ',<br><br><br>' . $message . '<br><br> Regards, <br><br>' . $active_company->name;
						$subject = '[' . $active_company->name . '] Batch Notification';
						notify_user($body, $user->email, $subject);
					}
				}

				$notification = new SystemNotifications();
				foreach ($position as $pos) {

					$notification->batchNotification($batch, $pos, $message, $request->status);
				}
			}
		} else {
			if (isset($request->batch_code)) {

				foreach ($request->batch_code as $code) {

					$batch = Sampleheader::where('batch_code', $code)->first();

					$responsibility = SystemConfiguration::where('key', $batch->status)->first();
					if (isset($responsibility->id)) {

						$users = JobDescription::where('config_id', $responsibility->id)->join('users', 'users.position', '=', 'job_designation_responsibility.job_id')->get('users.*');
						// return response()->json($users,200);
						$message = 'Batch ' . $code . ' needs your attention - ' . $request->status . '.';
						$emails = $users->pluck('email')->toarray();
						$position = $users->pluck('position')->toarray();
						$position = array_unique($position);
						$notification = new SystemNotifications();

						foreach ($position as $pos) {

							$notification->batchNotification($batch, $pos, $message, $request->status);
						}
					}
				}
			} elseif (isset($request->batch_id)) {
				$batch = Sampleheader::find((int)$request->batch_id);

				$responsibility = SystemConfiguration::where('key', $batch->status)->first();
				if (isset($responsibility->id)) {
					$users = JobDescription::where('config_id', $responsibility->id)->join('users', 'users.position', '=', 'job_designation_responsibility.job_id')->get('users.*');
					// return response()->json($responsibility,200);
					$message = 'Batch ' . $batch->batch_code . ' needs your attention - ' . $request->status . '.';

					$emails = $users->pluck('email')->toarray();
					$position = $users->pluck('position')->toarray();
					$position = array_unique($position);
					$notification = new SystemNotifications();
					foreach ($position as $pos) {

						$notification->batchNotification($batch, $pos, $message, $request->status);
					}
				}
			}
		}




		if (isset($request->type) && $request->type == 'Recheck') {
			$comment = new BatchComment();
			$comment->created_by = \Auth::user()->id;
			$comment->personnel_to_cc = $request->has('followers') ? implode(",", $request->followers) : 0;
			$comment->reminder_for = $request->user_id;
			$comment->comments = $request->comments;
			$comment->comment_type = $request->type;
			$comment->sample_header_id = $request->batch_id;
			$comment->save();

			$companyDetails = getCompanyDetails();
			$batch = getSampleHeaderByID($request->batch_id);
			$active_company = getActiveCompany();

			if ($comment->personnel_to_cc != 0) {
				$contacts = explode(',', $comment->personnel_to_cc);
				array_push($contacts, $comment->reminder_for);
				foreach ($contacts as $contact) {
					$user = getUserById((int)$contact);
					$message = 'There is a new note for batch ' . $batch->batch_code . '.<br><br> Kindly review the notes.';
					$body = 'Hi ' . $user->name . ',<br><br>'
						. $message . '<br>
							Regards, <br>'
						. $active_company->name . ' ';
					$subject = '[' . $companyDetails['name'] . '] Batch Recheck Notification';
					$notify = notify_user($body, $user->email, $subject);
				}
			} else {
				$user = getUserById($comment->reminder_for);
				$message = 'There is a new note for batch ' . $batch->batch_code . '. Kindly review the notes.';
				$body = 'Hi ' . $user->name . ',<br><br>'
					. $message . '<br>
						Regards, <br><br>'
					. $companyDetails['name'] . ' ';
				$subject = '[' . $companyDetails['name'] . '] Batch Recheck Notification';
				$notify = notify_user($body, $user->email, $subject);
			}
		}

		return redirect()->route('sample-workflow', ['status' => $previousWorkflow])->with('success', 'Batch move was successful');
	}

	public function missing_analysis_parameters_by_sample_code(Request $request)
	{
		$analytes = array();
		$ids = json_decode($request->id, true);
		// return json_encode(DB::table('captured_results')->where('analysis_type_id', 10008)->pluck('analyte_id'));
		foreach ($ids as $id) {
			$elements = AnalysisElements::join('analytes as a', 'a.id', '=', 'analysis_elements.analyte_id')
				->join('analysis_types as at', 'at.id', '=', 'analysis_elements.analysis_type_id')
				->leftJoin('users as u', 'u.id', '=', 'analysis_elements.operator_id')
				->leftJoin('equipment as e', 'e.id', '=', 'analysis_elements.equipment_id')
				->leftjoin('captured_results as cr', 'cr.analyte_id', '=', 'analysis_elements.analyte_id')
				->selectRaw('DISTINCT a.id, a.name, a.code, analysis_elements.reporting_unit, analysis_elements.reporting_symbol, e.name as equipment, e.id as equipment_id, u.name as operator, at.name as analysis_type, at.id as analysis_type_id,cr.analyte_status_contracted as analyte_status')
				->whereNotIn(
					'analysis_elements.analyte_id',
					DB::table('captured_results')->where('sample_detail_code', $request->sample)->where('analysis_type_id', $id)
						->pluck('analyte_id')
				)

				->where('analysis_elements.active', 1)
				->where('analysis_elements.analysis_type_id', $id)->get()->toArray();

			$analytes = array_merge($analytes, $elements);
		}
		// return response()->json($analytes,200);
		return $analytes;
	}

	public function add_analyte_to_sample_analysis(Request $request)
	{
		$item = $request->item;

		if (!$item || count($item) == 0) {
			return redirect()->back()->with('error', 'No analytes to be added were selected.');
		}

		// return json_encode($item);

		foreach ($item as $i) {
			$i = json_decode($i);

			$sampleDetail = SampleDetails::where('sample_code', $i->sample_code)->first();
			$analysisType = AnalysisElements::where('analysis_type_id', $i->analysis_type_id)
				->where('analyte_id', $i->id)->first();

			$captured = CapturedResult::where('sample_detail_code', $i->sample_code)
				->where('sample_detail_id', $sampleDetail->id)
				->where('analyte_id', $i->id)
				->where('analysis_type_id', $i->analysis_type_id)
				->where('sample_header_id', $sampleDetail->sample_header_id)->first()  ?? new CapturedResult;
			$captured->sample_detail_code = $i->sample_code;
			$captured->sample_detail_id = $sampleDetail->id;
			$captured->sample_header_id = $sampleDetail->sample_header_id;
			$captured->analyte_id = $i->id;
			$captured->analysis_type_id = $i->analysis_type_id;
			$captured->analyte_code = $i->code;
			$captured->equipment_id = $i->equipment_id;
			$captured->method_id = $analysisType->method;
			$batch = getSampleHeaderByID($sampleDetail->sample_header_id);
			$labstr = implode(',', $batch->labs(true));
			$labarr = explode(' - ', $labstr);
			$lab = Lab::where('code', $labarr[0])->where('name', $labarr[1])->first();
			$captured->analyte_status_contracted = $lab->is_external ?? 0;

			$captured->user_id = \Auth::user()->id;

			$captured->save();

			$result = Result::where('sample_detail_code', $i->sample_code)
				->where('sample_detail_id', $sampleDetail->id)
				->where('captured_result_id', $captured->id)
				->where('analyte_id', $i->id)
				->where('analysis_type_id', $i->analysis_type_id)
				->where('sample_header_id', $sampleDetail->sample_header_id)->first()  ?? new Result;
			$result->captured_result_id = $captured->id;
			$result->sample_detail_code = $i->sample_code;
			$result->sample_detail_id = $sampleDetail->id;
			$result->sample_header_id = $sampleDetail->sample_header_id;
			$result->analyte_id = $i->id;
			$result->analysis_type_id = $i->analysis_type_id;
			$result->analyte_code = $i->code;
			$result->unit_code = $i->reporting_unit;
			$result->reporting_symbol = $i->reporting_symbol;
			$result->analyte_status_contracted = $lab->is_external ?? 0;
			$result->recheck = 0;

			$result->save();
		}

		return redirect()->back()->with('success', 'Analyte has been added.');
	}
	public function send_payment_notification(Request $request)
	{
		$company = getActiveCompany();
		foreach ($request->batch_code as $code) {
			$batch = SampleHeader::where('batch_code', $code)->first();
			if (isset($batch->id)) {
				$customer = CRMCustomer::find($batch->crm_customer_id);

				if (isset($customer->id)) {
					$body = 'Hi ' . $customer->name . ',<br><br>This is a reminder of payment for batch ' . $batch->batch_code . ' from ' .					$company->name . '.<br><br>Kindly ignore this if you have already made the payment. <br><br> Regards,<br><br>' .							$company->name;
					$subject = '[' . $company->name . '] Payment Reminder Batch - ' . $batch->batch_code;
					$customer_contacts = CustomerContact::where('crm_customer_id', $customer->id)->where('receive_invoice', 1)->get();
					foreach ($customer_contacts as $contact) {
						notify_user($body, $contact->email, $subject);
					}
				}
			} else {
				return redirect()->back()->with('error', 'No Batch record with the specified code!');
			}
		}
		return redirect()->back()->with('success', 'Payment reminders sent successfully!');
	}

	public function capture_raw_results(Request $request)
	{
		$batchid = 0;

		// return response()->json($request->all(), 200);
		foreach ($request->captured_result_id as $cID) {
			$captured = CapturedResult::find($cID);

			if (isset($request->subcontracted[$cID])) {
				$captured->analyte_status_contracted = 1;
			} else {
				$captured->analyte_status_contracted = 0;
			}
			if (isset($request->accredited[$cID])) {
				// return response()->json($request->accredited[$cID]);
				$captured->analyte_accredited = 1;
			} else {
				// return response()->json('here '.$cID);
				$captured->analyte_accredited = 0;
			}
			$captured->result_reporting_symbol = $request->result_reporting_symbol[$cID];
			$captured->reporting_unit_id = $request->reporting_unit[$cID];
			$captured->measure_uncertanity = $request->measure_uncertanity[$cID]
			$captured->method_id = $request->method_id[$cID];
			$captured->result = $request->result[$cID];
			$captured->result_reporting_symbol = $request->result_reporting_symbol[$cID];
			$captured->operator_id = $request->operators[$cID] ?? 0;
			$captured->analyte_code = Analyte::find($captured->id)->code;

			$captured->remark = $captured->remark_is_manual == 0 ? $request->remark[$cID] : $request->remarkmanual[$cID];
			$standard_main = Standards::where('code', $request->main_standard[$cID])->first();
			$sec_standard = Standards::where('code', $request->secondary_standard[$cID])->first();
			if (isset($standard_main->id)) {
				$main_standard_analyte = StandardAnalytes::where('analyte_id', $captured->analyte_id)->where('standard_id', $standard_main->id)->first();
				$sec_standard_analyte = isset($sec_standard->id) ? StandardAnalytes::where('analyte_id', $captured->analyte_id)->where('standard_id', $sec_standard->id)->first() : '';
			} else {
				return redirect()->back()->with('error', 'Kindly set Main and Secondary standard for tghe following sample!');
			}
			// return response()->json($captured->analyte_id);
			$captured->main_standard_id = isset($main_standard_analyte->id) ? $main_standard_analyte->id : 0;
			$captured->secondary_standard_id = isset($sec_standard_analyte->id)   ? $sec_standard_analyte->id :  0;
			// return response()->json($request);
			$captured->main_value = $request->main_value[$cID];
			if (isset($sec_standard_analyte->id)) {
				if ($sec_standard_analyte->standard_value_type == 'is_range') {
					$captured->secondary_value = $sec_standard_analyte->low . ' - ' . $sec_standard_analyte->high;
				} else {
					if ($sec_standard_analyte->standard_is_value == '') {
						$standard_value = StandardValue::find($sec_standard_analyte->standard_value_id);
						$captured->secondary_value = $standard_value->code;
					} else {
						$captured->secondary_value = $sec_standard_analyte->standard_is_value;
					}
				}
			} else {
				$captured->secondary_value = '-';
			}
			// return response()->json($captured,200);
			$captured->save();
			// return response()->json($captured);
			$batchid = $captured->sample_header_id;
			$batch = SampleHeader::find($batchid);
			$sample_detail = getSampleDetailById($captured->sample_detail_id);
			$sample_detail->ammendment_number = $batch->is_amendment;
			$sample_detail->save();

			// return response()->json($sample_detail,200);

		}
		$batch = SampleHeader::find($batchid);
		// return response()->json($batch->id,200);
		$strStage = 'Capture Results';
		$samWk = "Samples In Lab";
		$stage = SampleAnalysisStage::where('name', $strStage)->where('sample_workflow', $samWk)->first();

		$custodyDetails = array(
			"batch_id" => $batch->id,
			"comments" => $request->comments ?? '',
			"current" => array(
				"status" => $batch->status,
				"tracking_stage" => $batch->sample_tracking_stage,
			),
			"target" => array(
				"status" => $samWk,
				"tracking_stage" => $stage->id,
			)
		);
		$this->updateChainofCustody($custodyDetails);
		return redirect()->back()->with('success', 'Result details saved.');
	}
	public function fetch_results_remark(Request $request)
	{
		$data = explode(',', $request->sample_code);
		$sample_detail = SampleDetails::where('sample_code', $data[0])->first();
		$reporting_symbol = $request->reporting_symbol;
		$analyte = Analyte::find($data[3]);
		$standard = Standards::find($sample_detail->main_standard);
		$captured_result = CapturedResult::find($request->captured_result_id);
		if ($captured_result->repeat_captured_id > 0) {
			$result = $request->result;
			$range = explode(' - ', $captured_result->repeatsampleresult);
			if ($range[0] <= $result && $result <= $range[1]) {
				return response()->json('PASS', 200);
			} else {
				return response()->json('FAIL', 200);
			}
		}
		// $check_arr = ['NIL','ND',0];

		if (isset($standard->id) && isset($analyte->id)) {
			$result = $request->result;
			$analyte_guide = StandardAnalytes::where('analyte_id', $analyte->id)->where('standard_id', $standard->id)->first();
			if (isset($analyte_guide->standard_value_type)) {
				if ($analyte_guide->standard_value_type == 'is_range') {
					if ($analyte_guide->low <= $result && $result <= $analyte_guide->high) {
						return response()->json('PASS', 200);
					} else {
						return response()->json('FAIL', 200);
					}
				} else {
					$standard_value = StandardValue::find($analyte_guide->standard_value_id);
					if (is_numeric($result)) {
						$type = gettype($analyte_guide->standard_is_value);
						if ($type == 'integer' || $type == 'double') {
							if (trim($reporting_symbol) == '>') {
								if ($analyte_guide->value_type == 'Max' || $analyte_guide->value_type == '' || $analyte_guide->value_type == null) {

									$response =  $result <  floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
								}
								if ($analyte_guide->value_type == 'Min' || $analyte_guide->value_type == '') {

									$response =  $result >  floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
								}
								if ($analyte_guide->value_type == 'less_than') {
									$response = $result <  floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
								}
								if ($analyte_guide->value_type == 'greater_than') {
									$response = $result > floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
								}
							} elseif($reporting_symbol == '<'){
								if ($analyte_guide->value_type == 'Max' || $analyte_guide->value_type == '' || $analyte_guide->value_type == null) {

									$response = $result <  floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';;
								}
								if ($analyte_guide->value_type == 'Min' || $analyte_guide->value_type == '') {

									$response =  $result >  floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';;;
								}
								if ($analyte_guide->value_type == 'less_than') {
									$response = $result <  floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';;;
								}
								if ($analyte_guide->value_type == 'greater_than') {
									$response = $result >  floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';;;
								}
							} else {
								if ($analyte_guide->value_type == 'Max' || $analyte_guide->value_type == '' || $analyte_guide->value_type == null) {

									$response = $result <= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
								}
								if ($analyte_guide->value_type == 'Min' || $analyte_guide->value_type == '') {

									$response = $result >= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
								}
								if ($analyte_guide->value_type == 'less_than') {
									$response = $result < floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
								}
								if ($analyte_guide->value_type == 'greater_than') {
									$response = $result > floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
								}
							}
							return response()->json($response, 200);
						} else {
							if ($analyte_guide->standard_is_value != '') {
								if (trim($reporting_symbol) == '>') {
									if ($analyte_guide->value_type == 'Max' || $analyte_guide->value_type == '' || $analyte_guide->value_type == null) {

										$response =  $result <  floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
									}
									if ($analyte_guide->value_type == 'Min' || $analyte_guide->value_type == '') {
	
										$response =  $result >  floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
									}
									if ($analyte_guide->value_type == 'less_than') {
										$response = $result <  floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
									}
									if ($analyte_guide->value_type == 'greater_than') {
										$response = $result > floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
									}
								} elseif($reporting_symbol == '<'){
									if ($analyte_guide->value_type == 'Max' || $analyte_guide->value_type == '' || $analyte_guide->value_type == null) {

										$response = $result <  floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';;
									}
									if ($analyte_guide->value_type == 'Min' || $analyte_guide->value_type == '') {
	
										$response =  $result >  floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';;;
									}
									if ($analyte_guide->value_type == 'less_than') {
										$response = $result <  floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';;;
									}
									if ($analyte_guide->value_type == 'greater_than') {
										$response = $result >  floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';;;
									}
								} else {
									if ($analyte_guide->value_type == 'Max' || $analyte_guide->value_type == '') {

										$response = $result <= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
									}
									if ($analyte_guide->value_type == 'Min' || $analyte_guide->value_type == '') {

										$response = $result >= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
									}
									if ($analyte_guide->value_type == 'less_than') {
										$response = $result < floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
									}
									if ($analyte_guide->value_type == 'greater_than') {
										$response = $result > floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
									}
								}

								return response()->json($response, 200);
							} else {
								if (strtoupper(trim($standard_value->code)) == "NS") {
									$response = '-';
									return response()->json($response, 200);
								} elseif (strtoupper(trim($standard_value->code)) == "NIL") {
									$response = $result <= 0  ? 'PASS' : 'FAIL';
									return response()->json($response, 200);
								} elseif (strtoupper(trim($standard_value->code)) == "ND") {
									$response = $result <= 0  ? 'PASS' : 'FAIL';
									return response()->json($response, 200);
								} else {
									$response = '-';
									return response()->json($response, 200);
								}
							}
							// $eresult->remarks = trim($analyte_guide->standard_is_value) == "" ? "PASS" : "++";

						}
					} else {
						// return response()->json($analyte_guide,200);
						$standard_value = StandardValue::find($analyte_guide->standard_value_id);
						if (strtoupper($result) == 'TN') {
							$response = 'FAIL';
							return response()->json($response, 200);
						} elseif (strtoupper($result) == 'ND' && strtoupper(trim($standard_value->code)) == "NS") {
							$response = '-';
							return response()->json($response, 200);
						} elseif (strtoupper($result) == 'ND' && strtoupper(trim($standard_value->code)) == "NIL") {
							$response = 'PASS';
							return response()->json($response, 200);
						} elseif (strtoupper($result) == 'ND' && strtoupper(trim($standard_value->code)) == "ND") {
							$response = 'PASS';
							return response()->json($response, 200);
						} elseif (strtoupper($result) == 'NIL' && strtoupper(trim($standard_value->code)) == "ND") {
							$response = 'PASS';
							return response()->json($response, 200);
						} else {
							$response = '-';
							return response()->json($response, 200);
						}
					}
				}
			} else {
				return response()->json('-', 200);
			}

			// if (isset($analyte_guide->id)) {
			// 	if ($analyte_guide->standard_value_type == 'is_range') {
			// 		if (floatval($analyte_guide->low )<= $result && $result <= floatval($analyte_guide->high)) {
			// 			return response()->json('PASS', 200);
			// 		} else {
			// 			return response()->json('FAIL', 200);
			// 		}
			// 	} elseif ($analyte_guide->standard_value_type == 'is_standard_value') {
			// 		$standard_value = StandardValue::find($analyte_guide->standard_value_id);
			// 		if (isset($standard_value->id)) {
			// 			if ($standard_value->code == 'IsValue') {
			// 				if ($result <= $analyte_guide->standard_is_value) {
			// 					return response()->json('PASS', 200);
			// 				} else {
			// 					return response()->json('FAIL', 200);
			// 				}
			// 			} else {
			// 				if($standard_value->code == "NIL" || $standard_value->code == "ND"){
			// 					if(!is_numeric($result)){
			// 						$response = in_array($result,$check_arr) ? "PASS" : 'FAIL';
			// 						return response()->json($response,200);		
			// 					}else{

			// 					}
			// 				}
			// 				if($standard_value->code == 'NS'){
			// 					return '-';
			// 				}

			// 			}
			// 		}
			// 	}
			// } else {
			// 	return response()->json('-', 200);
			// }
		} else {
			return response()->json('-', 200);
		}
	}
	public function process_raw_results(Request $request, $batch_id, $internal = false)
	{
		$captured = CapturedResult::join('analysis_elements as ae', function ($join) {
			$join->on('ae.analyte_id', '=', 'captured_results.analyte_id');
			$join->on('ae.analysis_type_id', '=', 'captured_results.analysis_type_id');
		})
			->selectRaw('captured_results.result, captured_results.id, ae.decimal_places, ae.significant_figures, ae.reporting_unit, ae.lod, ae.level, ae.hod')
			->where('captured_results.sample_header_id', $batch_id)->whereNotNull('captured_results.result')
			->orderBy('level', 'asc')->get();

		$arr = array();


		foreach ($captured as $c) {
			$result = floatval($c->result);

			$eresult = Result::where('captured_result_id', $c->id)->first(); //result to process to
			$eresult->reporting_symbol = '';
			$eresult->unit_code = $c->reporting_unit;
			if ($c->lod && $result < floatval($c->lod)) {
				$result  = $c->lod;
				$eresult->reporting_symbol = '<';
			}

			if ($c->hod && $result > floatval($c->hod)) {
				$result  = $c->hod;
				$eresult->reporting_symbol = '>';
			}

			if (intval($c->significant_figures) && intval($c->significant_figures > 0)) {
				$result = sigFig($result, intval($c->significant_figures));
			} else {
				if (trim($c->decimal_places) != "") {
					$result = round($result, intval($c->decimal_places));
				}
			}

			$eresult->result = $result;
			$eresult->save();

			$arr[] = $eresult;
		}
		$header = SampleHeader::find($batch_id);
		$header->set_date("Processing Date", \Carbon\Carbon::now(), true);

		// return response()->json($arr, 200);

		if ($internal) {
			return $header;
		}

		return redirect()->back()->with('success', 'Results Processed saved.');
	}


	public function process_results(Request $request, $batch_id, $internal = false)
	{
		$report_format = $request->report_format;
		$resultsData = Result::where('sample_header_id', $batch_id)->get();
		foreach ($resultsData as $data) {
			$checkCaptured = CapturedResult::find($data->captured_result_id);
			if (!isset($checkCaptured->id)) {
				$data->delete();
			}
		}
		$captured = CapturedResult::join('analysis_elements as ae', function ($join) {
			$join->on('ae.analyte_id', '=', 'captured_results.analyte_id');
			$join->on('ae.analysis_type_id', '=', 'captured_results.analysis_type_id');
		})
			->leftJoin('analysis_guides as ag', function ($join) {
				$join->on('ag.analyte_id', '=', 'captured_results.analyte_id');
				$join->on('ag.analysis_type_id', '=', 'captured_results.analysis_type_id');
			})

			->selectRaw('captured_results.result,captured_results.main_standard_id,captured_results.secondary_standard_id,captured_results.remark,captured_results.analysis_type_id,captured_results.analyte_id,captured_results.analyte_status_contracted,captured_results.analyte_accredited, captured_results.id, ae.decimal_places, ae.significant_figures, ae.reporting_unit, ae.lod, ae.level, ae.hod,captured_results.result_reporting_symbol,captured_results.repeat_captured_id')
			->where('captured_results.sample_header_id', $batch_id)->whereNotNull('captured_results.result')
			->orderBy('level', 'asc')->get();

		$arr = array();

		// return response()->json($captured,200);
		foreach ($captured as $c) {
			$type = gettype($c->result);
			if ($type == 'integer' || $type == 'double') {

				$result = floatval($c->result);
			} else {
				$result = $c->result;
			}
			// return response()->json($result,200);


			$main_standard = StandardAnalytes::find($c->main_standard_id);
			$secondary_standard = StandardAnalytes::find($c->secondary_standard_id);

			// return response()->json($secondary_standard,200);
			$eresult = Result::where('captured_result_id', $c->id)->first(); //result to process to
			// return response()->json($eresult,200);
			$eresult->reporting_symbol = '';
			$eresult->analyte_status_contracted = $c->analyte_status_contracted;
			$eresult->analyte_accredited = $c->analyte_accredited;
			$eresult->unit_code = $c->reporting_unit;
			$eresult->result = $result;
			$eresult->guide = $c->main_value;
			$eresult->remarks = $c->remark;
			$eresult->seond_guide = $c->secondary_value;
			$eresult->result = $result;
			$eresult->reporting_symbol = $c->result_reporting_symbol;
			$eresult->save();

			$arr[] = $eresult;
		}
		$header = SampleHeader::find($batch_id);
		$header->set_date("Processing Date", \Carbon\Carbon::now(), true);
		if ($internal) {
			return $header;
		}
		// return response()->json($report_format);
		return redirect()->route('process-pdf-report', ['batch_id' => $batch_id, 'report_format' => $report_format]);
	}

	public function remove_analyte_from_captured_result(Request $request)
	{

		$this->refactorReportingTime($request->id);

		CapturedResult::find($request->id)->delete();

		Result::where('captured_result_id', $request->id)->delete();

		return json_encode(array(
			"status" => true
		));
	}
	private function refactorReportingTime($id)
	{
		$actual_c = CapturedResult::find($id);
		$analysis_type_ids = CapturedResult::where('sample_header_id', $actual_c->sample_header_id)->pluck('analysis_type_id')->toArray();
		$unique_typeIds = array_unique($analysis_type_ids);
		$analyte_ids = CapturedResult::where('sample_header_id', $actual_c->sample_header_id)->pluck('analyte_id')->toArray();
		$unique_analyteIds = array_unique($analyte_ids);

		$analysis_max_report_time = AnalysisType::whereIn('id', $unique_typeIds)->max('reporting_time');
		$analytes_max_report_time = AnalysisElements::whereIn('analysis_type_id', $unique_typeIds)->whereIn('analyte_id', $unique_analyteIds)->max('reporting_time');
		$maxReportingTime = $analysis_max_report_time > $analytes_max_report_time ? $analysis_max_report_time : $analytes_max_report_time;
		$targetDateStr = "Target Date";
		$targetDate = \App\SampleDate::where('sample_header_id', $actual_c->sample_header_id)->where('name', $targetDateStr)->first() ?? new \App\SampleDate;
		$targetDate->name = $targetDateStr;
		$targetDate->sample_header_id = $actual_c->sample_header_id;
		$sampleheader = getSampleHeaderByID($actual_c->sample_header_id);
		$targetDate->date = \Carbon\Carbon::parse($sampleheader->receipt_date)->addDays($maxReportingTime);
		$targetDate->save();
		return 'success';
	}


	public function send_report_email(Request $request)
	{
		if (!$request->has('sample_code')) {
			return redirect()->back()->with('error', 'No batch selected.');
		}

		if (!$request->has('contacts')) {
			return redirect()->back()->with('error', 'No customer contact selected.');
		}

		$mailData = array("batches" => $request->sample_code);
		// return response()->json($request->sample_code);

		$contact = CustomerContact::whereIn('id', $request->contacts)
			->selectRaw("first_name, middle_name, last_name, email")->get();

		$cArr = array();
		$company = getActiveCompany();
		$message = $request->email_body;
		foreach ($request->sample_code as $code) {
			$batch = SampleHeader::where('id', $code)->first();
			$customer = CRMCustomer::find($batch->crm_customer_id);
			$samples = SampleDetails::where('sample_header_id', $batch->id)->get();
			$start =  SampleDetails::where('sample_header_id', $batch->id)->first();
			$end =  SampleDetails::where('sample_header_id', $batch->id)->orderBy('id', 'DESC')->first();
			$previous = $batch->status;
			// return response()->json(['start'=>$start,'end'=>$end],200);
			foreach ($contact as $c) {

				$body = 'Dear Sir / Madam, <br><br>
				I hope this email finds you well. <br><br>
				We are pleased to let you know that the <b>' . $samples->count() . '</b> test reports are ready as attached..<br><br>'
					. $message . '<br><br>
				We are grateful for giving us an opportunity to be of service to you. <br><br>
				We look forward to more engagements in the future. <br><br>
				Should you have any questions or concerns please do not hesitate to contact us.<br><br>
				Regards, <br>
				
				' . $company->name;
				$subject = 'TEST REPORTS;' . $customer->name . ' - ' . $start->sample_code . ' - ' . $end->sample_code;
				$file = \storage_path() . '/app' . $batch->batch_report_url;
				$bcc = true;
				$notify = notify_user($body, $c->email, $subject, $file, $bcc);
			}
			$batch->email_date = getTodayDate();
			$batch->status = "Completed";
			$batch->save();

			$custodyDetails = array(
				"batch_id" => $batch->id,
				"comments" => 'Send out sample report to the client',
				"current" => array(
					"status" => $previous,
					"tracking_stage" => $batch->sample_tracking_stage,
				),
				"target" => array(
					"status" => $batch->status,
					"tracking_stage" => $batch->sample_tracking_stage,
				)
			);

			$this->updateChainofCustody($custodyDetails);
			// return response()->json($batch);
		}

		// return response()->json($cArr,200);



		// return $sendMail;

		return redirect()->back()->with('success', 'Reports sent out.');
	}

	public function certificate_analysis($id)
	{
		$sample = SampleHeader::find($id);
		$sample_details = SampleDetails::where('sample_header_id', $sample->id)->get();
		$customer = getCrmCustomerByID($sample->crm_customer_id);

		return view('layouts.lab.sample-workflow.certificateAnalysis', compact('sample', 'sample_details', 'customer'));
	}
	public function approve_batch($id)
	{
		$batch  = getSampleHeaderByID($id);
		if ($batch->is_qc_batch) {
			$results = Result::where('sample_header_id', $id)->get();
			foreach ($results as $r) {
				$qc_res = QcResults::where('result_id', $r->id)->first() ?? new QcResults();
				$qc_res->captured_result_id = $r->captured_result_id;
				$qc_res->sample_detail_code = $r->sample_detail_code;
				$qc_res->sample_detail_id = $r->sample_detail_id;
				$qc_res->sample_header_id = $r->sample_header_id;
				$qc_res->analyte_id = $r->analyte_id;
				$qc_res->analyte_code = $r->analyte_code;
				$qc_res->result = $r->result;
				$qc_res->guide = $r->guide;
				$qc_res->comments = $r->comments;
				$qc_res->recheck = $r->recheck;
				$qc_res->guide_low = $r->guide_low;
				$qc_res->guide_high = $r->guide_high;
				$qc_res->unit_code = $r->unit_code;
				$qc_res->status_code = $r->status_code;
				$qc_res->reporting_symbol = $r->reporting_symbol;
				$qc_res->correct_target = $r->correct_target;
				$qc_res->standard_target = $r->standard_target;
				$qc_res->recommendations = $r->recommendations;
				$qc_res->initial_result = $r->initial_result;
				$qc_res->initial_reporting_symbol = $r->initial_reporting_symbol;
				$qc_res->very_low_guide = $r->very_low_guide;
				$qc_res->very_high_guide = $r->very_high_guide;
				$qc_res->analysis_type_id = $r->analysis_type_id;
				$qc_res->seond_guide = $r->seond_guide;
				$qc_res->remarks = $r->remarks;
				$qc_res->analyte_status_contracted = $r->analyte_status_contracted;
				$qc_res->analyte_accredited = $r->analyte_accredited;
				$qc_res->result_id = $r->id;
				$qc_res->qc_scheme_id = $batch->qc_scheme_id;
				$qc_res->qc_type_id = $batch->qc_type_id;
				$qc_res->standard_value = $r->guide;
				$qc_res->save();
			}
		}
		if ($batch->verify_user_id == auth()->user()->id) {
			return redirect()->back()->with('error', 'You are not allowed to approve this batch');
		}
		$batch->approve_user_id = auth()->user()->id;
		$batch->approval_date = getTodayDate();
		$current_stage = $batch->status;

		$batch->save();

		if ($batch->is_qc_batch) {
			return redirect()->route('sample-workflow', ['status' => $current_stage])->with('success', 'Approval was successful');
		}


		return redirect()->back()->with('success', 'Batch approved successfully');
	}
	public function delete_batch(Request $request)
	{
		$codes = $request->batch_code;

		foreach ($codes as $code) {
			$batch = SampleHeader::where('batch_code', $code)->get();
			$batch[0]->isactive = 0;

			$batch[0]->save();
		}
		return redirect()->back()->with('success', 'Batches deleted successfully!');
	}
	public function generate_batch_invoice(Request $request)
	{
		$config = getConfigByName('generate_sample_invoice');
		if (!isset($config[0]->id)) {
			return redirect()->back()->with('error', 'generate_sample_invoice configuration is not set');
		}
		if ($config[0]->value == 'true') {
			$customer_ids = [];
			// return response()->json($request->batch_code,200);
			foreach ($request->batch_code as $code) {
				$batch = SampleHeader::where('batch_code', $code)->first();
				if ($batch->invoice_id != 0) {
					return redirect()->back()->with('error', 'Batch' . $code . ' has an existing Invoice!');
				}
				array_push($customer_ids, $batch->crm_customer_id);
			}
			$check_customer = array_unique($customer_ids);
			if (sizeof($check_customer) > 1) {
				return redirect()->back()->with('error', 'The choosen batches are of different customers!');
			}
			$customer = CRMCustomer::find($check_customer[0]);
			if (!isset($customer->id)) {
				return redirect()->back()->with('error', 'There is no customer with the specified Batches!');
			}
			$customer_pricelist = PricelistCustomer::where('customer_id', $customer->id)->get();
			if (!isset($customer_pricelist[0]->id)) {
				return redirect()->back()->with('error', 'The specified has no pricelist assigned');
			}

			$pricelist = Pricelist::find($customer_pricelist[0]->pricelist_id);
			if (!isset($pricelist->id)) {
				return redirect()->back()->with('error', 'There is no pricelist with the specified customer pricelist ID!');
			}
			$invoice = new Invoice();
			$invoice->pricelist_id = $customer_pricelist[0]->pricelist_id;
			$invoice->currency_id = $pricelist->currency_id;
			$invoice->customer_id = $customer->id;
			$invoice->save();
			if ($customer->credit_days > 0) {
				$date = date('Y-m-d', strtotime($invoice->created_at . '+' . $customer->credit_days . ' days'));
			} else {
				$date = date('Y-m-d', strtotime($invoice->created_at . '+ 30 days'));
			}
			$invoice->due_date = $date;
			$id_str = strval($invoice->id);
			if (strlen($id_str) < 4) {
				$count = 4 - strlen($id_str);
				$zeros = str_repeat('0', $count);
				$number = 'INV-' . $zeros . $id_str;
			} else {
				$number = 'INV-' . $id_str;
			}
			$invoice->invoice_number = $number;
			$invoice->save();
			foreach ($request->batch_code as $code) {
				$batch = SampleHeader::where('batch_code', $code)->first();
				if (isset($batch->id)) {

					$batch->invoice_id = $invoice->id;
					$details = SampleDetails::where('sample_header_id', $batch->id)->get();
					foreach ($details as $detail) {
						$analysis_ids = explode(',', $detail->analysis_type_id);
						foreach ($analysis_ids as $analysis_id) {
							$price = PricelistItem::where('pricelist_id', $pricelist->id)->where('analysis_id', (int)$analysis_id)->where('sample_type_id', $batch->sample_type_id)->first();
							if (!isset($price->id)) {
								return redirect()->back()->with('error', 'kindly add analysis to pricelist!');
							}
							$check_invoice_detail = InvoiceDetails::where('invoice_id', $invoice->id)->where('analysis_type', (int)$analysis_id)->first();
							if (!isset($check_invoice_detail->id)) {
								$invoice_detail = new InvoiceDetails();
								$invoice_detail->crm_customer_id = $batch->crm_customer_id;
								$invoice_detail->analysis_type = $analysis_id;
								$analysis = getAnalysisTypeID($analysis_id);
								$invoice_detail->analysis_type_name = $analysis->name;
								$invoice_detail->sample_header_id = $batch->id;
								$invoice_detail->sample_detail_id = $detail->id;
								$invoice_detail->invoice_id = $invoice->id;
								$invoice_detail->cost_price = $price->cost_price;
								$invoice_detail->selling_price = $price->selling_price;
								if ($price->vat == 1) {
									$rate = TaxRegime::where('active', 1)->first();
									$tax = $rate->value / 100 * $price->selling_price;
									$total_price = $tax + $price->selling_price;
									$invoice_detail->selling_amount = $total_price;
									$invoice_detail->tax_rate = strval($rate->value);
									$invoice_detail->tax_amount = $tax;
									$invoice_detail->total = $total_price;
								} else {
									$invoice_detail->selling_amount = $price->selling_price;
									$invoice_detail->total = $price->selling_price;
								}
								// return response()->json($price,200);
								$invoice_detail->save();
							} else {
								$current = $check_invoice_detail->quantity;
								$current_tax = $check_invoice_detail->tax_amount;
								$unit_price = $check_invoice_detail->selling_amount;
								$current_total = $check_invoice_detail->total;
								$check_invoice_detail->total = $unit_price + $current_total;
								$check_invoice_detail->quantity = $current + 1;
								if ($check_invoice_detail->tax_rate != 0) {
									$taxable = strval($check_invoice_detail->tax_rate / 100 * $check_invoice_detail->selling_price);
									$check_invoice_detail->tax_amount = $taxable + $current_tax;
								}
								$check_invoice_detail->save();
							}
						}
					}
					$batch->save();
				}
			}
			$details_invoice = InvoiceDetails::where('invoice_id', $invoice->id);
			$details_invoice_total_including_tax = InvoiceDetails::where('invoice_id', $invoice->id)->pluck('total')->toarray();
			$details_invoice_total_tax = InvoiceDetails::where('invoice_id', $invoice->id)->pluck('tax_amount')->toarray();
			foreach ($details_invoice as $detail) {
				$selling_amount = $detail->selling_price * $detail->quantity;
				$detail->selling_price_amount = $selling_amount;
				$detail->save();
			}
			$total_including_tax = array_sum($details_invoice_total_including_tax);
			$total_invoice_tax = array_sum($details_invoice_total_tax);

			$invoice->total = $total_including_tax;
			$invoice->total_tax = $total_invoice_tax;
			$invoice->save();
			return redirect()->back()->with('success', 'Invoice Created Successfully');
		} else {
			return redirect()->back()->with('error', 'Kindly set generate_sample_invoice configuration value to true! ');
		}
	}
	public function return_back_verification(Request $request)
	{
		$batch = SampleHeader::find($request->batch_id);
		// return response()->json($request->all(),200);
		if (isset($batch->id)) {
			$current = $batch->status;
			$batch->verify_user_id = '';
			$batch->approve_user_id = '';
			$batch->approval_date = '';
			$batch->status = "Sample Verification";
			if (isset($request->batch_comment)) {
				$sample_detail = SampleDetails::where('sample_header_id', $batch->id)->first();
				$sample_detail->main_body = $request->comment ?? '';
				$sample_detail->save();
			}
			$batch->save();
			$custodyDetails = array(
				"batch_id" => $batch->id,
				"comments" => $request->comment ?? '',
				"current" => array(
					"status" => $current,
					"tracking_stage" => $batch->sample_tracking_stage,
				),
				"target" => array(
					"status" => $batch->status,
					"tracking_stage" => $batch->sample_tracking_stage,
				)
			);

			$this->updateChainofCustody($custodyDetails);
		} else {
			return redirect()->back()->with('error', 'No batch with the specified ID!');
		}
		return redirect()->back()->with('success', 'Batch return to verification successfully!');
	}
	public function approve_batch_begin_process(Request $request)
	{
		foreach ($request->batch_code as $code) {
			$batch = SampleHeader::where('batch_code', $code)->first();
			if (isset($batch->id)) {
				$batch->begin_proccess = 1;
				$batch->save();
			} else {
				return redirect()->back()->with('error', 'There is no batch with the specified code -' . $code);
			}
		}
		return redirect()->back()->with('success', 'Batches approved to begin process successfully');
	}
	public function add_batch_attachment(Request $request)
	{
		$batch = SampleHeader::find($request->batch_id);
		if (isset($batch->id)) {
			$new = new BatchAttachment();
			$new->batch_id = $batch->id;
			$new->uploaded_by = auth()->user()->id;
			$new->title = $request->title;
			$new->attachment_type = $request->attachment_type;
			if (isset($request->is_internal)) {
				$new->is_internal = 1;
			}
			$path = $request->attachment->path();
			$file = Storage::putFile('batch-attachments', new File($path));
			$file = explode('/', $file);

			$fName = '/storage/batch-attachments/' . urlencode(end($file));

			$new->attachment_url = (string) $fName;
			$new->save();
			return redirect()->back()->with('success', 'Attachment Added Successfully');
		} else {
			return redirect()->back()->with('error', 'No batch with the specified ID');
		}
	}
	public function delete_batch_attachmment(Request $request)
	{
		$attachment = BatchAttachment::find($request->attachment_id);
		if (isset($attachment->id)) {
			$attachment->delete();
			return redirect()->back()->with('success', 'Attachment deleted successfully!');
		} else {
			return redirect()->back()->with('error', 'No attachment with the specified ID');
		}
	}
	public function fetch_sample_type($id)
	{
		$sample_type = SampleType::find($id);
		$analysis_types = AnalysisType::where('sample_type_id', $sample_type->id)->get();

		$final['analysis'] = $analysis_types;
		$final['sample_type'] = $sample_type->name;
		return $final;
	}
	public function fetch_sample_analyte($id, $analysis, $detail = false)
	{
		$sample_type = SampleType::find($id);
		$analysis_types = AnalysisType::where('sample_type_id', $sample_type->id)->get();
		$analytes = [];
		$selected_analysis = explode(',', $analysis);
		$sub = [];
		$acc = [];
		$default = [];
		$both = [];
		if ($detail != false) {
			$detail_data = QuotationDetails::find($detail);
			$sub = explode(',', $detail_data->subcontracted_analytes);
			$acc = explode(',', $detail_data->accredited_analytes);
			$default = explode(',', $detail_data->default_analytes);
			$both = explode(',', $detail_data->sub_acc_analytes);
		}
		$check = array_merge($sub, $acc, $default, $both);
		foreach ($analysis_types as $type) {
			if (in_array($type->id, $selected_analysis)) {

				$analysis_analytes = AnalysisElements::where('analysis_type_id', $type->id)->get();
				foreach ($analysis_analytes as $aa) {
					$analyte = getAnalyteByID($aa->analyte_id);
					$aa->analyte_code = $analyte->code;
					$aa->analyte_name = $analyte->name;
					$aa->default = in_array($aa->id, $default) ? 1 : 0;
					$aa->acc = in_array($aa->id, $acc) ? 1 : 0;
					$aa->sub = in_array($aa->id, $sub) ? 1 : 0;
					$aa->both = in_array($aa->id, $both) ? 1 : 0;
					$aa->present = $detail != false ? 1 : 0;
					if ($detail != false) {
						if (in_array($aa->id, $check)) {
							$aa->selected = 1;
						} else {
							$aa->selected = 0;
						}
					} else {
						$aa->selected = 1;
					}

					if (!isset($analytes[$type->name])) {
						$analytes[$type->name] = [];
					}
					array_push($analytes[$type->name], $aa);
				}
			}
		}

		return $analytes;
	}
	public function check_rft_no(Request $request)
	{
		$batch = SampleHeader::where('reference_number', $request->rft_no)->first();
		if (isset($batch->id)) {
			if ($batch->id == $request->batch_id) {
				return response()->json('success');
			} else {
				return response()->json('fail');
			}
		} else {
			return response()->json('success');
		}
	}
	public function resolveTest()
	{
		$range_r = range(738, 747);
		foreach ($range_r as $r) {
			$sample = getSampleDetailById($r);
			$captured = CapturedResult::where('sample_detail_id', $sample->id)->get();
			$results = Result::where('sample_detail_id', $sample->id)->get();
			foreach ($captured as $c) {
				$c->sample_detail_code = $sample->sample_code;
				$c->save();
			}
			foreach ($results as $r) {
				$r->sample_detail_code = $sample->sample_code;
				$r->save();
			}
		}
		return response()->json('done');
	}
	public function return_batch_reception(Request $request)
	{
		foreach ($request->batch_code as $code) {
			$header = SampleHeader::where('batch_code', $code)->first();
			if (isset($header->id)) {
				$header->status = 'Samples Reception';
				$header->save();
				$responsibility = SystemConfiguration::where('key', $header->status)->first();
				$users = JobDescription::where('config_id', $responsibility->id)->join('users', 'users.position', '=', 'job_designation_responsibility.job_id')->get('users.*');

				$numbers = $users->pluck('phone')->toarray();

				$message = 'Batch ' . $header->batch_code . ' Approval Request has been rejected because - ' . $request->comment . '.';
				if (isset($request->send_message)) {
					foreach ($numbers as $num) {
						$send = sendTextMessage($num, $message);
						if ($send == 'error') {
							return redirect()->back()->with('error', 'Kindly add the sms configurations in the configurations section!');
						}
					}
				}
				if (isset($request->send_email)) {
					foreach ($users as $user) {
						$subject = '[' . $header->batch_code . '] Approval Request Reject.';
						$body = 'Hi ' . $user->name . ',<br>' . $message;
						notify_user($body, $user->email, $subject);
					}
				}

				$custodyDetails = array(
					"batch_id" => $header->id,
					"comments" => $request->comment ?? '',
					"current" => array(
						"status" => $header->status,
						"tracking_stage" => $header->sample_tracking_stage,
					),
					"target" => array(
						"status" => $header->status,
						"tracking_stage" => $header->sample_tracking_stage,
					)
				);
				$this->updateChainofCustody($custodyDetails);
			}
		}
		return redirect()->back()->with('success', 'Batch(es) rejected succesfully');
	}
	public function regerateCustomerInvoice($id)
	{
		$batch = SampleHeader::find($id);
		$invoice = Invoice::find($batch->invoice_id);
		$details = SampleDetails::where('sample_header_id', $batch->id)->get();
		$customer_pricelist = PricelistCustomer::where('customer_id', $batch->crm_customer_id)->get();
		$pricelist = Pricelist::find($customer_pricelist[0]->pricelist_id);
		foreach ($details as $detail) {
			$analysis_ids = explode(',', $detail->analysis_type_id);
			foreach ($analysis_ids as $analysis_id) {
				$price = PricelistItem::where('pricelist_id', $pricelist->id)->where('analysis_id', (int)$analysis_id)->where('sample_type_id', $batch->sample_type_id)->first();
				if (!isset($price->id)) {
					return redirect()->back()->with('error', 'kindly add analysis to pricelist!');
				}
				$check_invoice_detail = InvoiceDetails::where('invoice_id', $invoice->id)->where('analysis_type', (int)$analysis_id)->first();
				if (!isset($check_invoice_detail->id)) {
					$invoice_detail = new InvoiceDetails();
					$invoice_detail->crm_customer_id = $batch->crm_customer_id;
					$invoice_detail->analysis_type = $analysis_id;
					$analysis = getAnalysisTypeID($analysis_id);
					$invoice_detail->analysis_type_name = $analysis->name;
					$invoice_detail->sample_header_id = $batch->id;
					$invoice_detail->sample_detail_id = $detail->id;
					$invoice_detail->invoice_id = $invoice->id;
					$invoice_detail->cost_price = $price->cost_price;
					$invoice_detail->selling_price = $price->selling_price;
					if ($price->vat == 1) {
						$rate = TaxRegime::where('active', 1)->first();
						$tax = $rate->value / 100 * $price->selling_price;
						$total_price = $tax + $price->selling_price;
						$invoice_detail->selling_amount = $total_price;
						$invoice_detail->tax_rate = strval($rate->value);
						$invoice_detail->tax_amount = $tax;
						$invoice_detail->total = $total_price;
					} else {
						$invoice_detail->selling_amount = $price->selling_price;
						$invoice_detail->total = $price->selling_price;
					}
					// return response()->json($price,200);
					$invoice_detail->save();
				} else {
					$current = $check_invoice_detail->quantity;
					$current_tax = $check_invoice_detail->tax_amount;
					$unit_price = $check_invoice_detail->selling_amount;
					$current_total = $check_invoice_detail->total;
					$check_invoice_detail->total = $unit_price + $current_total;
					$check_invoice_detail->quantity = $current + 1;
					if ($check_invoice_detail->tax_rate != 0) {
						$taxable = strval($check_invoice_detail->tax_rate / 100 * $check_invoice_detail->selling_price);
						$check_invoice_detail->tax_amount = $taxable + $current_tax;
					}
					$check_invoice_detail->save();
				}
			}
		}
		$details_invoice = InvoiceDetails::where('invoice_id', $invoice->id);
		$details_invoice_total_including_tax = InvoiceDetails::where('invoice_id', $invoice->id)->pluck('total')->toarray();
		$details_invoice_total_tax = InvoiceDetails::where('invoice_id', $invoice->id)->pluck('tax_amount')->toarray();
		foreach ($details_invoice as $detail) {
			$selling_amount = $detail->selling_price * $detail->quantity;
			$detail->selling_price_amount = $selling_amount;
			$detail->save();
		}
		$total_including_tax = array_sum($details_invoice_total_including_tax);
		$total_invoice_tax = array_sum($details_invoice_total_tax);

		$invoice->total = $total_including_tax;
		$invoice->total_tax = $total_invoice_tax;
		$invoice->save();
		return response()->json(['invoice' => $invoice, 'detail' => $details_invoice]);
	}

	public function fillCapturedresultOperator()
	{
		$captured_reults  = CapturedResult::where('operator_id', 0)->get();
		foreach ($captured_reults as $c) {
			$ae = AnalysisElements::where('analyte_id', $c->analyte_id)->where('analysis_type_id', $c->analysis_type_id)->first();
			if (isset($ae->id)) {
				$c->operator_id = $ae->operator_id;
			}
			$c->save();
		}
		return response()->json('success');
	}
	public function markQcSampleComplete($id)
	{
		$header = SampleHeader::find($id);
		$previous_status = $header->status;
		$header->status = "QC Approved";
		$header->save();
		return redirect()->route('sample-workflow', ['status' => $previous_status])->with('success', 'Batch marked complete succesffuly');
	}

	public function markAccredittedSamples($header_id = 0)
	{

		if ($header_id == 0) {
			$sample_ids = SampleHeader::whereIn('status', ["Samples Reception", "Samples Request Review", "Samples In Lab"])->pluck('id')->toArray();

			$captured_results = CapturedResult::whereIn('id', $sample_ids)->get();
			foreach ($captured_results as $cr) {
				$analysisElement = AnalysisElements::where('analysis_type_id', $cr->analysis_type_id)
					->where('analyte_id', $cr->analyte_id)->first();
				if (isset($analysisElement->id)) {
					$cr->analyte_accredited = $analysisElement->non_accredited;
					$cr->save();
					$result = Result::where('captured_result_id', $cr->id)->first();
					$result->analyte_accredited = $analysisElement->non_accredited;
					$result->save();
				}
			}
		}
		return response()->json('done');
	}
	public function getLabsByAnalysisTypeIdAjax(Request $request)
	{
		// $lab_ids = AnalysisType::whereIn('id',$request->ids)->pluck('lab_id')->toArray();
		$labs =  Lab::where('active', 1)->get();
		return response()->json($labs);
	}
	public function assignLabSectionToAnalysisElement($id)
	{
		$analysistype = AnalysisType::find($id);
		$elements = AnalysisElements::where('analysis_type_id', $id)->update(['lab_section_id' => $analysistype->lab_section_id]);
		return response()->json('success');
	}

	public function create_sample_inter_lab_log(Request $request)
	{
		if ($request->quantity == '' || $request->to_lab_section_id == '') {
			return redirect()->back()->with('error', 'Quantity, To Lab are mandatory fields');
		}
		// return response()->json($request->all());
		if (isset($request->batch_level)) {
			$log = [];
			$batches = SampleHeader::whereIn('batch_code', $request->batch_code)->pluck('id')->toArray();
			$sample_ids = SampleDetails::whereIn('sample_header_id', $batches)->pluck('id')->toArray();
			foreach ($sample_ids as $id) {
				$batch = getSampleHeaderByID(getSampleDetailByID($id)->sample_header_id);
				$last_log = InterLabLog::where('sample_id', $id)->where('status', 1)->orderBy('date_received', 'DESC')->first();
				$log[] = [
					"sample_id" => $id,
					"to_lab_section_id" => $request->to_lab_section_id,
					"from_lab_section_id" => isset($last_log->id) ? $last_log->to_lab_section_id : 0,
					"quantity" => $request->quantity,
					"submited_by" => auth()->user()->id,
					"date_submitted" => date('Y-m-d h:i:s a'),
					"expected_date" => $request->expected_date == '' ?  date('Y-m-d', strtotime($batch->get_date('Target Date')['date'])) : $request->expected_date,
					"prelim_date" => $request->prelim_date ?? '',
					"remarks" => $request->remarks ?? '',

				];
			}

			// return response()->json($log);
		} else {

			$last_log = InterLabLog::where('sample_id', $request->sample_id)->where('status', 1)->orderBy('date_received', 'DESC')->first();
			if (isset($request->interlab_id) && $request->interlab_id != '0') {
				$log = [
					"sample_id" => $request->sample_id,
					"to_lab_section_id" => $request->to_lab_section_id,
					"quantity" => $request->quantity,
					"submited_by" => auth()->user()->id,
					"date_submitted" => date('Y-m-d h:i:s a'),
					"expected_date" => $request->expected_date,
					"from_lab_section_id" => isset($last_log->id) ? $last_log->to_lab_section_id : 0,
					"prelim_date" => $request->prelim_date ?? '',
					"remarks" => $request->remarks ?? '',
				];
			} else {
				$log = [
					"sample_id" => $request->sample_id,
					"to_lab_section_id" => $request->to_lab_section_id,
					"from_lab_section_id" => isset($last_log->id) ? $last_log->to_lab_section_id : 0,
					"quantity" => $request->quantity,
					"submited_by" => auth()->user()->id,
					"date_submitted" => date('Y-m-d h:i:s a'),
					"expected_date" => $request->expected_date,
					"prelim_date" => $request->prelim_date ?? '',
					"remarks" => $request->remarks ?? '',
				];
			}
		}
		isset($request->interlab_id) && $request->interlab_id != '0' ? InterLabLog::find($request->interlab_id)->update($log) : InterLabLog::insert($log);
		if ($request->notify_user != '' || $request->sms_notify !=  '') {
			$sample_codes = isset($request->batch_level) ? SampleDetails::whereIn('id', $sample_ids)->get() : SampleDetails::where('id',$request->sample_id)->get();
			$samplecodesList= '';
			
			foreach($sample_codes as $s_code){
				$samplecodesList .= '<li><a href="http://172.16.16.252:8080/sample-workflow/batch/'.$s_code->sample_header_id.'/details/0/0/'.$s_code->getSampleHeader()->status.'">'.$s_code->sample_code.'</a></li>';
			}
			$bcc_emails = User::whereIn('id', $request->also_notify ?? [])->pluck('email')->toArray();
			$body = 'Hi Team, <br> The following sample(s) require  your attention for approval of inter laboratory transfer raised by ' . auth()->user()->name . '<br>Click the sample codes to access the sample InterLab Log <br><ul>' . $samplecodesList.'</ul>';
			$to_email = getUserById($request->notify_user);



			if (isset($to_email->id)) {
				notify_user($body, $to_email->email, '[Polucon  Polucon Services Limited] Inter Laboratory Transfer Approval Notification', false, false, $bcc_emails);
				// sendTextMessage($to_email->phone, 'Hi ' . $to_email->name . ', The following sample(s) require  your attention for approval of inter laboratory transfer raised by ' . auth()->user()->name);
				// foreach (User::whereIn('id', $request->also_notify ?? [])->get() as $user) {
				// 	$user->phone != '' ? sendTextMessage($user->phone, 'Hi ' . $user->name . ', The following sample(s) require  your attention for approval of inter laboratory transfer raised by ' . auth()->user()->name) . ':  ' . $sample_codes : '';
				// }
			}
		}

		return redirect()->back()->with('success', 'Inter laboratory Log updated successfully');
	}

	public function getSampleCurrentLabSection($id)
	{
		$last_log = InterLabLogView::where('sample_id', $id)->where('status', 1)->orderBy('date_received', 'DESC')->orderBy('id', 'DESC')->first();
		return response()->json(isset($last_log->id) ? $last_log->to_lab_code . ' ' . $last_log->to_lab_name : "Reception");
	}
	public function changeInterLabLogStatus(Request $request)
	{
		if ($request->status == '') {
			return redirect()->back()->with('error', 'Status is a required field');
		}
		if (isset($request->inter_lab_id)) {
			InterLabLog::find($request->inter_lab_id)->update(['status' => $request->status, 'date_received' => date('Y-m-d h:i:s a'), "received_by" => auth()->user()->id]);
		} else {
			InterLabLog::whereIn('id', explode(',', $request->inter_lab_ids))->update(['status' => $request->status, 'date_received' => date('Y-m-d h:i:s a'), "received_by" => auth()->user()->id]);
		}
		return redirect()->back()->with('success', 'Inter laboratory Log status updated successfully');
	}
	public function interLabTransferIndex($is_archived = 0)
	{
		$interlabs = $is_archived == 0 ? InterLabLogView::orderBy('id', 'DESC')->where('batch_status', '!=', 'Completed')->get() : InterLabLogView::orderBy('id', 'DESC')->get();
		$samples = $interlabs->pluck('sample_code')->toArray();
		$labs  = Lab::where('active', 1)->get();
		$users = User::where('is_client', 0)->where('supplier_id', 0)->where('active', 1)->get();

		return view('layouts.lab.interlab.index', compact('interlabs', 'samples', 'labs', 'users'));
	}
	public function deleteInterLabTransferLogs(Request $request)
	{
		// return response()->json($request->all());
		InterLabLog::whereIn('id', explode(',', $request->inter_lab_ids))->delete();
		return redirect()->back()->with('success', 'Inter Laboratory Transfer Log(s) deleted successfully');
	}

	public function generateCustomerFocusIndex(Request $request, $batch_id)
	{
		// return response()->json($request->all());
		$batch = SampleHeader::find($batch_id);
		if ($batch_id == 0 || $batch->c_focus_ids_clustered != '') {
			$batches = $batch_id == 0 ? SampleHeader::whereIn('batch_code', $request->batch_code) : SampleHeader::whereIn('id', explode(',', $batch->c_focus_ids_clustered));
			$getCustomers = clone $batches;
			$customer_ids = array_unique($getCustomers->pluck('crm_customer_id')->toArray());
			if (sizeof($customer_ids) > 1) {
				return redirect()->back()->with('error', 'All batches should be of the same client! Kindly check on the batches you have selected');
			}
			$batch =  $getCustomers->orderBy('created_at', 'ASC')->first();
			$customer = CrmCustomer::find($batch->crm_customer_id);
			$sample_type_ids = $batches->pluck('sample_type_id')->toArray();
			$sample_types = implode(', ', array_unique(SampleType::whereIn('id', $sample_type_ids)->pluck('name')->toArray()));
			$company = getActiveCompany();
			$config_docs_setting = SystemConfiguration::where('key', 'customer_focus_id')->first();
			$docs_settings = SystemConfiguration::where('configuration_type_id', $config_docs_setting->value)->pluck('value', 'key')->toArray();
			$payment_detail = [
				"balance" => $request->balance,
				// "total_amount"=>array_sum($batches->pluck('invoice_amount')->toArray()),
				"amount_paid" => $request->amount_paid,
				"vat" => $request->vat,
				"invoice_amount" => $request->invoice_amount
			];
			$batch_ids = $batches->pluck('id')->toArray();
			$samples = SamplesCategory::whereIn('sample_header_id', $batch_ids)->get();
			sampleAnalysisTypeRelation::whereIn('batch_id', $batch_ids)->whereNotIn('sample_detail_id', $samples->pluck('id')->toArray())->delete();
			$review_staff = getUserById($batch->receiving_officer);
			$is_clustered = 1;
			// return response()->json($batch_ids);

			SampleHeader::whereIn('batch_code', $request->batch_code)->update(['c_focus_ids_clustered' => implode(',', $batch_ids)]);

			return view('layouts.lab.sample-workflow.customer_focus', compact('batch', 'customer', 'company', 'docs_settings', 'review_staff', 'samples', 'payment_detail', 'sample_types', 'is_clustered'));
		}
		$is_clustered = 0;
		$batch = SampleHeader::find($batch_id);
		$customer = CrmCustomer::find($batch->crm_customer_id);
		$company = getActiveCompany();
		$config_docs_setting = SystemConfiguration::where('key', 'customer_focus_id')->first();
		$docs_settings = SystemConfiguration::where('configuration_type_id', $config_docs_setting->value)->pluck('value', 'key')->toArray();
		$review_staff = getUserById($batch->receiving_officer);
		$samples = SamplesCategory::where('sample_header_id', $batch_id)->get();
		sampleAnalysisTypeRelation::where('batch_id', $batch->id)->whereNotIn('sample_detail_id', $samples->pluck('id')->toArray())->delete();
		$payment_detail = InvoicePaymentDetail::where('batch_id', $batch->id)->orderBy('id', 'DESC')->first();

		return view('layouts.lab.sample-workflow.customer_focus', compact('batch', 'customer', 'company', 'docs_settings', 'review_staff', 'samples', 'payment_detail', 'is_clustered'));
	}

	public function sendBatchScheduleAnalysis(Request $request)
	{
		$batch = SampleHeader::find($request->batch_id);
		$samples = SampleDetails::where('sample_header_id', $batch->id)->get();
		$customer = CrmCustomer::find($batch->crm_customer_id);
		$sampleTrs = '';
		$contact = CustomerContact::find($request->contact_id);
		foreach ($samples as $sample) {
			$sampleTrs = $sampleTrs . '
			<tr style="border: 1px solid black">
				<td style="border: 1px solid black">' . $sample->sample_code . '</td>
				<td style="border: 1px solid black">' . $sample->getAnalysisTestDone() . ' </td>
				<td style="border: 1px solid black">-</td>
			</tr>
			';
		}
		$specified_days = SystemConfiguration::where('key', 'specified_duration_days')->first();

		// return response()->json($sampleTrs);

		$body = '
		<p>
				Dear ' . $customer->name . ', <br><br>
				Thank you for chosing our laboratory for sample testing.We will be running the following tests on your sample:
			</p>
			<table class="table-sm table-bordered" style="border: 1px solid black;width:100%">
				<thead>

					<tr style="border: 1px solid black">
						<th style="border: 1px solid black">Sample No</th>
						<th style="border: 1px solid black">Analysis</th>
						<th style="border: 1px solid black">#</th>
					</tr>
				</thead>
				<tbody>
					' . $sampleTrs . '
					<tr>
						<td colspan="2" style="border: 1px solid black"><b>Total Amount</b></td>
						<td style="text-align: right;border: 1px solid black">' . number_format($batch->invoice_amount, 2) . '</td>
					</tr>
				</tbody>
			</table>
			<br>
			<p>
				If we don`t hear from you within ' . $specified_days->value . ', we will proceed with the analysis as shared. <br>
				For any questions or modifications, please contact us at polucon@polucon.com | laboratory@polucon.com. <br><br>
				Thank you, <br>
				' . auth()->user()->name . '

			</p>
		';
		notify_user($body, $contact->email, '[POLUCON LIMS] Schedule Of Analysis ' . $batch->batch_code);
		return redirect()->back()->with('success', 'Schedule of analysis sent out successfully');
	}
	public function sendBatchPaymentReminder(Request $request)
	{
		$contact = CustomerContact::find($request->contact_id);
		$batch = SampleHeader::find($request->batch_id);
		notify_user($request->body, $contact->email, '[POLUCON LIMS] Payment Reminder ' . $batch->batch_code);
		return redirect()->back()->with('success', 'Payment reminder sent out successfully');
	}

	public function getLabSectionsByLab($lab_id)
	{
		$sections = $lab_id > 0 ? SampleAnalysisStage::where('lab_id', $lab_id)->where('active', 1)->get() : SampleAnalysisStage::where('active', 1)->get();
		return response()->json($sections);
	}

	public function moveToLab(Request $request)
	{
		// return response()->json($request->all());
		$status = 'Samples In Lab';
		foreach ($request->batch_code as $code) {
			$batch = SampleHeader::where('batch_code', $code)->first();
			$previousWorkflow = $batch->status;

			$custodyDetails = array(
				"batch_id" => $batch->id,
				"comments" => $request->comments ?? '',
				"current" => array(
					"status" => $batch->status,
					"tracking_stage" => $batch->sample_tracking_stage,
				),
				"target" => array(
					"status" => $status,
					"tracking_stage" => $batch->sample_tracking_stage,
				)
			);

			$this->updateChainofCustody($custodyDetails);
			$batch->status = $status;
			$batch->save();
		}
		return redirect()->back()->with('success', 'Sample(s) moved to samples in Lab section successfully');
	}

	public function showBatchCOA(Request $request)
	{
		$batch = SampleHeader::find($request->batch_id);
		$batch_approvers = BatchLabSectionApprover::where('batch_id', $batch->id)->where('show_report',1)->where('status', 1)->get();
		$samples = SamplesCategory::where('sample_header_id', $request->batch_id)->get();
		$disclaimer = SystemConfiguration::where('key', 'lab_report_disclaimer_config')->first();
		$non_accredited = SystemConfiguration::where('key', 'lab_report_accreditted_config')->first();
		$status = $batch->status;

		$company = getActiveCompany();
		$standard_report = $request->template_id;
		$analysis_date = SampleAnalysisDates::where('sample_header_id',$batch->id)->orderBy('start_analysis_date','ASC')->first();
		// return response()->json('here');
		return view('layouts.lab.sample-workflow.report-formats.standard_report', compact('batch', 'samples', 'disclaimer', 'non_accredited', 'status', 'company', 'batch_approvers', 'standard_report','analysis_date'));
	}

	public function getShowBatchCOA($batch_code, $format)
	{
		$batch = SampleHeader::where('batch_code', $batch_code)->first();
		$batch_approvers = BatchLabSectionApprover::where('batch_id', $batch->id)->where('status', 1)->get();
		$samples = SamplesCategory::where('sample_header_id', $request->batch_id)->get();
		$disclaimer = SystemConfiguration::where('key', 'lab_report_disclaimer_config')->first();
		$non_accredited = SystemConfiguration::where('key', 'lab_report_accreditted_config')->first();
		$status = $batch->status;
		// $url = env('APP_URL').'/showBatchCOAGet';
		// $qr_url = url($url);
		// return response()->json($batch_result,200);


		// $qrcode = base64_encode(\QrCode::format('svg')->size(50)->errorCorrection('H')->generate($qr_url));

		$company = getActiveCompany();
		// return response()->json('here');
		return view('layouts.lab.sample-workflow.report-formats.standard_report', compact('batch', 'samples', 'disclaimer', 'non_accredited', 'status', 'company', 'batch_approvers'));
	}

	public function moveToVerificationApprovalLevel(Request $request)
	{
		// return response()->json($request->all());
		$batch = SampleHeader::find($request->batch_id);
		$previousWorkflow = $batch->status;
		if ($batch->lab_section_ids == '') {
			return redirect()->back()->with('error', 'Kindly provide the lab sections associated with the sample at batch information section');
		}
		$section_users = LabSectionApproverRelationShip::whereIn('lab_section_id', explode(',', $batch->lab_section_ids))->get();
		$users =[];
		$user_approvers = [];
		foreach(explode(',', $batch->lab_section_ids) as $section_id){
			$c_user = CapturedResult::where('lab_section_id',$section_id)->where('sample_header_id',$batch->id)->orderBy('updated_at','DESC')->first();
			
			if($c_user && $c_user->operator_id) {
				array_push($users,$c_user->operator_id);
				$user_approvers[$c_user->operator_id] = $section_id;
			}
			
		}
		$analysts = User::whereIn('id',$users)->get();
		
		if ($section_users->count() <= 0) {
			return redirect()->back()->with('error', 'Kindly provide approval configuration for the selected batch lab sections');
		}
		if ($request->status == 'Sample Verification') {
			$batch->status = $request->level == "0" ?  $request->status : $batch->status;
			$batch->report_status = $request->level == "0" ? $request->level : $batch->report_status;
			$batch->prelim_report_status = $request->level != "0" ? $request->level : $batch->prelim_report_status;
			$batch->prelim_batch_status =  $request->level != "0" ? $request->status : $batch->prelim_batch_status;
			if ($request->level == "0") {
				$batch->report_status = "";
				$batch->prelim_report_status = 0;
				$batch->prelim_batch_status =  '';
			}
			if ($request->level != "2") {
				$request->level != 0 ? BatchLabSectionApprover::where('batch_id', $batch->id)->delete() : BatchLabSectionApprover::where('batch_id', $batch->id)->where('is_prelim', 0)->delete();
				foreach ($section_users as $user_id) {
					$approvers = BatchLabSectionApprover::where('batch_id', $batch->id)->where('user_id', $user_id->user_id)->first() ?? new BatchLabSectionApprover();
					$approvers->status = 0;
					$approvers->user_id = $user_id->user_id;
					$approvers->title = $user_id->title;
					$approvers->lab_section_ids = $approvers->lab_section_ids == '' ?  $approvers->lab_section_ids . $user_id->lab_section_id : $approvers->lab_section_ids . ',' . $user_id->lab_section_id;
					$approvers->batch_id = $batch->id;
					$approvers->batch_status = $request->status;
					$approvers->is_prelim = $request->level != "0" ? 1 : 0;
					$approvers->save();
				}
				foreach($analysts as $analyst){
					$approvers = BatchLabSectionApprover::where('batch_id', $batch->id)->where('user_id', $analyst->id)->first() ?? new BatchLabSectionApprover();
					$section = SampleAnalysisStage::find($user_approvers[$analyst->id]);
					$approvers->status = 1;
					$approvers->user_id = $analyst->id;
					$approvers->title = $section->title;
					$approvers->lab_section_ids = $approvers->lab_section_ids == '' ?  $approvers->lab_section_ids . $section->id: $approvers->lab_section_ids . ',' . $section->id;
					$approvers->batch_id = $batch->id;
					$approvers->batch_status = $request->status;
					$approvers->is_prelim = $request->level != "0" ? 1 : 0;
					$approvers->approval_date = date('Y-m-d h:i:s a');
					$approvers->show_report = 1;
					$approvers->save();
				}

			}
			$batch->save();
			return redirect()->route('sample-workflow', ['status' => $previousWorkflow])->with('success', 'Batch move was successful');
		}

		BatchLabSectionApprover::where('batch_id', $batch->id)->where('lab_section_ids', 0)->delete();
		$approvers = new BatchLabSectionApprover();
		$approvers->status = 0;
		$approvers->user_id = $request->user_id;
		$approvers->title = $request->title;
		$approvers->lab_section_ids = 0;
		$approvers->batch_id = $batch->id;
		$approvers->batch_status = $request->status;
		$approvers->show_report = 1;
		$approvers->save();
		$batch->status = $request->status;
		$batch->save();
		if (isset($request->notification)) {
			$user = User::find($request->user_id);
			$message  = 'Hi ' . $user->name . ', <br>' . $batch->batch_code . ' COA needs your approval at ' . $batch->status . '. <br> Comments : ' . $request->comments;
			notify_user($message, $user->email, '[Polucon LIMS] ' . $batch->batch_code . ' Batch Approval Notification');
		}
		if (isset($request->send_message)) {
			$user = User::find($request->user_id);
			$sms_message = 'Hi ' . $user->name . ', ' . $batch->batch_code . ' COA needs your approval at ' . $batch->status . '. Comments : ' . $request->comments;
			sendTextMessage($user->phone, $sms_message);
		}

		return redirect()->route('sample-workflow', ['status' => $previousWorkflow])->with('success', 'Batch move was successful');
	}
	public function editVerificationApproverConfig(Request $request)
	{
		$config = BatchLabSectionApprover::find($request->approver_id);
		$config->user_id = $request->user_id;
		$config->title = $request->title;
		$config->save();
		return redirect()->back()->with('success', 'Batch Approval updated successfully');
	}
	public function deleteVerificationApproverConfig(Request $request)
	{
		BatchLabSectionApprover::find($request->approver_id)->delete();
		return redirect()->back()->with('success', 'Batch Approval deleted successfully');
	}
	public function changeBatchApprovalStatus(Request $request)
	{
		BatchLabSectionApprover::where('id', $request->approver_id)->update(['status' => $request->status, 'approval_date' => date('Y-m-d H:i:s'), 'remark' => $request->remark]);
		if (BatchLabSectionApprover::where('id', $request->approver_id)->where('status', 0)->get()->count() == 0) {
			$approver = BatchLabSectionApprover::find($request->approver_id);
			$batch = SampleHeader::find($approver->batch_id);
			if ($batch->status == 'Sample Approval') {
				$batch->approval_date = getTodayDate();
				$batch->save();
			}
		}
		return redirect()->back()->with('success', 'Batch Approval updated successfully');
	}
	public function getClientDetailsAjax($id)
	{
		$customer = CrmCustomer::with('units', 'contacts')->find($id);

		$res = [
			"units" => $customer->units,
			"unit_name" => 'Company Units',
			"sample_point_name" => 'Sample Point',
			"contacts" => $customer->contacts,
		];
		return response()->json($res);
	}
	public function generateTabletCustomerFocusIndex(Request $request)
	{
		$sample = SampleDetails::where('sample_no', $request->sample_no)->first();
		if (!isset($sample->id)) {
			return redirect()->back()->with('error', 'There is no sample with ' . $request->sample_no . ' sample/job number');
		}
		$batch = SampleHeader::find($sample->sample_header_id);
		if(!isset($batch->id)){
			return redirect()->back()->with('error', 'There is no batch associated with the specified sample'); 
		}

		if (isset($request->is_clustered)) {
			$batches = SampleHeader::whereIn('id', explode(',', $batch->c_focus_ids_clustered));
			$getCustomers = clone $batches;
			$customer_ids = array_unique($getCustomers->pluck('crm_customer_id')->toArray());
			if (sizeof($customer_ids) > 1) {
				return redirect()->back()->with('error', 'All batches should be of the same client! Kindly check on the batches you have selected');
			}
			$batch =  $getCustomers->orderBy('created_at', 'ASC')->first();
			if(!isset($batch->id)){
				return redirect()->back()->with('error', 'the batch has no clustered customer focus');
			}
			$customer = CrmCustomer::find($batch->crm_customer_id);
			$sample_type_ids = $batches->pluck('sample_type_id')->toArray();
			$sample_types = implode(', ', array_unique(SampleType::whereIn('id', $sample_type_ids)->pluck('name')->toArray()));
			$company = getActiveCompany();
			$config_docs_setting = SystemConfiguration::where('key', 'customer_focus_id')->first();
			$docs_settings = SystemConfiguration::where('configuration_type_id', $config_docs_setting->value)->pluck('value', 'key')->toArray();
			$payment_detail = [
				"balance" => $batch->cluster_balance,
				// "total_amount"=>array_sum($batches->pluck('invoice_amount')->toArray()),
				"amount_paid" => $batch->cluster_amount_paid,
				"vat" => $batch->cluster_vat,
				"invoice_amount" => $batch->cluster_amount
			];
			$batch_ids = $batches->pluck('id')->toArray();
			$samples = SamplesCategory::whereIn('sample_header_id', $batch_ids)->get();
			$review_staff = getUserById($batch->receiving_officer);
			$is_clustered = 1;

			// SampleHeader::whereIn('batch_code',$request->batch_code)->update(['c_focus_ids_clustered'=>implode(',',$batch_ids),'cluster_amount'=>$request->invoice_amount,'cluster_vat'=>$request->vat,'cluster_amount_paid'=>$request->amount_paid,'cluster_balance'=>$request->balance]);
			return view('layouts.lab.sample-workflow.sign-customer-focus-show', compact('batch', 'customer', 'company', 'docs_settings', 'review_staff', 'samples', 'payment_detail', 'is_clustered', 'sample_types'));
		}


		$is_clustered = 0;

		$customer = CrmCustomer::find($batch->crm_customer_id);
		$company = getActiveCompany();
		$config_docs_setting = SystemConfiguration::where('key', 'customer_focus_id')->first();
		$docs_settings = SystemConfiguration::where('configuration_type_id', $config_docs_setting->value)->pluck('value', 'key')->toArray();
		$review_staff = getUserById($batch->receiving_officer);
		$samples = SamplesCategory::where('sample_header_id', $batch->id)->get();
		$payment_detail = InvoicePaymentDetail::where('batch_id', $batch->id)->orderBy('id', 'DESC')->first();
		return view('layouts.lab.sample-workflow.sign-customer-focus-show', compact('batch', 'customer', 'company', 'docs_settings', 'review_staff', 'samples', 'payment_detail', 'is_clustered'));
	}
	public function getTableCustomerFocusSigning(Request $request)
	{
		if (isset($request->is_clustered)) {
			$batch = Sampleheader::find($request->sample_header_id);
			if ($batch->c_focus_ids_clustered != '') {
				Sampleheader::whereIn('id', explode(',', $batch->c_focus_ids_clustered))->update(['declaration_customer_approval_date' => date('Y-m-d h:i:s a'), "declaration_customer_signature" => $request->signature, 'declaration_customer_contact_name' => $request->contact_person]);
				return response()->json('success');
			}
		}
		Sampleheader::find($request->sample_header_id)->update(['declaration_customer_approval_date' => date('Y-m-d h:i:s a'), "declaration_customer_signature" => $request->signature, 'declaration_customer_contact_name' => $request->contact_person]);
		return response()->json('success');
		// return redirect()->back()->with('success','Customer Focus document signed successfully');
	}
	public function getSampleCodeToResultsAndCr()
	{
		$samples = SampleDetails::where('sample_header_id', '>', 13)->get();
		foreach ($samples as $sample) {
			CapturedResult::where('sample_detail_id', $sample->id)->update(['sample_detail_code' => $sample->sample_code]);
			Result::where('sample_detail_id', $sample->id)->update(['sample_detail_code' => $sample->sample_code]);
		}
		return response()->json('success');
	}
	public function anothershow($batch, $client = false, $portal = false, $status = false)
	{
		$batchID = $batch;
		$batch = SampleHeader::with('comments', 'comments.creator')->find($batchID);
		$batch_scope = SystemConfiguration::where('key', 'batch_scope')->first();
		$customer_survey = SystemConfiguration::where('key', 'customer_survey')->first();
		$countries = Country::orderBy('name')->get();
		$methods = AnalysisMethod::where('active', 1)->get();
		$account_settings = getConfigTypeByName('Account Settings');
		$atachment_type = SystemConfiguration::where('key', 'attachment_type')->get();
		$users = User::where('is_client', 0)->where('supplier_id', 0)->where('active', 1)->get();
		$labsections = SampleAnalysisStage::where('active', 1)->get();
		$reportingUnits = getReportingUnits();
		$conditions = SampleCondition::all();
		$products = CompanyProduct::all();
		$workflowstages = [];
		$workflows = getSampleWorflowStages();
		$sample_types = getSampleTypes();
		$samplingmethods = getSamplingMethods();
		$interlabs = [];
		$disposal_date = '';
		if (isset($account_settings->id)) {
			$accounts = getconfigByID($account_settings->id);
		} else {

			$accounts = array();
		}
		$not_captured = [];
		$payment_detail = [];
		$contacts = [];
		$batch_sample_codes = '';
		$report_formats = [];
		$approvers = [];
		$headerDetails = isset($batch->id) ? $batch->report_header_details() : array();
		$ammendments = isset($batch->id) ? getBatchAmmendmentsById($batch->id) : [];
		$allsamples = isset($batch->id)  ? $batch->all_samples()  : [];


		if (isset($batch->id)) {
			$workflowstages = getWorkflowStage_Stages($batch->status);
			if (in_array($batch->status, ['Sample Verification', 'Sample Approval'])) {
				$report_format_config = SystemConfiguration::where('key', 'coa_report_format')->first();
				$report_formats = SystemConfiguration::where('configuration_type_id', $report_format_config->value)->get();
			}
			$disposal_date =  \Carbon\Carbon::parse($batch->receipt_date)->addMonths(3)->format('Y-m-d');
			// return response()->json($disposal_date);
			$contacts = getCrmCustomerContactSchedule($batch->crm_customer_id);
			// return response()->json($contacts);
			$batch_sample_codes = getBacthSampleCodes($batch->id);
			$payment_detail = InvoicePaymentDetail::where('batch_id', $batch->id)->get();
			$interlabs = InterLabLogView::where('sample_header_id', $batch->id)->orderBy('status', 'ASC')->orderBy('id', 'DESC')->get();
			// $equipment_data = $batch->get_captured();
			$approvers = BatchLabSectionApprover::where('batch_id', $batch->id)->get();
			// foreach ($equipment_data['items'] as $b => $d) {
			// 	foreach ($d as $a => $k) {
			// 		foreach ($k as $i => $e) {

			// 			if ($e == '') {
			// 				if (!isset($not_captured[$b])) {
			// 					$not_captured[$b] = array();
			// 					array_push($not_captured[$b], $a);
			// 				} else {
			// 					array_push($not_captured[$b], $a);
			// 				}
			// 			}
			// 		}
			// 	}
			// }
			$not_captured = CapturedResult::where('sample_header_id', $batch->id)->whereNull('result')->selectRaw('group_concat(analyte_code) as codes,sample_detail_code')->groupBy('sample_detail_id')->get();
			// return response()->json($test);
		}

		$selectedSampleType = \App\SampleType::find($batch->sample_type_id ?? 0) ?? false;
		$selected_analysis_types = isset($batch->sample_type_id) ? $selectedSampleType->analysis_types : [];
		if (isset($batch->id)) {
			if ($batch->is_qc_batch) {
				$standards = Standards::where('status', 1)->where('qc_type_id', $batch->qc_type_id)->get();
			} else {
				$standards =  Standards::where('status', 1)->get();
			}
		} else {
			$standards = [];
		}
		$defaultClient = $client;
		$client_portal = $portal;
		if (isset($batch->id)) {
			$attachments = BatchAttachment::where('batch_id', $batch->id)->get();

			if ($batch->in_ammendment_proccess == 1) {

				$ammendment = BatchAmmendment::where('batch_id', $batch->id)->where('version_number', $batch->is_amendment)->first();
				// return response()->json($batch,200);
				if (isset($ammendment->id)) {

					$samples = json_decode($ammendment->samples, true);
					$ammendable = array_keys($samples);
				}
				// return response()->json($ammendments,200);


			} else {

				$ammendable = $batch->all_samples()->pluck('sample_code');
			}
		} else {
			$ammendable = array();
			$attachments = [];
			// return response()->json($ammendable,200);
		}
		$role_a = SystemConfiguration::where('key', 'analyst_role_id')->first();
		$labs  = Lab::where('active', 1)->get();
		// $analysts = getUsersByRole('Analyst');
		$analysts = User::orderBy('name')->join('user_roles as ur', 'ur.user_id', '=', 'users.id')
			->join('roles as r', 'r.id', '=', 'ur.role_id')
			->where('r.id', $role_a->value)->where('users.active', 1)->where('users.is_support_staff', 0)->selectRaw('users.*')->get();

		// -----------------------------------
		// ---------------------------------------

		$customer = isset($batch->id) ? getCrmCustomerByID($batch->crm_customer_id) : [];
		$requestTypes = getRequestTypes();
		$notifiable_users  = getNotifiableUsers();
		$notesReminderType = getNotesReminderTypes();
		$clients = getClients();
		$active_company = getActiveCompany();
		$samples = SamplesCategory::where('sample_header_id', $batch->id)->get();
		return view('layouts.lab.sample-workflow.show-again', compact('batch', 'batchID', 'defaultClient', 'selectedSampleType', 'client_portal', 'ammendable', 'standards', 'attachments', 'not_captured', 'analysts', 'countries', 'accounts', 'methods', 'atachment_type', 'batch_scope', 'customer_survey', 'interlabs', 'labs', 'users', 'payment_detail', 'labsections', 'contacts', 'batch_sample_codes', 'report_formats', 'approvers', 'reportingUnits', 'conditions', 'products', 'headerDetails', 'status', 'workflowstages', 'workflows', 'clients', 'sample_types', 'samplingmethods', 'active_company', 'ammendments', 'samples', 'customer', 'requestTypes', 'notifiable_users', 'notesReminderType', 'disposal_date'));
	}

	public function getAnalysisTypeBySampleTypeIDAjax($sample_type_id)
	{
		return response()->json(AnalysisType::where('sample_type_id', $sample_type_id)->where('active', 1)->get());
	}
	public function getSampleConditionsAjax()
	{

		return response()->json(SampleCondition::where('active', 1)->get());
	}
	public function getSampleProductsAjax()
	{
		return response()->json(CompanyProduct::where('active', 1)->get());
	}
	public function getSampleStandardsAjax()
	{
		return response()->json(Standards::where('status', 1)->get());
	}
	public function getCrmCustomerSamplePointAjax($crm_id, $name)
	{
		$company_unit = CrmCompanyUnit::where('name', $name)->where('crm_customer_id', $crm_id)->first();
		return response()->json(isset($company_unit->id) ? SamplePoint::where('active', 1)->where('crm_company_unit_id', $company_unit->id)->get() : []);
	}
	public function getShowSampleParameterDataAjax($sample_id)
	{
		$captured_results = CapturedResultView::where('sample_detail_id', $sample_id)->get();
		$equipments = Equipment::where('active', 1)->get();
		$role_a = SystemConfiguration::where('key', 'analyst_role_id')->first();
		$analysts = UserRoleView::where('role_id', $role_a->value)->where('active', 1)->where('is_support_staff', 0)->orderBy('name')->get();
		$res = [
			"captured" => $captured_results,
			"equipments" => $equipments,
			"analysts" => $analysts
		];
		return response()->json($res);
	}

	public function methodNameFromId($inputObject, $inputKeysStr)
	{
		$inputKeys = explode(",", $inputKeysStr);
		$outputObject = [];
		foreach ($inputKeys as $key) {
			if (isset($inputObject[$key])) {
				$outputObject[$inputObject[$key]] = (int) $key;
			}
		}
		return $outputObject;
	}
	public function cloneBatchInformation(Request $request)
	{

		$batch_config = SystemConfiguration::where('key', 'batch_code_config')->first();
		foreach ($request->batch_code as $batch_code) {
			$batch = SampleHeader::where('batch_code', $batch_code)->first();
			$batch_data = SamplesCategory::where('batch_code', $batch_code)->first();

			$cust_code = str_split($batch_data->crm_code);
			$code = [];
			$loop = 0;
			$cont = [];
			foreach ($cust_code as $cc) {
				if ((int)$cc > 0) {
					array_push($cont, $loop);
				} elseif (is_string($cc) && $cc != '0') {
					array_push($code, $cc);
				}
				++$loop;
			}
			$tt = sizeof($cust_code) - 1;

			$ranges = range($cont[0], $tt);
			$values = [];
			if (sizeof($cont) < 2) {
				array_push($values, '0');
				array_push($values, $cust_code[$cont[0]]);
			} else {

				foreach ($ranges as $r) {
					array_push($values, $cust_code[$r]);
				}
			}

			$cP = implode('', $code) . $batch_config->value . implode('', $values) . $batch_data->sample_type_code;
			$config_batch_no = SystemConfiguration::where('key', 'batch_start_no')->first();
			if (!isset($config_batch_no->id)) {
				return redirect()->back()->with('error', 'Kindly set the start batch no');
			}
			$last_id = isset(SampleHeader::latest('id')->first()->id) ? SampleHeader::latest('id')->first()->id : 0;
			$batch_no_s = $config_batch_no->value + $last_id + 1;
			$final_no = '';
			if (strlen(strval($batch_no_s)) < 4) {
				$zerosss = str_repeat('0', 4 - strlen(strval($batch_no_s)));
				$final_no = $zerosss . '' . strval($batch_no_s);
			} else {
				$final_no = strval($batch_no_s);
			}
			$new_batch_code = $cP . '' . $final_no;

			$new_batch = $batch->replicate()->fill([
				"updated_at" => '',
				"created_at" => date('Y-m-d H:s:i.u'),
				'batch_code' => $new_batch_code,
				'receipt_date' => getTodayDate(),
				'status' => 'Samples Reception'
			]);
			$new_batch->save();
			$samples = SampleDetails::with('captured_results')
				->where('sample_header_id', $batch->id)->get();
			$relation_analysis = [];

			foreach ($samples as $sample) {
				// $config_start_no = SystemConfiguration::where('key', 'start_sample_no')->first();
				$sample_data = SamplesCategory::where('id', $sample->id)->first();
				$code = SampleDetails::where('lab_id', $sample_data->main_lab_id)->orderBy('id', 'DESc')->first()->sample_code;
				$last_sample = substr($code, 9, strlen($code));
				// $last_sample = isset(SampleDetails::latest('id')->first()->id) ? substr(SampleDetails::latest('id')->first()->sample_code,9,strlen(SampleDetails::latest('id')->first()->sample_code) -1) : $config_start_no->value;

				// return response()->json($request->sample_details['lab_id'][$k]);
				$sample_number = intval($last_sample)  + 1;
				$new_sample_code = 'S' . date('Y') . $sample_data->main_lab_code . $sample_number;

				$new_sample = $sample->replicate()->fill([
					'sample_code' => $new_sample_code,
					'sample_header_id' => $new_batch->id,
					'disposal_date' => \Carbon\Carbon::parse($new_batch->receipt_date)->addMonths(3)->format('Y-m-d')
				]);
				$new_sample->save();

				foreach (explode(',', $new_sample->analysis_type_id) as $at_id) {
					$relation_analysis[] = [
						"analysis_type_id" => $at_id,
						"batch_id" => $new_batch->id,
						"sample_detail_id" => $new_sample->id
					];
				}
				// $this->createDetailAnalysisRelation($new_batch->id, $new_sample->id, explode(',', $new_sample->analysis_type_id));

				$captured = $sample->captured_results;
				foreach ($captured as $c) {
					$new_captured = $c->replicate()->fill([
						'sample_header_id' => $new_batch->id,
						'sample_detail_id' => $new_sample->id,
						'sample_detail_code' => $new_sample->sample_code,
						'result' => '',
						'user_id' => auth()->user()->id,
						'remark' => '',
					]);
					$new_captured->save();
					$result = Result::where('captured_result_id', $c->id)->first();
					$new_result = $result->replicate()->fill([
						'captured_result_id' => $new_captured->id,
						'sample_header_id' => $new_batch->id,
						'sample_detail_id' => $new_sample->id,
						'sample_detail_code' => $new_sample->sample_code,
						'result' => '',
						'remarks' => '',
					]);
					$new_result->save();
				}
			}
			sampleAnalysisTypeRelation::insert($relation_analysis);
			$analysis_types_id = SampleAnalysisTypeRelation::where('batch_id', $new_batch->id)->pluck('analysis_type_id')->toArray();
			$analysis_max_report_time = AnalysisType::whereIn('id', $analysis_types_id)->max('reporting_time');
			$analytes_max_report_time = AnalysisElements::whereIn('analysis_type_id', $analysis_types_id)->max('reporting_time');
			$maxReportingTime = $analysis_max_report_time > $analytes_max_report_time ? $analysis_max_report_time : $analytes_max_report_time;
			$targetDateStr = "Target Date";
			$targetDate = \App\SampleDate::where('sample_header_id', $new_batch->id)->where('name', $targetDateStr)->first() ?? new \App\SampleDate;
			$targetDate->name = $targetDateStr;
			$targetDate->sample_header_id = $new_batch->id;
			$targetDate->date = \Carbon\Carbon::parse($new_batch->receipt_date)->addDays($maxReportingTime);
			$targetDate->save();

			$custodyDetails = array(
				"batch_id" => $new_batch->id,
				"comments" => $request->comments ?? '',
				"current" => array(
					"status" => $new_batch->status,
					"tracking_stage" => $new_batch->sample_tracking_stage,
				),
				"target" => array(
					"status" => $new_batch->status,
					"tracking_stage" => $new_batch->sample_tracking_stage,
				)
			);
			$this->updateChainofCustody($custodyDetails);
		}
		return redirect()->back()->with('success', 'Cloned batch created successfully');
	}
	public function getStandardValuesDataAjax()
	{
		$values = StandardValue::where('status', 1)->get();
		return response()->json($values);
	}
	public function updateStandardAnalyteLimit(Request $request)
	{
		$standard = Standards::where('code', $request->standard_id)->first();
		$standard_analyte = StandardAnalytes::where('standard_id', $standard->id)->where('analyte_id', $request->analyte_id)->first() ?? new StandardAnalytes();
		$standard_analyte->low = $request->low;
		$standard_analyte->high = $request->high;
		$standard_analyte->standard_value_id = $request->standard_valuetype;
		$standard_analyte->value_type = $request->limit_measure;
		$standard_analyte->standard_is_value = $request->value;
		$standard_analyte->standard_value_type = $request->standard_value_type == 1 ? 'is_range' : 'is_standard_value';
		$standard_analyte->analyte_id = $request->analyte_id;
		$standard_analyte->standard_id = $standard->id;
		$standard_analyte->is_active = 1;
		$standard_analyte->save();
		$value = 'NS';

		$value = $request->standard_value_type == 1 ? $request->low . ' - ' . $request->high : $value;
		$value = $request->standard_value_type == 2 && $request->limit_measure == '' ? StandardValue::find($request->standard_valuetype)->code : $value;
		$value = $request->standard_value_type == 2 && $request->limit_measure != '' ? $request->value.' '.$request->limit_measure  : $value;
		$format_value = $request->standard_value_type == 2 && $request->limit_measure != '' ? $request->value : $value;
		return response()->json(["format_value" => $value, "value" => $format_value]);
	}
	public function saveSampleAnalysisDate(Request $request){
		$sample = SampleDetails::where('sample_code',$request->sample_id)->first();
		
		$analysis_date = SampleAnalysisDates::where('sample_header_id',$request->batch_id)->where('sample_detail_id',$sample->id)->first() ?? new SampleAnalysisDates();
		if(isset($analysis_date->id)){
			$prev_dates = $analysis_date->analysis_dates != '' ? json_decode($analysis_date->analysis_dates,true) : array();
			// foreach($prev_dates as $key=>$value){
			// 	if($key == )
			// }
			if(isset($prev_dates[$request->lab_section_id])){
				$prev_dates[$request->lab_section_id] = $request->start_analysis_date;
			}else{
				$prev_dates[$request->lab_section_id] = $request->start_analysis_date;
				// array_push($prev_dates,[$request->lab_section_id=>$request->start_analysis_date]);

			}
			$analysis_date->start_analysis_date = $analysis_date->start_analysis_date > $request->start_analysis_date ? $request->start_analysis_date : $analysis_date->start_analysis_date;
		}else{
			$prev_dates = [];
			// array_push($prev_dates,[$request->lab_section_id=>$request->start_analysis_date]);
			$prev_dates[$request->lab_section_id] = $request->start_analysis_date;
			$analysis_date->start_analysis_date = $request->start_analysis_date;
		}
		$analysis_date->sample_header_id = $request->batch_id;
		$analysis_date->sample_detail_id =$sample->id;
		// return response()->json($prev_dates);

		$analysis_date->analysis_dates =json_encode($prev_dates);
		$analysis_date->save();
		return response()->json('success');
		
	}
	public function getSampleIntelabLogsApprovalStatus($sample_id){
		$sample = SampleDetails::where('sample_code',$sample_id)->first();
		$approval = InterLabLog::where('sample_id',$sample->id)->where('status',0)->first();
		return isset($approval->id) ? 1 : 0;
	}
	public function getSampleResultCapturedNot($sample_id){
		$sample = SampleDetails::where('sample_code',$sample_id)->first();
		$captured = CapturedResult::where('sample_detail_id',$sample->id)->WhereNotNull('result')->get()->count();
		$captured_not = CapturedResult::where('sample_detail_id',$sample->id)->WhereNull('result')->get()->count();
		return response()->json(['captured'=>$captured,"not_captured"=>$captured_not]);
	}
}

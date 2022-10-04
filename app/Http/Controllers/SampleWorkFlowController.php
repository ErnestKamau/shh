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
use App\QuotationDetails;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\PrettyPrinter\Standard;

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
		// return response()->json(getSampleWorkFLowTotals(), 200);
		if (!$status) {
			$status = getSampleWorflowStages()[0];
		}

		// return response()->json('test');
		$batches = SampleHeader::where('isactive', 1)->orderBy('receipt_date', 'desc');

		if ($status != "All Samples") {
			if ($status == "Schedule of Analysis") {
				$q = "Samples In Lab";
				$l = 1;
				$batches = $batches->where('status', $q)->where('schedule_sent', '<', $l);
			} else {
				$batches = $batches->where('status', $status);
			}
		} else {
			$batches = $batches->where('status', '!=', 'Completed');
		}

		$batches = $batches->get();
		foreach ($batches as $b) {
			$sample_codes = SampleDetails::where('sample_header_id', $b->id)->pluck('sample_code')->toArray();
			// return response()->json()
			$b['sample_codes'] = implode(',', $sample_codes);
		}


		$role_a = SystemConfiguration::where('key', 'analyst_role_id')->first();
		$analysts = User::orderBy('name')->join('user_roles as ur', 'ur.user_id', '=', 'users.id')
			->join('roles as r', 'r.id', '=', 'ur.role_id')
			->where('r.id', $role_a->value)->where('users.active', 1)->where('users.is_support_staff', 0)->selectRaw('users.*')->get();

		return view('layouts.lab.sample-workflow.index', compact('batches', 'status', 'analysts'));
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
				$analysis = $sample->analysis();
				$data = array();
				// return response()->json($batch,200);
				foreach ($analysis as $a) {

					if (!isset($data['Code'])) {
						$data['Code'] = $sample->sample_code;
					}
					if (!isset($data['Description'])) {
						$data['Description'] = getSampleTypeByID($batch->sample_type_id)->name;
					}

					if (!isset($data['Date Received'])) {
						$data['Date Received'] = $batch->receipt_date;
					}
					// if(!isset($data[$client->unit_configurable_name]) || !isset($data['unit'])){
					// 	if($client->unit_configurable_name != "" ){
					// 		$data[$client->unit_configurable_name] = $batch->crm_unit_name;
					// 	}else{
					// 		$data['unit'] = $batch->crm_unit_name;
					// 	}
					// 	// return response($data,200);
					// 	// trim($client->unit_configurable_name) != "" ? $data[$client->unit_configurable_name] : $data['unit'] = $batch->crm_unit_name;
					// }
					if (isset($data[$client->sample_point_configurable_name])) {
						if (isset($sample->sample_point->name)) {
							if ($data[$client->sample_point_configurable_name] != $sample->sample_point->name) {
								$value = $data[$client->sample_point_configurable_name];
								$data[$client->sample_point_configurable_name] = $value . ',' . $sample->sample_point->name;
							}
						} else {
							if ($data[$client->sample_point_configurable_name] != 'n/a') {
								$value = $data[$client->sample_point_configurable_name];
								$data[$client->sample_point_configurable_name] = $value . ',n/a';
							}
						}
					}
					if (isset($data['Sample Point'])) {
						if (isset($sample->sample_point->name)) {
							if ($data['Sample Point'] != $sample->sample_point->name) {

								$value = $data['Sample Point'];
								$data['Sample Point'] = $value . ',' . $sample->sample_point->name;
							}
						} else {
							if ($data['Sample Point'] != 'n/a') {
								$value = $data['Sample Point'];
								$data['Sample Point'] = $value . ',n/a';
							}
						}
					}
					if (!isset($data[$client->sample_point_configurable_name]) || !isset($data['Sample Point'])) {
						trim($client->sample_point_configurable_name) != "" ? $data[$client->sample_point_configurable_name] = $sample->sample_point->name ?? 'n/a' : $data['Sample Point'] = $sample->sample_point->name ?? 'n/a';
					}
					if (isset($data[$client->product_configurable_name])) {
						if (isset($sample->product->name)) {
							if ($data[$client->product_configurable_name] != $sample->product->name) {
								$value = $data[$client->product_configurable_name];
								$data[$client->product_configurable_name] = $value . ',' . $sample->product->name;
							}
						} else {
							if ($data[$client->product_configurable_name] != 'n/a') {
								$value = $data[$client->product_configurable_name];
								$data[$client->product_configurable_name] = $value . ',n/a';
							}
						}
					}
					if (isset($data['product'])) {
						if (isset($sample->product->name)) {
							if ($data['product'] != $sample->product->name) {
								$value  = $data[$client->product_configurable_name];
								$data[$client->product_configurable_name] = $value . ',' . $sample->product->name;
							}
						} else {
							if ($data['product'] != 'n/a') {
								$value = $data[$client->product_configurable_name];
								$data[$client->product_configurable_name] = $value . ',n/a';
							}
						}
					}
					// if(!isset($data[$client->product_configurable_name]) && !isset($data['product'])){
					// 	trim($client->product_configurable_name) != "" ? $data[$client->product_configurable_name] = $sample->product->name ?? 'n/a' : $data["product"] = $sample->product->name ?? 'n/a';
					// }
					if (isset($data['Test To Be Done'])) {
						if ($data['Test To Be Done'] != $a->name) {
							$value = $data['Test To Be Done'];
							$data['Test To Be Done'] = $value . ',' . $a->name;
						}
					}
					if (!isset($data['Test To Be Done'])) {
						$data['Test To Be Done'] = $a->name;
					}
					if (isset($data['Lab'])) {
						if ($data['Lab'] != $a->lab->name . " - " . date('Y-m-d')) {
							$value = $data['Test To Be Done'];
							$data['Test To Be Done'] = $value . ',' . $a->lab->name . " - " . date('Y-m-d');
						}
					}
					if (!isset($data['Test To Be Done'])) {
						$data['Test To Be Done'] = $a->lab->name . " - " . date('Y-m-d');
					}
					// $data = array(
					// 	"Code" => $sample->sample_code,
					// 	"Client" => $client->name,
					// 	trim($client->unit_configurable_name) != "" ? $client->unit_configurable_name : 'unit' => $batch->crm_unit_name,
					// 	trim($client->sample_point_configurable_name) != "" ? $client->sample_point_configurable_name : "sample_point" => $sample->sample_point->name ?? 'n/a',
					// 	trim($client->product_configurable_name) != "" ? $client->product_configurable_name : "product" => $sample->product->name ?? 'n/a',
					// 	"Analysis" => $a->name,
					// 	"Lab" => $a->lab->name . " - " . date('Y-m-d')
					// );
				}
				$labels[] = $data;
			}
			// return response()->json($labels,200);
			$batch->sample_tracking_stage = $stage->id;
			$batch->save();

			$hasCapturedResults = false;

			$analysis_to_be_done = array();

			$batch_analysis = $batch->samples;


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

			foreach ($analysis_to_be_done as $atbs) {
				foreach ($atbs["analysis_to_do"] as $a) {
					$analytes = $a->active_analysis_elements();

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

						$result->save();
					}
				}
			}
		}

		// return response()->json($labels, 200);

		return view('layouts.lab.sample-workflow.labels', compact('labels'));
	}

	public function add_batch_info(Request $request, $batch)
	{
		$selectedCustomer = CRMCustomer::find($request->crm_customer_id);
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

		$header->receipt_date = $request->receipt_date;
		$header->date_collected = $request->date_collected;
		$header->batch_scope = $request->batch_scope;
		$header->customer_survey = $request->customer_survey;

		if ($isInReception) {
			$header->crm_customer_id = $request->crm_customer_id;
			$header->sample_type_id = $request->sample_type_id;
			$header->crm_unit_name = $request->crm_unit_name;

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

		$header->description = $request->description;
		$header->document_number = $request->document_number;
		$header->importer_address = $request->importer_address;
		$header->date_expected = $request->date_expected;
		$header->quote_id = $request->quote_id;
		$header->radio_active_levels = $request->radio_active_levels;
		$header->receiving_officer_name = $request->receive_by;
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

		$route_obj = ['batch' => $header->id];

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
		foreach ($request->sample_details['sample_code'] as $k => $v) {
			$detailId = $request->sample_details['detail_header'][$k];

			$detail = $detailId && $detailId != "undefined" ? SampleDetails::find($detailId) : new SampleDetails;
			$detail->sample_header_id = $SampleHeader->id;

			if (!isset($detail->sample_code)) {
				$config_start_no = SystemConfiguration::where('key', 'start_sample_no')->first();
				if (!isset($config_start_no->id)) {
					return redirect()->back()->with('error', 'Kindly configure the start sample No');
				}
				$last_sample = isset(SampleDetails::latest('id')->first()->id) ? explode('-', SampleDetails::max('sample_code'))[1]  : $config_start_no->value;
				$sample_number = intval($last_sample)  + 1;
				$detail->sample_code = 'S0513-' . $sample_number;
			}
			if (isset($detail->id)) {
				$current_analysis = explode(',', $detail->analysis_type_id);
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
			$detail->lab_sub_no = $request->sample_details['submission_no'][$k];

			$detail->main_standard = $request->sample_details['main_standard'][$k];
			$detail->secondary_standard = $request->sample_details['secondary_standard'][$k];


			$detail->save();

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
		$batch->sample_tracking_stage = $stage->id;
		$batch->save();

		$batch_analysis = $batch->samples;
		$hasCapturedResults = false;

		$analysis_to_be_done = array();

		$batch_analysis = $batch->samples;


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

		// return response()->json(explode(',',$labarr[1]),200);

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

					$captured->analyte_status_contracted = $lab->is_external;

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
					$result->analyte_status_contracted = $lab->is_external;
					$result->save();
				}
			}
		}

		return redirect()->back()->within('success', 'Batch Samples updated.');
	}
	public function addBatchSamplesDynamically()
	{
		$batches = SampleHeader::all();
		foreach ($batches as $batch) {
			$batch_analysis = $batch->samples;
			$hasCapturedResults = false;

			$analysis_to_be_done = array();

			$batch_analysis = $batch->samples;


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

						$captured->analyte_status_contracted = $lab->is_external;

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
						$result->analyte_status_contracted = $lab->is_external;
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

	public function show($batch, $client = false, $portal = false)
	{
		$perms = ['Laboratory', 'components', 'RFT Form', 'View'];
		$permsD = ['Laboratory', 'components', 'RFT Form', 'Delete'];
		$check_perm_view = auth()->user()->check_permission($perms);
		$check_perm_delete = auth()->user()->check_permission($permsD);
		// return response()->json($check_perm_view);
		$batchID = $batch;

		$batch = SampleHeader::find($batchID);
		$batch_scope = SystemConfiguration::where('key', 'batch_scope')->first();
		$customer_survey = SystemConfiguration::where('key', 'customer_survey')->first();
		$countries = Country::orderBy('name')->get();
		$methods = AnalysisMethod::where('active', 1)->get();
		$account_settings = getConfigTypeByName('Account Settings');
		$atachment_type = SystemConfiguration::where('key', 'attachment_type')->get();
		if (isset($account_settings->id)) {
			$accounts = getconfigByID($account_settings->id);
		} else {

			$accounts = array();
		}


		// $samples = $batch->all_samples();
		// return response()->json($samples,200);
		$not_captured = [];
		if (isset($batch->id)) {

			$equipment_data = $batch->get_captured();
			foreach ($equipment_data['items'] as $b => $d) {
				foreach ($d as $a => $k) {
					foreach ($k as $i => $e) {

						if ($e == '') {
							if (!isset($not_captured[$b])) {
								$not_captured[$b] = array();
								array_push($not_captured[$b], $a);
							} else {
								array_push($not_captured[$b], $a);
							}
						}
					}
				}
			}
		}

		$selectedSampleType = \App\SampleType::find($batch->sample_type_id ?? 0) ?? false;
		$standards = Standards::where('status', 1)->get();
		$defaultClient = $client;
		$client_portal = $portal;
		if (isset($batch->id)) {
			$attachments = BatchAttachment::where('batch_id', $batch->id)->get();
			$config_attach = SystemConfiguration::where('key', 'attachment_type')->where('value', 'RFT Form')->first();
			foreach ($attachments as $attach) {
				if ($attach->attachment_type == $config_attach->id) {
					$attach->view = $check_perm_view == false ? 0 : 1;
					$attach->delete = $check_perm_delete == false ? 0 : 1;
				} else {

					$attach->view = 1;
					$attach->delete = 1;
				}
			}

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
		// $analysts = getUsersByRole('Analyst');
		$analysts = User::orderBy('name')->join('user_roles as ur', 'ur.user_id', '=', 'users.id')
			->join('roles as r', 'r.id', '=', 'ur.role_id')
			->where('r.id', $role_a->value)->where('users.active', 1)->where('users.is_support_staff', 0)->selectRaw('users.*')->get();
		return view('layouts.lab.sample-workflow.show', compact('batch', 'batchID', 'defaultClient', 'selectedSampleType', 'client_portal', 'ammendable', 'standards', 'attachments', 'not_captured', 'analysts', 'countries', 'accounts', 'methods', 'atachment_type', 'check_perm_view', 'check_perm_delete', 'batch_scope', 'customer_survey'));
	}

	public function fetch_unit_stuff($name, $client)
	{
		$unit = CRMCompanyUnit::where('name', $name)->where('crm_customer_id', $client)->first();

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
			$batch->verify_user_id = '';
			$batch->approval_date = '';
			$batch->save();
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

		if ($batch->status == 'Sample Verification' && $status == 'Sample Approval') {

			$batch->verify_user_id = auth()->user()->id;
		}
		if ($status == 'Reports for Collection') {
			$customer = getCrmCustomerByID($batch->crm_customer_id);
			$account_status = SystemConfiguration::find($customer->account_status);
			if (isset($account_status->id)) {
				if ($account_status->value != 'Account Holder(OK)') {
					if ($batch->invoice_id == 0) {
						return redirect()->back()->with('error', 'The following Batch has no invoice attached to it!');
					}
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
				->join('captured_results as cr', 'cr.analyte_id', '=', 'analysis_elements.analyte_id')
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
				->where('analyte_id', $i->id)->where('equipment_id', $i->equipment_id)->first();

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
				$captured->analyte_accredited = 1;
			} else {
				$captured->analyte_accredited = 0;
			}
			$captured->method_id = $request->method_id[$cID];
			$captured->result = $request->result[$cID];
			$captured->result_reporting_symbol = $request->result_reporting_symbol[$cID];
			$captured->operator_id = $request->operators[$cID] ?? 0;


			$captured->remark = $request->remark[$cID];
			$standard_main = Standards::where('code', $request->main_standard[$cID])->first();
			$sec_standard = Standards::where('code', $request->secondary_standard[$cID])->first();
			if (isset($standard_main->id) && $sec_standard->id) {
				$main_standard_analyte = StandardAnalytes::where('analyte_id', $captured->analyte_id)->where('standard_id', $standard_main->id)->first();
				$sec_standard_analyte = StandardAnalytes::where('analyte_id', $captured->analyte_id)->where('standard_id', $sec_standard->id)->first();
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
								$response = 'FAIL';
							} else {

								$response = $result <= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
							}
							return response()->json($response, 200);
						} else {
							if ($analyte_guide->standard_is_value != '') {
								if (trim($reporting_symbol) == '>') {
									$response = 'FAIL';
								} else {

									$response = $result <= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
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


	public function process_results($batch_id, $internal = false)
	{
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

			->selectRaw('captured_results.result,captured_results.main_standard_id,captured_results.secondary_standard_id,captured_results.remark,captured_results.analysis_type_id,captured_results.analyte_id,captured_results.analyte_status_contracted,captured_results.analyte_accredited, captured_results.id, ae.decimal_places, ae.significant_figures, ae.reporting_unit, ae.lod, ae.level, ae.hod,captured_results.result_reporting_symbol')
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
			$eresult->save();
			// if ($c->lod && $result < floatval($c->lod)) {
			// 	$result  = $c->lod;
			// 	$eresult->reporting_symbol = '<';
			// }


			if (isset($main_standard->standard_value_type)) {
				if ($main_standard->standard_value_type == 'is_range') {
					$range = $main_standard->low . " - " . $main_standard->high;
					$eresult->guide = $range;

					$eresult->remarks = (floatval($main_standard->low) <= $result) && ($result <= floatval($main_standard->high)) ? 'PASS' : 'FAIL';
				} else {

					$standard_value = StandardValue::find($main_standard->standard_value_id);
					if ($standard_value->code == 'IsValue') {
						$eresult->guide = $main_standard->standard_is_value;
					} else {
						$eresult->guide = $standard_value->code;
					}


					if (is_numeric($result)) {
						$type = gettype($main_standard->standard_is_value);
						if ($type == 'integer' || $type == 'double') {
							if (trim($c->result_reporting_symbol) == '>') {
								$eresult->remarks = 'FAIL';
							} else {

								$eresult->remarks = $result <= floatval($main_standard->standard_is_value) ? 'PASS' : 'FAIL';
							}
						} else {
							if ($main_standard->standard_is_value != '') {
								if (trim($c->result_reporting_symbol) == '>') {
									$eresult->remarks = 'FAIL';
								} else {

									$eresult->remarks = $result <= floatval($main_standard->standard_is_value) ? 'PASS' : 'FAIL';
								}
							} else {
								if (strtoupper(trim($standard_value->code)) == "NS") {
									$eresult->remarks = '-';
								} elseif (strtoupper(trim($standard_value->code)) == "NIL") {
									$eresult->remarks = $result <= 0  ? 'PASS' : 'FAIL';
								} elseif (strtoupper(trim($standard_value->code)) == "ND") {
									$eresult->remarks = $result <= 0  ? 'PASS' : 'FAIL';
								} else {
									$eresult->remarks = '-';
								}
							}
							// $eresult->remarks = trim($main_standard->standard_is_value) == "" ? "PASS" : "++";

						}
					} else {
						// return response()->json($main_standard,200);
						$standard_value = StandardValue::find($main_standard->standard_value_id);
						if (strtoupper($result) == 'TN') {
							$eresult->remarks = 'FAIL';
						} elseif (strtoupper($result) == 'ND' && strtoupper(trim($standard_value->code)) == "NS") {
							$eresult->remarks = '-';
						} elseif (strtoupper($result) == 'ND' && strtoupper(trim($standard_value->code)) == "NIL") {
							$eresult->remarks = 'PASS';
						} elseif (strtoupper($result) == 'ND' && strtoupper(trim($standard_value->code)) == "ND") {
							$eresult->remarks = 'PASS';
						} elseif (strtoupper($result) == 'NIL' && strtoupper(trim($standard_value->code)) == "ND") {
							$eresult->remarks = 'PASS';
						} else {
							$eresult->remarks = '-';
						}
					}
				}
			} else {
				$eresult->guide = 'NS';
				$eresult->remarks = '-';
			}
			if (isset($secondary_standard->id)) {

				$sec_standard_value = StandardValue::find($secondary_standard->standard_value_id);
			}
			if (isset($secondary_standard->standard_value_type)) {
				if ($secondary_standard->standard_value_type == 'is_range') {
					$range = $secondary_standard->low . " - " . $secondary_standard->high;
					$eresult->seond_guide = $range;
				} else {
					if ($sec_standard_value->code == 'IsValue') {
						$eresult->seond_guide = $secondary_standard->standard_is_value;
					} else {

						$eresult->seond_guide = $sec_standard_value->code;
					}
				}
			} else {
				if (isset($secondary_standard->id)) {

					$eresult->seond_guide = $sec_standard_value->code;
				}

				// $eresult->remarks = '-';
			}

			// if ($c->hod && $result > floatval($c->hod)) {
			// 	$result  = $c->hod;
			// 	$eresult->reporting_symbol = '>';
			// }

			// if (intval($c->significant_figures) && intval($c->significant_figures > 0)) {
			// 	$result = sigFig($result, intval($c->significant_figures));
			// } else {
			// 	if (trim($c->decimal_places) != "") {
			// 		$result = round($result, intval($c->decimal_places));
			// 	}
			// }

			$eresult->result = $result;
			$eresult->reporting_symbol = $c->result_reporting_symbol;
			$eresult->save();
			// return response()->json($eresult,200);
			$arr[] = $eresult;
		}
		$header = SampleHeader::find($batch_id);
		$header->set_date("Processing Date", \Carbon\Carbon::now(), true);
		// return response()->json($header,200);

		// return response()->json($arr, 200);

		if ($internal) {
			return $header;
		}

		return redirect()->route('process-pdf-report', ['batch_id' => $batch_id]);
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
	private function refactorReportingTime($id){
		$actual_c =CapturedResult::find($id);
		$analysis_type_ids = CapturedResult::where('sample_header_id',$actual_c->sample_header_id)->pluck('analysis_type_id')->toArray();
		$unique_typeIds = array_unique($analysis_type_ids);
		$analyte_ids = CapturedResult::where('sample_header_id',$actual_c->sample_header_id)->pluck('analyte_id')->toArray();
		$unique_analyteIds = array_unique($analyte_ids);

		$analysis_max_report_time = AnalysisType::whereIn('id', $unique_typeIds)->max('reporting_time');
		$analytes_max_report_time = AnalysisElements::whereIn('analysis_type_id', $unique_typeIds)->whereIn('analyte_id',$unique_analyteIds)->max('reporting_time');
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
			$batch = SampleHeader::where('batch_code', $code)->where('isactive',1)->first();
			$customer = CRMCustomer::find($batch->crm_customer_id);
			$samples = SampleDetails::where('sample_header_id', $batch->id)->get();
			$start =  SampleDetails::where('sample_header_id', $batch->id)->first();
			$end =  SampleDetails::where('sample_header_id', $batch->id)->orderBy('id', 'DESC')->first();
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
		if ($batch->verify_user_id == auth()->user()->id) {
			return redirect()->back()->with('error', 'You are not allowed to approve this batch');
		}
		$batch->approve_user_id = auth()->user()->id;
		$batch->approval_date = getTodayDate();
		$batch->save();
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

	public function fillCapturedresultOperator(){
		$captured_reults  = CapturedResult::where('operator_id',0)->get();
		foreach($captured_reults as $c){
			$ae = AnalysisElements::where('analyte_id',$c->analyte_id)->where('analysis_type_id',$c->analysis_type_id)->first();
			if(isset($ae->id)){
				$c->operator_id = $ae->operator_id;
			}
			$c->save();
		}
		return response()->json('success');
	}
}
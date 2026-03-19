<?php

namespace App\Http\Controllers;

use App\AnalysisElements;
use App\AnalysisMethod;
use App\AnalysisType;
use App\Analyte;
use App\BatchAmmendment;
use App\BatchAttachment;
use App\BatchComment;
use App\BatchLabSectionApprover;
use App\CapturedResult;
use App\CapturedResultView;
use App\Company;
use App\Country;
use App\Http\Controllers\System\SystemNotifications;
use App\InterLabLog;
use App\InterLabLogView;
use App\InventorySubCategories;
use App\Invoice;
use App\InvoiceDetails;
use App\InvoicePaymentDetail;
use App\JobDescription;
use App\Lab;
use App\LabSectionApproverRelationShip;
use App\Models\CRM\CompanyProduct;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\SamplePoint;
use App\Models\Equipments\Equipment;
use App\Models\Lab\TatCaptured;
use App\Models\Lab\TatCapturedView;
use App\Models\QcModule\Configurations\QcSchemes;
use App\Models\QcModule\Configurations\QcTypes;
use App\Models\QcModule\QCProcessedResults;
use App\Models\SubmissionFormInstance;
use App\Models\System\SystemConfiguration;
use App\Services\SubmissionFormPdfService;
use App\ModulePreConfigs;
use App\Pricelist;
use App\PricelistCustomer;
use App\PricelistItem;
use App\QuotationDetails;
use App\Result;
use App\SampleAnalysisDates;
use App\SampleAnalysisStage;
use App\SampleAnalysisTypeRelation;
use App\SampleAnalysisTypeRelationView;
use App\SampleCondition;
use App\SampleDate;
use App\SampleDetails;
use App\SampleHeader;
use App\SamplesCategory;
use App\SampleType;
use App\SchoolContacts;
use App\StandardAnalytes;
use App\Standards;
use App\StandardValue;
use App\TaxRegime;
use App\User;
use App\UserRole;
use App\UserRoleView;
use App\ZohoCustomers;
use App\ZohoPricelist;
use Illuminate\Http\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\BatchAttachmentAnnotation;
use setasign\Fpdi\TcpdfFpdi;
use Illuminate\Support\Facades\Log;
use App\Models\Procedures\ProcedureWorksheet;
use App\Services\ProcedureWorksheetPdfService;

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

        return view('livewire.layout.sample-workflow', [
            'status' => $status,
            'initialFilters' => $request->all(),
        ]);
    }

    public function print_labels(Request $request)
    {
        // return response()->json(getSampleWorkFLowTotals(), 200);
        $ids = $request->sample_code;

        if (count($ids) == 0) {
            return redirect()->back()->with('error', 'No Samples selected');
        }

        $labels = [];

        foreach ($ids as $batch_code) {
            $batch = SampleHeader::with(['receivingofficer'])->where('batch_code', $batch_code)->first();
            $strStage = 'Sample Labeling';
            $samWk = 'Samples Reception';

            $stage = SampleAnalysisStage::where('name', $strStage)->where('sample_workflow', $samWk)->first();

            $custodyDetails = [
                'batch_id' => $batch->id,
                'comments' => $request->comments ?? '',
                'current' => [
                    'status' => $batch->status,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
                'target' => [
                    'status' => $samWk,
                    'tracking_stage' => $stage->id,
                ],
            ];
            $this->updateChainofCustody($custodyDetails);

            $samples = $batch->all_samples();

            // echo json_encode($batch);
            // return;
            $client = $batch->client;

            // Get target date for the batch
            $targetDate = $batch->get_date('Target Date');
            $targetDateFormatted = $targetDate ? date('Y-m-d', strtotime($targetDate->date)) : 'N/A';

            foreach ($samples as $sample) {
                // return response()->json(SampleAnalysisTypeRelationView::where('sample_detail_id',$sample->id)->pluck('analysis_type_name')->toArray(),200);
                $analysis = $sample->analysis();
                $data = [];
                !isset($data['ref_no']) ? $data['ref_no'] = $sample->sample_code : $data;

                // Add client name from batch
                !isset($data['client_name']) ? $data['client_name'] = $client->name ?? 'N/A' : $data;

                // Add sample point name from sample detail
                $samplePoint = $sample->sample_point;
                !isset($data['sample_point']) ? $data['sample_point'] = (isset($samplePoint->name) ? $samplePoint->name : 'N/A') : $data;

                // Add sample code (separate from ref_no which is already there)
                !isset($data['sample_code']) ? $data['sample_code'] = $sample->sample_code : $data;

                // Add analysis types (using getAnalysisRelation method)
                !isset($data['analysis_types']) ? $data['analysis_types'] = $sample->getAnalysisRelation() ?: 'N/A' : $data;

                // Add target date
                !isset($data['target_date']) ? $data['target_date'] = $targetDateFormatted : $data;

                // Keep legacy fields for reference (commented out)
                // !isset($data['Markings']) ? $data['Markings']  = $sample->comments : $data;
                // !isset($data['date_received']) ? $data['date_received'] = $batch->receipt_date : $data;
                // !isset($data['test']) ? $data['test'] = implode(', ', $sample->analyteNames() ?? []) : $data;
                // !isset($data['received_by']) ? $data['received_by'] = $batch->receivingofficer->name : $data;
                // !isset($data['sample_type']) ? $data['sample_type'] = getSampleTypeByID($batch->sample_type_id)->name : $data;
                // !isset($data['time']) ? $data['time'] = $batch->radio_active_levels : $data;

                // !isset($data['Date Expected']) ? $data['Date Expected'] = date('Y-m-d', strtotime($batch->get_date('Target Date')['date'])) : $data;
                // !isset($data['Disposal Date']) ? $data['Disposal Date'] = $sample->disposal_date : $data;


                $labels[] = $data;
            }
            // return response()->json($labels,200);
            $batch->sample_tracking_stage = $stage->id;
            $batch->save();
        }

        // return response()->json($labels, 200);

        return view('layouts.lab.sample-workflow.labels', compact('labels'));
    }

    public function add_batch_info(Request $request, $batch)
    {
        Log::info(json_encode($request->all(), JSON_PRETTY_PRINT));
        if (isset($request->is_qc_batch)) {
            $qc_customer_id = SystemConfiguration::where('key', 'qc_customer_id')->first();
            if (!isset($qc_customer_id->id)) {
                return redirect()->back()->with('error', 'Kindly set company QC Customer first');
            }
            $selectedCustomer = CRMCustomer::find($qc_customer_id->value);
            if (isset($request->repeat_samples_id) && $request->repeat_samples_id != '') {
                $repeat_samples = SampleDetails::whereIn('id', $request->repeat_samples_id)->get();
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
            if ((int) $cc > 0) {
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

        $cP = 'BA' . $batch_config->value . implode('', $values) . $selectedSampleType->code;
        $isNew = false;
        $isInReception = false;
        $header = SampleHeader::find($batch) ?? new SampleHeader();
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

        if (isset($header->status) && in_array($header->status, ['Samples Reception', 'Samples In Lab', 'Sample Verification', 'Sample Approval', 'Reports for Collection', 'Reports In Payment', 'Completed'])) {
            $isInReception = true;
        }

        if (!isset($request->is_bl_save)) {
            $header->receipt_date = $request->receipt_date;
            $header->date_collected = $request->date_collected;
            $header->batch_scope = $request->batch_scope;
            $header->customer_survey = $request->customer_survey;
            $header->is_qc_batch = isset($request->is_qc_batch);
            $header->qc_type_id = $request->qc_type_id;
            $header->qc_scheme_id = $request->qc_scheme_id;
            $header->repeat_batch_id = isset($request->repeat_sample_id) ? $request->repeat_batch_id : 0;
            $header->repeat_sample_id = isset($request->repeat_sample_id) && $request->repeat_sample_id > 0 ? $request->repeat_sample_id : $header->repeat_sample_id;
            $header->begin_proccess = isset($request->is_qc_batch) ? 1 : 0;
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
            // $header->invoice_amount = $request->invoice_amount;
            $header->lab_section_ids = implode(',', $request->lab_section_ids ?? []);
            $header->crm_contact_id = $request->crm_contact_id;
            $header->schedule_customer_email = $request->customer_email;

            if ($isInReception) {
                $header->sample_type_id = $request->sample_type_id;
                if (isset($request->is_qc_batch)) {
                    $qc_customer_id = SystemConfiguration::where('key', 'qc_customer_id')->first();
                    $qc_customer_unit = SystemConfiguration::where('key', 'qc_customer_unit')->first();

                    $header->crm_customer_id = $qc_customer_id->value;

                    $header->crm_unit_name = $qc_customer_unit->value;
                    $header->qc_type_id = $request->qc_type_id;
                    $header->qc_scheme_id = $request->qc_scheme_id;
                    $header->repeat_sample_id = implode(',', $request->repeat_samples_id) ?? '';
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
            $header->quote_id = $request->quote_id;
            $header->sampling_method_id = $request->sampling_method_id;
            $header->radio_active_levels = $request->radio_active_levels;
            $header->receiving_officer_name = $request->receive_by;
            $header->receiving_officer = $request->receive_by;
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
        $header->submit_by = $request->submit_by;
        $header->radio_active_levels = $request->radio_active_levels;
        $header->kra_office_ref = $request->kra_office_ref;
        $header->use_of_goods = $request->use_of_goods;
        // $header->how_sample_was_obtained = $request->how_sample_was_obtained;
        // $header->declared_commodity_code = $request->declared_commodity_code;
        // $header->declared_amount = $request->declared_amount ?? 0;
        // $header->net_quantity_and_unit_of_quantity = $request->net_quantity_and_unit_of_quantity;
        // $header->sample_appearance_description = $request->sample_appearance_description;
        // $header->kra_office_station = $request->kra_office_station;
        // $header->where_sample_was_obtained = $request->where_sample_was_obtained;
        // $header->importer_address = $request->importer_address;
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

        Log::info("-------------------------------------");

        if (isset($request->repeat_samples_id) && $request->repeat_samples_id != '') {
            $sample_point = SystemConfiguration::where('key', 'qc_sample_point_id')->first();
            foreach ($repeat_samples as $old_sample) {
                $new_sample = $old_sample->replicate();
                $lab = Lab::find($old_sample->lab_id);
                $code = SampleDetails::orderBy('id', 'DESC')->first();
                $last_sample = isset($code->id) ? $code->sample_no : $lab->start_sample_no;

                // $last_sample = isset(SampleDetails::latest('id')->first()->id) ? substr(SampleDetails::latest('id')->first()->sample_code,9,strlen(SampleDetails::latest('id')->first()->sample_code) -1) : $config_start_no->value;

                // return response()->json($request->sample_details['lab_id'][$k]);
                $sample_number = intval($last_sample) + 1;
                // $sample_number = str_pad($sample_number, 4, '0', STR_PAD_LEFT);

                $sample_code = 'S' . date('Y') . $lab->code . $selectedSampleType->code . sprintf('%0' . '4' . 'd', $sample_number);
                $sample_no = sprintf('%0' . '4' . 'd', $sample_number);
                $report_number = 'LR/' . $selectedSampleType->code . '/' . date('Y') . '/' . $lab->code . '/' . sprintf('%0' . '4' . 'd', $sample_number);
                $new_sample->fill([
                    "sample_header_id" => $header->id,
                    "sample_code" => $sample_code,
                    "sample_no" => $sample_no,
                    "report_number" => $report_number,
                    "sample_point_id" => $sample_point->value,
                ]);
                $new_sample->save();
                $this->createDetailAnalysisRelation($header->id, $new_sample->id, explode(',', $new_sample->analysis_type_id));
                $captured_results = CapturedResult::where('sample_detail_id', $old_sample->id)->get();
                foreach ($captured_results as $c_result) {
                    $new_captured = $c_result->replicate();
                    $new_captured->fill([
                        "sample_detail_id" => $new_sample->id,
                        "sample_header_id" => $header->id,
                        "sample_detail_code" => $new_sample->sample_code,
                        "result" => null,
                        "remark" => null,
                        "repeat_captured_id" => $c_result->id,
                    ]);
                    $new_captured->save();
                    $result = Result::where('captured_result_id', $c_result->id)->first();
                    $new_result = $result->replicate();
                    $new_result->fill([
                        "captured_result_id" => $new_captured->id,
                        "sample_detail_id" => $new_sample->id,
                        "sample_header_id" => $header->id,
                        "sample_detail_code" => $new_sample->sample_code,
                        "result" => null,
                        "remark" => null,
                        "repeat_results_id" => $result->id,
                    ]);
                    $new_result->save();
                }
            }
        }
        if (isset($request->is_qc_batch) && $request->repeat_samples_id != '') {
            $batch_a_types = SampleAnalysisTypeRelationView::where('batch_id', $header->id)->pluck('analysis_type_id')->toArray();
            $analysis_time = AnalysisType::whereIn('id', $batch_a_types)->max('reporting_time');
            $analytes_ids = CapturedResult::where('sample_header_id', $header->id)->pluck('analyte_id')->toArray();
            $element_time = AnalysisElements::whereIn('analysis_type_id', $batch_a_types)->whereIn('analyte_id', $analytes_ids)->max('reporting_time');
            $maxReportingTime = $analysis_time > $element_time ? $analysis_time : $element_time;
        } else {
            $maxReportingTime = 0;
        }
        $targetDateStr = 'Target Date';
        $targetDate = \App\SampleDate::where('sample_header_id', $header->id)->where('name', $targetDateStr)->first() ?? new \App\SampleDate();
        $targetDate->name = $targetDateStr;
        $targetDate->sample_header_id = $header->id;
        $targetDate->date = \Carbon\Carbon::parse($header->receipt_date)->addDays($maxReportingTime);
        $targetDate->save();

        if ($isNew) {
            $custodyDetails = [
                'batch_id' => $header->id,
                'comments' => $request->comments ?? '',
                'current' => [
                    'status' => $header->status,
                    'tracking_stage' => $header->sample_tracking_stage,
                ],
                'target' => [
                    'status' => $header->status,
                    'tracking_stage' => $header->sample_tracking_stage,
                ],
            ];
            $this->updateChainofCustody($custodyDetails);
        }

        $route_obj = ['batch' => $header->id, 'client' => 0, 'portal' => 0, 'status' => $header->status];

        if ($request->has('is_client_order')) {
            $route_obj['client'] = $request->crm_customer_id;
        } else {
            $header->set_date('Login Date', $header->created_at, true);
        }

        return redirect()->route('view-batch-details', $route_obj)->within('success', 'Batch Info added.');
    }

    public function add_batch_samples(Request $request, $batch)
    {
        // return response()->json($request->all(), 200);
        $SampleHeader = SampleHeader::with(['sample_type'])->find($batch);

        $selectedCustomer = CRMCustomer::find($SampleHeader->crm_customer_id);
        $selectedSampleType = SampleType::find($SampleHeader->sample_type_id);

        $configuration = SystemConfiguration::where('key', 'sample_code_naming')->first();
        if (isset($configuration->id)) {
            $codePrefix = $configuration->value . '-';
        } else {
            return redirect()->back()->with('error', 'Setup the naming convention of samples in system configurations');
        }

        $samplesRequiringStorage = ['samples' => [], 'store_ids' => []];

        // return response()->json($request->all(), 200);
        $maxReportingTime = 0;
        $currentAnalysisSample = [];
        $duplicate_samples = [];
        $duplicate_samples_ids = [];
        $duplicateDataSampleIds = [];

        foreach ($request->sample_details['sample_code'] as $k => $v) {
            $detailId = $request->sample_details['detail_header'][$k];

            $detail = $detailId && $detailId != 'undefined' ? SampleDetails::find($detailId) : new SampleDetails();
            $detail->sample_header_id = $SampleHeader->id;

            if (!isset($detail->sample_code)) {
                // $config_start_no = SystemConfiguration::where('key', 'start_sample_no')->first();
                $lab = Lab::find($request->sample_details['lab_id'][$k]);
                $code = SampleDetails::orderBy('id', 'DESC')->first();
                $last_sample = isset($code->id) ? $code->sample_no : $lab->start_sample_no;

                // $last_sample = isset(SampleDetails::latest('id')->first()->id) ? substr(SampleDetails::latest('id')->first()->sample_code,9,strlen(SampleDetails::latest('id')->first()->sample_code) -1) : $config_start_no->value;

                // return response()->json($request->sample_details['lab_id'][$k]);
                $sample_number = intval($last_sample) + 1;
                // $sample_number = str_pad($sample_number, 4, '0', STR_PAD_LEFT);

                $detail->sample_code = 'S' . date('Y') . $lab->code . $SampleHeader->sample_type->code . sprintf('%0' . '4' . 'd', $sample_number);
                $detail->sample_no = sprintf('%0' . '4' . 'd', $sample_number);
                $detail->report_number = 'LR/' . $SampleHeader->sample_type->code . '/' . date('Y') . '/' . $lab->code . '/' . sprintf('%0' . '4' . 'd', $sample_number);
            }

            if (isset($detail->id) && $SampleHeader->status == 'Samples Reception') {
                $current_analysis = explode(',', $detail->analysis_type_id);
                $currentAnalysisSample[$detail->sample_code] = $current_analysis;
                $updated_analysis = $request->sample_details['sample_analysis'][$k];
                // return response()->json($updated_analysis);
                foreach ($current_analysis as $ca) {
                    if (!in_array($ca, $updated_analysis)) {
                        CapturedResult::where('sample_detail_id', $detail->id)->where('analysis_type_id', $ca)->delete();

                        Result::where('sample_detail_id', $detail->id)->where('analysis_type_id', $ca)->delete();
                    }
                }
            }
            if ($SampleHeader->status == 'Samples Reception') {
                $detail->analysis_type_id = implode(',', $request->sample_details['sample_analysis'][$k] ?? []);
                $detail->sample_condition_id = $request->sample_details['sample_condition'][$k];
                $detail->sample_point_id = $request->sample_details['sample_point'][$k];
                $detail->company_product_id = $request->sample_details['product'][$k];
                $detail->barcode = $request->sample_details['barcode'][$k];

                $detail->disposal_date = $request->sample_details['disposal_date'][$k];
                // $detail->lab_sub_no = $request->sample_details['submission_no'][$k];

                $detail->main_standard = $request->sample_details['main_standard'][$k];
                $detail->secondary_standard = $request->sample_details['secondary_standard'][$k];
                $detail->third_standard_id = $request->sample_details['third_standard'][$k];
                $detail->lab_id = $request->sample_details['lab_id'][$k];
            }
            $s_samples_comments = str_replace('<p>&nbsp;</p>', '', $request->sample_details['comments'][$k]);
            $detail->comments = trim($s_samples_comments);

            // Calculate has_no_result_capture
            $hasNoResultCapture = true;
            if (!empty($detail->analysis_type_id)) {
                $aTypes = explode(',', $detail->analysis_type_id);
                if (count($aTypes) > 0) {
                    foreach ($aTypes as $atId) {
                        if (trim($atId) != "") {
                            $at = AnalysisType::find($atId);
                            if (!$at || !$at->has_no_result) {
                                $hasNoResultCapture = false;
                                break;
                            }
                        }
                    }
                } else {
                    $hasNoResultCapture = false;
                }
            } else {
                $hasNoResultCapture = false;
            }
            $detail->has_no_result_capture = $hasNoResultCapture;

            $detail->save();
            if ($SampleHeader->status == 'Samples Reception') {
                if ($request->sample_details['is_duplicate'][$k] != '0') {
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

                if (trim($request->sample_details['sample_store'][$k]) != '' && trim($request->sample_details['sample_store_slot'][$k]) != '' && trim($request->sample_details['sample_quantity'][$k]) != '') {
                    $ISCC = new InventorySubCategoriesController();

                    $sampleLabItemExists = \App\InventorySubCategories::where('name', $SampleHeader->batch_code . '/' . $detail->sample_code)->first();

                    if (!isset($sampleLabItemExists->id)) {
                        $category = SystemConfiguration::where('key', 'lab_samples_inventory_category_id')->first();
                        $labSupplier = SystemConfiguration::where('key', 'lab_samples_supplier_id')->first();
                        $inventoryLabDep = SystemConfiguration::where('key', 'inventory_lab_dep_id')->first();
                        if (!isset($category->id)) {
                            return redirect()->back()->with('error', 'Kindly a inventory sub-category for lab items;');
                        }
                        if (!isset($labSupplier->id)) {
                            return redirect()->back()->with('error', 'Kindly set the default lab supplier for lab sample storage;');
                        }
                        if (!isset($inventoryLabDep->id)) {
                            return redirect()->back()->with('error', 'Kindly set Inventory Lab Department;');
                        }
                        $req = new Request();
                        $req->category_id = $category->value;
                        $req->name = $SampleHeader->batch_code . '/' . $detail->sample_code;
                        $req->description = 'Sample for batch - ' . $SampleHeader->batch_code;
                        $req->manufacturer = 'Source: Lab';
                        $req->minimum_level = 0;
                        $req->unit_type = $request->sample_details['sample_reporting_unit'][$k];
                        $req->unit_price = 0;
                        $req->reporting_decimal_places = 2;
                        $req->parent = "\App\SampleDetails";
                        $req->parent_id = $detail->id;

                        $sampleItem = $ISCC->add($req, true); //Create Item in Inventory for storage

                        // return response()->json($sampleItem);

                        $IIC = new InventoryItemController();

                        $req = new Request();
                        $req->category_id = $category->value;
                        $req->sub_category_id = $sampleItem->id;
                        $req->batchcode = $sampleItem->name;
                        $req->inventory_department_id = $inventoryLabDep->value;
                        $req->supplier_id = $labSupplier->value;
                        $req->received_by = 0;
                        $req->previous_batch_code = 'N/A';
                        $req->quantity = $request->sample_details['sample_quantity'][$k] ?? 0;
                        $req->barcode = $request->sample_details['barcode'][$k] ?? 'n/a';
                        $req->store = $request->sample_details['sample_store'][$k] ?? 0;
                        $req->slot = $request->sample_details['sample_store_slot'][$k] ?? 0;
                        $req->price = 0;

                        $inventoryItem = $IIC->add($req, true);

                        $samplesRequiringStorage['samples'][] = $detail->sample_code;
                        $samplesRequiringStorage['store_ids'][] = $request->sample_details['sample_store'][$k] ?? 0;

                        // $ISSCC = new InventoryStoreSlotContentController;

                        // $req = new Request();
                        // $req->item = $inventoryItem->batchcode;

                        // $content = $ISSCC->add($req, $request->sample_details['sample_store_slot'][$k], $request->sample_details['sample_store'][$k], true);
                        // return json_encode($content);
                    }
                }
            }
        }

        if ($SampleHeader->status == 'Samples Reception') {
            if (count($samplesRequiringStorage['samples']) > 0) {
                $companyDetails = getCompanyDetails();
                $samplesRequiringStorage['batch_route'] = route('view-batch-details', ['batch' => $SampleHeader->id]);
                $samplesRequiringStorage['batch_code'] = $SampleHeader->batch_code;

                $samplesOL = '<ol>';

                foreach ($samplesRequiringStorage['samples'] as $sampleC) {
                    $samplesOL .= '<li>' . $sampleC . '</li>';
                }

                $samplesOL .= '</ol>';

                $body = '
					Hi,<br>
					<p>
						The following samples have been added to the batch ' . $SampleHeader->batch_code . '.<br>
						' . $samplesOL . '
					</p>
					' . ($SampleHeader->status == 'Samples En-Route' ? 'The samples are expected on ' . $SampleHeader->date_expected : '') . '
					<p>
						Please view the batch for more information about the samples: <a href="' . $samplesRequiringStorage['batch_route'] . '">' . $samplesRequiringStorage['batch_route'] . '</a>
					</p>
					Regards,<br>
					' . $companyDetails['name'] . '
				';

                $subject = '[' . $companyDetails['name'] . '] Samples En-Route Storage Notification for Batch - ' . $SampleHeader->batch_code;

                $emails = \App\InventoryStoreContact::join('users as u', 'u.id', 'inventory_store_contacts.user_id')
                    ->selectRaw('u.email')->whereIn('inventory_store_contacts.store', $samplesRequiringStorage['store_ids'])->get()->pluck('email')->toArray();

                // return json_encode($emails, JSON_PRETTY_PRINT);

                $emails = array_unique($emails);
                notify_user($body, $emails, $subject);
            }

            $targetDateStr = 'Target Date';
            $targetDate = \App\SampleDate::where('sample_header_id', $SampleHeader->id)->where('name', $targetDateStr)->first() ?? new \App\SampleDate();
            $targetDate->name = $targetDateStr;
            $targetDate->sample_header_id = $SampleHeader->id;
            $targetDate->date = \Carbon\Carbon::parse($SampleHeader->receipt_date)->addDays($maxReportingTime);
            $targetDate->save();

            $batch = SampleHeader::find($request->batch);
            $strStage = 'Sample Labeling';
            $samWk = 'Samples Reception';

            $stage = SampleAnalysisStage::where('name', $strStage)->where('sample_workflow', $samWk)->first();
            $batch->days_of_analysis = $maxReportingTime;
            $batch->sample_tracking_stage = $stage->id;
            $batch->save();
            $duplicateSampleAnalysis = [];
            if (sizeof($duplicate_samples_ids) > 0) {
                foreach ($duplicate_samples as $key => $value) {
                    $choosen_analysis = explode(',', SampleDetails::find($key)->analysis_type_id);
                    $done_analysis = [];
                    $captured_results = CapturedResult::where('sample_detail_code', $value)->whereIn('analysis_type_id', $choosen_analysis)->get();
                    foreach ($captured_results as $c_value) {
                        array_push($done_analysis, $c_value->analysis_type_id);
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
                    $analysis_diff = array_diff($choosen_analysis, $done_analysis);
                    $duplicateSampleAnalysis[$key] = $analysis_diff;
                }
            }

            $hasCapturedResults = false;
            $analysis_to_be_done = [];

            $batch_analysis = $batch->samples;

            foreach ($batch_analysis as $a) {
                if (!isset($analysis_to_be_done[$a->sample_code]) && !in_array($a->id, $duplicate_samples_ids)) {
                    $analysis_to_be_done[$a->sample_code] = [
                        'sample_detail_code' => $a->sample_code,
                        'sample_detail_id' => $a->id,
                        'sample_header_id' => $batch->id,
                        'analysis_to_do' => [],
                    ];
                }
                if (!in_array($a->id, $duplicate_samples_ids)) {
                    $analysis_to_be_done[$a->sample_code]['analysis_to_do'] = array_merge($analysis_to_be_done[$a->sample_code]['analysis_to_do'], $a->analysis());
                }
                if (in_array($a->id, $duplicate_samples_ids)) {
                    if (sizeof($duplicateSampleAnalysis[$a->id]) > 0) {
                        $analysis_d = AnalysisType::whereIn('id', $duplicateSampleAnalysis[$a->id])->get();
                        if (!isset($analysis_to_be_done[$a->sample_code])) {
                            $analysis_to_be_done[$a->sample_code] = [
                                'sample_detail_code' => $a->sample_code,
                                'sample_detail_id' => $a->id,
                                'sample_header_id' => $batch->id,
                                'analysis_to_do' => [],
                            ];
                        }
                        $analysis_to_be_done[$a->sample_code]['analysis_to_do'] = array_merge($analysis_to_be_done[$a->sample_code]['analysis_to_do'], $analysis_d);
                    }
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
                foreach ($atbs['analysis_to_do'] as $a) {
                    if (isset($currentAnalysisSample[$atbs['sample_detail_code']]) && in_array($a->id, $currentAnalysisSample[$atbs['sample_detail_code']])) {
                        $analytes = [];
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
                            ->where('sample_header_id', $atbs['sample_header_id'])->first() ?? new CapturedResult();
                        $captured->sample_detail_code = $atbs['sample_detail_code'];
                        $captured->sample_detail_id = $atbs['sample_detail_id'];
                        $captured->sample_header_id = $atbs['sample_header_id'];
                        $captured->analyte_id = $an->analyte_id;
                        $captured->analysis_type_id = $a->id;
                        $captured->analyte_code = $an->analyte_code;
                        $captured->equipment_id = $an->equipment_id;
                        $captured->method_id = $analysisType->method;
                        $captured->reporting_unit_id = $analysisType->reporting_unit;
                        $captured->user_id = \Auth::user()->id;
                        $captured->operator_id = $analysisType->operator_id;
                        $captured->ltm_method_id = $analysisType->ltm_method_id;
                        $captured->analyte_accredited = $analysisType->non_accredited;
                        $captured->analyte_status_contracted = $lab->is_external ?? 0;
                        $captured->lab_section_id = $analysisType->lab_section_id;
                        $captured->parameters_order = $analysisType->level ?? 0;
                        $captured->remark_is_manual = $analysisType->remark_is_manual;
                        $captured->formular_id = $analysisType->formular_id;
                        $captured->method_sequence_id = $analysisType->method_sequence_id;

                        // Get analysis type for has_no_result_capture
                        $aType = AnalysisType::find($a->id);
                        $captured->has_no_result_capture = $aType ? $aType->has_no_result : 0;

                        $captured->save();

                        $result = Result::where('sample_detail_code', $atbs['sample_detail_code'])
                            ->where('sample_detail_id', $atbs['sample_detail_id'])
                            ->where('captured_result_id', $captured->id)
                            ->where('analyte_id', $an->analyte_id)
                            ->where('analysis_type_id', $a->id)
                            ->where('sample_header_id', $atbs['sample_header_id'])->first() ?? new Result();
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
                        $result->lab_section_id = $analysisType->lab_section_id;
                        $result->parameters_order = $analysisType->level ?? 0;
                        $result->remark_is_manual = $analysisType->remark_is_manual;
                        $result->has_no_result_capture = $captured->has_no_result_capture;

                        $result->save();
                    }
                }
            }
        }

        return redirect()->back()->within('success', 'Batch Samples updated.');
    }

    public function createDetailAnalysisRelation($batch_id, $sample_id, $analysis_type)
    {
        $data = [];
        SampleAnalysisTypeRelation::where('batch_id', $batch_id)->where('sample_detail_id', $sample_id)->whereNotIn('analysis_type_id', $analysis_type)->delete();
        $existing = SampleAnalysisTypeRelation::where('batch_id', $batch_id)->where('sample_detail_id', $sample_id)->pluck('analysis_type_id')->toArray();
        foreach ($analysis_type as $at) {
            if (!in_array($at, $existing)) {
                $data[] = [
                    'analysis_type_id' => $at,
                    'batch_id' => $batch_id,
                    'sample_detail_id' => $sample_id,
                ];
            }
        }
        sizeof($data) > 0 ? SampleAnalysisTypeRelation::insert($data) : '';

        return 'success';
    }

    public function addBatchSamplesDynamically()
    {
        $batches = SampleHeader::all();
        foreach ($batches as $batch) {
            $batch_analysis = $batch->samples;
            $hasCapturedResults = false;

            $analysis_to_be_done = [];

            foreach ($batch_analysis as $a) {
                if (!isset($analysis_to_be_done[$a->sample_code])) {
                    $analysis_to_be_done[$a->sample_code] = [
                        'sample_detail_code' => $a->sample_code,
                        'sample_detail_id' => $a->id,
                        'sample_header_id' => $batch->id,
                        'analysis_to_do' => [],
                    ];
                }

                $analysis_to_be_done[$a->sample_code]['analysis_to_do'] = array_merge($analysis_to_be_done[$a->sample_code]['analysis_to_do'], $a->analysis());
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
                foreach ($atbs['analysis_to_do'] as $a) {
                    if (isset($current_analysis) && in_array($a->id, $current_analysis)) {
                        $analytes = [];
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
                            ->where('sample_header_id', $atbs['sample_header_id'])->first() ?? new CapturedResult();
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
                            ->where('sample_header_id', $atbs['sample_header_id'])->first() ?? new Result();
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

    public function show(Request $request, $batch, $client = false, $portal = false, $status = false)
    {
        $batchID = $batch;

        $batch = SampleHeader::with('comments.creator', 'samples.sample_detail_lab', 'captured_results.my_analyte', 'captured_results.defacto_analyst_with', 'captured_results.sample', 'stagingDetails', 'sample_type')->find($batchID);
        if (isset($batch->id) && $batch->crm_unit_id < 1) {
            $crm_unit = CRMCompanyUnit::where('crm_customer_id', $batch->crm_customer_id)->where('name', $batch->crm_unit_name)->first();
            $batch->crm_unit_id = isset($crm_unit->id) ? $crm_unit->id : $batch->crm_unit_id;
            // return response()->json($batch);
            $batch->save();
        }

        $qc_schemes = QcSchemes::where('is_active', 1)->get();
        $qc_types = QcTypes::where('is_active', 1)->get();
        if (isset($batch->id) && $batch->is_qc_batch == 1) {
            $qcconfigperc = SystemConfiguration::where('key', 'qc_percentage_config')->first();
            $qc_config_perc = $qcconfigperc->value;
        } else {
            $qc_config_perc = '';
        }
        $section_approvers_users = isset($batch->id) ? LabSectionApproverRelationShip::whereIn('lab_section_id', explode(',', $batch->lab_section_ids))->get() : [];

        $receiving_role = SystemConfiguration::where('key', 'receiving_role_id')->first();
        // $test =  UserRole::where('role_id',isset($receiving_role->value) ? $receiving_role->value : 0)->get();
        // return response()->json($test);
        $recieving_users = UserRole::where('role_id', isset($receiving_role->value) ? $receiving_role->value : 0)->join('users as u', 'u.id', '=', 'user_roles.user_id')->where('u.is_support_staff', 0)->selectRaw('u.*')->get();

        $batch_scope = SystemConfiguration::where('key', 'batch_scope')->first();
        $customer_survey = SystemConfiguration::where('key', 'customer_survey')->first();
        $countries = Country::orderBy('name')->get();
        // $methods = AnalysisMethod::where('active', 1)->where('is_sampling_method',0)->where('is_ltm',0)->get();
        $is_ltm_id = SystemConfiguration::where('key', 'method_ltm_id')->first();
        $ltmethods = AnalysisMethod::where('active', 1)->where('method_type_id', $is_ltm_id->value)->get();
        // return response()->json(['methods'=>$methods,'ltm'=>$ltmethods])

        $account_settings = getConfigTypeByName('Account Settings');
        $atachment_type = SystemConfiguration::where('key', 'attachment_type')->get();
        $users = User::where('is_client', 0)->where('supplier_id', 0)->where('active', 1)->get();
        $labsections = SampleAnalysisStage::where('active', 1)->where('is_system', 0)->get();
        $reportingUnits = getReportingUnits();
        $labStores = getStorageByType('lab_store');
        // return response()->json($reportingUnits);
        $conditions = SampleCondition::where('active', 1)->get();
        $products = CompanyProduct::all();
        $workflowstages = [];
        $workflows = getSampleWorflowStages();
        $sample_types = getSampleTypes();
        $is_sampling = SystemConfiguration::where('key', 'sampling_method_type_id')->first();
        $samplingmethods = AnalysisMethod::where('active', 1)->where('method_type_id', $is_sampling->value)->get();
        $processed_results = [];
        $raw_results = [];
        $interlabs = [];
        $disposal_date = '';
        if (isset($account_settings->id)) {
            $accounts = getconfigByID($account_settings->id);
        } else {
            $accounts = [];
        }
        $not_captured = [];
        $payment_detail = [];
        $contacts = [];
        $batch_sample_codes = '';
        $report_formats = [];
        $approvers = [];
        $approvers_user_ids = [];
        $headerDetails = isset($batch->id) ? $batch->report_header_details() : [];
        $ammendments = isset($batch->id) ? getBatchAmmendmentsById($batch->id) : [];
        $allsamples = isset($batch->id) ? $batch->all_samples() : [];

        if (isset($batch->id)) {
            if (in_array($batch->status, ['Samples In Lab', 'Sample Verification', 'Sample Approval', 'Reports In Payment', 'Reports for Collection'])) {
                $raw_results = CapturedResult::with(['sample', 'analysis_type', 'operator'])->where('sample_header_id', $batch->id)->orderBy('sample_detail_id', 'ASC')->get();
                $processed_results = Result::with(['captured', 'captured.sample', 'captured.analysis_type', 'captured.operator'])->where('sample_header_id', $batch->id)->orderBy('sample_detail_id', 'ASC')->get();

                // return response()->json(['raw'=>$raw_results,'processed' => $processed_results]);
            }
            $workflowstages = getWorkflowStage_Stages($batch->status);
            if (in_array($batch->status, ['Sample Verification', 'Sample Approval'])) {
                // Fetch report formats configured for this batch's lab sections (report_format_sample_analysis_stage)
                $labSectionIds = array_filter(explode(',', $batch->lab_section_ids ?? ''));

                if (!empty($labSectionIds)) {
                    $configuredFormatIds = \App\Models\LabSectionReportConfig::whereIn('sample_analysis_stage_id', $labSectionIds)
                        ->select('report_format_id', 'is_default', 'sample_analysis_stage_id')
                        ->get();

                    if ($configuredFormatIds->isNotEmpty()) {
                        $formatIds = $configuredFormatIds->pluck('report_format_id')->unique()->toArray();
                        $report_formats = \App\ReportFormat::whereIn('id', $formatIds)->where('is_active', true)->get();

                        foreach ($report_formats as $format) {
                            $format->is_default = $configuredFormatIds->where('report_format_id', $format->id)->where('is_default', true)->isNotEmpty();
                        }
                    } else {
                        $report_formats = \App\ReportFormat::active()->get();
                    }
                } else {
                    $report_formats = \App\ReportFormat::active()->get();
                }
            }
            $disposal_date = \Carbon\Carbon::parse($batch->receipt_date)->addDays(14)->format('Y-m-d');
            // return response()->json($disposal_date);
            $contacts = getCrmCustomerContactSchedule($batch->crm_customer_id);
            // return response()->json($contacts);
            $batch_sample_codes = getBacthSampleCodes($batch->id);
            $payment_detail = InvoicePaymentDetail::where('batch_id', $batch->id)->get();
            $interlabs = InterLabLogView::where('sample_header_id', $batch->id)->orderBy('status', 'ASC')->orderBy('id', 'DESC')->get();
            // $equipment_data = $batch->get_captured();
            $approvers = BatchLabSectionApprover::where('batch_id', $batch->id)->get();
            $approvers_user_ids = BatchLabSectionApprover::where('batch_id', $batch->id)->pluck('user_id')->toArray();
            // return response()->json(["ids"=>$approvers_user_ids,'users'=>$users]);
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
            $not_captured = CapturedResult::where('sample_header_id', $batch->id)->whereNull('result')->join('analysis_elements as ae', function ($join) {
                $join->on('ae.analysis_type_id', '=', 'captured_results.analysis_type_id');
                $join->on('ae.analyte_id', '=', 'captured_results.analyte_id');
            })->where('ae.active', 1)->selectRaw('group_concat(analyte_code) as codes,sample_detail_code')->groupBy('sample_detail_id')->get();
            // return response()->json($test);
        }

        $selectedSampleType = \App\SampleType::find($batch->sample_type_id ?? 0) ?? false;

        $selected_analysis_types = isset($batch->sample_type_id) ? $selectedSampleType->analysis_types : [];
        if (isset($batch->id)) {
            if ($batch->is_qc_batch) {
                $standards = Standards::where('status', 1)->where('qc_type_id', $batch->qc_type_id)->get();
            } else {
                $standards = Standards::where('status', 1)->get();
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
            $ammendable = [];
            $attachments = [];
            // return response()->json($ammendable,200);
        }

        $role_a = SystemConfiguration::where('key', 'analyst_role_id')->first();
        $labs = Lab::where('active', 1)->get();
        // $analysts = getUsersByRole('Analyst');
        $analysts = User::orderBy('name')->join('user_roles as ur', 'ur.user_id', '=', 'users.id')
            ->join('roles as r', 'r.id', '=', 'ur.role_id')
            ->where('r.id', $role_a->value)->where('users.active', 1)->where('users.is_support_staff', 0)->selectRaw('users.*')->get();

        // -----------------------------------

        $analaytesHolder = [];
        $analaytesHolderPesticide = [];
        $analysisBySample = [];
        $analysisBySampleNames = [];
        $labSamples = [];

        // echo date('Y-m-d H:i:s');
        $l = 1;

        // return response()->json($batch);


        $methods = AnalysisMethod::whereNotIn('method_type_id', [$is_sampling->value])->pluck('name', 'id')->toArray();

        // return response()->json($methods);

        foreach ($batch->captured_results ?? [] as $item) {
            // return response()->json($item);

            if (!isset($analaytesHolderPesticide[$item->sample_detail_code]) && $item->pesticide == 1) {
                $analaytesHolderPesticide[$item->sample_detail_code] = [];
            } else {
                if (!isset($analaytesHolder[$item->sample_detail_code])) {
                    $analaytesHolder[$item->sample_detail_code] = [];
                }
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
                'Max' => 'Max',
                'Min' => 'Min',
                'greater_than' => '>',
                'less_than' => '<',
            ];
            $item->methods = $this->methodNameFromId($methods, $analyte->method);
            $lab_section = SampleAnalysisStage::find($item->lab_section_id);
            // !isset($analaytesHolder[$item->sample_detail_code]) ? $analaytesHolder[$item->sample_detail_code] = [] : '';
            // isset($lab_section->id) && !isset($analaytesHolder[$item->sample_detail_code][$item->lab_section_id]) ? $analaytesHolder[$item->sample_detail_code][$item->lab_section_id] = [] : '';
            if ($item->pesticide == 0) {
                $item->lab_section_id > 0 ? $analaytesHolder[$item->sample_detail_code][$item->lab_section_id]['section'] = $lab_section->name : $analaytesHolder[$item->sample_detail_code]['000']['section'] = 'Not Set';
                $item->lab_section_id > 0 ? $analaytesHolder[$item->sample_detail_code][$item->lab_section_id]['cr'][] = $item : $analaytesHolder[$item->sample_detail_code]['000']['cr'][] = $item;
            } else {
                $item->lab_section_id > 0 ? $analaytesHolderPesticide[$item->sample_detail_code][$item->lab_section_id]['section'] = $lab_section->name : $analaytesHolderPesticide[$item->sample_detail_code]['000']['section'] = 'Not Set';
                $item->lab_section_id > 0 ? $analaytesHolderPesticide[$item->sample_detail_code][$item->lab_section_id]['cr'][] = $item : $analaytesHolderPesticide[$item->sample_detail_code]['000']['cr'][] = $item;
            }

            $sample_details_test = $item->sample;
            $item->standard_limit_value = '';
            if (isset($sample_details_test->id)) {
                $standard = $sample_details_test->main_standard;
                $sec = $sample_details_test->secondary_standard;
                $third = $sample_details_test->third_standard_id;
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
                if ($sec != '') {
                    $s_analyte_standard = StandardAnalytes::where('standard_id', $sec)->where('analyte_id', $item->analyte_id)->first();
                    if (isset($s_analyte_standard->id)) {
                        if ($s_analyte_standard->standard_value_type == 'is_range') {
                            $item->sec_Standard_value = $s_analyte_standard->low . ' - ' . $s_analyte_standard->high;
                        } elseif ($s_analyte_standard->standard_value_type == 'is_standard_value') {
                            $value_id = StandardValue::find($s_analyte_standard->standard_value_id);
                            if (isset($value_id->id)) {
                                if ($value_id->code == 'IsValue') {
                                    $item->sec_standard_value = $s_analyte_standard->standard_is_value;
                                    $item->sec_standard_limit_value = $s_analyte_standard->value_type;
                                } else {
                                    $item->sec_standard_value = $value_id->code;
                                }
                            }
                        }
                    } else {
                        $item->sec_standard_value = 'NS';
                    }

                    $item->secondary_standard = Standards::find($sec)->code ?? '';
                }
                if ($third != '') {
                    $t_analyte_standard = StandardAnalytes::where('standard_id', $third)->where('analyte_id', $item->analyte_id)->first();
                    if (isset($t_analyte_standard->id)) {
                        if ($t_analyte_standard->standard_value_type == 'is_range') {
                            $item->third_Standard_value = $t_analyte_standard->low . ' - ' . $t_analyte_standard->high;
                        } elseif ($t_analyte_standard->standard_value_type == 'is_standard_value') {
                            $value_id = StandardValue::find($t_analyte_standard->standard_value_id);
                            if (isset($value_id->id)) {
                                if ($value_id->code == 'IsValue') {
                                    $item->third_standard_value = $t_analyte_standard->standard_is_value;
                                    $item->third_standard_limit_value = $t_analyte_standard->value_type;
                                } else {
                                    $item->third_standard_value = $value_id->code;
                                }
                            }
                        }
                    } else {
                        $item->third_standard_value = 'NS';
                    }

                    $item->third_standard = Standards::find($third)->code ?? '';
                }
            }
            // return response()->json($batch);
        }

        // return response()->json($analaytesHolder);

        foreach ($batch->samples ?? [] as $sample) {
            $labSamples[$sample->sample_code] = getSampleDetailsLab($sample->id);
            if (!isset($analysisBySample[$sample->sample_code])) {
                $analysisBySample[$sample->sample_code] = [];
            }
            $analysisBySample[$sample->sample_code] = array_merge(explode(',', $sample->analysis_type_id), $analysisBySample[$sample->sample_code]);

            foreach ($analysisBySample[$sample->sample_code] as $id) {
                $analysis = getAnalysisTypeID($id);
                if (isset($analysis->id)) {
                    if (!isset($analysisBySampleNames[$sample->sample_code])) {
                        $analysisBySampleNames[$sample->sample_code] = [];
                    }
                    $analysisBySampleNames[$sample->sample_code][$analysis->name] = $analysis->id;
                }
            }
        }
        // echo "Ending - ".date('Y-m-d H:i:s');
        $active_company = getActiveCompany();
        // return response()->json($analaytesHolder);
        // ---------------------------------------
        $userLabSections = auth()->user()->labsectionids;
        $customer = isset($batch->id) ? getCrmCustomerByID($batch->crm_customer_id) : [];
        $requestTypes = getRequestTypes();
        $notifiable_users = getNotifiableUsers();
        $notesReminderType = getNotesReminderTypes();
        $clientPageSize = 50;
        $clients = CRMCustomer::query()
            ->select(['id', 'name'])
            ->where('active', 1)
            ->orderBy('name')
            ->limit($clientPageSize)
            ->get();

        $selectedClientId = $batch->crm_customer_id ?? ($defaultClient !== false ? $defaultClient : null);

        if ($selectedClientId) {
            $selectedClient = CRMCustomer::query()
                ->select(['id', 'name'])
                ->where('id', $selectedClientId)
                ->where('active', 1)
                ->first();

            if ($selectedClient !== null && !$clients->contains('id', $selectedClientId)) {
                if ($clients->count() >= $clientPageSize) {
                    $clients->pop();
                }

                $clients->push($selectedClient);
            }
        }

        $clients = $clients->sortBy('name')->values();
        // return response()->json($analaytesHolder);
        return view('batches.show', compact('batch', 'labStores', 'batchID', 'defaultClient', 'selectedSampleType', 'client_portal', 'ammendable', 'standards', 'attachments', 'not_captured', 'analysts', 'countries', 'accounts', 'methods', 'atachment_type', 'batch_scope', 'customer_survey', 'interlabs', 'labs', 'users', 'payment_detail', 'labsections', 'contacts', 'batch_sample_codes', 'report_formats', 'approvers', 'reportingUnits', 'conditions', 'products', 'headerDetails', 'analaytesHolder', 'analysisBySample', 'analysisBySampleNames', 'labSamples', 'workflowstages', 'workflows', 'sample_types', 'samplingmethods', 'active_company', 'ammendments', 'allsamples', 'selected_analysis_types', 'userLabSections', 'customer', 'requestTypes', 'notifiable_users', 'notesReminderType', 'clients', 'disposal_date', 'status', 'recieving_users', 'section_approvers_users', 'analaytesHolderPesticide', 'approvers_user_ids', 'ltmethods', 'processed_results', 'raw_results', 'qc_schemes', 'qc_types', 'qc_config_perc', 'clientPageSize'));
    }

    public function fetch_unit_stuff($name, $client)
    {
        $unit = CRMCompanyUnit::where('id', $name)->where('crm_customer_id', $client)->first();

        return response()->json([
            'products' => $unit->products,
            'sample_points' => $unit->sample_points,
        ], 200);
    }

    public function updateChainofCustody($data)
    {
        \App\ChainOfCustody::where('sample_header_id', $data['batch_id'])
            ->whereNull('moved_out_date')->update([
                'moved_out_date' => \Carbon\Carbon::now(),
                'moved_out_by' => \Auth::user()->id,
                'comments' => $data['comments'],
            ]);

        $custody = new \App\ChainOfCustody();
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
        if ($request->status == 'Samples Request Review' && $request->has('tracking_stage')) {
            $batch = Sampleheader::find($request->bacth_id);
            // return response()->json($batch,200);
            $previousStatus = $batch->status;
            $custodyDetails = [
                'batch_id' => $request->bacth_id,
                'comments' => $request->comments,
                'current' => [
                    'status' => $batch->status,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
                'target' => [
                    'status' => $request->status,
                    'tracking_stage' => $request->tracking_stage,
                ],
            ];

            $requestTypes = $request->request_type_id;
            $requestTypes = array_diff($requestTypes, ['Other']);

            if ($request->has('other_type') && $request->other_reason) {
                $reason = new \App\RequestType();
                $reason->name = $request->other_type;
                $reason->visible = 0;
                $reason->save();

                $requestTypes = array_merge($requestTypes, [$reason->id]);
            }

            $batch->reason_for_submission = implode(',', $requestTypes);

            $batch->sample_tracking_stage = $request->tracking_stage;
            $batch->priority = $request->is_priority ?? 'Normal';
            $batch->save();

            $this->updateChainofCustody($custodyDetails);

            return redirect()->route('sample-workflow', ['status' => $previousStatus])->with('success', 'Approval was successful');
        }

        if ($request->status == 'Samples In Lab') {
            if (isset($request->batch_code)) {
                $batch_codes = $request->batch_code;
                foreach ($batch_codes as $code) {
                    $batch = Sampleheader::where('batch_code', $code)->get();
                    $batch[0]->in_lab_date = date('Y-m-d');

                    $previousStatus = $batch[0]->status;
                    $custodyDetails = [
                        'batch_id' => $batch[0]->id,
                        'comments' => $request->comments,
                        'current' => [
                            'status' => $batch[0]->status,
                            'tracking_stage' => $batch[0]->sample_tracking_stage,
                        ],
                        'target' => [
                            'status' => $request->status,
                            'tracking_stage' => $request->tracking_stage,
                        ],
                    ];

                    $requestTypes = $request->request_type_id;
                    $requestTypes = array_diff($requestTypes, ['Other']);

                    if ($request->has('other_type') && $request->other_reason) {
                        $reason = new \App\RequestType();
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

                    $batch[0]->reason_for_submission = implode(',', $requestTypes);
                    $batch[0]->specialist_analyst_id = $request->specialist_analyst_id;
                    $batch[0]->sample_tracking_stage = $request->tracking_stage;
                    $batch[0]->priority = $request->is_priority ?? 'Normal';
                    $batch[0]->save();

                    $this->updateChainofCustody($custodyDetails);
                }

                return redirect()->route('sample-workflow', ['status' => $previousStatus])->with('success', 'Batches Successfully Moved to ' . $request->status);
            }
        } elseif ($request->status = 'Samples Request Review') {
            $strStage = 'Sample Labeling';
            $samWk = 'Samples Reception';
            $stage = SampleAnalysisStage::where('name', $strStage)->where('sample_workflow', $samWk)->first();
            $batch_codes = $request->batch_code;
            $previousStatus = 'Samples Reception';

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

                $custodyDetails = [
                    'batch_id' => $batch->id,
                    'comments' => $request->comments ?? '',
                    'current' => [
                        'status' => $batch->status,
                        'tracking_stage' => $batch->sample_tracking_stage,
                    ],
                    'target' => [
                        'status' => $request->status,
                        'tracking_stage' => '20007',
                    ],
                ];
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

        $custodyDetails = [
            'batch_id' => $batch_id,
            'comments' => $request->comments ?? '',
            'current' => [
                'status' => $batch->status,
                'tracking_stage' => $batch->sample_tracking_stage,
            ],
            'target' => [
                'status' => $batch->status,
                'tracking_stage' => $stage,
            ],
        ];

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

        // Ensure we always have a valid tracking_stage_id when updating chain of custody
        $targetTrackingStage = $batch->sample_tracking_stage;

        if (!$targetTrackingStage) {
            // Try to derive from batch stages for the target workflow (legacy behavior)
            if (method_exists($batch, 'stages')) {
                $stages = $batch->stages($status);
                if (isset($stages[0]->id)) {
                    $targetTrackingStage = $stages[0]->id;
                }
            }

            // Fallback: use the first defined SampleAnalysisStage for the target workflow
            if (!$targetTrackingStage) {
                $stage = SampleAnalysisStage::where('sample_workflow', $status)
                    ->orderBy('level', 'asc')
                    ->first();

                if (isset($stage->id)) {
                    $targetTrackingStage = $stage->id;
                }
            }

            // If we still don't have a tracking stage, prevent a broken custody record
            if (!$targetTrackingStage) {
                return redirect()->back()->with(
                    'error',
                    'Kindly add Sample Analysis Stage to the sample type'
                );
            }
        }

        $custodyDetails = [
            'batch_id' => $batch_id,
            'comments' => $request->comments ?? '',
            'current' => [
                'status' => $batch->status,
                'tracking_stage' => $batch->sample_tracking_stage,
            ],
            'target' => [
                'status' => $status,
                'tracking_stage' => $targetTrackingStage,
            ],
        ];

        $this->updateChainofCustody($custodyDetails);

        // Keep batch tracking stage in sync with the target workflow stage
        $batch->sample_tracking_stage = $targetTrackingStage;

        if ($batch->status == 'Samples In Lab' && $status == 'Sample Verification') {
            // Persist method deviation details at batch level when moving to verification
            $batch->has_method_deviation = $request->boolean('has_method_deviation');
            $batch->method_deviation_reason = $batch->has_method_deviation
                ? ($request->input('method_deviation_reason') ?? '')
                : null;

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
            // $fail_arr = explode('_', $fail->value);

            // $pass_arr = explode('_', $pass->value);

            // $sample_type = SampleType::find($batch->sample_type_id);
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
                //$main_s = Standards::find($sample->main_standard);
                //if (sizeof($analytes) > 0) {
                //$message = $fail_arr[0] . ' ' . $sample_type->name . ' (' . $main_s->name . ')' . $fail_arr[4] . ' ' . implode(', ', $analytes) . ' ' . $fail_arr[5];
                //$sample->header_body = $message;
                //} else {
                //	$message = $pass_arr[0] . ' ' . $sample_type->name . ' (' . $main_s->name . ')';
                //	$sample->header_body = $message;
                //}
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
                $batch = Sampleheader::find((int) $request->batch_id);

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
            $comment->personnel_to_cc = $request->has('followers') ? implode(',', $request->followers) : 0;
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
                    $user = getUserById((int) $contact);
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
        $analytes = [];
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
                ->where('sample_header_id', $sampleDetail->sample_header_id)->first() ?? new CapturedResult();
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
                ->where('sample_header_id', $sampleDetail->sample_header_id)->first() ?? new Result();
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
                    $body = getPaymentReminderBody($customer->name);
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

            $batchid = $captured->sample_header_id;
            $batch = SampleHeader::find($batchid);

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
            $captured->result_reporting_symbol = $request->result_reporting_symbol[$cID] ?? '';
            // 10013 - id for blank reporting unit
            $captured->reporting_unit_id = $request->reporting_unit[$cID] ?? 10013;
            $captured->measure_uncertanity = $request->measure_uncertanity[$cID] ?? 0;
            $captured->method_id = $request->method_id[$cID] ?? '';
            $captured->result_reporting_symbol = $request->result_reporting_symbol[$cID] ?? '';
            $captured->operator_id = $request->operators[$cID] ?? 0;
            $captured->analyte_code = Analyte::find($captured->analyte_id)->code;

            if ($batch->status == 'Samples In Lab') {
                $captured->ltm_method_id = $request->ltm_method_id[$cID] ?? '';
                $scientific_arr = is_numeric($request->result[$cID]) ? $this->toScientificNotation($request->result[$cID]) : [];
                $captured->scienctific_result = !is_numeric($request->result[$cID]) ? $request->result[$cID] : $scientific_arr['scientific'];
                $captured->result = $request->result[$cID] ?? '';
                $captured->remark = $captured->remark_is_manual == 0 ? $request->remark[$cID] : $request->remarkmanual[$cID];
                if (is_numeric($request->result[$cID])) {
                    if ($scientific_arr['to_power'] <= 0) {
                        $captured->supercsript_base = number_format($scientific_arr['value'], 1);
                        $captured->superscript_number = $scientific_arr['to_power'];
                        $captured->superscript_negative = round(intval($request->result[$cID])) >= 1 ? 0 : 1;
                    }
                }
                $main_std_code = $request->input('main_standard.' . $cID);
                $standard_main = $main_std_code ? Standards::where('code', $main_std_code)->first() : null;

                $sec_std_code = $request->input('secondary_standard.' . $cID);
                $sec_standard = $sec_std_code ? Standards::where('code', $sec_std_code)->first() : null;

                $third_std_code = $request->input('third_standard.' . $cID);
                $third_standard = $third_std_code ? Standards::where('code', $third_std_code)->first() : null;
                if (isset($standard_main->id)) {
                    $main_standard_analyte = StandardAnalytes::where('analyte_id', $captured->analyte_id)->where('standard_id', $standard_main->id)->first();
                    $sec_standard_analyte = isset($sec_standard->id) ? StandardAnalytes::where('analyte_id', $captured->analyte_id)->where('standard_id', $sec_standard->id)->first() : '';
                    $third_standard_analyte = isset($third_standard->id) ? StandardAnalytes::where('analyte_id', $captured->analyte_id)->where('standard_id', $third_standard->id)->first() : '';
                } else {
                    return redirect()->back()->with('error', 'Kindly set Main and Secondary standard for the following sample!');
                }
                // return response()->json($captured->analyte_id);
                $captured->main_standard_id = isset($main_standard_analyte->id) ? $main_standard_analyte->id : 0;
                $captured->secondary_standard_id = isset($sec_standard_analyte->id) ? $sec_standard_analyte->id : 0;
                $captured->third_standard_id = isset($third_standard_analyte->id) ? $third_standard_analyte->id : 0;
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
                if (isset($third_standard_analyte->id)) {
                    if ($third_standard_analyte->standard_value_type == 'is_range') {
                        $captured->third_value = $third_standard_analyte->low . ' - ' . $third_standard_analyte->high;
                    } else {
                        if ($third_standard_analyte->standard_is_value == '') {
                            $standard_value = StandardValue::find($third_standard_analyte->standard_value_id);
                            $captured->third_value = $standard_value->code;
                        } else {
                            $captured->third_value = $third_standard_analyte->standard_is_value;
                        }
                    }
                } else {
                    $captured->third_value = '-';
                }
            }
            // return response()->json($captured,200);
            $captured->save();
            // return response()->json($captured);

            $sample_detail = getSampleDetailById($captured->sample_detail_id);
            $sample_detail->ammendment_number = $batch->is_amendment;
            $sample_detail->save();

            // return response()->json($sample_detail,200);
        }
        $batch = SampleHeader::find($batchid);
        // return response()->json($batch->id,200);
        $strStage = 'Capture Results';
        $samWk = 'Samples In Lab';
        $stage = SampleAnalysisStage::where('name', $strStage)->where('sample_workflow', $samWk)->first();
        if (!$stage) {
            $stage = SampleAnalysisStage::find(20015);
        }


        $custodyDetails = [
            'batch_id' => $batch->id,
            'comments' => $request->comments ?? '',
            'current' => [
                'status' => $batch->status,
                'tracking_stage' => $batch->sample_tracking_stage,
            ],
            'target' => [
                'status' => $samWk,
                'tracking_stage' => $stage ? $stage->id : $batch->sample_tracking_stage,
            ],
        ];
        $this->updateChainofCustody($custodyDetails);

        return redirect()->back()->with('success', 'Result details saved.');
    }

    public function fetch_results_remark(Request $request)
    {
        $first_res = '';
        $second_res = '';
        $third_res = '';

        $data = explode(',', $request->sample_code);
        $sample_detail = SampleDetails::where('sample_code', $data[0])->first();
        $reporting_symbol = $request->reporting_symbol;
        $analyte = Analyte::find($data[3]);
        $standard = Standards::find($sample_detail->main_standard);
        $sec_standard = Standards::find($sample_detail->secondary_standard);
        $third_standard = Standards::find($sample_detail->third_standard_id);
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

        $first_res = isset($standard->id) ? $this->getResultRenark($standard, $analyte, $request->result, $reporting_symbol) : $first_res;
        $second_res = isset($sec_standard->id) ? $this->getResultRenark($sec_standard, $analyte, $request->result, $reporting_symbol) : $second_res;
        $third_res = isset($third_standard->id) ? $this->getResultRenark($third_standard, $analyte, $request->result, $reporting_symbol) : $third_res;
        $remarkArr = [];
        if ($first_res != '') {
            array_push($remarkArr, $first_res);
        }
        if ($second_res != '') {
            array_push($remarkArr, $second_res);
        }
        if ($third_res != '') {
            array_push($remarkArr, $third_res);
        }

        return response()->json($remarkArr, 200);

        if (in_array('FAIL', array_unique($remarkArr))) {
            return response()->json('FAIL', 200);
        } elseif (in_array('-', array_unique($remarkArr)) && in_array('PASS', array_unique($remarkArr))) {
            return response()->json('PASS', 200);
        } elseif (in_array('PASS', array_unique($remarkArr))) {
            return response()->json('PASS', 200);
        } else {
            return response()->json('-', 200);
        }
    }
    private function getResultRenark($standard, $analyte, $result, $reporting_symbol)
    {
        if (isset($standard->id) && isset($analyte->id)) {
            $analyte_guide = StandardAnalytes::where('analyte_id', $analyte->id)->where('standard_id', $standard->id)->first();
            if (isset($analyte_guide->standard_value_type)) {
                if ($analyte_guide->standard_value_type == 'is_range') {
                    if ($analyte_guide->low <= $result && $result <= $analyte_guide->high) {
                        return 'PASS';
                    } else {
                        return 'FAIL';
                    }
                } else {
                    $standard_value = StandardValue::find($analyte_guide->standard_value_id);
                    if (is_numeric($result)) {
                        $type = gettype($analyte_guide->standard_is_value);
                        if ($type == 'integer' || $type == 'double') {
                            if (trim($reporting_symbol) == '>') {
                                if ($analyte_guide->value_type == 'Max' || $analyte_guide->value_type == '' || $analyte_guide->value_type == null) {
                                    $response = $result < floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
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
                            } elseif ($reporting_symbol == '<') {
                                if ($analyte_guide->value_type == 'Max' || $analyte_guide->value_type == '' || $analyte_guide->value_type == null) {
                                    $response = $result <= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                }
                                if ($analyte_guide->value_type == 'Min' || $analyte_guide->value_type == '') {
                                    $response = $result > floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                }
                                if ($analyte_guide->value_type == 'less_than') {
                                    $response = $result < floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                }
                                if ($analyte_guide->value_type == 'greater_than') {
                                    $response = $result > floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
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

                            return $response;
                        } else {
                            if ($analyte_guide->standard_is_value != '') {
                                if (trim($reporting_symbol) == '>') {
                                    if ($analyte_guide->value_type == 'Max' || $analyte_guide->value_type == '' || $analyte_guide->value_type == null) {
                                        $response = $result < floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                    if ($analyte_guide->value_type == 'Min' || $analyte_guide->value_type == '') {
                                        $response = $result >= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                    if ($analyte_guide->value_type == 'less_than') {
                                        $response = $result <= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                    if ($analyte_guide->value_type == 'greater_than') {
                                        $response = $result >= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                } elseif ($reporting_symbol == '<') {

                                    if ($analyte_guide->value_type == 'Max' || $analyte_guide->value_type == '' || $analyte_guide->value_type == null) {
                                        $response = $result <= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                    if ($analyte_guide->value_type == 'Min' || $analyte_guide->value_type == '') {
                                        $response = $result > floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                    if ($analyte_guide->value_type == 'less_than') {
                                        $response = $result <= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
                                    }
                                    if ($analyte_guide->value_type == 'greater_than') {
                                        $response = $result >= floatval($analyte_guide->standard_is_value) ? 'PASS' : 'FAIL';
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

                                return $response;
                            } else {
                                if (strtoupper(trim($standard_value->code)) == 'NS') {
                                    $response = '-';

                                    return $response;
                                } elseif (strtoupper(trim($standard_value->code)) == 'NIL') {
                                    $response = $result <= 0 ? 'PASS' : 'FAIL';

                                    return $response;
                                } elseif (strtoupper(trim($standard_value->code)) == 'ND') {
                                    $response = $result <= 0 ? 'PASS' : 'FAIL';

                                    return $response;
                                } elseif (strtoupper(trim($standard_value->code)) == 'ABSENT') {
                                    $response = in_array(strtoupper($result), ['ABSENT', 'ND']) ? 'PASS' : 'FAIL';

                                    return $response;
                                } else {
                                    $response = '-';

                                    return $response;
                                }
                            }
                            // $eresult->remarks = trim($analyte_guide->standard_is_value) == "" ? "PASS" : "++";
                        }
                    } else {
                        // return response()->json($analyte_guide,200);
                        $standard_value = StandardValue::find($analyte_guide->standard_value_id);
                        if (strtoupper($result) == 'TN') {
                            $response = 'FAIL';

                            return $response;
                        } elseif (strtoupper($result) == 'ND' && strtoupper(trim($standard_value->code)) == 'NS') {
                            $response = '-';

                            return $response;
                        } elseif (strtoupper($result) == 'ND' && strtoupper(trim($standard_value->code)) == 'NIL') {
                            $response = 'PASS';

                            return $response;
                        } elseif (strtoupper($result) == 'ND' && strtoupper(trim($standard_value->code)) == 'ND') {
                            $response = 'PASS';

                            return $response;
                        } elseif (strtoupper($result) == 'NIL' && strtoupper(trim($standard_value->code)) == 'ND') {
                            $response = 'PASS';

                            return $response;
                        } elseif (strtoupper($result) == 'ABSENT' && strtoupper(trim($standard_value->code)) == 'ABSENT') {
                            $response = 'PASS';

                            return $response;
                        } elseif (strtoupper($result) == 'PRESENT' && strtoupper(trim($standard_value->code)) == 'ABSENT') {
                            $response = 'FAIL';
                            return $response;
                        } else {
                            $response = '-';

                            return $response;
                        }
                    }
                }
            } else {
                return '-';
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
            return '-';
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

        $arr = [];

        foreach ($captured as $c) {
            $result = floatval($c->result);

            $eresult = Result::where('captured_result_id', $c->id)->first(); //result to process to
            $eresult->reporting_symbol = '';
            $eresult->unit_code = $c->reporting_unit;
            if ($c->lod && $result < floatval($c->lod)) {
                $result = $c->lod;
                $eresult->reporting_symbol = '<';
            }

            if ($c->hod && $result > floatval($c->hod)) {
                $result = $c->hod;
                $eresult->reporting_symbol = '>';
            }

            if (intval($c->significant_figures) && intval($c->significant_figures > 0)) {
                $result = sigFig($result, intval($c->significant_figures));
            } else {
                if (trim($c->decimal_places) != '') {
                    $result = round($result, intval($c->decimal_places));
                }
            }

            $eresult->result = $result;
            $eresult->save();

            $arr[] = $eresult;
        }
        $header = SampleHeader::find($batch_id);
        $header->set_date('Processing Date', \Carbon\Carbon::now(), true);

        // return response()->json($arr, 200);

        if ($internal) {
            return $header;
        }

        return redirect()->back()->with('success', 'Results Processed saved.');
    }

    public function process_results(Request $request, $batch_id, $internal = false)
    {
        $report_format = $request->report_format;
        \Log::info('process_results called', [
            'batch_id' => $batch_id,
            'report_format' => $report_format,
            'include_pesticide' => isset($request->add_pesticide) ? 1 : 0,
            'merge_with_attachments' => $request->boolean('merge_with_attachments'),
            'attachment_ids' => $request->input('attachment_ids', ''),
        ]);
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

        $arr = [];

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
        $header->set_date('Processing Date', \Carbon\Carbon::now(), true);

        // When processing results at Sample Approval stage, generate Procedure Worksheet PDFs
        // and attach them to the batch as "Procedure Worksheet" attachments.
        if (! $internal && $header && $header->status === 'Sample Approval') {
            // Keep the procedure-worksheet "Checked By" aligned with the COA's final approver.
            // COA templates use: show_report=1 and status=1 (no strict batch_status filter),
            // then pick a final approver by title keywords.
            $batchApprovers = BatchLabSectionApprover::where('batch_id', $header->id)
                ->where('show_report', 1)
                ->where('status', 1)
                ->get();

            $checker = $batchApprovers->first(function (BatchLabSectionApprover $a) {
                $title = (string) ($a->title ?? '');

                return stripos($title, 'author') !== false
                    || stripos($title, 'approv') !== false
                    || stripos($title, 'signatory') !== false;
            }) ?? $batchApprovers->last();

            // Fallback for legacy data where show_report/status might not be populated consistently.
            if (! $checker) {
                $checker = BatchLabSectionApprover::where('batch_id', $header->id)
                    ->where('batch_status', 'Sample Approval')
                    ->where('status', 1)
                    ->orderByDesc('approval_date')
                    ->first();
            }

            // One worksheet PDF per (worksheet, analyte) combination in this batch.
            $combos = CapturedResult::where('sample_header_id', $header->id)
                ->whereNotNull('procedure_worksheet_id')
                ->whereNotNull('analyte_id')
                ->get(['procedure_worksheet_id', 'analyte_id'])
                ->unique(function ($row) {
                    return $row->procedure_worksheet_id . '-' . $row->analyte_id;
                });

            if ($combos->isNotEmpty()) {
                $pdfService = app(ProcedureWorksheetPdfService::class);

                foreach ($combos as $combo) {
                    $worksheet = ProcedureWorksheet::find($combo->procedure_worksheet_id);
                    if (! $worksheet) {
                        continue;
                    }

                    $analyteId = (int) $combo->analyte_id;

                    // Optionally scope to samples that have this analyte + worksheet in this batch.
                    $sampleIds = CapturedResult::where('sample_header_id', $header->id)
                        ->where('procedure_worksheet_id', $combo->procedure_worksheet_id)
                        ->where('analyte_id', $analyteId)
                        ->pluck('sample_detail_id')
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();

                    $pdfService->generateAndAttach(
                        $header,
                        $worksheet,
                        $checker,
                        [$analyteId],
                        $sampleIds
                    );
                }
            }
        }

        if ($internal) {
            return $header;
        }

        $include_pesticide = isset($request->add_pesticide) ? 1 : 0;

        // Optional attachment merge parameters
        $merge_with_attachments = $request->boolean('merge_with_attachments');
        $attachment_ids = $request->input('attachment_ids', '');

        return redirect()->route('process-pdf-report', [
            'batch_id' => $batch_id,
            'report_format' => $report_format,
            'include_pesticide' => $include_pesticide,
            'merge_with_attachments' => $merge_with_attachments ? 1 : 0,
            'attachment_ids' => $attachment_ids,
        ]);
    }

    public function remove_analyte_from_captured_result(Request $request)
    {
        $this->refactorReportingTime($request->id);

        CapturedResult::find($request->id)->delete();

        Result::where('captured_result_id', $request->id)->delete();

        return json_encode([
            'status' => true,
        ]);
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
        $targetDateStr = 'Target Date';
        $targetDate = \App\SampleDate::where('sample_header_id', $actual_c->sample_header_id)->where('name', $targetDateStr)->first() ?? new \App\SampleDate();
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

        $mailData = ['batches' => $request->sample_code];
        // return response()->json($request->sample_code);

        $contact = CustomerContact::whereIn('id', $request->contacts)
            ->selectRaw('first_name, middle_name, last_name, email')->get();

        $cArr = [];
        $company = getActiveCompany();
        $message = $request->email_body;
        foreach ($request->sample_code as $code) {
            $batch = SampleHeader::where('id', $code)->first();
            $customer = CRMCustomer::find($batch->crm_customer_id);
            $samples = SampleDetails::where('sample_header_id', $batch->id)->get();
            $start = SampleDetails::where('sample_header_id', $batch->id)->first();
            $end = SampleDetails::where('sample_header_id', $batch->id)->orderBy('id', 'DESC')->first();
            $previous = $batch->status;
            // return response()->json(['start'=>$start,'end'=>$end],200);
            foreach ($contact as $c) {
                $body = 'Dear ' . $customer->name . ',<br><br>
                We are pleased to inform you that your test report is now ready. Please find the report
                attached for your review.<br>
                ' . ($message == '' ? '' : $message . '<br>') . '
                If you have any questions or clarifications, feel free to contact us.<br><br>
                Thank you for choosing FIVET COMPANY LIMITED.<br><br>
                Best regards, <br>
				
				' . $company->name;
                $subject = 'TEST REPORTS;' . $customer->name . ' - ' . $start->sample_code . ' - ' . $end->sample_code;
                $file = \storage_path() . '/app' . $batch->batch_report_url;
                $bcc = true;
                $notify = notify_user($body, $c->email, $subject, $file, $bcc);
            }
            $batch->email_date = getTodayDate();
            $batch->status = 'Completed';
            $batch->save();

            $custodyDetails = [
                'batch_id' => $batch->id,
                'comments' => 'Send out sample report to the client',
                'current' => [
                    'status' => $previous,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
                'target' => [
                    'status' => $batch->status,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
            ];

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
        $batch = getSampleHeaderByID($id);
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
        // DEPRECATED: Redirecting to new Sales Order Wizard
        // This method has been replaced with the modern Livewire-based wizard

        $batchCodes = $request->batch_code ?? [];
        $batchesParam = http_build_query(['batches' => $batchCodes]);

        return redirect()->route('billing.sales-order.create', $batchesParam)
            ->with('info', 'Using new Sales Order Wizard interface');
    }

    /**
     * DEPRECATED: Old pricelist-based invoice generation
     * Kept for reference only
     */
    private function generate_batch_invoice_OLD(Request $request)
    {
        $config = getConfigByName('generate_sample_invoice');
        if (!isset($config[0]->id)) {
            return redirect()->back()->with('error', 'generate_sample_invoice configuration is not set');
        }
        if ($config[0]->value == 'true') {
            $customer_ids = [];
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

            // OLD PRICELIST CODE REMOVED
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
                            $price = PricelistItem::where('pricelist_id', $pricelist->id)->where('analysis_id', (int) $analysis_id)->where('sample_type_id', $batch->sample_type_id)->first();
                            if (!isset($price->id)) {
                                return redirect()->back()->with('error', 'kindly add analysis to pricelist!');
                            }
                            $check_invoice_detail = InvoiceDetails::where('invoice_id', $invoice->id)->where('analysis_type', (int) $analysis_id)->first();
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

    public function generate_batch_invoice_ajax(Request $request)
    {
        // DEPRECATED: Redirecting to new Sales Order Wizard
        // Return URL for frontend to redirect
        $batchCodes = $request->batch_code ?? [];
        $url = route('billing.sales-order.create') . '?' . http_build_query(['batches' => $batchCodes]);

        return response()->json([
            'redirect' => $url,
            'message' => 'Redirecting to Sales Order Wizard...'
        ]);
    }

    /**
     * DEPRECATED: Old Zoho-based AJAX invoice generation
     * Kept for reference only
     */
    private function generate_batch_invoice_ajax_OLD(Request $request)
    {
        $config = getConfigByName('generate_sample_invoice');
        if (!isset($config[0]->id)) {
            return response()->json(['error' => 'generate_sample_invoice configuration is not set']);
        }
        if ($config[0]->value == 'true') {
            $batch_ids = SampleHeader::whereIn('batch_code', $request->batch_code)->leftJoin('customer_invoice', 'customer_invoice.id', '=', 'sample_headers.invoice_id')->whereNull('customer_invoice.sales_order_id')->pluck('sample_headers.id')->toArray();
            $customer_ids = SampleHeader::whereIn('id', $batch_ids)->pluck('crm_customer_id')->toArray();

            if (sizeof($batch_ids) < 1) {
                return response()->json(['error' => 'The selected batch(es) have sales order attached to already sent to zoho']);
            }
            $analysis_with_no_zoho = SampleAnalysisTypeRelation::whereIn('batch_id', $batch_ids)->join('analysis_types', 'analysis_types.id', '=', 'sample_analysis_type_relation.analysis_type_id')->whereNull('analysis_types.zoho_id')->pluck('analysis_types.name')->toArray();
            if (sizeof($analysis_with_no_zoho) > 0) {
                return response()->json(['error' => 'The following analysis types (' . implode(',', $analysis_with_no_zoho) . ') have not been tied to a zoho item']);
            }
            $check_customer = array_unique($customer_ids);
            if (sizeof($check_customer) > 1) {
                return response()->json(['error' => 'The choosen batches are of different customers!']);
            }
            $customer = CRMCustomer::find($check_customer[0]);
            if (!isset($customer->id)) {
                return response()->json(['error' => 'There is no customer with the specified Batches!']);
            }
            if ($customer->currency_id == '') {
                return response()->json(['error' => 'The specified customer has no currency assigned']);
            }
            if (!isset($customer->zohocustomer->zoho_contact_id)) {
                return response()->json(['error' => 'The specified customer has not been tied to zoho customer']);
            }
            $invoices_ids = SampleHeader::whereIn('batch_code', $request->batch_code)->leftJoin('customer_invoice', 'customer_invoice.id', '=', 'sample_headers.invoice_id')->whereNull('customer_invoice.sales_order_id')->pluck('customer_invoice.id')->toArray();
            if (sizeof($invoices_ids) > 0) {
                Invoice::whereIn('id', $invoices_ids)->update(['deleted_at' => date('Y-m-d'), 'delete_reason' => 'Generation of another invoice for batch ' . implode(', ', $request->batch_code)]);
                SampleHeader::whereIn('invoice_id', $invoices_ids)->update(['invoice_id' => null]);
            }

            $module = "Inventory-Management";
            $defaultCurrency = ModulePreConfigs::where('type', 'Currency')->where('module', $module)->where("name", "KES")->first();

            $invoice = new Invoice();
            $invoice->pricelist_id = 0;
            $invoice->currency_id = $customer->currency_id > 0 ? $customer->currency_id : $defaultCurrency->id;
            $invoice->customer_id = $customer->id;
            $invoice->zoho_customer_id = $customer->zohocustomer->zoho_contact_id;
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
                $number = 'IM/SO/' . $zeros . $id_str;
            } else {
                $number = 'IM/SO/' . $id_str;
            }
            $invoice->invoice_number = $number;
            $invoice->save();
            $analyis_types = SampleAnalysisTypeRelation::whereIn('batch_id', $batch_ids)->join('analysis_types', 'analysis_types.id', '=', 'sample_analysis_type_relation.analysis_type_id')->join('inventory_sub_categories', 'inventory_sub_categories.id', '=', 'analysis_types.zoho_id')->leftjoin('zoho_items_pricelist', function ($join) use ($customer) {
                $join->on('inventory_sub_categories.id', '=', 'zoho_items_pricelist.item_id');
                $join->on('zoho_items_pricelist.customer_id', '=', DB::raw($customer->id));
            })->selectRaw('sample_analysis_type_relation.*,analysis_types.name,inventory_sub_categories.name as zoho_name,inventory_sub_categories.unit_price,inventory_sub_categories.zoho_item_code,inventory_sub_categories.id as zoho_analysis_type,zoho_items_pricelist.unit_price as unit_price_rate')->get();

            $details_arr = [];
            foreach ($analyis_types as $a_type) {
                $unit_price = $a_type->unit_price_rate > 0 ? $a_type->unit_price_rate : $a_type->unit_price ?? 0;
                if (!isset($details_arr[$a_type->zoho_item_code])) {
                    // $details_arr[$a_type->zoho_item_code] = [];
                    $details_arr[$a_type->zoho_item_code] = [
                        "crm_customer_id" => $customer->id,
                        "analysis_type" => $a_type->zoho_analysis_type,
                        "analysis_type_name" => $a_type->name,
                        "sample_header_id" => $a_type->batch_id,
                        "sample_detail_id" => $a_type->sample_detail_id,
                        "invoice_id" => $invoice->id,
                        "selling_price" => $unit_price,
                        "cost_price" => 0,
                        "zoho_item_id" => $a_type->zoho_item_code,
                        "zoho_item_name" => $a_type->zoho_name,
                        "quantity" => 1,
                        "total" => $unit_price,
                        "final_unit_price" => $unit_price,
                    ];
                } else {
                    $analysis_arr = explode(',', $details_arr[$a_type->zoho_item_code]['analysis_type_name']);
                    if (!in_array($a_type->name, $analysis_arr)) {
                        $details_arr[$a_type->zoho_item_code]['analysis_type_name'] .= ', ' . $a_type->name;
                    }
                    $details_arr[$a_type->zoho_item_code]['sample_header_id'] .= ', ' . $a_type->batch_id;
                    $details_arr[$a_type->zoho_item_code]['sample_detail_id'] .= ', ' . $a_type->sample_detail_id;
                    $details_arr[$a_type->zoho_item_code]['quantity'] += 1;
                    $details_arr[$a_type->zoho_item_code]['total'] = $details_arr[$a_type->zoho_item_code]['quantity'] * $unit_price;
                }
            }
            InvoiceDetails::where('invoice_id', $invoice->id)->delete();
            $details = array_values($details_arr);
            InvoiceDetails::insert($details);
            $details_invoice = InvoiceDetails::where('invoice_id', $invoice->id)->get();

            $invoice->total = InvoiceDetails::where('invoice_id', $invoice->id)->sum('total');
            // $invoice->total_tax = 0;
            $invoice->save();
            SampleHeader::wherein('id', $batch_ids)->update(['invoice_id' => $invoice->id]);
            return response()->json(['success' => 'Invoice Created Successfully', "invoice" => $invoice, "details" => $details_invoice, 'customer' => $customer]);
        } else {
            return response()->json(['error' => 'Kindly set generate_sample_invoice configuration value to true!']);
        }
    }
    public function sendSalesOrder($invoice_id)
    {
        $invoice = Invoice::with(['currencyinfo', 'crmCustomer'])->find($invoice_id);
        $details = InvoiceDetails::where('invoice_id', $invoice_id)->get();
        $batchids = SampleHeader::where('invoice_id', $invoice->id)->pluck('id')->toArray();
        $sample = SampleDetails::whereIn('sample_header_id', $batchids)->orderBy('id', 'ASC')->first();
        $lineitems = [];
        $itemcounter = 0;
        foreach ($details as $detail) {
            $lineitems[] = [
                "item_order" => $itemcounter,
                "item_id" => $detail->zoho_item_id,
                "rate" => $detail->selling_price,
                "name" => $detail->zoho_item_name,
                "description" => $detail->analysis_title,
                "quantity" => $detail->quantity,
                "discount" => $detail->discount > 0 ? ($detail->discount_type == 'percentage' ? $detail->discount . '%' : $detail->discount) : 0,
            ];
            $itemcounter = $itemcounter + 1;
        }

        $salesOrder = [
            "customer_id" => $invoice->zoho_customer_id,
            "currency_id" => $invoice->currencyinfo->zoho_id,
            "date" => date('Y-m-d'),
            "line_items" => $lineitems,
            "reference_number" => $sample->sample_code,
            "custom_fields" => [
                [
                    "customfield_id" => config('zoho.ZOHO_SO_IMARAUSER_FIELD'),
                    "value" => auth()->user()->name,
                ]
            ],

        ];

        // return response()->json($salesOrder);
        $zohoService = new ZohoController();
        $zoho_sales = $zohoService->createSalesrder($salesOrder);
        $zoho_sales_id = isset($zoho_sales['salesorder']['salesorder_id']) ? $zoho_sales['salesorder']['salesorder_id'] : 0;
        $invoice->zoho_response = json_encode($zoho_sales);
        if ($zoho_sales_id != 0) {
            $invoice->sales_order_id = $zoho_sales_id;
            $invoice->save();
            return response()->json(['success' => 'Sales Order Created successfully!', 'invoice' => $invoice]);
        }
        $invoice->save();
        return response()->json(['error' => 'Sales Order not created successfully!', 'invoice' => $invoice, 'zoho_res' => $zoho_sales, 'salesorder' => $salesOrder]);
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
            $batch->status = 'Sample Verification';
            if (isset($request->batch_comment)) {
                $sample_detail = SampleDetails::where('sample_header_id', $batch->id)->first();
                $sample_detail->main_body = $request->comment ?? '';
                $sample_detail->save();
            }
            $batch->save();
            $custodyDetails = [
                'batch_id' => $batch->id,
                'comments' => $request->comment ?? '',
                'current' => [
                    'status' => $current,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
                'target' => [
                    'status' => $batch->status,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
            ];

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

    /**
     * Regenerate the submission form PDF for a batch and replace any existing Submission Form attachment.
     * Batch must have a submission_form_instance_id. Uses latest instance data for PDF generation.
     */
    public function regenerateSubmissionForm(Request $request, $batch): \Illuminate\Http\RedirectResponse
    {
        $sampleHeader = SampleHeader::find($batch);
        if (! $sampleHeader || ! $sampleHeader->hasSubmissionForm()) {
            return redirect()->back()->with('error', 'This batch has no linked submission form.');
        }

        $instance = $sampleHeader->submissionFormInstance;
        if (! $instance) {
            return redirect()->back()->with('error', 'Submission form instance not found.');
        }

        $submissionFormAttachmentTypeId = SystemConfiguration::where('key', 'attachment_type')
            ->where('value', 'Submission Form')
            ->value('id');
        if ($submissionFormAttachmentTypeId === null) {
            $submissionFormAttachmentTypeId = SystemConfiguration::where('key', 'attachment_type')->value('id');
        }
        if ($submissionFormAttachmentTypeId === null) {
            return redirect()->back()->with('error', 'Submission Form attachment type is not configured.');
        }

        $existing = BatchAttachment::where('batch_id', $sampleHeader->id)
            ->where('attachment_type', $submissionFormAttachmentTypeId)
            ->get();
        foreach ($existing as $att) {
            $url = $att->attachment_url;
            if ($url && str_starts_with($url, '/storage/batch-attachments/')) {
                $filename = basename(urldecode(parse_url($url, PHP_URL_PATH)));
                Storage::delete('batch-attachments/' . $filename);
            }
            $att->delete();
        }

        app(SubmissionFormPdfService::class)->attachSubmissionFormPdfToBatch(
            $instance,
            $sampleHeader,
            $submissionFormAttachmentTypeId
        );

        return redirect()->back()->with('success', 'Submission form PDF regenerated and attached.');
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
            if (isset($request->is_internal) || isset($request->internal_use)) {
                $new->is_internal = 1;
            }
            // Flag whether this attachment should be included when merging into the COA
            $new->show_on_coa = isset($request->show_on_coa) ? 1 : 0;
            $path = $request->attachment->path();
            $file = Storage::putFile('batch-attachments', new File($path));
            $file = explode('/', $file);

            $fName = '/storage/batch-attachments/' . urlencode(end($file));

            $new->attachment_url = (string) $fName;
            $new->save();

            // Link captured results to this attachment (when provided)
            if ($request->filled('selected_captured_result_ids')) {
                $ids = array_filter(
                    array_map('intval', explode(',', $request->selected_captured_result_ids))
                );

                if (!empty($ids)) {
                    // Safety: only update results that truly belong to this batch
                    CapturedResult::whereIn('id', $ids)
                        ->where('sample_header_id', $batch->id)
                        ->update(['batch_attachment_id' => $new->id]);

                    // For any attachment-based placeholder results, flip the
                    // textual result to "as attached" now that an attachment exists.
                    CapturedResult::whereIn('id', $ids)
                        ->where('sample_header_id', $batch->id)
                        ->whereIn('result', ['has attachment', 'No attachment', 'no attachment'])
                        ->update(['result' => 'as attached']);

                    \Log::info('add_batch_attachment: linked captured results', [
                        'batch_id'          => $batch->id,
                        'attachment_id'     => $new->id,
                        'captured_ids'      => $ids,
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Attachment Added Successfully');
        } else {
            return redirect()->back()->with('error', 'No batch with the specified ID');
        }
    }

    public function merge_attachments(Request $request)
    {
        $request->validate([
            'attachment_ids' => 'required|string',
            'title' => 'required|string',
            'attachment_type' => 'required|integer',
            'batch_id' => 'required|integer'
        ]);

        $ids = explode(',', $request->attachment_ids);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No attachments selected for merging.');
        }

        // Fetch attachments. Note: WHERE IN does not guarantee order, so we sort manually
        $attachments = BatchAttachment::whereIn('id', $ids)->get();

        $orderedAttachments = [];
        foreach ($ids as $id) {
            $att = $attachments->firstWhere('id', $id);
            if ($att) {
                $orderedAttachments[] = $att;
            }
        }

        if (empty($orderedAttachments)) {
            return redirect()->back()->with('error', 'Could not retrieve selected attachments.');
        }

        $pdf = new TcpdfFpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);

        $validFiles = [];
        $totalPageCount = 0;

        foreach ($orderedAttachments as $attachment) {
            $relativePath = urldecode($attachment->attachment_url);
            $relativePath = ltrim($relativePath, '/');
            $filePath = public_path($relativePath);

            if (!file_exists($filePath)) {
                $cleanPath = ltrim($relativePath, '/');
                if (strpos($cleanPath, 'storage/') === 0) {
                    $storageInternalPath = substr($cleanPath, 8);
                    $fallbackPath = storage_path('app/' . $storageInternalPath);
                    if (file_exists($fallbackPath)) {
                        $filePath = $fallbackPath;
                    }
                }
            }

            if (file_exists($filePath)) {
                try {
                    $tempPdf = new TcpdfFpdi();
                    $pCount = $tempPdf->setSourceFile($filePath);
                    $totalPageCount += $pCount;
                    $validFiles[] = ['path' => $filePath, 'count' => $pCount, 'title' => $attachment->title];
                } catch (\Exception $e) {
                    \Log::warning("Could not pre-scan PDF {$attachment->title}: " . $e->getMessage());
                }
            }
        }

        $filesMerged = count($validFiles);
        if ($filesMerged === 0) {
            return redirect()->back()->with('error', 'No valid files found to merge.');
        }

        $currentPageGlobal = 1;
        foreach ($validFiles as $fileInfo) {
            try {
                $pdf->setSourceFile($fileInfo['path']);
                for ($pageNo = 1; $pageNo <= $fileInfo['count']; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($templateId);

                    $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
                    $pdf->useTemplate($templateId);

                    // Set white fill color for covering original page numbers
                    $pdf->SetFillColor(255, 255, 255); // White
                    $pdf->SetDrawColor(255, 255, 255);

                    // Cover common page number positions with comprehensive areas
                    // Use larger coverage to account for different font sizes, positions, and variations
                    $coverageWidth = 90; // Generous width for "Page 999 of 9999" in various font sizes
                    $coverageHeight = 22; // Generous height for page numbers in various font sizes

                    // 1. Bottom-right position (most common)
                    // Cover multiple variations to catch all possible positions
                    $pdf->Rect($size['width'] - 95, $size['height'] - 28, $coverageWidth, $coverageHeight, 'F');
                    $pdf->Rect($size['width'] - 85, $size['height'] - 23, 80, 20, 'F');
                    $pdf->Rect($size['width'] - 75, $size['height'] - 18, 70, 18, 'F');

                    // 2. Top-right position (covers "Page 1 of 6" etc.)
                    // Cover multiple variations in top-right corner
                    $pdf->Rect($size['width'] - 95, 0, $coverageWidth, $coverageHeight, 'F');
                    $pdf->Rect($size['width'] - 85, 0, 80, 28, 'F');
                    $pdf->Rect($size['width'] - 75, 0, 70, 22, 'F');

                    // 3. Bottom-center position (some reports use this)
                    $bottomCenterX = ($size['width'] / 2) - ($coverageWidth / 2);
                    $pdf->Rect($bottomCenterX, $size['height'] - 28, $coverageWidth, $coverageHeight, 'F');
                    $pdf->Rect(($size['width'] / 2) - 45, $size['height'] - 23, 90, 20, 'F');

                    // 4. Top-center position (less common but some documents use it)
                    $topCenterX = ($size['width'] / 2) - ($coverageWidth / 2);
                    $pdf->Rect($topCenterX, 0, $coverageWidth, $coverageHeight, 'F');

                    // Now add "Page X of Y" numbering at the bottom center
                    $pdf->SetFont('helvetica', '', 10);
                    $text = "Page $currentPageGlobal of $totalPageCount";
                    $xTextPos = $size['width'] / 2 - 15; // Where text will be drawn
                    $yTextPos = $size['height'] - 10; // Where text will be drawn
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->Text($xTextPos, $yTextPos, $text);

                    $currentPageGlobal++;
                }
            } catch (\Exception $e) {
                \Log::error("Error merging file {$fileInfo['title']}: " . $e->getMessage());
            }
        }

        if ($filesMerged === 0) {
            return redirect()->back()->with('error', 'No valid files found to merge.');
        }

        // Output merged PDF
        $outputContent = $pdf->Output('S');
        $fileName = 'Merged_Report_' . time() . '.pdf';
        $storagePath = 'batch-attachments/' . $fileName;

        // Save to storage
        Storage::put($storagePath, $outputContent);

        // Save to Database
        $newAttachment = new BatchAttachment();
        $newAttachment->batch_id = $request->batch_id;
        $newAttachment->uploaded_by = auth()->user()->id;
        $newAttachment->title = $request->title;
        $newAttachment->attachment_type = $request->attachment_type;
        $newAttachment->is_internal = 0; // Default to public/external
        $newAttachment->attachment_url = '/storage/' . $storagePath;
        $newAttachment->save();

        return redirect()->back()->with('success', 'Attachments merged successfully!');
    }

    public function delete_batch_attachmment(Request $request)
    {
        $attachment = BatchAttachment::find($request->attachment_id);
        if ($attachment) {

            // Delete the physical file
            $relativePath = urldecode($attachment->attachment_url);
            $filePath = public_path($relativePath);

            if (!file_exists($filePath)) {
                // Fallback check in storage/app
                $cleanPath = ltrim($relativePath, '/');
                if (strpos($cleanPath, 'storage/') === 0) {
                    $storageInternalPath = substr($cleanPath, 8);
                    $fallbackPath = storage_path('app/' . $storageInternalPath);
                    if (file_exists($fallbackPath)) {
                        $filePath = $fallbackPath;
                    }
                }
            }

            if (file_exists($filePath)) {
                try {
                    unlink($filePath);
                    \Log::info("Deleted attachment file: {$filePath}");
                } catch (\Exception $e) {
                    \Log::error("Failed to delete attachment file: {$filePath}. Error: " . $e->getMessage());
                }
            } else {
                \Log::warning("Attachment file to delete not found: {$attachment->attachment_url}");
            }

            // Delete the database record
            // Since User requested "Deletes it permanently", we force delete if soft deletes were enabled, 
            // but BatchAttachment model doesn't use SoftDeletes trait, so delete() is permanent.
            $attachment->delete();

            return redirect()->back()->with('success', 'Attachment deleted successfully!');
        } else {
            return redirect()->back()->with('error', 'No attachment with the specified ID');
        }
    }

    public function downloadBatchAttachment($id)
    {
        $attachment = BatchAttachment::findOrFail($id);

        // Get the file path from attachment_url
        $relativePath = urldecode($attachment->attachment_url);
        $relativePath = ltrim($relativePath, '/');
        $filePath = public_path($relativePath);

        // Check if file exists in public path
        if (!file_exists($filePath)) {
            // Fallback: Check in storage/app
            $cleanPath = ltrim($relativePath, '/');
            if (strpos($cleanPath, 'storage/') === 0) {
                $storageInternalPath = substr($cleanPath, 8);
                $fallbackPath = storage_path('app/' . $storageInternalPath);

                if (file_exists($fallbackPath)) {
                    $filePath = $fallbackPath;
                }
            }
        }

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'Attachment file not found.');
        }

        // Get the original filename from the path
        $fileName = basename($filePath);

        // Return the file as a download
        return response()->download($filePath, $fileName);
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

                $custodyDetails = [
                    'batch_id' => $header->id,
                    'comments' => $request->comment ?? '',
                    'current' => [
                        'status' => $header->status,
                        'tracking_stage' => $header->sample_tracking_stage,
                    ],
                    'target' => [
                        'status' => $header->status,
                        'tracking_stage' => $header->sample_tracking_stage,
                    ],
                ];
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
                $price = PricelistItem::where('pricelist_id', $pricelist->id)->where('analysis_id', (int) $analysis_id)->where('sample_type_id', $batch->sample_type_id)->first();
                if (!isset($price->id)) {
                    return redirect()->back()->with('error', 'kindly add analysis to pricelist!');
                }
                $check_invoice_detail = InvoiceDetails::where('invoice_id', $invoice->id)->where('analysis_type', (int) $analysis_id)->first();
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
        $captured_reults = CapturedResult::where('operator_id', 0)->get();
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
        $header->status = 'QC Approved';
        $header->save();

        return redirect()->route('sample-workflow', ['status' => $previous_status])->with('success', 'Batch marked complete succesffuly');
    }

    public function markAccredittedSamples($header_id = 0)
    {
        if ($header_id == 0) {
            $sample_ids = SampleHeader::whereIn('status', ['Samples Reception', 'Samples Request Review', 'Samples In Lab'])->pluck('id')->toArray();

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
        $labs = Lab::where('active', 1)->get();

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
                    'sample_id' => $id,
                    'to_lab_section_id' => $request->to_lab_section_id,
                    'from_lab_section_id' => isset($last_log->id) ? $last_log->to_lab_section_id : 0,
                    'quantity' => $request->quantity,
                    'submited_by' => auth()->user()->id,
                    'date_submitted' => date('Y-m-d h:i:s a'),
                    'expected_date' => $request->expected_date == '' ? date('Y-m-d', strtotime($batch->get_date('Target Date')['date'])) : $request->expected_date,
                    'prelim_date' => $request->prelim_date ?? '',
                    'remarks' => $request->remarks ?? '',
                ];
            }

            // return response()->json($log);
        } else {
            $last_log = InterLabLog::where('sample_id', $request->sample_id)->where('status', 1)->orderBy('date_received', 'DESC')->first();
            if (isset($request->interlab_id) && $request->interlab_id != '0') {
                $log = [
                    'sample_id' => $request->sample_id,
                    'to_lab_section_id' => $request->to_lab_section_id,
                    'quantity' => $request->quantity,
                    'submited_by' => auth()->user()->id,
                    'date_submitted' => date('Y-m-d h:i:s a'),
                    'expected_date' => $request->expected_date,
                    'from_lab_section_id' => isset($last_log->id) ? $last_log->to_lab_section_id : 0,
                    'prelim_date' => $request->prelim_date ?? '',
                    'remarks' => $request->remarks ?? '',
                ];
            } else {
                $log = [
                    'sample_id' => $request->sample_id,
                    'to_lab_section_id' => $request->to_lab_section_id,
                    'from_lab_section_id' => isset($last_log->id) ? $last_log->to_lab_section_id : 0,
                    'quantity' => $request->quantity,
                    'submited_by' => auth()->user()->id,
                    'date_submitted' => date('Y-m-d h:i:s a'),
                    'expected_date' => $request->expected_date,
                    'prelim_date' => $request->prelim_date ?? '',
                    'remarks' => $request->remarks ?? '',
                ];
            }
        }
        isset($request->interlab_id) && $request->interlab_id != '0' ? InterLabLog::find($request->interlab_id)->update($log) : InterLabLog::insert($log);
        if ($request->notify_user != '' || $request->sms_notify != '') {
            $sample_codes = isset($request->batch_level) ? SampleDetails::whereIn('id', $sample_ids)->get() : SampleDetails::where('id', $request->sample_id)->get();
            $samplecodesList = '';

            foreach ($sample_codes as $s_code) {
                $samplecodesList .= '<li><a href="http://172.16.16.252:8080/sample-workflow/batch/' . $s_code->sample_header_id . '/details/0/0/' . $s_code->getSampleHeader()->status . '">' . $s_code->sample_code . '</a></li>';
            }
            $bcc_emails = User::whereIn('id', $request->also_notify ?? [])->pluck('email')->toArray();
            $body = 'Hi Team, <br> The following sample(s) require  your attention for approval of inter laboratory transfer raised by ' . auth()->user()->name . '<br>Click the sample codes to access the sample InterLab Log <br><ul>' . $samplecodesList . '</ul>';
            $to_email = getUserById($request->notify_user);

            if (isset($to_email->id)) {
                notify_user($body, $to_email->email, '[FIVET LIMS] Inter Laboratory Transfer Approval Notification', false, false, $bcc_emails);
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

        return response()->json(isset($last_log->id) ? $last_log->to_lab_code . ' ' . $last_log->to_lab_name : 'Reception');
    }

    public function changeInterLabLogStatus(Request $request)
    {
        if ($request->status == '') {
            return redirect()->back()->with('error', 'Status is a required field');
        }
        if (isset($request->inter_lab_id)) {
            InterLabLog::find($request->inter_lab_id)->update(['status' => $request->status, 'date_received' => date('Y-m-d h:i:s a'), 'received_by' => auth()->user()->id]);
        } else {
            InterLabLog::whereIn('id', explode(',', $request->inter_lab_ids))->update(['status' => $request->status, 'date_received' => date('Y-m-d h:i:s a'), 'received_by' => auth()->user()->id]);
        }

        return redirect()->back()->with('success', 'Inter laboratory Log status updated successfully');
    }

    public function interLabTransferIndex($is_archived = 0)
    {
        $interlabs = $is_archived == 0 ? InterLabLogView::orderBy('id', 'DESC')->where('batch_status', '!=', 'Completed')->get() : InterLabLogView::orderBy('id', 'DESC')->get();
        $samples = $interlabs->pluck('sample_code')->toArray();
        $labs = Lab::where('active', 1)->get();
        $users = User::where('is_client', 0)->where('supplier_id', 0)->where('active', 1)->get();

        return view('layouts.lab.interlab.index', compact('interlabs', 'samples', 'labs', 'users'));
    }

    public function deleteInterLabTransferLogs(Request $request)
    {
        InterLabLog::whereIn('id', explode(',', $request->inter_lab_ids))->delete();

        return redirect()->back()->with('success', 'Inter Laboratory Transfer Log(s) deleted successfully');
    }

    public function generateCustomerFocusIndex(Request $request, $batch_id)
    {
        $batch = SampleHeader::with('submissionFormInstance')->find($batch_id);

        // Check if batch exists
        if (!$batch) {
            return redirect()->back()->with('error', 'Batch not found.');
        }

        // Check if batch has a submission form instance linked
        if ($batch->hasSubmissionForm()) {
            return redirect()->route('submission-forms.instances.batch-view', $batch->submissionFormInstance);
        }

        // No submission form instance - redirect back with error message
        return redirect()->route('view-batch-details', $batch_id)
            ->with('error', 'This batch does not have a submission form instance linked. Please create or link a submission form first.');

        // Old customer_focus logic below (kept for reference but unreachable)
        if ($batch_id == 0 || $batch->c_focus_ids_clustered != '') {
            $batches = $batch_id == 0 ? SampleHeader::whereIn('batch_code', $request->batch_code) : SampleHeader::whereIn('id', explode(',', $batch->c_focus_ids_clustered));
            $getCustomers = clone $batches;
            $customer_ids = array_unique($getCustomers->pluck('crm_customer_id')->toArray());
            if (sizeof($customer_ids) > 1) {
                return redirect()->back()->with('error', 'All batches should be of the same client! Kindly check on the batches you have selected');
            }
            $batch = $getCustomers->orderBy('created_at', 'ASC')->first();
            $customer = CrmCustomer::find($batch->crm_customer_id);
            $sample_type_ids = $batches->pluck('sample_type_id')->toArray();
            $sample_types = implode(', ', array_unique(SampleType::whereIn('id', $sample_type_ids)->pluck('name')->toArray()));
            $company = getActiveCompany();
            $config_docs_setting = SystemConfiguration::where('key', 'customer_focus_id')->first();
            $docs_settings = SystemConfiguration::where('configuration_type_id', $config_docs_setting->value)->pluck('value', 'key')->toArray();
            $payment_detail = [
                'balance' => $request->balance,
                // "total_amount"=>array_sum($batches->pluck('invoice_amount')->toArray()),
                'amount_paid' => $request->amount_paid,
                'vat' => $request->vat,
                'invoice_amount' => $request->invoice_amount,
            ];
            $batch_ids = $batches->pluck('id')->toArray();
            $samples = SamplesCategory::whereIn('sample_header_id', $batch_ids)->get();
            SampleAnalysisTypeRelation::whereIn('batch_id', $batch_ids)->whereNotIn('sample_detail_id', $samples->pluck('id')->toArray())->delete();
            $review_staff = getUserById($batch->receiving_officer);
            $is_clustered = 1;
            // return response()->json($batch_ids);

            isset($request->batch_code) && sizeof($request->batch_code) > 0 ? SampleHeader::whereIn('batch_code', $request->batch_code)->update(['c_focus_ids_clustered' => sizeof($batch_ids) > 0 ? implode(',', $batch_ids) : '']) : '';

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
        SampleAnalysisTypeRelation::where('batch_id', $batch->id)->whereNotIn('sample_detail_id', $samples->pluck('id')->toArray())->delete();
        $payment_detail = InvoicePaymentDetail::where('batch_id', $batch->id)->orderBy('id', 'DESC')->first();

        return view('layouts.lab.sample-workflow.customer_focus', compact('batch', 'customer', 'company', 'docs_settings', 'review_staff', 'samples', 'payment_detail', 'is_clustered'));
    }

    public function sendBatchScheduleAnalysis(Request $request)
    {
        $batch = SampleHeader::with(['customer', 'sample_type'])->find($request->batch_id);

        if ($batch->samples()->count() == 0) {
            return redirect()->back()->with('error', 'You cannot send schedule of analysis for a batch with no sample');
        }

        $samples = SampleDetails::where('sample_header_id', $batch->id)->get();
        $sampleTrs = "";
        foreach ($samples as $sample) {
            $target_date = date('Y-m-d', strtotime($sample->targetDateRelation()));
            $sampleTrs .= '
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($sample->sample_code) . '</td>
                <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars(implode(',', $sample->analyteNames())) . '</td>
                <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($target_date) . '</td>  
            </tr>';
        }

        // $customer = CrmCustomer::find($batch->crm_customer_id);
        $contact = CustomerContact::find($request->contact_id);
        if (isset($contact->id) && $contact->email != '') {
            // return response()->json($sampleTrs);

            $body = '
			<div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
                <p style="font-size: 12px;">
                    Dear ' . $batch->customer->name . ', <br><br>
                    I hope this message finds you well. <br>
                    We are pleased to confirm that your samples <b>' . strtoupper($batch->sample_type->name) . '</b> have been successfully received and assigned following Ref IDs: 
                </p>

                <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                    <tr>
                        <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Sample Reference No</th>
                        <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Test(s) Required</th>
                        <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Expected Results Date</th>
                    </tr>
                    ' . $sampleTrs . '
                </table>

                <p style="font-size: 12px; margin-top: 15px;">
                    <br>
                    Once analysis is completed, you will receive an update regarding your test results.<br>
                    For any inquiries, please contact us on <b>fivet.co.ke, /+12345678 </b>. <br>
                    Thank you for the opportunity to serve you. <br><br>
                    Kind regards, <br>
                    FIVET COMPANY LIMITED
                </p>
            </div>
			';
            notify_user($body, $contact->email, '[FIVET] Confirmation of Sample Receipt and Schedule of Analysis ' . $batch->batch_code, false, true, ['dannyagah13@gmail.com']);

            $batch->schedule_sent = 1;
            $batch->schedule_analysis_sent = date('Y-m-d');
            $batch->schedule_analysis_sender = auth()->user()->id;
            $batch->save();
            $schedule_str = 'Schedule Of Analysis Sendoff';
            $schedueDate = SampleDate::where('sample_header_id', $batch->id)->where('name', $schedule_str)->first() ?? new SampleDate();
            $schedueDate->name = $schedule_str;
            $schedueDate->sample_header_id = $batch->id;
            $schedueDate->date = date('Y-m-d');
            $schedueDate->save();

            return redirect()->back()->with('success', 'Schedule of analysis sent out successfully');
        }

        return redirect()->back()->with('error', 'Kindly choose the customer contact first on the form before sending the schedule of analysis');
    }

    public function sendBatchPaymentReminder(Request $request)
    {
        $contact = CustomerContact::find($request->contact_id);
        $batch = SampleHeader::find($request->batch_id);
        notify_user($request->body, $contact->email, '[FIVET LIMS] Payment Reminder ' . $batch->batch_code);

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

            $custodyDetails = [
                'batch_id' => $batch->id,
                'comments' => $request->comments ?? '',
                'current' => [
                    'status' => $batch->status,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
                'target' => [
                    'status' => $status,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
            ];

            $this->updateChainofCustody($custodyDetails);
            $batch->status = $status;
            $batch->save();
        }

        return redirect()->back()->with('success', 'Sample(s) moved to samples in Lab section successfully');
    }

    public function showBatchCOA(Request $request)
    {
        $batch = SampleHeader::find($request->batch_id);
        $batch_approvers = BatchLabSectionApprover::where('batch_id', $batch->id)->where('show_report', 1)->where('status', 1)->get();
        $samples = SamplesCategory::where('sample_header_id', $request->batch_id)->get();

        $status = $batch->status;
        $result_presentation = SystemConfiguration::where('key', 'exponential_result_format')->first();

        $company = getActiveCompany();
        $exclude_pesticides = isset($request->add_pesticide) ? 0 : 1;
        $standard_report = $request->template_id;
        $analysis_date = SampleAnalysisDates::where('sample_header_id', $batch->id)->orderBy('start_analysis_date', 'ASC')->first();
        // return response()->json('here');
        return view('layouts.lab.sample-workflow.report-formats.standard_report', compact('batch', 'samples', 'status', 'company', 'batch_approvers', 'standard_report', 'analysis_date', 'exclude_pesticides', 'result_presentation'));
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

        $users = [];
        $user_approvers = [];
        foreach (explode(',', $batch->lab_section_ids) as $section_id) {
            $c_user = CapturedResult::where('lab_section_id', $section_id)->where('sample_header_id', $batch->id)->orderBy('updated_at', 'DESC')->first();

            if ($c_user && $c_user->operator_id) {
                array_push($users, $c_user->operator_id);
                $user_approvers[$c_user->operator_id] = $section_id;
            }
        }
        $analysts = User::whereIn('id', $users)->get();

        if ($section_users->count() <= 0) {
            return redirect()->back()->with('error', 'Kindly provide approval configuration for the selected batch lab sections');
        }
        if ($request->status == 'Sample Verification') {
            TatCaptured::where('sample_header_id', $batch->id)->update(['is_complete' => 1]);
            $batch->status = $request->level == '0' ? $request->status : $batch->status;
            $batch->report_status = $request->level == '0' ? $request->level : $batch->report_status;
            $batch->prelim_report_status = $request->level != '0' ? $request->level : $batch->prelim_report_status;
            $batch->prelim_batch_status = $request->level != '0' ? $request->status : $batch->prelim_batch_status;
            if ($request->level == '0') {
                $batch->report_status = '';
                $batch->prelim_report_status = 0;
                $batch->prelim_batch_status = '';
            }
            if ($request->level != '2') {
                $request->level != 0 ? BatchLabSectionApprover::where('batch_id', $batch->id)->delete() : BatchLabSectionApprover::where('batch_id', $batch->id)->where('is_prelim', 0)->delete();
                foreach ($request->section_id as $section_id) {
                    $user_id = $request->appover_user[$section_id];
                    $approvers = BatchLabSectionApprover::where('batch_id', $batch->id)->where('user_id', $user_id)->where('title', '$user_id->title')->first() ?? new BatchLabSectionApprover();
                    $approvers->status = 0;
                    $approvers->user_id = $user_id;
                    $approvers->title = $request->title[$section_id];
                    $approvers->lab_section_ids = $approvers->lab_section_ids == '' ? $approvers->lab_section_ids . $section_id : $approvers->lab_section_ids . ',' . $section_id;
                    $approvers->batch_id = $batch->id;
                    $approvers->batch_status = $request->status;
                    $approvers->is_prelim = $request->level != '0' ? 1 : 0;
                    $approvers->show_report = 1;
                    $approvers->save();
                }
                foreach ($analysts as $analyst) {
                    $approvers = BatchLabSectionApprover::where('batch_id', $batch->id)->where('user_id', $analyst->id)->where('title', '$user_id->title')->first() ?? new BatchLabSectionApprover();
                    $section = SampleAnalysisStage::find($user_approvers[$analyst->id]);
                    $approvers->status = 1;
                    $approvers->user_id = $analyst->id;
                    $approvers->title = $section->title ?? 'Verifier';
                    $approvers->lab_section_ids = $approvers->lab_section_ids == '' ? $approvers->lab_section_ids . $section->id : $approvers->lab_section_ids . ',' . $section->id;
                    $approvers->batch_id = $batch->id;
                    $approvers->batch_status = $request->status;
                    $approvers->is_prelim = $request->level != '0' ? 1 : 0;
                    $approvers->approval_date = date('Y-m-d h:i:s a');
                    $approvers->show_report = 0;
                    $approvers->save();
                }
            }
            $batch->save();

            return redirect()->route('sample-workflow', ['status' => $previousWorkflow])->with('success', 'Batch move was successful');
        }

        $approvers_user_ids = BatchLabSectionApprover::where('batch_id', $batch->id)->pluck('user_id')->toArray();
        if (in_array($request->user_id, $approvers_user_ids)) {
            return redirect()->back()->with('error', 'System cannot assign the specified user as an approver since the user is already an approver');
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
            $message = 'Hi ' . $user->name . ', <br>' . $batch->batch_code . ' COA needs your approval at ' . $batch->status . '. <br> Comments : ' . $request->comments;
            notify_user($message, $user->email, '[FIVET LIMS] ' . $batch->batch_code . ' Batch Approval Notification');
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
        $ip_address_link = request()->root();
        BatchLabSectionApprover::where('id', $request->approver_id)->update(['status' => $request->status, 'approval_date' => date('Y-m-d H:i:s'), 'remark' => $request->remark]);
        if (BatchLabSectionApprover::where('id', $request->approver_id)->where('status', 0)->get()->count() == 0) {
            $approver = BatchLabSectionApprover::find($request->approver_id);
            $batch = SampleHeader::find($approver->batch_id);
            if ($batch->status == 'Sample Approval') {
                $batch->approval_date = getTodayDate();
                $batch->save();
                $link = $ip_address_link . '/sample-workflow/batch/' . $batch->id . '/details/0/0/All%20Samples';
                $samplescodes = SampleDetails::where('sample_header_id', $batch->id)->pluck('sample_code')->toArray();
                $li_str = '';
                foreach ($samplescodes as $code) {
                    $li_str .= '<li><a href="' . $link . '" >' . $code . '</a></li>';
                }
                $subject = 'Automated Invoice Request - Job [' . implode(', ', $samplescodes) . ']';
                $message = 'Dear Finance Team,<br><br>

				This is an automated notification to inform you that the following job is now ready to be invoiced: <br>
				
				<b>*Job Number/Report Number:*</b> <br>
				<ul>' . $li_str . '</ul>
				Please proceed with creating an invoice for this job at your earliest convenience. If additional information is required, kindly reach out to the relevant department.
				Thank you for your attention.';
                $emails = ['laboratory@FIVET.com'];

                $invoice = Invoice::find($batch->invoice_id);
                if (isset($invoice->id)) {
                    $zohoService = new ZohoController();
                    $zoho_sales = $zohoService->changeSalesOrderStatus($invoice->zoho_id);
                    if ($zoho_sales['code'] == 0) {
                        $invoice->zoho_so_confirmed = date('Y-m-d');
                        $invoice->save();
                    }
                }

                // $emails = ['danmuv12@gmail.com'];
                // notify_user($message, 'dannyagah13@gmail.com', $subject, false, true, $emails);
                try {
                    notify_user($message, 'Accounts@FIVET.com', $subject, false, true, $emails);
                } catch (\Exception $e) {
                    return redirect()->back()->with('success', 'Batch Approval updated successfully but notifications to accounts and lab were not set');
                }
            }
        }

        return redirect()->back()->with('success', 'Batch Approval updated successfully');
    }

    public function getClientDetailsAjax($id)
    {
        $customer = CrmCustomer::with(['units' => function ($q) {
            $q->where('active', 1);
        }, 'contacts'])->find($id);

        if (! $customer) {
            return response()->json([
                'units' => [],
                'unit_name' => 'Company Section',
                'sample_point_name' => 'Sample Point',
                'contacts' => [],
                'customer' => null,
            ]);
        }

        // Prefer the configurable "unit" label from CRM.
        // If not set, default to "Company Section" as requested.
        $unitName = trim((string) ($customer->unit_configurable_name ?? ''));
        if ($unitName === '') {
            $unitName = 'Company Section';
        }

        // Prefer configurable sample point label, with a sensible default.
        $samplePointName = trim((string) ($customer->sample_point_configurable_name ?? ''));
        if ($samplePointName === '') {
            $samplePointName = 'Sample Point';
        }

        return response()->json([
            'units' => $customer->units,
            'unit_name' => $unitName,
            'sample_point_name' => $samplePointName,
            'contacts' => $customer->contacts,
            'customer' => $customer,
        ]);
    }

    public function searchClients(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 50);
        if ($perPage < 1) {
            $perPage = 50;
        }
        $perPage = min($perPage, 100);

        $page = (int) $request->input('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $term = trim((string) $request->input('term', $request->input('q', '')));

        $builders = CrmCustomer::query()
            ->select(['id', 'name'])
            ->where('active', 1);

        if ($term !== '') {
            $builders->where(function ($query) use ($term) {
                $query->where('name', 'like', '%' . $term . '%')
                    ->orWhere('code', 'like', '%' . $term . '%');
            });
        }

        $clients = $builders->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);

        $results = collect($clients->items())->map(static function (CRMCustomer $client): array {
            return [
                'id' => $client->id,
                'text' => $client->name,
            ];
        })->values();

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => $clients->hasMorePages(),
            ],
        ]);
    }

    public function generateTabletCustomerFocusIndex(Request $request)
    {
        $sample_code = 'S' . date('Y') . $request->sample_no;
        $sample = SampleDetails::where('sample_code', $request->sample_no)->first();
        if (!isset($sample->id)) {
            return redirect()->back()->with('error', 'There is no sample with ' . $request->sample_no . ' sample/job number');
        }
        $batch = SampleHeader::find($sample->sample_header_id);
        if (!isset($batch->id)) {
            return redirect()->back()->with('error', 'There is no batch associated with the specified sample');
        }

        if (isset($request->is_clustered)) {
            $batches = SampleHeader::whereIn('id', explode(',', $batch->c_focus_ids_clustered));
            $getCustomers = clone $batches;
            $customer_ids = array_unique($getCustomers->pluck('crm_customer_id')->toArray());
            if (sizeof($customer_ids) > 1) {
                return redirect()->back()->with('error', 'All batches should be of the same client! Kindly check on the batches you have selected');
            }
            $batch = $getCustomers->orderBy('created_at', 'ASC')->first();
            if (!isset($batch->id)) {
                return redirect()->back()->with('error', 'the batch has no clustered customer focus');
            }
            $customer = CrmCustomer::find($batch->crm_customer_id);
            $sample_type_ids = $batches->pluck('sample_type_id')->toArray();
            $sample_types = implode(', ', array_unique(SampleType::whereIn('id', $sample_type_ids)->pluck('name')->toArray()));
            $company = getActiveCompany();
            $config_docs_setting = SystemConfiguration::where('key', 'customer_focus_id')->first();
            $docs_settings = SystemConfiguration::where('configuration_type_id', $config_docs_setting->value)->pluck('value', 'key')->toArray();
            $payment_detail = [
                'balance' => $batch->cluster_balance,
                // "total_amount"=>array_sum($batches->pluck('invoice_amount')->toArray()),
                'amount_paid' => $batch->cluster_amount_paid,
                'vat' => $batch->cluster_vat,
                'invoice_amount' => $batch->cluster_amount,
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
                Sampleheader::whereIn('id', explode(',', $batch->c_focus_ids_clustered))->update(['declaration_customer_approval_date' => date('Y-m-d h:i:s a'), 'declaration_customer_signature' => $request->signature, 'declaration_customer_contact_name' => $request->contact_person]);

                return response()->json('success');
            }
        }
        Sampleheader::find($request->sample_header_id)->update(['declaration_customer_approval_date' => date('Y-m-d h:i:s a'), 'declaration_customer_signature' => $request->signature, 'declaration_customer_contact_name' => $request->contact_person]);

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
            $accounts = [];
        }
        $not_captured = [];
        $payment_detail = [];
        $contacts = [];
        $batch_sample_codes = '';
        $report_formats = [];
        $approvers = [];
        $headerDetails = isset($batch->id) ? $batch->report_header_details() : [];
        $ammendments = isset($batch->id) ? getBatchAmmendmentsById($batch->id) : [];
        $allsamples = isset($batch->id) ? $batch->all_samples() : [];

        if (isset($batch->id)) {
            $workflowstages = getWorkflowStage_Stages($batch->status);
            if (in_array($batch->status, ['Sample Verification', 'Sample Approval'])) {
                // Fetch report formats configured specifically for this batch's lab sections
                $labSectionIds = array_filter(explode(',', $batch->lab_section_ids));

                if (!empty($labSectionIds)) {
                    $configuredFormatIds = \App\Models\LabSectionReportConfig::whereIn('sample_analysis_stage_id', $labSectionIds)
                        ->select('report_format_id', 'is_default', 'sample_analysis_stage_id')
                        ->get();

                    if ($configuredFormatIds->count() > 0) {
                        // Get the unique report format IDs
                        $formatIds = $configuredFormatIds->pluck('report_format_id')->unique()->toArray();

                        // Fetch the actual ReportFormat models
                        $report_formats = \App\ReportFormat::whereIn('id', $formatIds)->where('is_active', true)->get();

                        // Inject is_default flag into the formats for the view
                        foreach ($report_formats as $format) {
                            $format->is_default = $configuredFormatIds->where('report_format_id', $format->id)->where('is_default', true)->isNotEmpty();
                        }
                    } else {
                        // Fallback completely to all report formats if no configs exist
                        $report_formats = \App\ReportFormat::active()->get();
                    }
                } else {
                    $report_formats = \App\ReportFormat::active()->get();
                }
            }
            $disposal_date = \Carbon\Carbon::parse($batch->receipt_date)->addMonths(3)->format('Y-m-d');
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
            $not_captured = CapturedResult::where('sample_header_id', $batch->id)->whereNull('result')->join('analysis_elements as ae', function ($join) {
                $join->on('ae.analysis_type_id', '=', 'captured_results.analysis_type_id');
                $join->on('ae.analyte_id', '=', 'captured_results.analyte_id');
            })->where('ae.active', 1)->where('ae.active', 1)->selectRaw('group_concat(analyte_code) as codes,sample_detail_code')->groupBy('sample_detail_id')->get();
            // return response()->json($test);
        }

        $selectedSampleType = \App\SampleType::find($batch->sample_type_id ?? 0) ?? false;
        $selected_analysis_types = isset($batch->sample_type_id) ? $selectedSampleType->analysis_types : [];
        if (isset($batch->id)) {
            if ($batch->is_qc_batch) {
                $standards = Standards::where('status', 1)->where('qc_type_id', $batch->qc_type_id)->get();
            } else {
                $standards = Standards::where('status', 1)->get();
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
            $ammendable = [];
            $attachments = [];
            // return response()->json($ammendable,200);
        }
        $role_a = SystemConfiguration::where('key', 'analyst_role_id')->first();
        $labs = Lab::where('active', 1)->get();
        // $analysts = getUsersByRole('Analyst');
        $analysts = User::orderBy('name')->join('user_roles as ur', 'ur.user_id', '=', 'users.id')
            ->join('roles as r', 'r.id', '=', 'ur.role_id')
            ->where('r.id', $role_a->value)->where('users.active', 1)->where('users.is_support_staff', 0)->selectRaw('users.*')->get();

        // -----------------------------------
        // ---------------------------------------

        $customer = isset($batch->id) ? getCrmCustomerByID($batch->crm_customer_id) : [];
        $requestTypes = getRequestTypes();
        $notifiable_users = getNotifiableUsers();
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
            'captured' => $captured_results,
            'equipments' => $equipments,
            'analysts' => $analysts,
        ];

        return response()->json($res);
    }

    public function methodNameFromId($inputObject, $inputKeysStr)
    {
        $inputKeys = explode(',', $inputKeysStr);
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
                if ((int) $cc > 0) {
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
                'updated_at' => '',
                'created_at' => date('Y-m-d H:s:i.u'),
                'batch_code' => $new_batch_code,
                'receipt_date' => getTodayDate(),
                'status' => 'Samples Reception',
                'c_focus_ids_clustered' => "",
                'cluster_amount' => "",
                'cluster_balance' => "",
                'cluster_vat' => "",
                'cluster_amount_paid' => "",
            ]);
            $new_batch->save();
            $samples = SampleDetails::with('captured_results')
                ->where('sample_header_id', $batch->id)->get();
            $relation_analysis = [];

            foreach ($samples as $sample) {
                // $config_start_no = SystemConfiguration::where('key', 'start_sample_no')->first();
                $sample_data = SamplesCategory::where('id', $sample->id)->first();
                $code = SampleDetails::where('lab_id', $sample_data->main_lab_id)->whereYear('created_at', date('Y'))->orderBy('id', 'DESc')->first()->sample_code;
                $last_sample = substr($code, 9, strlen($code));
                // $last_sample = isset(SampleDetails::latest('id')->first()->id) ? substr(SampleDetails::latest('id')->first()->sample_code,9,strlen(SampleDetails::latest('id')->first()->sample_code) -1) : $config_start_no->value;

                // return response()->json($request->sample_details['lab_id'][$k]);
                $sample_number = intval($last_sample) + 1;
                $new_sample_code = 'S' . date('Y') . $sample_data->main_lab_code . sprintf('%0' . '4' . 'd', $sample_number);

                $new_sample = $sample->replicate()->fill([
                    'sample_code' => $new_sample_code,
                    'sample_header_id' => $new_batch->id,
                    'disposal_date' => \Carbon\Carbon::parse($new_batch->receipt_date)->addMonths(3)->format('Y-m-d'),
                    'sample_no' => sprintf('%0' . '4' . 'd', $sample_number),
                ]);
                $new_sample->save();

                foreach (explode(',', $new_sample->analysis_type_id) as $at_id) {
                    $relation_analysis[] = [
                        'analysis_type_id' => $at_id,
                        'batch_id' => $new_batch->id,
                        'sample_detail_id' => $new_sample->id,
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
            SampleAnalysisTypeRelation::insert($relation_analysis);
            $analysis_types_id = SampleAnalysisTypeRelation::where('batch_id', $new_batch->id)->pluck('analysis_type_id')->toArray();
            $analysis_max_report_time = AnalysisType::whereIn('id', $analysis_types_id)->max('reporting_time');
            $analytes_max_report_time = AnalysisElements::whereIn('analysis_type_id', $analysis_types_id)->max('reporting_time');
            $maxReportingTime = $analysis_max_report_time > $analytes_max_report_time ? $analysis_max_report_time : $analytes_max_report_time;
            $targetDateStr = 'Target Date';
            $targetDate = \App\SampleDate::where('sample_header_id', $new_batch->id)->where('name', $targetDateStr)->first() ?? new \App\SampleDate();
            $targetDate->name = $targetDateStr;
            $targetDate->sample_header_id = $new_batch->id;
            $targetDate->date = \Carbon\Carbon::parse($new_batch->receipt_date)->addDays($maxReportingTime);
            $targetDate->save();

            $custodyDetails = [
                'batch_id' => $new_batch->id,
                'comments' => $request->comments ?? '',
                'current' => [
                    'status' => $new_batch->status,
                    'tracking_stage' => $new_batch->sample_tracking_stage,
                ],
                'target' => [
                    'status' => $new_batch->status,
                    'tracking_stage' => $new_batch->sample_tracking_stage,
                ],
            ];
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
        $value = $request->standard_value_type == 2 && $request->limit_measure != '' ? $request->value . ' ' . $request->limit_measure : $value;
        $format_value = $request->standard_value_type == 2 && $request->limit_measure != '' ? $request->value : $value;

        return response()->json(['format_value' => $value, 'value' => $format_value]);
    }

    public function saveSampleAnalysisDate(Request $request)
    {
        $sample = SampleDetails::where('sample_code', $request->sample_id)->first();
        $analysis_date = SampleAnalysisDates::where('sample_header_id', $request->batch_id)->where('sample_detail_id', $sample->id)->first() ?? new SampleAnalysisDates();
        if (isset($analysis_date->id)) {
            $prev_dates = $analysis_date->analysis_dates != '' ? json_decode($analysis_date->analysis_dates, true) : [];
            // foreach($prev_dates as $key=>$value){
            // 	if($key == )
            // }
            if (isset($prev_dates[$request->lab_section_id])) {
                $prev_dates[$request->lab_section_id] = $request->start_analysis_date;
            } else {
                $prev_dates[$request->lab_section_id] = $request->start_analysis_date;
                // array_push($prev_dates,[$request->lab_section_id=>$request->start_analysis_date]);
            }
            $start_date = '';
            foreach ($prev_dates as $key => $val) {
                if ($start_date == '') {
                    $start_date = $val;
                } else {
                    $start_date = $val > $start_date ? $start_date : $val;
                }
            }
            $analysis_date->start_analysis_date = $start_date;
        } else {
            $prev_dates = [];
            // array_push($prev_dates,[$request->lab_section_id=>$request->start_analysis_date]);
            $prev_dates[$request->lab_section_id] = $request->start_analysis_date;
            $analysis_date->start_analysis_date = $request->start_analysis_date;
        }
        $analysis_date->sample_header_id = $request->batch_id;
        $analysis_date->sample_detail_id = $sample->id;
        // return response()->json($prev_dates);

        $analysis_date->analysis_dates = json_encode($prev_dates);
        $analysis_date->save();

        return response()->json('success');
    }

    public function getSampleIntelabLogsApprovalStatus(Request $request)
    {
        $sample_id = $request->sample_id;
        $sample = SampleDetails::where('sample_code', $sample_id)->first();
        $approval = InterLabLog::where('sample_id', $sample->id)->where('status', 0)->first();

        return response()->json(['approval_status' => isset($approval->id) ? 1 : 0, 'sample' => $sample]);
    }

    public function getSampleResultCapturedNot(Request $request)
    {
        $sample_id = $request->sample_id;
        $sample = SampleDetails::where('sample_code', $sample_id)->first();
        $captured = CapturedResult::where('sample_detail_id', $sample->id)->WhereNotNull('result')->join('analysis_elements as ae', function ($join) {
            $join->on('ae.analysis_type_id', '=', 'captured_results.analysis_type_id');
            $join->on('ae.analyte_id', '=', 'captured_results.analyte_id');
        })->where('ae.active', 1)->where('ae.active', 1)->get()->count();
        $captured_not = CapturedResult::where('sample_detail_id', $sample->id)->WhereNull('result')->join('analysis_elements as ae', function ($join) {
            $join->on('ae.analysis_type_id', '=', 'captured_results.analysis_type_id');
            $join->on('ae.analyte_id', '=', 'captured_results.analyte_id');
        })->where('ae.active', 1)->where('ae.active', 1)->get()->count();

        return response()->json(['captured' => $captured, 'not_captured' => $captured_not]);
    }
    public function addBatchInvoice(Request $request)
    {
        $batch = SampleHeader::find($request->batch_id);
        $batch->invoice_number = $request->invoice_number;
        $batch->invoice_amount = $request->invoice_amount;
        $batch->save();

        return redirect()->back()->with('success', 'Invoice Details added successfully');
    }
    public function markBatchesFinished(Request $request)
    {
        $batches = SampleHeader::whereIn('batch_code', $request->batch_code)->update(['status' => 'Finished Sample']);
        return redirect()->back()->with('success', 'Samples moved to finished samples successfully');
    }
    public function returnFromFinished(Request $request)
    {
        SampleHeader::whereIn('batch_code', $request->batch_code)->update(['status' => 'Sample Approval']);
        return redirect()->back()->with('success', 'Samples moved to Sample Approval successfully');
    }

    public function sendBatchesScheduleAnalysis(Request $request)
    {
        $batch_customers = SampleHeader::whereIn('batch_code', $request->batch_code)->pluck('crm_customer_id')->toArray();
        if (sizeof(array_unique($batch_customers)) > 1) {
            return redirect()->back()->with('error', 'Ensure that the selected batches are for the same client');
        }
        $customer = CrmCustomer::find(array_unique($batch_customers)[0]);
        // return response()->json(array_unique($batch_customers));
        $batch_ids = SampleHeader::whereIn('batch_code', $request->batch_code)->pluck('id')->toArray();
        $sampletypesIds = SampleHeader::whereIn('batch_code', $request->batch_code)->pluck('sample_type_id')->toArray();
        $sampleTypeNames = implode(', ', SampleType::whereIn('id', $sampletypesIds)->pluck('name')->toArray());
        $samples = SampleDetails::whereIn('sample_header_id', $batch_ids)->get();
        $sampleTrs = "";
        foreach ($samples as $sample) {
            $target_date = date('Y-m-d', strtotime($sample->targetDateRelation()));
            $sampleTrs .= '
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($sample->sample_code) . '</td>
                <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars(implode(',', $sample->analyteNames())) . '</td>
                <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($target_date) . '</td>
            </tr>';
        }


        if ($customer->email != '') {
            foreach ($batch_ids as $b_ids) {
                $header = SampleHeader::find($b_ids);
                $header->schedule_sent = 1;
                $header->schedule_analysis_sent = date('Y-m-d');
                $header->schedule_analysis_sender = auth()->user()->id;
                $header->save();
                $schedule_str = 'Schedule Of Analysis Sendoff';
                $schedueDate = \App\SampleDate::where('sample_header_id', $header->id)->where('name', $schedule_str)->first() ?? new
                    \App\SampleDate();
                $schedueDate->name = $schedule_str;
                $schedueDate->sample_header_id = $header->id;
                $schedueDate->date = date('Y-m-d');
                $schedueDate->save();
            }
            $body = '
			<div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
                <p style="font-size: 12px;">
                    Dear ' . $customer->name . ', <br><br>
                    I hope this message finds you well. <br>
                    We are pleased to confirm that your samples <b>' . strtoupper($sampleTypeNames) . '</b> have been successfully received and assigned following Ref IDs: 
                </p>

                <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                    <tr>
                        <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Sample Reference No</th>
                        <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Test(s) Required</th>
                        <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Expected Results Date</th>
                    </tr>
                    ' . $sampleTrs . '
                </table>

                <p style="font-size: 12px; margin-top: 15px;">
                    <br>
                    Once analysis is completed, you will receive an update regarding your test results.<br>
                    For any inquiries, please contact us on <b>Fivet@co.ke, /+12345678 </b>. <br>
                    Thank you for the opportunity to serve you. <br><br>
                    Kind regards, <br>
                    FIVET COMPANY LIMITED
                </p>
            </div>';
            notify_user($body, $customer->email, '[FIVET LIMS] Confirmation of Sample Receipt and Schedule of Analysis', false, true, ['dannyagah13@gmanil.com.com']);

            return redirect()->back()->with('success', 'Schedule of analysis sent successfully!');
        } else {
            return redirect()->back()->with('error', 'Kindly set an email to the specified customer');
        }
    }

    public function disposalReportIndex(Request $request)
    {
        $data = [];
        $filter = [];
        if (isset($request->has_filter)) {
            $filter = [
                'date_from' => $request->date_from,
                "date_to" => $request->date_to,
                "customer_id" => $request->customer_id,
                "sample_type_id" => $request->sample_type_id,
                "store_id" => $request->store_id,
            ];
            $data = SamplesCategory::query();
            if (isset($request->date_from) && $request->date_from != '') {
                $data = $data->where('disposal_date', '>=', $request->date_from);
            }
            if (isset($request->date_to) && $request->date_to != '') {
                $data = $data->where('disposal_date', '<=', $request->date_to);
            }
            if (isset($request->customer_id) && $request->customer_id != '' && $request->customer_id != 'All') {
                $data = $data->where('crm_customer_id', $request->customer_id);
            }
            if (isset($request->sample_type_id) && $request->sample_type_id != '' && $request->sample_type_id != 'All') {
                $data = $data->where('sample_type_id', $request->sample_type_id);
            }
            if (isset($request->store_id) && $request->store_id != '' && $request->store_id != 'All') {
                $data = $data->where('store_id', $request->store_id);
            }
            $data = $data->orderBy('disposal_date', 'DESC')->get();
        }
        $sampletypes = SampleType::where('active', 1)->get();
        $customers = CRMCustomer::where('active', 1)->get();
        $stores = getStorageByType('lab_store');
        return view('layouts.lab.reports.disposal', compact('sampletypes', 'customers', 'stores', 'filter', 'data'));
    }
    public function tatReportIndex(Request $request)
    {
        $data = [];
        $filter = [];
        if (isset($request->has_filter)) {
            $filter = [
                "date_from" => $request->date_from,
                "date_to" => $request->date_to,
                "user_id" => $request->user_id,
                'sample_type_id' => $request->sample_type_id,
                'analysis_type_id' => $request->analysis_type_id,

            ];
            $data = TatCapturedView::query();
            if (isset($request->date_from) && $request->date_from != '') {
                $data = $data->where('receipt_date', '>=', $request->date_from);
            }
            if (isset($request->date_to) && $request->date_to != '') {
                $data = $data->where('receipt_date', '<=', $request->date_to);
            }
            if (isset($request->user_id) && $request->user_id != '' && $request->user_id != 'All') {
                $data = $data->where('analyst_id', $request->user_id);
            }
            if (isset($request->sample_type_id) && $request->sample_type_id != '' && $request->sample_type_id != 'All') {
                $data = $data->where('sample_type_id', $request->sample_type_id);
            }
            if (isset($request->analysis_type_id) && $request->analysis_type_id != '' && $request->analysis_type_id != 'All') {
                $data = $data->where('analysis_type_id', $request->analysis_type_id);
            }
            if (isset($request->analyte_id) && $request->analyte_id != '' && $request->analyte_id != 'All') {
                $data = $data->where('analyte_id', $request->analyte_id);
            }

            $data = $data->where('is_complete', 1)->orderBy('created_at', 'ASC')->get();
        }
        $sampletypes = SampleType::where('active', 1)->get();
        $role_a = SystemConfiguration::where('key', 'analyst_role_id')->first();
        $analysts = User::orderBy('name')->join('user_roles as ur', 'ur.user_id', '=', 'users.id')
            ->join('roles as r', 'r.id', '=', 'ur.role_id')
            ->where('r.id', $role_a->value)->where('users.active', 1)->where('users.is_support_staff', 0)->selectRaw('users.*')->get();
        return view('layouts.lab.reports.tat-report', compact('sampletypes', 'analysts', 'filter', 'data'));
    }
    public function getAnalysisTypeAjax($sampletype)
    {
        $analysis = AnalysisType::where('sample_type_id', $sampletype)->where('active', 1)->get();
        return response()->json($analysis);
    }
    public function getAnalyteAjax($analysistype)
    {
        $analytes = Analyte::where('analysis_type_id', $analysistype)->where('active', 1)->get();
        return response()->json($analytes);
    }

    public function getTatDelayedSample()
    {
        $date = \Carbon\Carbon::now();
        $date->addDays(1);
        $headers = SampleDate::join('sample_headers as s', 's.id', '=', 'sample_dates.sample_header_id')->where('sample_dates.date', '<=', $date)->whereIn('s.status', ["Samples En-Route", "Samples Reception", "Samples Request Review", "Samples In Lab", "Sample Verification", "Sample Approval"])->where('name', 'Target Date')->selectRaw('s.*,sample_dates.date as tat_date,date(sample_dates.date) < date(now()) as is_late,date(sample_dates.date) = date(now()) as is_today')->orderBy('tat_date', 'DESC')->get();
        return response()->json($headers);
    }
    public function awaitingApprovalSamples($status)
    {
        // return response()->json($status);
        $headers = BatchLabSectionApprover::join('sample_headers as s', 's.id', '=', 'batch_labsection_approval.batch_id')->join('users as u', 'u.id', '=', 'batch_labsection_approval.user_id')->where('batch_labsection_approval.status', 0)->where('batch_labsection_approval.batch_status', $status)->where('s.isactive', 1)->selectRaw('s.*,u.name as batch_approver')->get();
        return response()->json($headers);
    }
    public function updateTatCaptured()
    {
        $batches = SampleHeader::whereIn('status', ["Sample Verification", "Sample Approval", "Reports In Payment", "Reports for Collection", "Finished Sample"])->pluck('id')->toArray();
        TatCaptured::whereIn('sample_header_id', $batches)->update(['is_complete' => 1]);
        return response()->json('success');
    }

    public function getTatBatchApprovalCounterAjax($status)
    {
        $approval = $headers = BatchLabSectionApprover::join('sample_headers as s', 's.id', '=', 'batch_labsection_approval.batch_id')->join('users as u', 'u.id', '=', 'batch_labsection_approval.user_id')->where('batch_labsection_approval.status', 0)->where('batch_labsection_approval.batch_status', $status)->selectRaw('s.*,u.name as batch_approver')->get()->count();

        $date = \Carbon\Carbon::now();
        $date->addDays(1);
        $atat_count = SampleDate::join('sample_headers as s', 's.id', '=', 'sample_dates.sample_header_id')->where('sample_dates.date', '<=', $date)->whereIn('s.status', ["Samples En-Route", "Samples Reception", "Samples Request Review", "Samples In Lab", "Sample Verification", "Sample Approval"])->where('name', 'Target Date')->selectRaw('s.*,sample_dates.date as tat_date')->get()->count();
        return response()->json(['tat_count' => $atat_count, 'approval_count' => $approval]);
    }

    public function deleteSalesOrder($id)
    {
        InvoiceDetails::where('invoice_id', $id)->delete();
        SampleHeader::where('invoice_id', $id)->update(['invoice_id' => 0]);
        Invoice::find($id)->delete();
        return response()->json(['status' => "success", "message" => "Sales order deleted successfully!"]);
    }
    public function matchCrmCurrency()
    {
        $customers = CRMCustomer::where('zoho_id', '>', 0)->whereNull('currency_id')->get();
        foreach ($customers as $customer) {
            $zoho = ZohoCustomers::find($customer->zoho_id);
            $z_currency = ModulePreConfigs::where('zoho_id', $zoho->currency_id)->first();
            if (isset($z_currency->id)) {
                $customer->currency_id = $z_currency->id;
                $customer->save();
            }
        }
        return response()->json('success');
    }

    public function updateInvoiceDetails(Request $request)
    {
        foreach ($request->details as $detail) {

            $total = $detail['final_price'] * $detail['quantity'];

            if ($detail['invoice_detail_id'] > 0) {

                InvoiceDetails::find($detail['invoice_detail_id'])->update(['quantity' => $detail['quantity'], 'selling_price' => $detail['unit_price'], "final_unit_price" => $detail['final_price'], 'total' => $total, 'discount' => $detail['discount'], 'discount_type' => $detail['discount_type'], 'analysis_title' => $detail['title']]);
                $invoice_detail = InvoiceDetails::with('invoice')->find($detail['invoice_detail_id']);
            } else {
                $invoice = Invoice::find($detail['invoice_id']);
                $item = InventorySubCategories::where('inventory_sub_categories.id', $detail['item_id'])->leftjoin('zoho_items_pricelist', function ($join) use ($invoice) {
                    $join->on('inventory_sub_categories.id', '=', 'zoho_items_pricelist.item_id');
                    $join->on('zoho_items_pricelist.customer_id', '=', DB::raw($invoice->customer_id));
                })->selectRaw('inventory_sub_categories.name as zoho_name,inventory_sub_categories.unit_price,inventory_sub_categories.zoho_item_code,inventory_sub_categories.id as zoho_analysis_type,zoho_items_pricelist.unit_price as unit_price_rate')->first();

                $invoice_detail = new InvoiceDetails();
                $invoice_detail->invoice_id = $detail['invoice_id'];
                $invoice_detail->analysis_type = $detail['item_id'];
                $invoice_detail->quantity = $detail['quantity'];
                $invoice_detail->selling_price = $detail['unit_price'];
                $invoice_detail->final_unit_price = $detail['final_price'];
                $invoice_detail->discount = $detail['discount'];
                $invoice_detail->discount_type = $detail['discount_type'];
                $invoice_detail->analysis_title = $detail['title'];
                $invoice_detail->total = $total;
                $invoice_detail->crm_customer_id = $invoice->customer_id;
                $invoice_detail->analysis_type_name = $item->zoho_name;
                $invoice_detail->sample_detail_id = 0;
                $invoice_detail->sample_header_id = 0;
                $invoice_detail->cost_price = 0;
                $invoice_detail->zoho_item_id = $item->zoho_item_code;
                $invoice_detail->zoho_item_name = $item->zoho_name;
                $invoice_detail->save();
            }


            $check_pl = ZohoPricelist::where('customer_id', $invoice_detail->invoice->customer_id)->where('item_id', $invoice_detail->analysis_type)->first();
            if (isset($check_pl->id)) {
                $check_pl->unit_price = $detail['unit_price'];
                $check_pl->save();
            } else {
                ZohoPricelist::create(["customer_id" => $invoice_detail->invoice->customer_id, "item_id" => $invoice_detail->analysis_type, 'unit_price' => $detail['unit_price']]);
            }
        }
        return response()->json('done');
    }

    public function getInvoiceItemData($invoice_id, $item_id)
    {
        $invoice = Invoice::find($invoice_id);
        $item = InventorySubCategories::where('inventory_sub_categories.id', $item_id)->leftjoin('zoho_items_pricelist', function ($join) use ($invoice) {
            $join->on('inventory_sub_categories.id', '=', 'zoho_items_pricelist.item_id');
            $join->on('zoho_items_pricelist.customer_id', '=', DB::raw($invoice->customer_id));
        })->selectRaw('inventory_sub_categories.name as zoho_name,inventory_sub_categories.unit_price,inventory_sub_categories.zoho_item_code,inventory_sub_categories.id as zoho_analysis_type,zoho_items_pricelist.unit_price as unit_price_rate')->first();

        return response()->json($item);
    }
    public function validateClientBatches(Request $request)
    {
        $clients = SampleHeader::whereIn('batch_code', $request->batch_code)->pluck('crm_customer_id')->toArray();
        $res = sizeof(array_unique($clients)) > 1 ? ['error' => "Ensure the batches are from 1 client before proceeding"] : ["client_id" => $clients[0]];
        return response()->json($res);
    }
    public function sendScheduleAjax(Request $request)
    {
        $check_customer_email = SampleHeader::whereIn('batch_code', $request->batch_code)->whereNull('schedule_customer_email')->first();
        if ($check_customer_email) {
            return response()->json(['error' => 'Kindly ensure the selected batches have customer email field']);
        }
        $sampleTrs = "";
        $customer_email = '';
        foreach ($request->batch_code as $code) {
            $batch = SampleHeader::where('batch_code', $code)->first();
            $customer_email = $batch->schedule_customer_email;
            if ($batch->schedule_analysis_sent == '') {
                $targetDate = SampleDate::where('sample_header_id', $batch->id)->where('name', 'Target Date')->first();
                $samples = SampleDetails::where('sample_header_id', $batch->id)->pluck('sample_code')->toArray();
                foreach ($samples as $sample) {
                    foreach ($samples as $sample) {
                        $target_date = $targetDate->date;
                        $sampleTrs .= '
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($sample) . '</td>
                        <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($target_date) . '</td>
                    </tr>';
                    }
                }
                $scheduleDateStr = 'Schedule of Analysis Sendoff Date';
                $scheduleDate = SampleDate::where('sample_header_id', $batch->id)->where('name', $scheduleDateStr)->first() ?? new SampleDate();
                $scheduleDate->name = $scheduleDateStr;
                $scheduleDate->sample_header_id = $batch->id;
                $scheduleDate->date = date('Y-m-d');
                $scheduleDate->save();
            }
        }
        $body = '
            <div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
                <p style="font-size: 16px;">
                    Dear Esteemed Client, <br><br>
                    We acknowledge receipt of your sample(s) submitted to our laboratory. The sample(s) have been forwarded to our laboratory, and analysis is scheduled to start anytime from now.<br>Sample Information : 
                </p>

                <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                    <tr>
                        <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Sample Reference No</th>
                        <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Expected Results Date</th>
                    </tr>
                    ' . $sampleTrs . '
                </table>

                <p style="font-size: 16px; margin-top: 15px;">
                    <br>
                    We will keep you updated on the progress report(s).<br>
                    Thank you for the opportunity to serve you.
                </p>
            </div>
            ';
        // return response()->json(['email'=>$customer_email,'body'=>$body,'error'=>'My testing']);
        if ($sampleTrs != "" && $customer_email != '') {
            notify_user($body, $customer_email, '[FIVET LIMS] Schedule Of Analysis ' . implode(',', $request->batch_code), false, true, ['donotreply@FIVET.com']);
            SampleHeader::whereIn('batch_code', $request->batch_code)->update(["schedule_analysis_sent" => date('Y-m-d'), "schedule_analysis_sender" => auth()->user()->id]);
        }
        return response()->json(["customer_email" => $customer_email]);
    }
    public function moveToLabAjax(Request $request)
    {
        $status = 'Samples In Lab';
        foreach ($request->batch_code as $code) {
            $batch = SampleHeader::where('batch_code', $code)->first();
            // if ($batch->schedule_analysis_sent == '' && $batch->schedule_customer_email == '') {
            //     return redirect()->back()->with('error', 'Kindly set the customer email under batch information for batch ' . $code);
            // }
            $previousWorkflow = $batch->status;

            $custodyDetails = [
                'batch_id' => $batch->id,
                'comments' => $request->comments ?? '',
                'current' => [
                    'status' => $batch->status,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
                'target' => [
                    'status' => $status,
                    'tracking_stage' => $batch->sample_tracking_stage,
                ],
            ];

            $this->updateChainofCustody($custodyDetails);
            $batch->status = $status;
            $batch->save();
        }
        return response()->json(['batch_codes' => $request->batch_code, 'process' => 'Complete']);
    }

    public function splitSchoolContacts()
    {
        $contacts = SchoolContacts::all();
        $address_counter = 0;
        foreach ($contacts as $contact) {
            $ad_arr = explode("\n", $contact->address);
            // return response()->json($ad_arr);
            $address_counter = sizeof($ad_arr) > $address_counter ? sizeof($ad_arr) : $address_counter;
            $a_counter = 0;

            $r_address = [];

            foreach ($ad_arr as $ad) {
                if ($ad != '') {
                    $address = explode('-', $ad);
                    $r_address[] = [
                        "address" => $address[0] ?? '',
                        "relation" => $address[1] ?? ''
                    ];
                }
            }
            $contact['refine_address'] = $r_address;
            // return response()->json($contact);
        }
        return view('layouts.lab.sample-workflow.index-other', compact('contacts'));
    }
    private function toScientificNotation($number)
    {
        $exponent = floor(log10(abs($number))); // Get the exponent (power of 10)
        $coefficient = $number / pow(10, $exponent); // Get the coefficient

        // Adjust if coefficient rounds to 10.0
        if (round($coefficient, 1) == 10.0) {
            $coefficient = 1.0;
            $exponent += 1;
        }


        return ['value' => $coefficient, 'to_power' => $exponent, 'scientific' => sprintf("%.1f × 10%s", $coefficient, $this->toSuperscript($exponent))];
    }
    private function toSuperscript($number)
    {
        $superscripts = [
            '0' => '⁰',
            '1' => '¹',
            '2' => '²',
            '3' => '³',
            '4' => '⁴',
            '5' => '⁵',
            '6' => '⁶',
            '7' => '⁷',
            '8' => '⁸',
            '9' => '⁹',
            '-' => '⁻' // Use HTML entity for superscript minus
        ];

        $strNumber = strval($number);
        $superscriptNumber = '';

        foreach (str_split($strNumber) as $digit) {
            $superscriptNumber .= $superscripts[$digit] ?? $digit;
        }

        return $superscriptNumber;
    }
    public function processRawResultsLab(Request $request)
    {
        $captured = CapturedResult::where('sample_header_id', $request->batch_id)->whereNotNull('result')->get();

        foreach ($captured as $c) {
            Result::where('captured_result_id', $c->id)->update([
                "result" => $c->result,
                "remarks" => $c->remark,
                "reporting_symbol" => $c->result_reporting_symbol,
                "seond_guide" => $c->secondary_value,
                'guide' => $c->main_value,
                'unit_code' => $c->reporting_unit,
                'analyte_accredited' => $c->analyte_accredited,
                'analyte_status_contracted' => $c->analyte_status_contracted,
            ]);
        }
        return redirect()->back()->with('success', 'Results processed successfully!');
    }

    public function markQCBatchComplete(Request $request)
    {
        $captured_results = CapturedResult::with('sample')->where('sample_header_id', $request->batch_id)->get();
        QcResults::where('sample_header_id', $request->batch_id)->delete();
        $qc_config_percentage = SystemConfiguration::where('key', 'qc_percentage_config')->first();
        $batch = SampleHeader::with('qctype')->find($request->batch_id);
        $qc_results = [];
        foreach ($captured_results as $c_result) {
            // $analyte_processed = QCProcessedResults::where('analyte_id',$c_result->analyte_id)->where('analysis_type_id',$c_result->analysis_type_id)->where('sample_type_id',$batch->sample_type_id)->where('method_id',$c_result->method_id)->where('standard_id',$c_result->sample->main_standard)->first();
            $analyte_processed = QCProcessedResults::where('analyte_id', $c_result->analyte_id)->where('analysis_type_id', $c_result->analysis_type_id)->where('sample_type_id', $batch->sample_type_id)->where('method_id', $c_result->method_id)->first();
            if (!isset($analyte_processed->id)) {
                $analyte_processed = QCProcessedResults::create([
                    'method_id' => $c_result->method_id,
                    "analyte_id" => $c_result->analyte_id,
                    "analysis_type_id" => $c_result->analysis_type_id,
                    "sample_type_id" => $batch->sample_type_id,
                    // "standard_value_id" => $c_result->main_standard_id,
                    // "standard_id" => $c_result->sample->main_standard
                ]);
            }
            $qc_results[] = [
                "captured_result_id" => $c_result->id,
                "sample_detail_code" => $c_result->sample_detail_code,
                "sample_detail_id" => $c_result->sample_detail_id,
                "sample_header_id" => $c_result->sample_header_id,
                "analyte_id" => $c_result->analyte_id,
                "analyte_code" => $c_result->analyte_code,
                "result" => $c_result->result,
                "analysis_type_id" => $c_result->analysis_type_id,
                "remarks" => $c_result->remark,
                "analyte_status_contracted" => $c_result->analyte_status_contracted,
                "analyte_accredited" => $c_result->analyte_accredited,
                "qc_scheme_id" => $batch->qc_scheme_id,
                "qc_type_id" => $batch->qc_type_id,
                "method_id" => $c_result->method_id,
                "sample_type_id" => $batch->sample_type_id,
                "repeat_captured_id" => $c_result->repeat_captured_id,
                "previous_result" => $c_result->repeatsampleresult,
                "config_percentage" => $qc_config_percentage->value ?? 0,
                // "is_qc_processed" => $batch->qctype->use_existing_sample == 1 ? 1 : 0,
                "is_qc_processed" => 0,
                "analyte_processed_id" => $analyte_processed->id,
            ];
        }
        // return response()->json($qc_results);
        QcResults::insert($qc_results);
        $previousStatus = $batch->status;
        $batch->status = 'Completed';
        $batch->save();

        return redirect()->route('sample-workflow', ['status' => $previousStatus])->with('success', 'QC batch marked as complete successfully. Note to processes the qc results on the qc module to incorporate the new results');
    }

    /**
     * Get available submission forms for the modal
     */
    public function getAvailableSubmissionForms()
    {
        try {
            $forms = \App\Models\SubmissionForm::with(['creator', 'sections'])
                ->where('is_published', true)
                ->where('is_active', true)
                ->withCount(['sections', 'instances'])
                ->orderBy('name')
                ->get()
                ->map(function ($form) {
                    return [
                        'id' => $form->id,
                        'name' => $form->name,
                        'description' => $form->description,
                        'sections_count' => $form->sections_count,
                        'instances_count' => $form->instances_count,
                        'created_at' => $form->created_at->format('M d, Y'),
                        'creator' => $form->creator->name ?? 'Unknown'
                    ];
                });

            return response()->json([
                'success' => true,
                'forms' => $forms
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load submission forms: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new form instance and redirect to fill page
     */
    public function createSubmissionFormInstance(Request $request)
    {
        try {
            $request->validate([
                'submission_form_id' => 'required|exists:submission_forms,id'
            ]);

            $submissionForm = \App\Models\SubmissionForm::findOrFail($request->submission_form_id);

            // Check if form is published and active
            if (!$submissionForm->is_published || !$submissionForm->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected form is not available for submission'
                ], 400);
            }

            // Create form instance
            //$formNumber = \App\Services\FormNumberGenerator::generate($submissionForm);
            $instance = \App\Models\SubmissionFormInstance::create([
                'submission_form_id' => $submissionForm->id,
                'submitted_by' => auth()->id(),
                'status' => 'draft',
                'title' => 'New ' . $submissionForm->name . ' Submission',
                //'form_number' => $formNumber['format'],
                //'sequence_number' => $formNumber['sequence_no'],
                'form_number' => null,
                'sequence_number' => null,
                'due_date' => now()->addDays(7), // Default 7 days from now
                'priority' => 'medium'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Form instance created successfully',
                'redirect_url' => route('submission-forms.instances.fill', [$submissionForm, $instance])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create form instance: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getAvailableMethods()
    {
        try {
            $methods = AnalysisMethod::where('active', 1)->select('id', 'name', 'code')->get();
            return response()->json($methods);
        } catch (\Exception $e) {
            \Log::error('Error loading methods: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load methods: ' . $e->getMessage()], 500);
        }
    }

    public function saveCaptureResults(Request $request)
    {
        try {
            $captureResults = $request->input('capture_results', []);

            if (empty($captureResults)) {
                return response()->json(['error' => 'No results to save'], 400);
            }

            foreach ($captureResults as $resultData) {
                $capturedResult = CapturedResult::find($resultData['parameter_id']);

                if ($capturedResult) {
                    $capturedResult->result = $resultData['result'];
                    $capturedResult->result_reporting_symbol = $resultData['reporting_symbol'] ?? '';

                    // Validate result against standard and set remark
                    $remark = $this->validateResultAgainstStandard(
                        $resultData['result'],
                        $resultData['standard_limit'] ?? '',
                        $capturedResult->analyte_id
                    );

                    $capturedResult->remark = $remark;
                    $capturedResult->save();
                }
            }

            return response()->json(['success' => true, 'message' => 'Results saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save results: ' . $e->getMessage()], 500);
        }
    }

    private function validateResultAgainstStandard($result, $standardLimit, $analyteId)
    {
        // Implement the same validation logic as in the existing system
        // This is a simplified version - you may need to adjust based on your specific validation rules

        if (!is_numeric($result) || !is_numeric($standardLimit)) {
            return 'N/A';
        }

        $resultValue = floatval($result);
        $limitValue = floatval($standardLimit);

        // Simple validation - adjust based on your requirements
        if ($resultValue <= $limitValue) {
            return 'PASS';
        } else {
            return 'FAIL';
        }
    }

    /**
     * Update parameter settings via AJAX
     */
    public function updateParameterSettings(Request $request)
    {
        try {
            $resultId = $request->input('result_id');
            $sampleCode = $request->input('sample_code');
            $analyte = $request->input('analyte');

            // Find or create the captured result
            $capturedResult = null;

            if ($resultId) {
                $capturedResult = CapturedResult::find($resultId);
            } else {
                // Create new captured result if it doesn't exist
                $sample = SampleDetails::where('sample_code', $sampleCode)->first();
                if (!$sample) {
                    return response()->json(['success' => false, 'message' => 'Sample not found'], 404);
                }

                // Find analyte
                $analyteRecord = Analyte::where('code', $analyte)->first();
                if (!$analyteRecord) {
                    return response()->json(['success' => false, 'message' => 'Analyte not found'], 404);
                }

                $capturedResult = new CapturedResult();
                $capturedResult->sample_detail_id = $sample->id;
                $capturedResult->sample_header_id = $sample->sample_header_id;
                $capturedResult->analyte_id = $analyteRecord->id;
                $capturedResult->analysis_type_id = 1; // Default - adjust as needed
                $capturedResult->analyte_code = $analyte;
            }

            // Update the captured result with new settings
            $capturedResult->reporting_unit_id = $request->input('reporting_unit', $capturedResult->reporting_unit_id);
            $capturedResult->method_id = $request->input('method_id', $capturedResult->method_id);
            $capturedResult->result_reporting_symbol = $request->input('reporting_symbol', $capturedResult->result_reporting_symbol);
            $capturedResult->operator_id = $request->input('analyst_id', $capturedResult->operator_id);
            $capturedResult->analyte_accredited = $request->input('accredited', 0);
            $capturedResult->analyte_status_contracted = $request->input('subcontracted', 0);

            $capturedResult->save();

            return response()->json([
                'success' => true,
                'message' => 'Parameter settings updated successfully',
                'result_id' => $capturedResult->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating parameter settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update standard limit via AJAX
     */
    public function updateStandardLimit(Request $request)
    {
        try {
            $resultId = $request->input('result_id');
            $sampleCode = $request->input('sample_code');
            $analyte = $request->input('analyte');
            $standardValue = $request->input('standard_value');
            $limitType = $request->input('limit_type');

            // Find or create the captured result
            $capturedResult = null;

            if ($resultId) {
                $capturedResult = CapturedResult::find($resultId);
            } else {
                // Create new captured result if it doesn't exist
                $sample = SampleDetails::where('sample_code', $sampleCode)->first();
                if (!$sample) {
                    return response()->json(['success' => false, 'message' => 'Sample not found'], 404);
                }

                $analyteRecord = Analyte::where('code', $analyte)->first();
                if (!$analyteRecord) {
                    return response()->json(['success' => false, 'message' => 'Analyte not found'], 404);
                }

                $capturedResult = new CapturedResult();
                $capturedResult->sample_detail_id = $sample->id;
                $capturedResult->sample_header_id = $sample->sample_header_id;
                $capturedResult->analyte_id = $analyteRecord->id;
                $capturedResult->analysis_type_id = 1; // Default - adjust as needed
                $capturedResult->analyte_code = $analyte;
            }

            // Update standard values
            $capturedResult->main_value = $standardValue;
            $capturedResult->standard_limit_value = $limitType;

            $capturedResult->save();

            return response()->json([
                'success' => true,
                'message' => 'Standard limit updated successfully',
                'result_id' => $capturedResult->id,
                'standard_limit' => $standardValue . ' ' . strtolower($limitType)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating standard limit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update result value via AJAX
     */
    public function updateResult(Request $request)
    {
        try {
            $resultId = $request->input('result_id');
            $sampleCode = $request->input('sample_code');
            $analyte = $request->input('analyte');
            $result = $request->input('result');

            // Find or create the captured result
            $capturedResult = null;

            if ($resultId) {
                $capturedResult = CapturedResult::find($resultId);
            } else {
                // Create new captured result if it doesn't exist
                $sample = SampleDetails::where('sample_code', $sampleCode)->first();
                if (!$sample) {
                    return response()->json(['success' => false, 'message' => 'Sample not found'], 404);
                }

                $analyteRecord = Analyte::where('code', $analyte)->first();
                if (!$analyteRecord) {
                    return response()->json(['success' => false, 'message' => 'Analyte not found'], 404);
                }

                $capturedResult = new CapturedResult();
                $capturedResult->sample_detail_id = $sample->id;
                $capturedResult->sample_header_id = $sample->sample_header_id;
                $capturedResult->analyte_id = $analyteRecord->id;
                $capturedResult->analysis_type_id = 1; // Default - adjust as needed
                $capturedResult->analyte_code = $analyte;
            }

            // Update result
            $capturedResult->result = $result;

            // Perform validation against standards if result exists
            $validationResult = null;
            if ($result && $capturedResult->main_value) {
                $validationResult = $this->validateResultAgainstStandard($result, $capturedResult->main_value, $capturedResult->analyte_id);
                $capturedResult->remark = $validationResult;
            }

            $capturedResult->save();

            return response()->json([
                'success' => true,
                'message' => 'Result updated successfully',
                'result_id' => $capturedResult->id,
                'validation_result' => $validationResult,
                'standard_limit' => $capturedResult->main_value ? $capturedResult->main_value . ' ' . strtolower($capturedResult->standard_limit_value) : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating result: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get parameter settings for a result
     */
    public function getParameterSettings($resultId)
    {
        try {
            $capturedResult = CapturedResult::find($resultId);

            if (!$capturedResult) {
                return response()->json(['success' => false, 'message' => 'Result not found'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'reporting_unit' => $capturedResult->reporting_unit_id,
                    'method_id' => $capturedResult->method_id,
                    'reporting_symbol' => $capturedResult->result_reporting_symbol,
                    'analyst_id' => $capturedResult->operator_id,
                    'accredited' => $capturedResult->analyte_accredited,
                    'subcontracted' => $capturedResult->analyte_status_contracted
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading parameter settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get standard settings for a result
     */
    public function getStandardSettings($resultId)
    {
        try {
            $capturedResult = CapturedResult::find($resultId);

            if (!$capturedResult) {
                return response()->json(['success' => false, 'message' => 'Result not found'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'standard_value' => $capturedResult->main_value,
                    'limit_type' => $capturedResult->standard_limit_value
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading standard settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update sample data for multiple samples
     */
    public function bulkUpdateSampleData(Request $request)
    {
        $request->validate([
            'sample_ids' => 'required|array|min:1',
            'sample_ids.*' => 'exists:sample_details,id',
            'batch_id' => 'required|exists:sample_headers,id',
            'main_standard' => 'nullable|exists:standards,id',
            'secondary_standard' => 'nullable|exists:standards,id',
            'store_id' => 'nullable|exists:inventory_stores,id',
            'store_slot_id' => 'nullable|exists:inventory_store_slots,id',
            'disposal_date' => 'nullable|date',
        ]);

        try {
            $sampleIds = $request->input('sample_ids');
            $batchId = $request->input('batch_id');

            // Build update data array - only include non-empty values
            $updateData = [];

            if ($request->filled('main_standard')) {
                $updateData['main_standard'] = $request->input('main_standard');
            }

            if ($request->filled('secondary_standard')) {
                $updateData['secondary_standard'] = $request->input('secondary_standard');
            }

            if ($request->filled('store_id')) {
                $updateData['store_id'] = $request->input('store_id');
            }

            if ($request->filled('store_slot_id')) {
                $updateData['store_slot_id'] = $request->input('store_slot_id');
            }

            if ($request->filled('disposal_date')) {
                $updateData['disposal_date'] = $request->input('disposal_date');
            }

            // Only proceed if there's data to update
            if (empty($updateData)) {
                return redirect()->back()->with('error', 'No fields were provided for update.');
            }

            // Update selected samples
            \App\SampleDetails::whereIn('id', $sampleIds)
                ->where('sample_header_id', $batchId)
                ->update($updateData);

            $updatedCount = count($sampleIds);

            // Recalculate Batch Target Date
            $sampleHeader = \App\SampleHeader::find($batchId);
            if ($sampleHeader) {
                // Get all analysis types for this batch
                $batchAnalysisTypeIds = \App\SampleAnalysisTypeRelation::where('batch_id', $batchId)
                    ->pluck('analysis_type_id')
                    ->unique()
                    ->toArray();

                if (!empty($batchAnalysisTypeIds)) {
                    // Calculate max reporting time
                    $analysisMaxReportingTime = \App\AnalysisType::whereIn('id', $batchAnalysisTypeIds)->max('reporting_time') ?? 0;
                    $elementsMaxReportingTime = \App\AnalysisElements::whereIn('analysis_type_id', $batchAnalysisTypeIds)->max('reporting_time') ?? 0;

                    $maxReportingTime = max($analysisMaxReportingTime, $elementsMaxReportingTime);

                    // Update Target Date
                    $targetDateStr = 'Target Date';
                    $targetDate = \App\SampleDate::where('sample_header_id', $batchId)
                        ->where('name', $targetDateStr)
                        ->first() ?? new \App\SampleDate();

                    $targetDate->name = $targetDateStr;
                    $targetDate->sample_header_id = $batchId;
                    // Use receipt_date or fallback to now
                    $baseDate = $sampleHeader->receipt_date ? \Carbon\Carbon::parse($sampleHeader->receipt_date) : now();
                    $targetDate->date = $baseDate->addDays($maxReportingTime);
                    $targetDate->save();

                    \Log::info('Recalculated Target Date for batch after bulk update', [
                        'batch_id' => $batchId,
                        'max_reporting_time' => $maxReportingTime,
                        'new_target_date' => $targetDate->date
                    ]);
                }
            }

            $updatedFields = implode(', ', array_keys($updateData));

            return redirect()->back()->with('success', "Successfully updated {$updatedCount} sample(s). Updated fields: {$updatedFields}");
        } catch (\Exception $e) {
            \Log::error('Bulk update sample data error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return redirect()->back()->with('error', 'Error updating samples: ' . $e->getMessage());
        }
    }

    public function store_attachment_type(Request $request)
    {
        $request->validate([
            'value' => 'required|string',
        ]);

        if (SystemConfiguration::where('key', 'attachment_type')->where('value', $request->value)->exists()) {
            return response()->json(['success' => false, 'message' => 'Attachment Type already exists']);
        }

        $config_type = SystemConfiguration::where('key', 'attachment_type_config_id')->first();
        if (!isset($config_type->id)) {
            return response()->json(['success' => false, 'message' => 'Attachment Type Config not found']);
        }

        $config = new SystemConfiguration();
        $config->key = 'attachment_type';
        $config->value = $request->value;

        $config->configuration_type_id = $config_type->id;
        $config->save();

        return response()->json([
            'success' => true,
            'id' => $config->id,
            'value' => $config->value
        ]);
    }

    /**
     * Show PDF annotation page
     */
    public function showAnnotationPage($id)
    {
        $attachment = BatchAttachment::findOrFail($id);

        // Verify attachment is a PDF
        if (!str_ends_with(strtolower($attachment->attachment_url), '.pdf')) {
            return redirect()->back()->with('error', 'Only PDF files can be annotated.');
        }

        $batch = \App\SampleHeader::find($attachment->batch_id);

        $user = auth()->user();
        $signatureUrl = null;
        if ($user && $user->electronic_sig) {
            $signatureUrl = $user->electronic_sig;
            if (!filter_var($signatureUrl, FILTER_VALIDATE_URL)) {
                $signatureUrl = asset($signatureUrl);
            }
        }

        return view('layouts.lab.sample-workflow.pdf-annotate', [
            'attachment' => $attachment,
            'batch' => $batch,
            'user' => $user,
            'signatureUrl' => $signatureUrl,
        ]);
    }

    /**
     * Get annotations for a PDF attachment
     */
    public function getAnnotations($id)
    {
        try {
            $annotations = \App\Models\BatchAttachmentAnnotation::where('batch_attachment_id', $id)
                ->orderBy('page_number')
                ->orderBy('created_at')
                ->get();

            return response()->json([
                'success' => true,
                'annotations' => $annotations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load annotations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete selected annotations
     */
    public function deleteAnnotations(Request $request, $id)
    {
        try {
            $request->validate([
                'annotation_ids' => 'required|array',
                'annotation_ids.*' => 'required|integer|exists:batch_attachment_annotations,id'
            ]);

            // Verify attachment exists
            $attachment = BatchAttachment::findOrFail($id);

            // Delete annotations
            $deletedCount = \App\Models\BatchAttachmentAnnotation::where('batch_attachment_id', $id)
                ->whereIn('id', $request->annotation_ids)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} annotation(s).",
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            \Log::error('Annotation deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete annotations: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Handle TinyMCE image uploads for annotations
     */
    public function uploadAnnotationImage(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json(['error' => 'No file uploaded'], 400);
            }

            $file = $request->file('file');

            // Validate file type
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $allowedExtensions)) {
                return response()->json(['error' => 'Invalid file type'], 400);
            }

            // Store the file
            $path = Storage::disk('public')->putFile('annotation-images', $file);

            // Return the full URL for TinyMCE
            $location = url(Storage::url($path));

            return response()->json(['location' => $location]);
        } catch (\Exception $e) {
            Log::error('TinyMCE upload error: ' . $e->getMessage());
            return response()->json(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Save annotated PDF
     */
    public function saveAnnotatedPdf(Request $request)
    {
        try {
            $request->validate([
                'attachment_id' => 'required|exists:batch_attachments,id',
                'annotations_data' => 'required|json',
            ]);

            $attachment = BatchAttachment::findOrFail($request->attachment_id);
            $annotationsData = json_decode($request->annotations_data, true);

            // Resolve file path
            $relativePath = urldecode($attachment->attachment_url);
            $filePath = public_path($relativePath);

            if (!file_exists($filePath)) {
                $cleanPath = ltrim($relativePath, '/');
                if (strpos($cleanPath, 'storage/') === 0) {
                    $storageInternalPath = substr($cleanPath, 8);
                    $fallbackPath = storage_path('app/' . $storageInternalPath);
                    if (file_exists($fallbackPath)) {
                        $filePath = $fallbackPath;
                    }
                }
            }

            if (!file_exists($filePath)) {
                throw new \Exception("Source PDF file not found at: " . $filePath);
            }

            // Backup old PDF
            $backupPath = str_replace('.pdf', '_backup_' . time() . '.pdf', $filePath);
            @copy($filePath, $backupPath);

            // Create new PDF using TcpdfFpdi (preserves quality)
            $pdf = new TcpdfFpdi('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('FIVET LIMS');
            $pdf->SetAuthor(auth()->user()->name);
            $pdf->SetTitle($attachment->title . ' (Annotated)');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false);

            // Import existing PDF as template
            $pageCount = $pdf->setSourceFile($filePath);

            // PDF.js scale 1.5 calculations
            // scale 1.0 = 72 DPI (72 pts per inch)
            // 1 inch = 25.4 mm
            // scale 1.5 means 1.5 * 72 pixels per inch
            $scale = 1.5;
            $ppi = $scale * 72;
            $pxToMm = 25.4 / $ppi;

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
                $pdf->useTemplate($templateId);

                // Filter annotations for this page
                $pageAnnotations = array_filter($annotationsData, function ($ann) use ($pageNo) {
                    return $ann['page_number'] == $pageNo;
                });

                // Draw border-only boxes last so they appear on top.
                $borderOnlyBoxes = [];

                foreach ($pageAnnotations as $ann) {
                    // Convert pixel coordinates to mm
                    $x = $ann['x_position'] * $pxToMm;
                    $y = $ann['y_position'] * $pxToMm;
                    $w = ($ann['width'] ?? 200) * $pxToMm;
                    $h = ($ann['height'] ?? 30) * $pxToMm;

                    if ($ann['annotation_type'] == 'text') {
                        $html = $ann['htmlContent'] ?? ($ann['content'] ?? '');

                        // Issue 2: Add black thin border (1 in writeHTMLCell adds border)
                        // Background is transparent (last arg false)
                        $fontSize = 8;
                        if (isset($ann['style_data']) && is_array($ann['style_data']) && isset($ann['style_data']['fontSize'])) {
                            $fontSize = (int) $ann['style_data']['fontSize'];
                        }
                        $pdf->SetFont('helvetica', '', $fontSize);
                        $pdf->SetFillColor(255, 255, 255);

                        $border = 1;
                        if (isset($ann['style_data']) && is_array($ann['style_data']) && !empty($ann['style_data']['noBorder'])) {
                            $border = 0;
                        }

                        // Special case: draw a solid border box only (used for smart annotation block)
                        if (isset($ann['style_data']) && is_array($ann['style_data']) && !empty($ann['style_data']['borderOnly'])) {
                            $borderOnlyBoxes[] = compact('x', 'y', 'w', 'h');
                            continue;
                        }

                        $pdf->writeHTMLCell($w, $h, $x, $y, $html, $border, 1, false, true, 'L', true);
                    } elseif ($ann['annotation_type'] == 'image') {
                        // Support both legacy 'content' key and newer 'imageData' key
                        $imgData = $ann['imageData'] ?? ($ann['content'] ?? null);
                        if (!$imgData) {
                            continue;
                        }
                        if (str_contains($imgData, 'base64,')) {
                            $imgParts = explode(',', $imgData);
                            $rawData = base64_decode($imgParts[1]);
                            $pdf->Image('@' . $rawData, $x, $y, $w, $h);
                        } else {
                            // If we received a URL/path (e.g. /storage/...png), resolve it to a local file path.
                            $imgPath = $imgData;

                            // Convert absolute URL to path component
                            if (filter_var($imgPath, FILTER_VALIDATE_URL)) {
                                $parsed = parse_url($imgPath);
                                $imgPath = $parsed['path'] ?? $imgPath;
                            }

                            // Try public path first (covers /storage symlink)
                            $localPath = public_path(ltrim($imgPath, '/'));
                            if (!file_exists($localPath) && str_starts_with($imgPath, '/storage/')) {
                                // Fallback to storage/app/public
                                $localPath = storage_path('app/public/' . ltrim(substr($imgPath, strlen('/storage/')), '/'));
                            }

                            if (file_exists($localPath)) {
                                try {
                                    $type = strtoupper((string) pathinfo($localPath, PATHINFO_EXTENSION));
                                    $pdf->Image($localPath, $x, $y, $w, $h, $type ?: null);
                                } catch (\Throwable $e) {
                                    Log::warning('PDF annotation image embed failed', [
                                        'imageData' => $imgData,
                                        'localPath' => $localPath,
                                        'type' => $type ?? null,
                                        'error' => $e->getMessage(),
                                    ]);
                                }
                                continue;
                            }

                            // Additional fallbacks: storage/app/... (non-public) and public_path with decoded URL
                            $decodedPath = urldecode($imgPath);
                            $altLocalPaths = [
                                // Signatures/photos are stored outside "public" in this app
                                str_starts_with($decodedPath, '/storage/personnel-signature/')
                                    ? storage_path('app/personnel-signature/' . ltrim(substr($decodedPath, strlen('/storage/personnel-signature/')), '/'))
                                    : null,
                                str_starts_with($decodedPath, '/storage/personnel/')
                                    ? storage_path('app/personnel/' . ltrim(substr($decodedPath, strlen('/storage/personnel/')), '/'))
                                    : null,
                                storage_path('app/' . ltrim($decodedPath, '/')),
                                public_path(ltrim($decodedPath, '/')),
                            ];
                            $altLocalPaths = array_values(array_filter($altLocalPaths));

                            foreach ($altLocalPaths as $altPath) {
                                if (file_exists($altPath)) {
                                    try {
                                        $type = strtoupper((string) pathinfo($altPath, PATHINFO_EXTENSION));
                                        $pdf->Image($altPath, $x, $y, $w, $h, $type ?: null);
                                    } catch (\Throwable $e) {
                                        Log::warning('PDF annotation image embed failed', [
                                            'imageData' => $imgData,
                                            'localPath' => $altPath,
                                            'type' => $type ?? null,
                                            'error' => $e->getMessage(),
                                        ]);
                                    }
                                    continue 2;
                                }
                            }

                            // Final fallback: if it's a URL, fetch bytes and embed directly.
                            if (filter_var($imgData, FILTER_VALIDATE_URL)) {
                                try {
                                    $raw = @file_get_contents($imgData);
                                    if ($raw !== false && $raw !== '') {
                                        $pdf->Image('@' . $raw, $x, $y, $w, $h);
                                        continue;
                                    }
                                } catch (\Throwable $e) {
                                    // ignore and log below
                                }
                            }

                            Log::warning('PDF annotation image not found', [
                                'imageData' => $imgData,
                                'resolved_path' => $localPath,
                                'alt_paths' => $altLocalPaths ?? [],
                            ]);
                        }
                    }
                }

                if (!empty($borderOnlyBoxes)) {
                    // Faint thin blue border
                    $pdf->SetDrawColor(110, 125, 200);
                    $pdf->SetLineWidth(0.1);
                    foreach ($borderOnlyBoxes as $box) {
                        $pdf->Rect($box['x'], $box['y'], $box['w'], $box['h']);
                    }
                }
            }

            // Save new PDF (overwriting original)
            $pdf->Output($filePath, 'F');

            // Clear annotations from database. 
            // Since they are now "baked" into the PDF, we clear the DB records 
            // to prevent overlapping renderings in the annotator tool.
            BatchAttachmentAnnotation::where('batch_attachment_id', $attachment->id)->delete();

            return redirect()->back()->with('success', 'PDF annotated and saved successfully!');
        } catch (\Exception $e) {
            Log::error('PDF annotation save error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to save annotated PDF: ' . $e->getMessage());
        }
    }


    public function updateStagingDetail(Request $request, $id)
    {
        $staging = \App\Models\SampleDetailStaging::find($id);
        if (!$staging) {
            return redirect()->back()->with('error', 'Staging record not found.');
        }

        $data = $staging->data_json;
        $data['company_sub_unit_name'] = $request->company_sub_unit_name;
        $data['analysis_type_names'] = $request->analysis_type_names;
        $data['quantity'] = $request->quantity;

        $staging->data_json = $data;
        $staging->save();

        return redirect()->back()->with('success', 'Staging detail updated successfully.');
    }

    public function deleteStagingDetail($id)
    {
        $staging = \App\Models\SampleDetailStaging::find($id);
        if ($staging) {
            $staging->delete();
            return redirect()->back()->with('success', 'Staging detail deleted successfully.');
        }
        return redirect()->back()->with('error', 'Staging record not found.');
    }
}

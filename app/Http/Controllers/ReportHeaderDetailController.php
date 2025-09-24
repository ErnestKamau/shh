<?php

namespace App\Http\Controllers;

use App\Http\Controllers\SampleWorkFlowController as SF;
use App\SampleDetails;
use App\ReportHeaderDetail;
use App\Models\CRM\CRMCustomer;
use App\Models\System\SystemConfiguration;
use App\SampleHeader;
use Illuminate\Http\Request;
use App\SampleResults;
use App\SampleType;
use App\StandardAnalytes;
use App\Standards;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use App\SampleAnalysisTypeRelationView;
use App\AnalysisType;
use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use PhpParser\PrettyPrinter\Standard;
use setasign\Fpdi\Fpdi;
use App\Company;
use App\CapturedResult;
use App\SamplesCategory;
use App\BatchLabSectionApprover;
use App\User;
use App\SampleAnalysisDates;
use App\BatchAmmendment;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class ReportHeaderDetailController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function sample_interpretations(Request $request, $sample_id)
	{
		$detail = SampleDetails::find($sample_id);

		$detail->main_body = $request->main_body;
		$detail->header_body = $request->header_body;
		$detail->notes_body = $request->notes_body;
		$detail->save();

		return \redirect()->back()->with('success', 'Sample Comments and Interpretations have been saved');
	}

	public function report_interpretations(Request $request, $batch_id)
	{
		$detailType = array("App\SampleHeader", "App\CRMCustomer");
		$batch = \App\SampleHeader::find($batch_id);

		$batch->declared_amount = $request->declared_amount;
		$batch->final_declared_amount = $request->final_declared_amount;

		$batch->save();

		$approvedBy = \App\ChainOfCustody::join('users as u', 'u.id', '=', 'chain_of_custodies.moved_out_by')
			->where('sample_header_id', $batch->id)
			->selectRaw('u.id')
			->where('workflow_stage', "Sample Approval")->orderBy('chain_of_custodies.created_at', 'desc')->first();

		$verifiedBy = \App\ChainOfCustody::join('users as u', 'u.id', '=', 'chain_of_custodies.moved_out_by')
			->where('sample_header_id', $batch->id)
			->selectRaw('u.id')
			->where('workflow_stage', "Sample Verification")->orderBy('chain_of_custodies.created_at', 'desc')->first();

		$Detail = ReportHeaderDetail::where('model', $detailType[0])->where('model_id', $batch_id)->first() ??
			new ReportHeaderDetail;
		$Detail->model = $detailType[0];
		$Detail->model_id = $batch_id;
		$Detail->sample_header_id = $batch_id;
		$Detail->specific_analyst_id = $batch->specialist_analyst_id;
		$Detail->approved_by_id = $approvedBy->id ?? 0;
		$Detail->verified_by_id = $verifiedBy->id ?? 0;
		$Detail->title = $request->report_title;
		$Detail->to = $request->report_to;
		$Detail->cc = $request->report_cc;
		$Detail->from = $request->report_from;
		$Detail->date = $request->report_date;
		$Detail->ref = $batch->reference_number;
		$Detail->re = $request->report_re;
		$Detail->for = $request->report_for;
		// $Detail->header_body = $request->header_body;
		// $Detail->main_body = $request->main_body;
		$Detail->outgoing_email_body = $request->outgoing_email_body;
		$Detail->save();

		if ($request->has('update_client_headers') && $request->update_client_headers == "1") {
			$clientDetail = $Detail->replicate();
			$clientDetail->save();

			$clientDetail->model = $detailType[1];
			$clientDetail->model_id = $batch->crm_customer_id;
			$clientDetail->save();
		}

		$sampleWorkflow = new SF;
		$processResults = $sampleWorkflow->process_raw_results($request, $batch->id, true);

		$customer = CRMCustomer::find($batch->crm_customer_id);

		$report_params['VAR_BATCH_ID'] = $batch->id;
		$report_params['report_type'] = 'analysis_report_kra';
		$report_params['report_name'] = 'analysis_report_kra';
		$report_params['client_name'] = $customer->name;
		$report_params['client_code'] = $customer->code;
		$report_params['batch_code'] = $batch->batch_code;
		$report_params['report_date'] = date("d-M-Y", strtotime($request->report_date));

		// return response()->json($report_params, 200);

		$report_generator = new ReportGeneratorController;

		$report_generator->index($report_params);

		// relative path
		// $batch->report_path = 'report file name';
		// $batch->save();

		return redirect()->back()->with('success', 'Report .processed successfully.');
	}

	public function process_pdf_report($batch_id, $report_format, $include_pesticide = 0)
	{
		// Load logos
		$report_logo = public_path('images/logo-report.png');
		$sadc_logo = public_path('images/sadcas_logo.png');
		$ilac_logo = public_path('images/ilac-logo.png');
		$stamp = public_path('images/company_logo.png'); // Using company logo as stamp placeholder

		$batch = SampleHeader::with(['customer'])->find($batch_id);
		$batch->processing_date = getTodayDate();
		$batch->in_ammendment_proccess = 0;
		$batch->save();
		
		$ammendment = BatchAmmendment::where('batch_id', $batch->id)->orderBy('id', 'DESC')->first();
		$report_type = '';
		$report_type = $batch->prelim_report_status == 1 ? 'PRELIM' : $report_type;
		$report_type = $batch->prelim_report_status == 2 ? 'DRAFT' : $report_type;

		$batch_approvers = BatchLabSectionApprover::where('batch_id', $batch->id)->where('show_report', 1)->where('status', 1)->get();
		$is_stamp = BatchLabSectionApprover::where('batch_id', $batch->id)->where('show_report', 1)->where('status', 1)->where('batch_status', 'Sample Approval')->first();
		$analysis_date = SampleAnalysisDates::where('sample_header_id', $batch->id)->orderBy('start_analysis_date', 'DESC')->first();

		$disclaimer = 'The report shall not be reproduced except in full without approval of the laboratory. The information supplied by the customer can affect the validity of results. The results relate only to the items tested. The results apply to the sample as received. Opinions, interpretations and comments herein are not covered within the scope of accreditation.';
		$status = $batch->status;

		$customer = $batch->customer;
		$company = getActiveCompany();
		$date = date("d-M-Y", strtotime(getTodayDate()));

		$customer_name = preg_replace('/[^A-Za-z0-9]/', '', $customer->name);
		$batch_code = preg_replace('/[^A-Za-z0-9]/', '', $batch->batch_code);
		
		if ($batch->document_number != '') {
			$filename = $customer_name . '-' . $batch_code . '-' . date("d-M-Y-H-i-s") . '-' . $batch->document_number . '.pdf';
		} else {
			$filename = $customer_name . '-' . $batch_code . '-' . date("d-M-Y-H-i-s") . '.pdf';
		}
		$filename = urlencode($filename);

		$qr_url = url('/storage/reports/' . $customer_name . '/' . $filename);
		$qrcode = base64_encode(QrCode::format('svg')->size(50)->errorCorrection('H')->generate($qr_url));

		$tempFiles = [];

		if ($report_format == 0) {
			// Aspergillus Report format (MB 821/25-3) - Optimized
			
			// Eager load samples with their sample point area relationship
			$samples = SamplesCategory::where('sample_header_id', $batch->id)
				->with('samplePointArea')
				->get();
			
			// Get all captured results in one query with joins for efficiency
			$allCapturedResults = CapturedResult::where('captured_results.sample_header_id', $batch->id)
				->join('sample_details as sd', 'sd.id', '=', 'captured_results.sample_detail_id')
				->leftJoin('analysis_methods as am', 'am.id', '=', 'captured_results.method_id')
				->select(
					'captured_results.*',
					'sd.main_standard',
					'am.name as method_name',
					'am.code as method_code'
				)
				->get();
			
			$allCapturedResultsCount = $allCapturedResults->count();
			$isAccreditedCount = $allCapturedResults->where('analyte_accredited', 1)->count();
			
			// Extract unique parameters efficiently
			$parameters = $allCapturedResults->unique(function ($item) {
				return $item->analyte_id;
			})->map(function ($result) {
				return (object) [
					'id' => $result->id,
					'analyte_id' => $result->analyte_id,
					'analyte_code' => $result->analyte_code,
					'analyte_name' => $result->analyte_code, // You may want to add analyte_name to the query
					'reporting_unit_id' => $result->reporting_unit_id,
					'main_value' => $result->main_value,
					'main_standard' => $result->main_standard,
					'method_id' => $result->method_id,
					'method_name' => $result->method_name,
					'method_code' => $result->method_code
				];
			})->sortBy('analyte_code')->values();
			
			// Pre-calculate standards
			$standards = $parameters->filter(function ($param) {
				return $param->main_value && $param->main_value !== 'NS';
			})->pluck('main_value', 'analyte_code')->toArray();
			
			// Pre-calculate analyte names and methods for method section
			$analyteNames = $parameters->pluck('analyte_code')->unique()->implode(', ') ?: 'Aspergillus Analysis';
			$methodNames = $parameters->pluck('method_name')->filter()->unique()->implode(', ') ?: 
						   $parameters->pluck('method_code')->filter()->unique()->implode(', ') ?: 'SOP MB 12';
			
			// Group results by sample for efficient lookup
			$resultsBySample = $allCapturedResults->groupBy('sample_detail_id');
			
			// Pre-calculate intensity values using a lookup function
			$calculateIntensity = function($value) {
				if (stripos($value, '+++') !== false || (is_numeric($value) && floatval($value) > 100)) {
					return '+++';
				} elseif (stripos($value, '++') !== false || (is_numeric($value) && floatval($value) > 50)) {
					return '++';
				} elseif (stripos($value, '+') !== false || (is_numeric($value) && floatval($value) > 0)) {
					return '+';
				}
				return '';
			};
			
			// Process samples with optimized loops
			$samplesData = $samples->map(function ($sample) use ($parameters, $resultsBySample, $calculateIntensity) {
				$sampleResults = [];
				$samplePasses = 0;
				$sampleTotal = 0;
				$intensity = '';
				
				$sampleCapturedResults = $resultsBySample->get($sample->id, collect());
				$resultsByAnalyte = $sampleCapturedResults->keyBy('analyte_id');
				
				foreach ($parameters as $parameter) {
					$result = $resultsByAnalyte->get($parameter->analyte_id);
					
					if ($result) {
						$value = $result->result;
						$remark = $result->remark ?? 'Pass';
						
						$sampleResults[$parameter->analyte_code] = [
							'value' => $value,
							'unit' => $result->reporting_unit_id,
							'remark' => $remark
						];
						
						$sampleTotal++;
						if (strtolower($remark) === 'pass') {
							$samplePasses++;
						}
						
						// Calculate intensity once per sample (not per parameter)
						if (!$intensity) {
							$intensity = $calculateIntensity($value);
						}
					} else {
						$sampleResults[$parameter->analyte_code] = [
							'value' => 'N/A',
							'unit' => '',
							'remark' => 'N/A'
						];
					}
				}
				
				$conformity = ($sampleTotal > 0 && $samplePasses === $sampleTotal) ? 'Pass' : 'Fail';
				
				return [
					'sample' => $sample,
					'results' => $sampleResults,
					'conformity' => $conformity,
					'intensity' => $intensity
				];
			});
			
			// Group samples by sample point area efficiently
			$groupedSamples = $samplesData->filter(function ($sampleData) {
				return isset($sampleData['sample']->samplePointArea) && $sampleData['sample']->samplePointArea;
			})->groupBy(function ($sampleData) {
				return $sampleData['sample']->samplePointArea->name;
			});
			
			$ungroupedSamples = $samplesData->filter(function ($sampleData) {
				return !isset($sampleData['sample']->samplePointArea) || !$sampleData['sample']->samplePointArea;
			});

			$data = [
				'batch' => $batch,
				'customer' => $customer,
				'company' => $company,
				'samples' => $samplesData,
				'parameters' => $parameters,
				'standards' => $standards,
				'grouped_samples' => $groupedSamples,
				'ungrouped_samples' => $ungroupedSamples,
				'analyte_names' => $analyteNames,
				'method_names' => $methodNames,
				'batch_approvers' => $batch_approvers,
				'analysis_date' => $analysis_date,
				'report_type' => $report_type,
				'ammendment' => $ammendment,
				'disclaimer' => $disclaimer,
				'qrcode' => $qrcode,
				'report_logo' => $report_logo,
				'sadc_logo' => $sadc_logo,
				'ilac_logo' => $ilac_logo,
				'stamp' => $stamp,
				'is_stamp' => $is_stamp,
				'date' => $date
			];

			ini_set('max_execution_time', 300); //300 seconds = 5 minutes 
			$pdf = app('dompdf.wrapper');
			$pdf->getDomPDF()->set_option("enable_php", true);
			$pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
			$pdf->getDomPDF()->set_option("isFontSubsettingEnabled", true);

			$pdf = PDF::loadView('layouts.lab.reports.coa_formats.aspergillus_report', $data);
			$tempFile = storage_path() . '/app/reports/'.$customer_name . '/' . $filename;
			
			if (!is_dir(storage_path() . '/app/reports/'.$customer_name)) {
				$path = storage_path() . '/app/reports/'.$customer_name;
				mkdir($path, 0755, true);
			}
			
			$pdf->save(storage_path() . '/app/reports/'.$customer_name . '/' . $filename);
			$tempFiles[] = $tempFile;

			$batch->batch_report_url = '/reports/' . $customer_name . '/' . $filename;
			$batch->save();
			
			return $pdf->stream($filename);
		}

		if ($report_format == 1) {
			// MB826/25 Microbiology Laboratory Report format
			$samples = SamplesCategory::where('sample_header_id', $batch->id)
				->with('samplePointArea') // Load the sample point area relationship if it exists
				->get();

			$standard_codes = implode(',', $samples->pluck('main_standard_code')->unique()->toArray());	
			
			// Get all captured results for this batch
			$allCapturedResults = CapturedResult::where('sample_header_id', $batch->id)->get();
			$allCapturedResultsCount = $allCapturedResults->count();
			$isAccreditedCount = CapturedResult::where('sample_header_id', $batch->id)->where('analyte_accredited', 1)->count();
			
			// Get unique parameters (analytes) for dynamic column headers
			$parameters = CapturedResult::where('captured_results.sample_header_id', $batch->id)
				->select('captured_results.id', 'captured_results.analyte_id', 'captured_results.analyte_code', 'captured_results.reporting_unit_id', 'captured_results.main_value','sd.main_standard', 'captured_results.method_id', 'am.name as method_name', 'am.code as method_code')
				->join('sample_details as sd', 'sd.id', '=', 'captured_results.sample_detail_id')
				->leftJoin('analysis_methods as am', 'am.id', '=', 'captured_results.method_id')
				->distinct()
				->orderBy('analyte_code')
				->get();

			// Build standards array with optimized logic
			$standards = [];
			foreach ($parameters as $parameter) {
				$standardValue = $this->getParameterStandardValue($parameter);
				if ($standardValue !== 'NS') {
					$standards[$parameter->analyte_code] = $standardValue;
				}
			}

			// Get all results in a single optimized query
			$allResults = CapturedResult::where('captured_results.sample_header_id', $batch->id)
				->select('captured_results.*')
				->get()
				->groupBy('sample_detail_id');

			// Process samples with grouped results and sample point areas
			$groupedSamples = [];
			$ungroupedSamples = [];
			
			foreach ($samples as $sample) {
				$sampleResults = [];
				$samplePasses = 0;
				$sampleTotal = 0;
				
				// Get all results for this sample
				$sampleResultsData = $allResults->get($sample->id, collect());
				$resultsByAnalyte = $sampleResultsData->keyBy('analyte_id');
				
				foreach ($parameters as $parameter) {
					$result = $resultsByAnalyte->get($parameter->analyte_id);
					
					if ($result) {
						$sampleResults[$parameter->analyte_code] = [
							'value' => $result->result,
							'unit' => $result->reporting_unit_id,
							'remark' => $result->remark ?? 'Pass'
						];
						
						$sampleTotal++;
						if (strtolower($result->remark ?? 'pass') === 'pass') {
							$samplePasses++;
						}
					} else {
						$sampleResults[$parameter->analyte_code] = [
							'value' => 'N/A',
							'unit' => '',
							'remark' => 'N/A'
						];
					}
				}
				
				// Determine overall conformity for the sample
				$conformity = ($sampleTotal > 0 && $samplePasses === $sampleTotal) ? 'Pass' : 'Fail';
				
				$sampleData = [
					'sample' => $sample,
					'results' => $sampleResults,
					'conformity' => $conformity
				];
				
				// Group by sample point area
				$areaId = $sample->sample_point_area_id ?? null;
				if ($areaId) {
					$areaName = $sample->samplePointArea->name ?? 'Area ' . $areaId;
					if (!isset($groupedSamples[$areaName])) {
						$groupedSamples[$areaName] = [];
					}
					$groupedSamples[$areaName][] = $sampleData;
				} else {
					$ungroupedSamples[] = $sampleData;
				}
			}

			// Get sample type name for the batch
			$sampleTypeName = 'Water Sample'; // Default
			if ($samples->count() > 0) {
				$firstSample = $samples->first();
				if (isset($firstSample->sample_type_name)) {
					$sampleTypeName = $firstSample->sample_type_name;
				} else {
					// Try to get from sample header relationship
					$sampleType = \App\SampleType::find($batch->sample_type_id);
					if ($sampleType) {
						$sampleTypeName = $sampleType->name;
					}
				}
			}

			$data = [
				'batch' => $batch,
				'customer' => $customer,
				'company' => $company,
				'grouped_samples' => $groupedSamples,
				'ungrouped_samples' => $ungroupedSamples,
				'parameters' => $parameters,
				'batch_approvers' => $batch_approvers,
				'analysis_date' => $analysis_date,
				'report_type' => $report_type,
				'ammendment' => $ammendment,
				'disclaimer' => $disclaimer,
				'qrcode' => $qrcode,
				'report_logo' => $report_logo,
				'sadc_logo' => $sadc_logo,
				'ilac_logo' => $ilac_logo,
				'stamp' => $stamp,
				'is_stamp' => $is_stamp,
				'date' => $date,
				'standards' => $standards,
				'standard_codes' => $standard_codes,
				'sample_type_name' => $sampleTypeName
			];

			ini_set('max_execution_time', 300); //300 seconds = 5 minutes 
			$pdf = app('dompdf.wrapper');
			$pdf->getDomPDF()->set_option("enable_php", true);
			$pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
			$pdf->getDomPDF()->set_option("isFontSubsettingEnabled", true);

			$pdf = PDF::loadView('layouts.lab.reports.coa_formats.microbiology_report', $data);
			$tempFile = storage_path() . '/app/reports/'.$customer_name . '/' . $filename;
			
			if (!is_dir(storage_path() . '/app/reports/'.$customer_name)) {
				$path = storage_path() . '/app/reports/'.$customer_name;
				mkdir($path, 0755, true);
			}
			
			$pdf->save(storage_path() . '/app/reports/'.$customer_name . '/' . $filename);
			$tempFiles[] = $tempFile;

			$batch->batch_report_url = '/reports/' . $customer_name . '/' . $filename;
			$batch->save();
			
			return $pdf->stream($filename);
		}

		if ($report_format == 2) {
			// Hygiene Swabs Report format (MB 756/25-2)
			$samples = SamplesCategory::where('sample_header_id', $batch->id)
				->with('samplePointArea') // Load the sample point area relationship if it exists
				->get();

			$standard_codes = implode(',', $samples->pluck('main_standard_code')->unique()->toArray());	
			
			// Get all captured results for this batch
			$allCapturedResults = CapturedResult::where('sample_header_id', $batch->id)->get();
			$allCapturedResultsCount = $allCapturedResults->count();
			$isAccreditedCount = CapturedResult::where('sample_header_id', $batch->id)->where('analyte_accredited', 1)->count();
			
			// Get unique parameters (analytes) for dynamic column headers
			$parameters = CapturedResult::where('captured_results.sample_header_id', $batch->id)
				->select('captured_results.id', 'captured_results.analyte_id', 'captured_results.analyte_code', 'captured_results.reporting_unit_id', 'captured_results.main_value','sd.main_standard', 'captured_results.method_id', 'am.name as method_name', 'am.code as method_code')
				->join('sample_details as sd', 'sd.id', '=', 'captured_results.sample_detail_id')
				->leftJoin('analysis_methods as am', 'am.id', '=', 'captured_results.method_id')
				->distinct()
				->orderBy('analyte_code')
				->get();

			$standards = [];
			foreach ($parameters as $parameter) {
				if ($parameter->main_value && $parameter->main_value !== 'NS') {
					$standards[$parameter->analyte_code] =  (getStandardLimitValue($parameter->id, $parameter->main_standard,1) ?? '').
					 ($parameter->main_value == 'NS' ? '--' : ($parameter->main_value ?? '')).' '.
					 (getStandardLimitValue($parameter->id, $parameter->main_standard) ?? '');
				}
			}

			// Prepare samples with their results for each parameter
			$samplesData = [];
			foreach ($samples as $sample) {
				$sampleResults = [];
				$samplePasses = 0;
				$sampleTotal = 0;
				
				foreach ($parameters as $parameter) {
					$result = CapturedResult::where('sample_header_id', $batch->id)
						->where('sample_detail_id', $sample->id)
						->where('analyte_id', $parameter->analyte_id)
						->first();
					
					if ($result) {
						$sampleResults[$parameter->analyte_code] = [
							'value' => $result->result,
							'unit' => $result->reporting_unit_id,
							'remark' => $result->remark ?? 'Pass'
						];
						
						$sampleTotal++;
						if (strtolower($result->remark ?? 'pass') === 'pass') {
							$samplePasses++;
						}
					} else {
						$sampleResults[$parameter->analyte_code] = [
							'value' => 'N/A',
							'unit' => '',
							'remark' => 'N/A'
						];
					}
				}
				
				// Determine overall conformity for the sample
				$conformity = ($sampleTotal > 0 && $samplePasses === $sampleTotal) ? 'Pass' : 'Fail';
				
				$samplesData[] = [
					'sample' => $sample,
					'results' => $sampleResults,
					'conformity' => $conformity
				];
			}

			// Get sample type name for the batch
			$sampleTypeName = 'Hygiene Swabs'; // Default
			if ($samples->count() > 0) {
				$firstSample = $samples->first();
				if (isset($firstSample->sample_type_name)) {
					$sampleTypeName = $firstSample->sample_type_name;
				} else {
					// Try to get from sample header relationship
					$sampleType = \App\SampleType::find($batch->sample_type_id);
					if ($sampleType) {
						$sampleTypeName = $sampleType->name;
					}
				}
			}

			$data = [
				'batch' => $batch,
				'customer' => $customer,
				'company' => $company,
				'samples' => $samplesData,
				'parameters' => $parameters,
				'batch_approvers' => $batch_approvers,
				'analysis_date' => $analysis_date,
				'report_type' => $report_type,
				'ammendment' => $ammendment,
				'disclaimer' => $disclaimer,
				'qrcode' => $qrcode,
				'report_logo' => $report_logo,
				'sadc_logo' => $sadc_logo,
				'ilac_logo' => $ilac_logo,
				'stamp' => $stamp,
				'is_stamp' => $is_stamp,
				'date' => $date,
				'standards' => $standards,
				'standard_codes' => $standard_codes,
				'sample_type_name' => $sampleTypeName
			];

			ini_set('max_execution_time', 300); //300 seconds = 5 minutes 
			$pdf = app('dompdf.wrapper');
			$pdf->getDomPDF()->set_option("enable_php", true);
			$pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
			$pdf->getDomPDF()->set_option("isFontSubsettingEnabled", true);

			$pdf = PDF::loadView('layouts.lab.reports.coa_formats.hygiene_swabs_report', $data);
			$tempFile = storage_path() . '/app/reports/'.$customer_name . '/' . $filename;
			
			if (!is_dir(storage_path() . '/app/reports/'.$customer_name)) {
				$path = storage_path() . '/app/reports/'.$customer_name;
				mkdir($path, 0755, true);
			}
			
			$pdf->save(storage_path() . '/app/reports/'.$customer_name . '/' . $filename);
			$tempFiles[] = $tempFile;

			$batch->batch_report_url = '/reports/' . $customer_name . '/' . $filename;
			$batch->save();
			
			return $pdf->stream($filename);
		}
		
		if($report_format == 'water_report'){
			$allCapturedResultsCount = CapturedResult::where('sample_header_id', $batch->id)->get()->count();
			$isAccreditedCount = CapturedResult::where('sample_header_id', $batch->id)->where('analyte_accredited', 1)->get()->count();
			$sample['is_accreddited_status'] = $isAccreditedCount >= $allCapturedResultsCount / 2 ? 1 : 0;
			$idArrs = array_unique(CapturedResult::where('sample_header_id', $batch->id)->pluck('lab_section_id')->toArray());
			array_push($idArrs, 0);
			$sample['lab_sect_ids_arr'] = $idArrs;

			ini_set('max_execution_time', 300); //300 seconds = 5 minutes 
			$pdf = app('dompdf.wrapper');
			$pdf->getDomPDF()->set_option("enable_php", true);
			$pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
			$pdf->getDomPDF()->set_option("isFontSubsettingEnabled", true);

			$pdf = PDF::loadView('layouts.lab.reports.coa_formats.report_formats', compact('sample', 'company', 'qrcode', 'report_logo', 'sadc_logo', 'ilac_logo', 'batch_approvers', 'pdf', 'batch', 'disclaimer', 'customer', 'report_type', 'analysis_date', 'stamp', 'is_stamp', 'ammendment'));
			$tempFile = storage_path() . '/app/reports/'.$customer_name . '/' . $filename;

			if (!is_dir(storage_path() . '/app/reports/'.$customer_name)) {
				$path = storage_path() . '/app/reports/'.$customer_name;
				mkdir($path, 0755, true);
			}
			
			$pdf->save(storage_path() . '/app/reports/'.$customer_name . '/' . $filename);
			$tempFiles[] = $tempFile;
		}

		$batch->batch_report_url = '/reports/' . $customer_name . '/' . $filename;
		$batch->save();
		
		return response()->json(['success' => true, 'message' => 'PDF successfully saved to FTP!']);
	}
	public function moveFTP($ftpPath, $localPath)
	{
		// Upload to FTP
		if (Storage::disk('ftp')->put($ftpPath, file_get_contents($localPath))) {
			return redirect()->back()->with('success', 'PDF successfully saved to FTP!');
		} else {
			return redirect()->back()->with('error', 'Failed to upload PDF to FTP server');
		}
	}

	/**
	 * Get optimized standard value for a parameter
	 */
	private function getParameterStandardValue($parameter)
	{
		// First try to get from the parameter's main_value and standard
		if ($parameter->main_value && $parameter->main_value !== 'NS') {
			$standardPrefix = getStandardLimitValue($parameter->id, $parameter->main_standard, 1) ?? '';
			$mainValue = ($parameter->main_value == 'NS') ? '--' : ($parameter->main_value ?? '');
			$standardSuffix = getStandardLimitValue($parameter->id, $parameter->main_standard) ?? '';
			
			$fullStandard = trim($standardPrefix . $mainValue . ' ' . $standardSuffix);
			if (!empty($fullStandard) && $fullStandard !== '--') {
				return $fullStandard;
			}
		}
		
		// Fall back to hardcoded values for common microbiology parameters
		$code = strtolower($parameter->analyte_code);
		$name = strtolower($parameter->analyte_name ?? '');
		$searchText = $code . ' ' . $name;
		
		if (strpos($searchText, 'tvc') !== false || strpos($searchText, 'total viable') !== false || strpos($searchText, 'total plate') !== false) {
			return '≤ 100 CFU/ml';
		} elseif (strpos($searchText, 'total coliform') !== false || strpos($searchText, 'coliforms') !== false) {
			return '0 CFU/100ml';
		} elseif (strpos($searchText, 'e. coli') !== false || strpos($searchText, 'e.coli') !== false || strpos($searchText, 'ecoli') !== false) {
			return '0 CFU/100ml';
		} elseif (strpos($searchText, 'fecal coliform') !== false || strpos($searchText, 'faecal coliform') !== false) {
			return '0 CFU/100ml';
		} elseif (strpos($searchText, 'enterococci') !== false || strpos($searchText, 'enterococcus') !== false) {
			return '0 CFU/100ml';
		} elseif (strpos($searchText, 'salmonella') !== false) {
			return 'Absent/25ml';
		} elseif (strpos($searchText, 'shigella') !== false) {
			return 'Absent/25ml';
		}
		
		return 'NS';
	}
}

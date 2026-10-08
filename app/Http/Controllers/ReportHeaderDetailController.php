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
use App\SampleAnalysisStage;
use App\BatchLabSectionApprover;
use App\BatchAttachment;
use App\User;
use App\SampleAnalysisDates;
use App\BatchAmmendment;
use App\ReportFormat;
use App\Models\LabSectionReportConfig;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use setasign\Fpdi\TcpdfFpdi;
use Illuminate\Support\Facades\Log;


class ReportHeaderDetailController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function sample_interpretations(Request $request, $sample_id)
	{
		$detail = SampleDetails::find($sample_id);
		if ($detail) {
			$batch = \App\SampleHeader::find($detail->sample_header_id);
			if ($batch && $batch->status === 'Sample Approval') {
				return redirect()->back()->with('error', 'This report is locked in the Sample Approval lifecycle and cannot be modified.');
			}
		}

		$detail->main_body = $request->main_body;
		$detail->notes_body = $request->notes_body;
		$detail->save();

		app(\App\Services\Sampleworkflow\StatementOfConformityService::class)->ensureForSample($detail);

		return \redirect()->back()->with('success', 'Sample Comments and Interpretations have been saved');
	}

	public function report_interpretations(Request $request, $batch_id)
	{
		$detailType = array("App\SampleHeader", "App\CRMCustomer");
		$batch = \App\SampleHeader::find($batch_id);
		if ($batch && $batch->status === 'Sample Approval') {
			return redirect()->back()->with('error', 'This report is locked in the Sample Approval lifecycle and cannot be modified.');
		}

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
		Log::info('process_pdf_report called', [
			'batch_id' => $batch_id,
			'report_format' => $report_format,
			'include_pesticide' => $include_pesticide,
			'merge_with_attachments' => request()->boolean('merge_with_attachments'),
			'attachment_ids' => request('attachment_ids', ''),
		]);

		// Ensure samples_by_category view exists in PostgreSQL
		try {
			\Illuminate\Support\Facades\DB::table('samples_by_category')->first();
		} catch (\Exception $e) {
			try {
				\Illuminate\Support\Facades\DB::statement("
					CREATE OR REPLACE VIEW samples_by_category AS
					SELECT 
						sh.batch_code AS batch_code,
						sh.receipt_date AS receipt_date,
						sh.date_collected AS date_collected,
						sh.crm_customer_id AS crm_customer_id,
						sh.sample_type_id AS sample_type_id,
						sh.reference_number AS reference_number,
						sh.status AS workflow_stage,
						sh.is_routine AS is_routine,
						sh.priority AS priority,
						sh.batch_scope AS batch_scope,
						sh.customer_survey AS customer_survey,
						sh.approval_date AS approval_date,
						sh.submit_by AS submit_by,
						sh.sampled_by_company_personnel AS sampled_by_company_personnel,
						sh.radio_active_levels AS batch_no,
						sh.description AS product_description,
						sh.batch_instructions AS batch_instructions,
						sh.sampling_officer_name AS sampling_officer_name,
						sh.retention_date AS retention_date,
						sh.kra_office_ref AS kra_office_ref,
						cc.code AS crm_code,
						cc.name AS crm_name,
						cc.postal_address AS postal_address,
						cc.physical_address AS physical_address,
						sh.crm_unit_name AS crm_unit_name,
						st.code AS sample_type_code,
						st.name AS sample_type_name,
						sd.id AS id,
						sd.sample_code AS sample_code,
						sd.analysis_type_id AS analysis_type_id,
						sd.sample_condition_id AS sample_condition_id,
						sd.barcode AS barcode,
						sd.comments AS comments,
						sd.gps AS gps,
						sd.photo_url AS photo_url,
						sd.created_at AS created_at,
						sd.updated_at AS updated_at,
						sd.sample_header_id AS sample_header_id,
						sd.sample_point_id AS sample_point_id,
						sd.company_product_id AS company_product_id,
						sd.main_body AS main_body,
						sd.header_body AS header_body,
						sd.is_ammendment AS is_ammendment,
						sd.ammendment_number AS ammendment_number,
						sd.main_standard AS main_standard,
						sd.secondary_standard AS secondary_standard,
						sd.short_code AS short_code,
						sd.material_status AS material_status,
						sd.third_standard_id AS third_standard_id,
						sd.sample_no AS sample_no,
						sd.no_of_samples AS no_of_samples,
						sd.no_of_pots_plants AS no_of_pots_plants,
						sd.standard_tests AS standard_tests,
						sd.compartiment_lot AS compartiment_lot,
						sd.coa_number AS coa_number,
						sd.results AS results,
						sd.lab_sub_no AS lab_sub_no,
						sd.store_id AS store_id,
						sd.store_slot_id AS store_slot_id,
						sd.quantity AS quantity,
						sd.reporting_unit_id AS reporting_unit_id,
						sd.mfg_date AS mfg_date,
						sd.expiry_date AS expiry_date,
						sd.batch_lot_no AS batch_lot_no,
						sd.coa_number_target AS coa_number_target,
						sd.disposal_date AS disposal_date,
						sd.is_disposed AS is_disposed,
						sd.notes_body AS notes_body,
						sd.report_number AS report_number,
						cp.name AS product_name,
						sp.name AS sample_point_name,
						NULL AS sample_point_area_name,
						sc.name AS sample_condition_name,
						smain.code AS main_standard_code,
						ssec.code AS sec_standard_code,
						sthird.code AS third_standard_code,
						iss.name AS store_slot_name,
						is2.name AS store_name,
						ru.name AS reporting_unit_name,
						ci.invoice_number AS invoice_number,
						am.name AS sampling_method_name,
						am.code AS sampling_method_code,
						l.code AS main_lab_code,
						l.name AS main_lab_name,
						l.id AS main_lab_id,
						ccu.name AS customer_crm_unit
					FROM sample_headers sh
					JOIN sample_details sd ON sh.id = sd.sample_header_id
					LEFT JOIN crm_customers cc ON sh.crm_customer_id = cc.id
					JOIN sample_types st ON sh.sample_type_id = st.id
					LEFT JOIN company_products cp ON sd.company_product_id = cp.id
					LEFT JOIN sample_conditions sc ON sd.sample_condition_id = sc.id
					LEFT JOIN sample_points sp ON sd.sample_point_id = sp.id
					LEFT JOIN crm_company_units ccu ON sh.crm_unit_id = ccu.id
					LEFT JOIN standards smain ON sd.main_standard = smain.id
					LEFT JOIN standards ssec ON sd.secondary_standard = ssec.id
					LEFT JOIN standards sthird ON sd.third_standard_id = sthird.id
					LEFT JOIN inventory_stores is2 ON sd.store_id = is2.id
					LEFT JOIN inventory_store_slots iss ON sd.store_slot_id = iss.id
					LEFT JOIN reporting_units ru ON sd.reporting_unit_id = ru.id
					LEFT JOIN customer_invoice ci ON sh.invoice_id = ci.id
					LEFT JOIN analysis_methods am ON sh.sampling_method_id = am.id
					LEFT JOIN labs l ON sd.lab_id = l.id
				");
			} catch (\Exception $inner) {
				Log::error("Failed to create samples_by_category view in PDF report processor: " . $inner->getMessage());
			}
		}
		// Load logos as base64 data URIs so DomPDF can render them
		// without chroot restrictions or HTTP deadlocks.
		$report_logo = $this->resolveImageAsDataUri($this->resolveCompanyLogoPath());
		$sadc_logo   = $this->resolveImageAsDataUri(public_path('images/sadcas_logo.png'));
		$ilac_logo   = $this->resolveImageAsDataUri(public_path('images/ilac-logo.png'));
		// Use the same base64 data URI approach as the main report logo.
		$accreditation_logo = $this->resolveImageAsDataUri(public_path('images/sadc-ilac.jpeg'));
		$stamp       = $this->resolveImageAsDataUri(public_path('images/company_logo.png'));

		$batch = SampleHeader::with(['customer', 'submissionFormInstance.submissionForm'])->find($batch_id);
		$batch->processing_date = getTodayDate();
		$batch->in_ammendment_proccess = 0;
		$batch->save();

		app(\App\Services\Sampleworkflow\StatementOfConformityService::class)->ensureForBatch($batch);

		$ammendment = BatchAmmendment::where('batch_id', $batch->id)->orderBy('id', 'DESC')->first();
		$report_type = '';
		$report_type = $batch->prelim_report_status == 1 ? 'PRELIM' : $report_type;
		$report_type = $batch->prelim_report_status == 2 ? 'DRAFT' : $report_type;

		$batch_approvers = BatchLabSectionApprover::where('batch_id', $batch->id)->where('show_report', 1)->where('status', 1)->get();
		$is_stamp = BatchLabSectionApprover::where('batch_id', $batch->id)->where('show_report', 1)->where('status', 1)->where('batch_status', 'Sample Approval')->first();
		$analysis_date = $this->resolveBatchAnalysisDateRange($batch->id);

		$disclaimer = 'The report shall not be reproduced except in full without approval of the laboratory. The information supplied by the customer can affect the validity of results. The results relate only to the items tested. The results apply to the sample as received. Opinions, interpretations and comments herein are not covered within the scope of accreditation.';
		$status = $batch->status;

		$customer = $batch->customer;
		$company = getActiveCompany();
		$date = date("d-M-Y", strtotime(getTodayDate()));

		$customer_name = preg_replace('/[^A-Za-z0-9]/', '', (string) ($customer->name ?? 'customer'));
		$batch_code = preg_replace('/[^A-Za-z0-9]/', '', $batch->batch_code);

		if ($batch->document_number != '') {
			$filename = $customer_name . '-' . $batch_code . '-' . date("d-M-Y-H-i-s") . '-' . $batch->document_number . '.pdf';
		} else {
			$filename = $customer_name . '-' . $batch_code . '-' . date("d-M-Y-H-i-s") . '.pdf';
		}
		$filename = urlencode($filename);

		if ($this->shouldUseTestRequestReport($batch)) {
			Log::info('process_pdf_report: using test request report generator', [
				'batch_id' => $batch->id,
				'batch_code' => $batch->batch_code,
				'report_format' => $report_format,
			]);

			$lang = strtolower((string) request('gcla_language', 'en'));
			if (! in_array($lang, ['en', 'ar', 'pt'], true)) {
				$lang = 'en';
			}

			$nextFromSequence = ((int) ($batch->test_request_report_sequence ?? 0)) + 1;
			$amendmentVersion = max(1, (int) ($batch->is_amendment ?? 1));
			$sequence = max($nextFromSequence, $amendmentVersion);

			$batch->test_request_report_sequence = $sequence;
			$batch->save();

			app(\App\Services\Sampleworkflow\JobSampleNumberingService::class)
				->syncReportNumbersForBatch($batch, $sequence);

			$reportRequest = Request::create('/generate-test-request-report', 'GET', [
				'batch_id' => (string) $batch->id,
				'seq' => $sequence,
				'lang' => $lang,
				'mode' => 'pdf',
			]);
			$reportRequest->setUserResolver(fn () => auth()->user());

			$response = app(SampleWorkFlowController::class)->generateTestRequestReport($reportRequest);

			if (! empty($batch->crm_customer_id)) {
				$cacheService = app(\App\Services\Dashboard\DashboardCacheService::class);
				$cacheService->forgetList('reports', (string) $batch->crm_customer_id);
				$cacheService->forgetCustomer((string) $batch->crm_customer_id);
			}

			return $response;
		}

		// Attachments merging options (passed via query parameters)
		$mergeWithAttachments = request()->boolean('merge_with_attachments');
		$attachmentIdsParam = (string) request('attachment_ids', '');
		$attachmentIds = array_values(array_filter(array_map('intval', explode(',', $attachmentIdsParam))));

		$qr_url = url('/storage/reports/' . $customer_name . '/' . $filename);
		$qrcode = base64_encode(QrCode::format('svg')->size(50)->errorCorrection('H')->generate($qr_url));

		$tempFiles = [];

		if ($report_format === 'gcla_02' || (string) $report_format === 'gcla_02') {
			return $this->processGCLA02Report(
				$batch,
				$customer,
				$company,
				$report_logo,
				$stamp,
				$is_stamp,
				$filename,
				$customer_name,
				$mergeWithAttachments,
				$attachmentIds,
				$batch_approvers,
				$analysis_date,
				$report_type,
				$ammendment,
				$disclaimer,
				$date
			);
		}

		if ($report_format === 'dcea_009' || (string) $report_format === 'dcea_009') {
			if (!$batch->hasForensicChemistryLab()) {
				abort(403, 'DCEA 009 report is only available for Forensic Chemistry laboratories.');
			}
			return $this->processDCEA009Report(
				$batch,
				$customer,
				$company,
				$report_logo,
				$stamp,
				$is_stamp,
				$filename,
				$customer_name,
				$mergeWithAttachments,
				$attachmentIds,
				$batch_approvers,
				$analysis_date,
				$report_type,
				$ammendment,
				$disclaimer,
				$date
			);
		}

		// Resolve report format: First try by ID, then fallback to report_code for legacy support
		$formatModel = null;
		$reportCode = null;

		if ($report_format === 'water_report' || (string) $report_format === 'water_report') {
			$reportCode = 'water_report';
		} else {
			$formatModel = ReportFormat::find($report_format);

			if (!$formatModel) {
				// Legacy hardcoded params passed '0', '1', '2', '3' which match these report_codes or specific formats
				$legacyToCodeMap = [
					'0' => '0', // Aspergillus
					'1' => '1', // Microbiology
					'2' => '2', // Hygiene Swabs
					'3' => 'SER-COA', // Serology
				];
				$searchCode = $legacyToCodeMap[(string)$report_format] ?? (string)$report_format;
				$formatModel = ReportFormat::where('report_code', $searchCode)->first();
			}

			if (!$formatModel || !$formatModel->is_active) {
				abort(404, 'Report format not found or inactive.');
			}
			$reportCode = $formatModel->report_code;
		}

		// Legacy water report fallback
		if ($reportCode !== 'water_report' && isset($formatModel)) {
			return $this->processDynamicReport(
				$batch,
				$formatModel,
				$customer,
				$company,
				$report_logo,
				$sadc_logo,
				$ilac_logo,
				$accreditation_logo,
				$stamp,
				$is_stamp,
				$qrcode,
				$filename,
				$customer_name,
				$mergeWithAttachments,
				$attachmentIds,
				$batch_approvers,
				$analysis_date,
				$report_type,
				$ammendment,
				$disclaimer,
				$date
			);
		}

		if ($reportCode === 'water_report') {
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

			$amendmentVersion = (int) ($ammendment->version_number ?? $batch->is_amendment ?? 1);
			// is_amendment defaults to 1 for original jobs — do not treat that as an amendment.
			if ($amendmentVersion > 1) {
				app(\App\Services\Sampleworkflow\JobSampleNumberingService::class)
					->applyAmendmentNumbering($batch, $amendmentVersion);
			} else {
				app(\App\Services\Sampleworkflow\JobSampleNumberingService::class)
					->clearSampleCodeSuffixesForBatch($batch);
			}
			$amendmentConfig = app(\App\Services\Sampleworkflow\AmendmentReportConfigurationService::class);
			$amendmentDisplay = $amendmentConfig->amendmentViewData(
				max(1, $amendmentVersion),
				(string) $batch->batch_code,
				$amendmentConfig->sampleSequenceNumbersForBatch($batch),
				$amendmentConfig->labSectionNamesForBatch($batch),
			);

			$pdf = PDF::loadView('layouts.lab.reports.coa_formats.report_formats', compact('sample', 'company', 'qrcode', 'report_logo', 'sadc_logo', 'ilac_logo', 'batch_approvers', 'pdf', 'batch', 'disclaimer', 'customer', 'report_type', 'analysis_date', 'stamp', 'is_stamp', 'ammendment', 'amendmentDisplay'));
			app(\App\Services\Reports\ReportWatermarkService::class)->applyToPdf($pdf, $company);
			$tempFile = storage_path() . '/app/reports/' . $customer_name . '/' . $filename;

			if (!is_dir(storage_path() . '/app/reports/' . $customer_name)) {
				$path = storage_path() . '/app/reports/' . $customer_name;
				mkdir($path, 0755, true);
			}

			$pdf->save(storage_path() . '/app/reports/' . $customer_name . '/' . $filename);
			$tempFiles[] = $tempFile;
		}

		$batch->batch_report_url = '/reports/' . $customer_name . '/' . $filename;
		$batch->save();

		// For water_report we currently just return a JSON response.
		// Attachment merging is not applied in this legacy flow.
		return response()->json(['success' => true, 'message' => 'PDF successfully saved to FTP!']);
	}

	private function shouldUseTestRequestReport(SampleHeader $batch): bool
	{
		$instance = $batch->submissionFormInstance;
		if (! $instance || ! $instance->submissionForm) {
			return false;
		}

		$documentCode = strtoupper((string) ($instance->submissionForm->document_code ?? ''));
		$formName = strtolower((string) ($instance->submissionForm->name ?? ''));

		if (str_starts_with($documentCode, 'TRF-') || $documentCode === 'LSR-001') {
			return true;
		}

		return str_contains($formName, 'test request form')
			|| str_contains($formName, 'laboratory service request');
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
	 * Merge a generated COA PDF with selected batch attachments.
	 *
	 * @param  string       $coaPath       Absolute path to the main COA PDF.
	 * @param  SampleHeader $batch         Batch whose attachments should be merged.
	 * @param  array        $attachmentIds Attachment IDs (from batch_attachments) to append.
	 * @param  string       $customerName  Sanitized customer name used in report paths.
	 * @param  string       $filename      Target filename for the merged PDF.
	 * @return string                      The merged PDF binary content.
	 */
	private function mergeCoaWithAttachments(string $coaPath, SampleHeader $batch, array $attachmentIds, string $customerName, string $filename): string
	{
		$pdf = new TcpdfFpdi();
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(0, 0, 0);
		$pdf->SetAutoPageBreak(false);

		$validFiles = [];
		$totalPageCount = 0;

		// First, add the main COA as the leading document if it exists
		if (file_exists($coaPath)) {
			try {
				$tempPdf = new TcpdfFpdi();
				$pageCount = $tempPdf->setSourceFile($coaPath);
				$totalPageCount += $pageCount;
				$validFiles[] = ['path' => $coaPath, 'count' => $pageCount, 'title' => 'COA'];
			} catch (\Exception $e) {
				\Log::warning("Could not read COA PDF for merging: " . $e->getMessage());
			}
		}

		// Fetch attachments belonging to the batch
		if (!empty($attachmentIds)) {
			$attachments = BatchAttachment::where('batch_id', $batch->id)
				->whereIn('id', $attachmentIds)
				->get();

			// Preserve user-selected order
			foreach ($attachmentIds as $id) {
				$attachment = $attachments->firstWhere('id', $id);
				if (!$attachment) {
					continue;
				}

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

				// Only attempt to merge PDFs
				if (!file_exists($filePath) || strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) !== 'pdf') {
					continue;
				}

				try {
					$tempPdf = new TcpdfFpdi();
					$pageCount = $tempPdf->setSourceFile($filePath);
					$totalPageCount += $pageCount;
					$validFiles[] = [
						'path' => $filePath,
						'count' => $pageCount,
						'title' => $attachment->title ?? 'Attachment'
					];
				} catch (\Exception $e) {
					\Log::warning("Could not pre-scan attachment PDF {$attachment->title}: " . $e->getMessage());
				}
			}
		}

		if (count($validFiles) === 0) {
			// Fallback: just return the original COA if no attachments could be merged
			return file_exists($coaPath) ? file_get_contents($coaPath) : '';
		}

		$currentPageGlobal = 1;
		foreach ($validFiles as $fileInfo) {
			try {
				$pdf->setSourceFile($fileInfo['path']);
				for ($pageNo = 1; $pageNo <= $fileInfo['count']; $pageNo++) {
					$templateId = $pdf->importPage($pageNo);
					$size = $pdf->getTemplateSize($templateId);

					$pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
					$pdf->useTemplate($templateId);

					// Set white fill color for covering original page numbers
					$pdf->SetFillColor(255, 255, 255); // White
					$pdf->SetDrawColor(255, 255, 255);

					// Cover common page number positions with comprehensive areas
					// Use larger coverage to account for different font sizes, positions, and variations
					$coverageWidth = 80; // Generous width for "Page 999 of 9999" in various font sizes
					$coverageHeight = 20; // Generous height for page numbers in various font sizes

					// 1. Bottom-right position (most common - covers "Page 1 of 2", "Page 1 of 9", etc.)
					// Cover multiple variations to catch all possible positions
					$pdf->Rect($size['width'] - 85, $size['height'] - 25, $coverageWidth, $coverageHeight, 'F');
					$pdf->Rect($size['width'] - 75, $size['height'] - 20, 70, 18, 'F');
					$pdf->Rect($size['width'] - 65, $size['height'] - 15, 60, 15, 'F');

					// 2. Bottom-center position (some reports use this)
					$bottomCenterX = ($size['width'] / 2) - ($coverageWidth / 2);
					$pdf->Rect($bottomCenterX, $size['height'] - 25, $coverageWidth, $coverageHeight, 'F');
					$pdf->Rect(($size['width'] / 2) - 40, $size['height'] - 20, 80, 18, 'F');

					// Now add new global page numbering at bottom-right
					$pdf->SetFont('helvetica', '', 8);
					$text = "Page {$currentPageGlobal} of {$totalPageCount}";
					$xTextPos = $size['width'] - 35; // Where text will be drawn
					$yTextPos = $size['height'] - 10; // Where text will be drawn
					$pdf->SetTextColor(0, 0, 0);
					$pdf->Text($xTextPos, $yTextPos, $text);

					$currentPageGlobal++;
				}
			} catch (\Exception $e) {
				\Log::error("Error merging PDF segment {$fileInfo['title']}: " . $e->getMessage());
			}
		}

		// Get merged binary content
		$mergedContent = $pdf->Output($filename, 'S');

		// Persist merged file over the original COA path so links remain valid
		$storageRelativePath = 'reports/' . $customerName . '/' . $filename;
		Storage::put($storageRelativePath, $mergedContent);

		return $mergedContent;
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

	/**
	 * Resolve the active company's logo to an absolute filesystem path.
	 * Falls back to the default logo-report.png if no company logo is configured.
	 */
	private function resolveCompanyLogoPath(): string
	{
		$company = getActiveCompany();

		if ($company && !empty($company->logo)) {
			$path = $company->logo;

			// Strip URL prefix if stored as a full URL
			if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
				$path = parse_url($path, PHP_URL_PATH) ?? $path;
			}
			$path = ltrim($path, '/');
			$filename = basename($path);

			if ($filename !== '') {
				// 1) Public storage disk (storage/app/public/...)
				$relative = preg_replace('#^storage/#', '', $path);
				if ($relative !== $path) {
					$fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($relative);
					if (file_exists($fullPath)) {
						return $fullPath;
					}
				}

				// 2) App convention: storage/app/companies/<filename>
				$fullPath = storage_path('app/companies/' . $filename);
				if (file_exists($fullPath)) {
					return $fullPath;
				}

				// 3) The company logo field may also be a public/ relative path
				if (file_exists(public_path($path))) {
					return public_path($path);
				}
			}
		}

		// Fallback: default report logo
		$defaultLogo = public_path('images/logo-report.png');
		return file_exists($defaultLogo) ? $defaultLogo : '';
	}

	private function resolveSingleLogoPath(?string $path): ?string
	{
		if (empty($path)) {
			return null;
		}

		if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
			$path = parse_url($path, PHP_URL_PATH) ?? $path;
		}
		$path = ltrim($path, '/');
		
		if (file_exists($path)) {
			return $path;
		}

		$filename = basename($path);
		if ($filename !== '') {
			$relative = preg_replace('#^storage/#', '', $path);
			if ($relative !== $path) {
				$fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($relative);
				if (file_exists($fullPath)) {
					return $fullPath;
				}
			}

			$fullPath = storage_path('app/companies/' . $filename);
			if (file_exists($fullPath)) {
				return $fullPath;
			}

			if (file_exists(public_path($path))) {
				return public_path($path);
			}

			if (file_exists(base_path('public/' . $path))) {
				return base_path('public/' . $path);
			}
		}

		return null;
	}

	private function getResolvedTanzaniaLogo($company): ?string
	{
		if ($company) {
			$paths = [
				$company->getReportLogoPath('coat_of_arms'),
				$company->getReportLogoPath('tz_flag'),
				$company->report_logo,
			];
			foreach ($paths as $path) {
				$resolved = $this->resolveSingleLogoPath($path);
				if ($resolved) {
					return $this->resolveImageAsDataUri($resolved);
				}
			}
		}

		$fallbacks = [
			public_path('images/forms/tanzanialogo.jpeg'),
			public_path('images/forms/tanzanialogo.jpg'),
			base_path('public/images/forms/tanzanialogo.jpeg'),
			base_path('public/images/forms/tanzanialogo.jpg'),
		];
		foreach ($fallbacks as $fallback) {
			if (is_file($fallback)) {
				return $this->resolveImageAsDataUri($fallback);
			}
		}

		return null;
	}

	private function getResolvedGclaLogo($company): ?string
	{
		if ($company) {
			$paths = [
				$company->getReportLogoPath('gcla_logo'),
				$company->getReportLogoPath('gcla'),
				$company->logo,
			];
			foreach ($paths as $path) {
				$resolved = $this->resolveSingleLogoPath($path);
				if ($resolved) {
					return $this->resolveImageAsDataUri($resolved);
				}
			}
		}

		$fallbacks = [
			public_path('images/forms/gclalogo.png'),
			public_path('images/forms/gclalogo.jpg'),
			base_path('public/images/forms/gclalogo.png'),
			base_path('public/images/forms/gclalogo.jpg'),
		];
		foreach ($fallbacks as $fallback) {
			if (is_file($fallback)) {
				return $this->resolveImageAsDataUri($fallback);
			}
		}

		return null;
	}

	/**
	 * Convert an image at the given absolute path to a base64 data URI string.
	 * Returns the original path string if the file cannot be read (DomPDF can try local paths).
	 */
	private function resolveImageAsDataUri(string $absolutePath): string
	{
		if ($absolutePath === '' || !is_readable($absolutePath)) {
			return $absolutePath;
		}

		$contents = @file_get_contents($absolutePath);
		if ($contents === false) {
			return $absolutePath;
		}

		$ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
		$mime = match ($ext) {
			'png'        => 'image/png',
			'jpg', 'jpeg' => 'image/jpeg',
			'gif'        => 'image/gif',
			'webp'       => 'image/webp',
			'svg'        => 'image/svg+xml',
			default      => 'image/png',
		};

		return 'data:' . $mime . ';base64,' . base64_encode($contents);
	}

	private function processDynamicReport($batch, $reportFormatModel, $customer, $company, $report_logo, $sadc_logo, $ilac_logo, $accreditation_logo, $stamp, $is_stamp, $qrcode, $filename, $customer_name, $mergeWithAttachments, $attachmentIds, $batch_approvers, $analysis_date, $report_type, $ammendment, $disclaimer, $date)
	{
		$samples = SamplesCategory::where('sample_header_id', $batch->id)->with('samplePointArea.companyUnit', 'samplePointArea.subUnit', 'samplePointArea.crmArea')->get();
		$standard_codes = implode(',', $samples->pluck('main_standard_code')->unique()->toArray());

		$parameters = CapturedResult::where('captured_results.sample_header_id', $batch->id)
			->select(
				'captured_results.id',
				'captured_results.analyte_id',
				'captured_results.analyte_code',
				'captured_results.analyte_accredited',
				'captured_results.reporting_unit_id',
				'captured_results.main_value',
				'captured_results.lab_section_id',
				'sd.main_standard',
				'captured_results.method_id',
				'am.name as method_name',
				'am.code as method_code',
				'ru.name as reporting_unit_name',
				'a.name as analyte_name',
				'sas.name as lab_section_name',
				'sas.code as lab_section_code'
			)
			->join('sample_details as sd', 'sd.id', '=', 'captured_results.sample_detail_id')
			->leftJoin('analysis_methods as am', 'am.id', '=', 'captured_results.method_id')
			->leftJoin('reporting_units as ru', 'ru.id', '=', 'captured_results.reporting_unit_id')
			->leftJoin('analytes as a', 'a.id', '=', 'captured_results.analyte_id')
			->leftJoin('sample_analysis_stages as sas', 'sas.id', '=', 'captured_results.lab_section_id')
			->distinct()
			->orderBy('analyte_code')
			->get();

		$standards = [];
		foreach ($parameters as $parameter) {
			$standardValue = $this->getParameterStandardValue($parameter);
			if ($standardValue !== 'NS') {
				$standards[$parameter->analyte_code] = $standardValue;
			}
		}

		// Build Tests Required label from captured analytes, marking accredited ones with an asterisk
		$testsRequiredParts = [];
		foreach ($parameters as $parameter) {
			$label = $parameter->analyte_name ?? $parameter->analyte_code;
			if (! $label) {
				continue;
			}

			// If analyte is accredited, surround with an asterisk as requested
			if ((int) ($parameter->analyte_accredited ?? 0) === 1) {
				$label = '*' . $label . '*';
			}

			$testsRequiredParts[] = $label;
		}
		$testsRequiredParts = array_values(array_unique($testsRequiredParts));
		$testsRequired = ! empty($testsRequiredParts) ? implode(', ', $testsRequiredParts) : null;

		$allResults = CapturedResult::where('captured_results.sample_header_id', $batch->id)
			->with('labSection')
			->select('captured_results.*')
			->get()
			->groupBy('sample_detail_id');

		$labSectionIds = $allResults->flatten()
			->pluck('lab_section_id')
			->filter()
			->map(fn ($id) => (string) $id)
			->unique()
			->values()
			->all();

		$labSectionNamesById = $labSectionIds === []
			? []
			: SampleAnalysisStage::query()
				->whereIn('id', $labSectionIds)
				->get()
				->mapWithKeys(fn (SampleAnalysisStage $stage) => [(string) $stage->id => $stage->name])
				->all();

		$sampleDetailsById = SampleDetails::query()
			->with('sample_point')
			->where('sample_header_id', $batch->id)
			->get()
			->keyBy('id');

		$groupedSamples = [];
		$ungroupedSamples = [];

		foreach ($samples as $sample) {
			$sampleResults = [];
			$samplePasses = 0;
			$sampleTotal = 0;

			$sampleResultsData = $allResults->get($sample->id, collect());
			$resultsByAnalyte = $sampleResultsData->keyBy('analyte_id');

			foreach ($parameters as $parameter) {
				$result = $resultsByAnalyte->get($parameter->analyte_id);
				if ($result) {
					$labSectionId = $result->lab_section_id ? (string) $result->lab_section_id : null;
					$sampleResults[$parameter->analyte_code] = [
						'value' => $result->result,
						'unit' => $result->reporting_unit_id,
						'remark' => $result->remark ?? 'PASS',
						'lab_section' => $labSectionId
							? ($labSectionNamesById[$labSectionId] ?? $result->labSection?->name)
							: null,
					];
					$sampleTotal++;
					if (is_conforming_remark($result->remark ?? 'PASS')) {
						$samplePasses++;
					}
				} else {
					$sampleResults[$parameter->analyte_code] = [
						'value' => 'N/A',
						'unit' => '',
						'remark' => 'N/A',
						'lab_section' => $parameter->lab_section_name ?? null,
					];
				}
			}

			$conformity = ($sampleTotal > 0 && $samplePasses === $sampleTotal) ? 'PASS' : 'FAIL';
			// Resolve sample code and sampling point for display in results tables
			$sampleDetail = $sampleDetailsById->get($sample->id);
			$sampleCode = $sampleDetail->sample_code ?? ($sample->sample_code ?? null);

			$samplePointName = null;
			if ($sampleDetail && $sampleDetail->sample_point) {
				$samplePointName = $sampleDetail->sample_point->name;
			} elseif ($sample->samplePointArea) {
				$samplePointName = $sample->samplePointArea->description ?? $sample->samplePointArea->name;
			}

			$sampleData = [
				'sample' => $sampleDetail ?: $sample,
				'sample_code' => $sampleCode,
				'sample_point_name' => $samplePointName,
				'results' => $sampleResults,
				'conformity' => $conformity,
			];

			if ($sample->samplePointArea) {
				$areaName = $sample->samplePointArea->name;
				$groupedSamples[$areaName][] = $sampleData;
			} else {
				$ungroupedSamples[] = $sampleData;
			}
		}

		// Determine sample type name for this batch
		$sampleTypeName = $batch->sample_type?->name ?? '';

		// Resolve lab section display name(s) for this batch (for "Test Section")
		$testSectionName = null;
		if (! empty($batch->lab_section_ids)) {
			$labSectionIds = array_filter(explode(',', $batch->lab_section_ids));
			if (! empty($labSectionIds)) {
				// Use collection pluck so we can access the accessor "namecode"
				$sections = \App\SampleAnalysisStage::whereIn('id', $labSectionIds)
					->get()
					->pluck('namecode')
					->toArray();

				if (! empty($sections)) {
					$testSectionName = implode(', ', $sections);
				}
			}
		}

		// Build rich customer reference from company hierarchy & sampling location
		$customerReference = null;
		try {
			// Prefer CRM SamplePoint from the first concrete sample detail for this batch
			$primaryDetail = $batch->all_samples()->first();
			$locationParts = [];

			if ($primaryDetail && $primaryDetail->sample_point_id) {
				$samplePoint = \App\Models\CRM\SamplePoint::with(['subUnit', 'unit', 'area.crmArea', 'crmSamplePoint'])->find($primaryDetail->sample_point_id);

				if ($samplePoint) {
					$companySection = optional($samplePoint->subUnit)->name;
					$companyUnit = optional($samplePoint->unit)->name;
					$sampleArea = optional(optional($samplePoint->area)->crmArea)->name ?: optional($samplePoint->area)->description;
					$samplePointName = $samplePoint->name;

					if ($companySection) {
						$locationParts[] = $companySection;
					}
					if ($companyUnit) {
						$locationParts[] = $companyUnit;
					}
					if ($sampleArea) {
						$locationParts[] = $sampleArea;
					}
					if ($samplePointName) {
						$locationParts[] = $samplePointName;
					}
				}
			}

			// Fallback: derive from first SamplesCategory's SamplePointArea hierarchy if needed
			if (empty($locationParts) && $samples->isNotEmpty() && $samples->first()->samplePointArea) {
				$spa = $samples->first()->samplePointArea;
				$companySection = optional($spa->subUnit)->name;
				$companyUnit = optional($spa->companyUnit)->name;
				$sampleArea = optional($spa->crmArea)->name ?: $spa->description;

				if ($companySection) {
					$locationParts[] = $companySection;
				}
				if ($companyUnit) {
					$locationParts[] = $companyUnit;
				}
				if ($sampleArea) {
					$locationParts[] = $sampleArea;
				}
			}

			if (!empty($locationParts)) {
				$customerReference = implode(' > ', $locationParts);
			}
		} catch (\Throwable $e) {
			// In case of any unexpected issues, gracefully fall back in the view.
			$customerReference = null;
		}

		// Resolve document control metadata (document code, revision number, issue date)
		$document_code = null;
		$revision_number = null;
		$issue_date = null;

		if ($batch->lab_section_ids && $reportFormatModel) {
			$labSectionIds = array_filter(explode(',', $batch->lab_section_ids));

			if (!empty($labSectionIds)) {
				$reportConfig = LabSectionReportConfig::whereIn('sample_analysis_stage_id', $labSectionIds)
					->where('report_format_id', $reportFormatModel->id)
					->orderByDesc('is_default')
					->orderByDesc('id')
					->first();

				if ($reportConfig) {
					$document_code = $reportConfig->document_code ?: null;
					$revision_number = $reportConfig->revision_number ?: null;
					$issue_date = $reportConfig->issue_date ? $reportConfig->issue_date->format('Y-m-d') : null;
				}
			}
		}

		// When a dynamic report format is configured to use attachment-based
		// results (e.g. serology), pre-load the batch attachments so the
		// dynamic report view can render an attachment summary instead of
		// a parameter grid/list.
		$attachments = collect();
		$serologySummaries = [];
		if ($reportFormatModel && ($reportFormatModel->results_display_type ?? 'grid') === 'attachment_summary') {
			$attachments = BatchAttachment::where('batch_id', $batch->id)
				->whereHas('capturedResults', function ($q) use ($batch) {
					$q->where('sample_header_id', $batch->id);
				})
				->with(['capturedResults.sample', 'annotations'])
				->orderBy('id')
				->get();

			foreach ($attachments as $attachment) {
				$sampleCodes = $attachment->capturedResults
					->map(function ($cr) {
						return optional($cr->sample)->sample_code;
					})
					->filter()
					->unique()
					->values()
					->all();

				$parameterCodes = $attachment->capturedResults
					->pluck('analyte_code')
					->filter()
					->unique()
					->values()
					->all();

				$pageFrom = null;
				$pageTo = null;
				if ($attachment->annotations && $attachment->annotations->count() > 0) {
					$pageFrom = $attachment->annotations->min('page_number');
					$pageTo = $attachment->annotations->max('page_number');
				}

				$serologySummaries[] = [
					'title' => $attachment->title ?? ($attachment->file_name ?? ('Attachment #' . $attachment->id)),
					'file_name' => $attachment->file_name,
					'samples' => $sampleCodes,
					'parameters' => $parameterCodes,
					'page_from' => $pageFrom,
					'page_to' => $pageTo,
				];
			}
		}

		$data = compact(
			'batch',
			'customer',
			'company',
			'groupedSamples',
			'ungroupedSamples',
			'parameters',
			'batch_approvers',
			'analysis_date',
			'report_type',
			'ammendment',
			'disclaimer',
			'qrcode',
			'report_logo',
			'sadc_logo',
			'ilac_logo',
			'accreditation_logo',
			'stamp',
			'is_stamp',
			'date',
			'standards',
			'standard_codes',
			'sampleTypeName',
			'reportFormatModel',
			'testsRequired',
			'attachments',
			'serologySummaries',
			'document_code',
			'revision_number',
			'issue_date',
			'testSectionName',
			'customerReference'
		);
		$amendmentVersion = (int) ($ammendment->version_number ?? $batch->is_amendment ?? 1);
		// is_amendment defaults to 1 for original jobs — do not treat that as an amendment.
		if ($amendmentVersion > 1) {
			app(\App\Services\Sampleworkflow\JobSampleNumberingService::class)
				->applyAmendmentNumbering($batch, $amendmentVersion);
		} else {
			app(\App\Services\Sampleworkflow\JobSampleNumberingService::class)
				->clearSampleCodeSuffixesForBatch($batch);
		}
		$amendmentConfig = app(\App\Services\Sampleworkflow\AmendmentReportConfigurationService::class);
		$data['amendmentDisplay'] = $amendmentConfig->amendmentViewData(
			max(1, $amendmentVersion),
			(string) $batch->batch_code,
			$amendmentConfig->sampleSequenceNumbersForBatch($batch),
			$amendmentConfig->labSectionNamesForBatch($batch),
		);
		$data['reportFormat'] = $reportFormatModel;
		$data['grouped_samples'] = $groupedSamples;
		$data['ungrouped_samples'] = $ungroupedSamples;
		$data['sample_type_name'] = $sampleTypeName;

		// Resolve GCLA logos for forensic DNA report templates dynamically from settings/defaults
		$tanzaniaLogo = $this->getResolvedTanzaniaLogo($company);
		$gclaLogo = $this->getResolvedGclaLogo($company);

		$data['logos'] = [
			'tanzania' => $tanzaniaLogo,
			'gcla' => $gclaLogo,
		];

		// Resolve signatures dynamically from custody flow
		$approvedByUser = \App\ChainOfCustody::join('users as u', 'u.id', '=', 'chain_of_custodies.moved_out_by')
			->where('sample_header_id', $batch->id)
			->select('u.*')
			->where('workflow_stage', "Sample Approval")->orderBy('chain_of_custodies.created_at', 'desc')->first();

		$verifiedByUser = \App\ChainOfCustody::join('users as u', 'u.id', '=', 'chain_of_custodies.moved_out_by')
			->where('sample_header_id', $batch->id)
			->select('u.*')
			->where('workflow_stage', "Sample Verification")->orderBy('chain_of_custodies.created_at', 'desc')->first();

		$data['analystName'] = $batch->specialist_analyst?->name ?? 'Dkt. John Doe';
		$data['verifierName'] = $verifiedByUser?->name ?? 'Prof. Jane Smith';
		$data['approverName'] = $approvedByUser?->name ?? 'Dkt. John Doe';

		ini_set('max_execution_time', 300);
		$pdf = app('dompdf.wrapper');
		$pdf->getDomPDF()->set_option("enable_php", true);
		$pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
		$pdf->getDomPDF()->set_option("isFontSubsettingEnabled", true);

		$isGclaForensic = false;
		if ($batch->status === 'Sample Approval' || 
			(isset($batch->sample_type) && ($batch->sample_type->code === 'ST-DNA' || stripos($batch->sample_type->name, 'DNA') !== false))) {
			$isGclaForensic = true;
		}

		if ($isGclaForensic) {
			$pdf = PDF::loadView('layouts.lab.reports.gcla-forensic-report', $data);
		} else {
			$pdf = PDF::loadView('layouts.lab.reports.dynamic_report', $data);
		}
		app(\App\Services\Reports\ReportWatermarkService::class)->applyToPdf($pdf, $company ?? ($data['company'] ?? null));

		$filename = $filename ?? ($customer_name . '-' . preg_replace('/[^A-Za-z0-9]/', '', $batch->batch_code) . '-' . date('d-M-Y-H-i-s') . '.pdf');
		$tempFile = storage_path() . '/app/reports/' . $customer_name . '/' . $filename;
		if (!is_dir(storage_path() . '/app/reports/' . $customer_name)) {
			mkdir(storage_path() . '/app/reports/' . $customer_name, 0755, true);
		}
		$pdf->save($tempFile);

		$batch->batch_report_url = '/reports/' . $customer_name . '/' . $filename;
		$publicReportPath = storage_path('app/public' . $batch->batch_report_url);
		if (!is_dir(dirname($publicReportPath))) {
			mkdir(dirname($publicReportPath), 0755, true);
		}
		$pdf->save($publicReportPath);
		$batch->save();

		if ($mergeWithAttachments && !empty($attachmentIds)) {
			$mergedContent = $this->mergeCoaWithAttachments($tempFile, $batch, $attachmentIds, $customer_name, $filename);
			@file_put_contents($tempFile, $mergedContent);
			@file_put_contents($publicReportPath, $mergedContent);
			return response($mergedContent, 200, ['Content-Type' => 'application/pdf']);
		}

		return $pdf->stream($filename);
	}

	private function resolveBatchAnalysisDateRange(string $batchId): ?object
	{
		$records = SampleAnalysisDates::where('sample_header_id', $batchId)
			->get(['start_analysis_date', 'analysis_dates']);

		$startDates = [];
		$endDates = [];

		foreach ($records as $record) {
			if (! empty($record->start_analysis_date)) {
				$startDates[] = $record->start_analysis_date;
				$endDates[] = $record->start_analysis_date;
			}

			$decoded = json_decode((string) $record->analysis_dates, true);
			if (! is_array($decoded)) {
				continue;
			}

			foreach ($decoded as $sectionRange) {
				if (is_array($sectionRange)) {
					$startDate = $sectionRange['start_date'] ?? $sectionRange['start'] ?? null;
					$endDate = $sectionRange['end_date'] ?? $sectionRange['end'] ?? null;

					if (! empty($startDate)) {
						$startDates[] = $startDate;
						$endDates[] = $startDate;
					}
					if (! empty($endDate)) {
						$endDates[] = $endDate;
					}
					continue;
				}

				if (! empty($sectionRange)) {
					$startDates[] = $sectionRange;
					$endDates[] = $sectionRange;
				}
			}
		}

		sort($startDates);
		rsort($endDates);

		$startDate = $startDates[0] ?? '';
		$endDate = $endDates[0] ?? $startDate;

		if ($startDate === '' && $endDate === '') {
			return null;
		}

		return (object) [
			'start_analysis_date' => $startDate,
			'end_analysis_date' => $endDate,
		];
	}

	private function processGCLA02Report($batch, $customer, $company, $report_logo, $stamp, $is_stamp, $filename, $customer_name, $mergeWithAttachments, $attachmentIds, $batch_approvers, $analysis_date, $report_type, $ammendment, $disclaimer, $date)
	{
		$language = request('gcla_language', 'sw');

		// 1. Resolve Coat of Arms and GCLA Logos as Base64 Data URIs dynamically from settings/defaults
		$tanzaniaLogo = $this->getResolvedTanzaniaLogo($company);
		$gclaLogo = $this->getResolvedGclaLogo($company);

		// 2. Resolve Samples Data and Captured Results
		$samples = \App\SampleDetails::where('sample_header_id', $batch->id)->get();
		$allCapturedForBatch = \App\CapturedResult::query()
			->where('sample_header_id', $batch->id)
			->whereNotNull('result')
			->get();
		$capturedBySample = $allCapturedForBatch->groupBy('sample_detail_id');

		$methodIds = $allCapturedForBatch->pluck('method_id')->filter()->unique()->values()->all();
		$unitIds = $allCapturedForBatch->pluck('reporting_unit_id')->filter()->unique()->values()->all();
		$methodsById = $methodIds !== []
			? \App\AnalysisMethod::query()->whereIn('id', $methodIds)->get()->keyBy('id')
			: collect();
		$unitsById = $unitIds !== []
			? \App\ReportingUnit::query()->whereIn('id', $unitIds)->get()->keyBy('id')
			: collect();

		$samplesData = [];
		foreach ($samples as $sample) {
			$capturedResults = $capturedBySample->get($sample->id, collect());

			$results = [];
			foreach ($capturedResults as $cr) {
				$methodName = '-';
				if ($cr->method_id) {
					$method = $methodsById->get($cr->method_id);
					if ($method) {
						$methodName = $method->code ?? $method->name;
					}
				}
				$results[] = [
					'analyte' => $cr->analyte_code ?? ($cr->analyte?->name ?? ''),
					'value' => $cr->result,
					'unit' => $cr->reporting_unit_id ? ($unitsById->get($cr->reporting_unit_id)->name ?? '') : '',
					'method' => $methodName,
				];
			}

			$appearance = $sample->notes_body;
			if (is_string($appearance)) {
				$appearance = trim(html_entity_decode(strip_tags($appearance), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
			}
			if ($appearance === null || $appearance === '') {
				$appearance = $sample->material_status ?? 'Liquid';
			}

			$samplesData[] = [
				'sample_code' => $sample->sample_code,
				'appearance' => $appearance,
				'results' => $results,
			];
		}

		// 3. Dynamically compile the "NB:" section underneath the main results table
		$uniqueMethodIds = $allCapturedForBatch
			->pluck('method_id')
			->filter()
			->unique()
			->values()
			->all();

		$nbNotesParts = [];
		$methodIndex = 1;
		foreach ($uniqueMethodIds as $mId) {
			$method = $methodsById->get($mId);
			if ($method) {
				$desc = !empty($method->description) ? $method->description : 'Analytical test method';
				$nbNotesParts[] = $methodIndex . '. ' . $method->code . ' - ' . $desc;
				$methodIndex++;
			}
		}
		$nbNotes = implode("\n", $nbNotesParts);

		// 4. Resolve unique Analytes for "Tests Requested"
		$uniqueAnalytes = \App\CapturedResult::where('sample_header_id', $batch->id)
			->join('analytes as a', 'a.id', '=', 'captured_results.analyte_id')
			->pluck('a.name')
			->unique()
			->toArray();
		$tests_requested = !empty($uniqueAnalytes) ? implode(', ', $uniqueAnalytes) : ($batch->description ?? 'Analysis');

		// 5. Resolve Comments
		$comments = $batch->approval_comment ?? ($batch->comments ?? ($batch->batch_instructions ?? ''));
		if (is_array($comments)) {
			$comments = implode("\n", array_filter($comments));
		}
		if (is_string($comments)) {
			$comments = trim($comments);
			if ($comments === '[]' || $comments === '[""]') {
				$comments = '';
			}
		}

		// 6. Resolve User Signatures and details
		$resolveUserSignature = function($user) {
			if (!$user) return null;
			$path = $user->signature_path ?? ($user->signature ?? null);
			if (!$path) return null;
			
			$candidates = [
				public_path($path),
				public_path('storage/' . $path),
				base_path('public/' . $path),
				base_path('public/storage/' . $path),
				storage_path('app/public/' . $path),
				$path
			];
			foreach ($candidates as $cand) {
				if (is_file($cand)) {
					return $this->resolveImageAsDataUri($cand);
				}
			}
			return null;
		};

		// Resolve signatures dynamically from custody flow
		$analystUser = null;
		if ($batch->specialist_analyst) {
			$analystUser = $batch->specialist_analyst;
		} else {
			$custodyAnalyst = \App\ChainOfCustody::join('users as u', 'u.id', '=', 'chain_of_custodies.moved_out_by')
				->where('sample_header_id', $batch->id)
				->select('u.*')
				->where('workflow_stage', "Sample Analysis")
				->orderBy('chain_of_custodies.created_at', 'desc')
				->first();
			if ($custodyAnalyst) {
				$analystUser = $custodyAnalyst;
			}
		}
		$analystName = $analystUser ? $analystUser->name : 'Dr. Elias S. Alute';
		$analystTitle = $analystUser ? $analystUser->designation : 'Chemist';

		$verifiedByUser = \App\ChainOfCustody::join('users as u', 'u.id', '=', 'chain_of_custodies.moved_out_by')
			->where('sample_header_id', $batch->id)
			->select('u.*')
			->where('workflow_stage', "Sample Verification")
			->orderBy('chain_of_custodies.created_at', 'desc')
			->first();
		$verifierName = $verifiedByUser ? $verifiedByUser->name : 'Mwanahawa H. Msangi';
		$verifierTitle = $verifiedByUser ? $verifiedByUser->designation : 'Senior Chemist II';

		$approvedByUser = \App\ChainOfCustody::join('users as u', 'u.id', '=', 'chain_of_custodies.moved_out_by')
			->where('sample_header_id', $batch->id)
			->select('u.*')
			->where('workflow_stage', "Sample Approval")
			->orderBy('chain_of_custodies.created_at', 'desc')
			->first();
		if (!$approvedByUser) {
			$batchApprover = \App\BatchLabSectionApprover::where('batch_id', $batch->id)
				->where('show_report', 1)
				->where('status', 1)
				->first();
			if ($batchApprover) {
				$approvedByUser = \App\User::find($batchApprover->user_id);
			}
		}
		$approverName = $approvedByUser ? $approvedByUser->name : 'Dr. Elias S. Alute';
		$approverTitle = $approvedByUser ? $approvedByUser->designation : 'Acting Director of Forensic Science Services';

		// 7. Compile $data
		$data = [
			'batch' => $batch,
			'customer' => $customer,
			'company' => $company,
			'language' => $language,
			'processing_date' => date('d/m/Y', strtotime(getTodayDate())),
			'receipt_date' => $batch->receipt_date ? date('d/m/Y', strtotime($batch->receipt_date)) : '-',
			'tests_requested' => $tests_requested,
			'sample_description' => $batch->sample_type?->name ?? 'Sampuli',
			'samples_data' => $samplesData,
			'nb_notes' => $nbNotes,
			'comments' => $comments,
			'analyst' => [
				'name' => $analystName,
				'title' => $analystTitle,
				'signature' => $resolveUserSignature($analystUser),
			],
			'verifier' => [
				'name' => $verifierName,
				'title' => $verifierTitle,
				'signature' => $resolveUserSignature($verifiedByUser),
			],
			'approver' => [
				'name' => $approverName,
				'title' => $approverTitle,
				'signature' => $resolveUserSignature($approvedByUser),
			],
			'coat_of_arms' => $tanzaniaLogo,
			'gcla_logo' => $gclaLogo,
		];

		ini_set('max_execution_time', 300);
		$pdf = app('dompdf.wrapper');
		$pdf->getDomPDF()->set_option("enable_php", true);
		$pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
		$pdf->getDomPDF()->set_option("isFontSubsettingEnabled", true);

		$pdf = PDF::loadView('batch.attachments.gcla-02-form-pdf', $data);

		$filename = $filename ?? ($customer_name . '-' . preg_replace('/[^A-Za-z0-9]/', '', $batch->batch_code) . '-' . date('d-M-Y-H-i-s') . '.pdf');
		$tempFile = storage_path() . '/app/reports/' . $customer_name . '/' . $filename;
		if (!is_dir(storage_path() . '/app/reports/' . $customer_name)) {
			mkdir(storage_path() . '/app/reports/' . $customer_name, 0755, true);
		}
		$pdf->save($tempFile);

		$batch->batch_report_url = '/reports/' . $customer_name . '/' . $filename;
		$publicReportPath = storage_path('app/public' . $batch->batch_report_url);
		if (!is_dir(dirname($publicReportPath))) {
			mkdir(dirname($publicReportPath), 0755, true);
		}
		$pdf->save($publicReportPath);
		$batch->save();

		// Save GCLA 02 Form PDF as a Batch Attachment so that it lists under Attachments tab!
		try {
			$attachmentTypeId = app(\App\Services\System\AttachmentTypeResolver::class)
				->resolveOrCreateAttachmentTypeId('GCLA 02 Form');
			
			$fallbackUser = \App\User::first();
			
			// Save database record in batch_attachments
			$attachment = new \App\BatchAttachment();
			$attachment->batch_id = $batch->id;
			$attachment->title = 'GCLA 02 Form - ' . ($language === 'sw' ? 'Kiswahili' : 'English');
			$attachment->attachment_url = $batch->batch_report_url;
			$attachment->attachment_type = $attachmentTypeId;
			$attachment->is_internal = 0;
			$attachment->show_on_coa = 0;
			$attachment->uploaded_by = auth()->id() ?? ($fallbackUser ? $fallbackUser->id : null);
			$attachment->save();
		} catch (\Exception $attEx) {
			\Log::error("Failed to auto-save GCLA 02 report as attachment: " . $attEx->getMessage());
		}

		if ($mergeWithAttachments && !empty($attachmentIds)) {
			$mergedContent = $this->mergeCoaWithAttachments($tempFile, $batch, $attachmentIds, $customer_name, $filename);
			@file_put_contents($tempFile, $mergedContent);
			@file_put_contents($publicReportPath, $mergedContent);
			return response($mergedContent, 200, ['Content-Type' => 'application/pdf']);
		}

		return $pdf->stream($filename);
	}

	private function processDCEA009Report($batch, $customer, $company, $report_logo, $stamp, $is_stamp, $filename, $customer_name, $mergeWithAttachments, $attachmentIds, $batch_approvers, $analysis_date, $report_type, $ammendment, $disclaimer, $date)
	{
		$language = request('gcla_language', 'sw');
		$isSwahili = $language === 'sw';

		// 1. Resolve DCEA Logo (prioritize company report_logo first, then tz_flag)
		$logoPath = null;
		if ($company && !empty($company->report_logo)) {
			$logoPath = $company->report_logo;
		}
		if (!$logoPath && $company) {
			$logoPath = $company->getReportLogoPath('tz_flag');
		}
		
		$dceaLogo = null;
		if ($logoPath) {
			if (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')) {
				$logoPath = parse_url($logoPath, PHP_URL_PATH) ?? $logoPath;
			}
			$cleanPath = ltrim($logoPath, '/');
			if (str_starts_with($cleanPath, 'storage/')) {
				$cleanPath = substr($cleanPath, 8);
			}
			
			$candidates = [
				public_path($logoPath),
				public_path('storage/' . $cleanPath),
				storage_path('app/public/' . $cleanPath),
				public_path($cleanPath),
				storage_path('app/companies/' . basename($cleanPath)),
				$logoPath
			];
			foreach ($candidates as $cand) {
				if (is_file($cand)) {
					$dceaLogo = $this->resolveImageAsDataUri($cand);
					break;
				}
			}
		}

		// Fallback to tanzanialogo dynamically from settings/defaults
		if (!$dceaLogo) {
			$dceaLogo = $this->getResolvedTanzaniaLogo($company);
		}

		// 2. Receipt Date details
		$receipt_time = $batch->receipt_date ? strtotime($batch->receipt_date) : time();
		$receipt_day = date('d', $receipt_time);
		$receipt_year = date('Y', $receipt_time);

		$monthsEn = [
			'01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', 
			'05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', 
			'09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
		];
		$monthsSw = [
			'01' => 'Januari', '02' => 'Februari', '03' => 'Machi', '04' => 'Aprili', 
			'05' => 'Mei', '06' => 'Juni', '07' => 'Julai', '08' => 'Agosti', 
			'09' => 'Septemba', '10' => 'Oktoba', '11' => 'Novemba', '12' => 'Disemba'
		];
		$monthNum = date('m', $receipt_time);
		$receipt_month_en = $monthsEn[$monthNum] ?? 'January';
		$receipt_month_sw = $monthsSw[$monthNum] ?? 'Januari';

		// 3. Address and Institutions details
		$institution = $company ? $company->name : 'Government Chemist Laboratory Authority';
		$receipt_place = $company ? ($company->location ?? ($company->address ?? 'Dar es Salaam')) : 'Dar es Salaam';
		
		$sending_institution = $customer ? $customer->name : 'Drug Control and Enforcement Authority';
		$sending_place = $customer ? ($customer->physical_address ?? ($customer->postal_address ?? ($customer->location ?? 'Dar es Salaam'))) : 'Dar es Salaam';

		// 3.1 Fetch fields from SampleSubmissionRequest
		$ssr = $batch->sampleSubmissionRequest;
		
		$officer_sending_samples = '.........................';
		$officer_bringing_samples = '.........................';
		$form_no = '...........';
		
		if ($ssr) {
			$officer_sending_samples = $ssr->submitting_officer_full_name ?? ($ssr->submitted_by_full_name ?? '.........................');
			$officer_bringing_samples = $ssr->submitted_by_full_name ?? '.........................';
			$form_no = $ssr->gcla_file_reference_number ?? ($ssr->case_no ?? '...........');
		}
		
		if ($officer_bringing_samples === '.........................' && $batch->sampling_officer_name) {
			$officer_bringing_samples = $batch->sampling_officer_name;
		}

		// 4. Resolve Exhibits Data and Findings
		$samples = \App\SampleDetails::where('sample_header_id', $batch->id)->get();
		$exhibits = [];
		$capturedBySample = \App\CapturedResult::query()
			->where('sample_header_id', $batch->id)
			->whereNotNull('result')
			->get()
			->groupBy('sample_detail_id');
		$exhibitsBySampleId = collect();
		if ($ssr) {
			$exhibitsBySampleId = \App\Models\SampleSubmissionRequestExhibit::query()
				->where('sample_submission_request_id', $ssr->id)
				->get()
				->keyBy('sample_detail_id');
		}

		foreach ($samples as $sample) {
			$capturedResults = $capturedBySample->get($sample->id, collect());

			$found = false;
			$drugType = 'N/A';
			$healthEffect = '-';
			$remarks = '';

			foreach ($capturedResults as $cr) {
				$val = strtolower(trim($cr->result));
				if (!empty($val) && $val !== 'absent' && $val !== 'negative' && $val !== 'nil') {
					$found = true;
					$analyteName = $cr->analyte_code ?? ($cr->analyte?->name ?? '');
					if ($drugType === 'N/A') {
						$drugType = $analyteName;
					} else {
						$drugType .= ', ' . $analyteName;
					}

					$analyteModel = $cr->analyte;
					if ($analyteModel && !empty($analyteModel->description)) {
						$healthEffect = $analyteModel->description;
					}
				}
			}

			if ($drugType === 'N/A') {
				$drugType = $batch->sample_type?->name ?? 'Narcotic Substance';
			}

			if ($healthEffect === '-' || empty($healthEffect)) {
				$lowerDrug = strtolower($drugType);
				if (str_contains($lowerDrug, 'heroin') || str_contains($lowerDrug, 'diacetylmorphine')) {
					$healthEffect = $isSwahili
						? "Kusababisha uraibu mkubwa, kukandamiza mfumo wa upumuaji, na kifo kikiasiliwa kupita kiasi."
						: "Causes severe addiction, respiratory depression, and death upon overdose.";
				} elseif (str_contains($lowerDrug, 'cocaine')) {
					$healthEffect = $isSwahili
						? "Kusisimua mfumo wa neva wa kati, kusababisha matatizo ya moyo na uraibu mkubwa."
						: "Stimulates central nervous system, causes cardiac complications and severe addiction.";
				} elseif (str_contains($lowerDrug, 'cannabis') || str_contains($lowerDrug, 'bangi') || str_contains($lowerDrug, 'tetrahydrocannabinol')) {
					$healthEffect = $isSwahili
						? "Kuharibu mtazamo wa akili, kuongeza mapigo ya moyo, na matatizo ya afya ya akili ya muda mrefu."
						: "Impairs cognitive perception, increases heart rate, and causes long-term mental health issues.";
				} else {
					$healthEffect = $isSwahili
						? "Kusababisha madhara makubwa ya kisaikolojia, uraibu, na uharibifu wa viungo vya mwili."
						: "Causes severe psychological impairment, addiction, and organic body organ damage.";
				}
			}

			// Resolve item description from exhibit tables if available
			$itemDescription = '';
			$ssrExhibit = $exhibitsBySampleId->get($sample->id);
			if ($ssrExhibit) {
				$itemDescription = $ssrExhibit->item_description;
			}
			if (empty($itemDescription)) {
				$itemDescription = $sample->comments ?? ($sample->barcode ?? ($sample->sample_code ?? ''));
			}

			$exhibits[] = [
				'found' => $found,
				'drug_type' => $drugType,
				'weight' => $sample->notes_body ?? ($sample->material_status ?? '1 Package'),
				'health_effect' => $healthEffect,
				'remarks' => $remarks,
				'description' => $itemDescription,
			];
		}

		// 5. Resolve user signatures
		$resolveUserSignature = function($user) {
			if (!$user) return null;
			$path = $user->signature_path ?? ($user->signature ?? null);
			if (!$path) return null;
			
			$candidates = [
				public_path($path),
				public_path('storage/' . $path),
				base_path('public/' . $path),
				base_path('public/storage/' . $path),
				storage_path('app/public/' . $path),
				$path
			];
			foreach ($candidates as $cand) {
				if (is_file($cand)) {
					return $this->resolveImageAsDataUri($cand);
				}
			}
			return null;
		};

		$examiningUser = null;
		if ($batch->specialist_analyst) {
			$examiningUser = $batch->specialist_analyst;
		} else {
			$custodyAnalyst = \App\ChainOfCustody::join('users as u', 'u.id', '=', 'chain_of_custodies.moved_out_by')
				->where('sample_header_id', $batch->id)
				->select('u.*')
				->where('workflow_stage', "Sample Analysis")
				->orderBy('chain_of_custodies.created_at', 'desc')
				->first();
			if ($custodyAnalyst) {
				$examiningUser = $custodyAnalyst;
			}
		}
		if (!$examiningUser) {
			$examiningUser = auth()->user() ?? \App\User::first();
		}
		$examiningName = $examiningUser ? $examiningUser->name : 'Dr. Elias S. Alute';
		$examiningTitle = $examiningUser ? ($examiningUser->designation ?? ($isSwahili ? 'Mkemia wa Serikali' : 'Government Analyst')) : ($isSwahili ? 'Mkemia wa Serikali' : 'Government Analyst');

		$certifyingUser = \App\ChainOfCustody::join('users as u', 'u.id', '=', 'chain_of_custodies.moved_out_by')
			->where('sample_header_id', $batch->id)
			->select('u.*')
			->where('workflow_stage', "Sample Approval")
			->orderBy('chain_of_custodies.created_at', 'desc')
			->first();
		if (!$certifyingUser) {
			$batchApprover = \App\BatchLabSectionApprover::where('batch_id', $batch->id)
				->where('show_report', 1)
				->where('status', 1)
				->first();
			if ($batchApprover) {
				$certifyingUser = \App\User::find($batchApprover->user_id);
			}
		}
		if (!$certifyingUser) {
			$certifyingUser = auth()->user() ?? \App\User::first();
		}
		$certifyingName = $certifyingUser ? $certifyingUser->name : 'Mwanahawa H. Msangi';
		$certifyingTitle = $certifyingUser ? ($certifyingUser->designation ?? ($isSwahili ? 'Mkemia Mkuu wa Serikali Anayethibitisha' : 'Certifying Government Analyst')) : ($isSwahili ? 'Mkemia Mkuu wa Serikali Anayethibitisha' : 'Certifying Government Analyst');

		$marked_numbers = $batch->reference_number ?? ($batch->case_id ?? '');
		if (empty($marked_numbers)) {
			$codes = [];
			foreach ($samples as $s) {
				$codes[] = $s->barcode ?? $s->sample_code;
			}
			$marked_numbers = implode(', ', array_unique(array_filter($codes)));
		}
		if (empty($marked_numbers)) {
			$marked_numbers = $batch->batch_code;
		}

		$exhibit_type = $batch->sample_type?->name;
		if (empty($exhibit_type)) {
			$types = [];
			foreach ($samples as $s) {
				if ($s->sample_type_id) {
					$st = \App\SampleType::find($s->sample_type_id);
					if ($st) {
						$types[] = $st->name;
					}
				}
			}
			$exhibit_type = implode(', ', array_unique(array_filter($types)));
		}
		if (empty($exhibit_type)) {
			$exhibit_type = $isSwahili ? 'Dawa za Kulevya' : 'Narcotic Substance';
		}

		$cert_time = time();
		$cert_day = date('d', $cert_time);
		$cert_year_short = date('y', $cert_time);
		$cert_year_full = date('Y', $cert_time);
		$cert_month_num = date('m', $cert_time);
		$cert_month_en = $monthsEn[$cert_month_num] ?? 'January';
		$cert_month_sw = $monthsSw[$cert_month_num] ?? 'Januari';

		$data = [
			'batch' => $batch,
			'customer' => $customer,
			'company' => $company,
			'language' => $language,
			'chemist_name' => $examiningName,
			'institution' => $institution,
			'receipt_day' => $receipt_day,
			'receipt_month_en' => $receipt_month_en,
			'receipt_month_sw' => $receipt_month_sw,
			'receipt_year' => $receipt_year,
			'receipt_place' => $receipt_place,
			'sending_institution' => $sending_institution,
			'sending_place' => $sending_place,
			'quantity' => count($samples),
			'marked_numbers' => $marked_numbers,
			'exhibit_type' => $exhibit_type,
			'seal_description' => $batch->sample_appearance_description ?? ($isSwahili ? 'Laki Rasmi ya Kufungia' : 'Official Sealing Wax'),
			'lab_no' => $batch->batch_code,
			'exhibits' => $exhibits,
			'examining_officer' => [
				'name' => $examiningName,
				'title' => $examiningTitle,
				'signature' => $resolveUserSignature($examiningUser),
			],
			'certifying_officer' => [
				'name' => $certifyingName,
				'title' => $certifyingTitle,
				'signature' => $resolveUserSignature($certifyingUser),
			],
			'certification_date' => date('d/m/Y', strtotime(getTodayDate())),
			'dcea_logo' => $dceaLogo,
			'form_no' => $form_no,
			'officer_sending_samples' => $officer_sending_samples,
			'officer_bringing_samples' => $officer_bringing_samples,
			'cert_day' => $cert_day,
			'cert_month_en' => $cert_month_en,
			'cert_month_sw' => $cert_month_sw,
			'cert_year_short' => $cert_year_short,
			'cert_year_full' => $cert_year_full,
			'cert_time' => $cert_time,
		];

		ini_set('max_execution_time', 300);
		$pdf = app('dompdf.wrapper');
		$pdf->getDomPDF()->set_option("enable_php", true);
		$pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
		$pdf->getDomPDF()->set_option("isFontSubsettingEnabled", true);

		$pdf = PDF::loadView('batch.attachments.dcea-009-form-pdf', $data);

		$filename = $filename ?? ($customer_name . '-DCEA009-' . preg_replace('/[^A-Za-z0-9]/', '', $batch->batch_code) . '-' . date('d-M-Y-H-i-s') . '.pdf');
		$tempFile = storage_path() . '/app/reports/' . $customer_name . '/' . $filename;
		if (!is_dir(storage_path() . '/app/reports/' . $customer_name)) {
			mkdir(storage_path() . '/app/reports/' . $customer_name, 0755, true);
		}
		$pdf->save($tempFile);

		$dceaReportUrl = '/reports/' . $customer_name . '/' . $filename;
		$publicReportPath = storage_path('app/public' . $dceaReportUrl);
		if (!is_dir(dirname($publicReportPath))) {
			mkdir(dirname($publicReportPath), 0755, true);
		}
		$pdf->save($publicReportPath);

		// Save database record in batch_attachments
		try {
			$attachmentTypeId = app(\App\Services\System\AttachmentTypeResolver::class)
				->resolveOrCreateAttachmentTypeId('DCEA 009 Form');
			
			$fallbackUser = \App\User::first();
			
			$attachment = new \App\BatchAttachment();
			$attachment->batch_id = $batch->id;
			$attachment->title = 'DCEA 009 Form - ' . ($language === 'sw' ? 'Kiswahili' : 'English');
			$attachment->attachment_url = $dceaReportUrl;
			$attachment->attachment_type = $attachmentTypeId;
			$attachment->is_internal = 0;
			$attachment->show_on_coa = 0;
			$attachment->uploaded_by = auth()->id() ?? ($fallbackUser ? $fallbackUser->id : null);
			$attachment->save();
		} catch (\Exception $attEx) {
			\Log::error("Failed to auto-save DCEA 009 report as attachment: " . $attEx->getMessage());
		}

		if ($mergeWithAttachments && !empty($attachmentIds)) {
			$mergedContent = $this->mergeCoaWithAttachments($tempFile, $batch, $attachmentIds, $customer_name, $filename);
			@file_put_contents($tempFile, $mergedContent);
			@file_put_contents($publicReportPath, $mergedContent);
			return response($mergedContent, 200, ['Content-Type' => 'application/pdf']);
		}

		return $pdf->stream($filename);
	}
}

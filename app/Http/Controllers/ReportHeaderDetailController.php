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
		Log::info('process_pdf_report called', [
			'batch_id' => $batch_id,
			'report_format' => $report_format,
			'include_pesticide' => $include_pesticide,
			'merge_with_attachments' => request()->boolean('merge_with_attachments'),
			'attachment_ids' => request('attachment_ids', ''),
		]);
		// Load logos as base64 data URIs so DomPDF can render them
		// without chroot restrictions or HTTP deadlocks.
		$report_logo = $this->resolveImageAsDataUri($this->resolveCompanyLogoPath());
		$sadc_logo   = $this->resolveImageAsDataUri(public_path('images/sadcas_logo.png'));
		$ilac_logo   = $this->resolveImageAsDataUri(public_path('images/ilac-logo.png'));
		// Use the same base64 data URI approach as the main report logo.
		$accreditation_logo = $this->resolveImageAsDataUri(public_path('images/sadc-ilac.jpeg'));
		$stamp       = $this->resolveImageAsDataUri(public_path('images/company_logo.png'));

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

		// Attachments merging options (passed via query parameters)
		$mergeWithAttachments = request()->boolean('merge_with_attachments');
		$attachmentIdsParam = (string) request('attachment_ids', '');
		$attachmentIds = array_values(array_filter(array_map('intval', explode(',', $attachmentIdsParam))));

		$qr_url = url('/storage/reports/' . $customer_name . '/' . $filename);
		$qrcode = base64_encode(QrCode::format('svg')->size(50)->errorCorrection('H')->generate($qr_url));

		$tempFiles = [];

		// Resolve report format: First try by ID, then fallback to report_code for legacy support
		$formatModel = null;
		$reportCode = null;

		if ($report_format === 'water_report' || (string) $report_format === 'water_report') {
			$reportCode = 'water_report';
		} else {
			$formatModel = ReportFormat::find((int) $report_format);

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

			$pdf = PDF::loadView('layouts.lab.reports.coa_formats.report_formats', compact('sample', 'company', 'qrcode', 'report_logo', 'sadc_logo', 'ilac_logo', 'batch_approvers', 'pdf', 'batch', 'disclaimer', 'customer', 'report_type', 'analysis_date', 'stamp', 'is_stamp', 'ammendment'));
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

		$allCapturedResults = CapturedResult::where('sample_header_id', $batch->id)->get();

		$parameters = CapturedResult::where('captured_results.sample_header_id', $batch->id)
			->select(
				'captured_results.id',
				'captured_results.analyte_id',
				'captured_results.analyte_code',
				'captured_results.analyte_accredited',
				'captured_results.reporting_unit_id',
				'captured_results.main_value',
				'sd.main_standard',
				'captured_results.method_id',
				'am.name as method_name',
				'am.code as method_code',
				'ru.name as reporting_unit_name',
				'a.name as analyte_name'
			)
			->join('sample_details as sd', 'sd.id', '=', 'captured_results.sample_detail_id')
			->leftJoin('analysis_methods as am', 'am.id', '=', 'captured_results.method_id')
			->leftJoin('reporting_units as ru', 'ru.id', '=', 'captured_results.reporting_unit_id')
			->leftJoin('analytes as a', 'a.id', '=', 'captured_results.analyte_id')
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
			->select('captured_results.*')
			->get()
			->groupBy('sample_detail_id');

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

			$conformity = ($sampleTotal > 0 && $samplePasses === $sampleTotal) ? 'Pass' : 'Fail';
			// Resolve sample code and sampling point for display in results tables
			$sampleDetail = \App\SampleDetails::with('sample_point')->find($sample->id);
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
		$data['reportFormat'] = $reportFormatModel;
		$data['grouped_samples'] = $groupedSamples;
		$data['ungrouped_samples'] = $ungroupedSamples;
		$data['sample_type_name'] = $sampleTypeName;

		ini_set('max_execution_time', 300);
		$pdf = app('dompdf.wrapper');
		$pdf->getDomPDF()->set_option("enable_php", true);
		$pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
		$pdf->getDomPDF()->set_option("isFontSubsettingEnabled", true);

		$pdf = PDF::loadView('layouts.lab.reports.dynamic_report', $data);

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
}

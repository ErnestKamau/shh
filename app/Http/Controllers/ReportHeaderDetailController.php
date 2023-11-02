<?php

namespace App\Http\Controllers;

use App\CapturedResult;
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
use App\BatchLabSectionApprover;
use App\SampleAnalysisDates;
use App\SampleAnalysisTypeRelationView;
use App\SamplesCategory;
use App\BatchAmmendment;
use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Support\Facades\Storage;
use PDF;
use PhpParser\PrettyPrinter\Standard;

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

	public function process_pdf_report($batch_id, $report_format)
	{
		// return response()->json('success3');
		$path = public_path('images/company_logo.png');
		$kenas = public_path('images/kenas_footer.jpg');
		$nema = public_path('images/nema_footer.jpg');
		$polucon_disclaimer = public_path('images/polucon_disclaimer.jpg');
		$polucon_disclaimer_not = public_path('images/polucon_disclaimer_not.jpg');
		$stamp = public_path('images/stamp.png');

		$batch = \App\SampleHeader::find($batch_id);
		$batch->processing_date = getTodayDate();
		$batch->in_ammendment_proccess = 0;
		$batch->save();
		$main_lab = implode(' ,',array_unique(SamplesCategory::where('sample_header_id', $batch->id)->pluck('main_lab_name')->toArray()));

		$ammendment = BatchAmmendment::where('batch_id',$batch->id)->where('version_number',$batch->is_amendment)->first();
		$report_type = '';
		$report_type = $batch->prelim_report_status == 1 ? 'PRELIM' : $report_type;
		$report_type = $batch->prelim_report_status == 2 ? 'DRAFT' : $report_type;
		
		$batch_approvers = BatchLabSectionApprover::where('batch_id', $batch->id)->where('show_report',1)->where('status', 1)->get();
		$is_stamp =  BatchLabSectionApprover::where('batch_id', $batch->id)->where('show_report',1)->where('status', 1)->where('batch_status','Sample Approval')->first();
		$analysis_date = SampleAnalysisDates::where('sample_header_id',$batch->id)->orderBy('start_analysis_date','DESC')->first();
		// return response()->json($analysis_date->start_analysis_date);

		$disclaimer = SystemConfiguration::where('key', 'lab_report_disclaimer_config')->first();
		$non_accredited = SystemConfiguration::where('key', 'lab_report_accreditted_config')->first();
		$status = $batch->status;

		$customer = CRMCustomer::find($batch->crm_customer_id);

		// $batch_view  = SampleResults::where('batch_id', $batch->id)->orderBy('analysis_level','asc')->orderBy('analyte_level','asc')->get();

		$customer_name = preg_replace('/[^A-Za-z0-9]/', '', $customer->name);
		if ($batch->document_number != '') {
			$filename = $customer_name . '-' . $batch->batch_code . '-' . date("d-M-Y-H-i-s") . '-' . $batch->document_number . '.pdf';
		} else {

			$filename = $customer_name . '-' . $batch->batch_code . '-' . date("d-M-Y-H-i-s") . '.pdf';
		}
		$filename = urlencode($filename);
		$company = getActiveCompany();
		// $date = date("d-M-Y", strtotime(getTodayDate()));
		$qr_url = url('/storage/reports/' . $customer_name . '/' . $filename);
		// return response()->json($batch_result,200);


		$qrcode = base64_encode(\QrCode::format('svg')->size(50)->errorCorrection('H')->generate($qr_url));
		$samples = SamplesCategory::where('sample_header_id', $batch->id)->get();
		if ($report_format == '1') {
			foreach ($samples as $sample) {
				$allCapturedResultsCount = CapturedResult::where('sample_detail_id',$sample->id)->where('sample_header_id',$batch->id)->get()->count();
				$isAccreditedCount = CapturedResult::where('sample_detail_id',$sample->id)->where('sample_header_id',$batch->id)->where('analyte_accredited',1)->get()->count();
				$sample['is_accreddited_status'] = $isAccreditedCount >= $allCapturedResultsCount/2 ? 1 : 0;
				$sample['getBrandOuts'] = [
					"normal" => SampleAnalysisTypeRelationView::where('brand_id', 0)->where('sample_detail_id',$sample->id)->where('batch_id',$sample->sample_header_id)->orderBy('analysis_level', 'DESC')->get(),
					"physical" => SampleAnalysisTypeRelationView::where('brand_id', 1)->where('sample_detail_id',$sample->id)->where('batch_id',$sample->sample_header_id)->orderBy('analysis_level', 'DESC')->get(),
					"pesticide" => SampleAnalysisTypeRelationView::where('brand_id', 2)->where('sample_detail_id',$sample->id)->where('batch_id',$sample->sample_header_id)->orderBy('analysis_level', 'DESC')->get(),
				];
			}
			// return response()->json($samples);
			ini_set('max_execution_time', 300); //300 seconds = 5 minutes
			$pdf = app('dompdf.wrapper');
			$pdf->getDomPDF()->set_option("enable_php", true);
			$pdf->setPaper('A4', 'portrait');
			$pdf = PDF::loadView('layouts.lab.reports.coa_formats.ktda_report', compact('samples', 'company', 'qrcode', 'path', 'kenas', 'batch_approvers', 'pdf', 'batch', 'non_accredited', 'disclaimer', 'nema','customer','analysis_date', 'polucon_disclaimer','stamp','is_stamp','ammendment','polucon_disclaimer_not','main_lab'));

			if (is_dir(storage_path() . '/app/reports/' . $customer_name)) {
				$pdf->save(storage_path() . '/app/reports/' . $customer_name . '/' . $filename);
			} else {
				$path = storage_path() . '/app/reports/' . $customer_name;
				// $pdf->save(storage_path() . '/app/reports/' . $customer_name . '/' . $filename);
				$check = mkdir($path);
				if ($check) {

					$pdf->save(storage_path() . '/app/reports/' . $customer_name . '/' . $filename);
				} else {
					return redirect()->back()->with('error', 'Error while creating customer storage folder');
				}
			}
			$batch->batch_report_url = '/reports/' . $customer_name . '/' . $filename;
			$batch->save();

			return 'success';
		}
		if ($report_format == '2') {
			foreach ($samples as $sample) {
				$allCapturedResultsCount = CapturedResult::where('sample_detail_id',$sample->id)->where('sample_header_id',$batch->id)->get()->count();
				$isAccreditedCount = CapturedResult::where('sample_detail_id',$sample->id)->where('sample_header_id',$batch->id)->where('analyte_accredited',1)->get()->count();
				$sample['is_accreddited_status'] = $isAccreditedCount >= $allCapturedResultsCount/2 ? 1 : 0;
				$sample['getBrandOuts'] = [
					"normal" => SampleAnalysisTypeRelationView::where('brand_id',0)->where('sample_detail_id',$sample->id)->where('batch_id',$sample->sample_header_id)->orderBy('analysis_level', 'DESC')->get(),
					"physical" => SampleAnalysisTypeRelationView::where('brand_id', 1)->where('sample_detail_id',$sample->id)->where('batch_id',$sample->sample_header_id)->orderBy('analysis_level', 'DESC')->get(),
					"pesticide" => SampleAnalysisTypeRelationView::where('brand_id', 2)->where('sample_detail_id',$sample->id)->where('batch_id',$sample->sample_header_id)->orderBy('analysis_level', 'DESC')->get(),
				];
			}
			// return response()->json($samples);
			ini_set('max_execution_time', 300); //300 seconds = 5 minutes 
			
			$pdf = app('dompdf.wrapper');
			$pdf->getDomPDF()->set_option("enable_php", true);
			$pdf = PDF::loadView('layouts.lab.reports.coa_formats.iran_report', compact('samples', 'company', 'qrcode', 'path', 'kenas', 'batch_approvers', 'pdf', 'batch', 'non_accredited', 'disclaimer','nema','customer','analysis_date', 'polucon_disclaimer','stamp','is_stamp','ammendment','polucon_disclaimer_not','main_lab'));

			if (is_dir(storage_path() . '/app/reports/' . $customer_name)) {
				$pdf->save(storage_path() . '/app/reports/' . $customer_name . '/' . $filename);
			} else {
				$path = storage_path() . '/app/reports/' . $customer_name;
				// $pdf->save(storage_path() . '/app/reports/' . $customer_name . '/' . $filename);
				$check = mkdir($path);
				if ($check) {

					$pdf->save(storage_path() . '/app/reports/' . $customer_name . '/' . $filename);
				} else {
					return redirect()->back()->with('error', 'Error while creating customer storage folder');
				}
			}
			$batch->batch_report_url = '/reports/' . $customer_name . '/' . $filename;
			$batch->save();

			return 'success';
		}

		foreach ($samples as $sample) {
			$allCapturedResultsCount = CapturedResult::where('sample_detail_id',$sample->id)->where('sample_header_id',$batch->id)->get()->count();
			$isAccreditedCount = CapturedResult::where('sample_detail_id',$sample->id)->where('sample_header_id',$batch->id)->where('analyte_accredited',1)->get()->count();
			$sample['is_accreddited_status'] = $isAccreditedCount >= $allCapturedResultsCount/2 ? 1 : 0;
		}
		// $samples = SamplesCategory::where('sample_header_id',$batch->id)->get();
		ini_set('max_execution_time', 300); //300 seconds = 5 minutes 
		$pdf = app('dompdf.wrapper');
		$pdf->getDomPDF()->set_option("enable_php", true);
		$pdf = PDF::loadView('layouts.lab.reports.coa_formats.standard_report', compact('samples', 'company', 'qrcode', 'path', 'kenas', 'batch_approvers', 'pdf', 'batch', 'non_accredited', 'disclaimer', 'nema','customer','report_type','analysis_date', 'polucon_disclaimer','stamp','is_stamp','ammendment','polucon_disclaimer_not','main_lab'));

		if (is_dir(storage_path() . '/app/reports/' . $customer_name)) {
			$pdf->save(storage_path() . '/app/reports/' . $customer_name . '/' . $filename);
		} else {
			$path = storage_path() . '/app/reports/' . $customer_name;
			// $pdf->save(storage_path() . '/app/reports/' . $customer_name . '/' . $filename);
			$check = mkdir($path);
			if ($check) {

				$pdf->save(storage_path() . '/app/reports/' . $customer_name . '/' . $filename);
			} else {
				return redirect()->back()->with('error', 'Error while creating customer storage folder');
			}
		}
		$batch->batch_report_url = '/reports/' . $customer_name . '/' . $filename;
		$batch->save();

		return 'success';
	}
}

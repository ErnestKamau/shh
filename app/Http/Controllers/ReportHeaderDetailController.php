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

	public function process_pdf_report($batch_id)
	{
		$path = public_path('images/aqua.jpg');
		$kenas = public_path('images/kenas.png');
		$tick = public_path('images/tick.png');

		$batch = \App\SampleHeader::find($batch_id);
		$batch->processing_date = getTodayDate();
		$batch->in_ammendment_proccess = 0;
		$batch->save();
		$qc_config_value = 0;
		if($batch->repeat_sample_id > 0){
			$qc_config  = SystemConfiguration::where('key','qc_percentage_config')->first();
			$qc_config_value = $qc_config->value;
		}

		$customer = CRMCustomer::find($batch->crm_customer_id);

		$report_params['VAR_BATCH_ID'] = $batch->id;
		$report_params['report_type'] = 'aqualytic_report_one';
		$report_params['report_name'] = 'aqualytic_report_one';
		$report_params['client_name'] = $customer->name;
		$report_params['client_code'] = $customer->code;
		$report_params['batch_code'] = $batch->batch_code;
		$report_params['report_date'] = date("d-M-Y", strtotime($batch->receipt_date));


		// $batch_view  = SampleResults::where('batch_id', $batch->id)->get();
		$batch_view  = SampleResults::where('batch_id', $batch->id)->orderBy('analysis_level','asc')->orderBy('analyte_level','asc')->get();
		// return response()->json($batch_view, 200);

		$batch_result = [];
		$responsible_personnel = [];
		foreach ($batch_view as $view) {
			// return response()->json($view,200);
			if($view->repeat_captured_id > 0){
				$captured_result = CapturedResult::find($view->captured_result_id);
				$view['repeat_result_range'] = $captured_result->repeatsampleresult;
				
			}
			if (isset($batch_result[$view->sample_code])) {
				
				
				// return response()->json($secondary_standard_analyte,200);
				$batch_result[$view->sample_code]['main_standard'] = $view->main_standard_code;
				$batch_result[$view->sample_code]['secondary_standard'] = $view->secondary_standard_code;


				array_push($batch_result[$view->sample_code]['samples'], $view);
			} else {
				$batch_result[$view->sample_code]['main_standard'] = $view->main_standard_code;
				$batch_result[$view->sample_code]['secondary_standard'] = $view->secondary_standard_code;
				$batch_result[$view->sample_code]['sample_type_name'] = $view->sample_type_name;
				$batch_result[$view->sample_code]['desc'] = $view->sample_description;
				
				$batch_result[$view->sample_code]['source'] = $view->sampling_point;
				$batch_result[$view->sample_code]['submit'] = $view->submit_by;
				$batch_result[$view->sample_code]['customer'] = $customer->name;
				$batch_result[$view->sample_code]['ammendment'] = $view->is_amendment;
				
				if ($view->ammendment_number > 1) {

					$batch_result[$view->sample_code]['sample'] = $view->sample_code . '-V' . $view->ammendment_number;
				} else {
					$batch_result[$view->sample_code]['sample'] = $view->sample_code;
				}
				$batch_result[$view->sample_code]['sampled_by'] = $view->sampling_officer_name;
				$batch_result[$view->sample_code]['sampling_date'] = $view->date_collected;
				$batch_result[$view->sample_code]['received'] = $view->receipt_date;
				$batch_result[$view->sample_code]['analysis_date'] = $view->processing_date;
				$batch_result[$view->sample_code]['report_issue'] = $view->approval_date;
				$batch_result[$view->sample_code]['comment'] = $view->header_body;
				
				$approve_sig_arr = explode('/',$view->approving_signature);
				$approve_sig_arr[1] = "app";
				$approve_sig = implode('/',$approve_sig_arr);

				$verify_sig_arr = explode('/',$view->verifying_signature);
				$verify_sig_arr[1] = "app";
				$verify_sig = implode('/',$verify_sig_arr);
				
				$responsible_personnel['approve_u'] = $view->approving_user;
				$responsible_personnel['approve_p'] = $view->approving_position;
				$responsible_personnel['approve_s'] = $view->approving_signature != '' ? storage_path(). $approve_sig : '';
				$responsible_personnel['verify_u'] = $view->verifying_user;
				$responsible_personnel['verify_p'] = $view->verifying_position;
				
				$responsible_personnel['verify_s'] = $view->verifying_signature != '' ?storage_path(). $verify_sig :'';
				// return response()->json($view->verifying_signature,200);
				
				$batch_result[$view->sample_code]['analysis'] = $view->analysis_type_name;

				$user = getUserById($batch->approve_user_id);
				$view->approve_user = $user->name ?? '';
				$batch_result[$view->sample_code]['samples'] = [];
				array_push($batch_result[$view->sample_code]['samples'], $view);
			}
		}
		$customer_name =preg_replace('/[^A-Za-z0-9]/', '', $customer->name);
		if ($batch->document_number != '') {
			$filename = $customer_name . '-' . $batch->batch_code . '-' . date("d-M-Y", strtotime(getTodayDate())) . '-' . $batch->document_number . '.pdf';
		} else {

			$filename = $customer_name . '-' . $batch->batch_code . '-' . date("d-M-Y", strtotime(getTodayDate())) . '.pdf';
		}
		$filename = urlencode($filename);
		$company = getActiveCompany();
		$date = date("d-M-Y", strtotime(getTodayDate()));
		$qr_url = url('/storage/reports/' . $customer_name . '/' . $filename);
		// return response()->json($batch_result,200);


		$qrcode = base64_encode(\QrCode::format('svg')->size(50)->errorCorrection('H')->generate($qr_url));

		ini_set('max_execution_time', 300); //300 seconds = 5 minutes 
		// return view('layouts.lab.reports.print.print_process_result',compact('batch_result','company','date','qrcode','path','kenas','tick','responsible_personnel'));
		// return response()->json($batch_result,200);
		$pdf = app('dompdf.wrapper');
		$pdf->getDomPDF()->set_option("enable_php", true);
		$pdf = PDF::loadView('layouts.lab.reports.print.print_process_result', compact('batch_result', 'company', 'date', 'qrcode', 'path', 'kenas', 'tick', 'responsible_personnel','pdf','batch','qc_config_value'));

		if (is_dir(storage_path() . '/app/reports/' . $customer_name)) {
			$pdf->save(storage_path() . '/app/reports/' . $customer_name . '/' . $filename);
		} else {
			$path = storage_path() . '/app/reports/' . $customer_name;

			$check = mkdir($path);
			if ($check) {

				$pdf->save(storage_path() . '/app/reports/' . $customer_name . '/' . $filename);
			} else {
				return redirect()->back()->with('error', 'Error while creating customer storage folder');
			}
		}
		$batch->batch_report_url = '/reports/' . $customer_name . '/' . $filename;
		$batch->save();
		$detailType = array("App\SampleHeader", "App\CRMCustomer");
		// return response()->json('test');

		return 'success';
	}
	
}

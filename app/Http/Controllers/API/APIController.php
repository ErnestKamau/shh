<?php

namespace App\Http\Controllers\API;

use Auth;
use App\User;
use App\InventoryItem as ITEM;
use App\InventorySubCategories as ISC;
use App\SampleDetails;
use App\SampleHeader;
use App\CapturedResult;
use App\Result;
use App\Models\CRM\CRMCustomer;
use App\AnalysisType;
use PDF;
use App\LabReportScheduler;
use App\Models\System\SystemConfiguration;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use App\Event;
use App\CalendarEventsNotification;

class APIController extends Controller
{
	// public function __construct()
	// {
	//   $this->middleware('auth');
	// }

	public function items_available(Request $request, $item_id, $brand_id = 0, $request_id = 0)
	{
		$items = ITEM::where('inventory_sub_category_id', $item_id)->selectRaw('SUM(stock_in) as stock_in, SUM(stock_out) as stock_out, item_brand_id')
			->groupBy('item_brand_id');

		$ccs = [];
		$storeIDs = collect();

		// Non-specific brand is submitted as 0 / "0" / empty; real brands are UUIDs.
		if (filled($brand_id) && ! in_array((string) $brand_id, ['0', ''], true)) {
			$items = $items->where('item_brand_id', $brand_id);
		}

		if (filled($request_id) && ! in_array((string) $request_id, ['0', ''], true)) {
			$entity = \App\RequestEntity::find($request_id);

			if ($entity) {
				$ccs = array_map('trim', explode(',', (string) $entity->cost_center));

				if (\Illuminate\Support\Facades\Schema::hasTable('store_to_cost_centers')) {
					$store_ids = \App\StoreToCostCenter::whereIn('cost_center', $ccs);

					if ($entity->request_type == 'Request to Store') {
						$store_ids = $store_ids->join('inventory_stores as s', 's.id', 'store_to_cost_centers.store_id');
						$store_ids = $store_ids->where('s.is_frozen', 0);
					}

					$storeIDs = $store_ids->get()->pluck('store_id')->filter()->values();

					// Match getAvailableStockByCostCenter: only restrict when mappings exist.
					// An empty whereIn() would incorrectly report 0 available stock.
					if ($storeIDs->isNotEmpty()) {
						$items = $items->whereIn('inventory_store_id', $storeIDs);
					}
				}
			}
		}

		$totalItems = 0;

		$created_at = \Carbon\Carbon::parse($request->created_at);

		foreach ($items->get() as $item) {
			$available = floatval($item->stock_in) - floatval($item->stock_out);
			$totalItems += $available;
		}

		$leadTime = ISC::find($item_id)->total_lead_time() ?? 0;

		$delivery_date = $created_at->addDays($leadTime == 0 ? 7 : $leadTime);

		return json_encode([
			'cc' => $ccs,
			'stores' => $storeIDs,
			'formatted' => number_format($totalItems, 2),
			'value' => $totalItems,
			'delivery_date' => $delivery_date,
		]);
	}
	private function process_results($batch_id, $internal = false)
	{
		app(\App\Services\Sampleworkflow\ProcessedResultSyncService::class)->syncBatch((string) $batch_id);

		return 'success';
	}

	private function process_pdf_report($batch_id)
	{
		$path = public_path('images/syngenta_flowe.png');
		$kenas = public_path('images/kenas.png');

		$batch = \App\SampleHeader::find($batch_id);
		$batch->processing_date = getTodayDate();
		$batch->in_ammendment_proccess = 0;
		$batch->save();

		$customer = CRMCustomer::find($batch->crm_customer_id);

		$report_params['VAR_BATCH_ID'] = $batch->id;
		$report_params['report_type'] = 'aqualytic_report_one';
		$report_params['report_name'] = 'aqualytic_report_one';
		$report_params['client_name'] = $customer->name;
		$report_params['client_code'] = $customer->code;
		$report_params['batch_code'] = $batch->batch_code;
		$report_params['report_date'] = date("d-M-Y", strtotime($batch->receipt_date));


		$batch_view  = SampleDetails::where('sample_header_id', $batch->id)->get();

		$batch_result = [];
		foreach ($batch_view as $view) {

			$analysis_ids = explode(',', $view->analysis_type_id);
			$analysis_arr = [];
			foreach ($analysis_ids as $id) {
				$analysis = AnalysisType::find(intval($id));
				if (isset($analysis->id)) {
					array_push($analysis_arr, $analysis->name);
				}
			}
			$view->analysis_type_name = implode(',', $analysis_arr);

			$view->results_arr = explode(',', $view->results);
		}
		if ($batch->document_number != '') {
			$filename = $customer->name . '-' . $batch->batch_code . '-' . date("d-M-Y", strtotime(getTodayDate())) . '-' . $batch->document_number . '.pdf';
		} else {

			$filename = $customer->name . '-' . $batch->batch_code . '-' . date("d-M-Y", strtotime(getTodayDate())) . '.pdf';
		}
		$filename = urlencode($filename);
		$customer = getCrmCustomerByID($batch->crm_customer_id);
		$sample_type = getSampleTypeByID($batch->sample_type_id);
		$verify_user = getUserById($batch->verify_user_id);
		$approve_user = getUserById($batch->approve_user_id);

		$batch_result['verify_user_name'] = isset($verify_user->id) ? $verify_user->name : 'N/a';
		$batch_result['approve_user_name'] = isset($approve_user->id) ? $approve_user->name : 'N/a';
		$batch_result['approve_user_position'] = isset($approve_user->id) ? getModulePreConfigById($approve_user->position)->name : 'N/a';
		$batch_result['verify_user_position'] = isset($verify_user->id) ? getModulePreConfigById($verify_user->position)->name : 'N/a';
		if (isset($verify_user->id)) {
			$v_pos = explode('/', $verify_user->electronic_sig);
			$v_pos[1] = 'app';
			$v_path = storage_path() . implode('/', $v_pos);
		} else {
			$v_path = '';
		}
		if (isset($approve_user->id)) {
			$a_pos = explode('/',  $approve_user->electronic_sig);
			$a_pos[1] = 'app';
			$a_path = storage_path() . implode('/', $a_pos);
		} else {
			$a_path = '';
		}


		$batch_result['verify_user_sig'] = $v_path;
		$batch_result['approve_user_sig'] = $a_path;
		$batch_result['sample_type_name'] = isset($sample_type->id) ? $sample_type->name : 'N/a';

		$company = getActiveCompany();
		$date = date("d-M-Y", strtotime(getTodayDate()));
		$qr_url = url('/storage/reports/' . $customer->name . '/' . $filename);
		// return response()->json($batch_view,200);


		$qrcode = base64_encode(\QrCode::format('svg')->size(50)->errorCorrection('H')->generate($qr_url));

		ini_set('max_execution_time', 1000); //300 seconds = 5 minutes
		$water_ = SystemConfiguration::where('key', 'water_analysis_id')->first();
		// return response()->json(floatval($batch->sample_type_id) == floatval($water_->value));
		if(floatval($batch->sample_type_id) == floatval($water_->value)){
			// return response()->json('test');
			$pdf = PDF::loadView('layouts.lab.reports.print.print_lab_water_report', compact('batch_result', 'customer', 'batch_view', 'company', 'batch', 'date', 'qrcode', 'path', 'kenas'))->setPaper('a4', 'landscape');
		}else{
			// return response()->json('test2');
			$pdf = PDF::loadView('layouts.lab.reports.print.print_process_result', compact('batch_result', 'customer', 'batch_view', 'company', 'batch', 'date', 'qrcode', 'path', 'kenas'))->setPaper('a4', 'landscape');
		}
		// return view('layouts.lab.reports.print.print_process_result',compact('batch_result','company','date','qrcode','path','kenas'));
		$customer_name =preg_replace('/[^A-Za-z0-9]/', '', $customer->name);
		if (is_dir(storage_path() . '/app/reports/' . $customer_name)) {
			$pdf->save(storage_path() . '/app/reports/' . $customer_name . '/' . $filename);
		} else {
			$path = storage_path() . '/app/reports/' . $customer_name;
			// return $path;
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



		// return response()->json($batch->report_header_details(),200);


		return 'success';
	}
	public function labReportsSchedulerJob(){
		$jobs = LabReportScheduler::where('is_pending',1)->get();
		// return response()->json($jobs);
		//
		foreach($jobs as $job){
			// ini_set(“memory_limit”,”16M“);
			$this->process_results($job->batch_id);
			$test = $this->process_pdf_report($job->batch_id);
			// return response()->json($test);
			$job->in_process = 1;
			$job->is_pending = 0;
			$job->save();
			$job->in_process = 0;
			$job->is_complete = 1;
			$job->save();
			// $message = 'Hi '.$job->requester_name.', Batch '.$job->batch_code.' for '.$job->customer_name.' lab report has been generated successfully!';
			// $subject = '[ Batch-'.$job->batch_code.' Lab Report Updated ]';
			// notify_user($message,$job->requester_email,$subject);
		}
		return 'success';
	}
	public function send_event_notifications()
	{
		$today_date = getTodayDate();
		$company = getActiveCompany();
		$events = Event::where('start_date', '>=', $today_date)->where('has_notification', 1)->where('notification_sent', 0)->get();
		foreach ($events as $event) {
			$notifications = CalendarEventsNotification::where('calendar_event_id', $event->id)->get();
			foreach ($notifications as $n) {
				if ($n->is_sent == 0) {
					$required_date = $event->start_date . ' ' . $event->start_time;
					$required_time = strtotime($required_date);
					$reduction = ' - ' . $n->duration . ' ' . $n->rate;
					$check_date = date('Y-m-d H:i:s', strtotime($required_date . $reduction));
					$balance = strtotime($check_date) - time();
					if ($balance < 180) {
						$subject = '[' . $company->name . '] Event Notification - ' . $event->title;
						$users = explode(',', $event->responsible_id);
						foreach ($users as $user) {
							$target_user = getUserById((int) $user);

							$body = 'Hi ' . $target_user->name . ' ,<br><br>
                                        This is a notification reminder for the <b>' . $event->title . '</b> calendar event.<br><br>
                                        The event is scheduled to start ' . $required_date . '.<br>
                                        Kindly avail your self.<br><br>
                                        Regards,<br>
                                        ' . $company->name . '.';
							notify_user($body, $target_user->email, $subject);
						}
						$n->is_sent = 1;
						$n->save();
						$event->notification_sent = 0;
					}
				}
			}
			$event->save();
		}
		return response()->json('succcess', 200);
	}
}

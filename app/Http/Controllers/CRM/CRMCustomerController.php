<?php

namespace App\Http\Controllers\CRM;

use App\Country;
use App\ModulePreConfigs;
use App\User;
use App\SampleHeader;
use App\SampleDetails;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\SamplePoint;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\CustomerCertification;
use App\Models\Lab\Qualification;
use App\Models\CRM\Complaint;
use App\Models\CRM\Complaint_Type;
use App\Models\CRM\CustomerFeedback;
use App\ZohoCustomers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CRMCustomerReportInfoColumn;
use App\Models\CustomerSubmissionFormColumn;
use App\Models\System\SystemConfiguration;
use App\QuotationHeader;
use App\SampleType;
use App\Http\Requests\CRM\StoreCustomerRequest;
use App\Http\Requests\CRM\UpdateCustomerRequest;
use App\Http\Requests\CRM\UpdateCustomerConfigurationsRequest;
use App\Http\Requests\CRM\UpdateCustomerLabelRequest;
use App\Services\CRM\CRMCustomerService;

class CRMCustomerController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
	}

	public function checkConfig(){
		return redirect()->back()->with('error','Kindly set the Account Settings configuration');
	}
	public function index()
	{
		return view('layouts.crm.customer-list');
	}

	public function add(StoreCustomerRequest $request, CRMCustomerService $service)
	{
		try {
			$service->create($request->validated());
			return redirect()->back()->with('success', 'Customer added.');
		} catch (\RuntimeException $e) {
			return redirect()->back()->with('error', $e->getMessage());
		}
	}

	public function show($id)
	{
		return view('layouts.crm.customer-show-wrapper', ['customerId' => (string) $id]);
	}

	public function edit_label(UpdateCustomerLabelRequest $request, $id, CRMCustomerService $service)
	{
		$service->updateLabel((string) $id, $request->validated('column'), $request->validated('name'));
		return redirect()->back()->with('success', 'Configuration Saved.');
	}


	public function edit(UpdateCustomerRequest $request, $id, CRMCustomerService $service)
	{
		$service->update((string) $id, $request->validated());
		return redirect()->back()->with('success', 'Customer edited.');
	}

	public function editConfigurations(UpdateCustomerConfigurationsRequest $request, $id, CRMCustomerService $service)
	{
		$service->updateConfigurations((string) $id, $request->validated());
		return redirect()->back()->with('success', 'Configurations saved.');
	}

  public function add_to_users(){
	  $all_clients = CustomerContact::all();
	//   return response()->json($all_clients, 200);
	  foreach($all_clients as $client){
		  $new_user = new User();
		  $new_user->name = $client->first_name." ".$client->middle_name." ".$client->last_name;
		  $new_user->password =  bcrypt('test1234');
		  $new_user->email = $client->email;
		  $new_user->company_id = $client->company_id;
		  $new_user->is_client = 1;
		  $new_user->client_id = $client->crm_customer_id;
		  $new_user->save();
		  $client->can_login = 1;
		  $client->save();
		}
		return redirect()->route('home');
  }
  public function fetch_client_quote(Request $request){
  	  $client = CRMCustomer::find($request->client_id);
	  if(isset($client->id)){
		  $quotes = QuotationHeader::where('crm_customer_id',$client->id)->get();
		  
		  return response()->json($quotes,200);
	  }else{
		  return response()->json($request->client_id,200);
	  }
  }
	public function delete_customer(Request $request, CRMCustomerService $service)
	{
		try {
			$service->softDelete((string) $request->customer_id);
			return redirect()->back()->with('success', 'Crm Customer deleted successfully!');
		} catch (\RuntimeException $e) {
			return redirect()->back()->with('error', $e->getMessage());
		}
	}
  public function validateCrmCustomerNameAjax($name){
	$namearr = explode(' ',$name);
	$tit = $namearr[0];
	$customers = CRMCustomer::where('name','LIKE', "%".$tit."%")->get();
	return response()->json($customers);
  }

  public function batch_reports(){
	$sample_types = SampleType::where('active',1)->get();
	$customers = CRMCustomer::where('active',1)->get();
	
	// Get all company units for filter dropdown
	$company_units = CRMCompanyUnit::where('active', 1)
		->with('customer')
		->orderBy('name')
		->get();
	
	// Get all sample points for filter dropdown (avoid orderBy name: column may be absent on some DBs)
	$sample_points = SamplePoint::where('active', 1)
		->with(['unit.customer', 'crmSamplePoint'])
		->orderBy('id')
		->get();
	
	return view('layouts.crm.crm_batch_report', compact('sample_types','customers', 'company_units', 'sample_points'));
  }

  public function batch_report_data(Request $request){
	$query = SampleHeader::with(['client', 'sample_type', 'samples','crmunit'])
		->select('sample_headers.*')
		->where('sample_headers.status', 'Completed'); // Only show completed batches

	// Apply date range filter
	if ($request->filled('date_from')) {
		$query->where('sample_headers.receipt_date', '>=', $request->date_from);
	}
	if ($request->filled('date_to')) {
		$query->where('sample_headers.receipt_date', '<=', $request->date_to);
	}

	// Apply customer filter
	if ($request->filled('customer_filter') && is_array($request->customer_filter)) {
		$query->whereIn('sample_headers.crm_customer_id', $request->customer_filter);
	}

	// Apply sample type filter
	if ($request->filled('sample_type_filter') && is_array($request->sample_type_filter)) {
		$query->whereIn('sample_headers.sample_type_id', $request->sample_type_filter);
	}

	// Apply company unit filter
	if ($request->filled('company_unit_filter') && is_array($request->company_unit_filter)) {
		$query->whereIn('sample_headers.crm_unit_id', $request->company_unit_filter);
	}

	// Apply sample point filter - this requires joining with sample_details
	if ($request->filled('sample_point_filter') && is_array($request->sample_point_filter)) {
		$query->whereExists(function($q) use ($request) {
			$q->select(\DB::raw(1))
			  ->from('sample_details')
			  ->whereRaw('sample_details.sample_header_id = sample_headers.id')
			  ->whereIn('sample_details.sample_point_id', $request->sample_point_filter);
		});
	}

	// Apply sample number filter
	if ($request->filled('sample_number_filter')) {
		$searchTerm = $request->sample_number_filter;
		$query->where(function($q) use ($searchTerm) {
			$q->where('sample_headers.batch_code', 'LIKE', "%{$searchTerm}%")
			  ->orWhere('sample_headers.reference_number', 'LIKE', "%{$searchTerm}%")
			  ->orWhere('sample_headers.document_number', 'LIKE', "%{$searchTerm}%");
		});
	}

	$batches = $query->orderBy('sample_headers.receipt_date', 'desc')->get();

	$formattedData = $batches->map(function($batch) {
		// Get sample codes for this batch
		$sampleCodes = $batch->samples->pluck('sample_code')->toArray();
		$sampleCodesStr = implode(', ', array_unique($sampleCodes));
		
		// Get unique sample points for this batch
		$samplePoints = $batch->samples()
			->with('sample_point')
			->whereNotNull('sample_point_id')
			->get()
			->pluck('sample_point.name')
			->filter()
			->unique()
			->toArray();
		$samplePointsStr = implode(', ', $samplePoints);
		
		return [
			'id' => $batch->id,
			'batch_code' => $batch->batch_code,
			'customer_name' => $batch->client ? $batch->client->name : 'N/A',
			'crm_unit_name' => $batch->crmunit ? $batch->crmunit->name : 'N/A',
			'sample_type_name' => $batch->sample_type ? $batch->sample_type->name : 'N/A',
			'receipt_date' => $batch->receipt_date ? date('M d, Y', strtotime($batch->receipt_date)) : 'N/A',
			'date_collected' => $batch->date_collected ? date('M d, Y', strtotime($batch->date_collected)) : 'N/A',
			'status' => $batch->status,
			'reference_number' => $batch->reference_number,
			'document_number' => $batch->document_number,
			'batch_report_url' => $batch->batch_report_url,
			'priority' => $batch->priority,
			'description' => $batch->description,
			'sample_codes' => $sampleCodesStr ?: 'N/A',
			'sample_points' => $samplePointsStr ?: 'N/A'
		];
	});

	return response()->json(['data' => $formattedData]);
  }

  public function batch_report_export(Request $request){
	$query = SampleHeader::with(['client', 'sample_type', 'samples','crmunit'])
		->select('sample_headers.*')
		->where('sample_headers.status', 'Completed'); // Only export completed batches

	// Apply the same filters as data method
	if ($request->filled('date_from')) {
		$query->where('sample_headers.receipt_date', '>=', $request->date_from);
	}
	if ($request->filled('date_to')) {
		$query->where('sample_headers.receipt_date', '<=', $request->date_to);
	}
	if ($request->filled('customer_filter') && is_array($request->customer_filter)) {
		$query->whereIn('sample_headers.crm_customer_id', $request->customer_filter);
	}
	if ($request->filled('sample_type_filter') && is_array($request->sample_type_filter)) {
		$query->whereIn('sample_headers.sample_type_id', $request->sample_type_filter);
	}
	
	// Apply company unit filter
	if ($request->filled('company_unit_filter') && is_array($request->company_unit_filter)) {
		$query->whereIn('sample_headers.crm_unit_id', $request->company_unit_filter);
	}

	// Apply sample point filter
	if ($request->filled('sample_point_filter') && is_array($request->sample_point_filter)) {
		$query->whereExists(function($q) use ($request) {
			$q->select(\DB::raw(1))
			  ->from('sample_details')
			  ->whereRaw('sample_details.sample_header_id = sample_headers.id')
			  ->whereIn('sample_details.sample_point_id', $request->sample_point_filter);
		});
	}
	
	if ($request->filled('sample_number_filter')) {
		$searchTerm = $request->sample_number_filter;
		$query->where(function($q) use ($searchTerm) {
			$q->where('sample_headers.batch_code', 'LIKE', "%{$searchTerm}%")
			  ->orWhere('sample_headers.reference_number', 'LIKE', "%{$searchTerm}%")
			  ->orWhere('sample_headers.document_number', 'LIKE', "%{$searchTerm}%");
		});
	}

	$batches = $query->orderBy('sample_headers.receipt_date', 'desc')->get();

	// Generate Excel file
	$fileName = 'batch_report_' . date('Y-m-d_H-i-s') . '.xlsx';
	$filePath = storage_path('app/public/exports/' . $fileName);

	// Ensure directory exists
	if (!file_exists(dirname($filePath))) {
		mkdir(dirname($filePath), 0755, true);
	}

	// Create Excel file using PhpSpreadsheet or similar library
	// For now, we'll create a CSV file as a fallback
	$csvData = [];
	$csvData[] = [
		'Batch Code',
		'COA Report',
		'Sample Code',
		'Sample Type',
		'Customer',
		'Customer Company Unit',
		'Sample Points',
		'Receipt Date',
		'Date Collected',
		'Status',
		'Reference Number',
		'Document Number',
		'Priority',
		'Description'
	];

	foreach ($batches as $batch) {
		// Get sample codes for this batch
		$sampleCodes = $batch->samples->pluck('sample_code')->toArray();
		$sampleCodesStr = implode(', ', array_unique($sampleCodes));
		
		// Get unique sample points for this batch
		$samplePoints = $batch->samples()
			->with('sample_point')
			->whereNotNull('sample_point_id')
			->get()
			->pluck('sample_point.name')
			->filter()
			->unique()
			->toArray();
		$samplePointsStr = implode(', ', $samplePoints);
		
		$csvData[] = [
			$batch->batch_code,
			$batch->batch_report_url ?: 'N/A',
			$sampleCodesStr ?: 'N/A',
			$batch->sample_type ? $batch->sample_type->name : 'N/A',
			$batch->client ? $batch->client->name : 'N/A',
			$batch->crmunit ? $batch->crmunit->name : 'N/A',
			$samplePointsStr ?: 'N/A',
			$batch->receipt_date,
			$batch->date_collected,
			$batch->status,
			$batch->reference_number,
			$batch->document_number,
			$batch->priority,
			$batch->description
		];
	}

	// Write CSV file
	$file = fopen($filePath, 'w');
	foreach ($csvData as $row) {
		fputcsv($file, $row);
	}
	fclose($file);

	// Return download URL
	$downloadUrl = asset('storage/exports/' . $fileName);
	return response()->json(['download_url' => $downloadUrl]);
  }
  
}

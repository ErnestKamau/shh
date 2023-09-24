<?php

use App\Models\System\SystemConfiguration;
use Illuminate\Support\Facades\DB;

use App\Supplier;
use App\SampleAnalysisTypeRelationView;
use App\InterLabLog;

function myCurl($url, $payload)
{
	$ch = curl_init($url);
	# Setup request to send json via POST.
	$payload = $payload;
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
	# Return response instead of printing.
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	# Send request.
	$result = json_decode(curl_exec($ch), true);
	curl_close($ch);

	return $result;
}

function textBetween($str, $starting_word, $ending_word)
{
	$subtring_start = strpos($str, $starting_word);
	//Adding the strating index of the strating word to
	//its length would give its ending index
	$subtring_start += strlen($starting_word);
	//Length of our required sub string
	$size = strpos($str, $ending_word, $subtring_start) - $subtring_start;
	// Return the substring from the index substring_start of length size
	return substr($str, $subtring_start, $size);
}

function getMethods()
{
	return App\AnalysisMethod::where('active',1)->where('is_sampling_method',0)->get();
}

function getCurrentDate()
{
	return \Carbon\Carbon::now();
}

function getUserLicenses()
{
	return array("shared_user" => "Shared", "named_user" => "Named");
}

function systemVariables($typ)
{
	$variables = array(
		"internal_supplier_id" => 4,
		"lab_samples_category_id" => 20003,
		"procurement_department" => 10006,
		"inter_store_department_id" => 10012,
		"stock_taking_department_id" => 4
	);

	return $variables[$typ];
}

function getCompanyDetails()
{
	return array(
		"name" => "Imara LIMS",
		"logo" => "/images/imara-sys.png"
	);
}
function getAllUsers()
{
	return App\User::where('is_client', 0)->where('supplier_id', 0)->where('active', 1)->where('is_support_staff', 0)->get();
}
function getBatchAmmendmentsById($id)
{
	$ammendments =  App\BatchAmmendment::where('batch_id', $id)->get();

	foreach ($ammendments as $a) {
		$s = json_decode($a->samples, true);
		$p = array_keys($s);
		$a->sample_name = implode(',', $p);
	}
	return $ammendments;
}
function getCompanyById($id)
{
	return App\Company::find($id);
}
function sigFig($value, $digits = 3)
{
	if ($value == 0) {
		$decimalPlaces = $digits - 1;
	} elseif ($value < 0) {
		$decimalPlaces = $digits - floor(log10($value * -1)) - 1;
	} else {
		$decimalPlaces = $digits - floor(log10($value)) - 1;
	}

	$answer = round($value, $decimalPlaces);
	return $answer;
}

function getRequisitionWorkflowTotals()
{
	$requests = App\RequestEntity::selectRaw('count(id) as count, request_type')
		->where('inventory_location_id', getCurrentUserLocation()->id)
		->groupBy('request_type')->get();

	$arr = array();

	foreach ($requests as $req) {
		$arr[$req->request_type] = $req->count;
	}

	return $arr;
}
function getSupplierRatings()
{
	$user = auth()->user();
	$supplier = App\Supplier::find($user->supplier_id);
	return $supplier;
}
function getRfqsById($id)
{
	return App\RequestEntity::find($id);
}
function getSupplierQouteById($id)
{
	return App\SupplierQuote::find($id);
}
function checkSupplierQuote($id)
{
	return App\SupplierQuote::where('supplier_id', auth()->user()->id)->where('request_item_id', $id)->get();
}
function getStoreById($id)
{
	return App\InventoryStore::find($id);
}
function getSupplierRfqs($id)
{
	return App\RequestEntity::where('supplier_id', $id)->get();
}
function getSupplierlpos($id)
{
	return App\SupplierQuote::where('supplier_id', $id)->where('is_awarded', 1)->get();
}
function getSupplierGoodReceipt($id)
{
	return App\RequestEntity::where('request_type', 'Goods Receipt')->where('supplier_id', $id)->get();
}
function getSlotById($id)
{
	return App\InventoryStoreSlot::find($id);
}
function getInventorySubCatByID($id)
{

	return App\InventorySubCategories::find($id);
}
function getInventoryItemById($id)
{
	return App\InventoryItem::find($id);
}
function getRfqsItemById($id)
{
	return App\RequestEntityItem::find($id);
}
function getStatus()
{
	return array('Active', 'Obsolete', 'Out Of Service');
}


function getRequisitionWorkflow()
{
	return array("Material Requisition", "Request for Quotation", "Purchase Orders", "Goods Receipt", "Goods Return");
}

function getRequestToStoreWorkflow()
{
	return array("Request to Store", "Material Issuance");
}

function getConvoID($id)
{
	$convo_id1 = App\Conversation::where('from_user_id', auth()->user()->id)->where('to_user_id', $id)->get();
	$convo_id2 = App\Conversation::where('from_user_id', $id)->where('to_user_id', auth()->user()->id)->get();

	if (isset($convo_id1[0]->id)) {
		return $convo_id1[0]->id;
	} elseif (isset($convo_id2[0]->id)) {
		return $convo_id2[0]->id;
	} else {
		return 0;
	}
}
function checkNewUserMessage($convo_id)
{
	$messages = App\ChatMessage::where('conversation_id', $convo_id)->where('is_new', 1)->where('from_user_id', '!=', auth()->user()->id)->get();
	return $messages;
}

function getStorageByType($type)
{
	$stores = \App\InventoryStore::join('inventory_store_slots as iss', 'iss.inventory_store_id', '=', 'inventory_stores.id')
		->where('inventory_stores.type_of_store', $type)->selectRaw('inventory_stores.id as store_id,  inventory_stores.name as store_name, iss.name as slot_name, iss.id as slot_id')
		->orderBy('store_name', 'asc')->orderBy('slot_name', 'asc')->get();

	$storesList = array();

	foreach ($stores as $s) {
		if (!isset($storesList[$s->store_name])) {
			$storesList[$s->store_name] = array(
				"name" => $s->store_name,
				"id" => $s->store_id,
				"items" => array()
			);
		}

		$storesList[$s->store_name]["items"][$s->slot_id] = $s->slot_name;
	}

	return $storesList;
}

function getAttachmentTypes()
{
	return array("Document File", "Image File", "Video File", "GR - Invoice", "GR - Delivery Note", "GR - Credit Note");
}

function getRequestPriority()
{
	return array("Normal", "Medium", "High");
}

function clear_underscore($str)
{
	return ucwords(implode(" ", (explode("_", $str))));
}

function getInventoryItemClassification($id = false)
{
	$classifications = array("1" => "Stock", "2" => "Non-Stock", "3" => "Service");
	if ($id !== false) {
		return $classifications[$id] ?? 'Not-Set';
	}
	return $classifications;
}

function pendingApprovals()
{
	$status = "Pending";
	return \App\EntityApproval::join('request_entities as re', 're.id', '=', 'entity_approvals.model_id')
		->join('approvals as a', 'a.id', 'entity_approvals.approval_id')
		->where('entity_approvals.user_id', \Auth::user()->id)
		->where('entity_approvals.inventory_location_id', getCurrentUserLocation()->id)
		->where('entity_approvals.status', $status)->get();
}

function getCurrency()
{
	return array("KES", "USD");
}

function getNoteTypes()
{
	return array("Rejection", "General Note", "Supplier Awarded");
}

function getStageApprovals($type, $entity)
{
	// $configID = getConfigByName('ammendment_approval_id')[0];
	return App\Approvals::where('for', $type)->where('stage', $entity)
		->where('inventory_location_id', getCurrentUserLocation()->id)
		// ->where('id', '!=', $configID->id)
		->orderBy('level', 'asc')->get();
}

function imageTobase64($link, $public=false){
	// return $link;
	$image = new \App\Http\Controllers\HomeController;
	return $image->get_file($link, $public);
}


function getEntityApprovals($model, $model_id)
{
	return App\EntityApproval::where('model', $model)->where('model_id', $model_id)
		->where('inventory_location_id', getCurrentUserLocation()->id)->get();
}

function getNotes($model, $model_id)
{
	return App\EntityNote::where('model', $model)->where('model_id', $model_id)->get();
}

function getAttachments($model, $model_id)
{
	return App\EntityAttachment::where('model', $model)->where('model_id', $model_id)->get();
}

function getAllCurrencies($name)
{
	return  App\ModulePreConfigs::where('type', $name)->get();
}
function getCurrencyById($id)
{
	return App\ModulePreConfigs::find($id);
}
function getfrequency($frequency)
{
	if ($frequency == 1) {
		return 'Daily';
	} elseif ($frequency == 7) {
		return 'Weekly';
	} elseif ($frequency == 30) {
		return 'Monthly';
	} elseif ($frequency == 365) {
		return 'Annualy';
	} else {
		return '-';
	}
}

function getUoMConverstion($quantity, $uom1, $uom2)
{
	if ($uom2 == $uom1) {
		return $quantity;
	}

	$uoms = [$uom1, $uom2];
	$conversion_exists = \App\UoMConversion::whereIn('uom1', $uoms)->whereIn('uom2', $uoms)->first();

	if (!isset($conversion_exists->ratio)) {
		return false;
	}

	$rate = $conversion_exists->ratio;

	if ($conversion_exists->uom1 == $uom1) {
		return $quantity * $rate;
	} else {
		return $quantity / $rate;
	}
}

function getEventHistory($id)
{
	return App\EventHistory::where('event_id', $id)->orderBy('id', 'desc')->get();
}
function convert_currency($price, $currency1, $currency2)
{
	$conversion = \App\CurrencyConversion::where('currency_1', $currency1)->where('currency_2', $currency2)
		->first();

	if ($conversion && isset($conversion->ratio)) {
		$ratio = floatval($conversion->ratio);
	} else {
		$conversion = \App\CurrencyConversion::where('currency_1', $currency2)->where('currency_2', $currency1)
			->first();
		$ratio = 1 / floatval($conversion->ratio);
	}

	return isset($ratio) ? (floatval($price) * $ratio) : $price;
}

function getCurrencies()
{
	$config = "Currency";
	$module = "Inventory-Management";
	return \App\ModulePreConfigs::where('type', $config)->where('module', $module)->get();
}

function getLocations()
{
	return App\InventoryLocation::where('company_id', getUserCompany())->orderBy('level', 'asc')->get();
}

function notify_user($body, $email, $subject, $file = false, $bcc = false,$bcc_emails =[])
{
	$mailData = array(
		'contacts' => $email,
		'body' => $body,
		'subject' => $subject
	);

	$mailer = new App\Http\Controllers\MailController;

	return $mailer->html_email($mailData, 'default', $file, $bcc,$bcc_emails);
}
function getExpertin()
{
	$general = App\Models\System\SystemConfigurationsType::where('configuration_type', 'General Configuration')->first();
	if (isset($general->id)) {
		return App\Models\System\SystemConfiguration::where('configuration_type_id', $general->id)->where('key', 'report_email_footer')->first();
	}
}
function getsystemconfigbyid($id)
{
	return App\Models\System\SystemConfiguration::find($id);
}
function getBccEmails()
{
	$general = App\Models\System\SystemConfigurationsType::where('configuration_type', 'General Configuration')->first();
	if (isset($general->id)) {
		$emails = App\Models\System\SystemConfiguration::where('key', 'report_bcc_emails')->where('configuration_type_id', $general->id)->first();
		if (isset($emails->id)) {
			$empty = explode(',', $emails->value);
			return $empty;
		} else {

			return $empty = [];
		}
	} else {

		return $empty = [];
	}
}

function getInventoryItems($type = 0, $not = false, $location = false)
{
	if (!$location) {
		$location = getCurrentUserLocation()->id;
	}
	if ($type > 0) {
		if ($not) {
			$items = App\InventorySubCategories::where('company_id', getUserCompany())
				->where('inventory_category_id', '!=', $type)
				->where('location_id', $location)->orderBy('name', 'asc')->get();
		} else {
			$items = App\InventorySubCategories::where('company_id', getUserCompany())
				->where('inventory_category_id', $type)
				->where('location_id', $location)->orderBy('name', 'asc')->get();
		}
	} else {
		$items = App\InventorySubCategories::where('company_id', getUserCompany())
			->where('location_id', $location)->orderBy('name', 'asc')->get();
	}

	$response = ["items" => [], "brands" => []];
	$itemIDs = [];
	foreach ($items as $it) {
		$itemIDs[] = $it->id;
	}

	$brands = \App\ItemBrand::whereIn('inventory_sub_category_id', $itemIDs)->orderBy('inventory_sub_category_id', 'asc')->get();

	foreach ($brands as $b) {
		if (!isset($response['brands'][$b->inventory_sub_category_id])) {
			$response['brands'][$b->inventory_sub_category_id] = [];
		}

		$response['brands'][$b->inventory_sub_category_id][] = $b;
	}

	foreach ($items as $i) {
		$i->item_brands = $response['brands'][$i->id] ?? [];
		$response['items'][] = $i;
	}
	return $response['items'];
}

function getRoleCertificationByID($id)
{
	return App\Models\Personnel\RoleCertification::find($id);
}
function getconfigByID($id)
{
	return App\Models\System\SystemConfiguration::where('configuration_type_id', $id)->get();
}
function getConfigByName($name)
{
	return App\Models\System\SystemConfiguration::where('key', $name)->get();
}
function getComplaintsInWorkflow($id)
{
	if ($id == 0) {
		return 0;
	}
	$total = App\Models\CRM\Complaint::all();

	$count = 0;

	foreach ($total as $item) {
		if ($item->complaint_workflow == $id) {
			++$count;
		}
	}
	return $count;
}
function getStandards()
{
	return App\Standards::all();
}
function getStandardValues()
{
	return App\StandardValue::all();
}
function getStandardByid($id)
{
	return App\Standards::find($id);
}
function getStandardValuebyID($id)
{
	return App\StandardValue::find($id);
}

function getComplaintNotesTotal($id)
{
	$notes = App\Models\CRM\Complaintnotes::where('complaint_id', $id)->get();
	$count = 0;
	foreach ($notes as $note) {
		++$count;
	}
	return $count;
}
function getComplaintAttachmentTotal($id)
{
	$attachments = App\Models\CRM\Complaintattachment::where('complaint_id', $id)->get();
	$count = 0;
	foreach ($attachments as $attachment) {
		++$count;
	}
	return $count;
}
function getComplaintsResolutionTotal($id)
{
	$resolutions = App\Models\CRM\Complaintsresolutions::where('complaint_id', $id)->get();
	$count = 0;
	foreach ($resolutions as $resolution) {
		++$count;
	}
	return $count;
}
function getUserById($user_id)
{
	return App\User::find($user_id);
}
function getOperators()
{
	return App\User::where('is_client', 0)->where('supplier_id', 0)->where('active', 1)->where('is_support_staff', 0)->get();
}

function updateContractStatus($contract_id, $status)
{
	$contract = \App\SupplierContract::find($contract_id);
	$contract->status = $status;
	$contract->save();

	return true;
}

function getUserStores($location = false, $includeFrozen = false, $item_id = false, $isRequisition=false)
{
  if (!$location) {
    $location = getCurrentUserLocation()->id;
  }

  $stores = App\InventoryStore::where('inventory_stores.inventory_location_id', $location);

  if (!$includeFrozen) {
    $stores = $stores->where('inventory_stores.is_frozen', '0');
  }

  $stores = $stores->join('inventory_store_slots as iss', 'iss.inventory_store_id', '=', 'inventory_stores.id')
    ->leftJoin('inventory_items as ii', function($join) use($item_id){
      $join->on('ii.inventory_store_id', '=', 'inventory_stores.id');
      $join->on('iss.id', '=', 'ii.inventory_store_slot_id');
      if($item_id){
        $join->where('ii.inventory_sub_category_id', $item_id);
      }
    })
    ->selectRaw('inventory_stores.id, inventory_stores.name, iss.name as slot_name, iss.id as slot_id, SUM(stock_in) as stock_in, SUM(stock_out) as stock_out, (SUM(stock_in) - SUM(stock_out)) as balance')
    ->groupBy('inventory_stores.name');

  if($isRequisition){
    $stores = $stores->groupBy('iss.name')->orderBy('slot_name', 'asc');
  }

  $tores = $stores->orderBy('balance', 'desc');

  // if ($item_id && $item_id > 0) {
  //  $stores = $stores->join('inventory_items as ii', 'ii.inventory_store_id', '=', 'inventory_stores.id')
  //    ->join('inventory_store_slots as iss', function ($join) {
  //      $join->on('iss.id', '=', 'ii.inventory_store_slot_id');
  //      $join->on('iss.inventory_store_id', '=', 'inventory_stores.id');
  //    })
  //    ->where('ii.inventory_sub_category_id', $item_id)
  //    ->selectRaw('inventory_stores.id, inventory_stores.name, iss.name as slot_name, ii.inventory_store_slot_id as slot_id, SUM(stock_in) as stock_in, SUM(stock_out) as stock_out')->groupBy('inventory_stores.id', 'inventory_stores.name', 'iss.name', 'ii.inventory_store_slot_id')
  //    ->havingRaw('SUM(stock_in) - SUM(stock_out) > 0')->orderBy('iss.name', 'asc');
  // }

  return $stores->orderBy('inventory_stores.name', 'asc')->get();
}

function getDepartments()
{
	return App\InventoryDepartment::where('company_id', getUserCompany())
		->where('location_id', getCurrentUserLocation()->id)
		->orderBy('name', 'asc')->get();
}

function getModulePreconfig($type, $module)
{
	return App\ModulePreConfigs::where('type', $type)->where('module', $module)->orderBy('name', 'asc')->get();
}

function getSampleWorflowStages()
{
	return array("All Samples", "Samples En-Route", "Samples Reception", "Samples Request Review", "Samples In Lab", "Sample Verification", "Sample Approval", "Reports In Payment", "Reports for Collection");
}
function getComplaintWorkflowStages()
{
	return array("All Complaints", "Open Complaints", "Complaints Approval", "Complaints Resolution", "Resolution Approval", "Closed Complaints", "Cancelled Complaints");
}
function getComplaintsWorkFlowValues()
{
	return array(
		"All Complaints" => 0,
		"Open Complaints" => 1,
		"Complaints Approval" => 2,
		"Complaints Resolution" => 3,
		"Resolution Approval" => 4,
		"Closed Complaints" => 5,
		"Cancelled Complaints" => 6
	);
}
function getComplaintWorkflow()
{
	return array(
		0 => "All Complaints",
		1 => "Open Complaints",
		2 => "Complaints Approval",
		3 => "Complaints Resolution",
		4 => "Resolution Approval",
		5 => "Closed Complaints",
		6 => "Cancelled Complaints"
	);
}
function getComplaintsActionsApproval()
{
	return array(
		1 => "Request Complaints Approval",
		2 => "Approve Compliant",
		3 => "Request Resolution Approval",
		4 => "Approve Resolution"
	);
}

function mamboSawa($licenseType = false)
{
	$license = \Auth::user()->available_license();

	return $licenseType ? $license[$licenseType] : $license;
}

function getAllComplaintsOrder()
{
	return App\Models\CRM\Complaint::all()->sortBy('priority');
}
function getComplaintsByWorkflow($stage)
{
	return App\Models\CRM\Complaint::where('complaint_workflow', $stage)->orderBy('id', 'desc')->get();
}
function getAllComplaints()
{
	$complaints = App\Models\CRM\Complaint::all();
	$count = 0;
	foreach ($complaints as $item) {
		++$count;
	}
	return $count;
}
function getComplaintActionReverse()
{
	return array(
		2 => "Return complaint to open complaints",
		4 => "Return resolution to complaints resolution"
	);
}
function getComplaintById($id)
{
	return App\Models\CRM\Complaint::find($id);
}

function getDisposalReason()
{
	return \App\DisposalReason::orderBy('description', 'asc')->get();
}

function addDisposalReason($r)
{
	$reason = \App\DisposalReason::where('description', $r)->first() ?? new \App\DisposalReason;
	$reason->description = $r;
	$reason->save();

	return $reason;
}

function getRequestTypes()
{
	$requests = \App\RequestType::all();
	$response = array();
	foreach ($requests as $req) {
		if (!isset($response[$req->visible])) {
			$response[$req->visible] = array();
		}
		$response[$req->visible][] = $req;
	}

	return $response;
}

function getWorkflowStage_Stages($workflow)
{
	return \App\SampleAnalysisStage::where('sample_workflow', $workflow)->where('company_id', getUserCompany())->orderBy('level', 'asc')->get();
}

function getSampleWorkFLowTotals()
{
	$batches = App\SampleHeader::selectRaw('count(id) as count, status')->groupBy('status')->where('isactive',1)->get();
	$arr = array("All Samples" => 0);

	foreach ($batches as $batch) {
		$arr[$batch->status] = $batch->count;
		$arr['All Samples'] += intval($batch->count);
	}

	return $arr;
}
function getSampleTypeQualificationById($id)
{
	return App\Models\Lab\Qualification::find($id);
}

function getSampleHeaderByID($id)
{
	return App\SampleHeader::find($id);
}

function getSampleCategoryByBatch($id)
{
	return App\SamplesCategory::where('batch_code', $id)->get();
}

function getSampleTypeByID($id)
{
	return App\SampleType::find($id);
}

function getSystemConfiguration($name)
{
	return App\Models\System\SystemConfiguration::where('key', $name)->get();
}
function getConfigTypeByName($name)
{
	return App\Models\System\SystemConfigurationsType::where('configuration_type', $name)->first();
}
function getDefaultCompany()
{
	return App\Company::where('active', 1)->get();
}

function getAnalysisTypeByID($id, $is_arr = false)
{
	if ($is_arr) {
		return App\AnalysisType::whereIn('id', $id)->get();
	}
	return App\AnalysisType::where('id', $id)->get();
}
function getAnalysisTypeID($id)
{
	return App\AnalysisType::find($id);
}
function getAnalysisTypes()
{
	return App\AnalysisType::all();
}

function getSamplePointByid($id)
{
	return DB::table('sample_points')->where('id', $id)->first();
}

function getCompanyProdut($id)
{
	return App\Models\CRM\CompanyProduct::find($id);
}

function getSamplelAllConditions()
{
	return App\SampleCondition::all();
}

function getAllCompanyproduct()
{
	return App\Models\CRM\CompanyProduct::all();
}


function getAllSamplePoints()
{
	return App\Models\CRM\SamplePoint::all();
}

function getTodayDate()
{
	return date('Y-m-d');
}

function getNotesReminderTypes()
{
	return array("Note to Self", "Non-Conformity Issue", "Reminder",);
}

function getRoutineFrequency()
{
	return array(
		"1" => "Daily",
		"7" => "Weekly",
		"30" => "Monthly",
		"91" => "Quarterly",
		"182" => "Semi-Annually",
		"365" => "Annually"
	);
}

function getSampleDateTypes()
{
	return array('Login Date', 'Target Date', 'To Lab Date', 'Data Import Date', 'Processing Date', 'Approval Date', 'Email Date', 'Payment Date');
}
function getSampleDetailById($id)
{
	return App\SampleDetails::find($id);
}
function getCustomerSampleHeader($id)
{
	return App\SampleHeader::where('crm_customer_id', $id)->get();
}
function getCrmCustomerContacts($id)
{
	return App\Models\CRM\CustomerContact::where('crm_customer_id', $id)->where('active', 1)->where('receive_invoice', 1)->get();
}
function getCrmCustomerContactById($id)
{
	return App\Models\CRM\CustomerContact::find($id);
}
function getSampleLab($id)
{
	return  App\SampleHeader::where('status', 'Samples In Lab')->where('crm_customer_id', $id)->join('sample_details', 'sample_details.sample_header_id', '=', 'sample_headers.id')->get('sample_details.*')->count();
}
function getInvoiceDetails($id)
{
	return App\InvoiceDetails::where('invoice_id', $id)->get();
}

function getBatchNotificationUser()
{
	return App\BatchNotification::where('position_id', auth()->user()->position)->where('active', 1)->orderBy('id', 'desc')->get();
}

function getTotaltaxAmount($id)
{
	$taxes = App\InvoiceDetails::where('invoice_id', $id)->pluck('tax_amount')->toarray();
	return array_sum($taxes);
}
function getPricelistCurrency($id)
{
	$invoice = App\Invoice::find($id);
	$pricelist =  App\Pricelist::find($invoice->pricelist_id);
	return App\ModulePreConfigs::find($pricelist->currency_id);
}
function getPricelistByID($id)
{
	return App\Pricelist::find($id);
}
function space_underscore($str, $sep = '_')
{
	return implode($sep, explode(' ', strtolower($str)));
}

function getPageAttachments($url)
{
	return App\EntityAttachment::where('model', $url)->orderBy('title', 'asc')->get();
}

function getReportingUnits()
{
	return App\ReportingUnit::orderBy('name', 'asc')->get()->toArray();
}

function getSuppliers($items = [])
{
	if (count($items) == 0) {
		return App\Supplier::orderBy('name')->where('inventory_location_id', getCurrentUserLocation()->id)->get();
	} else {
		$supplierIDs = App\SupplierCategory::whereIn('inventory_sub_category_id', $items)->pluck('supplier_id')->toArray();

		$supplierIDs = array_unique($supplierIDs);

		$suppliers = App\Supplier::orderBy('suppliers.name')->where('inventory_location_id', getCurrentUserLocation()->id)
			->whereIn('id', $supplierIDs)->get();

		return $suppliers;
	}
}

function getEquipment()
{
	return App\Models\Equipments\Equipment::orderBy('name')->get();
}
function getEquipmentById($id)
{
	return App\Models\Equipments\Equipment::find($id);
}
function getCompanyUsers()
{
	$users =  App\User::where('active', 1)->where('is_client', 0)->where('supplier_id', 0)->where('id', '!=', auth()->user()->id)->get();
	foreach ($users as $user) {
		$chats = App\ChatMessage::where('to_user_id', auth()->user()->id)->where('from_user_id', $user->id)->where('is_new', 1)->get();
		$user['chats'] = $chats->count();
	};
	return $users;
}

function getEvents()
{
	return App\Event::all();
}
function getEventNotification($id)
{
	return App\CalendarEventsNotification::where('calendar_event_id', $id)->get();
}
function getUserEvents()
{
	return App\Event::where('responsible_id', auth()->user()->id)->get();
}
function getUserChats()
{
	$chats = App\ChatMessage::where('to_user_id', auth()->user()->id)->where('is_new', 1)->get();
	return $chats;
}
function getUsers($all = false)
{
	if ($all) {
		return App\User::orderBy('name')->where('company_id', getUserCompany())->where('active', 1)->where('is_support_staff', 0)->where('is_client', 0)->where('supplier_id', 0)->get();
	}
	return App\User::orderBy('name')->where('location_id', getCurrentUserLocation()->id)
		->where('company_id', getUserCompany())->where('active', 1)->where('is_support_staff', 0)->where('is_client', 0)->where('supplier_id', 0)->get();
}

function getUsersByRole($role, $is_id = false)
{
	$users = App\User::orderBy('name')->join('user_roles as ur', 'ur.user_id', '=', 'users.id')
		->join('roles as r', 'r.id', '=', 'ur.role_id');
	if ($is_id) {
		$users = $users->where('r.id', $role)->selectRaw('users.*');
	} else {
		$users = $users->where('r.name', $role)->selectRaw('users.*');
	}

	$users = $users->where('users.company_id', getUserCompany())->where('users.active', 1)->where('users.is_support_staff', 0)->get();

	return $users;
}

function getComplaintChainOfCustody($id)
{
	return App\Models\CRM\Chain_of_Custody_Complaint::where('complaint_id', $id)->get();
}
function getComplaintAttachment($id)
{
	return App\Models\CRM\Complaintattachment::where('complaint_id', $id)->get();
}
function getComplaintNotes($id)
{
	return App\Models\CRM\Complaintnotes::where('complaint_id', $id)->get();
}
function getComplaintsResolutions($id)
{
	return App\Models\CRM\Complaintsresolutions::where('complaint_id', $id)->get();
}


function getRoles()
{
	return App\Role::orderBy('name')->where('company_id', getUserCompany())->get();
}

function getNotifiableUsers()
{
	//for now return users. To be changed to users for lab only based on personnel module
	return App\User::orderBy('name')->where('active', 1)->where('is_support_staff', 0)->get();
}

function getRestockNotifications($alertTop = false, $count = false)
{
	$sub_categories = App\InventorySubCategories::join('inventory_categories as c', 'c.id', '=', 'inventory_sub_categories.inventory_category_id')
		->where('c.inventory_location_id', getCurrentUserLocation()->id)->where('requires_reorder', 1)
		->where("c.category_type", "!=", "is_lab_samples")
		->selectRaw('inventory_sub_categories.*');

	if ($count) {
		return $sub_categories->get()->count();
	}

	if ($alertTop) {
		$sub_categories = $sub_categories->limit(10);
	}

	$sub_categories = $sub_categories->get();


	$restockNo = array();

	// return \json_encode($sub_categories);

	foreach ($sub_categories as $sub) {
		$restockNo[] = array(
			"category" => $sub->inventory_category_id,
			"subcategory" => $sub->inventory_sub_category_id,
			"url_name" => $sub->name,
			"alert_url" => route('show-inventory-items', ['category' => $sub->inventory_category_id, 'id' => $sub->id])
		);
	}

	return array("items" => $restockNo, "count" => count($restockNo));
}
function getEquipmentLogs()
{
	return array("Maintainance-Log", "Calibrate-Log", "Repair-Log", "Verification-Log", "Operator-Log");
}
function getInventoryDepartmentByid($id)
{
	return App\InventoryDepartment::find($id);
}

function getSampleTrackingStages()
{
	return App\SampleAnalysisStage::all();
}
function getRepairLogParts($id)
{
	return App\Models\Equipments\PartsRepaired::where('log_id', $id)->where('is_delete', 0)->get();
}

function getModulePermissions()
{
	return array(
		"Laboratory" => array(
			"permission" => false,
			"components" => array_merge(array_diff(getSampleWorflowStages(), array("All Samples")), array("Analytes", "Labs", "Sample-Types", "Reporting-Units", "Methods", "Sample-Tracking-Stages", "Analysis Types", "Proforma Invoices", "Tax Regime", "Pricelists", "Quotation", "Approve For Analysis", "Generate Invoice", "RFT Form","Qc Sample"))
		),
		"Inventory" => array(
			"permission" => false,
			"components" => array_merge(getRequestToStoreWorkflow(), array_merge(getRequisitionWorkflow(), array("General Requisition", "Categories", "Inventory-Movement", "Departments", "Suppliers", "Store", "Stock-Taking", "Stock-Transfer", "Configuration", "Approval-Requests")))
		),
		"Equipment" => array(
			"permission" => false,
			"components" => array_merge(array("Equipment-List", "Asset-Type", "Asset-Location"), getEquipmentLogs())
		),

		"CRM" => array(
			"permission" => false,
			"components" => array(
				"Customer-List", "Station", "Location", "Material", "Company-Units", "Sample-Points", "Products", "Contacts", "Results", "Details", "Certificates", "Complaints", "Feedbacks", "All Complaints",
				"Open Complaints", "Complaints Approval", "Complaints Resolution", "Resolution Approval", "Closed Complaints", "Cancelled Complaints", "Complaint Type", "Customer Feedback"
			)
		),
		"Personnel" => array(
			"permission" => false,
			"components" => array("Personnel", "Departments", "Roles", "Organizational Structure", "Audit Trail", "Configurations")
		),
		"Sampling-Planner" => array(
			"permission" => false,
			"components" => array("All Events")
		)
	);
}

function getCompanies()
{
	return App\Company::orderBy('name')->get();
}
function getActiveCompany()
{
	return App\Company::where('active', 1)->first();
}

function getClients($location = false)
{
	return App\Models\CRM\CRMCustomer::orderBy('name')->where('active', 1)->get();
}

function getAssetTypes()
{
	return App\Models\Assets\AssetType::all();
}
function getAssetLocation()
{
	return App\Models\Assets\AssetLocation::all();
}
function getAssetTypeById($id)
{
	return App\Models\Assets\AssetType::find($id);
}
function getAssetLocationByid($id)
{
	return App\Models\Assets\AssetLocation::find($id);
}
function getCrmCustomerByID($id)
{
	return App\Models\CRM\CRMCustomer::find($id);
}
function getSampleConditionByID($id)
{
	return App\SampleCondition::find($id);
}
function getSupplierByID($id)
{
	return App\Supplier::find($id);
}

function getSampleTypes()
{
	return App\SampleType::orderBy('name')->get();
}
function getInventorySubs()
{

	$config  = App\Models\System\SystemConfiguration::where('key', 'inventory_sub_category_configuration_id')->first();
	if (isset($config->id)) {
		$subs = App\Models\System\SystemConfiguration::where('configuration_type_id', $config->value)->get();
		return $subs;
	} else {
		return array();
	}
}
function getInvoiceBatches($id)
{
	return App\SampleHeader::where('invoice_id', $id)->get();
}
function getAnalyteStandardValue($analyte_id, $standard_id)
{
	return App\StandardAnalytes::where('analyte_id', $analyte_id)->where('standard_id', $standard_id)->first();
}
function getAnalyteElement($analyte_id, $analysis_type_id)
{
	return App\AnalysisElements::where('analyte_id', $analyte_id)->where('analysis_type_id', $analysis_type_id)->first();
}
function getUserCompany()
{
	$company_id = \Session::get('company_id');

	return $company_id ?? \Auth::user()->company_id;
}

function getSampleDetailResultByID($id)
{
	return App\Result::where('sample_detail_id', $id)->get();
}

function getAnalyteByID($id)
{
	return App\Analyte::find($id);
}

function hasUnitName($unit, $unitstr)
{
	$parts = explode(",", $unitstr);

	return in_array($unit, $parts);
}

function pad_str($str, $padCount)
{
	return str_pad($str, $padCount, "0", STR_PAD_LEFT);
}

function getNamingConventionCode($model, $name, $requiredName = '')
{
	if ($model == "Customers") {
		$nameString = "C" . substr($name, 0, 1);
	}

	$defaultPadding = 4;

	if ($requiredName == "S0513-") {
		$defaultPadding = 5;
	}

	if ($requiredName != '') {
		$nameString = $requiredName;
	}

	$namingConV = App\NamingConvensionConsensus::where('string_part', $nameString)->where('model', $model)->first();
	if ($model == 'Samples') {
		$header = App\SampleHeader::latest('id')->first();
		$nameInteger = 0;
		if ($namingConV && isset($namingConV->string_part)) {
			$nameInteger = intval("15001") + $header->id + 1;
			$nameInteger = str_pad($nameInteger, $defaultPadding, "0", STR_PAD_LEFT);
		} else {
			$namingConV = new App\NamingConvensionConsensus;
			$namingConV->string_part = $nameString;
			$namingConV->model = $model;
			$namingConV->company_id = getUserCompany();
		}
		$namingConV->integer_part = $nameInteger;
		$namingConV->save();

		return $nameString . "" . $nameInteger;
	}


	$nameInteger = "15001";

	if ($namingConV && isset($namingConV->string_part)) {
		$nameInteger = intval($namingConV->integer_part) + 1;
		$nameInteger = str_pad($nameInteger, $defaultPadding, "0", STR_PAD_LEFT);
	} else {
		$namingConV = new App\NamingConvensionConsensus;
		$namingConV->string_part = $nameString;
		$namingConV->model = $model;
		$namingConV->company_id = getUserCompany();
	}
	$namingConV->integer_part = $nameInteger;
	$namingConV->save();

	return $nameString . "" . $nameInteger;
}
function getInvoiceById($id)
{
	return App\Invoice::find($id);
}
function getUserLocations()
{
	$user_locations = App\InventoryLocationUser::join('inventory_locations as l', 'l.id', '=', 'inventory_location_users.inventory_location_id')
		->selectRaw('l.name, l.id')->where('user_id', \Auth::user()->id)->orderBy('l.level', 'asc')->get();

	$location_ids = array();

	foreach ($user_locations as $loc) {
		$location_ids[] = $loc->id;
	}

	return $location_ids;
}
function getCustomerQuote($id)
{
	return App\QuotationHeader::where('crm_customer_id', $id)->get();
}
function getallQuotes()
{
	return App\QuotationHeader::all();
}
function viewableLocations()
{
	$locs = getUserLocations();
	$viewable_locations = array();

	foreach ($locs as $locID) {
		$loc = App\InventoryLocation::find($locID);
		if (!isset($viewable_locations[$loc->name])) {
			$viewable_locations[$loc->name] = array();
		}
		if ($loc->locations->count() > 0) {
			$viewable_locations[$loc->name] = array_merge($viewable_locations[$loc->name], getLocationChildren($loc));
		} else {
			$viewable_locations[$loc->name] = $loc;
			if (!isset(getCurrentUserLocation()->id)) {
				Session::put('current_user_location', $loc);
			}
		}
	}

	return $viewable_locations;
}

function getCurrentUserLocation()
{
	// Session::forget('current_user_location');
	$loc = Session::get('current_user_location');
	return $loc ?? App\InventoryLocation::find(Auth::user()->location_id);
}

function getPersonnelcertification($personnel_id, $role_cert_id)
{
	$cert =  App\Models\Personnel\PersonelCertification::where('personnel_id', $personnel_id)->where('role_certification_id', $role_cert_id)->get();
	$cert2 = new App\Models\Personnel\PersonelCertification();
	$cert2->personnel_id = '';
	$cert2->role_certification_id = '';
	$cert2->status = 1;
	$cert2->certificate = "";
	$cert2->certificate_body = "";
	$cert2->certificate_date = "";
	$cert2->expire_date = "";
	$cert2->edited_by = "";
	$cert2->id = '';
	$cert->created_at = '';
	$cert->updated_at = '';

	if (isset($cert[0]->id)) {
		return $cert;
	} else {
		return array($cert2);
	}
}


function getLocationChildren($location)
{
	$childrenLocs = array();
	$locs = App\InventoryLocation::where('inventory_location_id', $location->id)->get();
	foreach ($locs as $loc) {

		if (!isset($childrenLocs[$loc->name])) {
			$childrenLocs[$loc->name] = array();
		}
		if ($loc->locations->count() > 0) {
			$childrenLocs[$loc->name] = array_merge($childrenLocs[$loc->name], getLocationChildren($loc));
		} else {
			$childrenLocs[$loc->name] = $loc;
			if (!isset(getCurrentUserLocation()->id)) {
				Session::put('current_user_location', $loc);
			}
		}
	}

	return $childrenLocs;
}

function getSubCategoriesByBrand($sub_id = 0, $categorize = false)
{
	$items = \App\InventorySubCategories::join('inventory_categories as ic', 'ic.id', 'inventory_sub_categories.inventory_category_id')
		->leftJoin('item_brands as ib', function ($join) {
			$join->on('ib.inventory_sub_category_id', 'inventory_sub_categories.id');
			$join->where('ib.status', 1);
		})
		->selectRaw('CONCAT(ic.name, " - ", inventory_sub_categories.name, " - ", COALESCE(ib.name, "NB")) as item, ic.name, inventory_sub_categories.id, ib.id as brand_id')
		->where('ic.inventory_location_id', getCurrentUserLocation()->id);

	if ($sub_id > 0) {
		$items = $items->where('inventory_sub_categories.id', $sub_id);
	}

	$items = $items->orderBy('item', 'asc')->get();
	$response = array();
	if ($categorize) {
		foreach ($items as $item) {
			if (!isset($response[$item->name])) {
				$response[$item->name] = array();
			}

			$response[$item->name][] = [
				'name' => $item->item,
				'sub_id' => $item->id,
				'brand' => $item->brand_id
			];
		}

		$items = $response;
	}
	return $items;
}

function getUsersInRoleForUser(User $user, $role)
{
}

function getNatureOfExpense()
{
	return ["LPO Purchase", "Opex", "Cash Purchase", "Normal"];
}

function getSamplesByWorkflow($stage)
{
	return \App\SampleDetails::join('sample_headers as sh', 'sh.id', 'sample_header_id')->where('sh.status', $stage)
		->selectRaw('sample_code, sample_details.id')->get();
}

function calculateAvailableStock($inventoryItemId)
{
	$item = \App\InventorySubCategories::find($inventoryItemId);
	$available = $item->available()['available'] ?? 0;
	$item->available_stock = $available;
	$item->save();

	if ($available <= $item->reaorder_level) {
		$item->requires_reorder = 1;
	} else {
		$item->requires_reorder = 0;
	}
	$item->save();
	return $item->available_stock;
}

function setItemReorderLevel($inventoryItemId, $reorderQ)
{
	$item = \App\InventorySubCategories::find($inventoryItemId);

	$item->reaorder_level = $reorderQ ?? 0;
	$item->save();

	return $item->reaorder_level;
}

function getDocumentTemplates()
{
	return [
		"Goods Receipt" => 'layouts.inventory.templates.goods-receipt',
		"Goods Return" => 'layouts.inventory.templates.goods-return',
		"Purchase Orders" => 'layouts.inventory.templates.purchase-order',
		"Material Requisition" => 'layouts.inventory.templates.requisition-sheet',
		"Supply Inspection Form" => 'layouts.inventory.templates.supply-inspection-form'
	];
}

function sendTextMessage($number, $text)
{
	$config = App\Models\System\SystemConfigurationsType::where('configuration_type', 'SMS Configuration')->first();
	if (isset($config->id)) {
		$api = App\Models\System\SystemConfiguration::where('configuration_type_id', $config->id)->where('key', 'apikey')->first();
		$short_code  = App\Models\System\SystemConfiguration::where('configuration_type_id', $config->id)->where('key', 'shortcode')->first();
		$partnerID =  App\Models\System\SystemConfiguration::where('configuration_type_id', $config->id)->where('key', 'partnerID')->first();
		if (isset($api->id) && isset($short_code->id) && isset($partnerID->id)) {
			$smsJSON = '{
					"apikey": "' . $api->value . '",
					"partnerID" : ' . (int)$partnerID->value . ',
					"shortcode" : "' . $short_code->value . '",
					"mobile" : "' . $number . '",
					"message" : "' . $text . '",
					"pass_type" : "plain"
				}';

			$smsOBJ = json_decode($smsJSON, true);

			$url = "https://quicksms.advantasms.com/api/services/sendsms/";

			$data_string = json_encode($smsOBJ);

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt(
				$ch,
				CURLOPT_HTTPHEADER,
				array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($data_string)
				)
			);

			$result = curl_exec($ch);

			return $result;
		} else {

			return 'error';
		}
	} else {
		return 'error';
	}
}
function getRandomHex($num_bytes = 2)
{
	return bin2hex(openssl_random_pseudo_bytes($num_bytes));
}
function getSampleCodesBySampleHeaderID($id){
	$sample_codes = App\SampleDetails::where('sample_header_id',$id)->pluck('sample_code')->toArray();
	// return response()->json()
	return implode(',',$sample_codes);
}
function getBatchSampleCodes($id){
	$sample_codes = App\SampleDetails::where('sample_header_id',$id)->pluck('sample_code')->toArray();
			// return response()->json()
	return implode(',',$sample_codes);
}

function split_emails($sep, $str){
	$emails = explode($sep, $str);

	$ems = [];

	foreach($emails as $em){
		$ems[] = trim($em);
	}

	return array_filter($ems);
}

function is_valid_email($email){
	$anyInvalid = [];
	$emails = explode(';', $email);

	foreach($emails as $e){
		$isValidEmail = filter_var($e, FILTER_VALIDATE_EMAIL);
		if($isValidEmail == false){
			$anyInvalid[] = trim($e) == "" ? "Blank Email" : $e;
		}
	}

	return count($anyInvalid) > 0 ? implode(',', $anyInvalid) : false;
}
function getSampleDetailsLab($sample_id){
	return  SampleAnalysisTypeRelationView::where('sample_detail_id',$sample_id)->distinct('sample_lab')->get();
}
function getInterLabTotals(){
	return InterLabLog::whereNotNull('sample_id')->get()->count();
}
function getSamplingMethods(){
	return App\AnalysisMethod::where('active',1)->where('is_sampling_method',1)->get();
}
function getCrmCustomerContactSchedule($id)
{
	return App\Models\CRM\CustomerContact::where('crm_customer_id', $id)->where('active', 1)->where('receive_report', 1)->get();
}

function getPaymentReminderBody($customer_name,$sample_codes){
	$body = '
	<p>
		Dear '.$customer_name.'<br><br> This is to remind you about the due payment for the Job Number.<b> '.$sample_codes.'</b><br> <br>
		Invoice# : <br>
		Due Date : <br> <br>

		Overdue : <br>
		Due Date :<br>

		If you have already paid, please accept our apologies and kindly ignore this payment reminder. <br><br>


		Regards,




	</p>
	';
	return $body;
}
function getBacthSampleCodes($batch_id){
	return implode(', ',App\SampleDetails::where('sample_header_id',$batch_id)->pluck('sample_code')->toArray());
}

function getCoaApproverSignature($signature){
	$verify_sig_arr = explode('/',$signature);
	$verify_sig_arr[1] = "app";
	$verify_sig = implode('/',$verify_sig_arr);
	return storage_path().$verify_sig;
}
function convertDateFormatReports($date,$format){
	if($format == 'dateShortMonth'){
		$raw_date = \Carbon\Carbon::parse($date);
		$format_date = $raw_date->format('jS M Y');
		return $format_date;
	}
}


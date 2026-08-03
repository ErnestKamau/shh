<?php

use App\Models\System\SystemConfiguration;
use Illuminate\Support\Facades\DB;

use App\Supplier;
use App\SampleAnalysisTypeRelationView;
use App\InterLabLog;
use App\CapturedResult;
use App\StandardAnalytes;
use App\StandardValue;

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
	if ($subtring_start === false) {
		return '';
	}
	$subtring_start += strlen($starting_word);
	$end = strpos($str, $ending_word, $subtring_start);
	if ($end === false) {
		return '';
	}
	$size = $end - $subtring_start;
	return substr($str, $subtring_start, $size);
}

function getMethods()
{
	return App\AnalysisMethod::where('active',1)->where('is_sampling_method',0)->where('is_ltm',0)->get();
}

function getCurrentDate()
{
	return \Carbon\Carbon::now();
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
	$company = getActiveCompany();

	return array(
		"name" => $company->name ?? config('app.name', 'LIMS'),
		"logo" => ($company && ! empty($company->logo)) ? $company->logo : '/images/logo.png'
	);
}
function getSystemFavicon()
{
	return \Illuminate\Support\Facades\Cache::rememberForever('system_favicon_url', function () {
		try {
			$company = App\Company::query()
				->whereNotNull('favicon')
				->where('favicon', '!=', '')
				->orderByDesc('active')
				->orderByDesc('show_on_reports')
				->first();

			return $company->favicon ?? null;
		} catch (\Throwable $e) {
			return null;
		}
	});
}
function getAllUsers()
{
	return App\User::where('is_client', 0)->whereNull('supplier_id')->where('active', 1)->where('is_support_staff', 0)->get();
}
function getBatchAmmendmentsById($id)
{
	$ammendments =  App\BatchAmmendment::where('batch_id', $id)->get();

	foreach ($ammendments as $a) {
		$s = json_decode($a->samples, true);
		if (is_array($s)) {
			$p = array_keys($s);
			$a->sample_name = implode(',', $p);
		} else {
			$a->sample_name = '';
		}
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
	return array("Purchase Request", "Request for Quotation", "Purchase Orders", "Goods Receipt", "Goods Return");
}

function getRequestToStoreWorkflow()
{
	return array("Request to Store", "Material Issuance");
}

function getInventoryWorkflowStageLabel(string $stage): string
{
	$labels = [
		'Purchase Request' => 'inventory.workflow_purchase_request',
		'Request for Quotation' => 'inventory.workflow_request_for_quotation',
		'Purchase Orders' => 'inventory.workflow_purchase_orders',
		'Goods Receipt' => 'inventory.workflow_goods_receipt',
		'Goods Return' => 'inventory.workflow_goods_return',
		'Request to Store' => 'inventory.workflow_request_to_store',
		'Material Issuance' => 'inventory.workflow_material_issuance',
		'Lend' => 'inventory.lend',
		'Loan' => 'inventory.loan',
	];

	if (isset($labels[$stage])) {
		$translated = __($labels[$stage]);

		return $translated !== $labels[$stage] ? $translated : $stage;
	}

	return $stage;
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
	return array("Document File", "Image File", "Video File", "GR - Invoice", "GR - Delivery Note", "GR - Credit Note", "Proof of Payment", "Material Safety Datasheet", "Supplier Item Specification File");
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
	return \App\EntityApproval::join('request_entities as re', function($join) {
			$join->on(DB::raw('cast(re.id as text)'), '=', DB::raw('cast(entity_approvals.model_id as text)'));
		})
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
	return array("Rejection", "General Note", "Supplier Awarded", "Delivery Rating", "Item Quantity Change Reason", "Reversal Reason");
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
	} elseif ($frequency == 90) {
		return 'Quarterly';
	} elseif ($frequency == 180) {
		return 'Semi Annually';
	} elseif ($frequency == 365) {
		return 'Annually';
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
	return \App\ModulePreConfigs::where('type', $config)->where('module', $module)->orderBy('level', 'desc')->orderBy('name', 'asc')->get();
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

function looksLikeEncryptedPayload(string $value): bool
{
	return str_starts_with($value, 'eyJ');
}

function plaintextConfigurationValue(?string $value): string
{
	$value = trim((string) $value);
	if ($value === '' || ! looksLikeEncryptedPayload($value)) {
		return $value;
	}

	for ($attempt = 0; $attempt < 30; $attempt++) {
		if (! looksLikeEncryptedPayload($value)) {
			break;
		}

		try {
			$decrypted = trim(\Illuminate\Support\Facades\Crypt::decryptString($value));
		} catch (\Throwable) {
			try {
				$decrypted = trim(decrypt($value));
			} catch (\Throwable) {
				return '';
			}
		}

		if ($decrypted === $value) {
			break;
		}

		$value = $decrypted;
	}

	// Never leak undecryptable ciphertext into emails or UI.
	return looksLikeEncryptedPayload($value) ? '' : $value;
}

function getReportEmailFooter(): string
{
	$config = getExpertin();

	return $config !== null
		? plaintextConfigurationValue($config->value)
		: '';
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
	return App\User::where('is_client', 0)->whereNull('supplier_id')->where('active', 1)->where('is_support_staff', 0)->get();
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
    ->groupBy('inventory_stores.id', 'inventory_stores.name', 'iss.name', 'iss.id');

  if($isRequisition){
    $stores = $stores->orderBy('slot_name', 'asc');
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

function getModulePreconfig($type, $module, $sortBy='name', $sortOrder='asc')
{
	if(gettype($module) == 'array'){
		return App\ModulePreConfigs::where('type', $type)->whereIn('module', $module)->orderBy($sortBy, $sortOrder)->get();
	}
	return App\ModulePreConfigs::where('type', $type)->where('module', $module)->orderBy($sortBy, $sortOrder)->get();
}

function getSampleWorflowStages()
{
	return array("All Samples", "Samples Receiving", "Samples In Lab", "Sample Verification", "Sample Approval", "Reports In Payment", "Reports for Collection","Completed Sample");
}

/**
 * Status values that mean a batch report is finished / available on CRM reports.
 * Lab workflow uses "Completed Sample"; older paths used "Completed" / "Finished Sample".
 *
 * @return list<string>
 */
function getCompletedReportStatuses(): array
{
	return ['Completed', 'Completed Sample', 'Finished Sample'];
}

function isCompletedReportStatus(?string $status): bool
{
	return in_array((string) $status, getCompletedReportStatuses(), true);
}

function getSampleWorkflowStageLabel($stage)
{
	$labels = [
		'Samples En-Route' => 'lab.workflow_samples_receiving',
		'Samples Reception' => 'lab.workflow_samples_receiving',
		'Samples Receiving' => 'lab.workflow_samples_receiving',
		'All Samples' => 'lab.workflow_all_samples',
		'Samples Request Review' => 'lab.workflow_samples_request_review',
		'Samples In Lab' => 'lab.status_samples_in_lab',
		'Sample Verification' => 'lab.status_sample_verification',
		'Sample Approval' => 'lab.status_sample_approval',
		'Reports In Payment' => 'lab.workflow_reports_in_payment',
		'Reports for Collection' => 'lab.workflow_reports_for_collection',
		'Completed Sample' => 'lab.status_finished_sample',
	];

	if (isset($labels[$stage])) {
		$translated = __($labels[$stage]);

		return $translated !== $labels[$stage] ? $translated : $stage;
	}

	return $stage;
}
function getVisibleComplaintWorkflowMap()
{
        return array(
                "Open Complaint" => 1,
                "Complaint Resolution" => 2,
                "Resolution Approval" => 4,
                "Closed Complaint" => 5,
                "Cancelled" => 6,
        );
}
function getComplaintWorkflowMenuItems()
{
        return array("All Complaints" => 0) + getVisibleComplaintWorkflowMap();
}
function getComplaintWorkflowNextStageMap()
{
        return array(
                1 => 2,
                2 => 4,
                4 => 5,
        );
}
function getComplaintWorkflowPreviousStageMap()
{
        return array(
                2 => 1,
                4 => 2,
                5 => 4,
        );
}
function getComplaintWorkflowStages()
{
        return array_keys(getComplaintWorkflowMenuItems());
}
function getComplaintsWorkFlowValues()
{
        return getComplaintWorkflowMenuItems();
}
function getComplaintWorkflow()
{
        return array(
                0 => "All Complaints",
                1 => "Open Complaint",
                2 => "Complaint Resolution",
                3 => "Complaint Verification (Removed)",
                4 => "Resolution Approval",
                5 => "Closed Complaint",
                6 => "Cancelled"
        );
}

function getComplaintWorkflowStageTranslationKey(string $stage): ?string
{
	$map = [
		"All Complaints" => 'complaint_stage_all_complaints',
		"Open Complaints" => 'complaint_stage_open_complaints',
		"Complaints Approval" => 'complaint_stage_complaints_approval',
		"Complaints Resolution" => 'complaint_stage_complaints_resolution',
		"Resolution Approval" => 'complaint_stage_resolution_approval',
		"Closed Complaints" => 'complaint_stage_closed_complaints',
		"Cancelled Complaints" => 'complaint_stage_cancelled_complaints',
		"Open Complaint" => 'complaint_stage_open_complaints',
		"Complaint Resolution" => 'complaint_stage_complaints_resolution',
		"Closed Complaint" => 'complaint_stage_closed_complaints',
		"Cancelled" => 'complaint_stage_cancelled_complaints',
		"Log & Intake" => 'complaint_stage_log_intake',
		"Active Investigations" => 'complaint_stage_active_investigations',
		"Pending Closure" => 'complaint_stage_pending_closure',
		"Verification Review & CAPA" => 'complaint_stage_verification_review_capa',
	];

	return $map[$stage] ?? null;
}

function translateComplaintWorkflowStage(string $stage): string
{
	$key = getComplaintWorkflowStageTranslationKey($stage);
	if ($key === null) {
		return $stage;
	}

	$translationKey = 'crm.' . $key;
	$translated = __($translationKey);

	return $translated === $translationKey ? $stage : $translated;
}

function getComplaintsActionsApproval()
{
        return array(
                1 => "Approve Complaint & Move to Resolution",
                2 => "Approve Resolution & Move to Approval",
                // 3 => "Approve Verification & Move to Pending Closure",
                4 => "Final Review & Close Complaint"
        );
}

function mamboSawa($licenseType = false)
{
	$license = \Auth::user()->available_license();

	if (!is_array($license)) {
		return $licenseType ? 0 : [];
	}

	return $licenseType ? ($license[$licenseType] ?? 0) : $license;
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
                2 => "Return to Open Complaint",
                // 3 => "Return to Investigation Stage",
                4 => "Return to Resolution Stage",
                5 => "Return for Final Review"
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

	// Form-centric stages: match WorkflowBoard queue logic, not batch status alone.
	$arr['Samples Receiving'] = \App\Livewire\Sampleworkflow\WorkflowBoard::sidebarReceivingRequestCount();

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
function getSystemModules()
{
	return array(
		'laboratory' => array(
			'name' => 'Laboratory',
			'route' => '/lab-dashboard',
			'default_visible' => true,
		),
		'inventory' => array(
			'name' => 'Inventory',
			'route' => '/inventory-home',
			'default_visible' => true,
		),
		'equipment' => array(
			'name' => 'Equipment',
			'route' => '/equipment-dashboard',
			'default_visible' => true,
		),
		'crm' => array(
			'name' => 'CRM',
			'route' => '/crm/dashboard',
			'default_visible' => true,
		),
		'personnel' => array(
			'name' => 'Personnel',
			'route' => '/personnel-home',
			'default_visible' => true,
		),
		'dms' => array(
			'name' => 'Document Management',
			'route' => '/dms/dashboard',
			'default_visible' => true,
		),
		'calendar' => array(
			'name' => 'System Planner',
			'route' => '/system-planner/dashboard',
			'default_visible' => true,
		),
		'matrix' => array(
			'name' => 'Skills Matrix',
			'route' => '/matrix',
			'default_visible' => true,
		),
		'risk' => array(
			'name' => 'Risk Management',
			'route' => '/risk/dashboard',
			'default_visible' => true,
		),
		'audit' => array(
			'name' => 'Audit',
			'route' => '/audit/dashboard',
			'default_visible' => true,
		),
		'tickets' => array(
			'name' => 'Help Desk',
			'route' => '/tickets/dashboard',
			'default_visible' => true,
		),
		'settings' => array(
			'name' => 'System Settings',
			'route' => '/system-settings',
			'default_visible' => true,
		),
		'ai_analytics' => array(
			'name' => 'AI Analytics',
			'route' => '/ai-analytics',
			'default_visible' => true,
		),
		'registry' => array(
			'name' => 'Registry',
			'route' => '/registry',
			'default_visible' => true,
		),
		'imaraai' => array(
			'name' => 'ImaraAI',
			'route' => '/imaraai',
			'default_visible' => true,
		),
	);
}
function getSystemModuleVisibilityMap()
{
	$modules = getSystemModules();
	$visibility = array();
	foreach ($modules as $key => $module) {
		$visibility[$key] = (bool) ($module['default_visible'] ?? true);
	}

	$configType = App\Models\System\SystemConfigurationsType::where('configuration_type', 'Module Visibility')->first();
	if (!isset($configType->id)) {
		return $visibility;
	}

	$configurations = App\Models\System\SystemConfiguration::where('configuration_type_id', $configType->id)
		->where('key', 'system_module_visibility')
		->orderByDesc('updated_at')
		->get();

	// Prefer newest JSON map even when duplicate rows exist from older saves.
	// Shape: {"laboratory":true,"inventory":false,"calendar":true,...}
	foreach ($configurations as $configuration) {
		$decoded = json_decode((string) $configuration->value, true);

		if (!is_array($decoded)) {
			continue;
		}

		foreach ($decoded as $moduleKey => $isVisible) {
			if (isset($visibility[$moduleKey])) {
				$visibility[$moduleKey] = (bool) $isVisible;
			}
		}

		return $visibility;
	}

	// Legacy shape: value is a module key, status is visibility.
	foreach ($configurations as $configuration) {
		$value = (string) $configuration->value;
		if (isset($visibility[$value])) {
			$visibility[$value] = (bool) $configuration->status;
		}
	}

	return $visibility;
}
function isSystemModuleVisible($moduleKey)
{
	$hiddenModules = [];
	if (in_array($moduleKey, $hiddenModules, true)) {
		return false;
	}

	// System Settings is permission-gated, not only module-visibility toggled.
	if ($moduleKey === 'settings') {
		$user = auth()->user();
		if (!$user || !$user->can('settings.module.access')) {
			return false;
		}
	}

	$visibility = getSystemModuleVisibilityMap();
	return isset($visibility[$moduleKey]) ? (bool) $visibility[$moduleKey] : true;
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
    if (empty($id)) {
        return $is_arr ? collect() : collect();
    }
	if ($is_arr) {
		return App\AnalysisType::whereIn('id', $id)->get();
	}
	return App\AnalysisType::where('id', $id)->get();
}
function getAnalysisTypeID($id)
{
    if (empty($id)) {
        return null;
    }
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

/**
 * Contacts available when creating or editing quotations.
 * Uses active contacts (not limited to receive_invoice) and always retains the assigned contact.
 */
function getQuotationCustomerContacts(string $id, ?string $assignedContactId = null)
{
	$contacts = App\Models\CRM\CustomerContact::query()
		->where('crm_customer_id', $id)
		->where('active', 1)
		->orderBy('first_name')
		->get();

	if ($assignedContactId !== null && $assignedContactId !== '') {
		$alreadyIncluded = $contacts->contains(
			fn ($contact) => (string) $contact->id === (string) $assignedContactId
		);

		if (! $alreadyIncluded) {
			$assigned = App\Models\CRM\CustomerContact::query()->find($assignedContactId);
			if ($assigned && (string) $assigned->crm_customer_id === (string) $id) {
				$contacts->prepend($assigned);
			}
		}
	}

	return $contacts;
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
	$user = auth()->user();
	$position = $user->position ?? null;

	if (!is_string($position) || !\Illuminate\Support\Str::isUuid($position)) {
		return collect();
	}

	return App\BatchNotification::where('position_id', $position)
		->where('active', 1)
		->orderBy('id', 'desc')
		->get();
}

function getTotaltaxAmount($id)
{
	$taxes = App\InvoiceDetails::where('invoice_id', $id)->pluck('tax_amount')->toarray();
	return array_sum($taxes);
}
function getPricelistCurrency($id)
{
	$invoice = App\Invoice::find($id);
    if($invoice && $invoice->currency_id) {
        return App\ModulePreConfigs::find($invoice->currency_id);
    }
    
    if ($invoice && $invoice->pricelist_id) {
        // Fallback for legacy if Pricelist class actually existed under a different name or if we can find it
        // But for new ones, currency_id is directly on the invoice
        $pricelist = DB::table('zoho_items_pricelist')->where('id', $invoice->pricelist_id)->first();
        if ($pricelist && isset($pricelist->currency_id)) {
            return App\ModulePreConfigs::find($pricelist->currency_id);
        }
    }

	return App\ModulePreConfigs::where('type', 'Currency')->first();
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
	$users =  App\User::where('active', 1)->where('is_client', 0)->whereNull('supplier_id')->where('id', '!=', auth()->user()->id)->get();
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
	$userId = (string) auth()->user()->id;

	return App\Event::where(function ($query) use ($userId) {
			$query->where('responsible_id', $userId)
				->orWhere('responsible_id', 'like', $userId.',%')
				->orWhere('responsible_id', 'like', '%,'.$userId.',%')
				->orWhere('responsible_id', 'like', '%,'.$userId);
		})
		->where(function($query) {
			$query->whereNull('parent_id')
			      ->orWhereColumn('id', 'parent_id')
			      ->orWhere('is_routine', '!=', 1)
			      ->orWhere('status', '!=', 'Pending');
		})
		->get();
}

function getEventResponsibleNames($responsibleIds): string
{
	$ids = array_values(array_filter(array_map('trim', explode(',', (string) $responsibleIds))));
	if ($ids === []) {
		return '-';
	}

	$names = App\User::query()
		->whereIn('id', $ids)
		->orderBy('name')
		->pluck('name')
		->filter()
		->values()
		->all();

	return $names !== [] ? implode(', ', $names) : '-';
}
function getUserChats()
{
	$chats = App\ChatMessage::where('to_user_id', auth()->user()->id)->where('is_new', 1)->get();
	return $chats;
}
function getUsers($all = false)
{
	$query = App\User::orderBy('name')
		->where('company_id', getUserCompany())
		->where('active', 1)
		->where('is_support_staff', 0)
		->where('is_client', 0)
		->whereNull('supplier_id');

	if ($all) {
		return $query->get();
	}

	$location = getCurrentUserLocation();
	if (!$location || empty($location->id)) {
		return $query->get();
	}

	return $query->where('location_id', $location->id)->get();
}


function getInventoryWorkflowRoleConfig($workflowRole)
{
	$map = [
		'assistant_supervisor' => [
			'role_names' => ['Inventory Assistant Supervisor Group', 'Assistant Supervisor', 'Supervisor', 'Requester', 'Admin'],
		],
		'procurement' => [
			'role_names' => ['Inventory Procurement Group', 'Procurement', 'Financial Accountant', 'Admin'],
		],
		'department_head' => [
			'role_names' => ['Inventory Department Head Group', 'Department Head', 'Lab Manager', 'Admin'],
		],
		'manager' => [
			'role_names' => ['Inventory Manager Group', 'Manager', 'Admin'],
		],
		'finance' => [
			'role_names' => ['Inventory Finance Group', 'Finance', 'Financial Accountant', 'Admin'],
		],
		'store_manager' => [
			'role_names' => ['Inventory Store Manager Group', 'Store Manager', 'Lab Manager', 'Admin'],
		],
		'site_manager' => [
			'role_names' => ['Inventory Site Manager Group', 'Site Manager', 'Admin'],
		],
	];

	return $map[$workflowRole] ?? ['role_names' => []];
}

function getInventoryWorkflowUsers($workflowRole, $departmentId = null)
{
	$config = getInventoryWorkflowRoleConfig($workflowRole);
	$roleNames = $config['role_names'] ?? [];

	$users = ! empty($roleNames)
		? App\User::role($roleNames)
			->orderBy('name')
			->where('users.company_id', getUserCompany())
			->where('users.active', 1)
			->where('users.is_support_staff', 0)
			->get()
		: collect();

	if ($departmentId !== null) {
		$users = $users->filter(function ($user) use ($departmentId) {
			return ($user->department_id ?? '') === (string) $departmentId;
		});
	}

	return $users->sortBy('name')->values();
}

function getFirstInventoryWorkflowUser($workflowRole, array $excludedUserIds = [], $departmentId = null)
{
	return getInventoryWorkflowUsers($workflowRole, $departmentId)
		->reject(function ($user) use ($excludedUserIds) {
			return in_array((string) $user->id, array_map('strval', $excludedUserIds), true);
		})
		->values()
		->first();
}

function currentUserHasInventoryWorkflowRole($workflowRole)
{
	if (! \Auth::check()) {
		return false;
	}

	$user = \Auth::user();
	$config = getInventoryWorkflowRoleConfig($workflowRole);
	$roleNames = $config['role_names'] ?? [];

	return ! empty($roleNames) && $user->hasRole($roleNames);
}

function getReportingUnitsByID($id)
{
	return App\ReportingUnit::find($id);
}

function getInventoryCategories(){
	return App\InventoryCategories::orderBy('name', 'asc')->get();
}

function getInventoryCatByID($id)
{
	return App\InventoryCategories::find($id);
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
	return App\Models\Auth\Role::orderBy('name')->where('company_id', getUserCompany())->get();
}

/**
 * @param  string|array<int, string>  $roleNames
 * @return \Illuminate\Database\Eloquent\Collection<int, \App\User>
 */
function getActiveUsersByRole(string|array $roleNames): \Illuminate\Database\Eloquent\Collection
{
	$roleNames = collect(is_array($roleNames) ? $roleNames : [$roleNames])
		->map(fn ($name) => trim((string) $name))
		->filter()
		->unique()
		->values();

	if ($roleNames->isEmpty()) {
		return new \Illuminate\Database\Eloquent\Collection();
	}

	$existingRoleNames = App\Models\Auth\Role::query()
		->where('guard_name', 'web')
		->whereIn('name', $roleNames->all())
		->pluck('name');

	if ($existingRoleNames->isEmpty()) {
		return new \Illuminate\Database\Eloquent\Collection();
	}

	return App\User::role($existingRoleNames->all())
		->where('is_support_staff', 0)
		->where('active', 1)
		->orderBy('name')
		->get();
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
	if ($id === null || $id === '') {
		return null;
	}

	$id = (string) $id;

	if (\Illuminate\Support\Str::isUuid($id)) {
		return App\InventoryDepartment::find($id);
	}

	return App\InventoryDepartment::where('name', $id)->first();
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
	$modules = array(
		"Laboratory" => array(
			"permission" => false,
			"components" => array_merge(array_diff(getSampleWorflowStages(), array("All Samples")), array("All Samples", "Analytes", "Labs", "Sample-Types", "Reporting-Units", "Methods", "Sample-Tracking-Stages", "Analysis Types", "Proforma Invoices", "Tax Regime", "Pricelists", "Quotation", "Approve For Analysis", "Generate Invoice", "RFT Form", "Qc Sample", "Dashboard", "Stock-Monitoring", "Lab-Reports", "Standards", "Inter-Lab-Logs", "Sales-Orders", "Customer-Focus", "Verification-Approvals"))
		),
		"Inventory" => array(
			"permission" => false,
			"components" => array_merge(getRequestToStoreWorkflow(), array_merge(getRequisitionWorkflow(), array("General Requisition", "Categories", "Inventory-Movement", "Departments", "Suppliers", "Store", "Stock-Taking", "Stock-Transfer", "Configuration", "Approval-Requests")))
		),
		"Skills-Matrix" => array(
			"permission" => false,
			"components" => array("Module-Preconfigs", "Skills-Matrix", "Capability", "Training-Needs", "Training-Plan", "Matrix-Configuration", "Other-Training")
		),
		"Equipment" => array(
			"permission" => false,
			"components" => array_merge(array("Equipment-List", "Asset-Type", "Asset-Location", "Equipment-Disposal", "Equipment-Evaluation", "Equipment-Decommission"), getEquipmentLogs())
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
		),
		"Helpdesk" => array(
			"permission" => false,
			"components" => array("Dashboard", "Tickets", "Categories", "Archived Tickets", "Chat")
		),
		"Documents" => array(
			"permission" => false,
			"components" => array("Document Types", "Document Management", "Document Publishing", "Notification Frequencies", "Reports")
		),
		"System" => array(
			"permission" => false,
			"components" => array("System Settings", "Configuration Types", "Configurations", "Translations")
		),
		"Audit" => array(
			"permission" => false,
			"components" => array("Audits", "Non-Conformances", "Corrective Actions", "Reports", "Configuration")
		),
		"Risk-Management" => array(
			"permission" => false,
			"components" => array("Risk Dashboard", "Risks", "Risk Settings")
		),
		"Registry" => array(
			"permission" => false,
			"components" => array(
				"Dashboard",
				"Requests",
				"Correspondence Register",
				"Approval Queue",
				"Assignments",
				"Documents",
				"Workflow Configuration",
				"Reports",
				"Audit Trail",
			)
		)
	);

	$normalizedModules = [];

	foreach ($modules as $moduleName => $moduleConfig) {
		$moduleKey = strtolower(trim((string) $moduleName));
		if ($moduleKey === '') {
			continue;
		}

		$components = $moduleConfig['components'] ?? [];
		if (!is_array($components)) {
			$components = [];
		}

		$normalizedComponents = array_values(array_unique(array_filter(array_map(function ($component) {
			$componentKey = strtolower(trim((string) $component));
			return $componentKey === '' ? null : $componentKey;
		}, $components))));

		$normalizedModules[$moduleKey] = [
			'permission' => (bool) ($moduleConfig['permission'] ?? false),
			'components' => $normalizedComponents,
		];
	}

	return $normalizedModules;
}

function getCompanies()
{
	return App\Company::orderBy('name')->get();
}
function getActiveCompany()
{
	try {
		return App\Company::where('active', 1)->first()
			?? App\Company::orderBy('id')->first();
	} catch (\Throwable $exception) {
		return null;
	}
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
	return App\SampleType::where('active', '1')->orderBy('name')->get();
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

function getUnitNamesByID(array $unitIds): string
{
	$values = array_values(array_filter(
		array_map(static fn ($id) => trim((string) $id), $unitIds),
		static fn ($id) => $id !== ''
	));

	if ($values === []) {
		return '—';
	}

	$uuidIds = array_values(array_filter($values, static fn ($value) => \Illuminate\Support\Str::isUuid($value)));

	$resolvedById = $uuidIds === []
		? []
		: App\Models\CRM\CRMCompanyUnit::query()
			->whereIn('id', $uuidIds)
			->pluck('name', 'id')
			->all();

	$display = [];

	foreach ($values as $value) {
		if (\Illuminate\Support\Str::isUuid($value)) {
			$display[] = $resolvedById[$value] ?? $value;
		} else {
			// Legacy contacts store unit names directly (e.g. "Nairobi"), not UUIDs.
			$display[] = $value;
		}
	}

	$display = array_values(array_filter($display, static fn ($name) => $name !== ''));

	return $display !== [] ? implode(', ', $display) : '—';
}

function getOtherCustomersByID(array $customerIds): string
{
	$values = array_values(array_filter(
		array_map(static fn ($id) => trim((string) $id), $customerIds),
		static fn ($id) => $id !== ''
	));

	if ($values === []) {
		return '—';
	}

	$uuidIds = array_values(array_filter($values, static fn ($value) => \Illuminate\Support\Str::isUuid($value)));

	if ($uuidIds === []) {
		return '—';
	}

	$resolvedById = App\Models\CRM\CRMCustomer::query()
		->whereIn('id', $uuidIds)
		->pluck('name', 'id')
		->all();

	$display = [];

	foreach ($values as $value) {
		if (\Illuminate\Support\Str::isUuid($value) && isset($resolvedById[$value])) {
			$display[] = $resolvedById[$value];
		}
	}

	return $display !== [] ? implode(', ', $display) : '—';
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

	if(in_array($requiredName, ['PR', 'RFQ', 'PO', 'GR', 'GRN', 'RS', 'MI', 'GP'])){
		$nameString = $requiredName.date("Y");
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


	$nameInteger = "1";

	if ($namingConV && isset($namingConV->string_part)) {
		$nameInteger = intval($namingConV->integer_part) + 1;
		$nameInteger = str_pad($nameInteger, $defaultPadding, "0", STR_PAD_LEFT);
	} else {
		$namingConV = new App\NamingConvensionConsensus;
		$namingConV->string_part = $nameString;
		$namingConV->model = $model;
		$namingConV->company_id = getUserCompany();
	}
	$nameInteger = str_pad($nameInteger, $defaultPadding, "0", STR_PAD_LEFT);
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
	$user = \Auth::user();
	if (! $user) {
		return [];
	}

	$user_locations = App\InventoryLocationUser::join('inventory_locations as l', 'l.id', '=', 'inventory_location_users.inventory_location_id')
		->selectRaw('l.name, l.id')->where('user_id', $user->id)->orderBy('l.level', 'asc')->get();

	$location_ids = array();

	foreach ($user_locations as $loc) {
		$location_ids[] = $loc->id;
	}

	if ($location_ids === [] && $user->hasRole(['admin', 'super admin', 'super-admin', 'system admin', 'system-admin'])) {
		return App\InventoryLocation::where('level', 1)->orderBy('name', 'asc')->pluck('id')->all();
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
			$currentLocation = getCurrentUserLocation();
			if (!$currentLocation || !isset($currentLocation->id)) {
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
	if ($loc) {
		return $loc;
	}

	$user = Auth::user();
	if (!$user) {
		return null;
	}

	if (!empty($user->location_id)) {
		$locationId = (string) $user->location_id;
		if (\Illuminate\Support\Str::isUuid($locationId)) {
			$location = App\InventoryLocation::find($locationId);
			if ($location) {
				return $location;
			}
		}
	}

	if (!empty($user->zone_id)) {
		$zone = App\Zone::find($user->zone_id);
		if ($zone?->inventory_location_id) {
			$location = App\InventoryLocation::find($zone->inventory_location_id);
			if ($location) {
				Session::put('current_user_location', $location);

				return $location;
			}
		}
	}

	$userLocationIds = getUserLocations();
	if ($userLocationIds !== []) {
		$location = App\InventoryLocation::find($userLocationIds[0]);
		if ($location) {
			Session::put('current_user_location', $location);

			return $location;
		}
	}

	return null;
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
			$currentLocation = getCurrentUserLocation();
			if (!$currentLocation || !isset($currentLocation->id)) {
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
		"Request for Quotation" => 'layouts.inventory.templates.requisition-sheet',
		"Supply Inspection Form" => 'layouts.inventory.templates.supply-inspection-form',
		"Gate Pass" => 'layouts.inventory.templates.gate-pass'
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

/**
 * Format a job sample code for display without the legacy category letter.
 * 260716003-C001 → 260716003-001
 */
function format_sample_code(?string $sampleCode): string
{
	if ($sampleCode === null || $sampleCode === '') {
		return '';
	}

	return app(\App\Services\Sampleworkflow\JobSampleNumberingService::class)
		->stripCategoryPrefixFromSampleCode($sampleCode);
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

function getPaymentReminderBody($customer_name){
	$body = '
	<p>
		Dear '.$customer_name.'<br><br> 
		We would like to kindly remind you that payment for the services provided is due. Your
		report is ready and will be released once payment is received.<br><br>
		Should you have any questions or need further assistance, please feel free to contact us. <br>
		Thank you for your prompt attention to this matter.<br><br>
		Best regards,<br>
		FIVET COMPANY LIMITED
	</p>
	';
	return $body;
}
function getBacthSampleCodes($batch_id){
	return implode(', ',App\SampleDetails::where('sample_header_id',$batch_id)->pluck('sample_code')->toArray());
}

/**
 * Resolve a stored electronic signature path to a readable filesystem path.
 *
 * Signatures may live on the local disk (storage/app/...) or public disk
 * (storage/app/public/...), and filenames may be URL-encoded in the DB.
 */
function getCoaApproverSignature($signature): string
{
	if ($signature === null || trim((string) $signature) === '') {
		return '';
	}

	$signature = trim((string) $signature);

	if (str_starts_with($signature, 'data:')) {
		return $signature;
	}

	if (is_readable($signature)) {
		return $signature;
	}

	$decoded = urldecode($signature);
	$relative = $decoded;

	if (str_contains($decoded, '/storage/')) {
		$parts = explode('/storage/', $decoded, 2);
		$relative = $parts[1] ?? $decoded;
	} elseif (str_starts_with($decoded, 'storage/')) {
		$relative = substr($decoded, strlen('storage/'));
	}

	$relative = ltrim(str_replace('\\', '/', (string) $relative), '/');
	if ($relative === '') {
		return '';
	}

	$candidates = [
		storage_path('app/public/' . $relative),
		storage_path('app/' . $relative),
		public_path('storage/' . $relative),
		public_path($relative),
		storage_path($relative),
	];

	// Legacy helper behaviour: /storage/foo -> storage_path()/app/foo
	$legacyParts = explode('/', $decoded);
	if (isset($legacyParts[1])) {
		$legacyParts[1] = 'app';
		$candidates[] = storage_path() . implode('/', $legacyParts);
	}

	foreach (array_unique($candidates) as $candidate) {
		if (is_readable($candidate)) {
			return $candidate;
		}
	}

	// Prefer public-disk path as default for newly uploaded Livewire signatures.
	return storage_path('app/public/' . $relative);
}

/**
 * Map stored lab remark codes to user-facing conforming language.
 * Canonical storage remains PASS / FAIL for evaluation and analytics.
 */
function format_result_remark(?string $remark): string
{
	if ($remark === null) {
		return '';
	}

	$normalized = strtoupper(trim($remark));
	$normalized = str_replace(['_', ' '], '-', $normalized);

	return match ($normalized) {
		'PASS', 'PASSED', 'CONFORMING', 'COMPLIANT' => 'Conforming',
		'FAIL', 'FAILED', 'FAILURE', 'REJECTED', 'NON-CONFORMING', 'NON-CONFORMANCE', 'NON-COMPLIANT' => 'Non-Conforming',
		'-' => '-',
		'' => '',
		default => trim($remark),
	};
}

/**
 * Whether a stored or display remark represents a failing / non-conforming result.
 */
function is_non_conforming_remark(?string $remark): bool
{
	if ($remark === null || trim($remark) === '') {
		return false;
	}

	return format_result_remark($remark) === 'Non-Conforming';
}

/**
 * Whether a stored or display remark represents a passing / conforming result.
 */
function is_conforming_remark(?string $remark): bool
{
	if ($remark === null || trim($remark) === '') {
		return false;
	}

	return format_result_remark($remark) === 'Conforming';
}

/**
 * Convert an electronic signature (path or data URI) into a base64 data URI for PDF/HTML reports.
 */
function signatureToDataUri(?string $signature): string
{
	if ($signature === null || trim($signature) === '') {
		return '';
	}

	$signature = trim($signature);

	if (str_starts_with($signature, 'data:')) {
		return $signature;
	}

	if (str_starts_with($signature, 'http://') || str_starts_with($signature, 'https://')) {
		return $signature;
	}

	$path = getCoaApproverSignature($signature);
	if ($path === '' || str_starts_with($path, 'data:') || ! is_readable($path)) {
		return '';
	}

	$contents = @file_get_contents($path);
	if ($contents === false || $contents === '') {
		return '';
	}

	$mime = @mime_content_type($path) ?: null;
	if (! is_string($mime) || $mime === '') {
		$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
		$mime = match ($extension) {
			'jpg', 'jpeg' => 'image/jpeg',
			'gif' => 'image/gif',
			'webp' => 'image/webp',
			'svg' => 'image/svg+xml',
			default => 'image/png',
		};
	}

	return 'data:' . $mime . ';base64,' . base64_encode($contents);
}

function convertDateFormatReports($date,$format){
	if($format == 'dateShortMonth'){
		$raw_date = \Carbon\Carbon::parse($date);
		$format_date = $raw_date->format('dS F Y');
		return $format_date;
		
	}else{
		$raw_date = \Carbon\Carbon::parse($date);
		$format_date = $raw_date->format('d/m/Y');
		return $format_date;
	}
}
function giveLessThanData($remark){
	if($remark == 'less_than'){
		return '<';
	}elseif($remark == 'greater_than'){
		return '>';
	}
	return '';
}
function getStandardLimitValue($captured_id,$standard_id,$counter = 0){
	$item = CapturedResult::find($captured_id);
	
	$analyte_standard = StandardAnalytes::where('standard_id', $standard_id)->where('analyte_id', $item->analyte_id)->first();
	if (isset($analyte_standard->id)) {
		if ($analyte_standard->standard_value_type == 'is_standard_value') {
			$value_id = StandardValue::find($analyte_standard->standard_value_id);
			if (isset($value_id->id)) {
				if ($value_id->code == 'IsValue') {
					if($counter == 0 && in_array($analyte_standard->value_type,['less_than','greater_than'])){
						return '';
					}
					if($counter == 1 && !in_array($analyte_standard->value_type,['less_than','greater_than'])){
						return '';
					}
					return $analyte_standard->value_type == 'NS' ? '--' : (in_array($analyte_standard->value_type,['less_than','greater_than']) ? giveLessThanData($analyte_standard->value_type) : $analyte_standard->value_type) ;
				}
			}else{
				return '';
			}
		}else{
			return '';
		}
	} else {
		return '';
	}
}

function formatReportResults($value){
	return $value == 'ND' ? 'Not Detected' : $value;
}

function isETCU(){
	$is_etcu_build = getConfigByName('is_etcu_build');
	$is_etcu_build_id = count($is_etcu_build) > 0 ? $is_etcu_build[0]->value : 0;
	return $is_etcu_build_id == 1;
}

function isKECU(){
	$is_kecu_build = getConfigByName('is_kecu_build');
	$is_kecu_build_id = count($is_kecu_build) > 0 ? $is_kecu_build[0]->value : 0;
	return $is_kecu_build_id == 1;
}

function getSupplierRatingCriteria(){
	return App\RatingCriteria::where('active', 1)->orderBy('title')->get();
}

function supplierRatingColorFromScore($rating){
	$rating = floatval($rating);
	return floatval($rating) == 100 ? 'bg-success' : ($rating < 100 && $rating > 60 ?
		'bg-info' : ($rating <= 60 && $rating > 35 ? 'bg-warning' : 'bg-danger'));
}

function has_exceeding_quantities($id){
	if (empty($id) || ! \Illuminate\Support\Str::isUuid((string) $id)) {
		return false;
	}

	$req = \App\RequestEntity::find($id);
	if(!isset($req->id) || $req->request_type != "Request to Store"){
		return false;
	}
	$items = \App\RequestEntityItem::where('request_id', $id)->get();


	$apiC = new \App\Http\Controllers\API\APIController();
	$hasExceeded = false;
	$request = new \Illuminate\Http\Request;

	$subItems = [];

	foreach($items as $i){
		$dc = $apiC->items_available($request, $i->inventory_sub_category_id, $i->item_brand_id, $id);

		$dc = json_decode($dc, true);

		if(!isset($subItems[$i->inventory_sub_category_id])){
			$subItems[$i->inventory_sub_category_id] = ["available"=> 0, "total"=>0];
		}

		$subItems[$i->inventory_sub_category_id]['available'] = floatval($dc['value']);
		$subItems[$i->inventory_sub_category_id]['total'] += floatval($i->quantity);
	}

	foreach($subItems as $si){
		if($si['available'] < $si['total']){
			$hasExceeded = true;
		}
	}

	return $hasExceeded;
}

function areThereFrozenStores()
{
	$frozen = \App\InventoryStore::where('is_frozen', 1)->get()->pluck('name')->toArray();

	return $frozen ?? [];
}

function getShippingMode()
{
	return ["Not Specified", "Air", "Ocean", "Rail", "Road"];
}

function getCostCenter()
{
	return App\InventoryDepartment::where('company_id', getUserCompany())
	->where('location_id', getCurrentUserLocation()->id)->where('module', 'organizational')
	->orderBy('name', 'asc')->get()->pluck('name')->toArray();
}

function isUserSomebody($USER)
{
	if (! $USER) {
		return false;
	}

	if ($USER->hasRole(['admin', 'super admin', 'super-admin', 'system admin', 'system-admin'])) {
		return true;
	}

	$workflowRoles = ['procurement', 'finance', 'manager', 'store_manager'];

	foreach ($workflowRoles as $workflowRole) {
		$config = getInventoryWorkflowRoleConfig($workflowRole);
		$roleNames = $config['role_names'] ?? [];

		if (! empty($roleNames) && $USER->hasRole($roleNames)) {
			return true;
		}
	}

	return false;
}

function status_colors($status){
	$colors = [
		'Approval Complete'=> 'border-left:10px solid #6dff00!important',
		'Purchase Order Sent'=> 'border-left:10px solid #6dff00!important',
		'Awaiting Approval'=> 'border-left:10px solid #fff911!important',
		'Partially Approved'=> 'border-left:10px solid #ffbe00!important',
	];

	return $colors[$status] ?? 'border-left:10px solid rgba(0,0,0,0.3)!important';
}

function auditableDelete($collection){
	foreach($collection as $c){
		$c->delete();
	}

	return true;
}

function issue_received_complete($parentID){
	$parentEntityItems = \App\RequestEntityItem::where('request_id', $parentID)->get();
	$childEntitiesIds = \App\RequestEntity::where('parent_request_id', $parentID)
	->whereNotIn('status', ['Reversed', 'Rejected'])->selectRaw('id')->get()->pluck('id');
	$childrenEntitiesNormal = \App\RequestEntityItem::whereIn('request_id', $childEntitiesIds)->where('action', 'normal')->get();
	$childrenEntitiesActioned = \App\RequestEntityItem::whereIn('request_id', $childEntitiesIds)->where('action', '!=', 'normal')->get();

	//whereNotIn('status', ['Reversed', 'Rejected'])

	$quantitiesHolder = ["parent"=>[], "children"=>[]];
	//first set maximum quantities
	foreach($parentEntityItems as $pei){
		if(!isset($quantitiesHolder['parent'][$pei->inventory_sub_category_id])){
		$quantitiesHolder['parent'][$pei->inventory_sub_category_id] = 0;
		$quantitiesHolder['children'][$pei->inventory_sub_category_id] = ["normal"=>0, "actioned"=>0];
		}
		$quantitiesHolder['parent'][$pei->inventory_sub_category_id] += floatval($pei->quantity);
	}

	//set normal children quantities
	foreach($childrenEntitiesNormal as $cen){
		$quantitiesHolder['children'][$cen->inventory_sub_category_id]['normal'] += floatval($cen->quantity);
	}

	//set normal children quantities
	foreach($childrenEntitiesActioned as $cea){
		$quantitiesHolder['children'][$cea->inventory_sub_category_id]['actioned'] += floatval($cea->quantity);
	}

	$entityType = ["items"=>0, "completed"=>0, "all"=>0];

	foreach($quantitiesHolder['parent'] as $sID=>$amount){
		$entityType['all'] += floatval($amount);

		$entityType['items'] += $quantitiesHolder['children'][$sID]['normal'];
		$entityType['completed'] += $quantitiesHolder['children'][$sID]['actioned'];
	}

	return $entityType;
}

function isOTPOptional(){
	$otp_is_optional = getConfigByName('otp_is_optional');
	$otp_is_optional_id = count($otp_is_optional) > 0 ? $otp_is_optional[0]->value : 0;
	return $otp_is_optional_id == 1;
}

function isGRNDocumentsOptional(){
	$grn_documents_optional = getConfigByName('grn_documents_optional');
	$grn_documents_optional_id = count($grn_documents_optional) > 0 ? $grn_documents_optional[0]->value : 0;
	return $grn_documents_optional_id == 1;
}

function getAvailableStockByCostCenter($item_id, $cc){
	$items = \App\InventoryItem::where('inventory_sub_category_id', $item_id)->selectRaw('SUM(stock_in) as stock_in, SUM(stock_out) as stock_out, item_brand_id')
	->groupBy('item_brand_id');

	$cc = explode(',', $cc);
	$ccs = array_map('trim', $cc);
	$store_ids = \App\StoreToCostCenter::whereIn('cost_center', $ccs)->get();

	if($store_ids->count() > 0){
		$storeIDs = $store_ids->pluck('store_id');
		$items = $items->whereIn('inventory_store_id', $storeIDs);
	}


	$totalItems = 0;

	foreach($items->get() as $item){
		$available = floatval($item->stock_in) - floatval($item->stock_out);
		$totalItems+=$available;
	}

	return $totalItems;
}

function getDepartmentalHeadID(){
	return 'Lab Manager';
}

function getTatRemark($id = null){
	$remarks = [
		"1"=>"Excelent",
		"2"=>"Satisfactory",
		"3"=>"Good",
		"4"=>"NEED IMPROVEMENT",
		"5"=>"UNSATISFACTORY",
	];
	if($id){
		return $remarks[strval($id)];
	}else{
		return $remarks;
	}
}
function getDiffBtnDates($date1,$date2){
	$c_date = \Carbon\Carbon::parse($date1);
	$c_date2 = \Carbon\Carbon::parse($date2);
	$diff = $c_date->diffInDays($c_date2);
	return $diff;

}

function getTaxes(){
	return [0,8,16];
}

function getTrainingDepartments($dept_ids){		
	$dept_array =  explode(',', $dept_ids);
	return App\InventoryDepartment::where('company_id', getUserCompany())
	->where('location_id', getCurrentUserLocation()->id)
	->whereIn('id', $dept_array)
	->selectRaw('name')
	->orderBy('name', 'asc')->get();
}

function getTrainingParticipantCnt($training_id){		
	return  App\Models\Training\SkillsOtherTrainingParticipant::where('skills_other_training_id',$training_id)->count();
}

function getTrainingPreviousComments($training_id){	
	return  App\Models\Training\SkillsOtherTrainingComment::where('skills_other_training_id',$training_id)->orderBy('created_at', 'desc')->get();		
}

function getSkillsTrainingPreviousComments($training_id,$matrix_id){	
	return  App\Models\SkillsMatrix\SkillsMatrixTrainingComment::where('skills_matrix_id',$matrix_id)->where('skills_matrix_config_id',$training_id)->orderBy('created_at', 'desc')->get();		
}

function getTrainingParticipantDetails($training_id,$dept_id){
	$participant = array();	
	if(!empty($dept_id)){			
		$participant =  App\Models\Training\SkillsOtherTrainingParticipant::where('skills_other_training_id',$training_id)
		->leftJoin('users as u', function($join){
			$join->on('u.id', 'skills_other_training_participants.participant_id');
			$join->on('u.department_id', 'skills_other_training_participants.department_id');				
		})
		->selectRaw('u.name,is_attended')
		->get();				
	}	
	return $participant;
}

function getMatrixRoles($role_ids){		
	return  \App\ModulePreConfigs::whereIn('id', $role_ids)->selectRaw('description')->get();		
}
function getMatrixRole($role_id){		
	return  \App\ModulePreConfigs::where('id', $role_id)->selectRaw('description')->first();		
}

function getStartAndEndDate($week, $year) {
	$time = strtotime("1 January $year", time());
	$day = date('w', $time);
	$time += ((7*$week)+1-$day)*24*3600;
	$dates[0] = date('M j', $time);
	$time += 6*24*3600;
	$dates[1] = date('M j', $time);		
	$status = "";
	$currentDate = date('Y-m-d');
	$currentDate=date('Y-m-d', strtotime($currentDate));
	$contractDateBegin = date('Y-m-d', strtotime($dates[0]));
	$contractDateEnd = date('Y-m-d', strtotime($dates[1]));
	if ($currentDate > $contractDateEnd){		
		$status =  "Done";
	}else if (($currentDate >= $contractDateBegin) && ($currentDate <= $contractDateEnd)){			
		$status =  "On going";
	}else{		
		$status =  "Coming Soon"; 
	}
	$response = array();
	$response = array('dates'=>$dates,'status'=>$status);
	return $response;
}

function getTrainerName($training_type, $trainer_id) {		
	if($training_type=='Inhouse'){
		$user = App\User::where('id', $trainer_id)->selectRaw('name')->first();	
	}else{
		$user = App\Supplier::where('id', $trainer_id)->selectRaw('name')->first();
	}
	return $user->name;
}

function getMatrixRolesValues($skills_matrix_config_id,$skills_matrix_id){			 
	  $role_values =  App\Models\SkillsMatrix\SkillsMatrixRoleRequirment::leftJoin('skills_matrix_configurations as c', 'c.id', '=', 'skills_matrix_role_requirments.skills_matrix_config_id')
		  ->leftJoin('module_pre_configs as a', 'a.id', '=','skills_matrix_role_requirments.color_code')
		  ->where('skills_matrix_role_requirments.skills_matrix_config_id', $skills_matrix_config_id)
		->where('skills_matrix_role_requirments.skills_matrix_id', $skills_matrix_id)
		->where('skills_matrix_role_requirments.active', 1)
		->selectRaw('skills_matrix_role_requirments.id as role_auto_id,role_id, color_code,color')
		->orderBy('skills_matrix_role_requirments.role_id', 'asc')
		->get();			
	return $role_values;		 		
}

function getTrainingRolesValues($training_matrix_config_id,$skills_training_id){ 
	$role_values =  App\Models\Training\SkillsTrainingMatrixRoleRequirment::leftJoin('skills_training_configurations as c', 'c.id', '=', 'skills_training_matrix_role_requirments.training_matrix_config_id')
		->leftJoin('module_pre_configs as a', 'a.id', '=','skills_training_matrix_role_requirments.color_code')
		->where('skills_training_matrix_role_requirments.training_matrix_config_id', $training_matrix_config_id)
	  ->where('skills_training_matrix_role_requirments.training_matrix_id', $skills_training_id)
	  ->selectRaw('skills_training_matrix_role_requirments.id as role_auto_id,role_id, color_code,color')
	  ->get();
	return $role_values;		 		
}

function getUserRolesValues($user_id,$user_role,$skills_matrix_config_id,$skills_matrix_id,$competence_history_date){	

	if(isset($competence_history_date) && !empty($competence_history_date)){

		$role_values =  App\Models\SkillsMatrix\SkillsMatrixUserRoleRequirment::leftJoin('skills_matrix_configurations as c', 'c.id', '=', 'skills_matrix_user_role_requirments.skills_matrix_config_id')
		->leftJoin('module_pre_configs as a', 'a.id', '=','skills_matrix_user_role_requirments.color_code')
		->where('skills_matrix_user_role_requirments.skills_matrix_config_id', $skills_matrix_config_id)
		->where('skills_matrix_user_role_requirments.skills_matrix_id', $skills_matrix_id)
		->where('skills_matrix_user_role_requirments.role_id', $user_role)
		->where('skills_matrix_user_role_requirments.user_id', $user_id)
		->where('skills_matrix_user_role_requirments.active', 1)
		->whereDate('skills_matrix_user_role_requirments.created_at', $competence_history_date)
		->selectRaw('skills_matrix_user_role_requirments.id as role_auto_id,role_id, color_code,color')
		->orderBy('version_id', 'desc')->first();
	
		$role_total_cnt =  App\Models\SkillsMatrix\SkillsMatrixUserRoleRequirment::leftJoin('skills_matrix_configurations as c', 'c.id', '=', 'skills_matrix_user_role_requirments.skills_matrix_config_id')
		->leftJoin('module_pre_configs as a', 'a.id', '=','skills_matrix_user_role_requirments.color_code')
		->where('skills_matrix_user_role_requirments.skills_matrix_config_id', $skills_matrix_config_id)
		->where('skills_matrix_user_role_requirments.skills_matrix_id', $skills_matrix_id)
		->where('skills_matrix_user_role_requirments.user_id', $user_id)
		->where('skills_matrix_user_role_requirments.active', 1)
		->whereDate('skills_matrix_user_role_requirments.created_at', $competence_history_date)
		->count();

	}else{

		$role_values =  App\Models\SkillsMatrix\SkillsMatrixUserRoleRequirment::leftJoin('skills_matrix_configurations as c', 'c.id', '=', 'skills_matrix_user_role_requirments.skills_matrix_config_id')
		->leftJoin('module_pre_configs as a', 'a.id', '=','skills_matrix_user_role_requirments.color_code')
		->where('skills_matrix_user_role_requirments.skills_matrix_config_id', $skills_matrix_config_id)
		->where('skills_matrix_user_role_requirments.skills_matrix_id', $skills_matrix_id)
		->where('skills_matrix_user_role_requirments.role_id', $user_role)
		->where('skills_matrix_user_role_requirments.user_id', $user_id)
		->where('skills_matrix_user_role_requirments.active', 1)
		->selectRaw('skills_matrix_user_role_requirments.id as role_auto_id,role_id, color_code,color')
		->orderBy('version_id', 'desc')->first();
			
		$role_total_cnt =  App\Models\SkillsMatrix\SkillsMatrixUserRoleRequirment::leftJoin('skills_matrix_configurations as c', 'c.id', '=', 'skills_matrix_user_role_requirments.skills_matrix_config_id')
		->leftJoin('module_pre_configs as a', 'a.id', '=','skills_matrix_user_role_requirments.color_code')
		->where('skills_matrix_user_role_requirments.skills_matrix_config_id', $skills_matrix_config_id)
		->where('skills_matrix_user_role_requirments.skills_matrix_id', $skills_matrix_id)
		->where('skills_matrix_user_role_requirments.active', 1)
		->where('skills_matrix_user_role_requirments.user_id', $user_id)
		->count();

	}				
	  $response = array();
	  $response = array('role_values'=>$role_values,'role_total_cnt'=>$role_total_cnt);	
	return $response;		 		
}

function check_is_new_user($user_id,$skills_matrix_config_id,$skills_matrix_id){

$default_color_code = App\ModulePreConfigs::where('type','Training')->where('color','#ffffff')->first();

$user_exists =  App\Models\Training\SkillsMatrixTrainingNeed::leftJoin('skills_matrix_configurations as c', 'c.id', '=', 'skills_matrix_training_needs.skills_matrix_config_id')
	->leftJoin('module_pre_configs as a', 'a.id', '=','skills_matrix_training_needs.color_code')
	->where('skills_matrix_training_needs.skills_matrix_config_id', $skills_matrix_config_id)
	->where('skills_matrix_training_needs.skills_matrix_id', $skills_matrix_id)
	->where('skills_matrix_training_needs.user_id', $user_id)
	->where('skills_matrix_training_needs.active', 1)
	->count();

	if($user_exists=='0'){
		$matrix_role_requirment_topology = App\Models\SkillsMatrix\SkillsMatrixRoleRequirment::where('skills_matrix_id', $skills_matrix_id)->where('active', 1)->get();	
		if(sizeof($matrix_role_requirment_topology)){							
				foreach($matrix_role_requirment_topology as $matrix_role){
					$training_need_config =  new App\Models\Training\SkillsMatrixTrainingNeed;	
					$training_need_config->skills_matrix_config_id = $matrix_role->skills_matrix_config_id;
					$training_need_config->skills_matrix_id =$matrix_role->skills_matrix_id;
					$training_need_config->user_id =$user_id;
					$training_need_config->version_id = 1;		
					$training_need_config->role_id = $matrix_role->role_id;
					$training_need_config->role_name = $matrix_role->role_name;
					$training_need_config->color_code = $default_color_code->id;
					$training_need_config->code = $default_color_code->code;
					$training_need_config->save();
				}	
		}			
	}	
}

function getUserTrainingRolesValues($user_id,$user_role,$skills_matrix_config_id,$skills_matrix_id,$competence_history_date){
	// If new user is added then add the defult tranning conf settings
	check_is_new_user($user_id,$skills_matrix_config_id,$skills_matrix_id);

	if(isset($competence_history_date) && !empty($competence_history_date)){
		
		$role_values =  App\Models\Training\SkillsMatrixTrainingNeed::leftJoin('skills_matrix_configurations as c', 'c.id', '=', 'skills_matrix_training_needs.skills_matrix_config_id')
		->leftJoin('module_pre_configs as a', 'a.id', '=','skills_matrix_training_needs.color_code')
		->where('skills_matrix_training_needs.skills_matrix_config_id', $skills_matrix_config_id)
		->where('skills_matrix_training_needs.skills_matrix_id', $skills_matrix_id)
		->where('skills_matrix_training_needs.role_id', $user_role)
		->where('skills_matrix_training_needs.user_id', $user_id)
		->where('skills_matrix_training_needs.active', 1)
		->whereDate('skills_matrix_training_needs.created_at', $competence_history_date)
		->selectRaw('skills_matrix_training_needs.id as role_auto_id,role_id, color_code,color,skills_matrix_training_needs.code')
		->orderBy('version_id', 'desc')->first();
			
		$role_total_cnt =  App\Models\Training\SkillsMatrixTrainingNeed::leftJoin('skills_matrix_configurations as c', 'c.id', '=', 'skills_matrix_training_needs.skills_matrix_config_id')
		->leftJoin('module_pre_configs as a', 'a.id', '=','skills_matrix_training_needs.color_code')
		->where('skills_matrix_training_needs.skills_matrix_config_id', $skills_matrix_config_id)
		->where('skills_matrix_training_needs.skills_matrix_id', $skills_matrix_id)
		->where('skills_matrix_training_needs.user_id', $user_id)
		->where('skills_matrix_training_needs.active', 1)
		->whereDate('skills_matrix_training_needs.created_at', $competence_history_date)
		->count();

	}else{

		$role_values =  App\Models\Training\SkillsMatrixTrainingNeed::leftJoin('skills_matrix_configurations as c', 'c.id', '=', 'skills_matrix_training_needs.skills_matrix_config_id')
		->leftJoin('module_pre_configs as a', 'a.id', '=','skills_matrix_training_needs.color_code')
		->where('skills_matrix_training_needs.skills_matrix_config_id', $skills_matrix_config_id)
		->where('skills_matrix_training_needs.skills_matrix_id', $skills_matrix_id)
		->where('skills_matrix_training_needs.role_id', $user_role)
		->where('skills_matrix_training_needs.user_id', $user_id)
		->where('skills_matrix_training_needs.active', 1)
		->selectRaw('skills_matrix_training_needs.id as role_auto_id,role_id, color_code,color,skills_matrix_training_needs.code')
		->orderBy('version_id', 'desc')->first();
			
		$role_total_cnt =  App\Models\Training\SkillsMatrixTrainingNeed::leftJoin('skills_matrix_configurations as c', 'c.id', '=', 'skills_matrix_training_needs.skills_matrix_config_id')
		->leftJoin('module_pre_configs as a', 'a.id', '=','skills_matrix_training_needs.color_code')
		->where('skills_matrix_training_needs.skills_matrix_config_id', $skills_matrix_config_id)
		->where('skills_matrix_training_needs.skills_matrix_id', $skills_matrix_id)
		->where('skills_matrix_training_needs.user_id', $user_id)
		->where('skills_matrix_training_needs.active', 1)
		->count();
	}			
	$response = array();
	$response = array('role_values'=>$role_values,'role_total_cnt'=>$role_total_cnt);	  
	
	return $response;		 		
}
// $captured->supercsript_base,$captured->superscript_negative,$captured->superscript_number,$captured->remark
function getCustomExp($base,$is_neagtive,$to_power,$remark){
	$pass_path = ['0'=>'ten_power_zero.JPG','-1'=>'ten_power_neg_one.JPG','-2'=>'ten_power_neg_two.JPG','-3'=>'ten_power_neg_three.JPG','-4'=>'ten_power_neg_four.JPG','-5'=>'ten_power_neg_five.JPG','-6'=>'ten_power_neg_six.JPG','-7'=>'ten_power_neg_seven.JPG','-8'=>'ten_power_neg_eight.JPG','-9'=>'ten_power_neg_nine.JPG','-10'=>'ten_power_neg_ten.JPG','-11'=>'ten_power_neg_eleven.JPG','-12'=>'ten_power_neg_twelve.JPG'];
	$fail_path = ['0'=>'ten_power_zero_red.JPG','-1'=>'ten_power_neg_one_red.JPG','-2'=>'ten_power_neg_two_red.JPG','-3'=>'ten_power_neg_three_red.JPG','-4'=>'ten_power_neg_four_red.JPG','-5'=>'ten_power_neg_five_red.JPG','-6'=>'ten_power_neg_six_red.JPG','-7'=>'ten_power_neg_seven_red.JPG','-8'=>'ten_power_neg_eight_red.JPG','-9'=>'ten_power_neg_nine_red.JPG','-10'=>'ten_power_neg_ten_red.JPG','-11'=>'ten_power_neg_eleven_red.JPG','-12'=>'ten_power_neg_twelve_red.JPG'];
	$expo_path = $remark == 'FAIL' ? 'images/failexpo/'.$fail_path[strval($to_power)] : 'images/passexpo/'.$pass_path[$to_power];
	$image = public_path($expo_path);
	return '<span>'.$base.' x </span><img src="'.$image.'" alt="expo icon" style="width:12px !important;height:10px !important;margin-top:3px !important">';
}

/**
 * Get available template processes
 * 
 * @return array
 */
function getTemplateProcesses()
{
	return [
		'submission_process' => 'Submission Process',
		'equipment_disposal' => 'Equipment Disposal',
		'sample_coa' => 'Sample COA',
		'sample_rejection' => 'Sample Rejection',
		'audit_report' => 'Audit Report',
		'document_management' => 'Document Management',
		'quality_control' => 'Quality Control',
	];
}

function getAuditWorkflowSteps()
{
	return [
		1 => 'Scheduled',
		2 => 'In Progress',
		3 => 'Record Findings & NC',
		4 => 'Findings Review',
		5 => 'Root Cause Analysis',
		6 => 'CAPA Assigned',
		7 => 'CAPA In Progress',
		8 => 'CAPA Verification',
		9 => 'Pending Closure',
		10 => 'Closed',
	];
}

function getAuditWorkflowTotals()
{
	$companyId = getUserCompany() ?? 0;
	$workflowSteps = getAuditWorkflowSteps();
	$totals = [];

	foreach ($workflowSteps as $stepNum => $stepName) {
		$totals[$stepNum] = App\Models\AuditModule\Audit::where('company_id', $companyId)
			->where('status_name', $stepName)
			->count();
	}

	return $totals;
}

/**
 * Map risk_statuses.workflow_step (1–7) to risks.workflow_step (2–8) used on the risk record.
 */
function mapRiskStatusWorkflowStepToRiskRecordStep(int $statusStep): int
{
	if ($statusStep >= 7) {
		return 8;
	}

	return $statusStep + 1;
}

/**
 * Map risks.workflow_step (1–8) to risk_statuses.workflow_step (1–7) for UI keys / sidebar.
 */
function mapRiskRecordWorkflowStepToStatusStep(?int $riskStep): ?int
{
	if ($riskStep === null || $riskStep < 1) {
		return null;
	}
	if ($riskStep >= 8) {
		return 7;
	}
	if ($riskStep <= 1) {
		return 1;
	}

	return $riskStep - 1;
}

/**
 * Canonical risk workflow steps (independent of configurable risk statuses).
 *
 * @return array<int, string>
 */
function getRiskWorkflowStepDefinitions(): array
{
	return [
		1 => 'Identified',
		2 => 'Under Assessment',
		3 => 'Under Evaluation',
		4 => 'Treatment Planning',
		5 => 'Treatment Implementation',
		6 => 'Under Monitoring',
		7 => 'Closed',
	];
}

/**
 * Risk workflow for navigation: key 0 = "All Risks" filter; keys 1–7 match risk_statuses.workflow_step
 * (Step 1 = Identified, …, Step 7 = Closed). Counts/queries map to risks.workflow_step via
 * mapRiskStatusWorkflowStepToRiskRecordStep().
 *
 * @return array<int, string>
 */
function getRiskWorkflowSteps(): array
{
	return [
		0 => 'All Risks',
		...getRiskWorkflowStepDefinitions(),
	];
}

/**
 * Counts per sidebar workflow step. Key 0 = all risks; keys 1–7 use risks.workflow_step via mapping.
 *
 * @return array<int, int>
 */
function getRiskWorkflowTotals(): array
{
	$risksByStep = App\Models\RiskManagement\Risk::forCompany()
		->selectRaw('workflow_step, COUNT(*) as count')
		->groupBy('workflow_step')
		->pluck('count', 'workflow_step')
		->toArray();

	$workflowSteps = getRiskWorkflowSteps();
	$totals = [];

	foreach ($workflowSteps as $stepNum => $_stepName) {
		if ($stepNum === 0) {
			$totals[0] = App\Models\RiskManagement\Risk::forCompany()->count();
		} else {
			$riskStep = mapRiskStatusWorkflowStepToRiskRecordStep($stepNum);
			$totals[$stepNum] = $risksByStep[$riskStep] ?? 0;
		}
	}

	return $totals;
}

function getNCHotspotsByDepartment()
{
	$companyId = getUserCompany() ?? 0;

	return App\Models\AuditModule\NonConformance::where('company_id', $companyId)
		->whereNotNull('department')
		->selectRaw('department as label, count(*) as count')
		->groupBy('department')
		->orderByDesc('count')
		->limit(10)
		->pluck('count', 'label')
		->toArray();
}

function getNCHotspotsByOrigin()
{
	$companyId = getUserCompany() ?? 0;

	return App\Models\AuditModule\NonConformance::where('company_id', $companyId)
		->whereNotNull('origin_name')
		->selectRaw('origin_name as label, count(*) as count')
		->groupBy('origin_name')
		->orderByDesc('count')
		->limit(10)
		->pluck('count', 'label')
		->toArray();
}

function getNCHotspotsByRiskLevel()
{
	$companyId = getUserCompany() ?? 0;

	return App\Models\AuditModule\NonConformance::where('company_id', $companyId)
		->whereNotNull('risk_level_name')
		->selectRaw('risk_level_name as label, count(*) as count')
		->groupBy('risk_level_name')
		->orderByDesc('count')
		->limit(10)
		->pluck('count', 'label')
		->toArray();
}

/**
 * Resolve a reporting_units.id from a unit name or existing UUID value.
 */
function resolveReportingUnitIdFromName(?string $unitName): ?string
{
	if ($unitName === null) {
		return null;
	}

	$unitName = trim($unitName);
	if ($unitName === '') {
		return null;
	}

	if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $unitName)) {
		$byId = App\ReportingUnit::query()->where('id', $unitName)->value('id');

		return $byId ? (string) $byId : null;
	}

	$id = App\ReportingUnit::query()
		->whereRaw('LOWER(name) = ?', [mb_strtolower($unitName)])
		->value('id');

	return $id ? (string) $id : null;
}

/**
 * Resolve a reporting unit id, creating the master-list row when only a name exists.
 * Analysis elements often store unit names (e.g. mg/kg) that were never seeded into reporting_units.
 */
function ensureReportingUnitIdFromName(?string $unitName): ?string
{
	$resolved = resolveReportingUnitIdFromName($unitName);
	if ($resolved !== null) {
		return $resolved;
	}

	if ($unitName === null) {
		return null;
	}

	$trimmed = trim($unitName);
	if ($trimmed === '' || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $trimmed)) {
		return null;
	}

	$unit = App\ReportingUnit::query()->firstOrCreate(
		['name' => $trimmed],
		['active' => true],
	);

	return (string) $unit->id;
}

/**
 * Human-readable reporting unit label from a UUID id or legacy name string.
 */
function resolveReportingUnitLabel(?string $value): string
{
	if ($value === null || trim($value) === '') {
		return '-';
	}

	$value = trim($value);

	if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value)) {
		return App\ReportingUnit::query()->where('id', $value)->value('name') ?? '-';
	}

	return $value;
}

/**
 * Resolve reporting_unit_id from analysis_elements.reporting_unit (unit name string).
 */
function resolveReportingUnitIdFromAnalyte(?string $analysisTypeId, ?string $analyteId, ?string $existingReportingUnitId): ?string
{
	$resolvedExisting = resolveReportingUnitIdFromName($existingReportingUnitId);
	if ($resolvedExisting) {
		return $resolvedExisting;
	}

	$unitName = null;
	if ($analysisTypeId && $analyteId) {
		$ae = App\AnalysisElements::query()
			->where('analysis_type_id', $analysisTypeId)
			->where('analyte_id', $analyteId)
			->where('active', 1)
			->first();
		$unitName = $ae->reporting_unit ?? null;
	}

	if (empty($unitName) && $analyteId) {
		$analyte = App\Analyte::find($analyteId);
		$unitName = $analyte->reporting_unit ?? null;
	}

	return ensureReportingUnitIdFromName($unitName);
}

require_once __DIR__.'/risk_helpers.php';
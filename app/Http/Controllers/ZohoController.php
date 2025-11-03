<?php

namespace App\Http\Controllers;

use App\EntityAttachment;
use App\InventorySubCategories;
use App\ModulePreConfigs;
use App\RequestEntity;
use App\RequestEntityItem;
use App\Supplier;
use App\SupplierCategory;
use App\ZohoApiTokens;
use App\ZohoCustomers;
use Carbon\Carbon;
use Error;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ZohoController extends Controller
{
	protected $client;
	protected $authClient;
	private $orgID;
	private $token;
	private $url;
	private $clientID;
	private $refresh;
	private $clientSecret;
	private $refreshUrl;

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function __construct()
	{
		$this->orgID = config('zoho.ZOHO_ORGID');
		$this->token = null;
		$this->url = "www.zohoapis.com/books/v3/";

		$this->clientID = config('zoho.ZOHO_CLIENT_ID');
		$this->clientSecret = config('zoho.ZOHO_CLIENT_SECRET');
		$this->refresh = config('zoho.ZOHO_REFRESH_TOKEN');
		$this->refreshUrl = config('zoho.ZOHO_URL');

		$this->authClient = new Client([
			'base_uri' => 'https://accounts.zoho.com/oauth/v2/',
			'verify' => false,
			'headers' => [
				'Content-Type' => 'application/json',
			],
		]);

		$this->token = $this->getToken();
		$this->client = new Client([
			'base_uri' => $this->url,
			'verify' => false,
			'headers' => [
				'Authorization' => 'Zoho-oauthtoken ' . $this->token,
				'Content-Type' => 'application/json',
			],
		]);
	}

	public function authenticate()
	{
		return $this->getToken();
		// return $this->getRefreshToken();
		$url = "https://accounts.zoho.com/oauth/v2/auth?scope=ZohoBooks.fullaccess.ALL&client_id=" . $this->clientID . "&state=testing&response_type=code&redirect_uri=" . $this->refreshUrl . "&access_type=offline&prompt=consent";
		return $url;
	}

	public function getRefreshToken()
	{
		$url = "https://accounts.zoho.com/oauth/v2/token?scope=ZohoBooks.fullaccess.ALL&code=1000.ce2f2b02b65e05f5ef86fe63aa5752d2.5b89f11410b2a7cbe3ceba7ed2abdb0e&client_id=" . $this->clientID . "&client_secret=" . $this->clientSecret . "&redirect_uri=" . $this->refreshUrl . "&grant_type=authorization_code";
		return $url;
	}

	public function getToken()
	{
		$now = Carbon::now()->subMinutes(2);
		$token = ZohoApiTokens::where("expiry", ">", $now)->select('token')->first();

		if ($token && $token->token) {
			return $token->token;
		}

		$data = [
			'refresh_token' => $this->refresh,
			'client_id' => $this->clientID,
			'client_secret' => $this->clientSecret,
			'redirect_uri' => $this->refreshUrl,
			'grant_type' => 'refresh_token',
		];
		$responseBody = null;
		try {
			$response = $this->authClient->post('token', [
				'form_params' => $data,
			]);

			$responseBody = json_decode($response->getBody()->getContents(), true);

			ZohoApiTokens::create([
				"token" => $responseBody['access_token'],
				"expiry" => Carbon::now()->addSeconds(intval($responseBody['expires_in']))
			]);
			return $responseBody['access_token'];
		} catch (\Exception $e) {
			return response()->json(['error' => 'Failed to refresh token', 'message' => [$e->getMessage(), $responseBody]], 500);
		}
	}

	public function getPurchaseOrders(Request $request)
	{
		$pArrays = [];
		if ($request->has("purchaseorder_number")) {
			$pArrays[] = $request->purchaseorder_number;
		} else {
			$pArrays = RequestEntity::whereIn('zoho_status', ['open', 'draft'])->select('zoho_id')->pluck('zoho_id')->toArray();
		}
		$updatedPOs = [];
		foreach ($pArrays as $number) {
			try {
				$response = $this->get('purchaseorders', [
					'query' => [
						'organization_id' => $this->orgID,
						'purchaseorder_number' => $number
					],
				]);
			} catch (ClientException $e) {
				return ['error' => $e->getMessage()];
			}

			$purchaseOrders = json_decode($response, true);
			$lists = $purchaseOrders['purchaseorders'];

			foreach ($lists as $list) {
				if ($list['status'] != "draft") {
					$po = RequestEntity::where('zoho_id', $list['purchaseorder_id'])->first();
					if ($po) {
						$po->update([
							"status" => $list['status'] != "cancelled" ? "Approval Complete" : "Rejected",
							"zoho_status" => $list['status']
						]);
						$updatedPOs[] = $po->request_code;
					}
				}
			}
		}
		return response()->json(['status' => true, "message" => "POs updated : " . implode(",", $updatedPOs)]);
	}

	public function redirect(Request $request)
	{
		return response()->json($request->all());
	}

	public function create($id)
	{
		$po = RequestEntity::with('request_entity_items')->where('id', $id)->first();
		$supplier = $po->supplier();
	}

	public function uploadPODocument(){
		
	}

	function createPurchaseOrder($requestEntity)
	{
		$id = $requestEntity->id;

		$requestItems = RequestEntityItem::with('sub_category')->where('request_id', $id)->get();

		$themItems = [];
		$itemOrder = 0;

		foreach ($requestItems as $item) {
			$isInclusive = intval($item->vat_inc) == 1;
			$vatPerc = intval($item->vat_perc);
			$cIt = [
				"account_id" => $item->item_account_id,
				"item_id" => $item->sub_category->zoho_item_code, // Replace with the appropriate item ID
				"name" => $item->sub_category->name, // Using the description from the request item
				"description" => trim($item['comments']) == "" ? $item->sub_category->name : $item['comments'],
				"item_order" => $itemOrder++,
				"rate" => round(floatval($item['net_value']) / floatval($item['quantity']), 2), // Using the net_value from the request item
				"quantity" => $item['quantity'], // Using the quantity from the request item
			];

			if ($vatPerc > 0) {
				$tax_ids = config("zoho.ZOHO_TAX_IDS");
				if ($isInclusive) {
					$totalPerc = 100 + $vatPerc;
					$totalN = $item['net_value'] * 100 / $totalPerc;

					$cIt['item_total'] = $totalN;
					$cIt['tax_percentage'] = $vatPerc;
					$cIt['item_total_inclusive_of_tax'] = floatval($item['net_value']);
				}
				if (!$isInclusive) {
					$totalPerc = 100 + $vatPerc;
					$totalN = $item['net_value'] * $totalPerc / 100;

					$cIt['item_total'] = floatval($item['net_value']);
					$cIt['tax_percentage'] = $vatPerc;
					$cIt['item_total_inclusive_of_tax'] = $totalN;
				}

				\Log::error("<<<<<<<<<<<<<<<<<<<<<<<" . json_encode($tax_ids));
				\Log::error(">>>>>>>>>>>>>>>>>>>>>>>>>>" . $tax_ids[$vatPerc]);

				if (isset($tax_ids[$vatPerc])) {
					$cIt['tax_id'] = $tax_ids[$vatPerc];
				}
				$cIt['rate'] = round(floatval($cIt['item_total']) / floatval($item['quantity']), 2);
			}

			$themItems[] = $cIt;
		}

		$custom_fields = [
			[
				"customfield_id" => config("zoho.ZOHO_PO_INSERTED_BY_FIELD"), //Inserted by
				"value" => trim($requestEntity->creator()->name)
			],
			[
				"customfield_id" => config("zoho.ZOHO_PO_DESCRIPTION_FIELD"), //description
				"value" => $requestEntity->source_request->description
			]
		];

		$supplier = $requestEntity->supplier();
		$currency = ModulePreConfigs::find($requestEntity->currency);
		$purchaseOrderData = array(
			"currency_id" => $currency->zoho_id,
			"vendor_id" => $supplier->zoho_supplier_id,
			"reference_number" => $requestEntity['request_code'],
			"date" => Carbon::now()->format('Y-m-d'),
			"delivery_date" => Carbon::parse($requestEntity->due_date)->format('Y-m-d'),
			"line_items" => $themItems,
			"notes" => $requestEntity['description'],
			"terms" => $supplier->payment_terms,
			"custom_fields" => $custom_fields
		);

		\Log::error($purchaseOrderData);

		try {
			$response = $this->post('purchaseorders', [
				'form_params' => ['JSONString' => json_encode($purchaseOrderData)],
				'query' => [
					'organization_id' => $this->orgID,
				],
			]);
			$zItem = json_decode($response, true);
			if (!isset($zItem['purchaseorder'])) {
				return ['error' => $zItem['message']];
			}
		} catch (Exception $e) {
			return ['error' => $e->getMessage()];
		}

		$requestEntity->zoho_id = $zItem['purchaseorder']['purchaseorder_id'];
		$requestEntity->save();

		return $response;
	}

	public function createZohoItem($item)
	{
		$itemArr = [
			"name" => $item->name,
			"rate" => $item->unit_price,
			"description" => $item->description
		];

		$response = $this->post('items', [
			'form_params' => ['JSONString' => json_encode($itemArr)],
			'query' => [
					'organization_id' => $this->orgID,
				],
		]);

		$zItem = json_decode($response, true);

		$item->zoho_item_code = $zItem['item']['item_id'];
		$item->save();
	}

	public function createZohoSupplier($supplier)
	{
		$itemArr = [
			"contact_name" => $supplier->name,
			"company_name" => $supplier->name,
			"contact_type" => 'vendor'
		];

		// throw new Error(json_encode($itemArr));

		$response = $this->post('contacts', [
			'form_params' => ['JSONString' => json_encode($itemArr)],
			'query' => [
					'organization_id' => $this->orgID
				],
		]);
		// throw new Error($response);
		$zItem = json_decode($response, true);
		$supplier->zoho_supplier_id = $zItem['contact']['contact_id'];
		$supplier->save();

		return $supplier;
	}

	public function getItemDetails($id)
	{
		$response = $this->get('items/' . $id, [
			'query' => [
				'organization_id' => $this->orgID,
			],
		]);

		$items = json_decode($response, true);
		return $items['item'];
	}

	public function sync_all($type)
	{
		try {
			if ($type == "items") {
				$this->sync_zoho_items();
			}
			if ($type == "vendors") {
				return $this->sync_zoho_vendors();
			}
			if ($type == "currencies") {
				return $this->sync_zoho_currencies();
			}
			if ($type == "customers") {
				return $this->sync_zoho_customers();
			}
		} catch (\Exception $e) {
			throw new Error($e->getMessage());
		}
	}


	public function sync_zoho_items($page = 1)
	{
		$response = $this->get('items', [
			'query' => [
				'organization_id' => $this->orgID,
				'page' => $page
			],
		]);
		$items = json_decode($response, true);
		// return $items;

		// echo $this->token;

		// throw new Error(json_encode($response));

		$zItems = $items['items'];
		$pref = "IM";
		// return $zItems;

		foreach ($zItems as $c) {
			$itemExists = InventorySubCategories::where(function ($query) use ($c) {
				$query->where('name', trim($c['name']));
				$query->orWhere('zoho_item_code', $c['item_id']);
			})->first();

			if (!$itemExists || !isset($itemExists->id)) {
				$itemExists = new InventorySubCategories();
			}

			$itemExists->name = $c['name'];
			$itemExists->description = $c['description'];
			$itemExists->zoho_account_id = $c['purchase_account_id'];
			$itemExists->unit_price = $c['rate'];
			$itemExists->zoho_item_code = $c['item_id'];
			$itemExists->unit_type = "Piece(s)";
			$itemExists->secondary_unit_type = "Piece(s)";
			$itemExists->delivery_days = 2;
			$itemExists->internal_lead_time = 2;
			$itemExists->external_lead_time = 2;
			$itemExists->location_id = 3;
			$itemExists->company_id = 1;
			$itemExists->annual_consumption = 365;
			$itemExists->item_classification = $c['product_type'] == 'goods' ? 1 : 3;
			$itemExists->estimated_variation_in_demand_average_consumption = 10;
			$itemExists->maximum_order_quantity = 100000;
			$itemExists->reaorder_level = 1;
			$itemExists->requires_reorder = 1;
			$itemExists->active = 1;
			$itemExists->minimum_level = 1;
			$itemExists->inventory_category_id = 3;
			$itemExists->code = getNamingConventionCode("SubCategories", false, $pref);

			$itemExists->save();
		}

		if (isset($items['page_context']) && $items['page_context']['has_more_page'] == true) {
			$this->sync_zoho_items($page + 1);
		}

		$this->supplier_to_item_sync();

		return $items;
	}

	public function sync_zoho_vendors($page = 1)
	{
		$response = $this->get('contacts', [
			'query' => [
				'organization_id' => $this->orgID,
				'contact_type' => 'vendor',
				'page' => $page
			],
		]);

		$vendors = json_decode($response, true);

		// \Log::error($page.">>>>>>>").json_encode($vendors);

		$contacts = $vendors['contacts'];

		foreach ($contacts as $c) {
			$supplierExists = Supplier::where(function ($query) use ($c) {
				$query->orWhere('zoho_supplier_id', $c['contact_id']);
			})->first();

			if (!$supplierExists || !isset($supplierExists->id)) {
				$supplierExists = new Supplier();
			}

			$supplierExists->name = $c['contact_name'];
			$supplierExists->email = $c['email'];
			$supplierExists->phone = $c['phone'];
			$supplierExists->company_id = 1;
			$supplierExists->inventory_location_id = 3;
			$supplierExists->zoho_supplier_id = $c['contact_id'];
			$supplierExists->save();
		}
		if (isset($vendors['page_context']) && $vendors['page_context']['has_more_page'] == true) {
			$this->sync_zoho_vendors($page + 1);
		}
	}

	public function sync_zoho_things($things, $single = null)
	{
		$response = $this->get($things, [
			'query' => [
				'organization_id' => $this->orgID,
			],
		]);

		$data = json_decode($response, true);

		$lists = $single ? $data[$single] : $data[$things];

		return json_encode($lists);
	}

	public function save_zoho_item_account_id($id)
	{
		$getItem = $this->sync_zoho_things('');
	}


	public function sync_zoho_currencies()
	{
		$currencies = $this->sync_zoho_things('/settings/currencies', 'currencies');
		// return json_decode($currencies);

		// return ">>>>>>>>>>>>>>>>>>>>>" . json_encode($currencies);

		$currencies = json_decode($currencies, true);
		$config = "Currency";
		$module = "Inventory-Management";

		// ModulePreConfigs::where('type', $config)->where('module', $module)->delete();

		$arr = [];

		foreach ($currencies as $cu) {
			$arr[] = [
				"type" => $config,
				"module" => $module,
				"name" => $cu['currency_code'],
				"zoho_id" => $cu['currency_id'],
				"description" => $cu['currency_name']
			];
		}
		ModulePreConfigs::insert($arr);

		return ModulePreConfigs::where('type', $config)->where('module', $module)->get();
	}

	public function sync_zoho_customers($page = 1)
	{
		$response = $this->get('contacts', [
			'query' => [
				'organization_id' => $this->orgID,
				'contact_type' => 'customer',
				'page' => $page
			],
		]);
		$customers = json_decode($response, true);
		// return $customers;
		$insertCst = [];
		if (isset($customers['contacts'])) {
			foreach ($customers['contacts'] as $customer) {
				$n_customer = ZohoCustomers::where('zoho_contact_id', $customer['contact_id'])->first();
				
				if (!isset($n_customer->id)) {
					$insertCst[] = [
						"zoho_contact_id" => $customer['contact_id'],
						"name" => $customer['customer_name'],
						"currency_id" => $customer['currency_id'],
						"status" => $customer['status'],
						"currency_code" => $customer['currency_code'],
						"email" => $customer['email'],
					];
				}else{
					ZohoCustomers::where('zoho_contact_id', $customer['contact_id'])->update(['email'=>$customer['email']]);
				}

			}
		}
		if (sizeof($insertCst) > 0) {
			ZohoCustomers::insert($insertCst);
		}

		if (isset($customers['page_context']) && $customers['page_context']['has_more_page'] == true) {
			$this->sync_zoho_customers($page + 1);
		}
		return $customers;
	}

	public function getVendorDetails($id)
	{
		$response = $this->get('contacts/' . $id, [
			'query' => [
				'organization_id' => $this->orgID,
			],
		]);

		$vendors = json_decode($response, true);
		return $vendors;
	}

	function createSalesrder($salesOrder)
	{
		$response = $this->post('salesorders', [
			'form_params' => ['JSONString' => json_encode($salesOrder)],
			'query' => [
					'organization_id' => $this->orgID,
				],
		]);

		$zItem = json_decode($response, true);
		return $zItem;

		// return isset($zItem['salesorder']['salesorder_id']) ? $zItem['salesorder']['salesorder_id'] : 0;
	}

	public function query_to_string($query)
	{
		$str = [];
		foreach ($query as $k => $v) {
			$str[] = ($k . "=" . $v);
		}

		return empty($str) ? '' : '?' . implode("&", $str);
	}

	public function get($endpoint, $configs)
	{
		$curl = curl_init();

		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL => 'https://www.zohoapis.com/books/v3/' . $endpoint . $this->query_to_string($configs['query']),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'GET',
				CURLOPT_HTTPHEADER => array(
						'Authorization: Zoho-oauthtoken ' . $this->token,
						'Content-Type: application/json'
					),
				CURLOPT_SSL_VERIFYPEER => false, // Disable SSL verification
				CURLOPT_SSL_VERIFYHOST => false, // Disable SSL verification
			)
		);

		$response = curl_exec($curl);

		if (curl_errno($curl)) {
			$error_msg = curl_error($curl);
			curl_close($curl);
			// Handle the error as needed, for example:
			return 'Error: ' . $error_msg;
		}

		curl_close($curl);
		return $response;
	}

	public function post($endpoint, $configs, $method = 'POST')
	{
		$curl = curl_init();
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL => 'https://www.zohoapis.com/books/v3/' . $endpoint . $this->query_to_string($configs['query']),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => $method,
				CURLOPT_POSTFIELDS => $configs['form_params'],
				CURLOPT_HTTPHEADER => array(
						'Authorization: Zoho-oauthtoken ' . $this->token
					),
				CURLOPT_SSL_VERIFYPEER => false, // Disable SSL verification
				CURLOPT_SSL_VERIFYHOST => false, // Disable SSL verification
			)
		);

		$response = curl_exec($curl);

		if (curl_errno($curl)) {
			$error_msg = curl_error($curl);
			curl_close($curl);
			// Handle the error as needed, for example:
			return 'Error: ' . $error_msg;
		}

		curl_close($curl);
		return $response;
	}

	public function upload($endpoint, $configs, $files = [])
	{
		$url = 'https://www.zohoapis.com/books/v3/' . $endpoint . $this->query_to_string($configs['query']);
		$headers = [];
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		if (isset($files['file'])) {
			$headers[] = 'Content-Type: multipart/form-data';
			
			if (!empty($files['file']) && is_file($files['file'])) {
				$params['file'] = new \CURLFile($files['file'], $files['mime'], basename($files['file']));
				// dd($params);
			}
			
			curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		}

		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($curl);
		$error = curl_error($curl);

		if ($error) {
			throw new Exception("cURL Error: $error");
		}

		curl_close($curl);
		return json_decode($response, true);
	}

	public function getItemsTest()
	{
		// $items = $this->sync_zoho_items();
		$customers = $this->sync_zoho_customers();
		// $currency = $this->sync_zoho_currencies();
		return response()->json(['items'=>$customers]);

		// $invoice = Invoice::with(['currencyinfo','crmCustomer'])->find($invoice_id);
		// $details = InvoiceDetails::with('analysisType')->where('invoice_id',$invoice_id)->get();
		$lineitems = [];
		$itemcounter = 0;
		$lineitems[] = [
			"item_order" => 0,
			"item_id" => '5453571000000088240',
			"rate" => 1000,
			"name" => 'AGRI INPUTS:Agricultural Soil',
			"description" => 'AGRI INPUTS:Agricultural Soil',
			"quantity" => 2,
		];

		$salesOrder = [
			"customer_id" => '5453571000000088212',
			"currency_id" => '5453571000000088099',
			"date" => date('Y-m-d'),
			"line_items" => $lineitems,
			"reference_number" => 'INV0001',
		];
		$response = $this->post('salesorders', [
			'form_params' => ['JSONString' => json_encode($salesOrder)],
			'query' => [
					'organization_id' => $this->orgID,
				],
		]);
		return response()->json($response);
	}

	public function changeSalesOrderStatus($sales_order_id)
	{
		$saleorders = ["status" => "Confirmed"];
		$response = $this->post('salesorders/' . $sales_order_id . '/status/confirmed', [
			'form_params' => ['JSONString' => ''],
			'query' => [
				'organization_id' => $this->orgID,
			],
		]);
		$res = json_decode($response, true);
		return $res;
	}

	public function supplier_to_item_sync()
	{
		SupplierCategory::whereNotNull('supplier_id')->delete();

		DB::statement("
			INSERT INTO supplier_categories (supplier_id, inventory_sub_category_id, status, inventory_item_brand_id, supplier_image)
			SELECT 
				s.id AS supplier_id, 
				i.id AS inventory_sub_category_id, 
				1 AS status, 
				0 AS inventory_item_brand_id, 
				'/images/no-logo.png' AS supplier_image
			FROM 
				suppliers s, 
				inventory_sub_categories i;
		");

		return true;
	}

	public function attachQuote(RequestEntity $request){
		$supplier = $request->supplier()->name;
		$attachment = EntityAttachment::where('title', 'like', '%'.$supplier.'%')
			->where('model_id', $request->parent_request_id)->first();

		$filePath = explode("/storage/supplier-quotes/", $attachment->file);

		$fileNameArray = array_values(array_filter($filePath));

		$filename = $fileNameArray[0];

		$path = storage_path('app/supplier-quotes/'. $filename);
		// return $path;
		if (!File::exists($path)) {
			abort(404);
		}
		
		$file = File::get($path);
		$type = File::mimeType($path);
	
		$response = $this->upload('purchaseorders/'.$request->zoho_id.'/attachment', [
			'query' => [
				'organization_id' => $this->orgID,
			],
			['file' => $file, 'mime' => $type]
		]);
		$zItem = json_decode($response, true);
		
	
		return $response;
	}
}
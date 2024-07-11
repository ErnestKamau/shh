<?php

namespace App\Http\Controllers;

use App\InventorySubCategories;
use App\ModulePreConfigs;
use App\RequestEntity;
use App\RequestEntityItem;
use App\Supplier;
use App\ZohoApiTokens;
use Carbon\Carbon;
use Error;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

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
		$this->orgID = "856810415";
		$this->token = null;
		$this->url = "www.zohoapis.com/books/v3/";
		$this->clientID = "1000.41T7VE3F537WHDSMOKWLJSQ8H00WMF";
		$this->clientSecret = "eeaf33ae5330bf4ee85bdcbadc2cba98219c6cc2e1";
		$this->refresh = "1000.c9638189c2876422d190884369a71cb6.4b3c94e0c657d5a03e68de763499dc10";
		$this->refreshUrl = "http://127.0.0.1:8000/zoho-auth-redirect";

		$this->authClient = new Client([
			'base_uri' => 'https://accounts.zoho.com/oauth/v2/',
			'headers' => [
				'Content-Type' => 'application/json',
			],
		]);

		$this->token = $this->getToken();
		$this->client = new Client([
			'base_uri' => $this->url,
			'headers' => [
				'Authorization' => 'Zoho-oauthtoken ' . $this->token,
				'Content-Type' => 'application/json',
			],
		]);
	}

	public function authenticate()
	{
		$url = "https://accounts.zoho.com/oauth/v2/auth?scope=ZohoBooks.fullaccess.ALL&client_id=" . $this->clientID . "&state=testing&response_type=code&redirect_uri=" . $this->refreshUrl . "&access_type=offline";
		return $url;
	}

	public function getRefreshToken()
	{
		$url = "https://accounts.zoho.com/oauth/v2/token?scope=ZohoBooks.fullaccess.ALL&code=1000.3fdfb76f5da3f0f8352c2dc18ed17293.7e06f97dadde31021552b11c12c0fcf2&client_id=" . $this->clientID . "&client_secret=" . $this->clientSecret . "&redirect_uri=" . $this->refreshUrl . "&grant_type=authorization_code";
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
			return response()->json(['error' => 'Failed to refresh token', 'message' => $e->getMessage()], 500);
		}
	}

	public function getPurchaseOrders(Request $request)
	{
		$pArrays = [];
		if ($request->has("purchaseorder_number")) {
			$pArrays[] = $request->purchaseorder_number;
		} else {
			$pArrays = RequestEntity::where('zoho_status', 'draft')->select('zoho_id')->pluck('zoho_id')->toArray();
		}
		$updatedPOs = [];
		foreach ($pArrays as $number) {
			try {
				$response = $this->get('purchaseorders', [
					'query' => [
						'organization_id' => $this->orgID,
					],
				]);
			} catch (ClientException $e) {
				return ['error' => $e->getMessage()];
			}

			$purchaseOrders = json_decode($response, true);
			$lists = $purchaseOrders['purchaseorders'];

			foreach($lists as $list){
				if($list['status'] != "draft")
				$request = RequestEntity::where('zoho_id', $list['purchaseorder_id'])->first();
				$request->update([
					"status" => "Approval Complete",
					"zoho_status" => $list['status']
				])->save();;
				$updatedPOs[] = $request->request_code;
			}
		}
		return response()->json(['status'=>true, "message"=>"POs updated : ".implode(",", $updatedPOs)]);
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

	function createPurchaseOrder($requestEntity)
	{
		$id = $requestEntity->id;
		$requestEntity->load('currency');
		$requestItems = RequestEntityItem::with('sub_category')->where('request_id', $id)->get();

		$themItems = [];
		$itemOrder = 0;

		foreach ($requestItems as $item) {
			$themItems[] = [
				"account_id" => $item->item_account_id,
				"item_id" => $item->sub_category->zoho_item_code, // Replace with the appropriate item ID
				"name" => $item->sub_category->name."(".$item->uom.")", // Using the description from the request item
				"description" => trim($item['comments']) == "" ? $item->sub_category->name : $item['comments'],
				"item_order" => $itemOrder++,
				"rate" => round(floatval($item['net_value']) / floatval($item['quantity']), 2), // Using the net_value from the request item
				"quantity" => $item['quantity'], // Using the quantity from the request item
			];
		}

		$supplier = $requestEntity->supplier();

		$purchaseOrderData = array(
			"currency_id" => $requestEntity->currency->zoho_id,
			"vendor_id" => $supplier->zoho_supplier_id,
			"reference_number" => $requestEntity['request_code'],
			"date" => Carbon::now()->format('Y-m-d'),
			"delivery_date" => Carbon::now()->addDays(30)->format('Y-m-d'),
			"line_items" => $themItems,
			"notes" => $requestEntity['description'],
			"terms" => $supplier->payment_terms
		);

		$response = $this->post('purchaseorders', [
			'form_params' => ['JSONString' => json_encode($purchaseOrderData)],
			'query' => [
				'organization_id' => $this->orgID,
			],
		]);

		$zItem = json_decode($response, true);
		
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

	public function sync_zoho_items($page = 1)
	{
		$response = $this->get('items', [
			'query' => [
				'organization_id' => $this->orgID,
				'page' => $page
			],
		]);
		$items = json_decode($response, true);

		// echo $this->token;

		// throw new Error(json_encode($items));

		$zItems = $items['items'];

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
			$itemExists->unit_price = $c['rate'];
			$itemExists->zoho_item_code = $c['item_id'];
			$itemExists->save();
		}

		if (isset($response['page_context']) && $response['page_context']['has_more_page'] == true) {
			$this->sync_zoho_items($page + 1);
		}

		return true;
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
		$contacts = $vendors['contacts'];

		foreach ($contacts as $c) {
			$supplierExists = Supplier::where(function ($query) use ($c) {
				$query->where('email', $c['email']);
				$query->orWhere('zoho_supplier_id', $c['contact_id']);
			})->first();

			if (!$supplierExists || !isset($supplierExists->id)) {
				$supplierExists = new Supplier();
			}

			$supplierExists->name = $c['contact_name'];
			$supplierExists->email = $c['email'];
			$supplierExists->phone = $c['phone'];
			$supplierExists->company_id = 1;
			$supplierExists->zoho_supplier_id = $c['contact_id'];
			$supplierExists->save();
		}
		if (isset($response['page_context']) && $response['page_context']['has_more_page'] == true) {
			$this->sync_zoho_vendors($page + 1);
		}
	}

	public function sync_zoho_things($things, $single=null){
		$response = $this->get($things, [
			'query' => [
				'organization_id' => $this->orgID,
			],
		]);

		// \Log::warning($response);

		$data = json_decode($response, true);
		$lists = $single ? $data[$single] : $data[$things];

		return json_encode($lists);
	}

	public function sync_zoho_currencies(){
		$currencies = $this->sync_zoho_things('/settings/currencies', 'currencies');
		$currencies = json_decode($currencies, true);
		$config = "Currency";
		$module = "Inventory-Management";

		ModulePreConfigs::where('type', $config)->where('module', $module)->delete();

		$arr = [];

		foreach($currencies as $cu){
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

	public function sync_zoho_customers()
	{
		$response = $this->get('contacts', [
			'query' => [
				'organization_id' => $this->orgID,
				'contact_type' => 'customer'
			],
		]);
		$customers = json_decode($response, true);
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
		
		return isset($zItem['salesorder']['salesorder_id']) ? 0 : $zItem['salesorder']['salesorder_id'];
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

		curl_setopt_array($curl, array(
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
		));

		$response = curl_exec($curl);

		curl_close($curl);
		return $response;
	}

	public function post($endpoint, $configs)
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://www.zohoapis.com/books/v3/' . $endpoint . $this->query_to_string($configs['query']),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => $configs['form_params'],
			CURLOPT_HTTPHEADER => array(
				'Authorization: Zoho-oauthtoken ' . $this->token
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		return $response;
	}
}

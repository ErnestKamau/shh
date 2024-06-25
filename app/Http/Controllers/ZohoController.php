<?php

namespace App\Http\Controllers;

use App\RequestEntity;
use App\RequestEntityItem;
use App\ZohoApiTokens;
use Carbon\Carbon;
use GuzzleHttp\Client;
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
		$this->orgID = "855258018";
		$this->token = null;
		$this->url = "www.zohoapis.com/books/v3/";
		$this->clientID = "1000.HDGXSESBNWE0S92DE8RRJRN6MDSITE";
		$this->clientSecret = "a733bd7dad6fde602266bc2308dd132775122a28cb";
		$this->refresh = "1000.62abaeac961f1fdfc158ea4e38943966.a2aee9ea5648d3de9d52fbe4520651fe";
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
		$url = "https://accounts.zoho.com/oauth/v2/auth?scope=ZohoBooks.purchaseorders.ALL,ZohoBooks.settings.ALL&client_id=" . $this->clientID . "&state=testing&response_type=code&redirect_uri=" . $this->refreshUrl . "&access_type=offline";
		return $url;
	}

	public function getRefreshToken(){
		$url = "https://accounts.zoho.com/oauth/v2/token?scope=ZohoBooks.purchaseorders.ALL,ZohoBooks.settings.ALL&code=1000.ae57606e1fd5634cf37fd0070b389844.04f49bc588968b12fef6edd18e1f6b1e&client_id=".$this->clientID."&client_secret=". $this->clientSecret ."&redirect_uri=" . $this->refreshUrl . "&grant_type=authorization_code";
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

	public function getPurchaseOrders()
	{
		// return $this->authenticate();
		return $this->getRefreshToken();
		return response()->json($this->getItemDetails("5200076000000089001"));
		// return response()->json($this->getVendorDetails('5200076000000089001'));
		return $this->createPurchaseOrder(10);
		$response = $this->client->get('purchaseorders', [
			'query' => [
				'organization_id' => $this->orgID,
			],
		]);
		$purchaseOrders = json_decode($response->getBody()->getContents(), true);

		return $purchaseOrders['purchaseorders'];
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

	function createPurchaseOrder($id)
	{
		$requestEntity = RequestEntity::find($id); 
		$requestItems = RequestEntityItem::where('request_id', $id)->get();

		$themItems = [];
		$itemOrder = 0;
		foreach($requestItems as $item){
			$IT = $this->getItemDetails("5200076000000089001");
			$themItems[] = [
				"item_id" => $IT['item_id'], // Replace with the appropriate item ID
				"account_id" => $IT['account_id'], // Replace with the appropriate account ID
				"name" => $IT['name'], // Using the description from the request item
				"description" => trim($item['comments']) == "" ? $IT['name'] : $item['comments']." ".$IT['description'],
				"item_order" => $itemOrder++,
				"rate" => $IT['sales_rate'], // Using the net_value from the request item
				"quantity" => $item['quantity'], // Using the quantity from the request item
				"unit" => $IT['unit'], // Using the UOM from the request item
				"tax_id" => "",
				"tags" => []
			];
		}

		$purchaseOrderData = array(
			"vendor_id" => "5200076000000086201",
			// "contact_persons" => array(
			// 	"460000000026051"
			// ),
			"reference_number" => $requestEntity['request_code'],
			"date" => Carbon::now()->format('Y-m-d'),
			"delivery_date" => Carbon::now()->addDays(30)->format('Y-m-d'),
			"custom_fields" => array(),
			"ship_via" => "Road",
			"line_items" => $themItems,
			"notes" => $requestEntity['description'],
			"terms" => $requestEntity['request_code'],
			"delivery_org_address_id" => "",
			"delivery_customer_id" => "",
			"attention" => "",
			"template_id" => "",
			"is_inclusive_tax" => "",
			"documents" => array()
		);

		// return json_encode($purchaseOrderData);

		$response = $this->client->post('purchaseorders', [
			'form_params' => ['JSONString' => json_encode($purchaseOrderData)],
			'query' => [
				'organization_id' => $this->orgID,
			],
		]);

		return $response;
	}

	public function getItemDetails($id){
		$response = $this->client->get('items/'.$id, [
			'query' => [
				'organization_id' => $this->orgID,
			],
		]);

		$items = json_decode($response->getBody()->getContents(), true);
		return $items['item'];
	}

	public function getVendorDetails($id){
		$response = $this->client->get('contacts/'.$id, [
			'query' => [
				'organization_id' => $this->orgID,
			],
		]);

		$vendors = json_decode($response->getBody()->getContents(), true);
		return $vendors;
	}
}

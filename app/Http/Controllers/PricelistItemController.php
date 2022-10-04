<?php

namespace App\Http\Controllers;

use App\Pricelist;
use App\PricelistItem;
use App\PricelistCustomer;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PricelistItemController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function __construct()
  {
    $this->middleware('auth');
	}

	public function index()
	{
		$pricelists = Pricelist::join('module_pre_configs as mpc', function($join){
				$join->on('mpc.id', 'pricelists.currency_id');
				$join->where('mpc.type', 'Currency');
			})->selectRaw('pricelists.*, mpc.name as currency_name')
			->orderBy('is_master', 'desc')->orderBy('created_at', 'asc')->get();
		return view('layouts.lab.pricelist.index', compact('pricelists'));
	}

	public function getLastLevel($pricelist_id){
		$maxLevel = PricelistItem::where('pricelist_id', $pricelist_id)->max('level');
		return $maxLevel ?? 0;
	}

	public function email(Request $request, $id){
		$companyDetails = getCompanyDetails();
		// return response()->json($request->all(), 200);
		if(trim($request->message)!=""){
			$messagebody = $request->message;
		}
		else{
			$messagebody = 'We have revised our pricelist. Please find the pricelist attached.';
		}

		$pricelist = Pricelist::find($id);

		foreach($request->clients as $client){
			$client  = (object) $client;

			$body = '
				Hi '.$client->name.',<br><br>
				'.$messagebody.'<br>
				Regards,<br><br>
				'.$companyDetails['name'].'
			';

			$subject = '['.$companyDetails["name"].'] '.date('Y').' Pricelist';

			$file = \storage_path().'/app/pricelist/'.$pricelist->pricelist_file;

			$notify = notify_user($body, $client->email, $subject, $file);
		}

		return redirect()->back()->with('success', 'Pricelist email to customers.');
	}

	public function update(Request $request, $id=false, $internal=false){

		$do_currency_conversion = false;

		if($id){
			$pricelist = Pricelist::find($id);

			if($pricelist->currency_id != $request->currency_id){
				$do_currency_conversion = true;
				$currency1 = $pricelist->currency_id;
				$currency2 = $request->currency_id;
			}
		}
		else{
			$pricelist = new Pricelist;
			$pricelist->revision_number = 1;
		}

		$pricelist->code = getNamingConventionCode("Pricelist", false, "PL-");
		$pricelist->description = $request->description;
		$pricelist->currency_id = $request->currency_id;
		$pricelist->is_master = $request->is_master ?? 0;
		$pricelist->valid_till = $request->valid_till ?? '2025-12-31';
		$pricelist->active = $request->active ?? 0;
		$pricelist->document_no = "DOC-";
		$pricelist->save();
	
		if($do_currency_conversion){
			$items = PricelistItem::where('pricelist_id', $id)->get();
			foreach($items as $item){
				if($do_currency_conversion){
					$item->cost_price = convert_currency($item->cost_price, $currency1, $currency2);
					$item->selling_price = convert_currency($item->selling_price, $currency1, $currency2);
					$item->changed_price = convert_currency($item->changed_price, $currency1, $currency2);
				}
				$item->save();
			}
		}

		if($internal){
			return $pricelist;
		}

		return redirect()->back()->with('success', 'Pricelist information '.$id ? 'updated': 'created'.'.');
	}

	public function show($id, $print=false){
		$pricelist = Pricelist::join('module_pre_configs as mpc', function($join){
			$join->on('mpc.id', 'pricelists.currency_id');
			$join->where('mpc.type', 'Currency');
		})->selectRaw('pricelists.*, mpc.name as currency_name')
		->where('pricelists.id', $id)
		->orderBy('is_master', 'desc')->orderBy('created_at', 'asc')->first();

		return view($print ? 'layouts.lab.pricelist.print' : 'layouts.lab.pricelist.show', compact('pricelist'));
	}

	public function move_pricelist_item($direction, $pricelist, $item){
		$theElement = PricelistItem::find($item);
		$currentLevel = $theElement->level;
		$currentMaxLevel = $this->getLastLevel($pricelist);

		if($currentMaxLevel == 0 || $currentLevel == null){
			$theElement->level = $currentMaxLevel+1;
			$theElement->save();

			return json_encode(array("status"=>true));
		}

		if($direction == 'move-up'){
			$newLevel = intval($currentLevel)-1;
		}
		else{
			$newLevel = intval($currentLevel)+1;
		}

		$newLevel = $newLevel < 1 ? 1 : $newLevel;
		$sibling = PricelistItem::where('pricelist_id', $pricelist)->where('level', $newLevel)->first();

		if($sibling){
			$sibling->level = $currentLevel;
			$sibling->save();
		}

		$theElement->level = $newLevel;
		$theElement->save();

		return json_encode(array("status"=>true));
	}

	public function update_item(Request $request, $id){

		// return response()->json($request->all(), 200);

		$existingItem = PricelistItem::find($request->pricelist_item_id);
		$pricelist = Pricelist::find($id);

		$item = $existingItem ?? new PricelistItem;
		$item->pricelist_id = $id;
		$item->analysis_id = $request->analysis_type_id;
		$item->sample_type_id = $request->sample_type_id;
		$item->cost_price = $request->cost_price;
		$item->vat = $request->vat;
		$item->internal_use = $request->internal_use;
		$item->external_view = $request->external_view;
		$item->active = $request->active;
		if(isset($existingItem->pricelist_id)){
			if($existingItem->selling_price != $request->selling_price){
				$item->changed_price = $request->selling_price;
				$pricelist->status = 'has-changes';
				$pricelist->save();
			}
		}
		else{
			$item->selling_price = $request->selling_price;
			$item->changed_price = $request->selling_price;

			$itemLevel = PricelistItem::where('pricelist_id', $request->pricelist_id)->max('level') ?? 1;

			$item->level = $itemLevel;

			$rev = intval($pricelist->revision_number);
			$pricelist->revision_number = 1+$rev;
			$pricelist->save();
		}
		$item->save();

		return response()->json(array("status"=>true, "items"=>$pricelist->items()), 200);
	}

	public function clone_items_to_new_pricelist(Request $request, $id){
		$item_ids = $request->item_id;

		$items = PricelistItem::whereIn('id', $item_ids)->get();

		$previousPricelist = Pricelist::find($id);

		$newPricelist = $this->update($request, false, true);

		$do_currency_conversion = false;

		if($previousPricelist->currency_id != $newPricelist->currency_id){
			$do_currency_conversion = true;
			$currency1 = $previousPricelist->currency_id;
			$currency2 = $newPricelist->currency_id;
		}

		foreach($items as $item){
			$newItem = $item->replicate();
			if($do_currency_conversion){
				$newItem->cost_price = convert_currency($item->cost_price, $currency1, $currency2);
				$newItem->selling_price = convert_currency($item->selling_price, $currency1, $currency2);
				$newItem->changed_price = convert_currency($item->changed_price, $currency1, $currency2);
			}
			$newItem->pricelist_id = $newPricelist->id;
			$newItem->save();
		}

		return redirect()->route('show-pricelist', ['id'=>$newPricelist->id])->with('success', 'Pricelist has been created');
	}

	public function save_price_changes(Request $request, $id){
		$items = PricelistItem::where('pricelist_id', $id)->get();

		foreach($items as $item){
			if($item->selling_price != $item->changed_price){
				$item->selling_price = $item->changed_price;
				$item->save();
			}
		}

		$pricelist = Pricelist::find($id);
		$pricelist->status = 'no-changes';

		$rev = intval($pricelist->revision_number);
		$pricelist->revision_number = 1+$rev;

		$pricelist->save();

		return redirect()->back()->with('success', 'Pricelist price changes completed.');
	}

	public function add_customer(Request $request, $id){
		$customers = $request->customer_ids;
		foreach($customers as $cus){
			$pr = PricelistCustomer::where('customer_id', $cus)->first() ?? new PricelistCustomer;
			$pr->pricelist_id = $id;
			$pr->customer_id = $cus;
			$pr->save();
		}

		return redirect()->back()->with('success', 'Customer assigned to pricelist.');
	}

	public function remove_customer($id){
		$cus = PricelistCustomer::find($id);

		$cus->delete();
		return redirect()->back()->with('success', 'Customer assignment removed.');
	}

	public function upload(Request $request, $id){
		// return response()->json($request->all(), 200);
		$pricelist = Pricelist::find($id);

		if($request->hasFile('document')){
			$path = $request->document->path();

			$rev = $request->name_code."-r".$pricelist->revision_number.".pdf";

      $file = Storage::putFileAs('pricelist', new File($path), $rev);
      $file = explode('/', $file);

      $fName = urlencode(end($file));

      $pricelist->pricelist_file = (String) $fName;
		}

		$pricelist->save();

		return redirect()->back()->with('success', 'Pricelist document updated.');
	}
}

<?php

namespace App\Http\Controllers;

use App\CapturedResult;
use App\SampleHeader;
use App\SampleDetails;
use App\Result;
use Illuminate\Http\Request;

class SampleDetailsController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
  }
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function delete($id)
	{
		$labDate = "To Lab Date";
		$detail = SampleDetails::find($id);
		$header = SampleHeader::find($detail->sample_header_id);
		$date = \App\SampleDate::where('sample_header_id', $header->id)->where('name', $labDate)->get();


		if($date->count() > 0){
			return array("status"=>false, "message"=>"Sample can not be deleted, as the lab process has been started.");
		}

		$sample_sub_cat = $header->batch_code."/".$detail->sample_code;

		$detail->delete();

		$cat = \App\InventorySubCategories::where('name', $sample_sub_cat)->first();

		if(isset($cat->id)) {
			$items = \App\InventoryItem::where('inventory_sub_category_id', $cat->id);
			$item_ids = $items->get()->pluck('id');

			$items->delete();

			\App\InventoryStoreSlotContent::whereIn('inventory_item_id', $item_ids)->delete();

			$cat->delete();
		}
		$captured = CapturedResult::where('sample_detail_id',$id)->get();
		$results = Result::where('sample_detail_id',$id)->get();
		foreach($captured as $c){
			$c->delete();
		}
		foreach($results as $r){
			$r->delete();
		}


		return json_encode(array("status"=>true, "message"=>"deleted"));
	}
}
